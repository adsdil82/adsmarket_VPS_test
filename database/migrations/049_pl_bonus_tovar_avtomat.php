<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pl_qatorlari MODIFY hisoblash_turi ENUM(
            'avtomat_naqd_savdo', 'avtomat_naqd_tannarx', 'avtomat_nasiya_savdo',
            'avtomat_harajat_turi', 'avtomat_bonus_tovar', 'qolda'
        ) DEFAULT 'qolda'");

        // "Bonusga berilgan tovar summasi" qatori endi tovarlar.turi='bonus' dan
        // avtomatik hisoblanadi (qo'lda kiritish shart emas).
        DB::table('pl_qatorlari')
            ->where('nomi', 'Bonusga berilgan tovar summasi')
            ->update(['hisoblash_turi' => 'avtomat_bonus_tovar']);
    }

    public function down(): void
    {
        DB::table('pl_qatorlari')
            ->where('hisoblash_turi', 'avtomat_bonus_tovar')
            ->update(['hisoblash_turi' => 'qolda']);

        DB::statement("ALTER TABLE pl_qatorlari MODIFY hisoblash_turi ENUM(
            'avtomat_naqd_savdo', 'avtomat_naqd_tannarx', 'avtomat_nasiya_savdo',
            'avtomat_harajat_turi', 'qolda'
        ) DEFAULT 'qolda'");
    }
};
