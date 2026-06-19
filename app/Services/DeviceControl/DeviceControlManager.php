<?php

namespace App\Services\DeviceControl;

use App\Services\DeviceControl\Contracts\DeviceControlProviderInterface;
use App\Services\DeviceControl\Providers\MockDeviceControlProvider;
use App\Services\DeviceControl\Providers\SamsungKnoxGuardProvider;
use App\Services\DeviceControl\Providers\UzimeiProvider;
use App\Services\DeviceControl\Providers\AppleIcloudProvider;
use App\Services\DeviceControl\Providers\AndroidMdmProvider;
use App\Services\DeviceControl\Providers\CustomLockAppProvider;

/**
 * Provider'larni boshqaruvchi singleton.
 * Yangi provider qo'shish uchun faqat shu classga register qiling.
 */
class DeviceControlManager
{
    /** @var array<string, DeviceControlProviderInterface> */
    private array $provayderlar = [];

    public function __construct()
    {
        $this->register(new MockDeviceControlProvider());
        $this->register(new SamsungKnoxGuardProvider());
        $this->register(new UzimeiProvider());
        $this->register(new AppleIcloudProvider());
        $this->register(new AndroidMdmProvider());
        $this->register(new CustomLockAppProvider());
    }

    public function register(DeviceControlProviderInterface $provayder): void
    {
        $this->provayderlar[$provayder->kod()] = $provayder;
    }

    public function provayder(string $kod): ?DeviceControlProviderInterface
    {
        return $this->provayderlar[$kod] ?? null;
    }

    /** @return array<string, DeviceControlProviderInterface> */
    public function barcha(): array
    {
        return $this->provayderlar;
    }

    public function tayyor(string $kod): bool
    {
        return $this->provayder($kod)?->tayyor() ?? false;
    }

    public function kodlar(): array
    {
        return array_keys($this->provayderlar);
    }
}
