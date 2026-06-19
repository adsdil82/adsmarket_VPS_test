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

        $kredit->load(['mijoz', 'grafik' => fn($q) => $q->tolanmagan()->orderBy('oylik_tartib')]);
        $tulovTurlari = TulovTuri::faol()->get();

        return view('tulov.create', compact('kredit', 'tulovTurlari'));
    }

    /** To'lovni saqlash */
    public function store(TulovRequest $request, RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        // Yopilgan shartnomaga to'lov qabul qilib bo'lmaydi
        if ($kredit->holat === 'yopilgan') {
            return back()->withErrors(['summa' => 'Bu shartnoma to\'liq yopilgan. Yangi to\'lov qabul qilish mumkin emas.']);
        }

        // To'lov summasi qoldiq qarzdan katta bo'lmasin
        if ($request->summa > $kredit->qoldiq_qarz) {
            return back()->withErrors([
                'summa' => "To'lov summasi ({$request->summa}) qoldiq qarzdan ({$kredit->qoldiq_qarz}) katta bo'lmasligi kerak."
            ])->withInput();
        }

        $tulov = $this->tulovService->tulovQabul($kredit, $request->validated());

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

        $summa = (float)$tulov->summa;

        // Tulovni audit log bilan o'chiramiz
        $tulov->delete();

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
