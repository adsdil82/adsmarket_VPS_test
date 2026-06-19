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

        $kunlikTulovlar = Tulov::select(
                DB::raw('DATE(tolov_sana) as sana'),
                DB::raw('SUM(summa) as jami'),
                DB::raw('COUNT(*) as soni'))
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->sanada(now()->subDays(29)->toDateString(), now()->toDateString())
            ->groupBy('sana')->orderBy('sana')->get();

        $tulovTurlariStatistika = Tulov::select('tulov_turi_id',
                DB::raw('SUM(summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('tulovTuri')
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->sanada($danSana, $gachaSana)
            ->groupBy('tulov_turi_id')->orderByDesc('jami')->get();

        $xodimlarStatistika = Tulov::select('xodim_id',
                DB::raw('SUM(summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('xodim')
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->sanada($danSana, $gachaSana)
            ->groupBy('xodim_id')->orderByDesc('jami')->get();

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
    public function konstruktor(Request $request)
    {
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $modullar  = $this->modullarRoyxati();
        $natija = null; $modul = null; $danSana = null; $gachaSana = null; $ustunlar = [];
        return view('hisobot.konstruktor', compact('filiallar','modullar','natija','modul','danSana','gachaSana','ustunlar'));
    }

    public function konstruktorHisobot(Request $request)
    {
        $modul     = $request->modul ?? 'kreditlar';
        $filialId  = $this->filialId($request);
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();
        $ustunlar  = $request->ustunlar   ?? [];
        $filiallar = Auth::user()->isAdmin() ? Filial::faol()->get() : collect();
        $modullar  = $this->modullarRoyxati();

        $natija = $this->konstruktorSorov($modul, $filialId, $danSana, $gachaSana, $request);

        if ($request->get('format') === 'excel') {
            $colMap  = $modullar[$modul]['ustunlar'] ?? [];
            $sel     = !empty($ustunlar) ? $ustunlar : array_keys($colMap);
            $headers = array_map(fn($k) => $colMap[$k] ?? $k, $sel);
            $exRows  = array_map(fn($r) => array_map(fn($k) => $r[$k] ?? '', $sel), $natija['rows']);
            return $this->excelResponse("Konstruktor — $modul $danSana/$gachaSana", $headers, $exRows);
        }

        return view('hisobot.konstruktor', compact('filiallar','modullar','natija','modul','danSana','gachaSana','ustunlar'));
    }

    public function konstruktorExcel(Request $request)
    {
        $request->merge(['format'=>'excel']);
        return $this->konstruktorHisobot($request);
    }

    // ── 7. Excel export yo'naltiruvchi ─────────────────────────────
    public function excelExport(Request $request, string $tur)
    {
        $request->merge(['format'=>'excel']);
        return match($tur) {
            'portfolio'   => $this->kreditPortfeli($request),
            'chiqarilgan' => $this->chiqarilganKreditlar($request),
            'aging'       => $this->kechikishAnaliz($request),
            default       => abort(404),
        };
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