<?php

namespace App\Http\Controllers;

use App\Models\RegKredit;
use App\Models\PochtaLog;
use App\Models\PochtaShablon;
use App\Services\HybridPochtaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PochXatController extends Controller
{
    public function __construct(private HybridPochtaService $svc) {}

    /**
     * AJAX: PDF yaratish + API da xat yaratish + imzolash uchun hash olish
     * Returns: { ok, letter_id, hash, log_id }
     */
    public function create(RegKredit $kredit, Request $request)
    {
        $this->ruxsat($kredit);

        $request->validate([
            'shablon_id' => 'required|exists:pochta_shablonlar,id',
            'receiver'   => 'required|string|max:200',
            'address'    => 'required|string|max:500',
            'region_id'  => 'required|integer|min:1',
            'area_id'    => 'required|integer|min:1',
        ]);

        if (!$this->svc->isEnabled()) {
            return response()->json(['xato' => 'Hybrid Pochta sozlanmagan. Sozlamalar bo\'limiga o\'ting.'], 422);
        }

        $shablon  = PochtaShablon::findOrFail($request->shablon_id);
        $regionId = (int) $request->region_id;
        $areaId   = (int) $request->area_id;

        if (!$this->svc->isYuborishMumkin($kredit->id, $shablon->id)) {
            return response()->json(['xato' => "Qayta yuborish muddati o'tmagan. Shablon: har {$shablon->qayta_yuborish_kun} kunda bir marta."], 422);
        }

        $log = PochtaLog::create([
            'reg_kredit_id' => $kredit->id,
            'mijoz_id'      => $kredit->mijoz_id,
            'shablon_id'    => $shablon->id,
            'receiver'      => $request->receiver,
            'address'       => $request->address,
            'region_id'     => $regionId,
            'area_id'       => $areaId,
            'holat'         => 'kutilmoqda',
        ]);

        try {
            $kredit->load('mijoz');
            $pdf = $this->svc->generatePdfBase64($kredit, $shablon);

            // Hybrid Pochta 1 varaqdan ortiq xat uchun qo'shimcha to'lov oladi —
            // shu sabab bir varaqqa sig'magan xatni umuman yaratmaymiz.
            if ($pdf['sahifalar'] > 1) {
                $log->update([
                    'holat'      => 'xato',
                    'xato_xabar' => "Xat matni 1 ta A4 varaqqa sig'madi ({$pdf['sahifalar']} varaq chiqdi) — qo'shimcha to'lovga sabab bo'lmasligi uchun yuborilmadi. Shablon matnini qisqartiring.",
                ]);
                return response()->json([
                    'xato' => "Xat matni 1 ta A4 varaqqa sig'madi ({$pdf['sahifalar']} varaq chiqdi). Hybrid Pochta ko'p varaqli xat uchun qo'shimcha to'lov oladi — shablon matnini qisqartiring. Log #{$log->id}",
                ], 422);
            }

            $mailResp = $this->svc->createMail(
                $request->receiver, $request->address, $regionId, $areaId, $pdf['base64']
            );

            if (!$mailResp || empty($mailResp['Id'])) {
                $log->update([
                    'holat'      => 'xato',
                    'xato_xabar' => 'API dan letter_id kelmadi',
                    'javob'      => $mailResp,
                ]);
                return response()->json(['xato' => 'API xatosi: xat yaratilmadi. Log #' . $log->id], 500);
            }

            $letterId = $mailResp['Id'];
            $log->update([
                'api_letter_id' => $letterId,
                'holat'         => 'yaratildi',
                'so_rov'        => [
                    'receiver'  => $request->receiver,
                    'address'   => $request->address,
                    'region_id' => $regionId,
                    'area_id'   => $areaId,
                ],
                'javob'          => $mailResp,
                'yaratildi_vaqt' => now(),
            ]);

            $hash = $this->svc->getHashForSign($letterId);
            if (!$hash) {
                $log->update(['holat' => 'xato', 'xato_xabar' => 'Hash olinmadi']);
                return response()->json(['xato' => 'API xatosi: imzo uchun hash olinmadi. Log #' . $log->id], 500);
            }

            return response()->json([
                'ok'        => true,
                'letter_id' => $letterId,
                'hash'      => $hash,
                'log_id'    => $log->id,
            ]);

        } catch (\Exception $e) {
            $log->update(['holat' => 'xato', 'xato_xabar' => $e->getMessage()]);
            return response()->json(['xato' => 'Server xatosi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: E-IMZO imzosi bilan xatni yuborish
     * Body: { letter_id, signature, log_id }
     */
    public function send(RegKredit $kredit, Request $request)
    {
        $this->ruxsat($kredit);

        $request->validate([
            'letter_id' => 'required|integer',
            'signature' => 'required|string',
            'log_id'    => 'required|integer',
        ]);

        $log = PochtaLog::findOrFail($request->log_id);

        try {
            $resp = $this->svc->sendMailVariantA($request->letter_id, $request->signature);

            if (!$resp) {
                $log->update(['holat' => 'xato', 'xato_xabar' => 'sendMail: API javob bermadi']);
                return response()->json(['xato' => 'Xat yuborilmadi. API javob bermadi. Log #' . $log->id], 500);
            }

            $log->update([
                'holat'          => 'yuborildi',
                'javob'          => array_merge($log->javob ?? [], ['send_resp' => $resp]),
                'yuborildi_vaqt' => now(),
            ]);

            return response()->json([
                'ok'        => true,
                'letter_id' => $request->letter_id,
                'log_id'    => $log->id,
                'xabar'     => 'Xat muvaffaqiyatli yuborildi!',
            ]);

        } catch (\Exception $e) {
            $log->update(['holat' => 'xato', 'xato_xabar' => $e->getMessage()]);
            return response()->json(['xato' => 'Server xatosi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Server sertifikati bilan yuborish (Variant B — brauzersiz, E-IMZO shart emas)
     * Body: { letter_id, log_id }
     */
    public function sendServer(RegKredit $kredit, Request $request)
    {
        $this->ruxsat($kredit);

        $request->validate([
            'letter_id' => 'required|integer',
            'log_id'    => 'required|integer',
        ]);

        $log = PochtaLog::findOrFail($request->log_id);

        try {
            $resp = $this->svc->sendMailVariantB((int) $request->letter_id);

            if (!$resp) {
                $log->update(['holat' => 'xato', 'xato_xabar' => 'Variant B: server sertifikati bilan yuborilmadi (sozlamalarda sertifikat/parolni tekshiring, log faylga qarang)']);
                return response()->json(['xato' => "Server sertifikati bilan yuborishda xato. Sozlamalarda sertifikat va parolni tekshiring. Log #{$log->id}"], 500);
            }

            $log->update([
                'holat'          => 'yuborildi',
                'javob'          => array_merge($log->javob ?? [], ['send_resp' => $resp]),
                'yuborildi_vaqt' => now(),
            ]);

            return response()->json([
                'ok'        => true,
                'letter_id' => $request->letter_id,
                'log_id'    => $log->id,
                'xabar'     => 'Xat muvaffaqiyatli yuborildi! (server sertifikat bilan)',
            ]);

        } catch (\Exception $e) {
            $log->update(['holat' => 'xato', 'xato_xabar' => $e->getMessage()]);
            return response()->json(['xato' => 'Server xatosi: ' . $e->getMessage()], 500);
        }
    }

    /** PDF ko'rish (brauzerda inline) */
    public function preview(RegKredit $kredit, Request $request)
    {
        $this->ruxsat($kredit);
        $kredit->load('mijoz');
        $shablon  = PochtaShablon::findOrFail($request->query('shablon_id'));
        $pdf      = $this->svc->generatePdfBase64($kredit, $shablon);
        $pdfBytes = base64_decode($pdf['base64']);
        $faylNomi = 'xat-' . $kredit->shartnoma_raqam . '.pdf';

        return response($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$faylNomi}\"",
            'Content-Length'      => (string) strlen($pdfBytes),
            'X-Sahifalar-Soni'    => (string) $pdf['sahifalar'],
        ]);
    }

    /** Faqat "hibrit_pochta" resursi bo'yicha "qoshish" ruxsati bor foydalanuvchilar xat yubora oladi. */
    private function ruxsat(RegKredit $kredit): void
    {
        if (!Auth::user()->ruxsat('hibrit_pochta', 'qoshish')) {
            abort(403, "Pochta xati yuborish uchun sizda ruxsat yo'q.");
        }
    }
}
