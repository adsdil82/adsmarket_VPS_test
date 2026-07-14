<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Shartnomalar" ro'yxati sahifasidagi kechikkan_summa/max_kechikish_kun
 * subquery'lari (RegKreditController::kreditlarSorovi) grafik jadvalini
 * reg_kredit_id+holat+tolov_sana bo'yicha filtrlaydi — mavjud
 * (reg_kredit_id, holat) indeksi tolov_sana ustidagi filtrni qamrab
 * olmaydi. grafik jadvali kattalashgani sayin (migratsiyalardan keyin
 * ~68 ming qator) bu sahifa VPS'ni qiynay boshladi (2026-07-14).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! $this->indexBormi()) {
            DB::statement('CREATE INDEX grafik_reg_kredit_id_holat_tolov_sana_index ON grafik (reg_kredit_id, holat, tolov_sana)');
        }
    }

    public function down(): void
    {
        if ($this->indexBormi()) {
            DB::statement('DROP INDEX grafik_reg_kredit_id_holat_tolov_sana_index ON grafik');
        }
    }

    private function indexBormi(): bool
    {
        $natija = DB::select("SHOW INDEX FROM grafik WHERE Key_name = 'grafik_reg_kredit_id_holat_tolov_sana_index'");
        return count($natija) > 0;
    }
};
