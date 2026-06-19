<?php

namespace App\Http\Controllers;

use App\Models\HarajatTuri;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HarajatTuriController extends Controller
{
    public function index()
    {
        $turlar = HarajatTuri::with('bolalar')->asosiy()->tartibli()->get();
        return view('malumotnamalar.harajat-turlari.index', compact('turlar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'       => 'required|string|max:150',
            'kod'        => 'nullable|string|max:30|unique:harajat_turlari,kod',
            'ota_id'     => 'nullable|exists:harajat_turlari,id',
            'rang'       => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);
        HarajatTuri::create($data + ['sort_order' => $data['sort_order'] ?? 100, 'rang' => $data['rang'] ?? 'secondary']);
        return back()->with('muvaffaqiyat', "Harajat turi «{$data['nomi']}» qo'shildi.");
    }

    public function update(Request $request, HarajatTuri $harajatTuri)
    {
        $data = $request->validate([
            'nomi'       => 'required|string|max:150',
            'kod'        => ['nullable','string','max:30', Rule::unique('harajat_turlari','kod')->ignore($harajatTuri->id)],
            'ota_id'     => 'nullable|exists:harajat_turlari,id',
            'rang'       => 'nullable|string|max:20',
            'holat'      => 'required|in:faol,nofaol',
            'sort_order' => 'nullable|integer',
        ]);
        $harajatTuri->update($data);
        return back()->with('muvaffaqiyat', "Harajat turi «{$harajatTuri->nomi}» yangilandi.");
    }

    public function destroy(HarajatTuri $harajatTuri)
    {
        if ($harajatTuri->bolalar()->exists()) {
            return back()->with('xato', "Bu turning bo'limlari bor — avval ularni o'chiring.");
        }
        $nomi = $harajatTuri->nomi;
        $harajatTuri->delete();
        return back()->with('muvaffaqiyat', "Harajat turi «{$nomi}» o'chirildi.");
    }
}
