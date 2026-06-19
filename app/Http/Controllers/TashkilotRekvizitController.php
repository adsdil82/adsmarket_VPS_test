<?php
namespace App\Http\Controllers;
use App\Models\Filial;
use App\Models\TashkilotRekvizit;
use Illuminate\Http\Request;

class TashkilotRekvizitController extends Controller {
    public function index() {
        $rekvizitlar = TashkilotRekvizit::with('filial')->orderBy('asosiy','desc')->orderBy('nomi')->get();
        $filiallar   = Filial::faol()->orderBy('nomi')->get(['id','nomi']);
        return view('malumotnamalar.tashkilot-rekvizit.index', compact('rekvizitlar','filiallar'));
    }
    public function create() {
        $filiallar = Filial::faol()->orderBy('nomi')->get(['id','nomi']);
        return view('malumotnamalar.tashkilot-rekvizit.form', compact('filiallar'));
    }
    public function store(Request $request) {
        $d = $this->validate($request);
        if (!empty($d['asosiy'])) TashkilotRekvizit::query()->update(['asosiy' => false]);
        $d['asosiy'] = !empty($d['asosiy']);
        TashkilotRekvizit::create($d);
        return redirect()->route('malumotnamalar.tashkilot-rekvizit.index')
            ->with('muvaffaqiyat', "Tashkilot rekviziti «{$d['nomi']}» saqlandi.");
    }
    public function edit(TashkilotRekvizit $tashkilotRekvizit) {
        $filiallar = Filial::faol()->orderBy('nomi')->get(['id','nomi']);
        return view('malumotnamalar.tashkilot-rekvizit.form', ['rekvizit'=>$tashkilotRekvizit, 'filiallar'=>$filiallar]);
    }
    public function update(Request $request, TashkilotRekvizit $tashkilotRekvizit) {
        $d = $this->validate($request);
        if (!empty($d['asosiy'])) TashkilotRekvizit::where('id','!=',$tashkilotRekvizit->id)->update(['asosiy'=>false]);
        $d['asosiy'] = !empty($d['asosiy']);
        $tashkilotRekvizit->update($d);
        return redirect()->route('malumotnamalar.tashkilot-rekvizit.index')
            ->with('muvaffaqiyat', "«{$tashkilotRekvizit->nomi}» yangilandi.");
    }
    public function destroy(TashkilotRekvizit $tashkilotRekvizit) {
        if ($tashkilotRekvizit->asosiy) return back()->with('xato', "Asosiy rekvizitni o'chirish mumkin emas.");
        $nomi = $tashkilotRekvizit->nomi; $tashkilotRekvizit->delete();
        return back()->with('muvaffaqiyat', "«{$nomi}» o'chirildi.");
    }
    private function validate(Request $r): array {
        return $r->validate([
            'nomi'              => 'required|string|max:250',
            'qisqa_nomi'        => 'nullable|string|max:100',
            'stir'              => 'nullable|string|max:20',
            'mfo'               => 'nullable|string|max:9',
            'bank_nomi'         => 'nullable|string|max:200',
            'hisob_raqam'       => 'nullable|string|max:30',
            'tranzit_hisob'     => 'nullable|string|max:30',
            'yuridik_manzil'    => 'nullable|string',
            'haqiqiy_manzil'    => 'nullable|string',
            'telefon'           => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:100',
            'direktor_ism'      => 'nullable|string|max:200',
            'hisobchi_ism'      => 'nullable|string|max:200',
            'imzochi_ism'       => 'nullable|string|max:200',
            'imzochi_lavozim'   => 'nullable|string|max:200',
            'filial_id'         => 'nullable|exists:filiallar,id',
            'asosiy'            => 'nullable|boolean',
            'holat'             => 'required|in:faol,nofaol',
        ]);
    }
}
