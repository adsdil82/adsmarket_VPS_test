<?php

namespace App\Services\DeviceControl\Providers;

use App\Models\Qurilma;
use App\Models\QurilmaProvayder;
use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\DeviceControlResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Samsung Knox Guard provider.
 * V1: mock_rejim=true bo'lsa simulyatsiya qiladi.
 * Haqiqiy API sozlamalarini provayder sozlamalaridan oladi.
 */
class SamsungKnoxGuardProvider implements DeviceControlProviderInterface
{
    private ?QurilmaProvayder $provayder = null;
    private bool $mock = true;

    public function __construct()
    {
        $this->provayder = QurilmaProvayder::where('kod', $this->kod())->first();
        $this->mock = $this->provayder?->mock_rejim ?? true;
    }

    public function kod(): string  { return 'samsung_knox_guard'; }
    public function nomi(): string { return 'Samsung Knox Guard'; }
    public function lockQollab(): bool   { return true; }
    public function unlockQollab(): bool { return true; }
    public function ogohQollab(): bool   { return true; }

    public function tayyor(): bool
    {
        if (!$this->provayder || !$this->provayder->faol) return false;
        if ($this->mock) return true;
        $token = $this->provayder->sozlamaQiymati('api_token');
        return !empty($token);
    }

    public function lock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse
    {
        if ($this->mock) {
            return DeviceControlResponse::ok('Knox Guard: Bloklash simulyatsiya qilindi', ['mock' => true]);
        }
        try {
            $response = $this->apiSorov('POST', '/devices/' . $qurilma->imei1 . '/lock', [
                'reason' => $sabab,
            ]);
            return $response->successful()
                ? DeviceControlResponse::ok('Knox Guard: Qurilma bloklandi')
                : DeviceControlResponse::xato('Knox Guard xato: ' . $response->status(), 'API_ERROR', true);
        } catch (\Exception $e) {
            Log::error('KnoxGuard lock xato', ['imei' => $qurilma->imei1, 'error' => $e->getMessage()]);
            return DeviceControlResponse::xato('Knox Guard ulanish xatosi', 'CONNECTION_ERROR', true);
        }
    }

    public function unlock(Qurilma $qurilma, string $sabab = ''): DeviceControlResponse
    {
        if ($this->mock) {
            return DeviceControlResponse::ok('Knox Guard: Blokdan chiqarish simulyatsiya qilindi', ['mock' => true]);
        }
        try {
            $response = $this->apiSorov('POST', '/devices/' . $qurilma->imei1 . '/unlock', [
                'reason' => $sabab,
            ]);
            return $response->successful()
                ? DeviceControlResponse::ok('Knox Guard: Qurilma blokdan chiqarildi')
                : DeviceControlResponse::xato('Knox Guard xato: ' . $response->status(), 'API_ERROR', true);
        } catch (\Exception $e) {
            Log::error('KnoxGuard unlock xato', ['imei' => $qurilma->imei1, 'error' => $e->getMessage()]);
            return DeviceControlResponse::xato('Knox Guard ulanish xatosi', 'CONNECTION_ERROR', true);
        }
    }

    public function ogohBerish(Qurilma $qurilma, string $xabar = ''): DeviceControlResponse
    {
        if ($this->mock) {
            return DeviceControlResponse::ok('Knox Guard: Ogohlantirish simulyatsiya qilindi', ['mock' => true]);
        }
        try {
            $response = $this->apiSorov('POST', '/devices/' . $qurilma->imei1 . '/warn', [
                'message' => $xabar,
            ]);
            return $response->successful()
                ? DeviceControlResponse::ok('Knox Guard: Ogohlantirish yuborildi')
                : DeviceControlResponse::xato('Knox Guard xato: ' . $response->status(), 'API_ERROR');
        } catch (\Exception $e) {
            return DeviceControlResponse::xato('Knox Guard ulanish xatosi', 'CONNECTION_ERROR');
        }
    }

    public function sinxronlash(Qurilma $qurilma): DeviceControlResponse
    {
        if ($this->mock) {
            return DeviceControlResponse::ok('Knox Guard: Sinxron simulyatsiya', ['mock' => true]);
        }
        try {
            $response = $this->apiSorov('GET', '/devices/' . $qurilma->imei1);
            return $response->successful()
                ? DeviceControlResponse::ok('Sinxronlandi', $response->json() ?? [])
                : DeviceControlResponse::xato('Knox Guard sinxron xato: ' . $response->status(), 'API_ERROR');
        } catch (\Exception $e) {
            return DeviceControlResponse::xato('Knox Guard ulanish xatosi', 'CONNECTION_ERROR');
        }
    }

    public function holatniOl(Qurilma $qurilma): DeviceControlResponse
    {
        return $this->sinxronlash($qurilma);
    }

    public function ozodQil(Qurilma $qurilma): DeviceControlResponse
    {
        if ($this->mock) {
            return DeviceControlResponse::ok('Knox Guard: Ozod qilish simulyatsiya qilindi', ['mock' => true]);
        }
        try {
            $response = $this->apiSorov('POST', '/devices/' . $qurilma->imei1 . '/release');
            return $response->successful()
                ? DeviceControlResponse::ok('Knox Guard: Qurilma ozod qilindi')
                : DeviceControlResponse::xato('Knox Guard xato: ' . $response->status(), 'API_ERROR');
        } catch (\Exception $e) {
            return DeviceControlResponse::xato('Knox Guard ulanish xatosi', 'CONNECTION_ERROR');
        }
    }

    private function apiSorov(string $method, string $path, array $data = [])
    {
        $baseUrl = $this->provayder->sozlamaQiymati('api_url', 'https://api.samsungknox.com/v1');
        $token   = $this->provayder->sozlamaQiymati('api_token', '');

        return Http::withToken($token)
            ->timeout(15)
            ->$method($baseUrl . $path, $data);
    }
}
