<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FilialController extends Controller
{
    public function index()
    {
        $filiallar = Filial::withCount([
            'foydalanuvchilar',
        ])->orderBy('nomi')->get();
        return view('malumotnamalar.filiallar.index', compact('filiallar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nomi'    => 'required|string|max:150',
            'kod'     => 'required|string|max:20|unique:filiallar,kod',
            'manzil'  => 'nullable|string|max:255',
            'telefon' => 'nullable|string|max:20',
        ]);
        Filial::create($data);
        return back()->with('muvaffaqiyat', "Filial «{$data['nomi']}» qo'shildi.");
    }

    public function update(Request $request, Filial $filial)
    {
        $data = $request->validate([
            'nomi'    => 'required|string|max:150',
            'kod'     => ['required','string','max:20', Rule::unique('filiallar','kod')->ignore($filial->id)],
            'manzil'  => 'nullable|string|max:255',
            'telefon' => 'nullable|string|max:20',
            'holat'   => 'required|in:faol,nofaol',
        ]);
        $filial->update($data);
        return back()->with('muvaffaqiyat', "Filial «{$filial->nomi}» yangilandi.");
    }

    public function destroy(Filial $filial)
    {
        if ($filial->foydalanuvchilar()->exists()) {
            return back()->with('xato', "Bu filialda foydalanuvchilar bor — o'chirish mumkin emas.");
        }
        $nomi = $filial->nomi;
        $filial->delete();
        return back()->with('muvaffaqiyat', "Filial «{$nomi}» o'chirildi.");
    }
}
