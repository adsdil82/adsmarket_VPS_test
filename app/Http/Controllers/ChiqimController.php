<?php
namespace App\Http\Controllers;

use App\Models\ChiqimTafsilot;
use App\Models\Filial;
use App\Models\OmbordanChiqim;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChiqimController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;

        $chiqimlar = OmbordanChiqim::with(['filial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->sabab, fn($q) => $q->where('sabab', $request->sabab))
            ->orderByDesc('sana')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();
        $sabablar  = OmbordanChiqim::$sabablar;

        return view('ombor.chiqim.index', compact('chiqimlar', 'filiallar', 'filialId', 'sabablar'));
    }

    public function create()
    {
        $user     = Auth::user();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $tovarlar  = TovarKatalog::faol()->with('guruh')->orderBy('nomi')->get();
        $sabablar  = OmbordanChiqim::$sabablar;
        return view('ombor.chiqim.create', compact('filiallar', 'tovarlar', 'sabablar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'sana'      => 'required|date',
            'sabab'     => 'required|in:' . implode(',', array_keys(OmbordanChiqim::$sabablar)),
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
}
