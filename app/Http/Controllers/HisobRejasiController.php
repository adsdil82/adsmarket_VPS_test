<?php

namespace App\Http\Controllers;

use App\Models\HisobRejasi;
use Illuminate\Http\Request;

class HisobRejasiController extends Controller
{
    public function index()
    {
        $hisoblar = HisobRejasi::with('ota')
            ->orderBy('hisob_raqam')
            ->get();

        $otalar = HisobRejasi::where('daraja', '<', 3)
            ->where('holat', 'faol')
            ->orderBy('hisob_raqam')
            ->get();

        return view('buxgalteriya.hisoblar', compact('hisoblar', 'otalar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hisob_raqam' => 'required|string|max:10|unique:hisoblar_rejasi,hisob_raqam',
            'nomi'        => 'required|string|max:250',
            'turi'        => 'required|in:faol,passiv,faol-passiv',
            'daraja'      => 'required|integer|in:1,2,3',
            'ota_id'      => 'nullable|exists:hisoblar_rejasi,id',
            'izoh'        => 'nullable|string|max:500',
        ]);

        HisobRejasi::create($data + ['holat' => 'faol']);

        return back()->with('muvaffaqiyat', "Hisob {$data['hisob_raqam']} qo'shildi.");
    }

    public function update(Request $request, HisobRejasi $hisob)
    {
        $data = $request->validate([
            'nomi'   => 'required|string|max:250',
            'turi'   => 'required|in:faol,passiv,faol-passiv',
            'holat'  => 'required|in:faol,nofaol',
            'ota_id' => 'nullable|exists:hisoblar_rejasi,id',
            'izoh'   => 'nullable|string|max:500',
        ]);

        $hisob->update($data);

        return back()->with('muvaffaqiyat', "Hisob {$hisob->hisob_raqam} yangilandi.");
    }

    public function destroy(HisobRejasi $hisob)
    {
        if ($hisob->bolalar()->exists()) {
            return back()->withErrors(['xato' => 'Bu hisobning sub-hisoblar mavjud. Avval ularni o\'chiring.']);
        }
        if ($hisob->tulovTurlariDebet()->exists() || $hisob->tulovTurlariKredit()->exists()) {
            return back()->withErrors(['xato' => 'Bu hisob to\'lov turlariga bog\'langan. Avval bog\'liqlikni olib tashlang.']);
        }

        $raqam = $hisob->hisob_raqam;
        $hisob->delete();

        return back()->with('muvaffaqiyat', "Hisob {$raqam} o'chirildi.");
    }
}
