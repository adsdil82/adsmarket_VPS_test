<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Kassa;
use App\Models\PulKategoriya;
use App\Models\PulOqim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PulOqimController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $base = PulOqim::with(['kategoriya.ota', 'kassa', 'xodim', 'filial'])
            ->tasdiqlangan()
            ->sanada($danSana, $gachaSana)
            ->filialda($filialId)
            ->when($request->yunalish,    fn($q) => $q->where('yunalish', $request->yunalish))
            ->when($request->kategoriya,  fn($q) => $q->where('kategoriya_id', $request->kategoriya))
            ->when($request->kassa_id,    fn($q) => $q->where('kassa_id', $request->kassa_id))
            ->when($request->qidiruv,     fn($q) => $q->where('izoh', 'like', '%'.$request->qidiruv.'%'));

        // Statistika
        $stat = [
            'kirim'  => (clone $base)->kirim()->sum('summa'),
            'chiqim' => (clone $base)->chiqim()->sum('summa'),
        ];
        $stat['sof'] = $stat['kirim'] - $stat['chiqim'];

        // Kategoriya bo'yicha breakdown (kirim va chiqim alohida)
        $chiqimByKat = (clone $base)->chiqim()
            ->select('kategoriya_id', DB::raw('SUM(summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('kategoriya')
            ->groupBy('kategoriya_id')
            ->orderByDesc('jami')
            ->get();

        $kirimByKat = (clone $base)->kirim()
            ->select('kategoriya_id', DB::raw('SUM(summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('kategoriya')
            ->groupBy('kategoriya_id')
            ->orderByDesc('jami')
            ->get();

        // Pastdagi operatsiyalar ro'yxati — agar foydalanuvchi aniq kassa
        // tanlamagan bo'lsa, standart holatda FAQAT naqd kassa operatsiyalari
        // ko'rsatiladi (oxirgi ustundagi "qoldiq" bitta kassa doirasidagina
        // mantiqiy bo'lgani uchun). Yuqoridagi KPI/kategoriya bloklari esa
        // hamma kassa turlarini (naqd/terminal/bank) ko'rsatishda davom etadi.
        $royxatBase = clone $base;
        if (!$request->kassa_id) {
            $naqdKassaIds = Kassa::where('tur', 'naqd')
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->pluck('id');
            $royxatBase->whereIn('kassa_id', $naqdKassaIds);
        }

        $oqimlar = $royxatBase->orderByDesc('sana')->orderByDesc('id')->paginate(30)->withQueryString();

        // Har bir qatordan keyingi kassa qoldig'i (salda) — shu kassaning davr
        // boshigacha bo'lgan qoldig'i + o'sha paytgacha (shu qatorni qo'shib)
        // bo'lgan barcha amaliyotlar yig'indisi.
        $ochilishCache = [];
        foreach ($oqimlar as $o) {
            if (!array_key_exists($o->kassa_id, $ochilishCache)) {
                $ochilishCache[$o->kassa_id] = (float) PulOqim::tasdiqlangan()
                    ->where('kassa_id', $o->kassa_id)
                    ->where('sana', '<', $danSana)
                    ->selectRaw("COALESCE(SUM(CASE WHEN yunalish='kirim' THEN summa ELSE -summa END),0) as qoldiq")
                    ->value('qoldiq');
            }

            $shuPaytgacha = (float) PulOqim::tasdiqlangan()
                ->where('kassa_id', $o->kassa_id)
                ->where('sana', '>=', $danSana)
                ->where(function ($q) use ($o) {
                    $q->where('sana', '<', $o->sana)
                      ->orWhere(function ($q2) use ($o) {
                          $q2->where('sana', $o->sana)->where('id', '<=', $o->id);
                      });
                })
                ->selectRaw("COALESCE(SUM(CASE WHEN yunalish='kirim' THEN summa ELSE -summa END),0) as net")
                ->value('net');

            $o->qoldiq_keyin = $ochilishCache[$o->kassa_id] + $shuPaytgacha;
        }

        $filiallar   = $user->isAdmin() ? Filial::faol()->get() : collect();
        $kassalar    = Kassa::where('holat', 'faol')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->get();
        $kategoriyalar = PulKategoriya::faol()->with('bolalar')->asosiy()->orderBy('sort_order')->get();

        // Kassalar (to'lov turlari: naqd/terminal/bank) bo'yicha davr boshiga
        // qoldiq, kirim, chiqim va davr oxiriga qoldiq — alohida blok.
        $kassaHarakati = $kassalar->map(function ($k) use ($danSana, $gachaSana) {
            $ochilishQoldiq = (float) PulOqim::tasdiqlangan()
                ->where('kassa_id', $k->id)
                ->where('sana', '<', $danSana)
                ->selectRaw("COALESCE(SUM(CASE WHEN yunalish='kirim' THEN summa ELSE -summa END),0) as qoldiq")
                ->value('qoldiq');

            $davr = PulOqim::tasdiqlangan()
                ->where('kassa_id', $k->id)
                ->sanada($danSana, $gachaSana)
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN yunalish='kirim' THEN summa ELSE 0 END),0) as kirim,
                    COALESCE(SUM(CASE WHEN yunalish='chiqim' THEN summa ELSE 0 END),0) as chiqim")
                ->first();

            $kirim  = (float) $davr->kirim;
            $chiqim = (float) $davr->chiqim;

            return (object) [
                'kassa'           => $k,
                'ochilish_qoldiq' => $ochilishQoldiq,
                'kirim'           => $kirim,
                'chiqim'          => $chiqim,
                'yopilish_qoldiq' => $ochilishQoldiq + $kirim - $chiqim,
            ];
        });

        return view('pul-oqimlari.index', compact(
            'oqimlar', 'filiallar', 'kassalar', 'kategoriyalar', 'kassaHarakati',
            'filialId', 'danSana', 'gachaSana', 'stat', 'chiqimByKat', 'kirimByKat'
        ));
    }

    public function create(Request $request)
    {
        $user      = Auth::user();
        $yunalish  = $request->yunalish ?? 'chiqim';
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $kassalar  = Kassa::where('holat', 'faol')->get();
        $kirimKategoriyalar  = PulKategoriya::kirimRoyxat();
        $chiqimKategoriyalar = PulKategoriya::chiqimRoyxat();
        return view('pul-oqimlari.create', compact(
            'filiallar', 'kassalar', 'yunalish', 'kirimKategoriyalar', 'chiqimKategoriyalar'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'yunalish'     => 'required|in:kirim,chiqim',
            'filial_id'    => 'required|exists:filiallar,id',
            'kassa_id'     => 'required|exists:kassalar,id',
            'kategoriya_id'=> 'required|exists:pul_kategoriyalar,id',
            'sana'         => 'required|date',
            'summa'        => 'required|numeric|min:0.01',
            'izoh'         => 'nullable|string|max:500',
        ]);

        PulOqim::create([
            'filial_id'    => $request->filial_id,
            'kassa_id'     => $request->kassa_id,
            'kategoriya_id'=> $request->kategoriya_id,
            'xodim_id'     => Auth::id(),
            'yunalish'     => $request->yunalish,
            'sana'         => $request->sana,
            'summa'        => $request->summa,
            'izoh'         => $request->izoh,
            'manba_tur'    => 'manual',
            'holat'        => 'tasdiqlangan',
            'tasdiqlagan_id' => Auth::id(),
        ]);

        return redirect()->route('pul-oqimlari.index')
            ->with('muvaffaqiyat', $request->yunalish === 'kirim' ? 'Kirim saqlandi.' : 'Chiqim saqlandi.');
    }

    public function edit(PulOqim $pulOqim)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $pulOqim->filial_id !== $user->filial_id) abort(403);
        // Avtomatik (manba modulidan yaratilgan) yozuvlar bu yerdan
        // tahrirlanmaydi — mos modulning o'zidan o'zgartiriladi, aks holda
        // manba (shartnoma to'lovi, ta'minotchi to'lovi va h.k.) bilan
        // mos kelmay qoladi.
        if ($pulOqim->manba_tur !== 'manual') abort(403, "Avtomatik yozuvni faqat manba modulidan o'zgartirish mumkin.");

        $filiallar  = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $kassalar   = Kassa::where('holat', 'faol')->get();
        $kirimKategoriyalar  = PulKategoriya::kirimRoyxat();
        $chiqimKategoriyalar = PulKategoriya::chiqimRoyxat();
        return view('pul-oqimlari.edit', compact(
            'pulOqim', 'filiallar', 'kassalar', 'kirimKategoriyalar', 'chiqimKategoriyalar'
        ));
    }

    public function update(Request $request, PulOqim $pulOqim)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $pulOqim->filial_id !== $user->filial_id) abort(403);
        if ($pulOqim->manba_tur !== 'manual') abort(403, "Avtomatik yozuvni faqat manba modulidan o'zgartirish mumkin.");

        $request->validate([
            'yunalish'     => 'required|in:kirim,chiqim',
            'filial_id'    => 'required|exists:filiallar,id',
            'kassa_id'     => 'required|exists:kassalar,id',
            'kategoriya_id'=> 'required|exists:pul_kategoriyalar,id',
            'sana'         => 'required|date',
            'summa'        => 'required|numeric|min:0.01',
            'izoh'         => 'nullable|string|max:500',
        ]);

        $pulOqim->update($request->only(
            'yunalish','filial_id','kassa_id','kategoriya_id','sana','summa','izoh'
        ));

        return redirect()->route('pul-oqimlari.index')
            ->with('muvaffaqiyat', 'Operatsiya yangilandi.');
    }

    public function destroy(PulOqim $pulOqim)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        // Avtomatik yozuvlar bu yerdan o'chirilmaydi — manba (shartnoma
        // to'lovi, ta'minotchi to'lovi va h.k.) bilan qarzdorlik/qoldiq
        // mos kelmay qolishining oldini olish uchun, faqat manba modulining
        // o'zidan o'chirish kerak (u yerda tegishli qoldiqlar ham tiklanadi).
        if ($pulOqim->manba_tur !== 'manual') {
            abort(403, "Avtomatik yozuvni faqat manba modulidan (masalan Ta'minotchilar yoki Shartnoma to'lovlari) o'chirish mumkin.");
        }
        $pulOqim->update(['holat' => 'bekor']);
        return back()->with('muvaffaqiyat', 'Operatsiya bekor qilindi.');
    }

    public function ajaxKunlikChart(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $dan      = now()->subDays(29)->toDateString();
        $gacha    = now()->toDateString();

        $rows = PulOqim::tasdiqlangan()->sanada($dan, $gacha)->filialda($filialId)
            ->select('sana', 'yunalish', DB::raw('SUM(summa) as jami'))
            ->groupBy('sana', 'yunalish')
            ->orderBy('sana')
            ->get()
            ->groupBy('sana');

        $labels = []; $kirimlar = []; $chiqimlar = [];
        for ($i = 29; $i >= 0; $i--) {
            $kun = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d.m');
            $dayData  = $rows[$kun] ?? collect();
            $kirimlar[]  = $dayData->where('yunalish','kirim')->sum('jami');
            $chiqimlar[] = $dayData->where('yunalish','chiqim')->sum('jami');
        }

        return response()->json(compact('labels','kirimlar','chiqimlar'));
    }

    /**
     * Pul oqimi hisoboti — bank darajasidagi "Statement of Cash Flows":
     * qatorlarda kategoriyalar (Operatsion / Moliyaviy bo'limlarga bo'lingan),
     * ustunlarda tanlangan yilning 12 oyi + yillik jami, pastda har oy
     * uchun boshlang'ich/yakuniy kassa qoldig'i.
     */
    public function hisobot(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $yil       = (int) ($request->yil ?? now()->year);
        $kassaTuri = in_array($request->kassa_turi, ['naqd','terminal','bank']) ? $request->kassa_turi : null;
        $birlik    = in_array($request->birlik, ['ming','mln']) ? $request->birlik : 'som';
        $bolgich   = $birlik === 'mln' ? 1_000_000 : ($birlik === 'ming' ? 1_000 : 1);

        // "Bargli" kategoriyalar — bolasi yo'q, ya'ni to'g'ridan-to'g'ri
        // yozuv qabul qiladigan kategoriyalar (faqat guruh sarlavhasi bo'lib,
        // o'zi yozuvga ega bo'lmagan ota kategoriyalar chiqarib tashlanadi).
        $kategoriyalar = PulKategoriya::whereDoesntHave('bolalar')->orderBy('kod')->get();

        $oylikXom = PulOqim::tasdiqlangan()
            ->filialda($filialId)
            ->when($kassaTuri, fn($q) => $q->whereHas('kassa', fn($k) => $k->where('tur', $kassaTuri)))
            ->whereYear('sana', $yil)
            ->select('kategoriya_id', DB::raw('MONTH(sana) as oy'), DB::raw('SUM(summa) as jami'))
            ->groupBy('kategoriya_id', 'oy')
            ->get()
            ->groupBy('kategoriya_id');

        $qatorlar = $kategoriyalar->map(function ($kat) use ($oylikXom) {
            $oylar = array_fill(1, 12, 0.0);
            foreach (($oylikXom[$kat->id] ?? collect()) as $r) {
                $oylar[(int) $r->oy] = (float) $r->jami;
            }
            return [
                'kategoriya' => $kat,
                'oylar'      => $oylar,
                'jami'       => array_sum($oylar),
            ];
        })->filter(fn($q) => $q['jami'] != 0 || true); // bo'sh qatorlarni ham ko'rsatamiz (statement to'liqligi uchun)

        $tushumlar = $qatorlar->filter(fn($q) => $q['kategoriya']->yunalish === 'kirim')->values();
        $tolovlar  = $qatorlar->filter(fn($q) => $q['kategoriya']->yunalish === 'chiqim')->values();

        // Har oy uchun umumiy (hamma kategoriya) sof kirim/chiqim — kassa
        // qoldig'ini hisoblash uchun.
        $oylikSofKirim  = array_fill(1, 12, 0.0);
        $oylikSofChiqim = array_fill(1, 12, 0.0);
        foreach ($qatorlar as $q) {
            for ($m = 1; $m <= 12; $m++) {
                if ($q['kategoriya']->yunalish === 'kirim') {
                    $oylikSofKirim[$m] += $q['oylar'][$m];
                } else {
                    $oylikSofChiqim[$m] += $q['oylar'][$m];
                }
            }
        }

        // Yil boshigacha to'plangan qoldiq (Cash at Beginning of Period — Yanvar)
        $boshlangichQoldiq = (float) (PulOqim::tasdiqlangan()->filialda($filialId)
            ->when($kassaTuri, fn($q) => $q->whereHas('kassa', fn($k) => $k->where('tur', $kassaTuri)))
            ->where('sana', '<', "{$yil}-01-01")
            ->selectRaw("SUM(CASE WHEN yunalish='kirim' THEN summa ELSE -summa END) as q")
            ->value('q') ?? 0);

        $oylikBoshlangich = [];
        $oylikYakuniy     = [];
        $joriy = $boshlangichQoldiq;
        for ($m = 1; $m <= 12; $m++) {
            $oylikBoshlangich[$m] = $joriy;
            $joriy += $oylikSofKirim[$m] - $oylikSofChiqim[$m];
            $oylikYakuniy[$m] = $joriy;
        }

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $yillarRoyxati = range(now()->year, 2019);

        return view('pul-oqimlari.hisobot', compact(
            'yil', 'filialId', 'filiallar', 'yillarRoyxati', 'kassaTuri', 'birlik', 'bolgich',
            'tushumlar', 'tolovlar',
            'oylikSofKirim', 'oylikSofChiqim', 'oylikBoshlangich', 'oylikYakuniy'
        ));
    }
}
