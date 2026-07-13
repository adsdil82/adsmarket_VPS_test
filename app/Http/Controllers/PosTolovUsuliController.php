<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\PosTolovUsuli;
use Illuminate\Http\Request;

class PosTolovUsuliController extends Controller
{
    public function index()
    {
        $usullar   = PosTolovUsuli::with('filial')->orderBy('filial_id')->orderBy('tartib')->orderBy('nomi')->get();
        $filiallar = Filial::faol()->orderBy('nomi')->get(['id', 'nomi']);
        return view('malumotnamalar.pos-tolov-usullari.index', compact('usullar', 'filiallar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'nomi'      => 'required|string|max:100',
            'turi'      => 'required|in:terminal,onlayn,boshqa',
            'tartib'    => 'nullable|integer|min:0',
            'izoh'      => 'nullable|string|max:255',
        ]);
        PosTolovUsuli::create($data);
        return back()->with('muvaffaqiyat', "To'lov usuli «{$data['nomi']}» qo'shildi.");
    }

    public function update(Request $request, PosTolovUsuli $posTolovUsuli)
    {
        $data = $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'nomi'      => 'required|string|max:100',
            'turi'      => 'required|in:terminal,onlayn,boshqa',
            'holat'     => 'required|in:faol,nofaol',
            'tartib'    => 'nullable|integer|min:0',
            'izoh'      => 'nullable|string|max:255',
        ]);
        $posTolovUsuli->update($data);
        return back()->with('muvaffaqiyat', "To'lov usuli «{$posTolovUsuli->nomi}» yangilandi.");
    }

    public function destroy(PosTolovUsuli $posTolovUsuli)
    {
        $nomi = $posTolovUsuli->nomi;
        $posTolovUsuli->delete();
        return back()->with('muvaffaqiyat', "To'lov usuli «{$nomi}» o'chirildi.");
    }
}
