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

    /** Ajax: tovarlarni qidirish/yuklash */
    public function tovarlar(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->filial_id ?? $request->filial_id;

        $tovarlar = TovarKatalog::faol()
            ->where('qoldiq', '>', 0)
            ->when($request->guruh_id, fn($q) => $q->where('guruh_id', $request->guruh_id))
            ->when($request->qidiruv,  fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('nomi', 'like', "%{$request->qidiruv}%")
                   ->orWhere('barkod', $request->qidiruv);
            }))
            ->with('guruh:id,nomi')
            ->orderBy('nomi')
            ->limit(50)
            ->get(['id','nomi','barkod','sotish_narx','qoldiq','birlik','guruh_id']);

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

        // Qoldiq tekshiruvi
        foreach ($request->tovarlar as $q) {
            $tovar = TovarKatalog::find($q['tovar_id']);
            if ($tovar->qoldiq < $q['miqdor']) {
                return response()->json(['xato' => "«{$tovar->nomi}»: omborda {$tovar->qoldiq} {$tovar->birlik} bor"], 422);
            }
        }

        $sotuv = DB::transaction(function () use ($request) {
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
                TovarKatalog::find($q['tovar_id'])->decrement('qoldiq', $q['miqdor']);
            }

            return $sotuv;
        });

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
