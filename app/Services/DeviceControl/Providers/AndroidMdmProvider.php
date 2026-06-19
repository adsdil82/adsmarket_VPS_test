<?php

namespace App\Services\DeviceControl\Providers;

use App\Models\Qurilma;
use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\DeviceControlResponse;

class AndroidMdmProvider implements DeviceControlProviderInterface
{
    public function kod(): string  { return 'android_mdm'; }
    public function nomi(): string { return 'Android Enterprise MDM'; }
    public function tayyor(): bool { return true; }
    public function lockQollab(): bool   { return true; }
    public function unlockQollab(): bool { return true; }
    public function ogohQollab(): bool   { return true; }

    public function lock(Qurilma $q, string $s = ''): DeviceControlResponse
    { return DeviceControlResponse::ok('Android MDM: Bloklash simulyatsiya (V1)', ['mock' => true]); }
    public function unlock(Qurilma $q, string $s = ''): DeviceControlResponse
    { return DeviceControlResponse::ok('Android MDM: Blokdan chiqarish simulyatsiya (V1)', ['mock' => true]); }
    public function ogohBerish(Qurilma $q, string $x = ''): DeviceControlResponse
    { return DeviceControlResponse::ok('Android MDM: Ogohlantirish simulyatsiya (V1)', ['mock' => true]); }
    public function sinxronlash(Qurilma $q): DeviceControlResponse
    { return DeviceControlResponse::ok('Android MDM: Sinxron simulyatsiya (V1)', ['mock' => true]); }
    public function holatniOl(Qurilma $q): DeviceControlResponse
    { return $this->sinxronlash($q); }
    public function ozodQil(Qurilma $q): DeviceControlResponse
    { return DeviceControlResponse::ok('Android MDM: Ozod qilish simulyatsiya (V1)', ['mock' => true]); }
}
