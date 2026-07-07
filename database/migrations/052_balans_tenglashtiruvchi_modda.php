<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bl_qatorlari MODIFY hisoblash_turi ENUM(
            'avtomat_naqd_pul',
            'avtomat_ombor_qiymati',
            'avtomat_nasiya_debitorlik',
            'avtomat_taminotchi_qarz',
            'avtomat_jamgarilgan_foyda',
            'avtomat_balans_tenglashtiruvchi',
            'qolda'
        ) NOT NULL DEFAULT 'qolda'");

        $kapitalBolim = DB::table('bl_bolimlar')->where('tur', 'kapital')->first();
        if (!$kapitalBolim) return;

        $mavjud = DB::table('bl_qatorlari')
            ->where('bolim_id', $kapitalBolim->id)
            ->where('hisoblash_turi', 'avtomat_balans_tenglashtiruvchi')
            ->exists();
        if ($mavjud) return;

        $sortOrder = (DB::table('bl_qatorlari')->where('bolim_id', $kapitalBolim->id)->max('sort_order') ?? 0) + 1;

        DB::table('bl_qatorlari')->insert([
            'bolim_id'          => $kapitalBolim->id,
            'nomi'              => 'Balans tenglashtiruvchi farq (vaqtinchalik)',
            'hisoblash_turi'    => 'avtomat_balans_tenglashtiruvchi',
            'joriy_holat_faqat' => false,
            'sort_order'        => $sortOrder,
            'holat'             => 'faol',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        $kapitalBolim = DB::table('bl_bolimlar')->where('tur', 'kapital')->first();
        if ($kapitalBolim) {
            DB::table('bl_qatorlari')
                ->where('bolim_id', $kapitalBolim->id)
                ->where('hisoblash_turi', 'avtomat_balans_tenglashtiruvchi')
                ->delete();
        }

        DB::statement("ALTER TABLE bl_qatorlari MODIFY hisoblash_turi ENUM(
            'avtomat_naqd_pul',
            'avtomat_ombor_qiymati',
            'avtomat_nasiya_debitorlik',
            'avtomat_taminotchi_qarz',
            'avtomat_jamgarilgan_foyda',
            'qolda'
        ) NOT NULL DEFAULT 'qolda'");
    }
};
