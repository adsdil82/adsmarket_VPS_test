<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Kassa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KassaController extends Controller
{
    public function index()
    {
        $kassalar  = Kassa::with('filial')->orderBy('filial_id')->orderBy('nomi')->get();
        $filiallar = Filial::faol()->orderBy('nomi')->get(['id','nomi']);
        return view('malumotnamalar.kassalar.index', compact('kassalar','filiallar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'nomi'      => 'required|string|max:100',
            'tur'       => 'required|in:naqd,bank,terminal,online',
            'valyuta'   => 'required|string|max:5',
        ]);
        Kassa::create(array_merge($data, ['qoldiq' => 0]));
        return back()->with('muvaffaqiyat', "Kassa «{$data['nomi']}» qo'shildi.");
    }

    public function update(Request $request, Kassa $kassa)
    {
        $data = $request->validate([
            'filial_id' => 'required|exists:filiallar,id',
            'nomi'      => 'required|string|max:100',
            'tur'       => 'required|in:naqd,bank,terminal,online',
            'valyuta'   => 'required|string|max:5',
            'holat'     => 'required|in:faol,nofaol',
            'izoh'      => 'nullable|string',
        ]);
        $kassa->update($data);
        return back()->with('muvaffaqiyat', "Kassa «{$kassa->nomi}» yangilandi.");
    }

    public function destroy(Kassa $kassa)
    {
        if ($kassa->qoldiq != 0) {
            return back()->with('xato', "Kassa qoldig'i 0 emas — o'chirish mumkin emas.");
        }
        $nomi = $kassa->nomi;
        $kassa->delete();
        return back()->with('muvaffaqiyat', "Kassa «{$nomi}» o'chirildi.");
    }
}
