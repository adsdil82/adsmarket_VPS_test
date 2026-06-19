<?php

namespace App\Console\Commands;

use App\Services\ImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * NasiyaImport — Eski bazadan ma'lumotlarni import qilish.
 *
 * Ishlatilish:
 *   php artisan nasiya:import {bosqich}
 *
 * Bosqichlar:
 *   filiallar        — Filiallarni seed qilish (seederdan chaqiriladi)
 *   tulov-turlari    — To'lov turlarini import qilish
 *   mijozlar         — Mijozlarni import qilish (mijoz_mysql.sql kerak)
 *   kreditlar        — Shartnomalarni import qilish (reg_kredit_mysql.sql kerak)
 *   tovarlar         — Tovarlarni import qilish (ch_kt_tv_mysql.sql kerak)
 *   grafik           — To'lov grafikini import qilish (grafik_mysql.sql kerak)
 *   tulovlar         — To'lovlarni import qilish (tulov_kt_mysql.sql kerak)
 *   oldin-tulovlar   — Oldindan to'lovlarni import qilish (tulov_oldindan_KT_mysql.sql kerak)
 *   taminotchilar    — Ta'minotchilarni import qilish (temp_taminotchi kerak)
 *   taminot-kirimlar — Ta'minotdan kirimlarni import qilish (temp_taminot_kirim, temp_taminot_kirim_qator kerak)
 *   harajatlar       — Umumiy xarajatlarni import qilish (temp_harajat kerak)
 *   hammasi          — Barcha bosqichlarni ketma-ket bajarish
 *
 * SQL fayllar import qilish tartibi:
 *   1. phpMyAdmin yoki mysql CLI orqali SQL fayllarni temp_* nomli jadvalga import qiling
 *   2. Keyin bu komandani ishga tushiring
 *
 * Misol:
 *   mysql -u root -p nasiya_db < storage/import/mijoz_mysql.sql
 *   php artisan nasiya:import mijozlar
 */
class NasiyaImport extends Command
{
    protected $signature = 'nasiya:import
                            {bosqich : filiallar|tulov-turlari|mijozlar|kreditlar|tovarlar|grafik|tulovlar|oldin-tulovlar|taminotchilar|taminot-kirimlar|harajatlar|hammasi}
                            {--xodim-id=1 : Import paytida ishlatiluvchi xodim ID si}
                            {--fresh : Jadvaldan avval tozalash (truncate)}';

    protected $description = 'Eski MySQL bazasidan NasiyaPro ga ma\'lumotlarni import qilish';

    public function __construct(private ImportService $importService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $bosqich  = $this->argument('bosqich');
        $xodimId  = (int) $this->option('xodim-id');
        $fresh    = $this->option('fresh');

        $this->info("NasiyaImport boshlandi: [{$bosqich}]");
        $this->newLine();

        return match($bosqich) {
            'filiallar'        => $this->filiallarImport(),
            'tulov-turlari'    => $this->tulovTurlariImport(),
            'mijozlar'         => $this->mijozlarImport($fresh),
            'kreditlar'        => $this->kreditlarImport($xodimId, $fresh),
            'tovarlar'         => $this->tovarlarImport($fresh),
            'grafik'           => $this->grafikImport($fresh),
            'tulovlar'         => $this->tulovlarImport($xodimId, $fresh),
            'oldin-tulovlar'   => $this->oldinTulovlarImport($xodimId, $fresh),
            'taminotchilar'    => $this->taminotchilarImport($fresh),
            'taminot-kirimlar' => $this->taminotKirimlarImport($xodimId, $fresh),
            'harajatlar'       => $this->harajatlarImport($xodimId, $fresh),
            'hammasi'          => $this->hammasiniImport($xodimId),
            default            => $this->nomaluumBosqich($bosqich),
        };
    }

    // ─── Bosqichlar ───────────────────────────────────────────────

    private function filiallarImport(): int
    {
        $this->info('Filiallar seed qilinmoqda...');
        $this->call('db:seed', ['--class' => 'FiliallarSeeder']);
        $this->success('Filiallar tayyor!');
        return self::SUCCESS;
    }

