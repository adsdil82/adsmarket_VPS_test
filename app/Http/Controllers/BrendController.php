<?php
namespace App\Http\Controllers;
use App\Models\Brend;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrendController extends Controller {
    public function index() {
        return view('malumotnamalar.brendlar.index', ['brendlar' => Brend::tartibli()->get()]);
    }
    public function store(Request $request) {
        $d = $request->validate([
            'nomi'       => 'required|string|max:100',
            'kod'        => 'nullable|string|max:30|unique:brendlar,kod',
            'mamlakat'   => 'nullable|string|max:60',
            'sort_order' => 'nullable|integer',
        ]);
        Brend::create($d + ['sort_order' => $d['sort_order'] ?? 100]);
        return back()->with('muvaffaqiyat', "Brend «{$d['nomi']}» qo'shildi.");
    }
    public function update(Request $request, Brend $brend) {
        $d = $request->validate([
            'nomi'       => 'required|string|max:100',
            'kod'        => ['nullable','string','max:30', Rule::unique('brendlar','kod')->ignore($brend->id)],
            'mamlakat'   => 'nullable|string|max:60',
            'holat'      => 'required|in:faol,nofaol',
            'sort_order' => 'nullable|integer',
        ]);
        $brend->update($d);
        return back()->with('muvaffaqiyat', "Brend «{$brend->nomi}» yangilandi.");
    }
    public function destroy(Brend $brend) {
        $nomi = $brend->nomi; $brend->delete();
        return back()->with('muvaffaqiyat', "Brend «{$nomi}» o'chirildi.");
    }
}
