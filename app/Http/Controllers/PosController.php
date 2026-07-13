<?php
namespace App\Http\Controllers;

use App\Models\ChiqimTafsilot;
use App\Models\Filial;
use App\Models\KassaTransfer;
use App\Models\OmbordanChiqim;
use App\Models\PosSmena;
use App\Models\PosSotuv;
use App\Models\PosTafsilot;
use App\Models\PosTolovUsuli;
use App\Models\TovarGuruh;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(
        private \App\Services\TulovService $tulovService,
        private \App\Services\StockService $stockService,
    ) {}

    public function index()
    {
        $user     = Auth::user();
        $filialId = $user->filial_id ?? Filial::first()->id;

        // Smena majburiy — ochiq smena bo'lmasa, savdo ekrani o'rniga smena
        // ochish formasiga yo'naltiramiz.
        $smena = PosSmenaController::joriy($filialId);
        if (!$smena) {
            return redirect()->route('pos.smena.ochish-forma');
        }

        $guruhlar = TovarGuruh::faol()->withCount(['tovarlar' => fn($q) => $q->where('holat','faol')->where('qoldiq','>',0)])->orderBy('nomi')->get();

        // Bugungi statistika
        $bugun_sotuv  = PosSotuv::where('filial_id', $filialId)->whereDate('sana', today())->where('holat','tugallangan')->sum('jami_tolov');
        $bugun_checklar = PosSotuv::where('filial_id', $filialId)->whereDate('sana', today())->where('holat','tugallangan')->count();

        $tolovUsullari = PosTolovUsuli::faol()->where('filial_id', $filialId)->orderBy('tartib')->orderBy('nomi')->get();

        return view('ombor.pos.index', compact('guruhlar', 'filialId', 'bugun_sotuv', 'bugun_checklar', 'smena', 'tolovUsullari'));
    }

    /** Ajax: tovarlarni qidirish/yuklash — FAQAT shu filialning o'z ombor qoldig'i bo'yicha. */
    public function tovarlar(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->filial_id ?? $request->filial_id ?? Filial::first()?->id;
        $ombor    = $filialId ? $this->stockService->asosiyOmbor($filialId) : null;

        $tovarlar = TovarKatalog::faol()
            ->when($ombor, fn($q) => $q->whereHas('omborQoldiqlar', fn($qq) =>
                $qq->where('ombor_id', $ombor->id)->where('miqdor', '>', 0)
            ), fn($q) => $q->whereRaw('1=0'))
            ->when($request->guruh_id, fn($q) => $q->where('guruh_id', $request->guruh_id))
            ->when($request->qidiruv,  fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('nomi', 'like', "%{$request->qidiruv}%")
                   ->orWhere('barkod', $request->qidiruv)
                   ->orWhereHas('barkodlar', fn($qb) => $qb->where('barkod', $request->qidiruv));
            }))
            ->with(['guruh:id,nomi', 'barkodlar:tovar_id,barkod'])
            ->orderBy('nomi')
            ->limit(50)
            ->get(['id','nomi','barkod','sotish_narx','qoldiq','birlik','guruh_id']);

        // Ko'rsatiladigan qoldiqni ham SHU OMBOR bo'yicha almashtiramiz
        // (tovar_katalog.qoldiq — kompaniya bo'yicha jami, chalg'itmasligi uchun).
        // Barcha barkodlar (asosiy + qo'shimcha) — frontend skanerlashda TENGLIK
        // tekshiruvini shu ro'yxatga qarab qiladi (multi-barkod).
        $tovarlar->each(function ($t) use ($ombor) {
            if ($ombor) {
                $t->qoldiq = $this->stockService->qoldiq($ombor->id, $t->id);
            }
            $t->barkodlar_royxati = $t->barkodlar->pluck('barkod')->push($t->barkod)->filter()->values();
        });

        return response()->json($tovarlar);
    }

    /** POS savdoni saqlash */
    public function store(Request $request)
    {
        $request->validate([
            'filial_id'   => 'required|exists:filiallar,id',
            'tolov_turi'  => 'required|in:naqd,plastik,aralash',
            'tolov_usuli_id' => 'nullable|exists:pos_tolov_usullari,id',
            'naqd_summa'  => 'nullable|numeric|min:0',
            'plastik_summa'=> 'nullable|numeric|min:0',
            'chegirma'    => 'nullable|numeric|min:0',
            'mijoz_ism'   => 'nullable|string|max:200',
            'tolov_izoh'  => 'nullable|string|max:500',
            'tovarlar'    => 'required|array|min:1',
            'tovarlar.*.tovar_id' => 'required|exists:tovar_katalog,id',
            'tovarlar.*.miqdor'   => 'required|numeric|min:0.001',
            'tovarlar.*.narx'     => 'required|numeric|min:0',
        ]);

        // Smena majburiy — ochiq smenasiz sotuv qilinmaydi.
        $smena = PosSmenaController::joriy((int) $request->filial_id);
        if (!$smena) {
            return response()->json(['xato' => "Ochiq smena topilmadi. Avval smenani oching."], 422);
        }

        // Qoldiq tekshiruvi — endi SHU FILIALNING o'z ombori bo'yicha (global
        // emas), boshqa filialning qoldig'i bu yerda sotilishiga yo'l qo'yilmaydi.
        $ombor = $this->stockService->asosiyOmbor($request->filial_id);
        if (!$ombor) {
            return response()->json(['xato' => "Bu filial uchun ombor topilmadi."], 422);
        }
        foreach ($request->tovarlar as $q) {
            $tovar = TovarKatalog::find($q['tovar_id']);
            $mavjud = $this->stockService->qoldiq($ombor->id, $q['tovar_id']);
            if ($mavjud < $q['miqdor']) {
                return response()->json(['xato' => "«{$tovar->nomi}»: «{$ombor->nomi}» omborida {$mavjud} {$tovar->birlik} bor"], 422);
            }
        }

        $sotuv = DB::transaction(function () use ($request, $ombor, $smena) {
            $umumiy = collect($request->tovarlar)->sum(fn($q) => $q['miqdor'] * $q['narx']);
            $chegirma = (float)($request->chegirma ?? 0);
            $jami = $umumiy - $chegirma;

            $sotuv = PosSotuv::create([
                'filial_id'      => $request->filial_id,
                'smena_id'       => $smena->id,
                'xodim_id'       => Auth::id(),
                'sana'           => today(),
                'check_raqam'    => PosSotuv::yangiCheckRaqam($request->filial_id),
                'umumiy_summa'   => $umumiy,
                'chegirma'       => $chegirma,
                'jami_tolov'     => $jami,
                'tolov_turi'     => $request->tolov_turi,
                'tolov_usuli_id' => $request->tolov_usuli_id,
                'naqd_summa'     => $request->naqd_summa ?? 0,
                'plastik_summa'  => $request->plastik_summa ?? 0,
                'qayta_pul'      => max(0, ($request->naqd_summa ?? 0) - $jami),
                'mijoz_ism'      => $request->mijoz_ism,
                'tolov_izoh'     => $request->tolov_izoh,
                'holat'          => 'tugallangan',
            ]);

            // Chiqim yaratish
            $chiqim = OmbordanChiqim::create([
                'filial_id'    => $request->filial_id,
                'xodim_id'     => Auth::id(),
                'sana'         => today(),
                'sabab'        => 'naqd_sotish',
                'umumiy_summa' => $jami,
                'izoh'         => "POS #{$sotuv->check_raqam}",
                'holat'        => 'tasdiqlangan',
            ]);

            foreach ($request->tovarlar as $q) {
                $summa = $q['miqdor'] * $q['narx'];
                PosTafsilot::create([
                    'sotuv_id'   => $sotuv->id,
                    'tovar_id'   => $q['tovar_id'],
                    'miqdor'     => $q['miqdor'],
                    'narx'       => $q['narx'],
                    'chegirma'   => 0,
                    'jami_summa' => $summa,
                ]);
                ChiqimTafsilot::create([
                    'chiqim_id'  => $chiqim->id,
                    'tovar_id'   => $q['tovar_id'],
                    'miqdor'     => $q['miqdor'],
                    'narx'       => $q['narx'],
                    'jami_summa' => $summa,
                ]);
                $this->stockService->chiqim(
                    $ombor->id, $q['tovar_id'], (float) $q['miqdor'],
                    manbaTur: 'pos_sotuv', manbaId: $sotuv->id,
                    izoh: "POS savdo #{$sotuv->check_raqam}", harakat: 'chiqim',
                );
            }

            return $sotuv;
        });

        // Naqd/plastik qabul qilingan pulni "Pul oqimlari"ga avtomatik kirim
        // sifatida yozish (CF-1300 — Naqd savdo POS). Naqd uchun mijozga
        // qaytarilgan pul (qayta_pul) ayiriladi — kassada qoladigan SOF
        // summa yoziladi.
        $naqdSofSumma = max(0, (float) $sotuv->naqd_summa - (float) $sotuv->qayta_pul);
        if ($naqdSofSumma > 0) {
            $this->tulovService->pulOqimigaYozKassaTuri(
                filialId: $sotuv->filial_id,
                kassaTuri: 'naqd',
                summa: $naqdSofSumma,
                sana: $sotuv->sana->toDateString(),
                kategoriyaKodi: 'CF-1300',
                izoh: "POS savdo #{$sotuv->check_raqam}" . ($sotuv->mijoz_ism ? " ({$sotuv->mijoz_ism})" : ''),
                manbaTur: 'pos_sotuv_naqd',
                manbaId: $sotuv->id,
            );
        }
        if ((float) $sotuv->plastik_summa > 0) {
            $this->tulovService->pulOqimigaYozKassaTuri(
                filialId: $sotuv->filial_id,
                kassaTuri: 'terminal',
                summa: (float) $sotuv->plastik_summa,
                sana: $sotuv->sana->toDateString(),
                kategoriyaKodi: 'CF-1300',
                izoh: "POS savdo #{$sotuv->check_raqam}" . ($sotuv->mijoz_ism ? " ({$sotuv->mijoz_ism})" : ''),
                manbaTur: 'pos_sotuv_plastik',
                manbaId: $sotuv->id,
            );
        }

        return response()->json([
            'muvaffaqiyat' => true,
            'check_raqam'  => $sotuv->check_raqam,
            'jami_tolov'   => $sotuv->jami_tolov,
            'qayta_pul'    => $sotuv->qayta_pul,
            'sotuv_id'     => $sotuv->id,
        ]);
    }

    /** Sotuv tarixi */
    public function tarix(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $sotuvlar = PosSotuv::with(['xodim','filial'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->sana, fn($q) => $q->whereDate('sana', $request->sana))
            ->orderByDesc('created_at')
            ->paginate(30)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $bugun_jami = PosSotuv::when($filialId, fn($q) => $q->where('filial_id',$filialId))
            ->whereDate('sana', today())->sum('jami_tolov');

        return view('ombor.pos.tarix', compact('sotuvlar', 'filiallar', 'filialId', 'bugun_jami'));
    }

    public function chekKorish(PosSotuv $sotuv)
    {
        $sotuv->load(['tafsilot.tovar', 'xodim', 'filial']);
        return view('ombor.pos.chek', compact('sotuv'));
    }

    /** POS Dashboard — bugungi statistika, kartochkalar va jadvallar. */
    public function dashboard(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        $bugunBase = fn() => PosSotuv::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereDate('pos_sotuv.sana', today())->where('pos_sotuv.holat', 'tugallangan');

        $bugunSotuv  = (clone $bugunBase())->sum('jami_tolov');
        $naqdTushum  = (clone $bugunBase())->sum('naqd_summa');
        $kartaTushum = (clone $bugunBase())->sum('plastik_summa');
        $chekSoni    = (clone $bugunBase())->count();

        $qarzgaSotuv = DB::table('reg_kredit')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereDate('boshlanish_sana', today())->sum('jami_summa');

        $qaytimSumma = \App\Models\PosQaytim::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereDate('sana', today())->where('holat', 'tugallangan')->sum('jami_summa');
        $sofTushum   = $bugunSotuv - $qaytimSumma;

        $oxirgiQaytimlar = \App\Models\PosQaytim::with(['sotuv', 'xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->latest('created_at')->limit(10)->get();

        $oxirgiSotuvlar = PosSotuv::with(['xodim', 'filial'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->latest('created_at')->limit(10)->get();

        $kassirlarKesimi = (clone $bugunBase())
            ->join('foydalanuvchilar', 'foydalanuvchilar.id', '=', 'pos_sotuv.xodim_id')
            ->selectRaw('foydalanuvchilar.ism_familiya, COUNT(*) as soni, SUM(jami_tolov) as summa')
            ->groupBy('foydalanuvchilar.id', 'foydalanuvchilar.ism_familiya')
            ->orderByDesc('summa')->get();

        $tolovTurlariKesimi = (clone $bugunBase())
            ->selectRaw('tolov_turi, COUNT(*) as soni, SUM(jami_tolov) as summa')
            ->groupBy('tolov_turi')->get();

        $engKopSotilgan = DB::table('pos_tafsilot as pt')
            ->join('pos_sotuv as ps', 'ps.id', '=', 'pt.sotuv_id')
            ->join('tovar_katalog as t', 't.id', '=', 'pt.tovar_id')
            ->when($filialId, fn($q) => $q->where('ps.filial_id', $filialId))
            ->whereDate('ps.sana', today())->where('ps.holat', 'tugallangan')
            ->selectRaw('t.nomi, SUM(pt.miqdor) as soni, SUM(pt.jami_summa) as summa')
            ->groupBy('t.id', 't.nomi')->orderByDesc('soni')->limit(10)->get();

        $kamQoldiq = TovarKatalog::faol()
            ->whereColumn('qoldiq', '<=', 'min_qoldiq')->where('min_qoldiq', '>', 0)
            ->orderBy('qoldiq')->limit(10)->get(['id', 'nomi', 'qoldiq', 'min_qoldiq', 'birlik']);

        $songgiTopshirishlar = KassaTransfer::with(['fromFilial', 'toFilial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('from_filial_id', $filialId))
            ->latest('created_at')->limit(10)->get();

        return view('ombor.pos.dashboard', compact(
            'filiallar', 'filialId', 'bugunSotuv', 'naqdTushum', 'kartaTushum', 'chekSoni',
            'qarzgaSotuv', 'qaytimSumma', 'sofTushum', 'oxirgiSotuvlar', 'oxirgiQaytimlar', 'kassirlarKesimi',
            'tolovTurlariKesimi', 'engKopSotilgan', 'kamQoldiq', 'songgiTopshirishlar'
        ));
    }

    /** POS hisobotlar — davr/filial/kassir/to'lov turi bo'yicha filtrlanadigan bank stilidagi jadval. */
    public function hisobotlar(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $kassirlar = DB::table('foydalanuvchilar')
            ->whereIn('rol', ['admin', 'menejer', 'kassir', 'sotuvchi'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->orderBy('ism_familiya')->get(['id', 'ism_familiya']);

        $qator = $this->posHisobotSorovi($filialId, $danSana, $gachaSana, $request);

        if ($request->get('format') === 'excel') {
            return $this->posHisobotExcel($qator, $danSana, $gachaSana);
        }

        $jami = (object) [
            'soni'        => $qator->sum('soni'),
            'jami_summa'  => $qator->sum('jami_summa'),
            'naqd'        => $qator->sum('naqd'),
            'plastik'     => $qator->sum('plastik'),
            'chegirma'    => $qator->sum('chegirma'),
        ];

        return view('ombor.pos.hisobotlar', compact('qator', 'jami', 'filiallar', 'filialId', 'kassirlar', 'danSana', 'gachaSana'));
    }

    /** Kunlik POS savdo hisoboti — sana bo'yicha guruhlangan qatorlar. */
    private function posHisobotSorovi(?int $filialId, string $dan, string $gacha, Request $request)
    {
        return PosSotuv::query()
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->kassir_id, fn($q) => $q->where('xodim_id', $request->kassir_id))
            ->when($request->tolov_turi, fn($q) => $q->where('tolov_turi', $request->tolov_turi))
            ->whereBetween('sana', [$dan, $gacha])
            ->where('holat', 'tugallangan')
            ->selectRaw("sana, COUNT(*) as soni, SUM(jami_tolov) as jami_summa,
                SUM(naqd_summa) as naqd, SUM(plastik_summa) as plastik, SUM(chegirma) as chegirma")
            ->groupBy('sana')->orderByDesc('sana')->get();
    }

    private function posHisobotExcel($qator, string $dan, string $gacha)
    {
        $headers = ['Sana', 'Cheklar soni', 'Jami summa', 'Naqd', 'Karta/terminal', 'Chegirma'];
        $rows = $qator->map(fn($r) => [
            $r->sana, $r->soni, (float) $r->jami_summa, (float) $r->naqd, (float) $r->plastik, (float) $r->chegirma,
        ])->toArray();

        $html  = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
        $html .= '<head><meta charset="UTF-8"><style>body{font-family:Arial;font-size:10pt}';
        $html .= 'th{background:#1d4ed8;color:#fff;font-weight:bold;border:1px solid #777;padding:5px 8px}';
        $html .= 'td{border:1px solid #ccc;padding:3px 8px}.r{text-align:right;mso-number-format:"#,##0"}</style></head><body>';
        $html .= '<h3>POS savdo hisoboti — ' . $dan . ' / ' . $gacha . '</h3><table><thead><tr>';
        foreach ($headers as $h) $html .= '<th>' . htmlspecialchars($h) . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $i => $cell) {
                $html .= $i === 0 ? '<td>' . htmlspecialchars((string) $cell) . '</td>' : '<td class="r">' . number_format((float) $cell, 0, '.', ' ') . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        $fn = 'pos_hisobot_' . $dan . '_' . $gacha . '.xls';
        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fn . '"',
        ]);
    }
}
