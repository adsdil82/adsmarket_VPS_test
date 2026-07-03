<?php
namespace App\Services;

use App\Models\FilialTransfer;
use App\Models\TovarKatalog;
use App\Models\TransferTafsilot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(private StockService $stock) {}

    /**
     * Yangi tovar transferi yaratish (qoralama). Jo'natuvchi ombordan
     * DARHOL chiqim qilinadi ("yuborildi" holatida yo'lda hisoblanadi) —
     * qabul qiluvchi tasdiqlagach, o'zining omboriga kirim bo'ladi.
     */
    public function yaratish(array $data, array $tovarlar): FilialTransfer
    {
        if (empty($data['from_ombor_id']) || empty($data['to_ombor_id'])) {
            throw new \Exception("Jo'natuvchi va qabul qiluvchi ombor tanlanishi shart.");
        }

        return DB::transaction(function () use ($data, $tovarlar) {
            $raqam = 'TT-' . now()->format('Ymd') . '-' . str_pad(
                FilialTransfer::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $transfer = FilialTransfer::create(array_merge($data, [
                'transfer_raqam' => $raqam,
                'holat'          => 'yuborildi',
                'xodim_id'       => Auth::id(),
                'sana'           => today(),
            ]));

            foreach ($tovarlar as $q) {
                $tovar = TovarKatalog::findOrFail($q['tovar_id']);

                TransferTafsilot::create([
                    'transfer_id' => $transfer->id,
                    'tovar_id'    => $q['tovar_id'],
                    'miqdor'      => $q['miqdor'],
                    'narx'        => $tovar->tan_narx,
                ]);

                // Jo'natuvchi OMBORIDAN (filial emas!) chiqim — qoldiq yetarli
                // bo'lmasa StockService o'zi aniq xabar bilan Exception otadi.
                $this->stock->chiqim(
                    $data['from_ombor_id'], $q['tovar_id'], (float) $q['miqdor'],
                    manbaTur: 'filiallar_transfer', manbaId: $transfer->id,
                    izoh: "Transfer #{$raqam} — yuborildi", harakat: 'transfer_out',
                );
            }

            return $transfer;
        });
    }

    /** Qabul qilish — qabul qiluvchi ombor tasdiqlaydi, o'z ombori qoldig'i oshadi. */
    public function qabulQilish(FilialTransfer $transfer): void
    {
        if ($transfer->holat !== 'yuborildi') {
            throw new \Exception("Bu transfer {$transfer->holat} holatida, qabul qilinmaydi.");
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->tafsilot as $t) {
                if (!$t->tovar_id) continue;
                $this->stock->kirim(
                    $transfer->to_ombor_id, $t->tovar_id, (float) $t->miqdor,
                    manbaTur: 'filiallar_transfer', manbaId: $transfer->id,
                    izoh: "Transfer #{$transfer->transfer_raqam} — qabul qilindi", harakat: 'transfer_in',
                );
            }

            $transfer->update([
                'holat'                => 'qabul_qilindi',
                'tasdiqlagan_xodim_id' => Auth::id(),
                'tasdiqlangan_vaqt'    => now(),
            ]);
        });
    }

    /** Bekor qilish — yo'ldagi tovarlar jo'natuvchi omboriga qaytariladi. */
    public function bekorQilish(FilialTransfer $transfer, string $sabab): void
    {
        if (!in_array($transfer->holat, ['yuborildi', 'qoralama'])) {
            throw new \Exception("Bu transfer bekor qilinmaydi (holat: {$transfer->holat}).");
        }

        DB::transaction(function () use ($transfer, $sabab) {
            if ($transfer->holat === 'yuborildi') {
                foreach ($transfer->tafsilot as $t) {
                    if (!$t->tovar_id) continue;
                    $this->stock->kirim(
                        $transfer->from_ombor_id, $t->tovar_id, (float) $t->miqdor,
                        manbaTur: 'filiallar_transfer', manbaId: $transfer->id,
                        izoh: "Transfer #{$transfer->transfer_raqam} bekor — tovar qaytarildi. Sabab: {$sabab}",
                        harakat: 'tuzatish',
                    );
                }
            }
            $transfer->update(['holat' => 'bekor', 'sabab' => $sabab]);
        });
    }
}
