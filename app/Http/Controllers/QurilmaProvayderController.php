<?php

namespace App\Http\Controllers;

use App\Models\QurilmaProvayder;
use App\Models\QurilmaProvayderSozlama;
use App\Services\DeviceControl\DeviceControlManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QurilmaProvayderController extends Controller
{
    public function __construct(private DeviceControlManager $manager) {}

    public function index()
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $provayderlar = QurilmaProvayder::with('sozlamalar')->orderBy('sort_order')->get();
        $kodlar       = $this->manager->kodlar();
        return view('qurilmalar.provayderlar.index', compact('provayderlar','kodlar'));
    }

    public function toggle(QurilmaProvayder $provayder)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $provayder->update(['faol' => !$provayder->faol]);
        return back()->with('muvaffaqiyat', "'{$provayder->nomi}' " . ($provayder->faol ? 'faollashtirildi' : 'o\'chirildi') . '.');
    }

    public function toggleMock(QurilmaProvayder $provayder)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $provayder->update(['mock_rejim' => !$provayder->mock_rejim]);
        return back()->with('muvaffaqiyat', "Mock rejim " . ($provayder->mock_rejim ? 'yoqildi' : 'o\'chirildi') . '.');
    }

    public function sozlamalar(QurilmaProvayder $provayder)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        $sozlamalar = $provayder->sozlamalar()->orderBy('id')->get();
        return view('qurilmalar.provayderlar.sozlamalar', compact('provayder','sozlamalar'));
    }

    public function sozlama_saqlash(Request $request, QurilmaProvayder $provayder)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $request->validate([
            'sozlamalar'         => 'required|array',
            'sozlamalar.*.kalit' => 'required|string|max:100',
            'sozlamalar.*.qiymat'=> 'nullable|string',
        ]);

        foreach ($request->sozlamalar as $item) {
            $sozlama = $provayder->sozlamalar()->firstOrCreate(
                ['kalit' => $item['kalit']],
                ['sarlavha' => $item['kalit'], 'tur' => 'string']
            );
            // Secret bo'lsa faqat yangi qiymat kiritilsa yangilansin
            if ($sozlama->isSecret() && empty($item['qiymat'])) continue;
            $sozlama->update(['qiymat' => $item['qiymat']]);
        }

        return back()->with('muvaffaqiyat', 'Sozlamalar saqlandi.');
    }

    public function sozlama_qoshish(Request $request, QurilmaProvayder $provayder)
    {
        if (!Auth::user()->isAdmin()) abort(403);

        $request->validate([
            'kalit'    => 'required|string|max:100|unique:qurilma_provayder_sozlamalari,kalit,NULL,id,provayder_id,' . $provayder->id,
            'qiymat'   => 'nullable|string',
            'tur'      => 'required|in:string,boolean,integer,json,secret',
            'sarlavha' => 'required|string|max:200',
            'majburiy' => 'boolean',
        ]);

        $provayder->sozlamalar()->create($request->only('kalit','qiymat','tur','sarlavha','tavsif','majburiy'));

        return back()->with('muvaffaqiyat', 'Sozlama qo\'shildi.');
    }

    public function sozlama_delete(QurilmaProvayder $provayder, QurilmaProvayderSozlama $sozlama)
    {
        if (!Auth::user()->isAdmin()) abort(403);
        if ($sozlama->provayder_id !== $provayder->id) abort(404);
        $sozlama->delete();
        return back()->with('muvaffaqiyat', 'Sozlama o\'chirildi.');
    }
}
