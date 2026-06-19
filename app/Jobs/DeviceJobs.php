<?php

namespace App\Jobs;

use App\Models\Qurilma;
use App\Models\QurilmaQoida;
use App\Services\DeviceControl\DeviceControlManager;
use App\Services\DeviceControl\DeviceControlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

// ─── DeviceWarningJob ─────────────────────────────────────────────
class DeviceWarningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public int $qurilmaId, public string $xabar = '') {}

    public function handle(DeviceControlService $service, DeviceControlManager $manager): void
    {
        $qurilma = Qurilma::find($this->qurilmaId);
        if (!$qurilma) return;

        $service->ogohBerish($qurilma, 'mock', $this->xabar ?: 'To\'lov muddati o\'tdi');
    }

    public function failed(\Throwable $e): void
    {
        Log::error("DeviceWarningJob xato", ['id' => $this->qurilmaId, 'error' => $e->getMessage()]);
    }
}

// ─── DeviceProviderLockJob ────────────────────────────────────────
class DeviceProviderLockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $backoff = 120;

    public function __construct(
        public int    $qurilmaId,
        public string $provayderKod,
        public string $sabab = '',
        public bool   $autoLock = true,
    ) {}

    public function handle(DeviceControlService $service): void
    {
        $qurilma = Qurilma::find($this->qurilmaId);
        if (!$qurilma) return;

        $service->lock($qurilma, $this->provayderKod, $this->sabab, $this->autoLock);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("DeviceProviderLockJob xato", ['id' => $this->qurilmaId, 'error' => $e->getMessage()]);
    }
}

// ─── DeviceProviderUnlockJob ──────────────────────────────────────
class DeviceProviderUnlockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 5;    // Unlock ko'proq urinadi
    public int $backoff = 30;

    public function __construct(
        public int    $qurilmaId,
        public string $sabab = '',
    ) {}

    public function handle(DeviceControlService $service, DeviceControlManager $manager): void
    {
        $qurilma = Qurilma::find($this->qurilmaId);
        if (!$qurilma || !$qurilma->canBeUnlocked()) return;

        // To'lovni qayta tekshir (xavfsizlik)
        if ($qurilma->kredit) {
            $halyKechikkan = \DB::table('grafik')
                ->where('reg_kredit_id', $qurilma->reg_kredit_id)
                ->whereIn('holat', ['tolanmagan','qisman'])
                ->where('tolov_sana', '<', now()->toDateString())
                ->exists();
            if ($halyKechikkan) {
                Log::info("DeviceProviderUnlockJob: To'lov hali kechikkan, unlock bekor qilindi", ['id' => $this->qurilmaId]);
                return;
            }
        }

        // Birinchi faol provider'ni topib unlock qilish
        $ulanish = $qurilma->provayderUlanishlari()->with('provayder')->where('holat','faol')->first();
        $provayderKod = $ulanish?->provayder?->kod ?? 'mock';

        $service->unlock($qurilma, $provayderKod, $this->sabab ?: 'To\'lov tiklandi — avtomatik unlock');
    }

    public function failed(\Throwable $e): void
    {
        Log::error("DeviceProviderUnlockJob xato", ['id' => $this->qurilmaId, 'error' => $e->getMessage()]);
    }
}

// ─── DeviceProviderSyncJob ────────────────────────────────────────
class DeviceProviderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 2;
    public int $backoff = 30;

    public function __construct(public int $qurilmaId, public string $provayderKod) {}

    public function handle(DeviceControlManager $manager): void
    {
        $qurilma = Qurilma::find($this->qurilmaId);
        if (!$qurilma) return;

        $provayder = $manager->provayder($this->provayderKod);
        if (!$provayder || !$provayder->tayyor()) return;

        $provayder->sinxronlash($qurilma);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("DeviceProviderSyncJob xato", ['id' => $this->qurilmaId, 'error' => $e->getMessage()]);
    }
}

// ─── DeviceRuleApplyJob ───────────────────────────────────────────
class DeviceRuleApplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 2;

    public function __construct(public int $qurilmaId, public int $qoidaId) {}

    public function handle(DeviceControlService $service): void
    {
        $qurilma = Qurilma::find($this->qurilmaId);
        $qoida   = QurilmaQoida::find($this->qoidaId);
        if (!$qurilma || !$qoida || !$qoida->faol) return;

        $provayderKod = $qoida->provayder?->kod ?? 'mock';
        $sabab = "Avtomatik: {$qoida->tavsif} ({$qoida->kechikish_kunlar} kun kechikdi)";

        match ($qoida->amal) {
            'ogoh_berish'      => $service->ogohBerish($qurilma, $provayderKod, $sabab),
            'lock'             => $service->lock($qurilma, $provayderKod, $sabab, true),
            'qolda_tekshirish' => null, // log'ga yoziladi, qo'lda ko'riladi
            default            => null,
        };
    }

    public function failed(\Throwable $e): void
    {
        Log::error("DeviceRuleApplyJob xato", ['qurilmaId' => $this->qurilmaId, 'error' => $e->getMessage()]);
    }
}
