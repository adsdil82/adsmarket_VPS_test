<?php
namespace App\Http\Controllers;
use App\Models\Filial;
use App\Models\ShartnomRekvizit;
use App\Models\TashkilotRekvizit;
use Illuminate\Http\Request;

class ShartnomRekvizitController extends Controller {
    public function index() {
        $rekvizitlar  = ShartnomRekvizit::with(['filial','tashkilotRekvizit'])->orderBy('asosiy','desc')->orderBy('nomi')->get();
        $filiallar    = Filial::faol()->orderBy('nomi')->get(['id','nomi']);
        $tashkilotlar = TashkilotRekvizit::where('holat','faol')->orderBy('nomi')->get(['id','nomi']);
        return view('malumotnamalar.shartnoma-rekvizit.index', compact('rekvizitlar','filiallar','tashkilotlar'));
    }
    public function store(Request $request) {
        $d = $request->validate([
            'nomi'                  => 'required|string|max:200',
            'filial_id'             => 'nullable|exists:filiallar,id',
            'tashkilot_rekvizit_id' => 'nullable|exists:tashkilot_rekvizitlari,id',
            'prefiks'               => 'nullable|string|max:20',
            'keyingi_raqam'         => 'nullable|integer|min:1',
            'raqam_formati'         => 'nullable|string|max:80',
            'imzochi_ism'           => 'nullable|string|max:200',
            'imzochi_lavozim'       => 'nullable|string|max:200',
            'asosiy'                => 'nullable|boolean',
            'holat'                 => 'required|in:faol,nofaol',
        ]);
        if (!empty($d['asosiy'])) ShartnomRekvizit::query()->update(['asosiy'=>false]);
        $d['asosiy']        = !empty($d['asosiy']);
        $d['keyingi_raqam'] = $d['keyingi_raqam'] ?? 1;
        $d['raqam_formati'] = $d['raqam_formati'] ?? '{PREFIX}-{RAQAM}';
        ShartnomRekvizit::create($d);
        return back()->with('muvaffaqiyat', "Shartnoma rekviziti «{$d['nomi']}» qo'shildi.");
    }
    public function update(Request $request, ShartnomRekvizit $shartnomRekvizit) {
        $d = $request->validate([
            'nomi'                  => 'required|string|max:200',
            'filial_id'             => 'nullable|exists:filiallar,id',
            'tashkilot_rekvizit_id' => 'nullable|exists:tashkilot_rekvizitlari,id',
            'prefiks'               => 'nullable|string|max:20',
            'keyingi_raqam'         => 'nullable|integer|min:1',
            'raqam_formati'         => 'nullable|string|max:80',
            'imzochi_ism'           => 'nullable|string|max:200',
            'imzochi_lavozim'       => 'nullable|string|max:200',
            'asosiy'                => 'nullable|boolean',
            'holat'                 => 'required|in:faol,nofaol',
        ]);
        if (!empty($d['asosiy'])) ShartnomRekvizit::where('id','!=',$shartnomRekvizit->id)->update(['asosiy'=>false]);
        $d['asosiy'] = !empty($d['asosiy']);
        $shartnomRekvizit->update($d);
        return back()->with('muvaffaqiyat', "«{$shartnomRekvizit->nomi}» yangilandi.");
    }
    public function destroy(ShartnomRekvizit $shartnomRekvizit) {
        if ($shartnomRekvizit->asosiy) return back()->with('xato', "Asosiy rekvizitni o'chirish mumkin emas.");
        $nomi = $shartnomRekvizit->nomi; $shartnomRekvizit->delete();
        return back()->with('muvaffaqiyat', "«{$nomi}» o'chirildi.");
    }
}
