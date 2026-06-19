<?php

namespace App\Console\Commands;

use App\Services\TulovService;
use App\Models\RegKredit;
use Illuminate\Console\Command;

/**
 * MuddatKuzatish — Har kuni ishga tushadigan scheduler komandasi.
 *
 * Vazifasi:
 *   - Muddati o'tgan grafik qatorlarini 'muddati_otgan' deb belgilaydi
 *   - Tegishli shartnomalar holatini yangilaydi
 *
 * Scheduler (routes/console.php yoki AppServiceProvider):
 *   Schedule::command('nasiya:muddat-kuzatish')->dailyAt('00:05');
 */
class MuddatKuzatish extends Command
{
    protected $signature   = 'nasiya:muddat-kuzatish';
    protected $description = 'Muddati o\'tgan to\'lov jadvallarini yangilash (har kuni ishga tushadi)';

    public function __construct(private TulovService $tulovService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Muddati o\'tgan to\'lovlar tekshirilmoqda...');

        $yangilandi = $this->tulovService->barchaMuddatiOtganYangilash();

        $this->line("  <fg=yellow>!</> Muddati o'tgan grafik qatorlar: {$yangilandi} ta yangilandi");

        // Muddati o'tgan shartnomalar soni
        $muddatiOtgan = RegKredit::where('holat', 'muddati_otgan')->count();
        $this->line("  <fg=red>!</> Muddati o'tgan shartnomalar jami: {$muddatiOtgan} ta");

        $this->info('Muddat kuzatish yakunlandi.');
        return self::SUCCESS;
    }
}
