<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\KunYopishLogi;
use App\Models\OperatsionKun;
use App\Services\OperatsionKunService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OperatsionKunController extends Controller
{
    public function __construct(private OperatsionKunService $svc) {}

    /** Joriy kun holati — filiallar bo'yicha jadval. */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();

        $kunlar = collect();
        foreach ($filiallar as $filial) {
            $kunlar->push($this->svc->joriyKun($filial->id));
        }

        return view('operatsion_kun.index', compact('kunlar', 'filiallar'));
    }

    /** AJAX: yopishdan oldin oldindan ko'rish (statistika). */
    public function oldinKorish(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|integer|exists:filiallar,id',
            'sana'      => 'required|date',
        ]);

        $natija = $this->svc->yopishOldinKorish((int) $request->filial_id, $request->sana);

        return response()->json(['ok' => true] + $natija);
    }

    public function yopish(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|integer|exists:filiallar,id',
            'sana'      => 'required|date',
        ]);

        try {
            $natija = $this->svc->kunniYop((int) $request->filial_id, $request->sana, Auth::user());
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'xato' => $e->getMessage()], 422);
            }

            return back()->with('xato', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'natija' => $natija]);
        }

        return back()->with('muvaffaqiyat', "Kun ({$request->sana}) yopildi.");
    }

    public function ochish(Request $request)
    {
        $request->validate([
            'filial_id' => 'required|integer|exists:filiallar,id',
            'sana'      => 'required|date',
            'izoh'      => 'required|string|min:3',
        ]);

        try {
            $this->svc->kunniOch((int) $request->filial_id, $request->sana, Auth::user(), $request->izoh);
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'xato' => $e->getMessage()], 422);
            }

            return back()->with('xato', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('muvaffaqiyat', "Kun ({$request->sana}) qayta ochildi.");
    }

    /** Yopish/ochish tarixi — filtr (filial, sana oralig'i). */
    public function tarix(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? $request->get('filial_id') : $user->filial_id;
        $sanaDan   = $request->get('sana_dan');
        $sanaGacha = $request->get('sana_gacha');

        $loglar = KunYopishLogi::with(['operatsionKun.filial', 'user'])
            ->when($filialId, fn ($q) => $q->whereHas('operatsionKun', fn ($k) => $k->where('filial_id', $filialId)))
            ->when($sanaDan, fn ($q) => $q->whereDate('vaqt', '>=', $sanaDan))
            ->when($sanaGacha, fn ($q) => $q->whereDate('vaqt', '<=', $sanaGacha))
            ->latest('vaqt')
            ->paginate(30)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        return view('operatsion_kun.tarix', compact('loglar', 'filiallar', 'filialId', 'sanaDan', 'sanaGacha'));
    }
}
