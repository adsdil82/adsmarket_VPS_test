<?php
namespace App\Http\Controllers;
use App\Models\Valyuta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ValyutaController extends Controller {
    public function index() {
        return view('malumotnamalar.valyutalar.index', ['valyutalar' => Valyuta::orderBy('asosiy','desc')->orderBy('kod')->get()]);
    }
    public function store(Request $request) {
        $d = $request->validate([
            'kod'       => 'required|string|max:10|unique:valyutalar,kod',
            'nomi'      => 'required|string|max:80',
            'belgi'     => 'nullable|string|max:10',
            'kurs'      => 'required|numeric|min:0',
            'kurs_sana' => 'nullable|date',
            'asosiy'    => 'nullable|boolean',
        ]);
        if (!empty($d['asosiy'])) Valyuta::query()->update(['asosiy' => false]);
        Valyuta::create($d);
        return back()->with('muvaffaqiyat', "Valyuta «{$d['kod']}» qo'shildi.");
    }
    public function update(Request $request, Valyuta $valyuta) {
        $d = $request->validate([
            'kod'       => ['required','string','max:10', Rule::unique('valyutalar','kod')->ignore($valyuta->id)],
            'nomi'      => 'required|string|max:80',
            'belgi'     => 'nullable|string|max:10',
            'kurs'      => 'required|numeric|min:0',
            'kurs_sana' => 'nullable|date',
            'asosiy'    => 'nullable|boolean',
            'holat'     => 'required|in:faol,nofaol',
        ]);
        if (!empty($d['asosiy'])) Valyuta::where('id','!=',$valyuta->id)->update(['asosiy' => false]);
        $d['asosiy'] = !empty($d['asosiy']);
        $valyuta->update($d);
        return back()->with('muvaffaqiyat', "Valyuta «{$valyuta->kod}» yangilandi.");
    }
    public function cbuUpdate() {
        try {
            $resp = Http::timeout(10)->get('https://cbu.uz/uz/arkhiv-kursov-valyut/json/');
            if (!$resp->ok()) {
                return back()->with('xato', 'CBU serveriga ulanib bo\'lmadi.');
            }
            $rates = collect($resp->json())->keyBy('Ccy');
            $yangilangan = 0;
            $sana = now()->toDateString();

            Valyuta::where('kod','!=','UZS')->where('holat','faol')->each(function ($v) use ($rates, $sana, &$yangilangan) {
                if ($rates->has($v->kod)) {
                    $nominal = (int)($rates[$v->kod]['Nominal'] ?? 1);
                    $rate    = (float)$rates[$v->kod]['Rate'];
                    $kurs    = $nominal > 1 ? round($rate / $nominal, 4) : $rate;
                    $v->update(['kurs' => $kurs, 'kurs_sana' => $sana]);
                    $yangilangan++;
                }
            });

            return back()->with('muvaffaqiyat', "CBU dan {$yangilangan} ta valyuta kursi yangilandi (sana: {$sana}).");
        } catch (\Exception $e) {
            return back()->with('xato', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function destroy(Valyuta $valyuta) {
        if ($valyuta->asosiy) return back()->with('xato', "Asosiy valyutani o'chirish mumkin emas.");
        $kod = $valyuta->kod; $valyuta->delete();
        return back()->with('muvaffaqiyat', "Valyuta «{$kod}» o'chirildi.");
    }
}
