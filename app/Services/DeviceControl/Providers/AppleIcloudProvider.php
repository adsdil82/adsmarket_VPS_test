<?php

namespace App\Services\DeviceControl\Providers;

use App\Models\Qurilma;
use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\DeviceControlResponse;

class AppleIcloudProvider implements DeviceControlProviderInterface
{
    public function kod(): string  { return 'apple_icloud'; }
    public function nomi(): string { return 'Apple iCloud Lock'; }
    public function tayyor(): bool { return true; }
    public function lockQollab(): bool   { return false; }
    public function unlockQollab(): bool { return false; }
    public function ogohQollab(): bool   { return false; }

    public function lock(Qurilma $q, string $s = ''): DeviceControlResponse
    { return DeviceControlResponse::xato('Apple iCloud avtomatik lock qollanmaydi - qolda bajaring', 'MANUAL_REQUIRED'); }
    public function unlock(Qurilma $q, string $s = ''): DeviceControlResponse
    { return DeviceControlResponse::xato('Apple iCloud avtomatik unlock qollanmaydi - qolda bajaring', 'MANUAL_REQUIRED'); }
    public function ogohBerish(Qurilma $q, string $x = ''): DeviceControlResponse
    { return DeviceControlResponse::xato('Apple iCloud ogohlantirish qollab-quvvatlanmaydi', 'NOT_SUPPORTED'); }
    public function sinxronlash(Qurilma $q): DeviceControlResponse
    { return DeviceControlResponse::ok('Apple iCloud: Qolda nazorat rejimi', ['manual' => true]); }
    public function holatniOl(Qurilma $q): DeviceControlResponse
    { return $this->sinxronlash($q); }
    public function ozodQil(Qurilma $q): DeviceControlResponse
    { return DeviceControlResponse::ok('Apple iCloud: Ozod qilish - qolda bajaring', ['manual' => true]); }
}
