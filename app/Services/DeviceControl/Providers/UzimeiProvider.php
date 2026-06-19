<?php

namespace App\Services\DeviceControl\Providers;

use App\Models\Qurilma;
use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\DeviceControlResponse;

/**
 * UZIMEI — O'zbekiston IMEI registri.
 * V1: faqat informatsion provider, lock/unlock qilmaydi.
 */
class UzimeiProvider implements DeviceControlProviderInterface
{
    public function kod(): string  { return 'uzimei'; }
    public function nomi(): string { return 'UZIMEI (O\'zbekiston IMEI Registri)'; }
    public function tayyor(): bool { return true; }
    public function lockQollab(): bool   { return false; }
    public function unlockQollab(): bool { return false; }
    public function ogohQollab(): bool   { return false; }

    public function lock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse
    {
        return DeviceControlResponse::xato('UZIMEI provider lock qilishni qo\'llab-quvvatlamaydi', 'NOT_SUPPORTED');
    }

    public function unlock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse
    {
        return DeviceControlResponse::xato('UZIMEI provider unlock qilishni qo\'llab-quvvatlamaydi', 'NOT_SUPPORTED');
    }

    public function ogohBerish(Qurilma $qurilma, string $xabar = ''): DeviceControlResponse
    {
        return DeviceControlResponse::xato('UZIMEI provider ogohlantirish qo\'llab-quvvatlamaydi', 'NOT_SUPPORTED');
    }

    public function sinxronlash(Qurilma $qurilma): DeviceControlResponse
    {
        return DeviceControlResponse::ok('UZIMEI: Ma\'lumot registrda', [
            'imei' => $qurilma->imei1, 'registered' => true,
        ]);
    }

    public function holatniOl(Qurilma $qurilma): DeviceControlResponse
    {
        return $this->sinxronlash($qurilma);
    }

    public function ozodQil(Qurilma $qurilma): DeviceControlResponse
    {
        return DeviceControlResponse::ok('UZIMEI: Qurilma registrda ro\'yxatdan chiqarildi (simulyatsiya)');
    }
}
