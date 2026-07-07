<?php
namespace App\Http\Controllers;

use App\Models\EtiketkaShablon;
use App\Models\TaminotKirim;
use App\Models\TovarGuruh;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BarcodeLabelController extends Controller
{
    /** Nasiya jadvalida ko'rsatiladigan oy variantlari. */
    public const NASIYA_OYLAR = [1, 3, 9, 12];

    /** Naqd to'lovda beriladigan chegirma foizi (nasiya narxidan). */
    public const NAQD_CHEGIRMA_FOIZ = 15;

    /** Etiketka chop etish sahifasi — tovar tanlash + o'lcham tanlash. */
    public function index(Request $request)
    {
        $guruhlar = TovarGuruh::faol()->orderBy('nomi')->get();
        $shablonlar = EtiketkaShablon::orderByRaw("turi = 'built_in' desc")->orderBy('id')->get();

        // "Kirim #X dan keyin etiketka chop etish" havolasi orqali kelingan
        // bo'lsa — o'sha kirimdagi tovarlar oldindan belgilangan holda ochiladi.
        $oldindanTanlangan = collect();
        if ($request->kirim_id) {
            $kirim = TaminotKirim::with('qatorlar.tovar')->find($request->kirim_id);
            if ($kirim) {
                $oldindanTanlangan = $kirim->qatorlar->filter(fn($q) => $q->tovar_id)->map(fn($q) => [
                    'id' => $q->tovar_id, 'miqdor' => (int) ceil($q->miqdor),
                ]);
            }
        }

        return view('ombor.etiketka.index', compact('guruhlar', 'oldindanTanlangan', 'shablonlar'));
    }

    /** Ajax: tovarlar ro'yxati (barkod bilan, katalogdagi barchasi — omborga bog'liq emas). */
    public function tovarlar(Request $request)
    {
        $tovarlar = TovarKatalog::faol()
            ->when($request->guruh_id, fn($q) => $q->where('guruh_id', $request->guruh_id))
            ->when($request->qidiruv, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('nomi', 'like', "%{$request->qidiruv}%")
                   ->orWhere('barkod', 'like', "%{$request->qidiruv}%");
            }))
            ->orderBy('nomi')
            ->limit(200)
            ->get(['id', 'nomi', 'barkod', 'tan_narx', 'sotish_narx', 'nasiya_narx', 'birlik', 'guruh_id']);

        return response()->json($tovarlar);
    }

    /** Ajax: barcha shablonlar ro'yxati (built-in + custom). */
    public function shablonlar()
    {
        return response()->json(
            EtiketkaShablon::orderByRaw("turi = 'built_in' desc")->orderBy('id')->get()
        );
    }

    /** Ajax: konstruktorda tuzilgan yangi shablonni saqlash. */
    public function shablonSaqlash(Request $request)
    {
        $data = $request->validate([
            'nomi'              => 'required|string|max:100',
            'reng_fon'          => 'required|string|max:20',
            'reng_matn'         => 'required|string|max:20',
            'reng_urgu'         => 'required|string|max:20',
            'belgi_matni'       => 'nullable|string|max:40',
            'joylashuv'         => 'required|array',
            'joylashuv.top'     => 'required|array',
            'joylashuv.inner'   => 'required|array',
            'joylashuv.bottom'  => 'required|array',
            'joylashuv.barcode' => 'required|array',
        ]);

        $shablon = EtiketkaShablon::create([
            'nomi'        => $data['nomi'],
            'turi'        => 'custom',
            'reng_fon'    => $data['reng_fon'],
            'reng_matn'   => $data['reng_matn'],
            'reng_urgu'   => $data['reng_urgu'],
            'belgi_matni' => $data['belgi_matni'] ?? null,
            'joylashuv'   => $data['joylashuv'],
            'created_by'  => Auth::id(),
        ]);

        return response()->json(['ok' => true, 'shablon' => $shablon]);
    }

    /** Ajax: custom shablonni o'chirish (built-in o'chirilmaydi). */
    public function shablonOchirish(EtiketkaShablon $shablon)
    {
        if ($shablon->turi === 'built_in') {
            return response()->json(['ok' => false, 'xabar' => "Standart shablonlar o'chirilmaydi."], 422);
        }
        $shablon->delete();
        return response()->json(['ok' => true]);
    }
}
