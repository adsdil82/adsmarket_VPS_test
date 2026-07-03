<?php
namespace App\Http\Controllers;

use App\Models\BLBolim;
use App\Models\BLQator;
use App\Models\Filial;
use App\Services\BLReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BLController extends Controller
{
    public function __construct(private BLReportService $service) {}

    public function index(Request $request)
    {
        $yil = (int) ($request->yil ?: now()->year);
        $filialId = $request->filial_id ? (int) $request->filial_id : null;

        $natija = $this->service->hisobotOylik($yil, $filialId);
        $filiallar = Filial::faol()->get();
        $bolimlarRoyxat = BLBolim::orderBy('sort_order')->get();

        return view('pul-oqimlari.balans.index', array_merge($natija, [
            'yil' => $yil, 'filialId' => $filialId, 'filiallar' => $filiallar,
            'bolimlarRoyxat' => $bolimlarRoyxat,
        ]));
    }

    /** Yangi (qo'lda kiritiladigan) balans moddasi qo'shish. */
    public function qatorStore(Request $request)
    {
        $request->validate([
            'bolim_id' => 'required|exists:bl_bolimlar,id',
            'nomi'     => 'required|string|max:200',
        ]);

        BLQator::create([
            'bolim_id'          => $request->bolim_id,
            'nomi'              => trim($request->nomi),
            'hisoblash_turi'    => 'qolda',
            'joriy_holat_faqat' => false,
            'sort_order'        => (BLQator::where('bolim_id', $request->bolim_id)->max('sort_order') ?? 0) + 1,
            'holat'             => 'faol',
        ]);

        return back()->with('muvaffaqiyat', "Yangi modda «{$request->nomi}» qo'shildi.");
    }

    /**
     * Qo'lda qo'shilgan moddani o'chirish (faqat "qolda" turdagilarni,
     * avtomatlarni emas). Agar moddada kiritilgan (nolga teng bo'lmagan)
     * summalar tarixi bo'lsa — o'chirish taqiqlanadi (moliyaviy ma'lumot
     * yo'qolib ketmasligi uchun); hech qanday real summa kiritilmagan
     * bo'lsa (bo'sh yoki hammasi 0) — bemalol o'chiriladi.
     */
    public function qatorDestroy(BLQator $qator)
    {
        if ($qator->hisoblash_turi !== 'qolda') {
            return back()->with('xato', "Avtomatik hisoblanadigan moddani o'chirib bo'lmaydi.");
        }

        $summaliYozuvlar = $qator->qiymatlar()->where('summa', '!=', 0)->count();
        if ($summaliYozuvlar > 0) {
            return back()->with('xato',
                "«{$qator->nomi}» moddasida {$summaliYozuvlar} ta davrga summa kiritilgan — tarixiy ma'lumot yo'qolmasligi uchun o'chirib bo'lmaydi. Avval barcha oylardagi summalarni 0 ga tenglashtiring."
            );
        }

        $nomi = $qator->nomi;
        $qator->qiymatlar()->delete(); // faqat nol qiymatli (bo'sh) yozuvlar
        $qator->delete();
        return back()->with('muvaffaqiyat', "«{$nomi}» moddasi o'chirildi.");
    }

    /** Modda nomini tahrirlash — noto'g'ri nomlangan bo'lsa tuzatish uchun (barcha turdagi qatorlarga ruxsat). */
    public function qatorUpdate(Request $request, BLQator $qator)
    {
        $request->validate(['nomi' => 'required|string|max:200']);

        $qator->update(['nomi' => trim($request->nomi)]);

        return response()->json(['ok' => true, 'nomi' => $qator->nomi]);
    }

    /**
     * Ajax: "qolda" turdagi qator uchun sana bo'yicha qiymat saqlash.
     * Sahifani to'liq qayta yuklamaslik uchun — shu yilning YANGILANGAN
     * bo'lim jamilari va balans farqlarini ham darhol qaytaradi, frontend
     * faqat tegishli katakchalarni yangilaydi (tezkor, bitta so'rov).
     */
    public function qiymatSaqlash(Request $request)
    {
        $request->validate([
            'qator_id'  => 'required|exists:bl_qatorlari,id',
            'filial_id' => 'nullable|exists:filiallar,id',
            'sana'      => 'required|date',
            'summa'     => 'required|numeric',
            'yil'       => 'required|integer',
            'izoh'      => 'nullable|string|max:300',
        ]);

        $qator = BLQator::findOrFail($request->qator_id);
        if ($qator->hisoblash_turi !== 'qolda') {
            return response()->json(['xato' => "Bu qator avtomatik hisoblanadi, qo'lda o'zgartirib bo'lmaydi."], 422);
        }

        $this->service->qiymatSaqlash(
            $qator->id, $request->filial_id, $request->sana,
            (float) $request->summa, $request->izoh, Auth::id(),
        );

        // Yangilangan holatni darhol hisoblab, jadvalni qayta chizish uchun qaytaramiz.
        $natija = $this->service->hisobotOylik((int) $request->yil, $request->filial_id ? (int) $request->filial_id : null);

        return response()->json([
            'ok'                => true,
            'bolim_jami_oylik'  => $natija['bolim_jami_oylik'],
            'balans_farqi_oylik'=> $natija['balans_farqi_oylik'],
            'oxirgi_oy'         => $natija['oxirgi_oy'],
        ]);
    }
}
