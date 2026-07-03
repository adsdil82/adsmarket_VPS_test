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
    public function __construct(
        private TulovService $tulovService,
        private SmsService $smsService,
        private \App\Services\ContractTransferService $transferService,
        private \App\Services\StockService $stockService,
    ) {}

    /** Shartnomalar ro'yxati */
    /** Ajax: shtrix-kod bo'yicha ANIQ mos tovarni topadi (30 tadan tashqarida qolgan eski tovarlar uchun ham). */
    public function tovarBarkod(Request $request)
    {
        $tovar = \App\Models\TovarKatalog::faol()
            ->where('barkod', $request->barkod)
            ->where('qoldiq', '>', 0)
            ->select(['id','guruh_id','nomi','barkod','qoldiq','sotish_narx','nasiya_narx','birlik'])
            ->first();

        if (!$tovar) {
            return response()->json(['xato' => "Bu shtrix-kodga mos tovar topilmadi yoki qoldiq yo'q."], 404);
        }

        return response()->json($tovar);
    }

    /** Jadvaldagi "Muddati o'tgan kunlar" filtri uchun oraliqlar (kechikish_oraligi) */
    public const KECHIKISH_ORALIQLARI = [
        '1-30'    => [1, 30],
        '31-60'   => [31, 60],
        '61-90'   => [61, 90],
        '91-120'  => [91, 120],
        '121-150' => [121, 150],
        '151-180' => [151, 180],
        '180+'    => [181, null],
    ];

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin()
            ? ($request->filial_id ?: null)
            : $user->filial_id;

        $query = $this->kreditlarSorovi($request, $user, $filialId);

        // Ajax qidiruv
        if ($request->expectsJson()) {
            return response()->json(
                $query->limit(10)->get(['id', 'shartnoma_raqam', 'mijoz_id'])
            );
        }

        $kreditlar = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $xodimlar  = Foydalanuvchi::faol()
            ->when(!$user->isAdmin(), fn($q) => $q->where('filial_id', $user->filial_id))
            ->orderBy('ism_familiya')->get(['id', 'ism_familiya']);
        $jamiSummalar = $this->kreditlarJamiSummalari($request, $user, $filialId);

        return view('kredit.index', compact('kreditlar', 'filiallar', 'filialId', 'xodimlar', 'jamiSummalar'));
    }

    /**
     * Joriy filtrlarga mos BARCHA shartnomalar bo'yicha (sahifalashdan qat'i nazar) jami
     * summalarni hisoblaydi — jadval shapkasi tagidagi "Jami" qatori uchun. Filtrli so'rovda
     * HAVING (kechikish_oraligi, muddati_kelgan va h.k.) ishlatilgani uchun natija subquery
     * ko'rinishida o'raladi (Laravel'ning sahifalash uchun COUNT hisoblash usuli bilan bir xil).
     */
    private function kreditlarJamiSummalari(Request $request, $user, ?int $filialId): object
    {
        $sorov = $this->kreditlarSorovi($request, $user, $filialId);

        return DB::table(DB::raw('(' . $sorov->toSql() . ') as t'))
            ->mergeBindings($sorov->getQuery())
            ->selectRaw('
                COUNT(*) as soni,
                SUM(jami_summa) as jami_summa,
                SUM(boshlangich_tolov) as boshlangich_tolov,
                SUM(kredit_summa) as kredit_summa,
                SUM(boshlangich_tolov + tolov_qilingan) as jami_tolangan,
                SUM(qoldiq_qarz) as qoldiq_qarz,
                SUM(kechikkan_summa) as kechikkan_summa
            ')
            ->first();
    }

    /** Shartnomalar ro'yxatini Excelga eksport qilish (joriy filtrlar bilan) */
    public function excel(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;

        $kreditlar = $this->kreditlarSorovi($request, $user, $filialId)
            ->orderByDesc('created_at')->limit(5000)->get();

        $headers = ['Shartnoma', 'Xodim', 'Mijoz', 'Telefon', 'Manzil', 'Filial', 'Boshlanish', 'Tugash', 'Muddat (oy)', 'Jami', 'Oldindan', 'Kredit', 'Jami to\'langan', 'Qoldiq', 'Kechikkan summa', 'Holat'];
        $rows = $kreditlar->map(fn($k) => [
            $k->shartnoma_raqam,
            $k->xodim->ism_familiya ?? '—',
            trim(($k->mijoz->familiya ?? '') . ' ' . ($k->mijoz->ism ?? '')),
            $k->mijoz->telefon ?? '—',
            $k->mijoz->manzil ?? '—',
            $k->filial->kod ?? '—',
            $k->boshlanish_sana ? $k->boshlanish_sana->format('d.m.Y') : '—',
            $k->tugash_sana ? $k->tugash_sana->format('d.m.Y') : '—',
            $k->muddati_oy,
            $k->jami_summa,
            $k->boshlangich_tolov,
            $k->kredit_summa,
            (float) $k->boshlangich_tolov + (float) $k->tolov_qilingan,
            $k->qoldiq_qarz,
            (float) $k->kechikkan_summa,
            $k->holatNomi,
        ]);

        return $this->excelResponse('Shartnomalar', $headers, $rows->toArray());
    }

    /** Shartnomalar ro'yxati uchun umumiy filtrlangan so'rov (index/ajax/excel'da qayta ishlatiladi) */
    private function kreditlarSorovi(Request $request, $user, ?int $filialId)
    {
        // "Muddati kelgan" (jadvalga aynan mos) va "Muddatidan oldinda" (jadvaldan oldinda) — bular
        // reg_kredit.holat ustunidagi haqiqiy qiymatlar emas, balki hisoblab topiladigan holatlar:
        // bugungacha muddati kelgan oylar soni bilan haqiqatda to'langan oylar soni solishtiriladi.
        $maxsusHolatlar = ['muddati_kelgan', 'muddatidan_oldinda'];

        $query = RegKredit::with(['mijoz', 'filial', 'xodim', 'joriyXodim'])
            ->withCount([
                'grafik as tolangan_oy_soni' => fn($q) => $q->where('holat', 'tolangan'),
                'grafik as muddati_kelgan_oy_soni' => fn($q) => $q->whereNotNull('tolov_sana')->where('tolov_sana', '<=', today()),
            ])
            ->addSelect(['max_kechikish_kun' => \App\Models\Grafik::selectRaw('MAX(DATEDIFF(CURDATE(), tolov_sana))')
                ->whereColumn('reg_kredit_id', 'reg_kredit.id')
                ->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan'])
                ->whereNotNull('tolov_sana')
                ->where('tolov_sana', '<', now()->toDateString()),
            ])
            ->addSelect(['kechikkan_summa' => \App\Models\Grafik::selectRaw('COALESCE(SUM(tolov_summa - tolangan_summa),0)')
                ->whereColumn('reg_kredit_id', 'reg_kredit.id')
                ->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan'])
                ->whereNotNull('tolov_sana')
                ->where('tolov_sana', '<', now()->toDateString()),
            ])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->xodim_id, fn($q) => $q->where('xodim_id', $request->xodim_id))
            ->when($request->holat && !in_array($request->holat, $maxsusHolatlar), fn($q) => $q->where('holat', $request->holat))
            ->when($request->holat === 'muddati_kelgan', fn($q) => $q->havingRaw('muddati_kelgan_oy_soni > 0 AND tolangan_oy_soni = muddati_kelgan_oy_soni'))
            ->when($request->holat === 'muddatidan_oldinda', fn($q) => $q->havingRaw('tolangan_oy_soni > muddati_kelgan_oy_soni'))
            ->when($request->kechikish_oraligi && isset(self::KECHIKISH_ORALIQLARI[$request->kechikish_oraligi]), function ($q) use ($request) {
                [$min, $max] = self::KECHIKISH_ORALIQLARI[$request->kechikish_oraligi];
                $q->havingRaw('max_kechikish_kun >= ?', [$min]);
                if ($max !== null) {
                    $q->havingRaw('max_kechikish_kun <= ?', [$max]);
                }
            })
            ->when($request->qidiruv, fn($q) => $q->qidirish($request->qidiruv));

        return $query;
    }

    // ─── Private: Excel HTML response (hisobot moduli bilan bir xil uslub) ─────
    private function excelResponse(string $sarlavha, array $headers, array $rows)
    {
        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $html .= 'xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>';
        $html .= '<x:ExcelWorksheet><x:Name>NasiyaPro</x:Name>';
        $html .= '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>body{font-family:Arial,sans-serif;font-size:10pt;}';
        $html .= 'h3{color:#1a3a2a;font-size:13pt;margin:0 0 6px 0;}';
        $html .= 'table{border-collapse:collapse;width:100%;}';
        $html .= 'th{background:#1d4ed8;color:#fff;font-weight:bold;border:1px solid #777;padding:5px 8px;white-space:nowrap;font-size:10pt;}';
        $html .= 'td{border:1px solid #ccc;padding:3px 8px;font-size:9pt;}';
        $html .= 'tr:nth-child(even) td{background:#eef4ff;}';
        $html .= '.r{text-align:right;mso-number-format:"#,##0";}</style></head><body>';
        $html .= '<h3>' . htmlspecialchars($sarlavha) . '</h3>';
        $html .= '<p style="color:#888;font-size:8pt;margin:0 0 8px 0">NasiyaPro — ' . now()->format('d.m.Y H:i') . '</p>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars((string) $h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ((array) $row as $cell) {
                $isNum = is_numeric($cell) && $cell !== '' && $cell !== null;
                $cls   = $isNum ? ' class="r"' : '';
                $val   = $isNum
                    ? number_format((float) $cell, 0, '.', ' ')
                    : htmlspecialchars((string) ($cell ?? ''));
                $html .= "<td$cls>$val</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody><tfoot><tr><td colspan="' . count($headers) . '" ';
        $html .= 'style="background:#eef4ff;font-size:8pt;color:#555;padding:4px 8px;">';
        $html .= 'Jami: ' . count($rows) . ' qator | NasiyaPro ' . now()->format('d.m.Y H:i');
        $html .= '</td></tr></tfoot></table></body></html>';

        $fn = 'nasiyapro_' . preg_replace('/[^\w]/', '_', strtolower($sarlavha)) . '_' . now()->format('Ymd_Hi') . '.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fn . '"',
            'Cache-Control'       => 'max-age=0',
            'Pragma'              => 'no-cache',
        ]);
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
            'tovarlar' => fn($q) => $q->faol()->where('qoldiq', '>', 0)->orderByDesc('created_at')->take(30)->select(['id','guruh_id','nomi','barkod','qoldiq','sotish_narx','nasiya_narx','birlik','created_at'])
        ])->whereHas('tovarlar', fn($q) => $q->faol()->where('qoldiq', '>', 0))
          ->orderBy('nomi')->get(['id','nomi']);

        $xodimlar = Foydalanuvchi::faol()
            ->when(!$user->isAdmin(), fn($q) => $q->where('filial_id', $user->filial_id))
            ->orderBy('ism_familiya')->get(['id','ism_familiya','filial_id']);

        return view('kredit.create', compact('filiallar', 'mijoz', 'tovarGuruhlar', 'xodimlar'));
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

            // Shartnoma "kutilmoqda" holatida yaratiladi — ombordan tovar hali
            // chiqarilmaydi va boshlang'ich to'lov hali kassa/to'lov-turiga
            // o'tkazilmaydi. Bu operatsiyalar faqat admin/menejer "Aktivlashtirish"
            // tugmasini bosganda (qarang: activate()) amalga oshiriladi — shu
            // muddatgacha shartnoma va hujjatlarni (PDF) chop etish mumkin.
            $kredit = RegKredit::create([
                ...$data,
                'shartnoma_raqam'           => $raqam,
                'xodim_id'                  => $user->id,
                'kredit_summa'              => $data['kredit_summa'],
                'qoldiq_qarz'               => $data['kredit_summa'],
                'oylik_tolov_miqdori'       => $data['oylik_tolov_miqdori'],
                'tolov_qilingan'            => 0,
                'holat'                     => 'kutilmoqda',
                'shartnoma_band_versiya_id' => $shartnomaBand?->id,
                'kafillik_band_versiya_id'  => $kafillikBand?->id,
                // "Kreditnik" tabida tanlangan mas'ul kredit xodimi — bo'sh
                // qoldirilsa, shartnomani tuzgan xodimning o'zi mas'ul bo'lib qoladi
                // (RegKredit::joriyXodim() — joriy_xodim_id ?? xodim_id).
                'joriy_xodim_id'            => $data['joriy_xodim_id'] ?? null,
            ]);

            // Tovarlarni saqlash (ombordan haligacha hech narsa chiqarilmaydi)
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

            // Ombordagi qoldiq yetarliligini oldindan tekshiramiz (xato bo'lsa
            // shartnoma umuman saqlanmasin), lekin haqiqiy chiqim/decrement
            // faqat activate()da bajariladi.
            $katalogItems = collect($data['tovarlar'])
                ->filter(fn($t) => !empty($t['tovar_katalog_id']));
            foreach ($katalogItems as $t) {
                $tk = TovarKatalog::find((int)$t['tovar_katalog_id']);
                if ($tk && $tk->qoldiq < $t['soni']) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        back()->withErrors(["«{$tk->nomi}»: omborda faqat {$tk->qoldiq} {$tk->birlik} bor."])->withInput()
                    );
                }
            }

            // To'lov grafikini yaratish (xodim qo'lda sozlagan sanalar bo'lsa ulardan, aks holda avtomatik)
            $this->grafikYarat($kredit, $data['grafik'] ?? null);

            // Boshlang'ich versiya
            $this->tulovService->versiyaSaqlash($kredit, 'Yangi shartnoma yaratildi (kutilmoqda)', []);

            return redirect()
                ->route('kreditlar.show', $kredit)
                ->with('muvaffaqiyat', "Shartnoma {$raqam} \"Kutilmoqda\" holatida saqlandi. Ombordan tovar chiqarish va to'lovni rasmiylashtirish uchun \"Aktivlashtirish\" tugmasini bosing.");
        });
    }

    /**
     * Shartnomani "kutilmoqda" holatidan "faol"ga o'tkazish — ombordan tovarni
     * haqiqatan chiqaradi (OmbordanChiqim + qoldiq decrement) va boshlang'ich
     * to'lov bo'lsa, uni OldinTulov sifatida rasmiylashtiradi. Faqat bir marta
     * bajariladi — allaqachon faollashtirilgan shartnomaga qayta ishlamaydi.
     */
    public function activate(Request $request, RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        if (!in_array(Auth::user()->rol, ['admin', 'menejer'])) {
            abort(403);
        }

        if ($kredit->holat !== 'kutilmoqda') {
            return back()->withErrors(['holat' => 'Bu shartnoma allaqachon faollashtirilgan yoki boshqa holatda.']);
        }

        return DB::transaction(function () use ($kredit) {
            $user = Auth::user();
            $kredit->load('tovarlar');

            $katalogItems = $kredit->tovarlar->filter(fn($t) => $t->tovar_katalog_id);

            if ($katalogItems->isNotEmpty()) {
                // Nasiya sotuv ham FAQAT shu shartnoma filialining o'z ombor
                // qoldig'idan chiqim qilinadi — boshqa filial zaxirasiga tegmaydi.
                $ombor = $this->stockService->asosiyOmbor($kredit->filial_id);
                if (!$ombor) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        back()->withErrors(["Bu filial uchun ombor topilmadi."])
                    );
                }

                foreach ($katalogItems as $t) {
                    $tk = TovarKatalog::find($t->tovar_katalog_id);
                    $mavjud = $this->stockService->qoldiq($ombor->id, $t->tovar_katalog_id);
                    if ($tk && $mavjud < $t->soni) {
                        throw new \Illuminate\Validation\ValidationException(
                            validator([], []),
                            back()->withErrors(["«{$tk->nomi}»: «{$ombor->nomi}» omborida faqat {$mavjud} {$tk->birlik} bor — aktivlashtirib bo'lmadi."])
                        );
                    }
                }

                $chiqimJami = $katalogItems->sum(fn($t) => $t->soni * $t->narx);

                $chiqim = OmbordanChiqim::create([
                    'filial_id'    => $kredit->filial_id,
                    'ombor_id'     => $ombor->id,
                    'shartnoma_id' => $kredit->id,
                    'xodim_id'     => $user->id,
                    'sana'         => today(),
                    'sabab'        => 'nasiya_sotish',
                    'umumiy_summa' => $chiqimJami,
                    'izoh'         => "Nasiya shartnoma #{$kredit->shartnoma_raqam}",
                    'holat'        => 'tasdiqlangan',
                ]);

                foreach ($katalogItems as $t) {
                    ChiqimTafsilot::create([
                        'chiqim_id'  => $chiqim->id,
                        'tovar_id'   => $t->tovar_katalog_id,
                        'miqdor'     => $t->soni,
                        'narx'       => $t->narx,
                        'jami_summa' => $t->soni * $t->narx,
                    ]);
                    $this->stockService->chiqim(
                        $ombor->id, $t->tovar_katalog_id, (float) $t->soni,
                        manbaTur: 'nasiya_shartnoma', manbaId: $kredit->id,
                        izoh: "Nasiya shartnoma #{$kredit->shartnoma_raqam}", harakat: 'chiqim',
                    );
                }
            }

            // Boshlang'ich to'lov bo'lsa — kassaga/oldindan to'lov sifatida rasmiylashtiramiz
            if ((float) $kredit->boshlangich_tolov > 0) {
                $tulovTuri = TulovTuri::faol()->first();
                if ($tulovTuri) {
                    $oldinTulov = \App\Models\OldinTulov::create([
                        'reg_kredit_id' => $kredit->id,
                        'xodim_id'      => $user->id,
                        'tulov_turi_id' => $tulovTuri->id,
                        'summa'         => $kredit->boshlangich_tolov,
                        'tolov_sana'    => today(),
                        'qabul_vaqt'    => now(),
                        'izoh'          => 'Aktivlashtirishda avtomatik rasmiylashtirilgan boshlang\'ich to\'lov',
                    ]);

                    // Naqd/terminal/bank bo'lsa — "Pul oqimlari"ga avtomatik kirim
                    $this->tulovService->pulOqimigaYoz(
                        filialId: $kredit->filial_id,
                        tulovTuriId: $tulovTuri->id,
                        summa: (float) $kredit->boshlangich_tolov,
                        sana: today()->toDateString(),
                        kategoriyaKodi: 'CF-1200',
                        izoh: "Shartnoma {$kredit->shartnoma_raqam} bo'yicha boshlang'ich to'lov",
                        manbaTur: 'oldin_tulov',
                        manbaId: $oldinTulov->id,
                    );
                }
            }

            $kredit->update(['holat' => 'faol']);

            $this->tulovService->versiyaSaqlash($kredit, 'Shartnoma aktivlashtirildi', []);

            return redirect()
                ->route('kreditlar.show', $kredit)
                ->with('muvaffaqiyat', "Shartnoma {$kredit->shartnoma_raqam} aktivlashtirildi — ombordan tovar chiqarildi.");
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

        $ustamaKorishMumkin = \App\Models\Rol::ustamaKorishMumkinmi(Auth::user()->rol);

        return view('kredit.show', compact(
            'kredit', 'tulovTurlari', 'xodimlar', 'filiallar', 'hp_yoqilgan', 'pochta_shablonlar', 'pochta_loglar', 'kredit_vars',
            'sms_shablonlar', 'sms_loglar', 'sms_shablon_matnlari', 'sms_oxirgi_24soat', 'ustamaKorishMumkin'
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
            'tovarlar' => fn($q) => $q->faol()->where('qoldiq', '>', 0)->orderByDesc('created_at')->take(30)->select(['id','guruh_id','nomi','barkod','qoldiq','sotish_narx','nasiya_narx','birlik','created_at'])
        ])->whereHas('tovarlar', fn($q) => $q->faol()->where('qoldiq', '>', 0))
          ->orderBy('nomi')->get(['id','nomi']);

        $xodimlar = Foydalanuvchi::faol()
            ->when(!Auth::user()->isAdmin(), fn($q) => $q->where('filial_id', $kredit->filial_id))
            ->orderBy('ism_familiya')->get(['id','ism_familiya','filial_id']);

        $xodimTarixi = \App\Models\ShartnomaxodimTarixi::where('shartnoma_id', $kredit->id)
            ->with(['eskiXodim:id,ism_familiya', 'yangiXodim:id,ism_familiya', 'ozgartirgan:id,ism_familiya'])
            ->latest()->get();

        return view('kredit.edit', compact('kredit', 'filiallar', 'tovarGuruhlar', 'xodimlar', 'xodimTarixi'));
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

            // "Kreditnik" tabi — mas'ul kredit xodimi o'zgartirilgan bo'lsa,
            // ContractTransferService orqali (tarix yozuvi bilan birga) qo'llaymiz —
            // shartnomaning boshqa maydonlari kabi to'g'ridan-to'g'ri update()
            // qilinmaydi, chunki bu o'zgarish "TRANSFERLAR" tarixida ko'rinishi kerak.
            $yangiXodimId = $data['joriy_xodim_id'] ?? null;
            if ($yangiXodimId) {
                $joriyXodimId = $kredit->joriy_xodim_id ?? $kredit->xodim_id;
                if ($yangiXodimId != $joriyXodimId) {
                    $this->transferService->xodimniQaytaTayin(
                        $kredit, (int) $yangiXodimId, 'Shartnoma tahrirlash sahifasidan o\'zgartirildi'
                    );
                }
            }

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

        $turlar = ['shartnoma','kafillik','grafik','yuk_xati','schyot','ariza','til_xat','plan_fakt'];
        if (!in_array($tur, $turlar)) abort(404);

        if ($tur === 'kafillik' && !$kredit->kafil_mijoz_id && !$kredit->kafil_ism) {
            abort(404, "Ushbu shartnomaga kafil biriktirilmagan — kafillik shartnomasini chop etib bo'lmaydi.");
        }

        $pdf = Pdf::loadView('kredit.hujjatlar.' . $tur, compact('kredit'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($kredit->shartnoma_raqam . '-' . $tur . '.pdf');
    }

    /**
     * Hujjatni tahrirlanadigan (oddiy HTML, PDF emas) ko'rinishda qaytarish —
     * "Hujjatlar" tabidagi "Tahrirlash" modali shu marshrutni iframe ichida
     * yuklab, brauzerda matnni to'g'ridan-to'g'ri (contenteditable) tahrirlash
     * va shundan keyin window.print() bilan chop etish imkonini beradi.
     * Hech narsa bazaga saqlanmaydi — bu faqat chop etishdan oldingi vaqtinchalik
     * tuzatish (masalan imlo xatosi, manzilni qo'lda aniqlashtirish).
     */
    public function hujjatHtml(RegKredit $kredit, string $tur)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);
        $kredit->load(['mijoz.viloyat', 'mijoz.tuman', 'kafil.viloyat', 'kafil.tuman', 'filial', 'xodim', 'tovarlar', 'grafik']);

        $turlar = ['shartnoma','kafillik','grafik','yuk_xati','schyot','ariza','til_xat','plan_fakt'];
        if (!in_array($tur, $turlar)) abort(404);

        if ($tur === 'kafillik' && !$kredit->kafil_mijoz_id && !$kredit->kafil_ism) {
            abort(404, "Ushbu shartnomaga kafil biriktirilmagan — kafillik shartnomasini chop etib bo'lmaydi.");
        }

        return view('kredit.hujjatlar.' . $tur, compact('kredit'));
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
            // Har oyning to'lov sanasi — addMonths() o'rniga addMonthsNoOverflow()
            // ishlatiladi: boshlanish kuni 29-31 bo'lib, keyingi oy shu kunni
            // bermasa (masalan fevral), oddiy addMonths() bir oyni butunlay
            // tashlab, sanani 2 oyga siljitib yuboradi (overflow). NoOverflow
            // bunday hollarda oyning oxirgi kuniga qisqartiradi.
            $sana = $kredit->boshlanish_sana->copy()->addMonthsNoOverflow($oy - 1);

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

    /**
     * Shartnomani tanlangan versiyadagi ("oldingi holat") qiymatlarga qaytarish.
     * Faqat admin uchun ochiq (xavfli/qaytarib bo'lmaydigan amal). Faqat reg_kredit
     * jadvalining o'z maydonlarini tiklaydi — tovarlar va grafik (to'lov jadvali)
     * jadvallariga TEGMAYDI, chunki ularning tarixi versiya jadvalida saqlanmaydi
     * va ularni avtomatik qayta generatsiya qilish to'langan to'lovlarni
     * yo'qotib qo'yishi mumkin (bu loyihada oldin real hodisa bo'lgan).
     */
    public function versiyaniQaytar(\App\Models\RegKredit $kredit, \App\Models\ShartnomavVersioniya $versiya)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        if (Auth::user()->rol !== 'admin') {
            abort(403, 'Versiyani qaytarish faqat admin uchun ochiq.');
        }

        if ($versiya->reg_kredit_id !== $kredit->id) {
            abort(404);
        }

        if (!$versiya->eski_holat) {
            return back()->withErrors(['versiya' => "Bu versiya uchun \"oldingi holat\" ma'lumoti mavjud emas (birinchi yaratilish versiyasi)."]);
        }

        return DB::transaction(function () use ($kredit, $versiya) {
            $tiklanadiganMaydonlar = array_intersect_key(
                $versiya->eski_holat,
                array_flip($kredit->getFillable())
            );

            $this->tulovService->versiyaSaqlash(
                $kredit,
                "v{$versiya->versiya_raqam} versiyasiga qaytarildi",
                $tiklanadiganMaydonlar
            );

            $kredit->update($tiklanadiganMaydonlar);

            return redirect()
                ->route('kreditlar.versiyalar.index', $kredit)
                ->with('muvaffaqiyat', "Shartnoma v{$versiya->versiya_raqam} versiyasidagi holatga qaytarildi. Diqqat: tovarlar va to'lov grafigi o'zgartirilmadi — agar summa farq qilsa, ularni qo'lda tekshiring.");
        });
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
