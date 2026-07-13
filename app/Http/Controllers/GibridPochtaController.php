<?php

namespace App\Http\Controllers;

use App\Models\PochtaLog;
use App\Services\HybridPochtaService;
use Illuminate\Http\Request;

class GibridPochtaController extends Controller
{
    public function __construct(private HybridPochtaService $svc) {}

    /** Sozlamalar sahifasidagi "Ulanishni tekshirish" tugmasi */
    public function testConnection(): \Illuminate\Http\JsonResponse
    {
        $result = $this->svc->testConnection();
        return response()->json($result);
    }

    /** Viloyatlar JSON (manzil tanlash dropdown uchun) */
    public function regions(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->svc->getRegions());
    }

    /** Tumanlar JSON (manzil tanlash dropdown uchun) */
    public function areas(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->svc->getAreas());
    }

    /** Pochta log jurnali */
    public function loglar(Request $request)
    {
        $query = PochtaLog::with(['kredit', 'mijoz', 'shablon'])->latest();

        if ($request->filled('holat')) {
            $query->where('holat', $request->holat);
        }
        if ($request->filled('kredit_id')) {
            $query->where('reg_kredit_id', $request->kredit_id);
        }
        if ($request->filled('dan')) {
            $query->whereDate('created_at', '>=', $request->dan);
        }
        if ($request->filled('gacha')) {
            $query->whereDate('created_at', '<=', $request->gacha);
        }

        $loglar = $query->paginate(30)->withQueryString();

        $statistika = [
            'jami'       => PochtaLog::count(),
            'yuborildi'  => PochtaLog::where('holat', 'yuborildi')->count(),
            'xato'       => PochtaLog::where('holat', 'xato')->count(),
            'bugun'      => PochtaLog::whereDate('created_at', today())->count(),
        ];

        return view('admin.pochta-loglar.index', compact('loglar', 'statistika'));
    }
    /** Yuborilgan xat kvitansiyasini PDF sifatida yuklab olish */
    public function kvitansiya(PochtaLog $log): \Symfony\Component\HttpFoundation\Response
    {
        if (!$log->api_letter_id) {
            abort(404, 'Bu log uchun API letter ID yo\'q');
        }

        // getReceipt() xom (raw) PDF baytlarini qaytaradi — bu yerda qayta
        // base64_decode() qilish fayl mazmunini buzib, ochilmaydigan PDF hosil
        // qilar edi (avvalgi xato).
        $pdfBytes = $this->svc->getReceipt($log->api_letter_id);
        if (!$pdfBytes) {
            return back()->with('error', 'Kvitansiya PDF olinmadi. API da hali tayyorlanmagan bo\'lishi mumkin.');
        }

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="kvitansiya-' . $log->id . '.pdf"',
        ]);
    }

}