    private function tulovTurlariImport(): int
    {
        $this->info('To\'lov turlari import qilinmoqda...');

        // temp_tulov_turi yo'q bo'lsa, temp_tulov_kt dan olamiz (u ham tulov_turi_nomi ustuniga ega)
        if ($this->jadvalBorMi('temp_tulov_turi')) {
            $manba = 'temp_tulov_turi';
        } elseif ($this->jadvalBorMi('temp_tulov_kt')) {
            $manba = 'temp_tulov_kt';
            $this->warn("temp_tulov_turi topilmadi — temp_tulov_kt dan to'lov turlari olinadi...");
        } else {
            $this->error('temp_tulov_turi ham, temp_tulov_kt ham topilmadi! Avval SQL fayllarini import qiling.');
            return self::FAILURE;
        }

        $nomlar = DB::table($manba)
            ->distinct()
            ->pluck('tulov_turi_nomi')
            ->filter()
            ->toArray();

        $this->importService->tulovTurlariImport($nomlar);
        $this->success("To'lov turlari import qilindi: " . count($nomlar) . " ta");
        return self::SUCCESS;
    }

    private function mijozlarImport(bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_mijoz')) {
            $this->error('temp_mijoz jadvali topilmadi!');
            $this->line('SQL faylni import qiling: mysql -u root -p nasiya_db < storage/import/mijoz_mysql.sql');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Mijozlar jadvalini tozalaymizmi? (XAVFLI!)')) {
            DB::table('mijozlar')->truncate();
            $this->warn('Mijozlar jadvali tozalandi.');
        }

        $this->info('Mijozlar import qilinmoqda...');
        $bar   = $this->output->createProgressBar(DB::table('temp_mijoz')->count());
        $count = 0;

        $this->importService->mijozlarImport(
            DB::table('temp_mijoz')->cursor(),
            $count
        );

        $bar->finish();
        $this->newLine();
        $this->success("Mijozlar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function kreditlarImport(int $xodimId, bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_reg_kredit')) {
            $this->error('temp_reg_kredit jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Kreditlar jadvalini tozalaymizmi?')) {
            DB::table('reg_kredit')->truncate();
        }

        $this->info("Kreditlar import qilinmoqda (xodim_id: {$xodimId})...");
        $count = 0;

        $this->importService->kreditlarImport(
            DB::table('temp_reg_kredit')->cursor(),
            $xodimId,
            $count
        );

        $this->success("Kreditlar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function tovarlarImport(bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_ch_kt_tv')) {
            $this->error('temp_ch_kt_tv jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Tovarlar jadvalini tozalaymizmi?')) {
            DB::table('tovarlar')->truncate();
        }

        $this->info('Tovarlar import qilinmoqda...');
        $count = 0;

        $this->importService->tovarlarImport(
            DB::table('temp_ch_kt_tv')->cursor(),
            $count
        );

        $this->success("Tovarlar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function grafikImport(bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_grafik')) {
            $this->error('temp_grafik jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Grafik jadvalini tozalaymizmi?')) {
            DB::table('grafik')->truncate();
        }

        $this->info('Grafik import qilinmoqda (bu biroz vaqt olishi mumkin)...');
        $count = 0;
        $jami  = DB::table('temp_grafik')->count();

        $bar = $this->output->createProgressBar($jami);
        $bar->setRedrawFrequency(1000);

        // Cursor bilan xotira tejash
        $this->importService->grafikImport(
            DB::table('temp_grafik')->cursor(),
            $count
        );

