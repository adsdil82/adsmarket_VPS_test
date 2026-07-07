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
            'avtomat_rezerv_nasiya',
            'qolda'
        ) NOT NULL DEFAULT 'qolda'");

        $aktivlarBolim = DB::table('bl_bolimlar')->where('tur', 'aktiv')->first();
        if (!$aktivlarBolim) return;

        $mavjud = DB::table('bl_qatorlari')
            ->where('bolim_id', $aktivlarBolim->id)
            ->where('hisoblash_turi', 'avtomat_rezerv_nasiya')
            ->exists();
        if ($mavjud) return;

        $sortOrder = (DB::table('bl_qatorlari')->where('bolim_id', $aktivlarBolim->id)->max('sort_order') ?? 0) + 1;

        DB::table('bl_qatorlari')->insert([
            'bolim_id'          => $aktivlarBolim->id,
            'nomi'              => "Rezerv nasiya (kechikkan/muddati o'tgan qarzlar zaxirasi)",
            'hisoblash_turi'    => 'avtomat_rezerv_nasiya',
            'joriy_holat_faqat' => false,
            'sort_order'        => $sortOrder,
            'holat'             => 'faol',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    public function down(): void
    {
        $aktivlarBolim = DB::table('bl_bolimlar')->where('tur', 'aktiv')->first();
        if ($aktivlarBolim) {
            DB::table('bl_qatorlari')
                ->where('bolim_id', $aktivlarBolim->id)
                ->where('hisoblash_turi', 'avtomat_rezerv_nasiya')
                ->delete();
        }

        DB::statement("ALTER TABLE bl_qatorlari MODIFY hisoblash_turi ENUM(
            'avtomat_naqd_pul',
            'avtomat_ombor_qiymati',
            'avtomat_nasiya_debitorlik',
            'avtomat_taminotchi_qarz',
            'avtomat_jamgarilgan_foyda',
            'avtomat_balans_tenglashtiruvchi',
            'qolda'
        ) NOT NULL DEFAULT 'qolda'");
    }
};
