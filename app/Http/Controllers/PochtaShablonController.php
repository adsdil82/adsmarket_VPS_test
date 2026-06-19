<?php

namespace App\Http\Controllers;

use App\Models\PochtaShablon;
use Illuminate\Http\Request;

class PochtaShablonController extends Controller
{
    public function index()
    {
        $shablonlar  = PochtaShablon::orderBy('sort_order')->orderBy('nomi')->get();
        $ozgaruvchilar = PochtaShablon::ozgaruvchilar();
        return view('malumotnamalar.pochta-shablonlar.index', compact('shablonlar', 'ozgaruvchilar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'                => 'required|string|max:100',
            'matn'                => 'required|string',
            'qayta_yuborish_kun'  => 'required|integer|min:0|max:365',
            'holat'               => 'required|in:faol,nofaol',
            'sort_order'          => 'nullable|integer|min:0',
        ]);

        PochtaShablon::create($data + ['sort_order' => $data['sort_order'] ?? 0]);

        return back()->with('muvaffaqiyat', "'{$data['nomi']}' shabloni qo'shildi.");
    }

    public function update(Request $request, PochtaShablon $pochtaShablon)
    {
        $data = $request->validate([
            'nomi'                => 'required|string|max:100',
            'matn'                => 'required|string',
            'qayta_yuborish_kun'  => 'required|integer|min:0|max:365',
            'holat'               => 'required|in:faol,nofaol',
            'sort_order'          => 'nullable|integer|min:0',
        ]);

        $pochtaShablon->update($data + ['sort_order' => $data['sort_order'] ?? 0]);

        return back()->with('muvaffaqiyat', "'{$data['nomi']}' shabloni yangilandi.");
    }

    public function destroy(PochtaShablon $pochtaShablon)
    {
        $nom = $pochtaShablon->nomi;

        if ($pochtaShablon->loglar()->exists()) {
            return back()->with('xato', "'{$nom}' — bu shablon orqali xatlar yuborilgan. O'chirish mumkin emas.");
        }

        $pochtaShablon->delete();
        return back()->with('muvaffaqiyat', "'{$nom}' shabloni o'chirildi.");
    }
}
