<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\PochtaLog;
use App\Models\PochtaShablon;
use App\Models\RegKredit;
use App\Services\HybridPochtaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * HibritPochta — hybrid.pochta.uz orqali jismoniy pochta xatlarini bitta
 * yagona sahifada boshqarish (AutoPay sahifasiga o'xshash tab uslubida).
 * Haqiqiy yuborish (E-IMZO imzolash) hali ham kredit sahifasidagi mavjud
 * oqim orqali amalga oshadi — bu yerda faqat ro'yxat/monitoring/shablonlar.
 */
class HibritPochtaController extends Controller
{
    public function __construct(private HybridPochtaService $svc) {}

    private const TABLAR = ['kutilayotgan', 'loglar', 'shablonlar'];
    private const PER_PAGE_VARIANTLARI = [5, 15, 20, 25, 30, 50, 100];

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $tab      = in_array($request->get('tab'), self::TABLAR, true) ? $request->get('tab') : 'kutilayotgan';
        $qidiruv  = trim((string) $request->get('qidiruv'));
        $holat    = $request->get('holat');
        $perPage  = in_array((int) $request->get('per_page'), self::PER_PAGE_VARIANTLARI, true) ? (int) $request->get('per_page') : 30;

        $kreditlar     = collect();
        $loglar        = collect();
        $shablonlar    = collect();
        $statistika    = [];
        $ozgaruvchilar = [];
        $kechikkanJami = 0;
        $qoldiqJami = 0;
        $jamiSummalar = null;

        if ($tab === 'kutilayotgan') {
            $kechikkanSelect = ['kechikkan_summa' => \App\Models\Grafik::selectRaw(
                    "CASE WHEN reg_kredit.holat = 'muddati_otgan' THEN reg_kredit.qoldiq_qarz ELSE COALESCE(SUM(tolov_summa - tolangan_summa),0) END"
                )
                ->whereColumn('reg_kredit_id', 'reg_kredit.id')
                ->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan'])
                ->whereNotNull('tolov_sana')
                ->where('tolov_sana', '<', now()->toDateString()),
            ];

            $kunPochtaBaseQuery = fn () => RegKredit::query()
                ->muddatiOtgan()
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($qidiruv, fn($q) => $q->where(function ($qq) use ($qidiruv) {
                    $qq->where('shartnoma_raqam', 'like', "%{$qidiruv}%")
                       ->orWhereHas('mijoz', fn($m) => $m->where('ism', 'like', "%{$qidiruv}%")
                                                          ->orWhere('familiya', 'like', "%{$qidiruv}%"));
                }));

            $kreditlar = $kunPochtaBaseQuery()
                ->with(['mijoz', 'filial', 'xodim', 'oxirgiYuborilganPochta'])
                ->addSelect($kechikkanSelect)
                ->orderByDesc('qoldiq_qarz')
                ->paginate($perPage)->withQueryString();

            $sorovJami = $kunPochtaBaseQuery()->addSelect($kechikkanSelect);
            $jamiSummalar = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw('(' . $sorovJami->toSql() . ') as t'))
                ->mergeBindings($sorovJami->getQuery())
                ->selectRaw('
                    SUM(jami_summa) as jami_summa,
                    SUM(boshlangich_tolov) as boshlangich_tolov,
                    SUM(kredit_summa) as kredit_summa,
                    SUM(boshlangich_tolov + tolov_qilingan) as jami_tolangan,
                    SUM(qoldiq_qarz) as qoldiq_qarz,
                    SUM(kechikkan_summa) as kechikkan_summa
                ')
                ->first();

            $kechikkanJami = (float) ($jamiSummalar->kechikkan_summa ?? 0);
            $qoldiqJami = (float) ($jamiSummalar->qoldiq_qarz ?? 0);
        } elseif ($tab === 'loglar') {
            $loglar = PochtaLog::with(['kredit', 'mijoz', 'shablon'])
                ->when($filialId, fn($q) => $q->whereHas('kredit', fn($k) => $k->where('filial_id', $filialId)))
                ->when($holat, fn($q) => $q->where('holat', $holat))
                ->when($qidiruv, fn($q) => $q->where('receiver', 'like', "%{$qidiruv}%")
                    ->orWhereHas('kredit', fn($k) => $k->where('shartnoma_raqam', 'like', "%{$qidiruv}%")))
                ->latest()
                ->paginate($perPage)->withQueryString();

            $statistika = [
                'jami'      => PochtaLog::count(),
                'yuborildi' => PochtaLog::where('holat', 'yuborildi')->count(),
                'xato'      => PochtaLog::where('holat', 'xato')->count(),
                'bugun'     => PochtaLog::whereDate('created_at', today())->count(),
            ];
        } elseif ($tab === 'shablonlar') {
            $shablonlar    = PochtaShablon::orderBy('sort_order')->orderBy('nomi')->get();
            $ozgaruvchilar = PochtaShablon::ozgaruvchilar();

            // Demo ma'lumotlar bilan har bir shablonning "Demo ko'rish" matnini oldindan tayyorlaymiz.
            $demoVars = [
                'mijoz_fio'       => 'Aliyev Vali Zokirovich',
                'shartnoma_raqam' => 'NP-12345',
                'eski_raqam'      => '101/2019',
                'kechikish_kun'   => '18',
                'jami_qarz'       => "2 450 000 so'm",
                'yuborish_sana'   => now()->format('d.m.Y'),
                'tashkilot_nomi'  => \App\Models\Sozlama::ol('kompaniya_nomi', config('app.name')),
            ];
            $shablonlar->each(fn($sh) => $sh->demoMatn = $sh->renderMatn($demoVars));
        }

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $yoqilgan  = $this->svc->isEnabled();

        return view('hibrit_pochta.index', compact(
            'tab', 'kreditlar', 'loglar', 'shablonlar', 'statistika', 'kechikkanJami', 'qoldiqJami', 'jamiSummalar', 'perPage',
            'filiallar', 'filialId', 'qidiruv', 'holat', 'yoqilgan',
            'ozgaruvchilar'
        ));
    }

    /**
     * AJAX: HibritPochta'ning O'ZINING viloyat ro'yxati (bizning ichki
     * manzil bazamizdan FARQLI — Region/Area ID'lar mos kelmasa, xat
     * kabinetda boshqa viloyat/tumanga yaratilib qoladi).
     */
    public function regions()
    {
        return response()->json($this->svc->getRegions());
    }

    /** AJAX: HibritPochta'ning tanlangan viloyatiga tegishli tumanlari. */
    public function areas(Request $request)
    {
        $regionId = (int) $request->get('region_id');
        $areas    = collect($this->svc->getAreas())
            ->when($regionId, fn($c) => $c->filter(fn($a) => (int) ($a['Region']['Id'] ?? 0) === $regionId))
            ->values();

        return response()->json($areas);
    }

    /**
     * AJAX: "yaratildi" holatidagi (hali kabinetda tasdiqlanmagan) xatlarning
     * haqiqiy holatini Hybrid Pochta API'sidan (GET /api/mail/{id}) tekshirib,
     * bizning lokal jadvalimizni sinxronlaydi:
     *  - IsSent=true  → holat 'yuborildi', yuborildi_vaqt = SentOn
     *  - IsDeleted=true → holat 'ochirilgan' (operator kabinetda o'chirib yuborgan)
     *  - aks holda hali kutilmoqda, o'zgarishsiz qoldiriladi
     */
    public function holatlarniSinxronlash(Request $request)
    {
        $loglar = PochtaLog::where('holat', 'yaratildi')
            ->whereNotNull('api_letter_id')
            ->latest()
            ->take(100)
            ->get();

        $yangilandi = 0;
        $ochirilgan = 0;
        $xatolar    = 0;

        foreach ($loglar as $log) {
            $natija = $this->svc->mailHolatiTekshir($log->api_letter_id);

            if (!$natija['topildi']) {
                $xatolar++;
                continue;
            }

            // 404 — xat HibritPochta tizimida umuman topilmadi (kabinetda o'chirilgan).
            if ($natija['ochirilgan']) {
                $log->update([
                    'holat'      => 'ochirilgan',
                    'xato_xabar' => 'Xat HibritPochta kabinetida o\'chirilgan.',
                ]);
                $ochirilgan++;
                continue;
            }

            $mail = $natija['data'];

            if (!empty($mail['IsDeleted'])) {
                $log->update([
                    'holat'      => 'ochirilgan',
                    'xato_xabar' => 'Xat HibritPochta kabinetida o\'chirilgan.',
                    'javob'      => array_merge($log->javob ?? [], ['sync' => $mail]),
                ]);
                $ochirilgan++;
            } elseif (!empty($mail['IsSent'])) {
                $log->update([
                    'holat'          => 'yuborildi',
                    'yuborildi_vaqt' => $mail['SentOn'] ?? now(),
                    'javob'          => array_merge($log->javob ?? [], ['sync' => $mail]),
                ]);
                $yangilandi++;
            }
        }

        return response()->json([
            'ok'          => true,
            'tekshirildi' => $loglar->count(),
            'yuborildi'   => $yangilandi,
            'ochirilgan'  => $ochirilgan,
            'xatolar'     => $xatolar,
        ]);
    }

    /**
     * AJAX: HibritPochta kabinetida o'chirilgan (yoki xato bilan tugagan)
     * loglarni bizning lokal jadvalimizdan ham o'chirish — ro'yxatni
     * tozalash uchun. "Yuborildi" holatidagi (haqiqatda yetkazilgan) loglar
     * hech qachon o'chirilmaydi — bu audit tarixi.
     */
    public function logOchirish(PochtaLog $log)
    {
        if (!in_array($log->holat, ['ochirilgan', 'xato', 'kutilmoqda'], true)) {
            return response()->json(['xato' => "Faqat 'Kabinetda o'chirilgan' yoki 'Xato' holatidagi loglarni o'chirish mumkin."], 422);
        }

        $log->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * AJAX: "Xato" va "Kabinetda o'chirilgan" holatidagi barcha loglarni
     * bir yo'la tozalash (ro'yxatni chalkashtirib yuruvchi eski sinov/xato
     * yozuvlarni ommaviy o'chirish uchun). "Yuborildi" holatidagi yozuvlar
     * hech qachon tegilmaydi — bu audit tarixi.
     */
    public function loglarniTozalash(Request $request)
    {
        $ochirildi = PochtaLog::whereIn('holat', ['xato', 'ochirilgan'])->delete();

        return response()->json(['ok' => true, 'ochirildi' => $ochirildi]);
    }

    /**
     * AJAX: bitta kredit uchun xat yuborish oynasini to'ldirish uchun kerakli
     * ma'lumotlar (shablonlar, oldingi loglar, shablon o'zgaruvchilari, mijoz
     * FIO/manzili). "Kutilayotgan" tabidagi umumiy (bitta) modal shu orqali
     * har bir qatorda boshqa-boshqa kredit uchun to'ldiriladi.
     */
    public function malumot(RegKredit $kredit)
    {
        if (!$this->svc->isEnabled()) {
            return response()->json(['xato' => "Hybrid Pochta sozlanmagan."], 422);
        }

        $kredit->load('mijoz');

        return response()->json([
            'ok'         => true,
            'kredit_id'  => $kredit->id,
            'mijoz'      => [
                'tolik_ism' => $kredit->mijoz?->tolik_ism,
                'manzil'    => $kredit->mijoz?->manzil,
            ],
            'shablonlar' => PochtaShablon::where('holat', 'faol')->orderBy('sort_order')->get(['id', 'nomi', 'matn', 'qayta_yuborish_kun']),
            'loglar'     => PochtaLog::where('reg_kredit_id', $kredit->id)
                ->latest()->take(20)->get()
                ->map(fn($l) => [
                    'shablon_id'     => $l->shablon_id,
                    'yuborildi_vaqt' => $l->yuborildi_vaqt?->toISOString(),
                    'holat'          => $l->holat,
                ]),
            'vars' => $this->svc->buildVars($kredit),
        ]);
    }
}
