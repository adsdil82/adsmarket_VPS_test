<?php

namespace App\Console\Commands;

use App\Jobs\DeviceProviderUnlockJob;
use App\Models\Qurilma;
use App\Models\RegKredit;
use Illuminate\Console\Command;

class DeviceAutoUnlock extends Command
{
    protected $signature = 'devices:auto-unlock {--dry-run}';
    protected $description = 'To\'lov tiklangan bloklangan qurilmalarni unlock qilish';

    public function handle(): int
    {
        if (!config('device_control.enabled', false)) {
            $this->warn('DEVICE_CONTROL_ENABLED=false — o\'tkazib yuborildi.');
            return 0;
        }

        // Bloklangan qurilmalarni olish
        $bloklangan = Qurilma::with('kredit')
            ->whereIn('holat', ['locked','unlock_pending'])
            ->whereNotNull('reg_kredit_id')
            ->get();

        $unlockKerak = 0;
        foreach ($bloklangan as $qurilma) {
            $kredit = $qurilma->kredit;
            if (!$kredit) continue;

            // Grafik kechikkanligi tekshiruvi — hozir kechikkan yozuv yo'q bo'lsa, to'lov tiklangan
            $kechikkan = \DB::table('grafik')
                ->where('reg_kredit_id', $kredit->id)
                ->whereIn('holat', ['tolanmagan','qisman'])
                ->where('tolov_sana', '<', now()->toDateString())
                ->exists();

            if (!$kechikkan) {
                $unlockKerak++;
                $this->line("  Qurilma #{$qurilma->id} ({$qurilma->imei1}) — to'lov tiklangan, unlock kerak");

                if (!$this->option('dry-run')) {
                    DeviceProviderUnlockJob::dispatch($qurilma->id, 'auto_unlock_after_payment');
                }
            }
        }

        if ($this->option('dry-run')) {
            $this->warn("--dry-run: {$unlockKerak} ta qurilma unlock bo'lishi kerak edi.");
        } else {
            $this->info("{$unlockKerak} ta qurilma uchun UnlockJob navbatga qo'yildi.");
        }

        return 0;
    }
}
