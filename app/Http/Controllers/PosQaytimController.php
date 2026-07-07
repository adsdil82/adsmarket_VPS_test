<?php

namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\PosQaytim;
use App\Models\PosQaytimTafsilot;
use App\Models\PosSotuv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosQaytimController extends Controller
{
    public function __construct(
        private \App\Services\StockService $stockService,
        private \App\Services\TulovService $tulovService,
    ) {}

    /** Qaytim formasi — sotuv qatorlari, har biri uchun qolgan (hali qaytarilmagan) miqdor bilan. */
    public function boshlash(PosSotuv $sotuv)
    {
        if ($sotuv->holat !== 'tugallangan') {
            return back()->with('xato', "Faqat tugallangan sotuvga qaytim qilish mumkin.");
        }

        $smena = PosSmenaController::joriy($sotuv->filial_id);
        if (!$smena) {
            return redirect()->route('pos.smena.ochish-forma')->with('xato', "Qaytim qilish uchun avval smenani oching.");
        }

        $sotuv->load('tafsilot.tovar');
        $qatorlar = $sotuv->tafsilot->map(function ($t) {
            $qaytarilgan = $t->qaytarilganMiqdor();
            return (object) [
                'tafsilot_id'  => $t->id,
                'tovar_id'     => $t->tovar_id,
                'nomi'         => $t->tovar->nomi ?? 'Nomsiz',
                'birlik'       => $t->tovar->birlik ?? 'dona',
                'narx'         => $t->narx,
                'sotilgan'     => $t->miqdor,
                'qaytarilgan'  => $qaytarilgan,
                'qolgan'       => max(0, $t->miqdor - $qaytarilgan),
            ];
        })->filter(fn($q) => $q->qolgan > 0)->values();

        if ($qatorlar->isEmpty()) {
            return back()->with('xato', "Bu sotuvdagi barcha tovarlar allaqachon qaytarilgan.");
        }

        return view('ombor.pos.qaytim.boshlash', compact('sotuv', 'qatorlar', 'smena'));
    }

    /** Qaytimni saqlash. */
    public function saqlash(Request $request, PosSotuv $sotuv)
    {
        $request->validate([
            'tolov_turi'          => 'required|in:naqd,plastik',
            'sabab'               => 'required|in:fikr_ozgardi,nosoz_mahsulot,notogri_mahsulot,boshqa',
            'izoh'                => 'nullable|string|max:500',
            'qatorlar'            => 'required|array|min:1',
            'qatorlar.*.tafsilot_id' => 'required|exists:pos_tafsilot,id',
            'qatorlar.*.miqdor'      => 'required|numeric|min:0',
        ]);

        if ($sotuv->holat !== 'tugallangan') {
            return response()->json(['xato' => "Faqat tugallangan sotuvga qaytim qilish mumkin."], 422);
        }

        $smena = PosSmenaController::joriy($sotuv->filial_id);
        if (!$smena) {
            return response()->json(['xato' => "Ochiq smena topilmadi. Avval smenani oching."], 422);
        }

        $tanlangan = collect($request->qatorlar)->filter(fn($q) => (float) $q['miqdor'] > 0);
        if ($tanlangan->isEmpty()) {
            return response()->json(['xato' => "Kamida bitta tovar miqdorini kiriting."], 422);
        }

        // Har bir qator uchun: sotilgan miqdordan (avvalgi qaytimlar ayrilib) oshmasligini tekshiramiz.
        $tafsilotlar = \App\Models\PosTafsilot::with('tovar')
            ->whereIn('id', $tanlangan->pluck('tafsilot_id'))
            ->where('sotuv_id', $sotuv->id)
            ->get()->keyBy('id');

        foreach ($tanlangan as $q) {
            $tafsilot = $tafsilotlar[$q['tafsilot_id']] ?? null;
            if (!$tafsilot) {
                return response()->json(['xato' => "Sotuv qatori topilmadi."], 422);
            }
            $qaytarilgan = $tafsilot->qaytarilganMiqdor();
            $qolgan = $tafsilot->miqdor - $qaytarilgan;
            if ((float) $q['miqdor'] > $qolgan + 0.0001) {
                return response()->json(['xato' => "«{$tafsilot->tovar->nomi}»: faqat {$qolgan} {$tafsilot->tovar->birlik} qaytarish mumkin."], 422);
            }
        }

        $ombor = $this->stockService->asosiyOmbor($sotuv->filial_id);
        if (!$ombor) {
            return response()->json(['xato' => "Bu filial uchun ombor topilmadi."], 422);
        }

        $qaytim = DB::transaction(function () use ($request, $sotuv, $smena, $tanlangan, $tafsilotlar, $ombor) {
            $jamiSumma = 0.0;
            foreach ($tanlangan as $q) {
                $tafsilot = $tafsilotlar[$q['tafsilot_id']];
                $jamiSumma += (float) $q['miqdor'] * (float) $tafsilot->narx;
            }

            $qaytim = PosQaytim::create([
                'qaytim_raqami' => PosQaytim::yangiQaytimRaqami($sotuv->filial_id),
                'sotuv_id'      => $sotuv->id,
                'smena_id'      => $smena->id,
                'filial_id'     => $sotuv->filial_id,
                'xodim_id'      => Auth::id(),
                'sana'          => today(),
                'tolov_turi'    => $request->tolov_turi,
                'jami_summa'    => $jamiSumma,
                'sabab'         => $request->sabab,
                'mijoz_ism'     => $sotuv->mijoz_ism,
                'izoh'          => $request->izoh,
                'holat'         => 'tugallangan',
            ]);

            foreach ($tanlangan as $q) {
                $tafsilot = $tafsilotlar[$q['tafsilot_id']];
                $miqdor = (float) $q['miqdor'];
                $summa = $miqdor * (float) $tafsilot->narx;

                PosQaytimTafsilot::create([
                    'qaytim_id'   => $qaytim->id,
                    'tafsilot_id' => $tafsilot->id,
                    'tovar_id'    => $tafsilot->tovar_id,
                    'miqdor'      => $miqdor,
                    'narx'        => $tafsilot->narx,
                    'jami_summa'  => $summa,
                ]);

                // Ombor qoldig'ini tiklash.
                $this->stockService->kirim(
                    $ombor->id, $tafsilot->tovar_id, $miqdor,
                    manbaTur: 'pos_qaytim', manbaId: $qaytim->id,
                    izoh: "POS qaytim #{$qaytim->qaytim_raqami}", harakat: 'kirim',
                );
            }

            return $qaytim;
        });

        // Pul oqimlariga CHIQIM sifatida yozamiz (mijozga qaytarilgan pul).
        $this->tulovService->pulOqimigaYozKassaTuri(
            filialId: $sotuv->filial_id,
            kassaTuri: $request->tolov_turi === 'naqd' ? 'naqd' : 'terminal',
            summa: (float) $qaytim->jami_summa,
            sana: $qaytim->sana->toDateString(),
            kategoriyaKodi: 'CF-2740',
            izoh: "POS qaytim #{$qaytim->qaytim_raqami} (sotuv #{$sotuv->check_raqam})",
            manbaTur: 'pos_qaytim',
            manbaId: $qaytim->id,
            yunalish: 'chiqim',
        );

        return response()->json([
            'muvaffaqiyat'   => true,
            'qaytim_raqami'  => $qaytim->qaytim_raqami,
            'jami_summa'     => $qaytim->jami_summa,
            'qaytim_id'      => $qaytim->id,
        ]);
    }

    /** Qaytimlar ro'yxati. */
    public function royxat(Request $request)
    {
        $user      = Auth::user();
        $filialId  = $user->isAdmin() ? ($request->filial_id ?: null) : $user->filial_id;
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        $sorov = PosQaytim::query()
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->when($request->dan_sana, fn($q) => $q->whereDate('sana', '>=', $request->dan_sana))
            ->when($request->gacha_sana, fn($q) => $q->whereDate('sana', '<=', $request->gacha_sana))
            ->when($request->sabab, fn($q) => $q->where('sabab', $request->sabab));

        $jamiSumma = (clone $sorov)->sum('jami_summa');
        $qaytimlar = $sorov->with(['sotuv', 'xodim', 'filial', 'smena'])
            ->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('ombor.pos.qaytim.royxat', compact('qaytimlar', 'filiallar', 'filialId', 'jamiSumma'));
    }

    public function korish(PosQaytim $qaytim)
    {
        $qaytim->load(['sotuv', 'smena', 'xodim', 'filial', 'tafsilot.tovar']);
        return view('ombor.pos.qaytim.korish', compact('qaytim'));
    }
}
