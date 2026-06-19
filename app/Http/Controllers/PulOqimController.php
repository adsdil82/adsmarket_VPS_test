<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Kassa;
use App\Models\PulKategoriya;
use App\Models\PulOqim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PulOqimController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $base = PulOqim::with(['kategoriya.ota', 'kassa', 'xodim', 'filial'])
            ->tasdiqlangan()
            ->sanada($danSana, $gachaSana)
            ->filialda($filialId)
            ->when($request->yunalish,    fn($q) => $q->where('yunalish', $request->yunalish))
            ->when($request->kategoriya,  fn($q) => $q->where('kategoriya_id', $request->kategoriya))
            ->when($request->kassa_id,    fn($q) => $q->where('kassa_id', $request->kassa_id))
            ->when($request->qidiruv,     fn($q) => $q->where('izoh', 'like', '%'.$request->qidiruv.'%'));

        // Statistika
        $stat = [
            'kirim'  => (clone $base)->kirim()->sum('summa'),
            'chiqim' => (clone $base)->chiqim()->sum('summa'),
        ];
        $stat['sof'] = $stat['kirim'] - $stat['chiqim'];

        // Kategoriya bo'yicha breakdown
        $chiqimByKat = (clone $base)->chiqim()
            ->select('kategoriya_id', DB::raw('SUM(summa) as jami'), DB::raw('COUNT(*) as soni'))
            ->with('kategoriya')
            ->groupBy('kategoriya_id')
            ->orderByDesc('jami')
            ->get();

        $oqimlar = $base->orderByDesc('sana')->orderByDesc('id')->paginate(30)->withQueryString();

        $filiallar   = $user->isAdmin() ? Filial::faol()->get() : collect();
        $kassalar    = Kassa::where('holat', 'faol')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->get();
        $kategoriyalar = PulKategoriya::faol()->with('bolalar')->asosiy()->orderBy('sort_order')->get();

        return view('pul-oqimlari.index', compact(
            'oqimlar', 'filiallar', 'kassalar', 'kategoriyalar',
            'filialId', 'danSana', 'gachaSana', 'stat', 'chiqimByKat'
        ));
    }

    public function create(Request $request)
    {
        $user      = Auth::user();
        $yunalish  = $request->yunalish ?? 'chiqim';
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $kassalar  = Kassa::where('holat', 'faol')->get();
        $kirimKategoriyalar  = PulKategoriya::kirimRoyxat();
        $chiqimKategoriyalar = PulKategoriya::chiqimRoyxat();
        return view('pul-oqimlari.create', compact(
            'filiallar', 'kassalar', 'yunalish', 'kirimKategoriyalar', 'chiqimKategoriyalar'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'yunalish'     => 'required|in:kirim,chiqim',
            'filial_id'    => 'required|exists:filiallar,id',
            'kassa_id'     => 'required|exists:kassalar,id',
            'kategoriya_id'=> 'required|exists:pul_kategoriyalar,id',
            'sana'         => 'required|date',
            'summa'        => 'required|numeric|min:0.01',
            'izoh'         => 'nullable|string|max:500',
        ]);

        PulOqim::create([
            'filial_id'    => $request->filial_id,
            'kassa_id'     => $request->kassa_id,
            'kategoriya_id'=> $request->kategoriya_id,
            'xodim_id'     => Auth::id(),
            'yunalish'     => $request->yunalish,
            'sana'         => $request->sana,
            'summa'        => $request->summa,
            'izoh'         => $request->izoh,
            'manba_tur'    => 'manual',
            'holat'        => 'tasdiqlangan',
            'tasdiqlagan_id' => Auth::id(),
        ]);

        return redirect()->route('pul-oqimlari.index')
            ->with('muvaffaqiyat', $request->yunalish === 'kirim' ? 'Kirim saqlandi.' : 'Chiqim saqlandi.');
    }

    public function edit(PulOqim $pulOqim)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $pulOqim->filial_id !== $user->filial_id) abort(403);

        $filiallar  = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $kassalar   = Kassa::where('holat', 'faol')->get();
        $kirimKategoriyalar  = PulKategoriya::kirimRoyxat();
        $chiqimKategoriyalar = PulKategoriya::chiqimRoyxat();
        return view('pul-oqimlari.edit', compact(
            'pulOqim', 'filiallar', 'kassalar', 'kirimKategoriyalar', 'chiqimKategoriyalar'
        ));
    }

    public function update(Request $request, PulOqim $pulOqim)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $pulOqim->filial_id !== $user->filial_id) abort(403);

        $request->validate([
            'yunalish'     => 'required|in:kirim,chiqim',
            'filial_id'    => 'required|exists:filiallar,id',
            'kassa_id'     => 'required|exists:kassalar,id',
            'kategoriya_id'=> 'required|exists:pul_kategoriyalar,id',
            'sana'         => 'required|date',
            'summa'        => 'required|numeric|min:0.01',
            'izoh'         => 'nullable|string|max:500',
        ]);

        $pulOqim->update($request->only(
            'yunalish','filial_id','kassa_id','kategoriya_id','sana','summa','izoh'
        ));

        return redirect()->route('pul-oqimlari.index')
            ->with('muvaffaqiyat', 'Operatsiya yangilandi.');
    }

    public function destroy(PulOqim $pulOqim)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $pulOqim->update(['holat' => 'bekor']);
        return back()->with('muvaffaqiyat', 'Operatsiya bekor qilindi.');
    }

    public function ajaxKunlikChart(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $dan      = now()->subDays(29)->toDateString();
        $gacha    = now()->toDateString();

        $rows = PulOqim::tasdiqlangan()->sanada($dan, $gacha)->filialda($filialId)
            ->select('sana', 'yunalish', DB::raw('SUM(summa) as jami'))
            ->groupBy('sana', 'yunalish')
            ->orderBy('sana')
            ->get()
            ->groupBy('sana');

        $labels = []; $kirimlar = []; $chiqimlar = [];
        for ($i = 29; $i >= 0; $i--) {
            $kun = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d.m');
            $dayData  = $rows[$kun] ?? collect();
            $kirimlar[]  = $dayData->where('yunalish','kirim')->sum('jami');
            $chiqimlar[] = $dayData->where('yunalish','chiqim')->sum('jami');
        }

        return response()->json(compact('labels','kirimlar','chiqimlar'));
    }
}
