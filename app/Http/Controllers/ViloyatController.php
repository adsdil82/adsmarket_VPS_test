<?php
namespace App\Http\Controllers;

use App\Models\Viloyat;
use App\Models\Tuman;
use Illuminate\Http\Request;

class ViloyatController extends Controller
{
    /** Ro'yxat sahifasi */
    public function index()
    {
        $viloyatlar = Viloyat::withCount('tumanlar')->orderBy('sort_order')->get();
        return view('malumotnamalar.viloyatlar.index', compact('viloyatlar'));
    }

    /** Viloyat AJAX JSON (Hybrid Pochta modal va boshqa dropdown lar uchun) */
    public function apiRoyhati(): \Illuminate\Http\JsonResponse
    {
        $list = Viloyat::orderBy('sort_order')->get(['id', 'nomi']);
        return response()->json($list);
    }

    /** Tuman AJAX JSON — viloyat_id bo'yicha filter */
    public function apiTumanlar(Viloyat $viloyat): \Illuminate\Http\JsonResponse
    {
        $list = $viloyat->tumanlar()->get(['id', 'nomi', 'viloyat_id']);
        return response()->json($list);
    }

    /** Barcha tumanlar JSON (optional viloyat_id filter) */
    public function apiBarcha(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Tuman::with('viloyat:id,nomi')->orderBy('sort_order');
        if ($request->filled('viloyat_id')) {
            $query->where('viloyat_id', $request->viloyat_id);
        }
        return response()->json($query->get(['id', 'viloyat_id', 'nomi']));
    }

    // ── CRUD (faqat admin) ───────────────────────────────────────────────

    public function storeViloyat(Request $request): \Illuminate\Http\JsonResponse
    {
        $d = $request->validate([
            'nomi'       => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);
        $viloyat = Viloyat::create([
            'nomi'       => $d['nomi'],
            'sort_order' => $d['sort_order'] ?? Viloyat::max('sort_order') + 1,
        ]);
        return response()->json(['ok' => true, 'viloyat' => $viloyat]);
    }

    public function updateViloyat(Viloyat $viloyat, Request $request): \Illuminate\Http\JsonResponse
    {
        $d = $request->validate([
            'nomi'       => 'required|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);
        $viloyat->update($d);
        return response()->json(['ok' => true]);
    }

    public function storeTuman(Request $request): \Illuminate\Http\JsonResponse
    {
        $d = $request->validate([
            'viloyat_id' => 'required|exists:viloyatlar,id',
            'nomi'       => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
        ]);
        $tuman = Tuman::create([
            'viloyat_id' => $d['viloyat_id'],
            'nomi'       => $d['nomi'],
            'sort_order' => $d['sort_order'] ?? Tuman::where('viloyat_id', $d['viloyat_id'])->max('sort_order') + 1,
        ]);
        return response()->json(['ok' => true, 'tuman' => $tuman]);
    }

    public function updateTuman(Tuman $tuman, Request $request): \Illuminate\Http\JsonResponse
    {
        $d = $request->validate([
            'nomi'       => 'required|string|max:150',
            'sort_order' => 'nullable|integer',
        ]);
        $tuman->update($d);
        return response()->json(['ok' => true]);
    }
    /** Kirillcha nomlarni qayta tiklash (admin sozlamalaridan) — viloyat + tumanlar */
    public function nomlarYangilash(): \Illuminate\Http\JsonResponse
    {
        $jsonPath = storage_path('app/viloyatlar_kirill.json');
        if (!file_exists($jsonPath)) {
            return response()->json(['ok' => false, 'message' => 'viloyatlar_kirill.json topilmadi']);
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        $vilUpdated = 0;
        foreach ($data['viloyatlar'] ?? [] as $v) {
            $vilUpdated += Viloyat::where('id', $v['id'])->update([
                'nomi'        => $v['nomi'],
                'kirill_nomi' => $v['kirill_nomi'],
            ]);
        }

        $tumUpdated = 0;
        foreach ($data['tumanlar'] ?? [] as $t) {
            $tumUpdated += Tuman::where('id', $t['id'])->update([
                'nomi'        => $t['nomi'],
                'kirill_nomi' => $t['kirill_nomi'],
            ]);
        }

        return response()->json([
            'ok'        => true,
            'viloyat'   => $vilUpdated,
            'tuman'     => $tumUpdated,
            'yangilandi'=> $vilUpdated + $tumUpdated,
        ]);
    }
}