        $bar->finish();
        $this->newLine();
        $this->success("Grafik import qilindi: {$count} ta ({$jami} dan)");
        return self::SUCCESS;
    }

    private function tulovlarImport(int $xodimId, bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_tulov_kt')) {
            $this->error('temp_tulov_kt jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('To\'lovlar jadvalini tozalaymizmi?')) {
            DB::table('tulovlar')->truncate();
        }

        $this->info('To\'lovlar import qilinmoqda...');
        $count = 0;

        $this->importService->tulovlarImport(
            DB::table('temp_tulov_kt')->cursor(),
            $xodimId,
            $count
        );

        $this->success("To'lovlar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function oldinTulovlarImport(int $xodimId, bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_tulov_oldindan')) {
            $this->error('temp_tulov_oldindan jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Oldindan to\'lovlar jadvalini tozalaymizmi?')) {
            DB::table('oldindan_tulov')->truncate();
        }

        $this->info('Oldindan to\'lovlar import qilinmoqda...');
        $count = 0;

        $this->importService->oldinTulovlarImport(
            DB::table('temp_tulov_oldindan')->cursor(),
            $xodimId,
            $count
        );

        $this->success("Oldindan to'lovlar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function taminotchilarImport(bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_taminotchi')) {
            $this->error('temp_taminotchi jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Ta\'minotchilar jadvalini tozalaymizmi?')) {
            DB::table('taminotchilar')->truncate();
        }

        $this->info('Ta\'minotchilar import qilinmoqda...');
        $count = 0;

        $this->importService->taminotchilarImport(
            DB::table('temp_taminotchi')->cursor(),
            $count
        );

        $this->success("Ta'minotchilar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function taminotKirimlarImport(int $xodimId, bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_taminot_kirim') || !$this->jadvalBorMi('temp_taminot_kirim_qator')) {
            $this->error('temp_taminot_kirim yoki temp_taminot_kirim_qator jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Ta\'minot kirimlari jadvallarini tozalaymizmi?')) {
            DB::table('taminot_kirim_qatorlar')->truncate();
            DB::table('taminot_kirimlar')->truncate();
        }

        $this->info('Ta\'minot kirimlari import qilinmoqda...');
        $count = 0;
        $qatorCount = 0;

        $this->importService->taminotKirimlarImport(
            DB::table('temp_taminot_kirim')->cursor(),
            DB::table('temp_taminot_kirim_qator')->cursor(),
            $xodimId,
            $count,
            $qatorCount
        );

        $this->success("Ta'minot kirimlari import qilindi: {$count} ta ({$qatorCount} qator)");
        return self::SUCCESS;
    }

    private function harajatlarImport(int $xodimId, bool $fresh = false): int
    {
        if (!$this->jadvalBorMi('temp_harajat')) {
            $this->error('temp_harajat jadvali topilmadi!');
            return self::FAILURE;
        }

        if ($fresh && $this->confirm('Harajatlar jadvalini tozalaymizmi?')) {
            DB::table('harajatlar')->truncate();
        }

        $this->info('Harajatlar import qilinmoqda...');
        $count = 0;

        $this->importService->harajatlarImport(
            DB::table('temp_harajat')->cursor(),
            $xodimId,
            $count
        );

        $this->success("Harajatlar import qilindi: {$count} ta");
        return self::SUCCESS;
    }

    private function hammasiniImport(int $xodimId): int
    {
        $this->warn('BARCHA BOSQICHLAR KETMA-KET BAJARILADI!');
        if (!$this->confirm('Davom etasizmi?')) {
            return self::SUCCESS;
        }

        $bosqichlar = [
            'filiallar',
            'mijozlar',
            'kreditlar',
            'tovarlar',
            'grafik',
            'tulovlar',       // tulov-turlari uchun temp_tulov_kt kerak — avval tulovlar import qilinadi
            'tulov-turlari',  // temp_tulov_kt dan noyob nomlarni oladi
            'oldin-tulovlar',
            'taminotchilar',
            'taminot-kirimlar',
            'harajatlar',
        ];

        foreach ($bosqichlar as $bosqich) {
            $this->newLine();
            $this->info("══════════ {$bosqich} ══════════");
            $natija = $this->call('nasiya:import', [
                'bosqich'     => $bosqich,
                '--xodim-id'  => $xodimId,
            ]);

            if ($natija !== self::SUCCESS) {
                $this->error("{$bosqich} bosqichida xatolik! To'xtatildi.");
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->success('Barcha bosqichlar muvaffaqiyatli bajarildi!');
        return self::SUCCESS;
    }

    // ─── Yordamchi metodlar ───────────────────────────────────────

    private function jadvalBorMi(string $jadvalNomi): bool
    {
        return Schema::hasTable($jadvalNomi);
    }

    private function success(string $xabar): void
    {
        $this->line("  <fg=green>✓</> {$xabar}");
    }

    private function nomaluumBosqich(string $bosqich): int
    {
        $this->error("Noma'lum bosqich: {$bosqich}");
        $this->line('Mavjud bosqichlar: filiallar, tulov-turlari, mijozlar, kreditlar, tovarlar, grafik, tulovlar, oldin-tulovlar, taminotchilar, taminot-kirimlar, harajatlar, hammasi');
        return self::FAILURE;
    }
}
