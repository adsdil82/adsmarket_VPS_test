<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ota = DB::table('pul_kategoriyalar')->where('kod', 'CF-2700')->first();
        if (!$ota) return;

        if (DB::table('pul_kategoriyalar')->where('kod', 'CF-2740')->exists()) return;

        DB::table('pul_kategoriyalar')->insert([
            'ota_id'     => $ota->id,
            'yunalish'   => 'chiqim',
            'kod'        => 'CF-2740',
            'nomi'       => 'POS savdo qaytimi (mijozga pul qaytarish)',
            'hisob_id'   => null,
            'avtomatik'  => true,
            'rang'       => 'red',
            'sort_order' => 40,
            'holat'      => 'faol',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('pul_kategoriyalar')->where('kod', 'CF-2740')->delete();
    }
};
