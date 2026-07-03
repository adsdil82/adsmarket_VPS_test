<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\Harajat;
use App\Models\HarajatTuri;
use App\Models\PulOqim;
use App\Services\TulovService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HarajatController extends Controller
{
    public function __construct(private TulovService $tulovService)
    {
    }

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;

        $danSana   = $request->dan_sana   ?? now()->startOfMonth()->toDateString();
        $gachaSana = $request->gacha_sana ?? now()->toDateString();

        $base = Harajat::with(['filial', 'xodim', 'kategoriya', 'harajatTuri', 'tegishliXodim'])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereBetween('sana', [$danSana, $gachaSana])
            ->when($request->turi,    fn($q) => $q->where('turi', $request->turi))
            ->when($request->qidiruv, fn($q) => $q->where('mazmuni', 'like', '%'.$request->qidiruv.'%'));

        $jami = (clone $base)->sum('summa');

        $harajatlar = $base->orderByDesc('sana')->orderByDesc('id')->paginate(30)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        $turlari = Harajat::select('turi')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->distinct()->orderBy('turi')->pluck('turi');

        return view('harajatlar.index', compact(
            'harajatlar', 'filiallar', 'filialId',
            'danSana', 'gachaSana', 'jami', 'turlari'
        ));
    }

    public function create()
    {
        $user      = Auth::user();
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $harajatTurlari = HarajatTuri::faol()->with('kategoriya')->orderBy('sort_order')->orderBy('nomi')->get();
        $xodimlar  = Foydalanuvchi::where('holat', 'faol')->orderBy('ism_familiya')->get(['id', 'ism_familiya']);
        return view('harajatlar.create', compact('filiallar', 'harajatTurlari', 'xodimlar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'filial_id'         => 'required|exists:filiallar,id',
            'sana'              => 'required|date',
            'harajat_turi_id'   => 'required|exists:harajat_turlari,id',
            'tegishli_xodim_id' => 'nullable|exists:foydalanuvchilar,id',
            'schetchik_raqami'  => 'nullable|string|max:100',
            'kassa_turi'        => 'required|in:naqd,terminal,bank',
            'summa'             => 'required|numeric|not_in:0',
            'mazmuni'           => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            $turi = HarajatTuri::with('kategoriya')->findOrFail($request->harajat_turi_id);

            $harajat = Harajat::create([
                'filial_id'         => $request->filial_id,
                'xodim_id'          => Auth::id(),
                'sana'              => $request->sana,
                'turi'              => $turi->nomi,
                'harajat_turi_id'   => $turi->id,
                'tegishli_xodim_id' => $turi->talab_xodim ? $request->tegishli_xodim_id : null,
                'schetchik_raqami'  => $turi->talab_schetchik ? $request->schetchik_raqami : null,
                'kassa_turi'        => $request->kassa_turi,
                'pul_kategoriya_id' => $turi->pul_kategoriya_id,
                'summa'             => $request->summa,
                'mazmuni'           => $request->mazmuni,
            ]);

            $this->pulOqimigaYoz($harajat, $turi);
        });

        return redirect()->route('harajatlar.index')
            ->with('muvaffaqiyat', 'Harajat saqlandi.');
    }

    public function edit(Harajat $harajat)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $harajat->filial_id !== $user->filial_id) {
            abort(403);
        }
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : Filial::where('id', $user->filial_id)->get();
        $harajatTurlari = HarajatTuri::faol()->with('kategoriya')->orderBy('sort_order')->orderBy('nomi')->get();
        // Eski (faol bo'lmagan yoki o'chirilgan) turdan foydalangan bo'lsa, ro'yxatda yo'qolib qolmasligi uchun qo'shamiz.
        if ($harajat->harajat_turi_id && !$harajatTurlari->contains('id', $harajat->harajat_turi_id)) {
            $eski = HarajatTuri::with('kategoriya')->find($harajat->harajat_turi_id);
            if ($eski) $harajatTurlari->push($eski);
        }
        $xodimlar  = Foydalanuvchi::where('holat', 'faol')->orderBy('ism_familiya')->get(['id', 'ism_familiya']);
        return view('harajatlar.edit', compact('harajat', 'filiallar', 'harajatTurlari', 'xodimlar'));
    }

    public function update(Request $request, Harajat $harajat)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $harajat->filial_id !== $user->filial_id) {
            abort(403);
        }
        $request->validate([
            'filial_id'         => 'required|exists:filiallar,id',
            'sana'              => 'required|date',
            'harajat_turi_id'   => 'required|exists:harajat_turlari,id',
            'tegishli_xodim_id' => 'nullable|exists:foydalanuvchilar,id',
            'schetchik_raqami'  => 'nullable|string|max:100',
            'kassa_turi'        => 'required|in:naqd,terminal,bank',
            'summa'             => 'required|numeric|not_in:0',
            'mazmuni'           => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $harajat) {
            $turi = HarajatTuri::with('kategoriya')->findOrFail($request->harajat_turi_id);

            $harajat->update([
                'filial_id'         => $request->filial_id,
                'sana'              => $request->sana,
                'turi'              => $turi->nomi,
                'harajat_turi_id'   => $turi->id,
                'tegishli_xodim_id' => $turi->talab_xodim ? $request->tegishli_xodim_id : null,
                'schetchik_raqami'  => $turi->talab_schetchik ? $request->schetchik_raqami : null,
                'kassa_turi'        => $request->kassa_turi,
                'pul_kategoriya_id' => $turi->pul_kategoriya_id,
                'summa'             => $request->summa,
                'mazmuni'           => $request->mazmuni,
            ]);

            $mavjudYozuv = PulOqim::where('manba_tur', 'harajat')->where('manba_id', $harajat->id)->first();

            if ($mavjudYozuv) {
                $kassa = \App\Models\Kassa::where('filial_id', $harajat->filial_id)
                    ->where('tur', $harajat->kassa_turi)->faol()->first()
                    ?? \App\Models\Kassa::where('filial_id', $harajat->filial_id)->faol()->first();

                $summa = (float) $harajat->summa;
                $mavjudYozuv->update([
                    'filial_id'     => $harajat->filial_id,
                    'kassa_id'      => $kassa?->id ?? $mavjudYozuv->kassa_id,
                    'kategoriya_id' => $turi->pul_kategoriya_id,
                    'yunalish'      => $summa < 0 ? 'kirim' : 'chiqim',
                    'sana'          => $harajat->sana->toDateString(),
                    'summa'         => abs($summa),
                    'izoh'          => $harajat->turi . ($harajat->mazmuni ? " — {$harajat->mazmuni}" : ''),
                ]);
            } else {
                $this->pulOqimigaYoz($harajat, $turi);
            }
        });

        return redirect()->route('harajatlar.index')
            ->with('muvaffaqiyat', 'Harajat yangilandi.');
    }

    public function destroy(Harajat $harajat)
    {
        DB::transaction(function () use ($harajat) {
            $this->tulovService->pulOqiminiOchir('harajat', $harajat->id);
            $harajat->delete();
        });

        return back()->with('muvaffaqiyat', "Harajat o'chirildi.");
    }

    /**
     * Harajatni Pul Oqimlariga yozadi. Summa manfiy bo'lsa (masalan inkasso,
     * qaytarish) — KIRIM sifatida, musbat bo'lsa — CHIQIM sifatida yoziladi.
     */
    private function pulOqimigaYoz(Harajat $harajat, HarajatTuri $turi): void
    {
        $summa = (float) $harajat->summa;
        if ($summa == 0 || !$turi->kategoriya) return;

        $this->tulovService->pulOqimigaYozKassaTuri(
            filialId: $harajat->filial_id,
            kassaTuri: $harajat->kassa_turi,
            summa: abs($summa),
            sana: $harajat->sana->toDateString(),
            kategoriyaKodi: $turi->kategoriya->kod,
            izoh: $harajat->turi . ($harajat->mazmuni ? " — {$harajat->mazmuni}" : ''),
            manbaTur: 'harajat',
            manbaId: $harajat->id,
            yunalish: $summa < 0 ? 'kirim' : 'chiqim',
        );
    }
}
