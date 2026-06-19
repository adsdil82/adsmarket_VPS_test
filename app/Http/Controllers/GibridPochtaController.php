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

        $pdfB64 = $this->svc->getReceipt($log->api_letter_id);
        if (!$pdfB64) {
            return back()->with('error', 'Kvitansiya PDF olinmadi. API da hali tayyorlanmagan bo\'lishi mumkin.');
        }

        return response(base64_decode($pdfB64), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="kvitansiya-' . $log->id . '.pdf"',
        ]);
    }

    /** Xato yoki yaratildi holatdagi xatni server sertifikati bilan qayta yuborish */
    public function qaytaYuborish(PochtaLog $log): \Illuminate\Http\JsonResponse
    {
        if (!in_array($log->holat, ['xato', 'yaratildi'])) {
            return response()->json(['xato' => 'Faqat xato yoki yaratildi holatdagi xatlarni qayta yuborish mumkin.'], 422);
        }
        if (!$log->api_letter_id) {
            return response()->json(['xato' => 'API letter ID yo\'q — xatni qaytadan kredit sahifasidan yarating.'], 422);
        }

        try {
            // Hash qayta olamiz
            $hash = $this->svc->getHashForSign($log->api_letter_id);
            if (!$hash) {
                $log->update(['holat' => 'xato', 'xato_xabar' => 'Qayta yuborishda hash olinmadi']);
                return response()->json(['xato' => 'Hash olinmadi. Xat API da mavjud emasdir.'], 500);
            }

            // Server sertifikati bilan imzolash (Variant B)
            $resp = $this->svc->sendMailVariantB($log->api_letter_id);
            if (!$resp) {
                $log->update(['holat' => 'xato', 'xato_xabar' => 'Variant B: API javob bermadi']);
                return response()->json(['xato' => 'Server sertifikati bilan yuborishda xato. Sozlamalarda sertifikat tekshiring.'], 500);
            }

            $log->update([
                'holat'          => 'yuborildi',
                'javob'          => array_merge($log->javob ?? [], ['qayta_yuborish' => $resp]),
                'yuborildi_vaqt' => now(),
                'xato_xabar'     => null,
            ]);

            return response()->json([
                'ok'    => true,
                'xabar' => 'Xat muvaffaqiyatli qayta yuborildi (server sertifikat bilan)',
            ]);

        } catch (\Exception $e) {
            $log->update(['holat' => 'xato', 'xato_xabar' => 'Qayta yuborish: ' . $e->getMessage()]);
            return response()->json(['xato' => $e->getMessage()], 500);
        }
    }

}
