<?php

namespace App\Console\Commands;

use App\Jobs\DeviceRuleApplyJob;
use App\Models\Qurilma;
use App\Models\QurilmaQoida;
use App\Models\RegKredit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeviceCheckOverdue extends Command
{
    protected $signature = 'devices:check-overdue {--dry-run : Faqat hisoblab ko\'rsatish}';
    protected $description = 'Kechikkan to\'lovlar uchun qurilma qoidalarini tekshirish va bajarish';

    public function handle(): int
    {
        if (!config('device_control.enabled', false)) {
            $this->warn('DEVICE_CONTROL_ENABLED=false — buyruq o\'tkazib yuborildi.');
            return 0;
        }

        $dryRun = $this->option('dry-run');
        $qoidalar = QurilmaQoida::faol()->with('provayder')->get();

        if ($qoidalar->isEmpty()) {
            $this->info('Faol qoidalar topilmadi.');
            return 0;
        }

        $this->info("Tekshirilmoqda: {$qoidalar->count()} ta faol qoida...");

        // Shartnomaga biriktirilgan qurilmalarni ol
        $qurilmalar = Qurilma::with(['kredit.grafik'])
            ->whereNotNull('reg_kredit_id')
            ->whereNotIn('holat', ['released','returned','lost'])
            ->get();

        $bajariladigan = 0;
        foreach ($qurilmalar as $qurilma) {
            $kredit = $qurilma->kredit;
            if (!$kredit) continue;

            $kechikish = $this->kechikishKunlarini($kredit);
            if ($kechikish <= 0) continue;

            foreach ($qoidalar as $qoida) {
                if ($kechikish < $qoida->kechikish_kunlar) continue;
                if ($qoida->amal === 'lock' && !config('device_control.auto_lock_enabled', false)) continue;

                $bajariladigan++;
                $this->line("  Qurilma #{$qurilma->id} ({$qurilma->imei1}) — {$kechikish} kun kechikkan → {$qoida->amal}");

                if (!$dryRun) {
                    DeviceRuleApplyJob::dispatch($qurilma->id, $qoida->id);
                }
            }
        }

        if ($dryRun) {
            $this->warn("--dry-run: {$bajariladigan} ta amal bajarilishi kerak edi.");
        } else {
            $this->info("{$bajariladigan} ta DeviceRuleApplyJob navbatga qo'yildi.");
        }

        return 0;
    }

    private function kechikishKunlarini(RegKredit $kredit): int
    {
        $oxirgi = DB::table('grafik')
            ->where('reg_kredit_id', $kredit->id)
            ->whereIn('holat', ['tolanmagan','qisman'])
            ->whereNotNull('tolov_sana')
            ->where('tolov_sana', '<', now()->toDateString())
            ->min('tolov_sana');

        if (!$oxirgi) return 0;
        return now()->diffInDays($oxirgi, false) * -1;
    }
}
