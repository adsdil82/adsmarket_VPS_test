<?php
namespace App\Http\Controllers;

use App\Models\TaminotKirim;
use App\Models\TovarGuruh;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;

class BarcodeLabelController extends Controller
{
    /** Etiketka chop etish sahifasi — tovar tanlash + o'lcham tanlash. */
    public function index(Request $request)
    {
        $guruhlar = TovarGuruh::faol()->orderBy('nomi')->get();

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

        return view('ombor.etiketka.index', compact('guruhlar', 'oldindanTanlangan'));
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
            ->get(['id', 'nomi', 'barkod', 'sotish_narx', 'birlik', 'guruh_id']);

        return response()->json($tovarlar);
    }
}
