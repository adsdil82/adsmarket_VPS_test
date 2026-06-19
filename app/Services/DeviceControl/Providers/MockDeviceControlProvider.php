<?php

namespace App\Services\DeviceControl\Providers;

use App\Models\Qurilma;
use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\DeviceControlResponse;

/**
 * Mock provider — real API'siz test uchun.
 * Barcha amallar muvaffaqiyatli qaytaradi.
 */
class MockDeviceControlProvider implements DeviceControlProviderInterface
{
    public function kod(): string  { return 'mock'; }
    public function nomi(): string { return 'Mock Provider (Test)'; }
    public function tayyor(): bool { return true; }
    public function lockQollab(): bool   { return true; }
    public function unlockQollab(): bool { return true; }
    public function ogohQollab(): bool   { return true; }

    public function lock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse
    {
        return DeviceControlResponse::ok("Mock: Qurilma {$qurilma->imei1} bloklandi (simulyatsiya)", [
            'mock' => true, 'imei' => $qurilma->imei1,
        ]);
    }

    public function unlock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse
    {
        return DeviceControlResponse::ok("Mock: Qurilma {$qurilma->imei1} blokdan chiqarildi (simulyatsiya)", [
            'mock' => true, 'imei' => $qurilma->imei1,
        ]);
    }

    public function ogohBerish(Qurilma $qurilma, string $xabar = ''): DeviceControlResponse
    {
        return DeviceControlResponse::ok("Mock: Ogohlantirish yuborildi (simulyatsiya)", ['mock' => true]);
    }

    public function sinxronlash(Qurilma $qurilma): DeviceControlResponse
    {
        return DeviceControlResponse::ok("Mock: Sinxronlandi", ['mock' => true, 'holat' => 'active']);
    }

    public function holatniOl(Qurilma $qurilma): DeviceControlResponse
    {
        return DeviceControlResponse::ok("Mock: Holat olindi", ['mock' => true, 'holat' => $qurilma->holat]);
    }

    public function ozodQil(Qurilma $qurilma): DeviceControlResponse
    {
        return DeviceControlResponse::ok("Mock: Qurilma ozod qilindi (simulyatsiya)", ['mock' => true]);
    }
}
