<?php

namespace App\Services\DeviceControl\Contracts;

use App\Models\Qurilma;
use App\Services\DeviceControl\DeviceControlResponse;

interface DeviceControlProviderInterface
{
    /** Provider kodi (samsung_knox_guard, uzimei, ...) */
    public function kod(): string;

    /** Provider nomi (UI uchun) */
    public function nomi(): string;

    /** Provider aktiv va ishlashga tayyor ekanini tekshir */
    public function tayyor(): bool;

    /** Qurilmani lock qilish */
    public function lock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse;

    /** Qurilmani unlock qilish */
    public function unlock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse;

    /** Ogohlantirish yuborish (lock emas, faqat xabar) */
    public function ogohBerish(Qurilma $qurilma, string $xabar = ''): DeviceControlResponse;

    /** Provider bilan sinxronlash (qurilma holati) */
    public function sinxronlash(Qurilma $qurilma): DeviceControlResponse;

    /** Provider'dan qurilma holatini olish */
    public function holatniOl(Qurilma $qurilma): DeviceControlResponse;

    /** Qurilmani provider'dan ozod qilish (shartnoma yopilganda) */
    public function ozodQil(Qurilma $qurilma): DeviceControlResponse;

    /** Lock qilishni qo'llab-quvvatlaydi */
    public function lockQollab(): bool;

    /** Unlock qilishni qo'llab-quvvatlaydi */
    public function unlockQollab(): bool;

    /** Ogohlantirish qo'llab-quvvatlaydi */
    public function ogohQollab(): bool;
}
