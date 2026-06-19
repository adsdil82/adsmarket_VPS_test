<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\KirimTafsilot;
use App\Models\OmboraKirim;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KirimController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;

        $kirimlar = OmboraKirim::with(['filial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->sana, fn($q) => $q->whereDate('sana', $request->sana))
            ->orderByDesc('sana')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        // Statistika
        $bugun_jami = OmboraKirim::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereDate('sana', today())->sum('umumiy_summa');
        $oy_jami = OmboraKirim::when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereMonth('sana', now()->month)->sum('umumiy_summa');

        return view('ombor.kirim.index', compact('kirimlar', 'filiallar', 'filialId', 'bugun_jami', 'oy_jami'));
    }

    public function create()
    {
        $user     = Auth::user();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $tovarlar  = TovarKatalog::faol()->with('guruh')->orderBy('nomi')->get();
        return view('ombor.kirim.create', compact('filiallar', 'tovarlar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'filial_id'     => 'required|exists:filiallar,id',
            'sana'          => 'required|date',
            'yetkazuvchi'   => 'nullable|string|max:200',
            'hujjat_raqam'  => 'nullable|string|max:50',
            'izoh'          => 'nullable|string',
            'tovarlar'      => 'required|array|min:1',
            'tovarlar.*.tovar_id'  => 'required|exists:tovar_katalog,id',
            'tovarlar.*.miqdor'    => 'required|numeric|min:0.001',
            'tovarlar.*.tan_narx'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $jami = 0;
            $kirim = OmboraKirim::create([
                'filial_id'    => $request->filial_id,
                'xodim_id'     => Auth::id(),
                'sana'         => $request->sana,
                'yetkazuvchi'  => $request->yetkazuvchi,
                'hujjat_raqam' => $request->hujjat_raqam,
                'izoh'         => $request->izoh,
                'holat'        => 'tasdiqlangan',
                'umumiy_summa' => 0,
            ]);

            foreach ($request->tovarlar as $q) {
                $summa = $q['miqdor'] * $q['tan_narx'];
                $jami += $summa;

                KirimTafsilot::create([
                    'kirim_id'  => $kirim->id,
                    'tovar_id'  => $q['tovar_id'],
                    'miqdor'    => $q['miqdor'],
                    'tan_narx'  => $q['tan_narx'],
                    'jami_summa'=> $summa,
                ]);

                // Ombor qoldig'ini yangilash
                TovarKatalog::find($q['tovar_id'])->increment('qoldiq', $q['miqdor']);

                // Tan narxni yangilash
                if ($q['tan_narx'] > 0) {
                    TovarKatalog::find($q['tovar_id'])->update(['tan_narx' => $q['tan_narx']]);
                }
            }

            $kirim->update(['umumiy_summa' => $jami]);
        });

        return redirect()->route('kirim.index')->with('muvaffaqiyat', 'Kirim muvaffaqiyatli saqlandi va ombor yangilandi.');
    }

    public function show(OmboraKirim $kirim)
    {
        $kirim->load(['filial', 'xodim', 'tafsilot.tovar.guruh']);
        return view('ombor.kirim.show', compact('kirim'));
    }

    public function destroy(OmboraKirim $kirim)
    {
        if ($kirim->holat === 'tasdiqlangan') {
            // Ombor qoldig'ini tiklash
            DB::transaction(function () use ($kirim) {
                foreach ($kirim->tafsilot as $t) {
                    TovarKatalog::find($t->tovar_id)?->decrement('qoldiq', $t->miqdor);
                }
                $kirim->update(['holat' => 'bekor']);
            });
        }
        return back()->with('muvaffaqiyat', 'Kirim bekor qilindi.');
    }
}
