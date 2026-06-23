<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegKreditRequest;
use App\Models\Filial;
use App\Models\Mijoz;
use App\Models\RegKredit;
use App\Models\Foydalanuvchi;
use App\Models\TulovTuri;
use App\Models\Tovar;
use App\Models\TovarGuruh;
use App\Models\TovarKatalog;
use App\Models\OmbordanChiqim;
use App\Models\ChiqimTafsilot;
use App\Services\TulovService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PochtaLog;
use App\Models\PochtaShablon;
use App\Services\HybridPochtaService;
use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Models\Sozlama;
use App\Services\Notification\SmsService;

class RegKreditController extends Controller
{
    public function __construct(private TulovService $tulovService, private SmsService $smsService) {}

    /** Shartnomalar ro'yxati */
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin()
            ? ($request->filial_id ?: null)
            : $user->filial_id;

        $query = RegKredit::with(['mijoz', 'filial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->holat, fn($q) => $q->where('holat', $request->holat))
            ->when($request->qidiruv, fn($q) => $q->qidirish($request->qidiruv));

        // Ajax qidiruv
        if ($request->expectsJson()) {
            return response()->json(
                $query->limit(10)->get(['id', 'shartnoma_raqam', 'mijoz_id'])
            );
        }

        $kreditlar = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        return view('kredit.index', compact('kreditlar', 'filiallar', 'filialId'));
    }

    /** Yangi shartnoma formasi */
    public function create(Request $request)
    {
        $user      = Auth::user();
        $filiallar = $user->isAdmin()
            ? Filial::faol()->get()
            : Filial::where('id', $user->filial_id)->get();

        // URL'dan mijoz tanlangan bo'lsa
        $mijoz = $request->mijoz_id ? Mijoz::find($request->mijoz_id) : null;

        if ($mijoz && $mijoz->shartnomaTaqiqlanganmi()) {
            return redirect()->route('mijozlar.show', $mijoz)
                ->with('xato', "Mijoz holati «{$mijoz->holat_nomi}» — yangi shartnoma tuzish taqiqlangan.");
        }

        // Ombordan tovarlar (qoldig'i bor, modal uchun guruh bo'yicha)
        $tovarGuruhlar = TovarGuruh::with([
            'tovarlar' => fn($q) => $q->faol()->where('qoldiq', '>', 0)->orderByDesc('created_at')->take(30)->select(['id','guruh_id','nomi','qoldiq','sotish_narx','birlik','created_at'])
        ])->whereHas('tovarlar', fn($q) => $q->faol()->where('qoldiq', '>', 0))
          ->orderBy('nomi')->get(['id','nomi']);

        return view('kredit.create', compact('filiallar', 'mijoz', 'tovarGuruhlar'));
    }

    /** Shartnomani saqlash */
    public function store(RegKreditRequest $request)
    {
        $mijoz = Mijoz::findOrFail($request->validated()['mijoz_id']);
        if ($mijoz->shartnomaTaqiqlanganmi()) {
            return back()->withErrors([
                'mijoz_id' => "Mijoz holati «{$mijoz->holat_nomi}» — yangi shartnoma tuzish taqiqlangan.",
            ])->withInput();
        }

        return DB::transaction(function () use ($request) {
            $data    = $request->validated();
            $user    = Auth::user();
            $filial  = Filial::findOrFail($data['filial_id']);

            // Shartnoma raqamini avtomatik yaratish
            $yil    = now()->year;
            $raqam  = RegKredit::yangiRaqamYaratish($filial, $yil);

            // Shartnoma va kafillik hujjat matnlariga joriy faol qo'shimcha band
            // versiyasini "suratga olamiz" (snapshot) — admin keyinroq matnni
            // o'zgartirsa ham, bu shartnomaning hujjati o'zgarmay qoladi.
            $shartnomaBand = \App\Models\HujjatBand::faolVersiya('shartnoma');
            $kafillikBand  = \App\Models\HujjatBand::faolVersiya('kafillik');

            // Shartnomani yaratish
            $kredit = RegKredit::create([
                ...$data,
                'shartnoma_raqam'           => $raqam,
                'xodim_id'                  => $user->id,
                'kredit_summa'              => $data['kredit_summa'],
                'qoldiq_qarz'               => $data['kredit_summa'],
                'oylik_tolov_miqdori'       => $data['oylik_tolov_miqdori'],
                'tolov_qilingan'            => 0,
                'holat'                     => 'faol',
                'shartnoma_band_versiya_id' => $shartnomaBand?->id,
                'kafillik_band_versiya_id'  => $kafillikBand?->id,
            ]);

            // Tovarlarni saqlash
            foreach ($data['tovarlar'] as $tovar) {
                Tovar::create([
                    'reg_kredit_id'   => $kredit->id,
                    'nomi'            => $tovar['nomi'],
                    'soni'            => $tovar['soni'],
                    'narx'            => $tovar['narx'],
                    'jami_narx'       => $tovar['soni'] * $tovar['narx'],
                    'barkod'          => $tovar['barkod'] ?? null,
                    'tovar_katalog_id'=> !empty($tovar['tovar_katalog_id']) ? (int)$tovar['tovar_katalog_id'] : null,
                ]);
            }

            // Ombor: qoldiqi bor tovarlar uchun ombordan chiqim va qoldiq decrement
            $katalogItems = collect($data['tovarlar'])
                ->filter(fn($t) => !empty($t['tovar_katalog_id']));

            if ($katalogItems->isNotEmpty()) {
                // Qoldiq tekshiruvi
                foreach ($katalogItems as $t) {
                    $tk = TovarKatalog::find((int)$t['tovar_katalog_id']);
                    if ($tk && $tk->qoldiq < $t['soni']) {
                        throw new \Illuminate\Validation\ValidationException(
                            validator([], []),
                            back()->withErrors(["«{$tk->nomi}»: omborda faqat {$tk->qoldiq} {$tk->birlik} bor."])->withInput()
                        );
                    }
                }

                $chiqimJami = $katalogItems->sum(fn($t) => $t['soni'] * $t['narx']);

                $chiqim = OmbordanChiqim::create([
                    'filial_id'    => $kredit->filial_id,
                    'ombor_id'     => 1,
                    'shartnoma_id' => $kredit->id,
                    'xodim_id'     => $user->id,
                    'sana'         => $kredit->boshlanish_sana,
                    'sabab'        => 'nasiya_sotish',
                    'umumiy_summa' => $chiqimJami,
                    'izoh'         => "Nasiya shartnoma #{$kredit->shartnoma_raqam}",
                    'holat'        => 'tasdiqlangan',
                ]);

                foreach ($katalogItems as $t) {
                    ChiqimTafsilot::create([
                        'chiqim_id'  => $chiqim->id,
                        'tovar_id'   => (int)$t['tovar_katalog_id'],
                        'miqdor'     => $t['soni'],
                        'narx'       => $t['narx'],
                        'jami_summa' => $t['soni'] * $t['narx'],
                    ]);
                    TovarKatalog::find((int)$t['tovar_katalog_id'])->decrement('qoldiq', $t['soni']);
                }
            }

            // To'lov grafikini yaratish (xodim qo'lda sozlagan sanalar bo'lsa ulardan, aks holda avtomatik)
            $this->grafikYarat($kredit, $data['grafik'] ?? null);

            // Boshlang'ich versiya
            $this->tulovService->versiyaSaqlash($kredit, 'Yangi shartnoma yaratildi', []);

            return redirect()
                ->route('kreditlar.show', $kredit)
                ->with('muvaffaqiyat', "Shartnoma {$raqam} muvaffaqiyatli yaratildi.");
        });
    }

    /** Shartnoma batafsil ko'rish (tablar bilan) */
    public function show(RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $kredit->load([
            'mijoz.filial',
            'mijoz.telefonlar',
            'filial',
            'xodim',
            'kafil',
            'tovarlar',
            'grafik',
            'tulovlar.tulovTuri',
            'tulovlar.xodim',
            'oldinTulovlar.tulovTuri',
            'oldinTulovlar.xodim',
            'versiyalar.xodim',
        ]);

        $tulovTurlari = TulovTuri::faol()->get();
        $xodimlar     = Foydalanuvchi::faol()->orderBy('ism_familiya')->get(['id','ism_familiya','filial_id']);
        $filiallar    = Filial::faol()->orderBy('nomi')->get(['id','nomi','kod']);

        // Hybrid Pochta
        $hp_yoqilgan = \App\Models\Sozlama::ol('hybrid_pochta_yoqilgan', '0') === '1';
        $pochta_shablonlar = $hp_yoqilgan
            ? \App\Models\PochtaShablon::where('holat', 'faol')->orderBy('sort_order')->get()
            : collect();
        $pochta_loglar = \App\Models\PochtaLog::where('reg_kredit_id', $kredit->id)
            ->with('shablon')->latest()->take(20)->get();

        // Blade da app() chaqirishdan qochish uchun
        $kredit_vars = $hp_yoqilgan
            ? app(\App\Services\HybridPochtaService::class)->buildVars($kredit)
            : [];

        // SMS yuborish tabi
        $sms_shablonlar = NotificationTemplate::faol()->channel('sms')->orderBy('name')->get();
        $sms_loglar = NotificationLog::where('channel', 'sms')->where('contract_id', $kredit->id)
            ->with('template')->latest()->take(20)->get();

        $bugun = today();
        $kechikkanQatorlar = $kredit->grafik
            ->whereIn('holat', ['muddati_otgan', 'qisman'])
            ->filter(fn($g) => $g->tolov_sana && \Carbon\Carbon::parse($g->tolov_sana)->lt($bugun));
        $overdueKun = $kechikkanQatorlar->count()
            ? $kechikkanQatorlar->min(fn($g) => $bugun->diffInDays(\Carbon\Carbon::parse($g->tolov_sana)))
            : 0;
        $overdueSumma = $kechikkanQatorlar->sum(fn($g) => $g->tolov_summa - ($g->tolangan_summa ?? 0));
        $kelayotganGrafik = $kredit->grafik->where('holat', 'tolanmagan')->sortBy('tolov_sana')->first();

        $sms_vars = [
            'client_name'      => trim(($kredit->mijoz->familiya ?? '') . ' ' . ($kredit->mijoz->ism ?? '')),
            'contract_number'  => $kredit->shartnoma_raqam,
            'branch_name'      => $kredit->filial?->nomi ?? '',
            'payment_date'     => $kelayotganGrafik ? \Carbon\Carbon::parse($kelayotganGrafik->tolov_sana)->format('d.m.Y') : '',
            'monthly_payment'  => number_format((float) $kredit->oylik_tolov_miqdori, 0, '.', ' '),
            'overdue_days'     => (int) $overdueKun,
            'overdue_amount'   => number_format((float) $overdueSumma, 0, '.', ' '),
            'total_debt'       => number_format((float) $kredit->qoldiq_qarz, 0, '.', ' '),
            'paid_amount'      => number_format((float) $kredit->tolov_qilingan, 0, '.', ' '),
            'remaining_amount' => number_format((float) $kredit->qoldiq_qarz, 0, '.', ' '),
            'company_name'     => Sozlama::ol('brand_nomi', 'AdsMarket'),
            'manager_phone'    => $kredit->xodim->telefon ?? '',
        ];
        $sms_shablon_matnlari = $sms_shablonlar->mapWithKeys(fn($t) => [$t->id => $t->render($sms_vars)]);

        // Shu shartnoma uchun shablonlar bo'yicha so'nggi 24 soatda yuborilgan SMS vaqti (cooldown)
        $sms_oxirgi_24soat = NotificationLog::where('channel', 'sms')
            ->where('contract_id', $kredit->id)
            ->whereIn('status', ['sent', 'test'])
            ->where('created_at', '>=', now()->subHours(24))
            ->orderByDesc('created_at')
            ->get(['template_id', 'created_at'])
            ->whereNotNull('template_id')
            ->groupBy('template_id')
            ->map(fn($g) => $g->first()->created_at->toIso8601String());

        return view('kredit.show', compact(
            'kredit', 'tulovTurlari', 'xodimlar', 'filiallar', 'hp_yoqilgan', 'pochta_shablonlar', 'pochta_loglar', 'kredit_vars',
            'sms_shablonlar', 'sms_loglar', 'sms_shablon_matnlari', 'sms_oxirgi_24soat'
        ));
    }

    /** Shartnoma sahifasidan SMS yuborish (AJAX) */
    public function smsYubor(Request $request, RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $request->validate([
            'phone'       => 'required|string',
            'message'     => 'required|string|min:5|max:800',
            'template_id' => 'nullable|exists:notification_templates,id',
        ]);

        $log = $this->smsService->sendSingle(
            $request->phone,
            $request->message,
            $kredit->mijoz_id,
            $kredit->id,
            $request->template_id ?: null,
            null,
            'manual'
        );
        $log->load('template');

        $statusText = match ($log->status) {
            'sent'    => 'Yuborildi',
            'test'    => 'Test rejimda yuborildi',
            'skipped' => 'Bekor qilindi',
            'failed'  => 'Xato',
            default   => $log->status,
        };

        return response()->json([
            'ok'           => in_array($log->status, ['sent', 'test']),
            'status'       => $log->status,
            'status_text'  => $statusText,
            'status_rang'  => $log->status_rangi,
            'error'        => $log->error_message,
            'provider'     => $log->provider,
            'sana'         => $log->created_at->format('d.m.Y H:i'),
            'shablon'      => $log->template?->name ?? '—',
            'telefon'      => $log->phone,
        ]);
    }

    /** Shartnoma tahrirlash formasi */
    public function edit(RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        if (!in_array(Auth::user()->rol, ['admin', 'menejer'])) {
            abort(403);
        }

        $kredit->load(['mijoz', 'kafil', 'tovarlar', 'grafik']);

        $filiallar = Auth::user()->isAdmin()
            ? Filial::faol()->get()
            : Filial::where('id', $kredit->filial_id)->get();

        $tovarGuruhlar = TovarGuruh::with([
            'tovarlar' => fn($q) => $q->faol()->where('qoldiq', '>', 0)->orderByDesc('created_at')->take(30)->select(['id','guruh_id','nomi','qoldiq','sotish_narx','birlik','created_at'])
        ])->whereHas('tovarlar', fn($q) => $q->faol()->where('qoldiq', '>', 0))
          ->orderBy('nomi')->get(['id','nomi']);

        return view('kredit.edit', compact('kredit', 'filiallar', 'tovarGuruhlar'));
    }

    /** Shartnomani yangilash */
    public function update(RegKreditRequest $request, RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        if (!in_array(Auth::user()->rol, ['admin', 'menejer'])) {
            abort(403);
        }

        return DB::transaction(function () use ($request, $kredit) {
            $data = $request->validated();

            // Grafik qulfini hisoblash uchun ASL (yangilanishdan oldingi) boshlanish
            // sanasini saqlab qo'yamiz — $kredit->update() pastda shu maydonni yangi
            // qiymat bilan almashtiradi.
            $asliyBoshlanishSana = $kredit->boshlanish_sana;

            $yangiMalumot = [
                'mijoz_id'            => $data['mijoz_id'],
                'filial_id'           => $data['filial_id'],
                'jami_summa'          => $data['jami_summa'],
                'boshlangich_tolov'   => $data['boshlangich_tolov'],
                'kredit_summa'        => $data['kredit_summa'],
                'qoldiq_qarz'         => $data['kredit_summa'],
                'oylik_tolov_miqdori' => $data['oylik_tolov_miqdori'],
                'muddati_oy'          => $data['muddati_oy'],
                'tolov_kuni'          => $data['tolov_kuni'] ?? 5,
                'foiz_stavka'         => $data['foiz_stavka'] ?? 0,
                'boshlanish_sana'     => $data['boshlanish_sana'],
                'tugash_sana'         => $data['tugash_sana'],
                'kafil_mijoz_id'      => $data['kafil_mijoz_id'] ?? null,
                'kafil_ism'           => $data['kafil_ism'] ?? null,
                'kafil_telefon'       => $data['kafil_telefon'] ?? null,
                'kafil_manzil'        => $data['kafil_manzil'] ?? null,
                'izoh'                => $data['izoh'] ?? null,
            ];

            // Agar shartnomada hali kafillik hujjati uchun band versiyasi
            // belgilanmagan bo'lsa (ya'ni yaratilganda kafil yo'q edi) va endi kafil
            // biriktirilayotgan bo'lsa — kafillik hujjati uchun joriy band versiyasini
            // shu yerda "suratga olamiz".
            if (!$kredit->kafillik_band_versiya_id && (!empty($data['kafil_mijoz_id']) || !empty($data['kafil_ism']))) {
                $yangiMalumot['kafillik_band_versiya_id'] = \App\Models\HujjatBand::faolVersiya('kafillik')?->id;
            }

            // Versiyani saqlash
            $sabab = $request->input('sabab', 'Shartnoma tahrirlandi');
            $this->tulovService->versiyaSaqlash($kredit, $sabab, $yangiMalumot);

            $kredit->update($yangiMalumot);

            // Tovarlarni yangilash (eski o'chirib yangisini yozish)
            if (!empty($data['tovarlar'])) {
                $kredit->tovarlar()->delete();
                foreach ($data['tovarlar'] as $tovar) {
                    Tovar::create([
                        'reg_kredit_id'    => $kredit->id,
                        'nomi'             => $tovar['nomi'],
                        'soni'             => $tovar['soni'],
                        'narx'             => $tovar['narx'],
                        'jami_narx'        => $tovar['soni'] * $tovar['narx'],
                        'barkod'           => $tovar['barkod'] ?? null,
                        'tovar_katalog_id' => !empty($tovar['tovar_katalog_id']) ? (int)$tovar['tovar_katalog_id'] : null,
                    ]);
                }
            }

            // Grafik yangilash — operatsion kun nazorati: shartnoma boshqa kunda
            // tuzilgan bo'lsa (bugun emas), oddiy xodim grafik sanalarini o'zgartira
            // olmaydi — eski grafik (jumladan to'langan qatorlar) tegilmay qoladi.
            $adminmi = Auth::user()->rol === 'admin';
            $grafikTahrirMumkin = $adminmi || !$asliyBoshlanishSana || $asliyBoshlanishSana->isToday();
            if ($grafikTahrirMumkin) {
                $kredit->grafik()->delete();
                $this->grafikYarat($kredit->fresh(), $data['grafik'] ?? null);
            }

            return redirect()
                ->route('kreditlar.show', $kredit)
                ->with('muvaffaqiyat', 'Shartnoma muvaffaqiyatli yangilandi.');
        });
    }

    /** PDF chop etish */
    public function pdf(RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        $kredit->load(['mijoz', 'filial', 'xodim', 'tovarlar', 'grafik']);

        $pdf = Pdf::loadView('kredit.pdf', compact('kredit'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("shartnoma-{$kredit->shartnoma_raqam}.pdf");
    }

    /** Hujjat chop etish */
    public function hujjat(RegKredit $kredit, string $tur)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);
        $kredit->load(['mijoz.viloyat', 'mijoz.tuman', 'kafil.viloyat', 'kafil.tuman', 'filial', 'xodim', 'tovarlar', 'grafik']);

        $turlar = ['shartnoma','kafillik','grafik','yuk_xati','schyot','ariza','til_xat'];
        if (!in_array($tur, $turlar)) abort(404);

        if ($tur === 'kafillik' && !$kredit->kafil_mijoz_id && !$kredit->kafil_ism) {
            abort(404, "Ushbu shartnomaga kafil biriktirilmagan — kafillik shartnomasini chop etib bo'lmaydi.");
        }

        $pdf = Pdf::loadView('kredit.hujjatlar.' . $tur, compact('kredit'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($kredit->shartnoma_raqam . '-' . $tur . '.pdf');
    }

    /** To'lov grafigini yaratish (yangi shartnoma uchun) */
    /**
     * @param array|null $grafikData Forma orqali xodim qo'lda tanlagan sanalar/summalar
     *        (kredit/_form_tabs.blade.php "Graf" tabidagi sana inputlari). Berilgan bo'lsa
     *        ulardan foydalaniladi, aks holda har oy bir xil oraliq bilan avtomatik hisoblanadi.
     */
    private function grafikYarat(RegKredit $kredit, ?array $grafikData = null): void
    {
        if ($kredit->kredit_summa <= 0 || $kredit->muddati_oy <= 0) return;

        // Jami summadagi ustamaning ulushi (har oylik to'lovdan ustama qismini
        // proporsional ajratish uchun) — qarang: kredit/_form_tabs.blade.php grafikKorsatish()
        $ustamaJami  = max(0, $kredit->jami_summa - ($kredit->jami_summa / (1 + ($kredit->foiz_stavka ?? 0) / 100)));
        $ustamaUlush = $kredit->jami_summa > 0 ? $ustamaJami / $kredit->jami_summa : 0;

        if (!empty($grafikData)) {
            $qoldiq = $kredit->kredit_summa;
            $oy     = 1;
            foreach ($grafikData as $row) {
                if (empty($row['sana']) || !isset($row['summa'])) continue;
                $buOyTolov  = round((float) $row['summa'], 2);
                $buOyUstama = isset($row['ustama']) ? round((float) $row['ustama'], 2) : round($buOyTolov * $ustamaUlush, 2);
                $qoldiq     = max(0, round($qoldiq - $buOyTolov, 2));

                \App\Models\Grafik::create([
                    'reg_kredit_id' => $kredit->id,
                    'oylik_tartib'  => $oy++,
                    'tolov_sana'    => $row['sana'],
                    'tolov_summa'   => $buOyTolov,
                    'ustama_summa'  => $buOyUstama,
                    'qoldiq_suma'   => $qoldiq,
                    'holat'         => 'tolanmagan',
                ]);
            }
            return;
        }

        $oylikTolov = $kredit->oylik_tolov_miqdori;
        $qoldiq     = $kredit->kredit_summa;

        for ($oy = 1; $oy <= $kredit->muddati_oy; $oy++) {
            // Har oyning to'lov sanasi
            $sana = $kredit->boshlanish_sana->copy()->addMonths($oy - 1);

            // Oxirgi oyda qoldiq miqdorni to'liq yopish
            $buOyTolov  = ($oy === $kredit->muddati_oy) ? $qoldiq : min($oylikTolov, $qoldiq);
            $buOyUstama = round($buOyTolov * $ustamaUlush, 2);
            $qoldiq    -= $buOyTolov;

            \App\Models\Grafik::create([
                'reg_kredit_id' => $kredit->id,
                'oylik_tartib'  => $oy,
                'tolov_sana'    => $sana->toDateString(),
                'tolov_summa'   => round($buOyTolov, 2),
                'ustama_summa'  => $buOyUstama,
                'qoldiq_suma'   => round($qoldiq, 2),
                'holat'         => 'tolanmagan',
            ]);
        }
    }

    /** Filial ruxsatini tekshirish */
    private function filialRuxsatTekshir(int $kreditFilialId): void
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->filial_id !== $kreditFilialId) {
            abort(403, 'Bu shartnoma sizning filialingizga tegishli emas.');
        }
    }
}
