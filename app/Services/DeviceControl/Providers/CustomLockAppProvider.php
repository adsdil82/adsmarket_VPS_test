<?php

namespace App\Services\DeviceControl\Providers;

use App\Models\Qurilma;
use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\DeviceControlResponse;

class CustomLockAppProvider implements DeviceControlProviderInterface
{
    public function kod(): string  { return 'custom_lock_app'; }
    public function nomi(): string { return 'Maxsus Lock Ilova'; }
    public function tayyor(): bool { return false; }
    public function lockQollab(): bool   { return true; }
    public function unlockQollab(): bool { return true; }
    public function ogohQollab(): bool   { return true; }

    public function lock(Qurilma $q, string $s = ''): DeviceControlResponse
    { return DeviceControlResponse::xato('Maxsus ilova hali tayyor emas (Faza 3)', 'NOT_READY'); }
    public function unlock(Qurilma $q, string $s = ''): DeviceControlResponse
    { return DeviceControlResponse::xato('Maxsus ilova hali tayyor emas (Faza 3)', 'NOT_READY'); }
    public function ogohBerish(Qurilma $q, string $x = ''): DeviceControlResponse
    { return DeviceControlResponse::xato('Maxsus ilova hali tayyor emas (Faza 3)', 'NOT_READY'); }
    public function sinxronlash(Qurilma $q): DeviceControlResponse
    { return DeviceControlResponse::xato('Maxsus ilova hali tayyor emas (Faza 3)', 'NOT_READY'); }
    public function holatniOl(Qurilma $q): DeviceControlResponse
    { return $this->sinxronlash($q); }
    public function ozodQil(Qurilma $q): DeviceControlResponse
    { return DeviceControlResponse::xato('Maxsus ilova hali tayyor emas (Faza 3)', 'NOT_READY'); }
}
