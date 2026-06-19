<?php
namespace App\Http\Controllers;
use App\Models\StatusSabab;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatusSababController extends Controller {
    public function index() {
        $modullar = StatusSabab::select('modul')->distinct()->orderBy('modul')->pluck('modul');
        $grouped  = StatusSabab::orderBy('modul')->orderBy('sort_order')->orderBy('nomi')->get()->groupBy('modul');
        return view('malumotnamalar.statuslar.index', compact('grouped','modullar'));
    }
    public function store(Request $request) {
        $d = $request->validate([
            'modul'      => 'required|string|max:50',
            'tur'        => 'required|in:status,sabab,holat',
            'kod'        => 'required|string|max:50',
            'nomi'       => 'required|string|max:200',
            'rang'       => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);
        // Check unique modul+kod
        if (StatusSabab::where('modul',$d['modul'])->where('kod',$d['kod'])->exists()) {
            return back()->with('xato', "Bu modul+kod allaqachon mavjud.");
        }
        StatusSabab::create($d + ['rang'=>$d['rang']??'secondary','sort_order'=>$d['sort_order']??100]);
        return back()->with('muvaffaqiyat', "«{$d['nomi']}» qo'shildi.");
    }
    public function update(Request $request, StatusSabab $statusSabab) {
        if ($statusSabab->tizim_holati) return back()->with('xato', "Tizim statusini o'zgartirish mumkin emas.");
        $d = $request->validate([
            'nomi'       => 'required|string|max:200',
            'rang'       => 'nullable|string|max:20',
            'holat'      => 'required|in:faol,nofaol',
            'sort_order' => 'nullable|integer',
        ]);
        $statusSabab->update($d);
        return back()->with('muvaffaqiyat', "«{$statusSabab->nomi}» yangilandi.");
    }
    public function destroy(StatusSabab $statusSabab) {
        if ($statusSabab->tizim_holati) return back()->with('xato', "Tizim statusini o'chirish mumkin emas.");
        $nomi = $statusSabab->nomi; $statusSabab->delete();
        return back()->with('muvaffaqiyat', "«{$nomi}» o'chirildi.");
    }
}
