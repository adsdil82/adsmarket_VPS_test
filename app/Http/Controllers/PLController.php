<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\PLQator;
use App\Services\PLReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PLController extends Controller
{
    public function __construct(private PLReportService $service) {}

    public function index(Request $request)
    {
        $yil = (int) ($request->yil ?: now()->year);
        $filialId = $request->filial_id ? (int) $request->filial_id : null;

        $natija = $this->service->hisobot($yil, $filialId);
        $filiallar = Filial::faol()->get();

        return view('pul-oqimlari.moliyaviy-natija.index', array_merge($natija, [
            'yil' => $yil, 'filialId' => $filialId, 'filiallar' => $filiallar,
        ]));
    }

    /** Ajax: "qolda" turdagi qator uchun bitta oy qiymatini saqlash. */
    public function qiymatSaqlash(Request $request)
    {
        $request->validate([
            'qator_id'  => 'required|exists:pl_qatorlari,id',
            'filial_id' => 'nullable|exists:filiallar,id',
            'yil'       => 'required|integer|min:2020|max:2100',
            'oy'        => 'required|integer|min:1|max:12',
            'summa'     => 'required|numeric',
            'izoh'      => 'nullable|string|max:300',
        ]);

        $qator = PLQator::findOrFail($request->qator_id);
        if ($qator->hisoblash_turi !== 'qolda') {
            return response()->json(['xato' => "Bu qator avtomatik hisoblanadi, qo'lda o'zgartirib bo'lmaydi."], 422);
        }

        $this->service->qiymatSaqlash(
            $qator->id, $request->filial_id, $request->yil, $request->oy,
            (float) $request->summa, $request->izoh, Auth::id(),
        );

        // Sahifa qayta yuklanmasligi uchun — darhol qayta hisoblab,
        // yangilangan bo'lim jamilari va formula (kumulyativ) qatorlarini
        // qaytaramiz, frontend faqat tegishli katakchalarni yangilaydi.
        $natija = $this->service->hisobot((int) $request->yil, $request->filial_id ? (int) $request->filial_id : null);

        return response()->json([
            'ok'         => true,
            'bolim_jami' => $natija['bolim_jami'],
            'formulalar' => $natija['formulalar'],
        ]);
    }
}
