<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Taminotchi;
use App\Models\TaminotKirim;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * "Tovar kirim" sahifasi — ta'minotchilar orqali kelgan tovarlar (taminot_kirimlar)
 * asosida ishlaydi. Haqiqiy kirim yaratish/tahrirlash TaminotchiController orqali
 * amalga oshiriladi (bu yerda faqat ta'minotchi tanlab, o'sha formaga yo'naltiriladi).
 */
class KirimController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $danSana   = $request->dan_sana ?: now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?: now()->toDateString();
        $taminotchiId = $request->taminotchi_id;

        $kirimlar = TaminotKirim::with(['filial', 'xodim', 'taminotchi'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($taminotchiId, fn($q) => $q->where('taminotchi_id', $taminotchiId))
            ->when($danSana, fn($q) => $q->whereDate('kirim_sana', '>=', $danSana))
            ->when($gachaSana, fn($q) => $q->whereDate('kirim_sana', '<=', $gachaSana))
            ->orderByDesc('kirim_sana')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        // Chap paneldagi "ta'minotchilar" ro'yxati — soni va summasi
        $taminotchiSoni = TaminotKirim::with('taminotchi:id,nomi')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($danSana, fn($q) => $q->whereDate('kirim_sana', '>=', $danSana))
            ->when($gachaSana, fn($q) => $q->whereDate('kirim_sana', '<=', $gachaSana))
            ->selectRaw('taminotchi_id, COUNT(*) as soni, COALESCE(SUM(jami_summa),0) as summa')
            ->groupBy('taminotchi_id')->orderByDesc('soni')
            ->get()
            ->each(fn($r) => $r->nomi = Taminotchi::find($r->taminotchi_id)?->nomi ?? 'Noma\'lum');

        // Statistika kartalari
        $bugunJami = TaminotKirim::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereDate('kirim_sana', today())->sum('jami_summa');
        $oyJami = TaminotKirim::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereYear('kirim_sana', now()->year)->whereMonth('kirim_sana', now()->month)->sum('jami_summa');
        $jamiYozuvlar = TaminotKirim::when($filialId, fn($q) => $q->where('filial_id', $filialId))->count();

        return view('ombor.kirim.index', compact(
            'kirimlar', 'filiallar', 'filialId', 'danSana', 'gachaSana', 'taminotchiId',
            'taminotchiSoni', 'bugunJami', 'oyJami', 'jamiYozuvlar'
        ));
    }

    /** Excelga eksport — joriy filtrlarga mos kirimlar */
    public function excelExport(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $danSana   = $request->dan_sana ?: now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?: now()->toDateString();

        $kirimlar = TaminotKirim::with(['filial', 'xodim', 'taminotchi'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->taminotchi_id, fn($q) => $q->where('taminotchi_id', $request->taminotchi_id))
            ->when($danSana, fn($q) => $q->whereDate('kirim_sana', '>=', $danSana))
            ->when($gachaSana, fn($q) => $q->whereDate('kirim_sana', '<=', $gachaSana))
            ->orderByDesc('kirim_sana')->orderByDesc('id')->limit(5000)->get();

        return $this->excelResponse('Tovar kirim',
            ['#', 'Sana', 'Ta\'minotchi', 'Hujjat #', 'Xodim', 'Filial', 'Jami summa', 'To\'langan', 'Qoldiq', 'Holat'],
            $kirimlar->values()->map(fn($k, $i) => [
                $i + 1, $k->kirim_sana->format('d.m.Y'), $k->taminotchi?->nomi ?: '—', $k->hujjat_raqam ?: '—',
                $k->xodim?->ism_familiya, $k->filial?->kod, (float) $k->jami_summa, (float) $k->tolangan,
                (float) $k->qoldiq, $k->holat,
            ])->toArray());
    }

    /** "Yangi kirim" — avval ta'minotchi tanlash sahifasi */
    public function create(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $taminotchilar = Taminotchi::faol()
            ->when($filialId, fn($q) => $q->filialda($filialId))
            ->orderBy('nomi')->get();

        return view('ombor.kirim.tanlash', compact('taminotchilar'));
    }

    /** Hujjat (kirim varaqasi / akt / schyot-faktura) — PDF yuklab olish */
    public function hujjat(TaminotKirim $kirim, string $tur)
    {
        $turlar = ['kirim_varaqasi', 'akt', 'schyot'];
        if (!in_array($tur, $turlar)) abort(404);

        $kirim->load(['filial', 'xodim', 'taminotchi', 'qatorlar.tovar']);
        $pdf = Pdf::loadView('ombor.kirim.hujjatlar.' . $tur, compact('kirim'))->setPaper('A4', 'portrait');
        return $pdf->stream('kirim-' . $kirim->id . '-' . $tur . '.pdf');
    }

    /** Hujjatni HTML ko'rinishda (modal/iframe uchun) ko'rsatish */
    public function hujjatHtml(TaminotKirim $kirim, string $tur)
    {
        $turlar = ['kirim_varaqasi', 'akt', 'schyot'];
        if (!in_array($tur, $turlar)) abort(404);

        $kirim->load(['filial', 'xodim', 'taminotchi', 'qatorlar.tovar']);
        return view('ombor.kirim.hujjatlar.' . $tur, compact('kirim'));
    }

    /** Excel HTML response (boshqa modullar bilan bir xil uslub) */
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
        $html .= 'h3{color:#14532d;font-size:13pt;margin:0 0 6px 0;}';
        $html .= 'table{border-collapse:collapse;width:100%;}';
        $html .= 'th{background:#15803d;color:#fff;font-weight:bold;border:1px solid #777;padding:5px 8px;white-space:nowrap;font-size:10pt;}';
        $html .= 'td{border:1px solid #ccc;padding:3px 8px;font-size:9pt;}';
        $html .= 'tr:nth-child(even) td{background:#f0fdf4;}';
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
        $html .= 'style="background:#f0fdf4;font-size:8pt;color:#555;padding:4px 8px;">';
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
}
