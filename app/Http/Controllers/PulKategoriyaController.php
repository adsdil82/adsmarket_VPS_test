<?php

namespace App\Http\Controllers;

use App\Models\HisobRejasi;
use App\Models\PulKategoriya;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PulKategoriyaController extends Controller
{
    public function index()
    {
        $kirims  = PulKategoriya::with('bolalar')->whereNull('ota_id')->where('yunalish','kirim')->orderBy('sort_order')->get();
        $chiqims = PulKategoriya::with('bolalar')->whereNull('ota_id')->where('yunalish','chiqim')->orderBy('sort_order')->get();
        $hisoblar = HisobRejasi::where('holat','faol')->orderBy('hisob_raqam')->get(['id','hisob_raqam','nomi']);
        return view('malumotnamalar.pul-kategoriyalar.index', compact('kirims','chiqims','hisoblar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'yunalish'   => 'required|in:kirim,chiqim',
            'nomi'       => 'required|string|max:200',
            'kod'        => 'required|string|max:20|unique:pul_kategoriyalar,kod',
            'ota_id'     => 'nullable|exists:pul_kategoriyalar,id',
            'hisob_id'   => 'nullable|exists:hisoblar_rejasi,id',
            'rang'       => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);
        PulKategoriya::create($data + [
            'rang'       => $data['rang'] ?? 'gray',
            'sort_order' => $data['sort_order'] ?? 100,
            'avtomatik'  => false,
        ]);
        return back()->with('muvaffaqiyat', "Kategoriya «{$data['nomi']}» qo'shildi.");
    }

    public function update(Request $request, PulKategoriya $pulKategoriya)
    {
        $data = $request->validate([
            'nomi'       => 'required|string|max:200',
            'kod'        => ['required','string','max:20', Rule::unique('pul_kategoriyalar','kod')->ignore($pulKategoriya->id)],
            'ota_id'     => 'nullable|exists:pul_kategoriyalar,id',
            'hisob_id'   => 'nullable|exists:hisoblar_rejasi,id',
            'rang'       => 'nullable|string|max:20',
            'holat'      => 'required|in:faol,nofaol',
            'sort_order' => 'nullable|integer',
        ]);
        if (!empty($data['ota_id']) && $data['ota_id'] == $pulKategoriya->id) {
            return back()->with('xato', 'Kategoriya o\'ziga ota bo\'la olmaydi.');
        }
        $pulKategoriya->update($data);
        return back()->with('muvaffaqiyat', "Kategoriya «{$pulKategoriya->nomi}» yangilandi.");
    }

    public function destroy(PulKategoriya $pulKategoriya)
    {
        if ($pulKategoriya->bolalar()->exists()) {
            return back()->with('xato', "Bu kategoriyaning bo'limlari bor.");
        }
        if ($pulKategoriya->avtomatik) {
            return back()->with('xato', "Avtomatik kategoriyani o'chirish mumkin emas.");
        }
        $nomi = $pulKategoriya->nomi;
        $pulKategoriya->delete();
        return back()->with('muvaffaqiyat', "Kategoriya «{$nomi}» o'chirildi.");
    }
}
