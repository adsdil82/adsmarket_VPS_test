<?php
namespace App\Services;

use App\Models\Kassa;
use App\Models\KassaTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashTransferService
{
    /**
     * Kassa transferini yuborish (pul "yo'lda" — qabul qiluvchi kassaga kirmaydi)
     */
    public function yuborish(array $data): KassaTransfer
    {
        return DB::transaction(function () use ($data) {
            $from = Kassa::findOrFail($data['from_kassa_id']);

            if ($from->qoldiq < $data['summa']) {
                throw new \Exception("Jo'natuvchi kassada yetarli mablag' yo'q. Qoldiq: " .
                    number_format($from->qoldiq, 0, '.', ' ') . " so'm, so'ralayotgan: " .
                    number_format($data['summa'], 0, '.', ' ') . " so'm.");
            }
            if ($data['from_kassa_id'] === $data['to_kassa_id']) {
                throw new \Exception("Jo'natuvchi va qabul qiluvchi kassa bir xil bo'lmasligi kerak.");
            }

            // Jo'natuvchi kassadan chiqarish
            $from->decrement('qoldiq', $data['summa_uzs'] ?? $data['summa']);

            return KassaTransfer::create(array_merge($data, [
                'holat'    => 'yuborildi',
                'xodim_id' => Auth::id(),
            ]));
        });
    }

    /**
     * Qabul qilish — pul qabul qiluvchi kassaga kiradi
     */
    public function qabulQilish(KassaTransfer $transfer): void
    {
        if ($transfer->holat !== 'yuborildi') {
            throw new \Exception("Bu transfer {$transfer->holat} holatida.");
        }

        DB::transaction(function () use ($transfer) {
            $to = Kassa::findOrFail($transfer->to_kassa_id);
            $to->increment('qoldiq', $transfer->summa_uzs ?: $transfer->summa);

            $transfer->update([
                'holat'             => 'qabul_qilindi',
                'tasdiqlagan_id'    => Auth::id(),
                'tasdiqlangan_vaqt' => now(),
            ]);
        });
    }

    /**
     * Bekor qilish — pulni jo'natuvchi kassaga qaytarish
     */
    public function bekorQilish(KassaTransfer $transfer, string $sabab): void
    {
        if ($transfer->holat !== 'yuborildi') {
            throw new \Exception("Faqat 'yuborildi' holatidagi transfer bekor qilinadi.");
        }

        DB::transaction(function () use ($transfer, $sabab) {
            $from = Kassa::findOrFail($transfer->from_kassa_id);
            $from->increment('qoldiq', $transfer->summa_uzs ?: $transfer->summa);

            $transfer->update(['holat' => 'bekor', 'sabab' => $sabab]);
        });
    }
}
