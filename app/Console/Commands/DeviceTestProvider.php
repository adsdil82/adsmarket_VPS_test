<?php

namespace App\Console\Commands;

use App\Models\Qurilma;
use App\Services\DeviceControl\DeviceControlManager;
use Illuminate\Console\Command;

class DeviceTestProvider extends Command
{
    protected $signature = 'devices:test-provider {provider_code} {device_id?}';
    protected $description = 'Provider ulanishini test qilish';

    public function handle(DeviceControlManager $manager): int
    {
        $kod = $this->argument('provider_code');
        $provayder = $manager->provayder($kod);

        if (!$provayder) {
            $this->error("Provider '{$kod}' topilmadi. Mavjudlar: " . implode(', ', $manager->kodlar()));
            return 1;
        }

        $this->info("Provider: {$provayder->nomi()}");
        $this->info("Tayyor: " . ($provayder->tayyor() ? '✓ Ha' : '✗ Yo\'q'));
        $this->info("Lock: " . ($provayder->lockQollab() ? '✓' : '✗') . " | Unlock: " . ($provayder->unlockQollab() ? '✓' : '✗') . " | Ogoh: " . ($provayder->ogohQollab() ? '✓' : '✗'));

        $deviceId = $this->argument('device_id');
        if ($deviceId) {
            $qurilma = Qurilma::find($deviceId);
            if (!$qurilma) {
                $this->error("Qurilma #{$deviceId} topilmadi.");
                return 1;
            }

            $this->info("\nHolat tekshiruvi (device #{$qurilma->id}, IMEI: {$qurilma->imei1})...");
            $javob = $provayder->holatniOl($qurilma);
            $this->info("Natija: " . ($javob->muvaffaqiyat ? '✓ ' : '✗ ') . $javob->xabar);
            if (!empty($javob->ma_lumotlar)) {
                $this->table(['Kalit','Qiymat'], collect($javob->ma_lumotlar)->map(fn($v,$k) => [$k, is_array($v) ? json_encode($v) : $v])->toArray());
            }
        }

        return 0;
    }
}
