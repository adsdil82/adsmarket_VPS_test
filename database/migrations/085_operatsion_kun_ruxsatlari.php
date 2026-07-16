<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Yangi 'operatsion_kun' resursi uchun standart ruxsatlar.
 * Admin har doim to'liq huquqli (Foydalanuvchi::ruxsat() dagi isAdmin() qisqa
 * yo'li orqali), shuning uchun bu yerda admin qatorlari faqat "Ruxsatlar"
 * sahifasida ko'rinish/izchillik uchun qo'shiladi. Menejer kunni ko'ra va
 * yopa oladi, lekin qayta ocholmaydi (spetsifikatsiya talabi).
 */
return new class extends Migration
{
    public function up(): void
    {
        $qatorlar = [
            ['rol' => 'admin',   'resurs' => 'operatsion_kun', 'amal' => 'korish',          'ruxsat' => 1],
            ['rol' => 'admin',   'resurs' => 'operatsion_kun', 'amal' => 'yopish',          'ruxsat' => 1],
            ['rol' => 'admin',   'resurs' => 'operatsion_kun', 'amal' => 'ochish',          'ruxsat' => 1],
            ['rol' => 'admin',   'resurs' => 'operatsion_kun', 'amal' => 'eski_tahrirlash', 'ruxsat' => 1],
            ['rol' => 'menejer', 'resurs' => 'operatsion_kun', 'amal' => 'korish',          'ruxsat' => 1],
            ['rol' => 'menejer', 'resurs' => 'operatsion_kun', 'amal' => 'yopish',          'ruxsat' => 1],
            ['rol' => 'menejer', 'resurs' => 'operatsion_kun', 'amal' => 'ochish',          'ruxsat' => 0],
            ['rol' => 'menejer', 'resurs' => 'operatsion_kun', 'amal' => 'eski_tahrirlash', 'ruxsat' => 0],
        ];

        foreach ($qatorlar as $q) {
            DB::table('ruxsatlar')->updateOrInsert(
                ['rol' => $q['rol'], 'resurs' => $q['resurs'], 'amal' => $q['amal']],
                ['ruxsat' => $q['ruxsat']]
            );
        }
    }

    public function down(): void
    {
        DB::table('ruxsatlar')->where('resurs', 'operatsion_kun')->delete();
    }
};
