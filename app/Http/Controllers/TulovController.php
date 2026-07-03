<?php

namespace App\Http\Controllers;

use App\Http\Requests\TulovRequest;
use App\Models\RegKredit;
use App\Models\TulovTuri;
use App\Services\TulovService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TulovController extends Controller
{
    public function __construct(private TulovService $tulovService) {}

    /** To'lov qabul qilish formasi */
    public function create(RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $kredit->load([
            'mijoz',
            'grafik' => fn($q) => $q->orderBy('oylik_tartib'),
            'tulovlar' => fn($q) => $q->with(['tulovTuri', 'xodim'])->orderByDesc('tolov_sana')->orderByDesc('id'),
        ]);

        // Admin har bir rol uchun qaysi to'lov turlari ko'rinishini cheklab
        // qo'yishi mumkin (Rollar sozlamasidan). Cheklov sozlanmagan bo'lsa —
        // o'zgarishsiz, barcha faol turlar ko'rinadi.
        $ruxsatEtilganIdlar = \App\Models\Rol::korinadiganTulovTurlari(Auth::user()->rol);
        $tulovTurlari = TulovTuri::faol()
            ->when($ruxsatEtilganIdlar, fn($q) => $q->whereIn('id', $ruxsatEtilganIdlar))
            ->get();

        // "Ustama" ustuni — moliyaviy maxfiy ma'lumot, faqat shu rolga ruxsat
        // berilgan bo'lsa ko'rinadi (standart: faqat admin).
        $ustamaKorishMumkin = \App\Models\Rol::ustamaKorishMumkinmi(Auth::user()->rol);

        $bugun = today();
        $tolanmaganQatorlar = $kredit->grafik->filter(
            fn($g) => in_array($g->holat, ['tolanmagan', 'qisman', 'muddati_otgan']) && $g->tolov_sana
        )->sortBy('oylik_tartib')->values();

        $kechikkanQatorlar = $tolanmaganQatorlar->filter(
            fn($g) => $g->tolov_sana && $g->tolov_sana->lt($bugun)
        );
        $kechikkanSumma = $kechikkanQatorlar->sum(fn($g) => $g->tolov_summa - ($g->tolangan_summa ?? 0));
        $kechikkanSoni  = $kechikkanQatorlar->count();

        $birinchiOy = $tolanmaganQatorlar->first();
        $maxKechikishKuni = (int) $kechikkanQatorlar->max('kechikish_kunlari');

        // ── Grafik bo'yicha "vaqtida to'landi / kechikib to'landi / qoldi" nisbati (donut diagramma uchun) ──
        // MUHIM: bu yerda oyning FIFO tartibida "to'liq yopilgan sana"si emas, balki KUMULYATIV
        // balans solishtiriladi: har bir grafik kuniga (tolov_sana) kelib, mijozning O'SHA
        // KUNGACHA jami to'lagan puli (barcha to'lovlar yig'indisi) o'sha kungacha REJA bo'yicha
        // to'lanishi kerak bo'lgan jami summadan (kumulyativ) kam bo'lmasa — o'sha oy "vaqtida"
        // hisoblanadi, hatto boshqa oy qisman-qisman to'lovlar bilan yopilgan bo'lsa ham.
        $grafikTartib  = $kredit->grafik->filter(fn($g) => $g->tolov_sana !== null)->sortBy('oylik_tartib')->values();
        $jamiRejaSumma = (float) $grafikTartib->sum('tolov_summa');

        $vaqtidaTolandi  = 0.0;
        $kechikibTolandi = 0.0;
        $kumulyativReja  = 0.0;
        foreach ($grafikTartib as $g) {
            $kumulyativReja += (float) $g->tolov_summa;
            if ($g->holat !== 'tolangan') {
                continue;
            }
            $kumulyativTolandi = (float) $kredit->tulovlar->filter(
                fn($t) => $t->tolov_sana && $t->tolov_sana->lte($g->tolov_sana)
            )->sum('summa');

            if ($kumulyativTolandi >= $kumulyativReja) {
                $vaqtidaTolandi += (float) $g->tolov_summa;
            } else {
                $kechikibTolandi += (float) $g->tolov_summa;
            }
        }
        $qoldiSumma = max(0, $jamiRejaSumma - $vaqtidaTolandi - $kechikibTolandi);

        $diagrammaFoiz = [
            'vaqtida'  => $jamiRejaSumma > 0 ? round($vaqtidaTolandi  / $jamiRejaSumma * 100, 1) : 0,
            'kechikib' => $jamiRejaSumma > 0 ? round($kechikibTolandi / $jamiRejaSumma * 100, 1) : 0,
            'qoldi'    => $jamiRejaSumma > 0 ? round($qoldiSumma      / $jamiRejaSumma * 100, 1) : 0,
        ];

        // Har bir to'lov turi uchun "keyingi kvitansiya raqami" oldindan ko'rsatish
        // (foydalanuvchi to'lov turini tanlaganda mos raqam darhol ko'rinadi).
        $kvitansiyaPreview = $tulovTurlari->mapWithKeys(
            fn($t) => [$t->id => $this->tulovService->keyingiKvitansiyaOldindanKorish($t->id)]
        );

        return view('tulov.create', compact(
            'kredit', 'tulovTurlari', 'kechikkanSumma', 'kechikkanSoni', 'birinchiOy', 'kvitansiyaPreview', 'ustamaKorishMumkin', 'maxKechikishKuni',
            'diagrammaFoiz', 'vaqtidaTolandi', 'kechikibTolandi', 'qoldiSumma'
        ));
    }

    /** To'lovni saqlash */
    public function store(TulovRequest $request, RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        // Yopilgan shartnomaga to'lov qabul qilib bo'lmaydi
        if ($kredit->holat === 'yopilgan') {
            return back()->withErrors(['summa' => 'Bu shartnoma to\'liq yopilgan. Yangi to\'lov qabul qilish mumkin emas.']);
        }

        // Rolga ruxsat etilmagan to'lov turi — forma orqali (DevTools bilan)
        // o'zgartirib yuborilgan bo'lishi mumkin, shuning uchun bu yerda ham
        // serverda qayta tekshiriladi.
        $ruxsatEtilganIdlar = \App\Models\Rol::korinadiganTulovTurlari(Auth::user()->rol);
        if ($ruxsatEtilganIdlar && !in_array((int) $request->tulov_turi_id, $ruxsatEtilganIdlar)) {
            return back()->withErrors(['tulov_turi_id' => 'Sizning rolingiz uchun bu to\'lov turi ruxsat etilmagan.'])->withInput();
        }

        // To'lov summasi qoldiq qarzdan katta bo'lmasin
        if ($request->summa > $kredit->qoldiq_qarz) {
            return back()->withErrors([
                'summa' => "To'lov summasi ({$request->summa}) qoldiq qarzdan ({$kredit->qoldiq_qarz}) katta bo'lmasligi kerak."
            ])->withInput();
        }

        // XAVFSIZLIK: to'lov sanasi va kvitansiya raqami HECH QACHON formadan
        // (foydalanuvchi kiritgan qiymatdan) olinmaydi — faqat server tomonda
        // belgilanadi. Aks holda kassir orqaga sana qo'yib hisobotlarni
        // buzishi yoki qo'lda raqam yozib kvitansiya tartibini chalkashtirishi
        // mumkin edi. tolov_sana doim BUGUNGI kun, kvitansiya_raqam esa
        // TulovService::tulovQabul() ichida avtomatik generatsiya qilinadi.
        $malumot = $request->validated();
        $malumot['tolov_sana']       = today()->toDateString();
        $malumot['kvitansiya_raqam'] = null;

        $tulov = $this->tulovService->tulovQabul($kredit, $malumot);

        $kvUrl = route('kreditlar.tulov.kvitansiya', [$kredit, $tulov]);

        return redirect()
            ->route('kreditlar.show', $kredit)
            ->with('muvaffaqiyat', "To'lov muvaffaqiyatli qabul qilindi: " . number_format($tulov->summa, 2) . " so'm.")
            ->with('kvitansiya_url', $kvUrl)
            ->with('kvitansiya_id', $tulov->id);
    }

    /** Oldindan to'lov qabul qilish */
    public function oldinStore(TulovRequest $request, RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $oldinTulov = $this->tulovService->oldinTulovSaqlash($kredit, $request->validated());

        return redirect()
            ->route('kreditlar.show', $kredit)
            ->with('muvaffaqiyat', "Boshlang'ich to'lov muvaffaqiyatli saqlandi.");
    }

    /** Ajax — kredit qoldiq ma'lumotlari */
    public function ajaxQoldiq(RegKredit $kredit)
    {
        return response()->json([
            'qoldiq_qarz'     => $kredit->qoldiq_qarz,
            'tolov_qilingan'  => $kredit->tolov_qilingan,
            'kredit_summa'    => $kredit->kredit_summa,
            'holat'           => $kredit->holat,
            'foiz'            => $kredit->tolov_foizi,
        ]);
    }

    /** To'lovni tahrirlash formasi (modal uchun JSON) */
    public function edit(RegKredit $kredit, \App\Models\Tulov $tulov)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $tulovTurlari = TulovTuri::faol()->get();

        if (request()->expectsJson()) {
            return response()->json([
                'tulov' => [
                    'id'              => $tulov->id,
                    'summa'           => (float)$tulov->summa,
                    'tolov_sana'      => $tulov->tolov_sana?->format('Y-m-d'),
                    'tulov_turi_id'   => $tulov->tulov_turi_id,
                    'kvitansiya_raqam'=> $tulov->kvitansiya_raqam,
                    'izoh'            => $tulov->izoh,
                ],
                'tulov_turlari' => $tulovTurlari->map(fn($t) => [
                    'id' => $t->id, 'nomi' => $t->nomi
                ]),
                'update_url' => route('kreditlar.tulov.update', [$kredit, $tulov]),
            ]);
        }

        return redirect()->route('kreditlar.show', $kredit);
    }

    /** To'lovni o'chirish (faqat Admin) */
    public function destroy(RegKredit $kredit, \App\Models\Tulov $tulov)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $summa  = (float)$tulov->summa;
        $tulovId = $tulov->id;

        // Tulovni audit log bilan o'chiramiz
        $tulov->delete();

        // Shu to'lovdan avtomatik yaratilgan "Pul oqimlari" yozuvini ham
        // o'chiramiz — aks holda kassa qoldig'ida osilib qoladi.
        $this->tulovService->pulOqiminiOchir('tulov', $tulovId);

        // Kredit statistikasini yangilash
        $kredit->decrement('tolov_qilingan', $summa);
        $kredit->increment('qoldiq_qarz', $summa);

        // Agar qoldiq_qarz > 0 bo'lsa va holat yopilgan bo'lsa — faolga qaytarish
        $kredit->refresh();
        if ($kredit->qoldiq_qarz > 0 && $kredit->holat === 'yopilgan') {
            $kredit->update(['holat' => 'faol']);
        }

        if (request()->expectsJson()) {
            return response()->json(['muvaffaqiyat' => true]);
        }

        return redirect()
            ->route('kreditlar.show', $kredit)
            ->with('muvaffaqiyat', number_format($summa, 0, '.', ' ') . " so'm to'lov o'chirildi.");
    }

    /** To'lovni yangilash */
    public function update(\Illuminate\Http\Request $request, RegKredit $kredit, \App\Models\Tulov $tulov)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $validated = $request->validate([
            'summa'           => 'required|numeric|min:1|max:' . ($kredit->jami_summa * 2),
            'tolov_sana'      => 'required|date',
            'tulov_turi_id'   => 'required|exists:tulov_turlari,id',
            'kvitansiya_raqam'=> 'nullable|string|max:50',
            'izoh'            => 'nullable|string|max:500',
        ]);

        // Eski va yangi summa farqini kreditga qaytarish
        $farq = (float)$validated['summa'] - (float)$tulov->summa;

        $tulov->update($validated);

        // Kredit qoldiq qarzini yangilash
        if ($farq != 0) {
            $kredit->increment('tolov_qilingan', $farq);
            $kredit->decrement('qoldiq_qarz', $farq);
        }

        // Bog'langan "Pul oqimlari" yozuvini ham yangilab qo'yamiz (summa va
        // sana mos kelmay qolmasligi uchun) — agar avtomatik yozuv bo'lsa.
        \App\Models\PulOqim::where('manba_tur', 'tulov')->where('manba_id', $tulov->id)
            ->update(['summa' => $validated['summa'], 'sana' => $validated['tolov_sana']]);

        if ($request->expectsJson()) {
            return response()->json(['muvaffaqiyat' => true, 'xabar' => "To'lov tahrirlandi"]);
        }

        return redirect()
            ->route('kreditlar.show', $kredit)
            ->with('muvaffaqiyat', "To'lov muvaffaqiyatli tahrirlandi.");
    }

    /** Kvitansiya (kassa kirim orderi) chop etish */
    public function kvitansiya(RegKredit $kredit, \App\Models\Tulov $tulov)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $kredit->load(['mijoz', 'filial']);
        $tulov->load(['tulovTuri', 'xodim']);

        $soz      = \App\Models\Sozlama::barchasi();
        $summaSoz = $this->summaniSozdaIfodalash((float)$tulov->summa);

        // Ushbu to'lov amalga oshirilgan paytdagi qoldiq qarz
        if ($tulov->tolov_sana) {
            $qoldiqSana = (float)$kredit->kredit_summa - (float)$kredit->tulovlar()
                ->where(function ($q) use ($tulov) {
                    $q->where('tolov_sana', '<', $tulov->tolov_sana)
                      ->orWhere(function ($q2) use ($tulov) {
                          $q2->where('tolov_sana', $tulov->tolov_sana)
                             ->where('id', '<=', $tulov->id);
                      });
                })
                ->sum('summa');
            $qoldiqSana = max(0, $qoldiqSana);
        } else {
            $qoldiqSana = max(0, (float)$kredit->qoldiq_qarz);
        }

        return view('tulov.kvitansiya', compact('kredit','tulov','soz','summaSoz','qoldiqSana'));
    }

    /** Summani o'zbek tilida so'zda ifodalash */
    private function summaniSozdaIfodalash(float $n): string
    {
        $n = (int)round($n);
        if ($n === 0) return 'nol';

        $birliklar = ['','bir','ikki','uch','tort','besh','olti','yetti','sakkiz','toqqiz'];
        $onlar     = ['','on','yigirma','ottiz','qirq','ellik','oltmish','yetmish','sakson','toqson'];
        $yuzlar    = ['','bir yuz','ikki yuz','uch yuz','tort yuz','besh yuz',
                       'olti yuz','yetti yuz','sakkiz yuz','toqqiz yuz'];

        $uch = function(int $num) use ($birliklar, $onlar, $yuzlar): string {
            $y = (int)($num / 100); $num %= 100;
            $o = (int)($num / 10);  $b = $num % 10;
            return trim(($y ? $yuzlar[$y] . ' ' : '') . ($o ? $onlar[$o] . ' ' : '') . ($b ? $birliklar[$b] : ''));
        };

        $natija = '';
        $mlrd = (int)($n / 1000000000); $n %= 1000000000;
        $mln  = (int)($n / 1000000);    $n %= 1000000;
        $ming = (int)($n / 1000);       $n %= 1000;

        if ($mlrd) $natija .= $uch($mlrd) . ' milliard ';
        if ($mln)  $natija .= $uch($mln)  . ' million ';
        if ($ming) $natija .= $uch($ming) . ' ming ';
        if ($n)    $natija .= $uch($n);

        $natija = trim($natija);

        // Apostrof bilan harflarni tiklash (faqat alohida so'zlar)
        $natija = preg_replace('/\bton\b/', "to'n", $natija);
        $natija = preg_replace('/\btort\b/', "to'rt", $natija);
        $natija = preg_replace('/\btoqqiz\b/', "to'qqiz", $natija);
        $natija = preg_replace('/\btoqson\b/', "to'qson", $natija);
        $natija = preg_replace('/\bon\b/', "o'n", $natija);
        $natija = preg_replace('/\bottiz\b/', "o'ttiz", $natija);

        return $natija . " so'm";
    }

    private function filialRuxsatTekshir(int $filialId): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->filial_id !== $filialId) {
            abort(403);
        }
    }
}
