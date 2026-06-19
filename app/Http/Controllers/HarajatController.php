<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Harajat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HarajatController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;

        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $base = Harajat::with(['filial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereBetween('sana', [$danSana, $gachaSana])
            ->when($request->turi,    fn($q) => $q->where('turi', $request->turi))
            ->when($request->qidiruv, fn($q) => $q->where('mazmuni', 'like', '%'.$request->qidiruv.'%'));

        $jami = (clone $base)->sum('summa');

        $harajatlar = $base->orderByDesc('sana')->orderByDesc('id')->paginate(30)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        $turlari = Harajat::select('turi')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->distinct()->orderBy('turi')->pluck('turi');

        return view('harajatlar.index', compact(
            'harajatlar', 'filiallar', 'filialId',
            'danSana', 'gachaSana', 'jami', 'turlari'
        ));
    }

    public function create()
    {
        $user      = Auth::user();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $turlari   = Harajat::select('turi')->distinct()->orderBy('turi')->pluck('turi');
        return view('harajatlar.create', compact('filiallar', 'turlari'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'sana'      => 'required|date',
            'turi'      => 'required|string|max:255',
            'summa'     => 'required|numeric',
            'mazmuni'   => 'nullable|string|max:500',
        ]);

        Harajat::create([
            'filial_id' => $request->filial_id,
            'xodim_id'  => Auth::id(),
            'sana'      => $request->sana,
            'turi'      => trim($request->turi),
            'summa'     => $request->summa,
            'mazmuni'   => $request->mazmuni,
        ]);

        return redirect()->route('harajatlar.index')
            ->with('muvaffaqiyat', 'Harajat saqlandi.');
    }

    public function edit(Harajat $harajat)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $harajat->filial_id !== $user->filial_id) {
            abort(403);
        }
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $turlari   = Harajat::select('turi')->distinct()->orderBy('turi')->pluck('turi');
        return view('harajatlar.edit', compact('harajat', 'filiallar', 'turlari'));
    }

    public function update(Request $request, Harajat $harajat)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $harajat->filial_id !== $user->filial_id) {
            abort(403);
        }
        $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'sana'      => 'required|date',
            'turi'      => 'required|string|max:255',
            'summa'     => 'required|numeric',
            'mazmuni'   => 'nullable|string|max:500',
        ]);

        $harajat->update([
            'filial_id' => $request->filial_id,
            'sana'      => $request->sana,
            'turi'      => trim($request->turi),
            'summa'     => $request->summa,
            'mazmuni'   => $request->mazmuni,
        ]);

        return redirect()->route('harajatlar.index')
            ->with('muvaffaqiyat', 'Harajat yangilandi.');
    }

    public function destroy(Harajat $harajat)
    {
        $harajat->delete();
        return back()->with('muvaffaqiyat', "Harajat o'chirildi.");
    }
}
