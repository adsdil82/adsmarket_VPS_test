<?php

namespace App\Http\Controllers;

use App\Models\HisobRejasi;
use App\Models\YangiTulovTuri;
use Illuminate\Http\Request;

class YangiTulovTuriController extends Controller
{
    public function index()
    {
        $turlar  = YangiTulovTuri::with(['debetHisob', 'kreditHisob'])->orderBy('id')->get();
        $hisoblar = HisobRejasi::where('holat', 'faol')->orderBy('hisob_raqam')->get();

        return view('buxgalteriya.tulov_turlari', compact('turlar', 'hisoblar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kod'            => 'required|string|max:30|unique:yangi_tulov_turlari,kod',
            'nomi'           => 'required|string|max:150',
            'kategoriya'     => 'required|in:kassa,terminal,bank,boshqa',
            'debet_hisob_id' => 'nullable|exists:hisoblar_rejasi,id',
            'kredit_hisob_id'=> 'nullable|exists:hisoblar_rejasi,id',
            'izoh'           => 'nullable|string|max:500',
        ]);

        YangiTulovTuri::create($data + ['holat' => 'faol']);

        return back()->with('muvaffaqiyat', "To'lov turi '{$data['nomi']}' qo'shildi.");
    }

    public function update(Request $request, YangiTulovTuri $tur)
    {
        $data = $request->validate([
            'nomi'           => 'required|string|max:150',
            'kategoriya'     => 'required|in:kassa,terminal,bank,boshqa',
            'debet_hisob_id' => 'nullable|exists:hisoblar_rejasi,id',
            'kredit_hisob_id'=> 'nullable|exists:hisoblar_rejasi,id',
            'holat'          => 'required|in:faol,nofaol',
            'izoh'           => 'nullable|string|max:500',
        ]);

        $tur->update($data);

        return back()->with('muvaffaqiyat', "To'lov turi '{$tur->nomi}' yangilandi.");
    }

    public function destroy(YangiTulovTuri $tur)
    {
        $nom = $tur->nomi;
        $tur->delete();

        return back()->with('muvaffaqiyat', "To'lov turi '{$nom}' o'chirildi.");
    }
}
