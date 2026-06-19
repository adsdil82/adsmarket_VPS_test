<?php

namespace App\Console\Commands;

use App\Jobs\DeviceProviderSyncJob;
use App\Models\Qurilma;
use App\Models\QurilmaProvayder;
use Illuminate\Console\Command;

class DeviceSyncProviders extends Command
{
    protected $signature = 'devices:sync-providers {--provayder= : Faqat shu provayder}';
    protected $description = 'Barcha qurilmalarni aktiv provayderlar bilan sinxronlash';

    public function handle(): int
    {
        if (!config('device_control.enabled', false)) {
            $this->warn('DEVICE_CONTROL_ENABLED=false — o\'tkazib yuborildi.');
            return 0;
        }

        $provayderlar = QurilmaProvayder::faol()
            ->when($this->option('provayder'), fn($q) => $q->where('kod', $this->option('provayder')))
            ->where('sinx_qollab', true)
            ->get();

        if ($provayderlar->isEmpty()) {
            $this->warn('Sinxronlash qo\'llagan faol provayderlar topilmadi.');
            return 0;
        }

        $qurilmalar = Qurilma::whereNotNull('reg_kredit_id')
            ->whereNotIn('holat', ['released','returned','lost'])
            ->get();

        $soni = 0;
        foreach ($qurilmalar as $qurilma) {
            foreach ($provayderlar as $provayder) {
                DeviceProviderSyncJob::dispatch($qurilma->id, $provayder->kod);
                $soni++;
            }
        }

        $this->info("{$soni} ta sinxronlash Job navbatga qo'yildi.");
        return 0;
    }
}
