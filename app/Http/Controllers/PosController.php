<?php
namespace App\Http\Controllers;

use App\Models\ChiqimTafsilot;
use App\Models\Filial;
use App\Models\OmbordanChiqim;
use App\Models\PosSotuv;
use App\Models\PosTafsilot;
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
        $guruhlar = TovarGuruh::faol()->withCount(['tovarlar' => fn($q) => $q->where('holat','faol')->where('qoldiq','>',0)])->orderBy('nomi')->get();

        // Bugungi statistika
        $bugun_sotuv  = PosSotuv::where('filial_id', $filialId)->whereDate('sana', today())->where('holat','tugallangan')->sum('jami_tolov');
        $bugun_checklar = PosSotuv::where('filial_id', $filialId)->whereDate('sana', today())->where('holat','tugallangan')->count();

        return view('ombor.pos.index', compact('guruhlar', 'filialId', 'bugun_sotuv', 'bugun_checklar'));
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
                   ->orWhere('barkod', $request->qidiruv);
            }))
            ->with('guruh:id,nomi')
            ->orderBy('nomi')
            ->limit(50)
            ->get(['id','nomi','barkod','sotish_narx','qoldiq','birlik','guruh_id']);

        // Ko'rsatiladigan qoldiqni ham SHU OMBOR bo'yicha almashtiramiz
        // (tovar_katalog.qoldiq — kompaniya bo'yicha jami, chalg'itmasligi uchun).
        if ($ombor) {
            $tovarlar->each(function ($t) use ($ombor) {
                $t->qoldiq = $this->stockService->qoldiq($ombor->id, $t->id);
            });
        }

        return response()->json($tovarlar);
    }

    /** POS savdoni saqlash */
    public function store(Request $request)
    {
        $request->validate([
            'filial_id'   => 'required|exists:filiallar,id',
            'tolov_turi'  => 'required|in:naqd,plastik,aralash',
            'naqd_summa'  => 'nullable|numeric|min:0',
            'plastik_summa'=> 'nullable|numeric|min:0',
            'chegirma'    => 'nullable|numeric|min:0',
            'mijoz_ism'   => 'nullable|string|max:200',
            'tovarlar'    => 'required|array|min:1',
            'tovarlar.*.tovar_id' => 'required|exists:tovar_katalog,id',
            'tovarlar.*.miqdor'   => 'required|numeric|min:0.001',
            'tovarlar.*.narx'     => 'required|numeric|min:0',
        ]);

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

        $sotuv = DB::transaction(function () use ($request, $ombor) {
            $umumiy = collect($request->tovarlar)->sum(fn($q) => $q['miqdor'] * $q['narx']);
            $chegirma = (float)($request->chegirma ?? 0);
            $jami = $umumiy - $chegirma;

            $sotuv = PosSotuv::create([
                'filial_id'      => $request->filial_id,
                'xodim_id'       => Auth::id(),
                'sana'           => today(),
                'check_raqam'    => PosSotuv::yangiCheckRaqam($request->filial_id),
                'umumiy_summa'   => $umumiy,
                'chegirma'       => $chegirma,
                'jami_tolov'     => $jami,
                'tolov_turi'     => $request->tolov_turi,
                'naqd_summa'     => $request->naqd_summa ?? 0,
                'plastik_summa'  => $request->plastik_summa ?? 0,
                'qayta_pul'      => max(0, ($request->naqd_summa ?? 0) - $jami),
                'mijoz_ism'      => $request->mijoz_ism,
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
}
