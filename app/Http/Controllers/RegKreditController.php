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

class RegKreditController extends Controller
{
    public function __construct(private TulovService $tulovService) {}

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
        return DB::transaction(function () use ($request) {
            $data    = $request->validated();
            $user    = Auth::user();
            $filial  = Filial::findOrFail($data['filial_id']);

            // Shartnoma raqamini avtomatik yaratish
            $yil    = now()->year;
            $raqam  = RegKredit::yangiRaqamYaratish($filial, $yil);

            // Shartnomani yaratish
            $kredit = RegKredit::create([
                ...$data,
                'shartnoma_raqam'     => $raqam,
                'xodim_id'            => $user->id,
                'kredit_summa'        => $data['kredit_summa'],
                'qoldiq_qarz'         => $data['kredit_summa'],
                'oylik_tolov_miqdori' => $data['oylik_tolov_miqdori'],
                'tolov_qilingan'      => 0,
                'holat'               => 'faol',
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

            // To'lov grafikini avtomatik yaratish
            $this->grafikYarat($kredit);

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

        return view('kredit.show', compact('kredit', 'tulovTurlari', 'xodimlar', 'filiallar', 'hp_yoqilgan', 'pochta_shablonlar', 'pochta_loglar', 'kredit_vars'));
    }

    /** Shartnoma tahrirlash formasi */
    public function edit(RegKredit $kredit)
    {
        $this->filialRuxsatTekshir($kredit->filial_id);

        if (!in_array(Auth::user()->rol, ['admin', 'menejer'])) {
            abort(403);
        }

        $kredit->load(['mijoz', 'tovarlar', 'grafik']);

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
                'kafil_ism'           => $data['kafil_ism'] ?? null,
                'kafil_telefon'       => $data['kafil_telefon'] ?? null,
                'kafil_manzil'        => $data['kafil_manzil'] ?? null,
                'izoh'                => $data['izoh'] ?? null,
            ];

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

            // Grafik yangilash
            $kredit->grafik()->delete();
            $this->grafikYarat($kredit->fresh());

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
        $kredit->load(['mijoz', 'filial', 'xodim', 'tovarlar', 'grafik']);

        $turlar = ['shartnoma','kafillik','grafik','yuk_xati','schyot','ariza','til_xat'];
        if (!in_array($tur, $turlar)) abort(404);

        $pdf = Pdf::loadView('kredit.hujjatlar.' . $tur, compact('kredit'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream($kredit->shartnoma_raqam . '-' . $tur . '.pdf');
    }

    /** To'lov grafigini yaratish (yangi shartnoma uchun) */
    private function grafikYarat(RegKredit $kredit): void
    {
        if ($kredit->kredit_summa <= 0 || $kredit->muddati_oy <= 0) return;

        $oylikTolov = $kredit->oylik_tolov_miqdori;
        $qoldiq     = $kredit->kredit_summa;

        for ($oy = 1; $oy <= $kredit->muddati_oy; $oy++) {
            // Har oyning to'lov sanasi
            $sana = $kredit->boshlanish_sana->copy()->addMonths($oy - 1);

            // Oxirgi oyda qoldiq miqdorni to'liq yopish
            $buOyTolov = ($oy === $kredit->muddati_oy) ? $qoldiq : min($oylikTolov, $qoldiq);
            $qoldiq   -= $buOyTolov;

            \App\Models\Grafik::create([
                'reg_kredit_id' => $kredit->id,
                'oylik_tartib'  => $oy,
                'tolov_sana'    => $sana->toDateString(),
                'tolov_summa'   => round($buOyTolov, 2),
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
