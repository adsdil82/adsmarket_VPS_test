<?php
namespace App\Services;

use App\Models\FilialTransfer;
use App\Models\Ombor;
use App\Models\StockLedger;
use App\Models\TovarKatalog;
use App\Models\TransferTafsilot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    /**
     * Yangi tovar transferi yaratish (qoralama)
     * Jo'natuvchi ombordan darhol CHIQARMAYMIZ — "yuborildi" statusida
     */
    public function yaratish(array $data, array $tovarlar): FilialTransfer
    {
        return DB::transaction(function () use ($data, $tovarlar) {
            // Transfer raqam
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

                // Qoldiq tekshiruvi
                if ($tovar->qoldiq < $q['miqdor']) {
                    throw new \Exception("Tovar «{$tovar->nomi}»: omborda {$tovar->qoldiq} {$tovar->birlik} bor, {$q['miqdor']} so'ralyapti.");
                }

                TransferTafsilot::create([
                    'transfer_id' => $transfer->id,
                    'tovar_id'    => $q['tovar_id'],
                    'miqdor'      => $q['miqdor'],
                    'narx'        => $tovar->tan_narx,
                ]);

                // Jo'natuvchi ombordan chiqarish + stock ledger
                $oldinQoldiq = $tovar->qoldiq;
                $tovar->decrement('qoldiq', $q['miqdor']);

                $this->ledgerYoz($data['from_ombor_id'] ?? null, $tovar, 'transfer_out',
                    $q['miqdor'], $oldinQoldiq, $oldinQoldiq - $q['miqdor'],
                    'filiallar_transfer', $transfer->id, "Transfer #{$raqam} — yuborildi");
            }

            return $transfer;
        });
    }

    /**
     * Qabul qilish — qabul qiluvchi ombor tasdiqlaydi
     */
    public function qabulQilish(FilialTransfer $transfer): void
    {
        if ($transfer->holat !== 'yuborildi') {
            throw new \Exception("Bu transfer {$transfer->holat} holatida, qabul qilinmaydi.");
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->tafsilot as $t) {
                $tovar = TovarKatalog::find($t->tovar_id);
                if (!$tovar) continue;

                $oldinQoldiq = $tovar->qoldiq;
                $tovar->increment('qoldiq', $t->miqdor);

                $this->ledgerYoz($transfer->to_ombor_id, $tovar, 'transfer_in',
                    $t->miqdor, $oldinQoldiq, $oldinQoldiq + $t->miqdor,
                    'filiallar_transfer', $transfer->id,
                    "Transfer #{$transfer->transfer_raqam} — qabul qilindi");
            }

            $transfer->update([
                'holat'                => 'qabul_qilindi',
                'tasdiqlagan_xodim_id' => Auth::id(),
                'tasdiqlangan_vaqt'    => now(),
            ]);
        });
    }

    /**
     * Bekor qilish — tovarlarni qaytarish
     */
    public function bekorQilish(FilialTransfer $transfer, string $sabab): void
    {
        if (!in_array($transfer->holat, ['yuborildi', 'qoralama'])) {
            throw new \Exception("Bu transfer bekor qilinmaydi (holat: {$transfer->holat}).");
        }

        DB::transaction(function () use ($transfer, $sabab) {
            if ($transfer->holat === 'yuborildi') {
                // Tovarlarni jo'natuvchi omborga qaytarish
                foreach ($transfer->tafsilot as $t) {
                    $tovar = TovarKatalog::find($t->tovar_id);
                    if (!$tovar) continue;
                    $oldinQoldiq = $tovar->qoldiq;
                    $tovar->increment('qoldiq', $t->miqdor);
                    $this->ledgerYoz($transfer->from_ombor_id, $tovar, 'tuzatish',
                        $t->miqdor, $oldinQoldiq, $oldinQoldiq + $t->miqdor,
                        'filiallar_transfer', $transfer->id,
                        "Transfer #{$transfer->transfer_raqam} bekor — tovar qaytarildi. Sabab: {$sabab}");
                }
            }
            $transfer->update(['holat' => 'bekor', 'sabab' => $sabab]);
        });
    }

    /** StockLedger ga yozuv */
    private function ledgerYoz(?int $omborId, TovarKatalog $tovar, string $harakat,
        float $miqdor, float $oldin, float $keyin,
        string $manba_tur, int $manba_id, string $izoh): void
    {
        if (!$omborId) return;
        StockLedger::create([
            'ombor_id'     => $omborId,
            'tovar_id'     => $tovar->id,
            'tovar_nomi'   => $tovar->nomi,
            'harakat'      => $harakat,
            'miqdor'       => $miqdor,
            'qoldiq_oldin' => $oldin,
            'qoldiq_keyin' => $keyin,
            'tan_narx'     => $tovar->tan_narx,
            'manba_tur'    => $manba_tur,
            'manba_id'     => $manba_id,
            'xodim_id'     => Auth::id(),
            'izoh'         => $izoh,
        ]);
    }
}
