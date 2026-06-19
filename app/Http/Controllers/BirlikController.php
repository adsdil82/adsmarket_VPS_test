<?php

namespace App\Http\Controllers;

use App\Models\Birlik;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BirlikController extends Controller
{
    public function index()
    {
        $birliklar = Birlik::orderBy('sort_order')->orderBy('nomi')->get();
        return view('malumotnamalar.birliklar.index', compact('birliklar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'       => 'required|string|max:80',
            'qisqa_nomi' => 'nullable|string|max:20',
            'kod'        => 'nullable|string|max:20|unique:birliklar,kod',
            'sort_order' => 'nullable|integer',
        ]);
        Birlik::create($data + ['sort_order' => $data['sort_order'] ?? 100]);
        return back()->with('muvaffaqiyat', "Birlik «{$data['nomi']}» qo'shildi.");
    }

    public function update(Request $request, Birlik $birlik)
    {
        $data = $request->validate([
            'nomi'       => 'required|string|max:80',
            'qisqa_nomi' => 'nullable|string|max:20',
            'kod'        => ['nullable','string','max:20', Rule::unique('birliklar','kod')->ignore($birlik->id)],
            'holat'      => 'required|in:faol,nofaol',
            'sort_order' => 'nullable|integer',
        ]);
        $birlik->update($data);
        return back()->with('muvaffaqiyat', "Birlik «{$birlik->nomi}» yangilandi.");
    }

    public function destroy(Birlik $birlik)
    {
        $nomi = $birlik->nomi;
        $birlik->delete();
        return back()->with('muvaffaqiyat', "Birlik «{$nomi}» o'chirildi.");
    }
}
