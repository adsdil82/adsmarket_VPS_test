<?php
namespace App\Http\Controllers;

use App\Models\ChiqimTafsilot;
use App\Models\Filial;
use App\Models\OmbordanChiqim;
use App\Models\TovarKatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChiqimController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $danSana   = $request->dan_sana ?: now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?: now()->toDateString();
        $isNasiyaBonus = $request->sabab === 'nasiya_bonus';

        $chiqimlar = null;
        $bonusRows = null;

        if ($isNasiyaBonus) {
            $bonusRows = $this->nasiyaBonusSorovi($filialId, $danSana, $gachaSana)
                ->select('t.*', 'rk.shartnoma_raqam', 'rk.boshlanish_sana')
                ->orderByDesc('rk.boshlanish_sana')
                ->paginate(20)->withQueryString();
        } else {
            $chiqimlar = OmbordanChiqim::with(['filial', 'xodim'])
                ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                ->when($request->sabab, fn($q) => $q->where('sabab', $request->sabab))
                ->when($danSana, fn($q) => $q->whereDate('sana', '>=', $danSana))
                ->when($gachaSana, fn($q) => $q->whereDate('sana', '<=', $gachaSana))
                ->orderByDesc('sana')->orderByDesc('id')
                ->paginate(20)->withQueryString();
        }

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $sabablar  = OmbordanChiqim::$sabablar;

        // Chap paneldagi "chiqim turlari" ro'yxati — har bir sabab bo'yicha soni va summasi
        // (joriy sana filtriga mos holda hisoblanadi)
        $sababSoni = OmbordanChiqim::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($danSana, fn($q) => $q->whereDate('sana', '>=', $danSana))
            ->when($gachaSana, fn($q) => $q->whereDate('sana', '<=', $gachaSana))
            ->selectRaw('sabab, COUNT(*) as soni, COALESCE(SUM(umumiy_summa),0) as summa')
            ->groupBy('sabab')->get()->keyBy('sabab');

        // Nasiya shartnomalariga biriktirilgan bonus tovarlar (avtomatik, ombordan_chiqim
        // jadvalidagi qo'lda yozuvlardan farqli — bu shartnoma yaratilishida tovarlar.turi='bonus'
        // sifatida saqlangan, alohida hujjatlashtirilmaydigan tovarlar).
        $nasiyaBonus = $this->nasiyaBonusSorovi($filialId, $danSana, $gachaSana)
            ->selectRaw('COUNT(*) as soni, COALESCE(SUM(t.jami_narx),0) as summa')
            ->first();

        return view('ombor.chiqim.index', compact(
            'chiqimlar', 'bonusRows', 'isNasiyaBonus', 'filiallar', 'filialId',
            'sabablar', 'sababSoni', 'danSana', 'gachaSana', 'nasiyaBonus'
        ));
    }

    /** Excelga eksport — joriy filtrlarga mos chiqimlar yoki nasiya-bonus ro'yxati */
    public function excelExport(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $danSana   = $request->dan_sana ?: now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?: now()->toDateString();

        if ($request->sabab === 'nasiya_bonus') {
            $rows = $this->nasiyaBonusSorovi($filialId, $danSana, $gachaSana)
                ->select('t.*', 'rk.shartnoma_raqam', 'rk.boshlanish_sana')
                ->orderByDesc('rk.boshlanish_sana')->limit(5000)->get();

            return $this->excelResponse('Nasiya shartnomalariga biriktirilgan bonus',
                ['#', 'Shartnoma', 'Sana', 'Tovar nomi', 'Miqdor', 'Narx', 'Jami'],
                $rows->values()->map(fn($r, $i) => [
                    $i + 1, $r->shartnoma_raqam, $r->boshlanish_sana, $r->nomi,
                    (float) $r->soni, (float) $r->narx, (float) $r->jami_narx,
                ])->toArray());
        }

        $chiqimlar = OmbordanChiqim::with(['filial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->sabab, fn($q) => $q->where('sabab', $request->sabab))
            ->when($danSana, fn($q) => $q->whereDate('sana', '>=', $danSana))
            ->when($gachaSana, fn($q) => $q->whereDate('sana', '<=', $gachaSana))
            ->orderByDesc('sana')->orderByDesc('id')->limit(5000)->get();

        $sabablar = OmbordanChiqim::$sabablar;

        return $this->excelResponse('Tovar chiqim',
            ['#', 'Sana', 'Sabab', 'Xodim', 'Filial', 'Summa', 'Holat'],
            $chiqimlar->values()->map(fn($c, $i) => [
                $i + 1, $c->sana->format('d.m.Y'), $sabablar[$c->sabab] ?? $c->sabab,
                $c->xodim?->ism_familiya, $c->filial?->kod, (float) $c->umumiy_summa, $c->holat,
            ])->toArray());
    }

    /**
     * Nasiya shartnomalariga biriktirilgan bonus tovarlar so'rovi (index/excel'da qayta ishlatiladi).
     * Select qo'shilmagan — chaqiruvchi kerakli ustunlarni (yoki agregatni) o'zi belgilaydi.
     */
    private function nasiyaBonusSorovi(?int $filialId, ?string $danSana, ?string $gachaSana)
    {
        return DB::table('tovarlar as t')
            ->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
            ->where('t.turi', 'bonus')
            ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
            ->when($danSana, fn($q) => $q->whereDate('rk.boshlanish_sana', '>=', $danSana))
            ->when($gachaSana, fn($q) => $q->whereDate('rk.boshlanish_sana', '<=', $gachaSana));
    }

    /** Excel HTML response (hisobot moduli bilan bir xil uslub) */
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
        $html .= 'h3{color:#7f1d1d;font-size:13pt;margin:0 0 6px 0;}';
        $html .= 'table{border-collapse:collapse;width:100%;}';
        $html .= 'th{background:#7f1d1d;color:#fff;font-weight:bold;border:1px solid #777;padding:5px 8px;white-space:nowrap;font-size:10pt;}';
        $html .= 'td{border:1px solid #ccc;padding:3px 8px;font-size:9pt;}';
        $html .= 'tr:nth-child(even) td{background:#fef2f2;}';
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
        $html .= 'style="background:#fef2f2;font-size:8pt;color:#555;padding:4px 8px;">';
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

    public function create()
    {
        $user     = Auth::user();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $tovarlar  = TovarKatalog::faol()->with('guruh')->orderBy('nomi')->get();
        $sabablar  = OmbordanChiqim::$qoldaSabablar;
        return view('ombor.chiqim.create', compact('filiallar', 'tovarlar', 'sabablar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'sana'      => 'required|date',
            'sabab'     => 'required|in:' . implode(',', array_keys(OmbordanChiqim::$qoldaSabablar)),
            'izoh'      => 'nullable|string',
            'tovarlar'  => 'required|array|min:1',
            'tovarlar.*.tovar_id' => 'required|exists:tovar_katalog,id',
            'tovarlar.*.miqdor'   => 'required|numeric|min:0.001',
            'tovarlar.*.narx'     => 'required|numeric|min:0',
        ]);

        // Qoldiq tekshiruvi
        foreach ($request->tovarlar as $q) {
            $tovar = TovarKatalog::find($q['tovar_id']);
            if ($tovar->qoldiq < $q['miqdor']) {
                return back()->withErrors(["Tovar «{$tovar->nomi}»: omborda {$tovar->qoldiq} {$tovar->birlik} bor, {$q['miqdor']} so'raldi."])->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $jami = 0;
            $chiqim = OmbordanChiqim::create([
                'filial_id'    => $request->filial_id,
                'xodim_id'     => Auth::id(),
                'sana'         => $request->sana,
                'sabab'        => $request->sabab,
                'izoh'         => $request->izoh,
                'holat'        => 'tasdiqlangan',
                'umumiy_summa' => 0,
            ]);

            foreach ($request->tovarlar as $q) {
                $summa = $q['miqdor'] * $q['narx'];
                $jami += $summa;
                ChiqimTafsilot::create([
                    'chiqim_id'  => $chiqim->id,
                    'tovar_id'   => $q['tovar_id'],
                    'miqdor'     => $q['miqdor'],
                    'narx'       => $q['narx'],
                    'jami_summa' => $summa,
                ]);
                TovarKatalog::find($q['tovar_id'])->decrement('qoldiq', $q['miqdor']);
            }

            $chiqim->update(['umumiy_summa' => $jami]);
        });

        return redirect()->route('chiqim.index')->with('muvaffaqiyat', 'Chiqim saqlandi va ombor yangilandi.');
    }

    public function show(OmbordanChiqim $chiqim)
    {
        $chiqim->load(['filial', 'xodim', 'tafsilot.tovar.guruh']);
        return view('ombor.chiqim.show', compact('chiqim'));
    }

    /** Hujjat (yuk xati / akt / schyot-faktura) — PDF yuklab olish */
    public function hujjat(OmbordanChiqim $chiqim, string $tur)
    {
        $turlar = ['yuk_xati', 'akt', 'schyot'];
        if (!in_array($tur, $turlar)) abort(404);

        $chiqim->load(['filial', 'xodim', 'tafsilot.tovar.guruh']);
        $pdf = Pdf::loadView('ombor.chiqim.hujjatlar.' . $tur, compact('chiqim'))->setPaper('A4', 'portrait');
        return $pdf->stream('chiqim-' . $chiqim->id . '-' . $tur . '.pdf');
    }

    /** Hujjatni HTML ko'rinishda (modal/iframe uchun) ko'rsatish */
    public function hujjatHtml(OmbordanChiqim $chiqim, string $tur)
    {
        $turlar = ['yuk_xati', 'akt', 'schyot'];
        if (!in_array($tur, $turlar)) abort(404);

        $chiqim->load(['filial', 'xodim', 'tafsilot.tovar.guruh']);
        return view('ombor.chiqim.hujjatlar.' . $tur, compact('chiqim'));
    }
}
