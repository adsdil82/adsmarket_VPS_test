<?php

namespace App\Http\Controllers;

use App\Models\AutopayShartnoma;
use App\Models\AutopayTranzaksiya;
use App\Models\Filial;
use App\Models\Mijoz;
use App\Models\NotificationSetting;
use App\Models\RegKredit;
use App\Services\AutoPayService;
use App\Services\TulovService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AutoPayController extends Controller
{
    public function __construct(private AutoPayService $autoPay) {}

    private const TABLAR = ['kutilayotgan', 'yuborilgan', 'tarix', 'kartalar', 'processing', 'monitoring', 'scoring', 'egov'];
    private const PER_PAGE_VARIANTLARI = [5, 15, 20, 25, 30, 50, 100];

    /** Kechikkan shartnomalar / yuborilganlar / to'lovlar tarixi — 3 tabli AutoPay sahifasi. */
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $tab      = in_array($request->get('tab'), self::TABLAR, true) ? $request->get('tab') : 'kutilayotgan';
        $qidiruv  = trim((string) $request->get('qidiruv'));
        $holat    = $request->get('holat');
        $manba    = $request->get('manba');
        $davr     = $request->get('davr', 'bugun');
        $perPage  = in_array((int) $request->get('per_page'), self::PER_PAGE_VARIANTLARI, true) ? (int) $request->get('per_page') : 30;

        $kreditlar = collect();
        $shartnomalar = collect();
        $tranzaksiyalar = collect();
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

            $baseQuery = fn () => RegKredit::query()
                ->muddatiOtgan()
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($qidiruv, fn($q) => $q->where(function ($qq) use ($qidiruv) {
                    $qq->where('shartnoma_raqam', 'like', "%{$qidiruv}%")
                       ->orWhereHas('mijoz', fn($m) => $m->where('ism', 'like', "%{$qidiruv}%")
                                                          ->orWhere('familiya', 'like', "%{$qidiruv}%"));
                }));

            $kreditlar = $baseQuery()
                ->with(['mijoz', 'filial', 'xodim', 'autopayShartnoma'])
                ->addSelect($kechikkanSelect)
                ->orderByDesc('qoldiq_qarz')
                ->paginate($perPage)->withQueryString();

            $sorovJami = $baseQuery()->addSelect($kechikkanSelect);
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
        } elseif ($tab === 'yuborilgan') {
            $shartnomalar = AutopayShartnoma::with(['mijoz', 'kredit.filial'])
                ->when($filialId, fn($q) => $q->whereHas('kredit', fn($k) => $k->where('filial_id', $filialId)))
                ->when($holat, fn($q) => $q->where('holat', $holat))
                ->when($manba, fn($q) => $q->where('manba', $manba))
                ->when($qidiruv, fn($q) => $q->where(function ($qq) use ($qidiruv) {
                    $qq->where('loan_id', 'like', "%{$qidiruv}%")
                       ->orWhereHas('mijoz', fn($m) => $m->where('ism', 'like', "%{$qidiruv}%")
                                                          ->orWhere('familiya', 'like', "%{$qidiruv}%"));
                }))
                ->orderByDesc('yuborilgan_vaqt')
                ->paginate($perPage)->withQueryString();
        } elseif ($tab === 'tarix') {
            $davrOraligi = match ($davr) {
                'bugun'    => [now()->startOfDay(), now()->endOfDay()],
                'shu_oy'   => [now()->startOfMonth(), now()->endOfMonth()],
                'otgan_oy' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
                default    => null,
            };

            $tranzaksiyalar = AutopayTranzaksiya::with(['shartnoma.mijoz', 'shartnoma.kredit.filial', 'shartnoma.kredit.mijoz', 'tulov'])
                ->when($filialId, fn($q) => $q->whereHas('shartnoma.kredit', fn($k) => $k->where('filial_id', $filialId)))
                ->when($manba, fn($q) => $q->whereHas('shartnoma', fn($s) => $s->where('manba', $manba)))
                ->when($qidiruv, fn($q) => $q->whereHas('shartnoma', fn($s) => $s->where('loan_id', 'like', "%{$qidiruv}%")))
                ->when($davrOraligi, fn($q) => $q->whereBetween('sana', $davrOraligi))
                ->orderByDesc('sana')
                ->paginate($perPage)->withQueryString();
        }

        $tanlanganMijoz = null;
        $kartaNatija = null;
        if ($tab === 'kartalar' && $request->filled('mijoz_id')) {
            $tanlanganMijoz = Mijoz::find($request->get('mijoz_id'));
            if ($tanlanganMijoz?->pinfl) {
                $kartaNatija = $this->autoPay->kartaMalumoti($tanlanganMijoz->pinfl);
                if ($kartaNatija['success']) {
                    $this->autoPay->kartalarniSaqla($tanlanganMijoz->id, $kartaNatija['result'] ?? []);
                }
            }
        }

        $processingMijoz = null;
        $processingNatija = null;
        $processingRecheckNatija = null;
        $processingTarix = collect();
        $processingYoqilgan = $this->autoPay->pullikYoqilganmi('processing');
        if ($tab === 'processing') {
            if ($request->filled('mijoz_id')) {
                $processingMijoz = Mijoz::find($request->get('mijoz_id'));
                if ($processingMijoz?->pinfl) {
                    $processingNatija = $this->autoPay->processingQidiruv($processingMijoz->pinfl);
                }
            }
            if ($request->filled('fails_key')) {
                $processingRecheckNatija = $this->autoPay->processingQaytaTekshirish($request->get('fails_key'));
            }
            if ($processingYoqilgan) {
                $tarixNatija = $this->autoPay->processingTarixi(
                    (int) $request->get('tarix_sahifa', 1),
                    30,
                    [
                        'date_from' => $request->get('tarix_dan'),
                        'date_to'   => $request->get('tarix_gacha'),
                        'status'    => $request->get('tarix_status'),
                    ]
                );
                if ($tarixNatija['success']) {
                    $processingTarix = collect($tarixNatija['result']['data'] ?? []);
                }
            }
        }

        $monitoringYoqilgan = $this->autoPay->pullikYoqilganmi('monitoring');
        $monitoringNatija = null;
        if ($tab === 'monitoring' && $request->filled('card_number') && $request->filled('sana_dan')) {
            $turi = $request->get('turi', 'uzcard');
            $monitoringNatija = $turi === 'humo'
                ? $this->autoPay->monitoringHumo($request->get('card_number'), $request->get('sana_dan'), $request->get('sana_gacha') ?: null)
                : $this->autoPay->monitoringUzcard($request->get('card_number'), $request->get('sana_dan'), $request->get('sana_gacha') ?: null, (int) $request->get('sahifa', 0));
        }

        $scoringMijoz = null;
        $scoringNatija = null;
        if ($tab === 'scoring' && $request->filled('mijoz_id')) {
            $scoringMijoz = Mijoz::find($request->get('mijoz_id'));
            if ($scoringMijoz?->pinfl) {
                $scoringNatija = $this->autoPay->scoringAutopayPinfl($scoringMijoz->pinfl);
            }
        }

        $egovMijoz = null;
        $egovXizmatlar = collect();
        $egovNatija = null;
        $egovYoqilgan = $this->autoPay->pullikYoqilganmi('egov');
        if ($tab === 'egov') {
            $xizmatlarNatija = \Illuminate\Support\Facades\Cache::remember('egov_xizmatlar', 3600, fn() => $this->autoPay->egovXizmatlar());
            if ($xizmatlarNatija['success']) {
                $egovXizmatlar = collect($xizmatlarNatija['result']['data'] ?? []);
            }
            if ($request->filled('mijoz_id')) {
                $egovMijoz = Mijoz::find($request->get('mijoz_id'));
                if ($egovMijoz?->pinfl && $request->filled('service_id')) {
                    $egovNatija = $this->autoPay->egovOlish($egovMijoz->pinfl, (int) $request->get('service_id'));
                }
            }
        }

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $yoqilgan  = $this->autoPay->yoqilganmi();
        $scoringYoqilgan = $this->autoPay->pullikYoqilganmi('scoring');

        return view('autopay.index', compact(
            'kreditlar', 'shartnomalar', 'tranzaksiyalar', 'kechikkanJami', 'qoldiqJami', 'jamiSummalar', 'perPage',
            'filiallar', 'filialId', 'yoqilgan', 'tab', 'qidiruv', 'holat', 'davr', 'manba',
            'tanlanganMijoz', 'kartaNatija', 'scoringMijoz', 'scoringNatija', 'scoringYoqilgan',
            'egovMijoz', 'egovXizmatlar', 'egovNatija', 'egovYoqilgan',
            'processingMijoz', 'processingNatija', 'processingRecheckNatija', 'processingTarix', 'processingYoqilgan',
            'monitoringYoqilgan', 'monitoringNatija'
        ));
    }

    /** AJAX: PINFL bor mijozlarni ism/familiya/telefon bo'yicha qidirish (Kartalar tabi uchun). */
    public function mijozQidir(Request $request)
    {
        $q = trim((string) $request->get('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $mijozlar = Mijoz::whereNotNull('pinfl')
            ->where(function ($qq) use ($q) {
                $qq->where('ism', 'like', "%{$q}%")
                   ->orWhere('familiya', 'like', "%{$q}%")
                   ->orWhere('telefon', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id'    => $m->id,
                'label' => "{$m->familiya} {$m->ism} — {$m->telefon}",
            ]);

        return response()->json($mijozlar);
    }

    /**
     * Yangi mijoz uchun kartani auto-yechishga ro'yxatga olish — 1-qadam.
     * Karta ma'lumotlari yuboriladi, AutoPay mijozga OTP jo'natadi.
     */
    public function kartaRoyxatgaOlish(Request $request)
    {
        set_time_limit(60); // OTP so'rovi SMS-gateway sabab 20s'dan uzoq cho'zilishi mumkin
        $validated = $request->validate([
            'mijoz_id'    => 'required|exists:mijozlar,id',
            'card_number' => 'required|digits:16',
            'expire'      => ['required', 'regex:/^\d{2}\/\d{2}$/'],
            'type'        => 'required|in:uzcard,humo',
            'phone'       => 'required_if:type,humo|nullable|string',
        ]);
        $mijoz = Mijoz::findOrFail($validated['mijoz_id']);

        if (!$mijoz->pinfl) {
            return back()->with('xato', "Mijozning PINFL raqami kiritilmagan.");
        }

        // Foydalanuvchi kartada yozilganidek MM/YY kiritadi, AutoPay esa YYMM kutadi.
        [$oy, $yil] = explode('/', $validated['expire']);
        $expireYYMM = $yil . $oy;

        $natija = $this->autoPay->kartaniRoyxatgaOlish(
            $mijoz->pinfl,
            $validated['card_number'],
            $expireYYMM,
            $validated['type'],
            $validated['phone'] ?? null
        );

        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        return redirect()->route('autopay.index', ['tab' => 'kartalar', 'mijoz_id' => $mijoz->id])
            ->with('muvaffaqiyat', "Mijozga OTP kod yuborildi (2 daqiqa amal qiladi).")
            ->with('karta_ext', $natija['result']['ext'] ?? null)
            ->with('karta_type', $validated['type'])
            ->with('karta_telefon_mask', $natija['result']['phone_mask'] ?? null);
    }

    /** Yangi mijoz uchun kartani auto-yechishga ro'yxatga olish — 2-qadam: OTP tasdiqlash. */
    public function kartaTasdiqlash(Request $request)
    {
        set_time_limit(60);
        $validated = $request->validate([
            'mijoz_id' => 'required|exists:mijozlar,id',
            'ext'      => 'required|string',
            'otp_code' => 'required|digits_between:4,6',
            'type'     => 'required|in:uzcard,humo',
        ]);
        $mijoz = Mijoz::findOrFail($validated['mijoz_id']);

        if (!$mijoz->pinfl) {
            return back()->with('xato', "Mijozning PINFL raqami kiritilmagan.");
        }

        $natija = $this->autoPay->kartaniTasdiqlash($mijoz->pinfl, $validated['ext'], $validated['otp_code'], $validated['type']);

        if (!$natija['success']) {
            return redirect()->route('autopay.index', ['tab' => 'kartalar', 'mijoz_id' => $mijoz->id])
                ->with('xato', "AutoPay xatosi: {$natija['error']}")
                ->with('karta_ext', $validated['ext'])
                ->with('karta_type', $validated['type']);
        }

        return redirect()->route('autopay.index', ['tab' => 'kartalar', 'mijoz_id' => $mijoz->id])
            ->with('muvaffaqiyat', "Karta tasdiqlandi, auto-yechish uchun faollashtirildi.");
    }

    /**
     * PULLIK — Monitoring uchun kartani OTP orqali ro'yxatga olish — 1-qadam.
     * Bu Kartalar tabidagi kartaRoyxatgaOlish()dan farqli: mijozga bog'lanmagan,
     * xom karta raqami bilan ishlaydi (monitoring.humo/uzcard chaqirishdan oldin
     * karta egasining roziligini olish uchun).
     */
    public function monitoringKartaRoyxatgaOlish(Request $request)
    {
        set_time_limit(60);
        $validated = $request->validate([
            'card_number' => 'required|digits:16',
            'expire'      => ['required', 'regex:/^\d{2}\/\d{2}$/'],
            'type'        => 'required|in:uzcard,humo',
            'phone'       => 'required_if:type,humo|nullable|string',
        ]);

        [$oy, $yil] = explode('/', $validated['expire']);
        $expireYYMM = $yil . $oy;

        $natija = $this->autoPay->monitoringKartaRoyxatgaOlish(
            $validated['card_number'],
            $expireYYMM,
            $validated['type'],
            $validated['phone'] ?? null
        );

        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        return redirect()->route('autopay.index', ['tab' => 'monitoring'])
            ->with('muvaffaqiyat', "Karta egasiga OTP kod yuborildi (2 daqiqa amal qiladi).")
            ->with('monitor_ext', $natija['result']['ext'] ?? null)
            ->with('monitor_type', $validated['type'])
            ->with('monitor_telefon_mask', $natija['result']['phone_mask'] ?? null);
    }

    /** PULLIK — Monitoring uchun kartani OTP orqali ro'yxatga olish — 2-qadam: OTP tasdiqlash. */
    public function monitoringKartaTasdiqlash(Request $request)
    {
        set_time_limit(60);
        $validated = $request->validate([
            'ext'      => 'required|string',
            'otp_code' => 'required|digits_between:4,6',
            'type'     => 'required|in:uzcard,humo',
        ]);

        $natija = $this->autoPay->monitoringKartaTasdiqlash($validated['ext'], $validated['otp_code'], $validated['type']);

        if (!$natija['success']) {
            return redirect()->route('autopay.index', ['tab' => 'monitoring'])
                ->with('xato', "AutoPay xatosi: {$natija['error']}")
                ->with('monitor_ext', $validated['ext'])
                ->with('monitor_type', $validated['type']);
        }

        return redirect()->route('autopay.index', ['tab' => 'monitoring'])
            ->with('muvaffaqiyat', "Karta tasdiqlandi, monitoring uchun tayyor.");
    }

    /** PULLIK — mijozni E-GOV xizmatlariga ro'yxatdan o'tkazish (birinchi marta). */
    public function egovSaqlashAction(Request $request)
    {
        $validated = $request->validate(['mijoz_id' => 'required|exists:mijozlar,id']);
        $mijoz = Mijoz::findOrFail($validated['mijoz_id']);

        if (!$mijoz->pinfl) {
            return back()->with('xato', "Mijozning PINFL raqami kiritilmagan.");
        }
        $passport = trim(($mijoz->passport_seriya ?? '') . ($mijoz->passport_raqam ?? ''));
        if (!$passport) {
            return back()->with('xato', "Mijozning pasport ma'lumoti kiritilmagan.");
        }

        $natija = $this->autoPay->egovSaqlash($mijoz->pinfl, $passport);
        if (!$natija['success']) {
            $bandmi = str_contains(mb_strtolower($natija['error'] ?? ''), 'занят')
                || str_contains(mb_strtolower($natija['error'] ?? ''), 'already');
            $xabar = "AutoPay xatosi: {$natija['error']}";
            if ($bandmi) {
                $xabar .= " — bu mijoz E-GOV'da allaqachon ro'yxatdan o'tgan (avval yoki boshqa tashkilot tomonidan). "
                    . "Ma'lumotni yangilash uchun \"Ro'yxatdan o'tkazish\" emas, \"Yangilash\" tugmasini bosing.";
            }
            return back()->with('xato', $xabar);
        }

        return redirect()->route('autopay.index', ['tab' => 'egov', 'mijoz_id' => $mijoz->id])
            ->with('muvaffaqiyat', "{$mijoz->familiya} {$mijoz->ism} E-GOV xizmatlariga ro'yxatdan o'tkazildi.");
    }

    /** PULLIK — mijozning E-GOV ma'lumotlarini qayta so'rab yangilash. */
    public function egovYangilashAction(Request $request)
    {
        $validated = $request->validate(['mijoz_id' => 'required|exists:mijozlar,id']);
        $mijoz = Mijoz::findOrFail($validated['mijoz_id']);

        if (!$mijoz->pinfl) {
            return back()->with('xato', "Mijozning PINFL raqami kiritilmagan.");
        }
        $passport = trim(($mijoz->passport_seriya ?? '') . ($mijoz->passport_raqam ?? ''));
        if (!$passport) {
            return back()->with('xato', "Mijozning pasport ma'lumoti kiritilmagan.");
        }

        $natija = $this->autoPay->egovYangilash($mijoz->pinfl, $passport);
        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        return redirect()->route('autopay.index', ['tab' => 'egov', 'mijoz_id' => $mijoz->id])
            ->with('muvaffaqiyat', "{$mijoz->familiya} {$mijoz->ism} uchun E-GOV ma'lumotlari yangilandi.");
    }

    /**
     * Shartnoma uchun loan_id aniqlash. AutoPay'da loan_id o'chirilgandan
     * keyin ham doimiy band bo'lib qoladi (contract.find "not found" desa
     * ham) — shuning uchun avval o'chirilgan shartnomani qayta yuborishda
     * eski loan_id emas, yangi (versiyalangan) loan_id ishlatiladi.
     */
    private function loanIdAniqla(RegKredit $kredit, AutopayShartnoma $shartnoma): string
    {
        return ($shartnoma->loan_id && $shartnoma->holat !== 'ochirilgan')
            ? $shartnoma->loan_id
            : "NP-{$kredit->filial_id}-{$kredit->id}-{$kredit->shartnoma_raqam}"
                . ($shartnoma->holat === 'ochirilgan' ? '-' . now()->format('YmdHis') : '');
    }

    /** Bitta kreditni AutoPay'ga yuborish uchun umumiy mantiq — yuborish() shu yerdan foydalanadi. */
    private function birKreditniYuborish(RegKredit $kredit): array
    {
        if (!$kredit->mijoz?->pinfl) {
            return ['success' => false, 'xabar' => "{$kredit->shartnoma_raqam}: PINFL yo'q"];
        }

        $shartnoma = AutopayShartnoma::firstOrNew(['reg_kredit_id' => $kredit->id]);
        $loanId = $this->loanIdAniqla($kredit, $shartnoma);

        $natija = $this->autoPay->shartnomaYuborish($kredit, $loanId);

        $shartnoma->fill([
            'mijoz_id'        => $kredit->mijoz_id,
            'loan_id'         => $loanId,
            'holat'           => $natija['success'] ? 'faol' : 'xato',
            'auto_yoqilgan'   => $natija['success'],
            'oxirgi_debt'     => $kredit->qoldiq_qarz,
            'xato_matni'      => $natija['error'],
            'yuborgan_id'     => Auth::id(),
            'yuborilgan_vaqt' => now(),
        ])->save();

        return [
            'success' => $natija['success'],
            'xabar'   => $natija['success']
                ? "{$kredit->shartnoma_raqam}: yuborildi"
                : "{$kredit->shartnoma_raqam}: {$natija['error']}",
        ];
    }

    /** Admin/menejer tasdig'i bilan — kechikkan shartnomani AutoPay'ga yuborish va auto-yechishni yoqish. */
    public function yuborish(RegKredit $kredit)
    {
        if (!$this->autoPay->yoqilganmi()) {
            return back()->with('xato', "AutoPay hali sozlamalarda yoqilmagan.");
        }

        $natija = $this->birKreditniYuborish($kredit);

        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['xabar']}");
        }
        return back()->with('muvaffaqiyat', "Shartnoma AutoPay'ga yuborildi, avtomatik yechish yoqildi.");
    }

    /** Bir nechta tanlangan shartnomani BITTA so'rovda (contract.createOrUpdate) AutoPay'ga yuborish. */
    public function yuborishBulk(Request $request)
    {
        if (!$this->autoPay->yoqilganmi()) {
            return back()->with('xato', "AutoPay hali sozlamalarda yoqilmagan.");
        }

        $ids = array_filter((array) $request->input('kredit_ids', []));
        if (!$ids) {
            return back()->with('xato', "Hech qanday shartnoma tanlanmagan.");
        }

        $kreditlar = RegKredit::whereIn('id', $ids)->with('mijoz')->get();

        $tayyor    = [];
        $xaritaMap = []; // loan_id => ['shartnoma' => ..., 'kredit' => ...]
        $ptsiz     = [];

        foreach ($kreditlar as $kredit) {
            if (!$kredit->mijoz?->pinfl) {
                $ptsiz[] = "{$kredit->shartnoma_raqam}: PINFL yo'q";
                continue;
            }

            $shartnoma = AutopayShartnoma::firstOrNew(['reg_kredit_id' => $kredit->id]);
            $loanId    = $this->loanIdAniqla($kredit, $shartnoma);
            $mijoz     = $kredit->mijoz;

            $tayyor[] = [
                'pinfl'       => $mijoz->pinfl,
                'passport'    => trim(($mijoz->passport_seriya ?? '') . ($mijoz->passport_raqam ?? '')),
                'first_name'  => $mijoz->ism,
                'last_name'   => $mijoz->familiya,
                'middle_name' => $mijoz->otasining_ismi,
                'loan_id'     => $loanId,
                'debt'        => $kredit->qoldiq_qarz,
                'ext'         => $loanId,
                'account'     => $kredit->shartnoma_raqam,
                'info'        => "NasiyaPro shartnoma {$kredit->shartnoma_raqam}",
                'auto'        => true,
            ];

            $xaritaMap[$loanId] = ['shartnoma' => $shartnoma, 'kredit' => $kredit];
        }

        if (!$tayyor) {
            return back()->with('xato', implode('; ', $ptsiz) ?: "Yuborish uchun shartnoma topilmadi.");
        }

        $natija = $this->autoPay->shartnomalarniOmmaviyYuborish($tayyor);

        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        $result   = $natija['result'] ?? [];
        $muvOk    = array_merge($result['created_contracts']['ok'] ?? [], $result['updated_contracts']['ok'] ?? []);
        $muvFail  = array_merge($result['created_contracts']['fail'] ?? [], $result['updated_contracts']['fail'] ?? []);

        foreach ($muvOk as $loanId) {
            if (!isset($xaritaMap[$loanId])) {
                continue;
            }
            ['shartnoma' => $shartnoma, 'kredit' => $kredit] = $xaritaMap[$loanId];
            $shartnoma->fill([
                'mijoz_id'        => $kredit->mijoz_id,
                'loan_id'         => $loanId,
                'holat'           => 'faol',
                'auto_yoqilgan'   => true,
                'oxirgi_debt'     => $kredit->qoldiq_qarz,
                'xato_matni'      => null,
                'yuborgan_id'     => Auth::id(),
                'yuborilgan_vaqt' => now(),
            ])->save();
        }

        foreach ($muvFail as $loanId) {
            if (!isset($xaritaMap[$loanId])) {
                continue;
            }
            ['shartnoma' => $shartnoma, 'kredit' => $kredit] = $xaritaMap[$loanId];
            $shartnoma->fill([
                'mijoz_id'        => $kredit->mijoz_id,
                'loan_id'         => $loanId,
                'holat'           => 'xato',
                'auto_yoqilgan'   => false,
                'oxirgi_debt'     => $kredit->qoldiq_qarz,
                'xato_matni'      => 'AutoPay tomonidan rad etildi',
                'yuborgan_id'     => Auth::id(),
                'yuborilgan_vaqt' => now(),
            ])->save();
        }

        $jami  = count($ids);
        $xabar = count($muvOk) . "/{$jami} ta shartnoma yuborildi.";
        if ($muvFail) {
            $xabar .= ' Rad etilgan loan_id: ' . implode(', ', array_slice($muvFail, 0, 5));
        }
        if ($ptsiz) {
            $xabar .= ' ' . implode('; ', array_slice($ptsiz, 0, 5));
        }

        return back()->with(($muvFail || $ptsiz) ? 'xato' : 'muvaffaqiyat', $xabar);
    }

    /** Avtomatik yechishni to'xtatish (masalan mijoz kelib naqd to'lagan bo'lsa). */
    public function toxtatish(AutopayShartnoma $shartnoma)
    {
        $natija = $this->autoPay->avtoToggle($shartnoma->loan_id, false);
        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }
        $shartnoma->update(['holat' => 'toxtatilgan', 'auto_yoqilgan' => false]);
        return back()->with('muvaffaqiyat', "Avtomatik yechish to'xtatildi.");
    }

    /** To'xtatilgan shartnomani qayta yoqish. */
    public function qaytaYoqish(AutopayShartnoma $shartnoma)
    {
        $natija = $this->autoPay->avtoToggle($shartnoma->loan_id, true);
        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }
        $shartnoma->update(['holat' => 'faol', 'auto_yoqilgan' => true]);
        return back()->with('muvaffaqiyat', "Avtomatik yechish qayta yoqildi.");
    }

    /**
     * Shartnomani AutoPay'dan butunlay o'chirish. Bizning yozuvni hard-delete
     * qilmaymiz (tranzaksiyalar tarixi bog'liq) — faqat "ochirilgan" deb
     * belgilaymiz, shartnoma qayta yuborilsa xuddi shu loan_id qayta ishlatiladi.
     */
    public function ochirish(AutopayShartnoma $shartnoma)
    {
        $natija = $this->autoPay->shartnomaOchirish($shartnoma->mijoz->pinfl, $shartnoma->loan_id);

        if (!$natija['success']) {
            // AutoPay'ning "Contract not found" javobi ishonchsiz bo'lishi mumkin —
            // contract.find bilan qo'shimcha tasdiqlaymiz, aks holda haqiqatda
            // mavjud shartnomani xato ravishda "o'chirilgan" deb belgilab qo'yamiz.
            $tekshiruv = $this->autoPay->shartnomaTop($shartnoma->loan_id);
            if ($tekshiruv['success']) {
                return back()->with('xato',
                    "AutoPay xatosi: {$natija['error']} — lekin shartnoma AutoPay'da hali ham mavjud (qarz: " .
                    number_format($tekshiruv['result']['current_debt_som'] ?? 0, 0, '.', ' ') .
                    " so'm). Qayta urinib ko'ring."
                );
            }
        }

        $shartnoma->update(['holat' => 'ochirilgan', 'auto_yoqilgan' => false]);

        return back()->with('muvaffaqiyat', $natija['success']
            ? "Shartnoma AutoPay'dan o'chirildi."
            : "Shartnoma AutoPay'da allaqachon topilmadi — bizning tomonda ham sinxronlandi.");
    }

    /**
     * Bir nechta tanlangan shartnomani BITTA so'rovda (contract.bulk.delete)
     * AutoPay'dan o'chirish. Amal atomik (yoki hammasi o'chadi, yoki
     * hech biri) va AutoPay talabiga ko'ra o'chirishdan oldin har bir
     * shartnomaning "auto" rejimi o'chirilgan bo'lishi kerak — shuning
     * uchun avval avtoToggle(false) chaqiriladi.
     */
    public function ochirishBulk(Request $request)
    {
        $ids = array_filter((array) $request->input('shartnoma_ids', []));
        if (!$ids) {
            return back()->with('xato', "Hech qanday shartnoma tanlanmagan.");
        }

        $shartnomalar = AutopayShartnoma::whereIn('id', $ids)->whereNotNull('loan_id')->get();
        if ($shartnomalar->isEmpty()) {
            return back()->with('xato', "O'chirish uchun shartnoma topilmadi.");
        }

        $loanIds = $shartnomalar->pluck('loan_id')->all();

        foreach ($loanIds as $loanId) {
            $this->autoPay->avtoToggle($loanId, false);
        }

        $natija = $this->autoPay->shartnomalarniOmmaviyOchirish($loanIds);

        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        AutopayShartnoma::whereIn('loan_id', $loanIds)
            ->update(['holat' => 'ochirilgan', 'auto_yoqilgan' => false]);

        return back()->with('muvaffaqiyat', count($loanIds) . " ta shartnoma AutoPay'dan o'chirildi.");
    }

    /**
     * Barcha faol (reg_kredit_id bog'langan) shartnomalarning qarzini
     * bizning bazadagi joriy qoldiq_qarz bilan BITTA so'rovda (chunklab,
     * 250 tadan) moslashtirish — contract.bulk.update.
     */
    public function qarzlarniOmmaviySinxronlash()
    {
        $shartnomalar = AutopayShartnoma::with('kredit')
            ->where('holat', 'faol')
            ->whereNotNull('reg_kredit_id')
            ->whereNotNull('loan_id')
            ->get()
            ->filter(fn($s) => $s->kredit);

        if ($shartnomalar->isEmpty()) {
            return back()->with('xato', "Sinxronlash uchun faol shartnoma topilmadi.");
        }

        $muvaffaqiyat = 0;
        $xatolar      = [];

        foreach ($shartnomalar->chunk(250) as $chunk) {
            $royxat = $chunk->map(fn($s) => [
                'loan_id'  => $s->loan_id,
                'debt_som' => $s->kredit->qoldiq_qarz,
            ])->values()->all();

            $natija = $this->autoPay->qarzlarniOmmaviyYangilash($royxat);

            if (!$natija['success']) {
                $xatolar[] = $natija['error'];
                continue;
            }

            $result   = $natija['result'] ?? [];
            $updated  = $result['updated'] ?? array_column($royxat, 'loan_id');
            $topilmadi = $result['not_found'] ?? [];

            $muvaffaqiyat += count($updated);
            if ($topilmadi) {
                $xatolar[] = 'AutoPay topmadi: ' . implode(', ', array_slice($topilmadi, 0, 5));
            }

            foreach ($chunk as $s) {
                if (in_array($s->loan_id, $updated, true)) {
                    $s->update(['oxirgi_debt' => $s->kredit->qoldiq_qarz]);
                }
            }
        }

        $jami  = $shartnomalar->count();
        $xabar = "{$muvaffaqiyat}/{$jami} ta shartnoma qarzi sinxronlandi.";
        if ($xatolar) {
            $xabar .= ' ' . implode('; ', array_slice($xatolar, 0, 5));
        }

        return back()->with($xatolar ? 'xato' : 'muvaffaqiyat', $xabar);
    }

    /**
     * AutoPay hisobidagi BARCHA shartnomalarni (bizning tizim yaratganlari ham,
     * qo'lda AutoPay kabinetida yaratilganlari ham) bazamizga tortadi. Mos
     * RegKredit topilsa (loan_id "NP-{filial}-{kredit_id}...") — manba=api,
     * darhol bog'langan holda saqlanadi. Aks holda — manba=qolda, reg_kredit_id
     * bo'sh qoladi (keyinroq "Shartnomaga biriktirish" orqali bog'lanadi) va
     * hech qanday to'lov avtomatik yozilmaydi.
     *
     * Shundan so'ng barcha tranzaksiyalarni ham (hisob bo'yicha, filtrsiz)
     * tortib, faqat bog'langan shartnomalar uchun to'lov yozadi.
     */
    public function sinxronlash()
    {
        if (!$this->autoPay->yoqilganmi()) {
            return back()->with('xato', "AutoPay hali sozlamalarda yoqilmagan.");
        }

        // AutoPay hisobida yuzlab kontrakt/minglab tranzaksiya bo'lishi mumkin —
        // sahifalab olish PHP-FPM'ning standart 30 soniyalik limitidan oshib
        // ketishi mumkin, shuning uchun shu amal uchun vaqtni kengaytiramiz.
        set_time_limit(240);

        $yangiApi = 0;
        $yangiQolda = 0;
        $yangilangan = 0;
        $topilganLoanIdlar = [];
        $page = 1;
        $lastPage = 1;

        do {
            $natija = $this->autoPay->barchaShartnomalarniOl(100, $page);
            if (!$natija['success']) {
                return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
            }

            foreach (($natija['result']['data'] ?? []) as $c) {
                $loanId = $c['loan_id'] ?? '';
                if (!$loanId) {
                    continue;
                }
                $topilganLoanIdlar[] = $loanId;

                $shartnoma = AutopayShartnoma::where('loan_id', $loanId)->first();
                $mavjudEdi = (bool) $shartnoma;

                if (!$shartnoma) {
                    $kredit = null;
                    if (preg_match('/^NP-\d+-(\d+)/', $loanId, $m)) {
                        $kredit = RegKredit::find((int) $m[1]);
                    }
                    $shartnoma = new AutopayShartnoma([
                        'reg_kredit_id' => $kredit?->id,
                        'mijoz_id'      => $kredit?->mijoz_id,
                        'manba'         => $kredit ? 'api' : 'qolda',
                    ]);
                }

                // reg_kredit_id/mijoz_id/manba — faqat yaratilishda belgilanadi,
                // keyingi sinxronlashlarda qo'lda biriktirilgan bog'lanish
                // qayta yozilib ketmasin.
                $shartnoma->fill([
                    'loan_id'         => $loanId,
                    'pinfl'           => $c['pinfl'] ?? $shartnoma->pinfl,
                    'holat'           => ($c['auto'] ?? false) ? 'faol' : 'toxtatilgan',
                    'auto_yoqilgan'   => (bool) ($c['auto'] ?? false),
                    'oxirgi_debt'     => round((($c['current_debt'] ?? 0)) / 100, 2),
                    'xato_matni'      => null,
                    'yuborgan_id'     => $shartnoma->yuborgan_id ?: Auth::id(),
                    'yuborilgan_vaqt' => $shartnoma->yuborilgan_vaqt ?: ($c['created_at'] ?? now()),
                ])->save();

                // "Qolda" (mijozga bog'lanmagan) yozuvlar uchun — pinfl bo'yicha
                // lokal mijozlar bazasida mos keluvchi yozuv paydo bo'lgan bo'lsa,
                // shu yerda avtomatik biriktiramiz (keyingi sinxronlashlarda ham
                // ishlaydi, mijoz keyinroq tizimga kiritilgan bo'lsa ham topiladi).
                if (!$shartnoma->mijoz_id && $shartnoma->pinfl) {
                    $mosMijoz = \App\Models\Mijoz::where('pinfl', $shartnoma->pinfl)->first();
                    if ($mosMijoz) {
                        $shartnoma->mijoz_id = $mosMijoz->id;
                        $shartnoma->save();
                    }
                }

                if (!$mavjudEdi) {
                    $shartnoma->manba === 'qolda' ? $yangiQolda++ : $yangiApi++;
                } else {
                    $yangilangan++;
                }
            }

            $lastPage = $natija['result']['last_page'] ?? 1;
            $page++;
        } while ($page <= $lastPage && $page <= 30);

        // Barcha sahifalar to'liq skanerlangan bo'lsa — bizning bazada "faol"/"toxtatilgan"
        // deb turgan, lekin AutoPay ro'yxatida endi umuman ko'rinmayotgan shartnomalarni
        // (AutoPay kabinetidan o'chirilgan bo'lishi mumkin) "o'chirilgan" deb belgilaymiz.
        $ochirilgan = 0;
        $tolaSkanerlandi = ($page - 1) >= $lastPage;

        if ($tolaSkanerlandi) {
            $query = AutopayShartnoma::whereIn('holat', ['faol', 'toxtatilgan']);
            if ($topilganLoanIdlar) {
                $query->whereNotIn('loan_id', $topilganLoanIdlar);
            }
            foreach ($query->get() as $sh) {
                $sh->update(['holat' => 'ochirilgan', 'auto_yoqilgan' => false]);
                $ochirilgan++;
            }
        }

        // Endi barcha tranzaksiyalarni (hisob bo'yicha, filtrsiz) tortib,
        // faqat bog'langan (reg_kredit_id mavjud) shartnomalar uchun to'lov yozamiz.
        $tulovService = app(TulovService::class);
        $yangiTolov = 0;
        $tp = 1;
        $tLastPage = 1;
        do {
            $tNatija = $this->autoPay->barchaTranzaksiyalarniOl(100, $tp);
            if (!$tNatija['success']) {
                break;
            }
            foreach (($tNatija['result']['data'] ?? []) as $t) {
                $tLoanId = $t['loan_id'] ?? null;
                if (!$tLoanId) {
                    continue;
                }
                $sh = AutopayShartnoma::where('loan_id', $tLoanId)->first();
                if (!$sh) {
                    continue;
                }
                $yozuv = $this->autoPay->tranzaksiyaniQayta($sh, $t, $tulovService);
                if ($yozuv && $yozuv->tulov_id) {
                    $yangiTolov++;
                }
            }
            $tLastPage = $tNatija['result']['last_page'] ?? 1;
            $tp++;
        } while ($tp <= $tLastPage && $tp <= 50);

        $xabar = "Sinxronlandi: {$yangiApi} ta yangi (API), {$yangiQolda} ta yangi (qo'lda, biriktirilmagan), "
            . "{$yangilangan} ta yangilandi, {$ochirilgan} ta o'chirilgan deb belgilandi, "
            . "{$yangiTolov} ta yangi to'lov yozildi.";
        if (!$tolaSkanerlandi) {
            $xabar .= " Diqqat: AutoPay hisobida juda ko'p kontrakt bor edi, barchasi to'liq skanerlanmadi.";
        }

        return back()->with('muvaffaqiyat', $xabar);
    }

    /**
     * Hech qachon bizning kredit tizimimizga biriktirilmagan (manba=qolda,
     * reg_kredit_id=null) shartnomalarni va ularning tranzaksiyalarini
     * bazadan o'chiradi — bu faqat bizning ICHKI kuzatuv yozuvlarimiz,
     * AutoPay'ning o'zidagi haqiqiy kontraktga tegmaydi. Keyingi safar
     * "Sinxronlash" bosilsa, hali ham AutoPay'da mavjud bo'lsa, qayta paydo bo'ladi.
     */
    public function tozalash()
    {
        $shartnomalar = AutopayShartnoma::where('manba', 'qolda')->whereNull('reg_kredit_id')->get();
        $soni = $shartnomalar->count();

        foreach ($shartnomalar as $sh) {
            $sh->delete(); // autopay_tranzaksiyalar FK onDelete('cascade') orqali ham o'chadi
        }

        return back()->with('muvaffaqiyat', "{$soni} ta bog'lanmagan (qo'lda, biriktirilmagan) shartnoma va ularning tranzaksiyalari bazadan tozalandi.");
    }

    /**
     * Tranzaksiyalar tabidan chaqiriladi — tanlangan davr (bugun/shu oy/o'tgan
     * oy/barchasi) bo'yicha AutoPay'dan tranzaksiyalarni tortadi. AutoPay API
     * sana oralig'ini emas, faqat aniq bitta sanani qabul qilgani uchun,
     * "barchasi"dan tashqari davrlar kun-kun bo'yicha so'raladi — bu to'liq
     * (1000+ yozuvli) skanerlashdan ancha tezroq.
     */
    public function sinxronlashTranzaksiya(Request $request)
    {
        if (!$this->autoPay->yoqilganmi()) {
            return back()->with('xato', "AutoPay hali sozlamalarda yoqilmagan.");
        }
        set_time_limit(240);

        $davr = $request->get('davr', 'bugun');
        $oraliq = match ($davr) {
            'bugun'    => [now()->startOfDay(), now()->endOfDay()],
            'shu_oy'   => [now()->startOfMonth(), now()->endOfMonth()],
            'otgan_oy' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            default    => null, // barchasi
        };

        $tulovService = app(TulovService::class);
        $jamiTopilgan = 0;
        $yangiTolov   = 0;
        $bogsiz       = 0;

        $birTranzaksiyaniIshla = function (array $t) use ($tulovService, &$jamiTopilgan, &$yangiTolov, &$bogsiz) {
            $jamiTopilgan++;
            $loanId = $t['loan_id'] ?? null;
            $sh = $loanId ? AutopayShartnoma::where('loan_id', $loanId)->first() : null;
            if (!$sh) {
                $bogsiz++;
                return;
            }
            $yozuv = $this->autoPay->tranzaksiyaniQayta($sh, $t, $tulovService);
            if ($yozuv && $yozuv->tulov_id) {
                $yangiTolov++;
            }
        };

        if ($oraliq) {
            $sana = $oraliq[0]->copy();
            while ($sana->lte($oraliq[1])) {
                $page = 1;
                $lastPage = 1;
                do {
                    $natija = $this->autoPay->tranzaksiyalarSanaBoyicha($sana->toDateString(), 100, $page);
                    if (!$natija['success']) {
                        break;
                    }
                    foreach (($natija['result']['data'] ?? []) as $t) {
                        $birTranzaksiyaniIshla($t);
                    }
                    $lastPage = $natija['result']['last_page'] ?? 1;
                    $page++;
                } while ($page <= $lastPage && $page <= 10);
                $sana->addDay();
            }
        } else {
            $page = 1;
            $lastPage = 1;
            do {
                $natija = $this->autoPay->barchaTranzaksiyalarniOl(100, $page);
                if (!$natija['success']) {
                    return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
                }
                foreach (($natija['result']['data'] ?? []) as $t) {
                    $birTranzaksiyaniIshla($t);
                }
                $lastPage = $natija['result']['last_page'] ?? 1;
                $page++;
            } while ($page <= $lastPage && $page <= 50);
        }

        return back()->with('muvaffaqiyat',
            "Tranzaksiyalar sinxronlandi: {$jamiTopilgan} ta topildi, {$yangiTolov} ta yangi to'lov yozildi, "
            . "{$bogsiz} ta bizning tizimga mos shartnoma topilmagani uchun o'tkazib yuborildi."
        );
    }

    /** AJAX: shartnoma raqami yoki mijoz ismi bo'yicha kreditlarni qidirish (biriktirish modali uchun). */
    public function kreditQidir(Request $request)
    {
        $q = trim((string) $request->get('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $kreditlar = RegKredit::with('mijoz')
            ->whereDoesntHave('autopayShartnoma')
            ->where(function ($qq) use ($q) {
                $qq->where('shartnoma_raqam', 'like', "%{$q}%")
                   ->orWhereHas('mijoz', fn($m) => $m->where('ism', 'like', "%{$q}%")
                                                      ->orWhere('familiya', 'like', "%{$q}%"));
            })
            ->limit(20)
            ->get()
            ->map(fn($k) => [
                'id'    => $k->id,
                'label' => "{$k->shartnoma_raqam} — {$k->mijoz?->familiya} {$k->mijoz?->ism} (qoldiq: " . number_format($k->qoldiq_qarz, 0, '.', ' ') . " so'm)",
            ]);

        return response()->json($kreditlar);
    }

    /**
     * Qo'lda (AutoPay kabinetida yaratilgan) shartnomani bizning kredit
     * tizimimizdagi biror RegKredit'ga biriktiradi. Biriktirilgach, shu
     * shartnoma bo'yicha allaqachon sinxronlangan (lekin to'lov sifatida
     * yozilmagan) tranzaksiyalar retroaktiv ravishda to'lov sifatida qayta ishlanadi.
     */
    public function biriktirish(Request $request, AutopayShartnoma $shartnoma)
    {
        $validated = $request->validate(['kredit_id' => 'required|exists:reg_kredit,id']);

        if (AutopayShartnoma::where('reg_kredit_id', $validated['kredit_id'])->where('id', '!=', $shartnoma->id)->exists()) {
            return back()->with('xato', "Bu shartnomaga allaqachon boshqa AutoPay kontrakti biriktirilgan.");
        }

        $kredit = RegKredit::findOrFail($validated['kredit_id']);

        $shartnoma->update([
            'reg_kredit_id' => $kredit->id,
            'mijoz_id'      => $kredit->mijoz_id,
        ]);

        // Retroaktiv: shu shartnomaga tegishli, hali to'lov sifatida yozilmagan
        // muvaffaqiyatli tranzaksiyalarni endi to'lov sifatida qayta ishlaymiz.
        $tulovService = app(TulovService::class);
        $yangiTolov = 0;
        $shartnoma->refresh();

        foreach ($shartnoma->tranzaksiyalar()->whereNull('tulov_id')->where('holat', AutoPayService::HOLAT_MUVAFFAQIYATLI)->get() as $tr) {
            $tulov = $tulovService->tulovQabul($kredit, [
                'tulov_turi_id' => 143,
                'summa'         => $tr->summa,
                'tolov_sana'    => optional($tr->sana)->toDateString() ?? now()->toDateString(),
                'izoh'          => "AutoPay orqali avtomatik yechildi (ext: {$tr->ext_id})",
                'xodim_id'      => AutoPayService::systemXodim()->id,
            ]);
            $tr->update(['tulov_id' => $tulov->id]);
            $yangiTolov++;
        }

        return back()->with('muvaffaqiyat',
            "Shartnoma {$kredit->shartnoma_raqam} ga biriktirildi." . ($yangiTolov ? " {$yangiTolov} ta eski tranzaksiya to'lov sifatida yozildi." : '')
        );
    }

    /** Shartnomaning AutoPay'dagi qarz/izoh maydonlarini qo'lda tahrirlash. */
    public function tahrirlash(Request $request, AutopayShartnoma $shartnoma)
    {
        $validated = $request->validate([
            'debt' => 'required|numeric|min:0',
            'info' => 'nullable|string|max:255',
        ]);

        $natija = $this->autoPay->shartnomaYangilash($shartnoma->loan_id, (float) $validated['debt'], $validated['info'] ?? null);
        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        $shartnoma->update(['oxirgi_debt' => $validated['debt']]);

        return back()->with('muvaffaqiyat', "Shartnoma AutoPay'da yangilandi.");
    }

    /**
     * Muvaffaqiyatli tranzaksiyani bekor qilish — AutoPay'da transaction.cancel
     * chaqiriladi, muvaffaqiyatli bo'lsa bizning yozilgan to'lovni ham
     * TulovController::destroy() bilan bir xil mantiqda qaytaramiz.
     */
    public function tranzaksiyaBekorQil(AutopayTranzaksiya $tranzaksiya, TulovService $tulovService)
    {
        if ($tranzaksiya->holat !== AutoPayService::HOLAT_MUVAFFAQIYATLI || !$tranzaksiya->tulov_id) {
            return back()->with('xato', "Bu tranzaksiyani bekor qilib bo'lmaydi (to'lov bilan bog'liq emas).");
        }

        $natija = $this->autoPay->tranzaksiyaniBekorQil($tranzaksiya->ext_id);
        if (!$natija['success']) {
            return back()->with('xato', "AutoPay xatosi: {$natija['error']}");
        }

        $tulov  = $tranzaksiya->tulov;
        $kredit = $tranzaksiya->shartnoma?->kredit;

        if ($tulov && $kredit) {
            $summa = (float) $tulov->summa;
            $tulovId = $tulov->id;

            $tulov->delete();
            $tulovService->pulOqiminiOchir('tulov', $tulovId);

            $kredit->decrement('tolov_qilingan', $summa);
            $kredit->increment('qoldiq_qarz', $summa);

            $kredit->refresh();
            if ($kredit->qoldiq_qarz > 0 && $kredit->holat === 'yopilgan') {
                $kredit->update(['holat' => 'faol']);
            }
        }

        $tranzaksiya->update(['holat' => AutoPayService::HOLAT_BEKOR_QILINGAN, 'tulov_id' => null]);

        return back()->with('muvaffaqiyat', "Tranzaksiya bekor qilindi, to'lov qaytarildi.");
    }

    /** AutoPay kredensiallarini saqlash (admin sozlamalar sahifasidan). */
    public function sozlamalarSaqla(Request $request)
    {
        $data = $request->only(['merchant_id', 'token']);
        $data['yoqilgan'] = $request->boolean('yoqilgan') ? '1' : '0';
        foreach (['scoring_yoqilgan', 'monitoring_yoqilgan', 'processing_yoqilgan', 'egov_yoqilgan'] as $kalit) {
            $data[$kalit] = $request->boolean($kalit) ? '1' : '0';
        }
        NotificationSetting::setChannel('autopay', $data);
        return back()->with('muvaffaqiyat', 'AutoPay sozlamalari saqlandi.');
    }

    /** Admin tugmasi — post-payment webhook manzilini AutoPay'da ro'yxatdan o'tkazish/yoqish. */
    public function webhookUlash(Request $request)
    {
        $host   = $request->input('host', url('/autopay/webhook'));
        $natija = $this->autoPay->webhookSozla($host, true);

        if (!$natija['success']) {
            return back()->with('xato', "Webhook ulanmadi: {$natija['error']}");
        }
        return back()->with('muvaffaqiyat', "Webhook ulandi: {$host}");
    }

    /** Admin tugmasi — prepayment verification manzilini AutoPay'da ro'yxatdan o'tkazish/yoqish. */
    public function verificationUlash(Request $request)
    {
        $host   = $request->input('host', url('/autopay/verify'));
        $delay  = (int) $request->input('delay', 10);
        $natija = $this->autoPay->verificationSozla($host, $delay, true);

        if (!$natija['success']) {
            return back()->with('xato', "Verification ulanmadi: {$natija['error']}");
        }
        return back()->with('muvaffaqiyat', "Prepayment verification ulandi: {$host}");
    }

    /**
     * AutoPay webhook qabuli — muvaffaqiyatli tranzaksiyadan so'ng AutoPay shu
     * manzilga tranzaksiya obyektini yuboradi (Authorization: Bearer {token}).
     * Har doim 200 qaytaramiz — muvaffaqiyatsiz bo'lsa ham AutoPaySync (soatlik
     * cron) zaxira sifatida keyinroq tuzatadi.
     */
    public function webhook(Request $request, TulovService $tulovService)
    {
        if ($request->bearerToken() !== $this->autoPay->webhookToken()) {
            return response()->json(['error' => 'Ruxsat yo\'q'], 401);
        }

        $tranz  = $request->all();
        $loanId = $tranz['loan_id'] ?? null;

        $shartnoma = $loanId ? AutopayShartnoma::where('loan_id', $loanId)->with('kredit')->first() : null;
        if (!$shartnoma) {
            Log::warning('AutoPay webhook: shartnoma topilmadi', ['loan_id' => $loanId]);
            return response()->json(['status' => 'ok'], 200);
        }

        $this->autoPay->tranzaksiyaniQayta($shartnoma, $tranz, $tulovService);

        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * AutoPay prepayment verification — har bir yechishdan oldin AutoPay
     * joriy qarzni shu manzildan so'raydi. Javob formati qat'iy: {code, debt}.
     * code: 100 = yechish mumkin (debt majburiy), 101 = shartnoma topilmadi/yopiq,
     * 102 = band (boshqa jarayon bilan).
     */
    public function verify(Request $request)
    {
        if ($request->bearerToken() !== $this->autoPay->webhookToken()) {
            return response()->json(['code' => 101], 200);
        }

        $loanId = $request->input('loan_id');
        $shartnoma = $loanId ? AutopayShartnoma::where('loan_id', $loanId)->faol()->with('kredit')->first() : null;

        if (!$shartnoma || !$shartnoma->kredit) {
            return response()->json(['code' => 101], 200);
        }

        $debtTiyin = (int) round($shartnoma->kredit->kechikkanSummaHisobla() * 100);

        return response()->json(['code' => 100, 'debt' => $debtTiyin], 200);
    }
}
