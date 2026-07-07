<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\FilialTransfer;
use App\Models\KassaTransfer;
use App\Models\ShartnomaxodimTarixi;
use App\Models\ShartnomaiFilialTarixi;
use App\Models\TulovTuri;
use App\Models\Grafik;
use App\Models\Foydalanuvchi;
use App\Models\RegKredit;
use App\Models\Tulov;
use App\Models\HisobotShablon;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HisobotController extends Controller
{
    private function filialId(Request $request): ?int
    {
        $user = Auth::user();
        return $user->isAdmin()
            ? ($request->filial_id ? (int)$request->filial_id : null)
            : (int)$user->filial_id;
    }

    // ── 1. Bosh sahifa ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $this->filialId($request);
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $tulovlarHisoboti = Tulov::with(['kredit.mijoz','kredit.filial','tulovTuri','xodim'])
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->sanada($danSana, $gachaSana)
            ->orderByDesc('tolov_sana')
            ->paginate(30)->withQueryString();

        $kunlikTulovlar = Tulov::when($filialId, fn($q) => $q->filialda($filialId))
            ->select(
                DB::raw('DATE(tulovlar.tolov_sana) as sana'),
                DB::raw('SUM(tulovlar.summa) as jami'),
                DB::raw('COUNT(*) as soni'))
            ->sanada(now()->subDays(29)->toDateString(), now()->toDateString())
            ->groupBy('sana')->orderBy('sana')->get();

        $tulovTurlariStatistika = Tulov::when($filialId, fn($q) => $q->filialda($filialId))
            ->select('tulovlar.tulov_turi_id',
                DB::raw('SUM(tulovlar.summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('tulovTuri')
            ->sanada($danSana, $gachaSana)
            ->groupBy('tulovlar.tulov_turi_id')->orderByDesc('jami')->get();

        $xodimlarStatistika = Tulov::when($filialId, fn($q) => $q->filialda($filialId))
            ->select('tulovlar.xodim_id',
                DB::raw('SUM(tulovlar.summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('xodim')
            ->sanada($danSana, $gachaSana)
            ->groupBy('tulovlar.xodim_id')->orderByDesc('jami')->get();

        $muddatiOtganlar = RegKredit::with(['mijoz','filial'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->where('holat','muddati_otgan')->orderByDesc('qoldiq_qarz')->limit(20)->get();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        return view('hisobot.index', compact(
            'tulovlarHisoboti','kunlikTulovlar','muddatiOtganlar',
            'tulovTurlariStatistika','xodimlarStatistika',
            'filiallar','filialId','danSana','gachaSana'));
    }

    // ── 2. Kredit portfeli ─────────────────────────────────────────
    public function kreditPortfeli(Request $request)
    {
        $filialId  = $this->filialId($request);
        $sana      = $request->sana ?? now()->toDateString();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();

        $portfolio = DB::table('reg_kredit as rk')
            ->join('filiallar as f','f.id','=','rk.filial_id')
            ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
            ->selectRaw("f.nomi as filial, f.kod,
                COUNT(*) as jami,
                SUM(rk.holat='faol') as faol,
                SUM(rk.holat='muddati_otgan') as muddati_otgan,
                SUM(rk.holat='yopilgan') as yopilgan,
                SUM(rk.holat='muzlatilgan') as muzlatilgan,
                COALESCE(SUM(rk.kredit_summa),0) as jami_kredit,
                COALESCE(SUM(CASE WHEN rk.holat IN('faol','muddati_otgan') THEN rk.qoldiq_qarz ELSE 0 END),0) as aktiv_qoldiq,
                COALESCE(SUM(rk.tolov_qilingan),0) as jami_tolov")
            ->groupBy('f.id','f.nomi','f.kod')->orderByDesc('jami')->get();

        $oyDinamika = DB::table('reg_kredit')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->where('boshlanish_sana','>=', now()->subMonths(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(boshlanish_sana,'%Y-%m') as oy,
                COUNT(*) as soni, COALESCE(SUM(kredit_summa),0) as summa")
            ->groupBy('oy')->orderBy('oy')->get();

        if ($request->get('format') === 'excel') {
            return $this->excelResponse("Kredit portfeli — $sana",
                ['Filial','Kod','Jami','Faol','Muddati otgan','Yopilgan','Jami kredit','Aktiv qoldiq','Tolov qilingan'],
                $portfolio->map(fn($r) => [
                    $r->filial,$r->kod,$r->jami,$r->faol,$r->muddati_otgan,
                    $r->yopilgan,$r->jami_kredit,$r->aktiv_qoldiq,$r->jami_tolov
                ])->toArray());
        }

        return view('hisobot.kredit_portfolio', compact('portfolio','oyDinamika','filiallar','filialId','sana'));
    }

    // ── 3. Chiqarilgan kreditlar ───────────────────────────────────
    public function chiqarilganKreditlar(Request $request)
    {
        $filialId  = $this->filialId($request);
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();

        $baseQuery = RegKredit::with(['mijoz:id,familiya,ism,telefon','filial:id,kod,nomi','xodim:id,ism_familiya'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereBetween('boshlanish_sana', [$danSana, $gachaSana]);

        $jami     = DB::table('reg_kredit')
            ->when($filialId, fn($q) => $q->where('filial_id',$filialId))
            ->whereBetween('boshlanish_sana',[$danSana,$gachaSana])
            ->selectRaw('COUNT(*) as soni, COALESCE(SUM(kredit_summa),0) as summa')->first();

        $kreditlar = $baseQuery->orderByDesc('boshlanish_sana')->paginate(50)->withQueryString();

        if ($request->get('format') === 'excel') {
            $all = RegKredit::with(['mijoz','filial','xodim'])
                ->when($filialId, fn($q) => $q->where('filial_id',$filialId))
                ->whereBetween('boshlanish_sana',[$danSana,$gachaSana])
                ->orderByDesc('boshlanish_sana')->get();
            return $this->excelResponse("Chiqarilgan kreditlar $danSana — $gachaSana",
                ['#','Shartnoma','Filial','Mijoz','Telefon','Boshlanish','Tugash','Kredit summa','Tolov qilingan','Qoldiq','Holat'],
                $all->map(fn($r,$i) => [
                    $i+1, $r->shartnoma_raqam, $r->filial->kod??'',
                    $r->mijoz->familiya.' '.$r->mijoz->ism, $r->mijoz->telefon??'',
                    $r->boshlanish_sana?->format('d.m.Y')??'',
                    $r->tugash_sana?->format('d.m.Y')??'',
                    (float)$r->kredit_summa, (float)$r->tolov_qilingan, (float)$r->qoldiq_qarz,
                    $r->holatNomi
                ])->toArray());
        }

        return view('hisobot.chiqarilgan', compact('kreditlar','jami','filiallar','filialId','danSana','gachaSana'));
    }

    // ── 3b. Kreditga sotilgan tovarlar ──────────────────────────────
    public function sotilganTovarlar(Request $request)
    {
        $filialId  = $this->filialId($request);
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();

        $baseQuery = fn () => DB::table('tovarlar as t')
            ->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
            ->leftJoin('tovar_katalog as tk', 'tk.id', '=', 't.tovar_katalog_id')
            ->when($filialId, fn ($q) => $q->where('rk.filial_id', $filialId))
            ->whereBetween('rk.boshlanish_sana', [$danSana, $gachaSana]);

        $tovarlar = $baseQuery()
            ->selectRaw("t.nomi,
                COALESCE(tk.birlik,'dona') as birlik,
                SUM(t.soni) as jami_soni,
                COUNT(DISTINCT t.reg_kredit_id) as shartnoma_soni,
                COALESCE(SUM(t.soni * COALESCE(tk.tan_narx,0)),0) as kirim_summasi,
                COALESCE(SUM(t.jami_narx),0) as sotilgan_summasi")
            ->groupBy('t.nomi', 'tk.birlik')
            ->orderByDesc('sotilgan_summasi')
            ->get()
            ->map(function ($r) {
                $r->farqi        = (float) $r->sotilgan_summasi - (float) $r->kirim_summasi;
                $r->ustama_foizi = $r->kirim_summasi > 0 ? round($r->farqi / $r->kirim_summasi * 100, 1) : 0;
                return $r;
            });

        $jami = (object) [
            'turlar_soni'      => $tovarlar->count(),
            'soni'             => $tovarlar->sum('jami_soni'),
            'shartnoma_soni'   => $baseQuery()->distinct('t.reg_kredit_id')->count('t.reg_kredit_id'),
            'kirim_summasi'    => $tovarlar->sum('kirim_summasi'),
            'sotilgan_summasi' => $tovarlar->sum('sotilgan_summasi'),
            'farqi'            => $tovarlar->sum('farqi'),
        ];
        $jami->ustama_foizi = $jami->kirim_summasi > 0 ? round($jami->farqi / $jami->kirim_summasi * 100, 1) : 0;

        if ($request->get('format') === 'excel') {
            return $this->excelResponse("Kreditga sotilgan tovarlar $danSana — $gachaSana",
                ['#','Tovar nomi','Birlik','Soni','Shartnomalar soni','Kirim summasi','Sotilgan summasi','Farqi (ustama)','Ustama %'],
                $tovarlar->values()->map(fn($r,$i) => [
                    $i+1, $r->nomi, $r->birlik, (int)$r->jami_soni, (int)$r->shartnoma_soni,
                    (float)$r->kirim_summasi, (float)$r->sotilgan_summasi, (float)$r->farqi, $r->ustama_foizi
                ])->toArray());
        }

        return view('hisobot.sotilgan_tovarlar', compact('tovarlar','jami','filiallar','filialId','danSana','gachaSana'));
    }

    // ── 3c. Bonusga berilgan tovarlar ───────────────────────────────
    public function bonusTovarlar(Request $request)
    {
        $filialId  = $this->filialId($request);
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();

        $baseQuery = fn () => DB::table('tovarlar as t')
            ->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
            ->leftJoin('tovar_katalog as tk', 'tk.id', '=', 't.tovar_katalog_id')
            ->where('t.turi', 'bonus')
            ->when($filialId, fn ($q) => $q->where('rk.filial_id', $filialId))
            ->whereBetween('rk.boshlanish_sana', [$danSana, $gachaSana]);

        $tovarlar = $baseQuery()
            ->selectRaw("t.nomi,
                COALESCE(tk.birlik,'dona') as birlik,
                SUM(t.soni) as jami_soni,
                COUNT(DISTINCT t.reg_kredit_id) as shartnoma_soni,
                COALESCE(SUM(t.jami_narx),0) as jami_qiymat")
            ->groupBy('t.nomi', 'tk.birlik')
            ->orderByDesc('jami_qiymat')
            ->get();

        $jami = (object) [
            'turlar_soni'    => $tovarlar->count(),
            'soni'           => $tovarlar->sum('jami_soni'),
            'shartnoma_soni' => $baseQuery()->distinct('t.reg_kredit_id')->count('t.reg_kredit_id'),
            'jami_qiymat'    => $tovarlar->sum('jami_qiymat'),
        ];

        if ($request->get('format') === 'excel') {
            return $this->excelResponse("Bonusga berilgan tovarlar $danSana — $gachaSana",
                ['#','Tovar nomi','Birlik','Soni','Shartnomalar soni','Jami qiymat (xarajat)'],
                $tovarlar->values()->map(fn($r,$i) => [
                    $i+1, $r->nomi, $r->birlik, (int)$r->jami_soni, (int)$r->shartnoma_soni, (float)$r->jami_qiymat
                ])->toArray());
        }

        return view('hisobot.bonus_tovarlar', compact('tovarlar','jami','filiallar','filialId','danSana','gachaSana'));
    }

    // Kechikish analizi -- AGING REPORT (paginate=20 bilan optimizatsiya)
    public function kechikishAnaliz(Request $request)
    {
        $filialId  = $this->filialId($request);
        $sana      = $request->sana ?? now()->toDateString();
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();

        // Sanani format tekshiruv (SQL injection xavfsizligi)
        $sana = preg_match('/^\d{4}-\d{2}-\d{2}$/', $sana) ? $sana : now()->toDateString();

        // DATEDIFF SQL fragmenti - to'g'ridan qiymat (? placeholder chalkashmaydi)
        $d = fn($a, $b) => "DATEDIFF('{$sana}',g.tolov_sana) BETWEEN {$a} AND {$b}";
        $d180p  = "DATEDIFF('{$sana}',g.tolov_sana) > 180";
        $diff   = "DATEDIFF('{$sana}',g.tolov_sana)";

        $agingSelect = "
            COALESCE(SUM(CASE WHEN {$d('1','30')}   THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d30,
            COALESCE(SUM(CASE WHEN {$d('31','60')}  THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d60,
            COALESCE(SUM(CASE WHEN {$d('61','90')}  THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d90,
            COALESCE(SUM(CASE WHEN {$d('91','120')} THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d120,
            COALESCE(SUM(CASE WHEN {$d('121','150')} THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d150,
            COALESCE(SUM(CASE WHEN {$d('151','180')} THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d180,
            COALESCE(SUM(CASE WHEN {$d180p}          THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d180p,
            COALESCE(SUM(g.tolov_summa-g.tolangan_summa),0) as jami_kechikkan
        ";

        // Asosiy filter (har safar yangi query yaratadi)
        $baseQuery = fn() => DB::table('grafik as g')
            ->join('reg_kredit as rk', 'rk.id', '=', 'g.reg_kredit_id')
            ->join('mijozlar as m',    'm.id',  '=', 'rk.mijoz_id')
            ->join('filiallar as f',   'f.id',  '=', 'rk.filial_id')
            ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
            ->where('g.holat', '!=', 'tolangan')
            ->whereNotNull('g.tolov_sana')
            ->where('g.tolov_sana', '<', $sana)
            ->whereIn('rk.holat', ['faol', 'muddati_otgan']);

        // 1. JAMI SUMMALARI — bir SQL qator (GROUP BY yo'q — tez!)
        $jamiData = $baseQuery()->selectRaw("
            COALESCE(SUM(CASE WHEN {$d('1','30')}    THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d30,
            COALESCE(SUM(CASE WHEN {$d('31','60')}   THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d60,
            COALESCE(SUM(CASE WHEN {$d('61','90')}   THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d90,
            COALESCE(SUM(CASE WHEN {$d('91','120')}  THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d120,
            COALESCE(SUM(CASE WHEN {$d('121','150')} THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d150,
            COALESCE(SUM(CASE WHEN {$d('151','180')} THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d180,
            COALESCE(SUM(CASE WHEN {$d180p}          THEN g.tolov_summa-g.tolangan_summa ELSE 0 END),0) as d180p,
            COALESCE(SUM(g.tolov_summa-g.tolangan_summa),0) as jami_summa,
            COUNT(DISTINCT rk.id) as soni
        ")->first();

        $jami = [
            'd30'  => (float)($jamiData->d30   ?? 0),
            'd60'  => (float)($jamiData->d60   ?? 0),
            'd90'  => (float)($jamiData->d90   ?? 0),
            'd120' => (float)($jamiData->d120  ?? 0),
            'd150' => (float)($jamiData->d150  ?? 0),
            'd180' => (float)($jamiData->d180  ?? 0),
            'd180p'=> (float)($jamiData->d180p ?? 0),
            'jami' => (float)($jamiData->jami_summa ?? 0),
            'soni' => (int)($jamiData->soni ?? 0),
        ];

        // 2. EXCEL EXPORT — to'liq data kerak
        if ($request->get('format') === 'excel') {
            $allRows = $baseQuery()->selectRaw("
                rk.id, rk.shartnoma_raqam,
                CONCAT(m.familiya,' ',m.ism) as mijoz_ism,
                m.familiya, m.telefon, f.kod as filial_kod,
                rk.kredit_summa, rk.qoldiq_qarz,
                {$agingSelect},
                MIN({$diff}) as min_kun
            ")
                ->groupBy('rk.id','rk.shartnoma_raqam','m.familiya','m.ism','m.telefon','f.kod','rk.kredit_summa','rk.qoldiq_qarz')
                ->having('jami_kechikkan','>', 0)
                ->orderByDesc('jami_kechikkan')
                ->get();

            return $this->excelResponse("Kechikish analizi (Aging) -- {$sana}",
                ['Familiya','Shartnoma','Filial','Telefon','Kredit summa','Qoldiq qarz',
                 '1-30 kun','31-60 kun','61-90 kun','91-120 kun','121-150 kun','151-180 kun','180+ kun','Jami kechikkan'],
                $allRows->map(fn($r) => [
                    $r->mijoz_ism,$r->shartnoma_raqam,$r->filial_kod,$r->telefon,
                    (float)$r->kredit_summa,(float)$r->qoldiq_qarz,
                    (float)$r->d30,(float)$r->d60,(float)$r->d90,(float)$r->d120,
                    (float)$r->d150,(float)$r->d180,(float)$r->d180p,(float)$r->jami_kechikkan
                ])->toArray());
        }

        // 3. SAHIFA — faqat 20 ta qator (tez!)
        $rows = $baseQuery()->selectRaw("
            rk.id, rk.shartnoma_raqam,
            CONCAT(m.familiya,' ',m.ism) as mijoz_ism,
            m.familiya, m.telefon, f.kod as filial_kod,
            rk.kredit_summa, rk.qoldiq_qarz,
            {$agingSelect},
            MIN({$diff}) as min_kun
        ")
            ->groupBy('rk.id','rk.shartnoma_raqam','m.familiya','m.ism','m.telefon','f.kod','rk.kredit_summa','rk.qoldiq_qarz')
            ->having('jami_kechikkan','>', 0)
            ->orderByDesc('jami_kechikkan')
            ->paginate(20)->withQueryString();

        return view('hisobot.kechikish_analiz', compact('rows','jami','filiallar','filialId','sana'));
    }

    // ── 5. Kelayotgan to'lovlar ────────────────────────────────────
    public function kelayotganTulovlar(Request $request)
    {
        $user     = Auth::user();
        $filialId = $this->filialId($request);
        $kunlar   = max(1, min(31, (int)($request->kunlar ?? 7)));
        $xodimId  = $request->xodim_id ? (int)$request->xodim_id : null;

        $tulovlar = Grafik::with(['kredit.mijoz','kredit.filial','kredit.xodim','kredit.joriyXodim'])
            ->when($filialId, fn($q) => $q->whereHas('kredit', fn($k) => $k->where('filial_id',$filialId)))
            ->when($xodimId, fn($q) => $q->whereHas('kredit', function($k) use ($xodimId) {
                $k->where('xodim_id', $xodimId)->orWhere('joriy_xodim_id', $xodimId);
            }))
            ->whereIn('holat',['tolanmagan','qisman'])
            ->whereNotNull('tolov_sana')
            ->whereBetween('tolov_sana',[now()->toDateString(), now()->addDays($kunlar)->toDateString()])
            ->orderBy('tolov_sana')->get();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $xodimlar  = Foydalanuvchi::where('holat','faol')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->orderBy('ism_familiya')->get(['id','ism_familiya']);

        return view('hisobot.kelayotgan', compact('tulovlar','kunlar','filiallar','filialId','xodimId','xodimlar'));
    }

    // ── 6. Konstruktor ─────────────────────────────────────────────

    /** Tezkor davr turi bo'yicha [dan, gacha] sanalarni hisoblaydi. */
    private function sanaOraligiHisobla(string $tur, ?string $maxsusDan = null, ?string $maxsusGacha = null): array
    {
        return match ($tur) {
            'bugun'      => [now()->toDateString(), now()->toDateString()],
            'kecha'      => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'otgan_oy'   => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
            'bu_chorak'  => [now()->startOfQuarter()->toDateString(), now()->toDateString()],
            'bu_yil'     => [now()->startOfYear()->toDateString(), now()->toDateString()],
            'maxsus'     => [$maxsusDan ?: now()->startOfMonth()->toDateString(), $maxsusGacha ?: now()->toDateString()],
            default      => [now()->startOfMonth()->toDateString(), now()->toDateString()], // bu_oy
        };
    }

    public function konstruktor(Request $request)
    {
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $modullar  = $this->modullarRoyxati();
        $natija = null; $modul = null; $danSana = null; $gachaSana = null; $ustunlar = []; $sanaTuri = 'bu_oy'; $guruhlash = null; $shartlar = [];
        $moliyaviyRuxsat  = $this->moliyaviyRuxsatBormi();
        $moliyaviyTurlari = $this->moliyaviyHisobotlarRoyxati();
        return view('hisobot.konstruktor', compact('filiallar','modullar','natija','modul','danSana','gachaSana','ustunlar','sanaTuri','guruhlash','shartlar','moliyaviyRuxsat','moliyaviyTurlari'));
    }

    public function konstruktorHisobot(Request $request)
    {
        $modul     = $request->modul ?? 'kreditlar';
        $filialId  = $this->filialId($request);
        $sanaTuri  = $request->sana_turi ?: 'maxsus';
        [$danSana, $gachaSana] = $this->sanaOraligiHisobla($sanaTuri, $request->dan_sana, $request->gacha_sana);
        $ustunlar  = $request->ustunlar   ?? [];
        $guruhlash = $request->guruhlash ?: null;
        $shartlar  = $request->shartlar ?? [];
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $modullar  = $this->modullarRoyxati();

        $natija = $this->konstruktorSorov($modul, $filialId, $danSana, $gachaSana, $request);

        if (in_array($request->get('format'), ['excel', 'csv'])) {
            $colMap  = $modullar[$modul]['ustunlar'] ?? [];
            $sel     = !empty($ustunlar) ? $ustunlar : array_keys($colMap);
            $headers = array_map(fn($k) => $colMap[$k] ?? $k, $sel);
            $exRows  = array_map(fn($r) => array_map(fn($k) => $r[$k] ?? '', $sel), $natija['rows']);
            return $request->get('format') === 'csv'
                ? $this->csvResponse("konstruktor_{$modul}_{$danSana}_{$gachaSana}", $headers, $exRows)
                : $this->excelResponse("Konstruktor — $modul $danSana/$gachaSana", $headers, $exRows);
        }

        $moliyaviyRuxsat  = $this->moliyaviyRuxsatBormi();
        $moliyaviyTurlari = $this->moliyaviyHisobotlarRoyxati();
        return view('hisobot.konstruktor', compact('filiallar','modullar','natija','modul','danSana','gachaSana','ustunlar','sanaTuri','guruhlash','shartlar','moliyaviyRuxsat','moliyaviyTurlari'));
    }

    public function konstruktorExcel(Request $request)
    {
        $request->merge(['format'=>'excel']);
        return $this->konstruktorHisobot($request);
    }

    public function konstruktorCsv(Request $request)
    {
        $request->merge(['format'=>'csv']);
        return $this->konstruktorHisobot($request);
    }

    // ─── Hisobot shablonlari (saqlash/ochish/o'chirish) ────────────

    public function shablonlarRoyxati(Request $request)
    {
        return response()->json(
            HisobotShablon::where('foydalanuvchi_id', Auth::id())
                ->when($request->modul, fn($q) => $q->where('modul', $request->modul))
                ->orderByDesc('id')->get()
        );
    }

    public function shablonSaqlash(Request $request)
    {
        $data = $request->validate([
            'nomi'       => 'required|string|max:150',
            'modul'      => 'required|string|max:40',
            'ustunlar'   => 'nullable|array',
            'shartlar'   => 'nullable|array',
            'sana_turi'  => 'nullable|string|max:30',
            'dan_sana'   => 'nullable|date',
            'gacha_sana' => 'nullable|date',
            'guruhlash'  => 'nullable|string|max:40',
        ]);

        $shablon = HisobotShablon::create($data + ['foydalanuvchi_id' => Auth::id()]);

        return response()->json(['ok' => true, 'shablon' => $shablon]);
    }

    public function shablonOchirish(HisobotShablon $shablon)
    {
        if ($shablon->foydalanuvchi_id !== Auth::id()) {
            abort(403);
        }
        $shablon->delete();
        return response()->json(['ok' => true]);
    }

    // ─── "Moliyaviy" tab (Balans / Cash Flow / Foyda-zarar / va h.k.) ──

    /** Rol bo'yicha "Moliyaviy" tabga ruxsat: kassir/omborchi ko'rmaydi. */
    private function moliyaviyRuxsatBormi(): bool
    {
        $user = Auth::user();
        return $user->isAdmin() || $user->isHisobchi() || $user->isAuditor()
            || in_array($user->rol, ['menejer']);
    }

    public function moliyaviyHisobotlarRoyxati(): array
    {
        return [
            'foyda_zarar' => ['nomi' => 'Foyda va zarar',            'icon' => 'bi-graph-up-arrow'],
            'balans'      => ['nomi' => 'Balans hisoboti',           'icon' => 'bi-bank'],
            'cash_flow'   => ['nomi' => "Pul oqimi / Cash Flow",     'icon' => 'bi-arrow-left-right'],
            'xarajatlar'  => ['nomi' => 'Xarajatlar hisoboti',       'icon' => 'bi-cash-stack'],
            'daromadlar'  => ['nomi' => 'Daromadlar hisoboti',       'icon' => 'bi-piggy-bank'],
            'filiallar'   => ['nomi' => 'Filiallar kesimida',        'icon' => 'bi-diagram-3'],
            'solishtirma' => ['nomi' => 'Solishtirma hisobot',       'icon' => 'bi-bar-chart-steps'],
        ];
    }

    /** Ikki sana oralig'i uzunligiga teng "oldingi davr"ni hisoblaydi. */
    private function oldingiDavrHisobla(string $tur, string $dan, string $gacha): array
    {
        $danC = \Carbon\Carbon::parse($dan);
        $gachaC = \Carbon\Carbon::parse($gacha);
        $kunlar = $danC->diffInDays($gachaC);

        return match ($tur) {
            'otgan_oy'   => [$danC->copy()->subMonthNoOverflow()->toDateString(), $gachaC->copy()->subMonthNoOverflow()->toDateString()],
            'otgan_yil'  => [$danC->copy()->subYear()->toDateString(), $gachaC->copy()->subYear()->toDateString()],
            'otgan_davr' => [$danC->copy()->subDays($kunlar + 1)->toDateString(), $danC->copy()->subDay()->toDateString()],
            default      => [$danC->copy()->subMonthNoOverflow()->toDateString(), $gachaC->copy()->subMonthNoOverflow()->toDateString()],
        };
    }

    public function moliyaviyHisobot(Request $request)
    {
        if (!$this->moliyaviyRuxsatBormi()) abort(403);

        $turi      = $request->hisobot_turi ?: 'foyda_zarar';
        $filialId  = $this->filialId($request);
        $sanaTuri  = $request->sana_turi ?: 'bu_oy';
        [$dan, $gacha] = $this->sanaOraligiHisobla($sanaTuri, $request->dan_sana, $request->gacha_sana);

        $svc = new FinancialReportService();

        if ($turi === 'solishtirma') {
            $ichkiTuri = $request->ichki_turi ?: 'foyda_zarar';
            $oldingiDavrTuri = $request->oldingi_davr_turi ?: 'otgan_oy';
            [$oldinDan, $oldinGacha] = $oldingiDavrTuri === 'maxsus'
                ? [$request->oldingi_dan_sana, $request->oldingi_gacha_sana]
                : $this->oldingiDavrHisobla($oldingiDavrTuri, $dan, $gacha);
            $natija = $svc->solishtirma($ichkiTuri, $dan, $gacha, $oldinDan, $oldinGacha, $filialId);
        } else {
            $natija = match ($turi) {
                'balans'     => $svc->balans($gacha, $filialId),
                'cash_flow'  => $svc->cashFlow($dan, $gacha, $filialId),
                'xarajatlar' => $svc->xarajatlar($dan, $gacha, $filialId),
                'daromadlar' => $svc->daromadlar($dan, $gacha, $filialId),
                'filiallar'  => $svc->filiallarKesimida($dan, $gacha),
                default      => $svc->foydaZarar($dan, $gacha, $filialId),
            };
        }
        unset($natija['tafsilot']); // tafsilot faqat ichki hisoblash uchun, javobga kerak emas

        if (in_array($request->get('format'), ['excel', 'csv'])) {
            [$headers, $rows] = $this->moliyaviyEksportGaTayyorla($natija, $turi);
            $nomi = $this->moliyaviyHisobotlarRoyxati()[$turi]['nomi'] ?? $turi;
            return $request->get('format') === 'csv'
                ? $this->csvResponse("moliyaviy_{$turi}_{$dan}_{$gacha}", $headers, $rows)
                : $this->excelResponse("$nomi — $dan / $gacha", $headers, $rows);
        }

        return response()->json($natija + ['dan_sana' => $dan, 'gacha_sana' => $gacha]);
    }

    /** Moliyaviy hisobot natijasini (bolim/qator daraxti) tekis Excel/CSV qatorlariga aylantiradi. */
    private function moliyaviyEksportGaTayyorla(array $natija, string $turi): array
    {
        $solishtirma = $turi === 'solishtirma';
        $headers = $solishtirma
            ? ['Bo\'lim', 'Modda', 'Joriy davr', 'Oldingi davr', 'Farq', 'Farq %']
            : ['Bo\'lim', 'Modda', 'Summa'];
        $rows = [];
        foreach ($natija['bolimlar'] as $bolim) {
            foreach ($bolim['qatorlar'] as $q) {
                $rows[] = $solishtirma
                    ? [$bolim['nomi'], $q['nomi'], $q['joriy'], $q['oldingi'], $q['farq'], $q['farq_foizi'] ?? '—']
                    : [$bolim['nomi'], $q['nomi'], $q['summa']];
            }
            if ($bolim['jami'] !== null) {
                $rows[] = $solishtirma
                    ? [$bolim['nomi'], 'JAMI', $bolim['jami']['joriy'], $bolim['jami']['oldingi'], $bolim['jami']['farq'], $bolim['jami']['farq_foizi'] ?? '—']
                    : [$bolim['nomi'], 'JAMI', $bolim['jami']];
            }
        }
        foreach ($natija['yakuniy'] ?? [] as $y) {
            $rows[] = $solishtirma ? ['YAKUNIY', $y['nomi'], $y['summa'], '', '', ''] : ['YAKUNIY', $y['nomi'], $y['summa']];
        }
        return [$headers, $rows];
    }

    // ── 7. Excel export yo'naltiruvchi ─────────────────────────────
    public function excelExport(Request $request, string $tur)
    {
        $request->merge(['format'=>'excel']);
        return match($tur) {
            'portfolio'   => $this->kreditPortfeli($request),
            'chiqarilgan' => $this->chiqarilganKreditlar($request),
            'sotilgan_tovarlar' => $this->sotilganTovarlar($request),
            'bonus_tovarlar' => $this->bonusTovarlar($request),
            'aging'       => $this->kechikishAnaliz($request),
            default       => abort(404),
        };
    }

    // ─── Private: CSV response ─────────────────────────────────────
    private function csvResponse(string $fayl, array $headers, array $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM — Excel'da kirill/lotin harflari to'g'ri ko'rinishi uchun
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, (array) $row, ';');
            }
            fclose($out);
        }, preg_replace('/[^\w]/', '_', strtolower($fayl)) . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ─── Private: Excel HTML response ─────────────────────────────
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
        $html .= 'th{background:#2d6a4f;color:#fff;font-weight:bold;border:1px solid #777;padding:5px 8px;white-space:nowrap;font-size:10pt;}';
        $html .= 'td{border:1px solid #ccc;padding:3px 8px;font-size:9pt;}';
        $html .= 'tr:nth-child(even) td{background:#f0f7f4;}';
        $html .= '.r{text-align:right;mso-number-format:"#,##0";}</style></head><body>';
        $html .= '<h3>' . htmlspecialchars($sarlavha) . '</h3>';
        $html .= '<p style="color:#888;font-size:8pt;margin:0 0 8px 0">NasiyaPro — ' . now()->format('d.m.Y H:i') . '</p>';
        $html .= '<table><thead><tr>';
        foreach ($headers as $h) {
            $html .= '<th>' . htmlspecialchars((string)$h) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ((array)$row as $cell) {
                $isNum = is_numeric($cell) && $cell !== '' && $cell !== null;
                $cls   = $isNum ? ' class="r"' : '';
                $val   = $isNum
                    ? number_format((float)$cell, 0, '.', ' ')
                    : htmlspecialchars((string)($cell ?? ''));
                $html .= "<td$cls>$val</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody><tfoot><tr><td colspan="' . count($headers) . '" ';
        $html .= 'style="background:#e8f5e9;font-size:8pt;color:#555;padding:4px 8px;">';
        $html .= 'Jami: ' . count($rows) . ' qator | NasiyaPro ' . now()->format('d.m.Y H:i');
        $html .= '</td></tr></tfoot></table></body></html>';

        $fn = 'nasiyapro_' . preg_replace('/[^\w]/','_',strtolower($sarlavha)) . '_' . now()->format('Ymd_Hi') . '.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fn . '"',
            'Cache-Control'       => 'max-age=0',
            'Pragma'              => 'no-cache',
        ]);
    }

    // ─── Private: Konstruktor so'rovi ──────────────────────────────
    private function konstruktorSorov(string $modul, ?int $filialId, string $dan, string $gacha, Request $request): array
    {
        $shartlar = $request->shartlar ?? [];

        switch ($modul) {
            case 'kreditlar':
                $q = DB::table('reg_kredit as rk')
                    ->join('mijozlar as m','m.id','=','rk.mijoz_id')
                    ->join('filiallar as f','f.id','=','rk.filial_id')
                    ->when($filialId, fn($q) => $q->where('rk.filial_id',$filialId))
                    ->whereBetween('rk.boshlanish_sana',[$dan,$gacha]);
                if (!empty($shartlar['holat'])) $q->where('rk.holat',$shartlar['holat']);
                if (!empty($shartlar['min_summa'])) $q->where('rk.kredit_summa','>=',(float)$shartlar['min_summa']);
                $rows = $q->selectRaw("rk.shartnoma_raqam, CONCAT(m.familiya,' ',m.ism) as mijoz,
                    m.telefon, f.kod as filial, rk.boshlanish_sana, rk.tugash_sana,
                    rk.kredit_summa, rk.tolov_qilingan, rk.qoldiq_qarz, rk.holat")
                    ->orderByDesc('rk.boshlanish_sana')->limit(5000)->get();
                break;
            case 'tulovlar':
                $q = DB::table('tulovlar as t')
                    ->join('reg_kredit as rk','rk.id','=','t.reg_kredit_id')
                    ->join('mijozlar as m','m.id','=','rk.mijoz_id')
                    ->join('filiallar as f','f.id','=','rk.filial_id')
                    ->leftJoin('tulov_turlari as tt','tt.id','=','t.tulov_turi_id')
                    ->when($filialId, fn($q) => $q->where('rk.filial_id',$filialId))
                    ->whereBetween('t.tolov_sana',[$dan,$gacha]);
                if (!empty($shartlar['min_summa'])) $q->where('t.summa','>=',(float)$shartlar['min_summa']);
                $rows = $q->selectRaw("t.tolov_sana, rk.shartnoma_raqam, CONCAT(m.familiya,' ',m.ism) as mijoz,
                    f.kod as filial, t.summa, tt.nomi as tulov_turi, t.izoh")
                    ->orderByDesc('t.tolov_sana')->limit(5000)->get();
                break;
            case 'mijozlar':
                $q = DB::table('mijozlar as m')
                    ->join('filiallar as f','f.id','=','m.filial_id')
                    ->when($filialId, fn($q) => $q->where('m.filial_id',$filialId));
                if (!empty($shartlar['holat'])) $q->where('m.holat',$shartlar['holat']);
                $rows = $q->selectRaw("CONCAT(m.familiya,' ',m.ism) as mijoz, m.telefon,
                    m.manzil, f.kod as filial, m.holat, m.created_at")
                    ->orderBy('m.familiya')->limit(5000)->get();
                break;
            case 'savdo':
                $nasiya = DB::table('tovarlar as t')
                    ->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
                    ->join('mijozlar as m', 'm.id', '=', 'rk.mijoz_id')
                    ->join('filiallar as f', 'f.id', '=', 'rk.filial_id')
                    ->leftJoin('foydalanuvchilar as x', 'x.id', '=', 'rk.xodim_id')
                    ->leftJoin('tovar_katalog as tk', 'tk.id', '=', 't.tovar_katalog_id')
                    ->leftJoin('tovar_guruhlar as tg', 'tg.id', '=', 'tk.guruh_id')
                    ->where('t.turi', 'kredit')
                    ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
                    ->whereBetween('rk.boshlanish_sana', [$dan, $gacha]);
                if (!empty($shartlar['min_summa'])) $nasiya->where('t.jami_narx', '>=', (float) $shartlar['min_summa']);
                if (!empty($shartlar['xodim_id'])) $nasiya->where('rk.xodim_id', $shartlar['xodim_id']);
                $nasiyaRows = $nasiya->selectRaw("rk.boshlanish_sana as sana, rk.shartnoma_raqam as hujjat_raqam,
                    CONCAT(m.familiya,' ',m.ism) as mijoz, f.kod as filial, t.nomi as tovar_nomi,
                    COALESCE(tg.nomi,'—') as tovar_guruhi, t.soni as miqdor, t.narx as sotish_narxi,
                    t.jami_narx as jami_summa, COALESCE(tk.tan_narx,0) as tan_narx,
                    (t.jami_narx - t.soni * COALESCE(tk.tan_narx,0)) as foyda,
                    'Nasiya' as tolov_turi, COALESCE(x.ism_familiya,'—') as xodim, rk.holat")
                    ->get();

                $pos = DB::table('pos_tafsilot as pt')
                    ->join('pos_sotuv as ps', 'ps.id', '=', 'pt.sotuv_id')
                    ->join('filiallar as f', 'f.id', '=', 'ps.filial_id')
                    ->leftJoin('foydalanuvchilar as x', 'x.id', '=', 'ps.xodim_id')
                    ->leftJoin('tovar_katalog as tk', 'tk.id', '=', 'pt.tovar_id')
                    ->leftJoin('tovar_guruhlar as tg', 'tg.id', '=', 'tk.guruh_id')
                    ->when($filialId, fn($q) => $q->where('ps.filial_id', $filialId))
                    ->whereBetween('ps.sana', [$dan, $gacha]);
                if (!empty($shartlar['min_summa'])) $pos->where('pt.jami_summa', '>=', (float) $shartlar['min_summa']);
                if (!empty($shartlar['xodim_id'])) $pos->where('ps.xodim_id', $shartlar['xodim_id']);
                $posRows = $pos->selectRaw("ps.sana, ps.check_raqam as hujjat_raqam,
                    COALESCE(ps.mijoz_ism,'—') as mijoz, f.kod as filial, COALESCE(tk.nomi,'—') as tovar_nomi,
                    COALESCE(tg.nomi,'—') as tovar_guruhi, pt.miqdor, pt.narx as sotish_narxi, pt.jami_summa as jami_summa,
                    COALESCE(tk.tan_narx,0) as tan_narx,
                    (pt.jami_summa - pt.miqdor * COALESCE(tk.tan_narx,0)) as foyda,
                    ps.tolov_turi, COALESCE(x.ism_familiya,'—') as xodim, ps.holat")
                    ->get();

                $rows = $nasiyaRows->concat($posRows)->sortByDesc('sana')->values();

                $guruhlash = $request->guruhlash;
                if (!empty($guruhlash)) {
                    $guruhKalit = match ($guruhlash) {
                        'kun'          => fn($r) => substr((string) $r->sana, 0, 10),
                        'oy'           => fn($r) => substr((string) $r->sana, 0, 7),
                        'filial'       => fn($r) => $r->filial,
                        'xodim'        => fn($r) => $r->xodim,
                        'tovar'        => fn($r) => $r->tovar_nomi,
                        'tovar_guruhi' => fn($r) => $r->tovar_guruhi,
                        default        => null,
                    };
                    if ($guruhKalit) {
                        $rows = $rows->groupBy($guruhKalit)->map(function ($g, $kalit) use ($guruhlash) {
                            return (object) [
                                $guruhlash    => $kalit,
                                'savdo_soni'  => $g->count(),
                                'miqdor'      => $g->sum('miqdor'),
                                'jami_summa'  => $g->sum('jami_summa'),
                                'tan_narx'    => $g->sum(fn($r) => $r->miqdor * $r->tan_narx),
                                'foyda'       => $g->sum('foyda'),
                            ];
                        })->values();
                    }
                }
                $rows = $rows->take(5000);
                break;
            case 'tovar_tarixi':
                $q = DB::table('stock_ledger as sl')
                    ->join('omborlar as o', 'o.id', '=', 'sl.ombor_id')
                    ->leftJoin('foydalanuvchilar as x', 'x.id', '=', 'sl.xodim_id')
                    ->when($filialId, fn($q) => $q->where('o.filial_id', $filialId))
                    ->whereBetween(DB::raw('DATE(sl.created_at)'), [$dan, $gacha]);
                if (!empty($shartlar['harakat'])) $q->where('sl.harakat', $shartlar['harakat']);
                $rows = $q->selectRaw("sl.created_at as sana, sl.tovar_nomi, o.nomi as ombor, sl.harakat,
                    CASE WHEN sl.harakat IN ('kirim','transfer_in') THEN sl.miqdor ELSE 0 END as kirim,
                    CASE WHEN sl.harakat IN ('chiqim','transfer_out') THEN sl.miqdor ELSE 0 END as chiqim,
                    sl.qoldiq_keyin as qoldiq, sl.tan_narx,
                    CONCAT(sl.manba_tur,' #',sl.manba_id) as hujjat, COALESCE(x.ism_familiya,'—') as xodim, sl.izoh")
                    ->orderByDesc('sl.created_at')->limit(5000)->get();
                break;
            case 'ombor_qoldiq':
                $q = DB::table('ombor_qoldiqlar as oq')
                    ->join('omborlar as o', 'o.id', '=', 'oq.ombor_id')
                    ->join('tovar_katalog as tk', 'tk.id', '=', 'oq.tovar_id')
                    ->leftJoin('tovar_guruhlar as tg', 'tg.id', '=', 'tk.guruh_id')
                    ->when($filialId, fn($q) => $q->where('o.filial_id', $filialId));
                if (!empty($shartlar['nol_qoldiq']) && $shartlar['nol_qoldiq'] === 'yashir') $q->where('oq.miqdor', '>', 0);
                if (!empty($shartlar['kam_qoldiq']) && $shartlar['kam_qoldiq'] === 'ha') $q->whereColumn('oq.miqdor', '<=', 'tk.min_qoldiq')->where('tk.min_qoldiq', '>', 0);
                $rows = $q->selectRaw("tk.barkod as tovar_kodi, tk.nomi as tovar_nomi, COALESCE(tg.nomi,'—') as tovar_guruhi,
                    o.nomi as ombor, oq.miqdor, tk.tan_narx, (oq.miqdor * tk.tan_narx) as jami_tan_narx,
                    tk.sotish_narx, (oq.miqdor * tk.sotish_narx) as jami_sotish_narx, tk.min_qoldiq,
                    CASE WHEN oq.miqdor <= 0 THEN 'Nol' WHEN oq.miqdor <= tk.min_qoldiq AND tk.min_qoldiq > 0 THEN 'Kam' ELSE 'Yetarli' END as holati")
                    ->orderBy('tk.nomi')->limit(5000)->get();
                break;
            default:
                $rows = collect();
        }

        return ['rows' => $rows->map(fn($r) => (array)$r)->toArray(), 'soni' => $rows->count()];
    }

    // ─── Modullar ro'yxati ─────────────────────────────────────────
    public function modullarRoyxati(): array
    {
        return [
            'kreditlar' => [
                'nomi' => 'Kreditlar', 'icon' => 'bi-file-earmark-text', 'sana_tur' => 'boshlanish',
                'ustunlar' => [
                    'shartnoma_raqam' => 'Shartnoma №', 'mijoz' => 'Mijoz', 'telefon' => 'Telefon',
                    'filial' => 'Filial', 'boshlanish_sana' => 'Boshlanish', 'tugash_sana' => 'Tugash',
                    'kredit_summa' => 'Kredit summa', 'tolov_qilingan' => "To'lov qilingan",
                    'qoldiq_qarz' => 'Qoldiq qarz', 'holat' => 'Holat',
                ],
                'shartlar' => [
                    'holat'     => ['nomi' => 'Holat', 'tur' => 'select', 'qiymatlar' => ['faol','yopilgan','muddati_otgan','muzlatilgan']],
                    'min_summa' => ['nomi' => 'Min kredit summa', 'tur' => 'number'],
                ],
            ],
            'tulovlar' => [
                'nomi' => "To'lovlar", 'icon' => 'bi-receipt', 'sana_tur' => "to'lov",
                'ustunlar' => [
                    'tolov_sana' => "To'lov sanasi", 'shartnoma_raqam' => 'Shartnoma',
                    'mijoz' => 'Mijoz', 'filial' => 'Filial', 'summa' => 'Summa',
                    'tulov_turi' => "To'lov turi", 'izoh' => 'Izoh',
                ],
                'shartlar' => [
                    'min_summa' => ['nomi' => "Min to'lov summa", 'tur' => 'number'],
                ],
            ],
            'mijozlar' => [
                'nomi' => 'Mijozlar', 'icon' => 'bi-people', 'sana_tur' => "ro'yxatga olish",
                'ustunlar' => [
                    'mijoz' => 'Mijoz', 'telefon' => 'Telefon', 'manzil' => 'Manzil',
                    'filial' => 'Filial', 'holat' => 'Holat', 'created_at' => "Qo'shilgan",
                ],
                'shartlar' => [
                    'holat' => ['nomi' => 'Holat', 'tur' => 'select', 'qiymatlar' => ['faol','nofaol']],
                ],
            ],
            'savdo' => [
                'nomi' => 'Savdo', 'icon' => 'bi-cart-check', 'sana_tur' => 'savdo',
                'ustunlar' => [
                    'sana' => 'Sana', 'hujjat_raqam' => 'Hujjat №', 'mijoz' => 'Mijoz', 'filial' => 'Filial',
                    'tovar_nomi' => 'Tovar nomi', 'tovar_guruhi' => 'Tovar guruhi', 'miqdor' => 'Miqdor',
                    'sotish_narxi' => 'Sotish narxi', 'jami_summa' => 'Jami summa', 'tan_narx' => 'Tannarx',
                    'foyda' => 'Foyda', 'tolov_turi' => "To'lov turi", 'xodim' => 'Xodim', 'holat' => 'Holat',
                ],
                'shartlar' => [
                    'min_summa' => ['nomi' => 'Min summa', 'tur' => 'number'],
                ],
                'guruhlash_qiymatlari' => ['kun' => 'Kun bo\'yicha', 'oy' => 'Oy bo\'yicha', 'filial' => 'Filial bo\'yicha', 'xodim' => 'Xodim bo\'yicha', 'tovar' => 'Tovar bo\'yicha', 'tovar_guruhi' => 'Tovar guruhi bo\'yicha'],
            ],
            'tovar_tarixi' => [
                'nomi' => 'Tovarlar tarixi', 'icon' => 'bi-clock-history', 'sana_tur' => 'harakat',
                'ustunlar' => [
                    'sana' => 'Sana', 'tovar_nomi' => 'Tovar nomi', 'ombor' => 'Ombor', 'harakat' => 'Harakat turi',
                    'kirim' => 'Kirim', 'chiqim' => 'Chiqim', 'qoldiq' => 'Qoldiq', 'tan_narx' => 'Tannarx',
                    'hujjat' => 'Hujjat', 'xodim' => 'Mas\'ul xodim', 'izoh' => 'Izoh',
                ],
                'shartlar' => [
                    'harakat' => ['nomi' => 'Harakat turi', 'tur' => 'select', 'qiymatlar' => ['kirim','chiqim','transfer_in','transfer_out']],
                ],
            ],
            'ombor_qoldiq' => [
                'nomi' => "Ombor qoldig'i", 'icon' => 'bi-boxes', 'sana_tur' => 'joriy',
                'ustunlar' => [
                    'tovar_kodi' => 'Tovar kodi', 'tovar_nomi' => 'Tovar nomi', 'tovar_guruhi' => 'Tovar guruhi',
                    'ombor' => 'Ombor', 'miqdor' => 'Miqdor', 'tan_narx' => "O'rtacha tannarx",
                    'jami_tan_narx' => 'Jami tannarx summasi', 'sotish_narx' => 'Sotish narxi',
                    'jami_sotish_narx' => 'Jami sotish summasi', 'min_qoldiq' => 'Minimal qoldiq', 'holati' => 'Holati',
                ],
                'shartlar' => [
                    'nol_qoldiq' => ['nomi' => "Nol qoldiqni yashirish", 'tur' => 'select', 'qiymatlar' => ['yashir','korsat']],
                    'kam_qoldiq' => ['nomi' => "Faqat kam qoldiqlar", 'tur' => 'select', 'qiymatlar' => ['ha','yoq']],
                ],
            ],
        ];
    }
    // ── Transfer hisobotlari ────────────────────────────────────────
    public function transferHisobot(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $this->filialId($request);
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();
        $tur       = $request->tur ?? 'tovar';

        $tovarTransferlar = FilialTransfer::with(['fromFilial','toFilial','xodim','tafsilot'])
            ->when($filialId, fn($q) => $q->where('from_filial_id',$filialId)->orWhere('to_filial_id',$filialId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$danSana, $gachaSana])
            ->when($request->holat, fn($q) => $q->where('holat',$request->holat))
            ->latest()->get();

        $kassaTransferlar = KassaTransfer::with(['fromFilial','toFilial','fromKassa','toKassa','xodim'])
            ->when($filialId, fn($q) => $q->where('from_filial_id',$filialId)->orWhere('to_filial_id',$filialId))
            ->whereBetween(DB::raw('DATE(created_at)'), [$danSana, $gachaSana])
            ->when($request->holat, fn($q) => $q->where('holat',$request->holat))
            ->latest()->get();

        $xodimTayinlash = ShartnomaxodimTarixi::with([
                'shartnoma:id,shartnoma_raqam',
                'eskiXodim:id,ism_familiya',
                'yangiXodim:id,ism_familiya',
                'ozgartirgan:id,ism_familiya',
            ])
            ->whereBetween(DB::raw('DATE(created_at)'), [$danSana, $gachaSana])
            ->latest()->get();

        $filialKochirish = ShartnomaiFilialTarixi::with([
                'shartnoma:id,shartnoma_raqam',
                'eskiFilial:id,kod,nomi',
                'yangiFilial:id,kod,nomi',
                'ozgartirgan:id,ism_familiya',
            ])
            ->whereBetween(DB::raw('DATE(created_at)'), [$danSana, $gachaSana])
            ->latest()->get();

        // To'lov turlari bo'yicha statistika
        $tulovTurlari = TulovTuri::withCount(['tulovlar as jami_count' => function($q) use ($danSana,$gachaSana) {
                $q->whereBetween(DB::raw('DATE(tolov_sana)'), [$danSana, $gachaSana]);
            }])
            ->withSum(['tulovlar as jami_summa' => function($q) use ($danSana,$gachaSana) {
                $q->whereBetween(DB::raw('DATE(tolov_sana)'), [$danSana, $gachaSana]);
            }], 'summa')
            ->orderBy('is_legacy')
            ->orderBy('sort_order')
            ->get();

        $filiallar = Filial::faol()->get(['id','nomi','kod']);

        return view('hisobot.transfer', compact(
            'tovarTransferlar','kassaTransferlar',
            'xodimTayinlash','filialKochirish',
            'tulovTurlari','filiallar',
            'danSana','gachaSana','tur'
        ));
    }
}