<?php
namespace App\Services;

use App\Models\RegKredit;
use App\Models\ShartnomaxodimTarixi;
use App\Models\ShartnomaiFilialTarixi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractTransferService
{
    /**
     * Shartnomani boshqa xodimga qayta tayinlash (Assignment)
     * eski xodim_id yangi joriy_xodim_id ga o'tadi
     */
    public function xodimniQaytaTayin(RegKredit $shartnoma, int $yangiXodimId, string $sabab, ?string $izoh = null): void
    {
        DB::transaction(function () use ($shartnoma, $yangiXodimId, $sabab, $izoh) {
            $eskiXodimId = $shartnoma->joriy_xodim_id ?? $shartnoma->xodim_id;

            if ($eskiXodimId === $yangiXodimId) {
                throw new \Exception("Shartnoma allaqachon bu xodimga tayinlangan.");
            }

            // Tarix yozuvi
            ShartnomaxodimTarixi::create([
                'shartnoma_id'   => $shartnoma->id,
                'eski_xodim_id'  => $eskiXodimId,
                'yangi_xodim_id' => $yangiXodimId,
                'ozgartirgan_id' => Auth::id(),
                'sabab'          => $sabab,
                'izoh'           => $izoh,
            ]);

            // Joriy xodimni yangilash (asl xodim_id SAQLANADI)
            $shartnoma->update(['joriy_xodim_id' => $yangiXodimId]);
        });
    }

    /**
     * Shartnomani boshqa filialga ko'chirish — EHTIYOTKORLIK TALAB QILADI
     * - asl filial_id SAQLANADI
     * - joriy_filial_id yangilanadi
     * - eski to'lovlar eski filialda ko'rinadi (to'lov yozuvlari o'zgarmaydi)
     * - yangi to'lovlar yangi filialda ko'rinadi
     */
    public function filialgaKochirish(RegKredit $shartnoma, int $yangiFilialId, string $sabab,
        bool $tolovlarYangiFilialda = false, ?string $izoh = null): void
    {
        if (!$sabab) {
            throw new \Exception("Filial ko'chirishda sabab majburiy.");
        }

        DB::transaction(function () use ($shartnoma, $yangiFilialId, $sabab, $tolovlarYangiFilialda, $izoh) {
            $eskiFilialId = $shartnoma->joriy_filial_id ?? $shartnoma->filial_id;

            if ($eskiFilialId === $yangiFilialId) {
                throw new \Exception("Shartnoma allaqachon bu filialda.");
            }

            // Tarix yozuvi (eski tarix yo'qolmaydi)
            ShartnomaiFilialTarixi::create([
                'shartnoma_id'               => $shartnoma->id,
                'eski_filial_id'             => $eskiFilialId,
                'yangi_filial_id'            => $yangiFilialId,
                'ozgartirgan_id'             => Auth::id(),
                'sabab'                      => $sabab,
                'izoh'                       => $izoh,
                'tolovlar_yangi_filialda'    => $tolovlarYangiFilialda,
            ]);

            // Joriy filialni yangilash (asl filial_id SAQLANADI)
            $shartnoma->update(['joriy_filial_id' => $yangiFilialId]);
        });
    }
}
