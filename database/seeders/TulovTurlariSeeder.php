<?php

namespace Database\Seeders;

use App\Models\TulovTuri;
use Illuminate\Database\Seeder;

class TulovTurlariSeeder extends Seeder
{
    /**
     * To'lov turlari — eski bazadagi barcha noyob nomlar.
     * Bu ro'yxat eski MDB fayllardan olingan haqiqiy nomlar.
     * Import jarayonida tulov_kt va tulov_oldindan_KT fayllaridan yangi nomlar
     * avtomatik qo'shiladi (ImportService orqali).
     */
    public function run(): void
    {
        $tulovTurlari = [
            // Naqd to'lovlar
            ['nomi' => 'Naqd', 'holat' => 'faol'],
            ['nomi' => 'Накд', 'holat' => 'faol'],
            ['nomi' => 'Накд Город', 'holat' => 'faol'],
            ['nomi' => 'Накд Яккатут', 'holat' => 'faol'],
            ['nomi' => 'Накд Навоий', 'holat' => 'faol'],
            ['nomi' => 'Накд Мингтут', 'holat' => 'faol'],
            ['nomi' => 'Накд Туркистан', 'holat' => 'faol'],

            // Bank to'lovlari
            ['nomi' => 'Банк', 'holat' => 'faol'],
            ['nomi' => 'Bank', 'holat' => 'faol'],
            ['nomi' => 'Банк Город', 'holat' => 'faol'],
            ['nomi' => 'Банк Яккатут', 'holat' => 'faol'],

            // Karta to'lovlari
            ['nomi' => 'UZCARD', 'holat' => 'faol'],
            ['nomi' => 'Uzcard', 'holat' => 'faol'],
            ['nomi' => 'HUMO', 'holat' => 'faol'],
            ['nomi' => 'Humo', 'holat' => 'faol'],
            ['nomi' => 'Эркин UZCARD', 'holat' => 'faol'],
            ['nomi' => 'Эркин HUMO', 'holat' => 'faol'],
            ['nomi' => 'UZCARD Город', 'holat' => 'faol'],
            ['nomi' => 'HUMO Город', 'holat' => 'faol'],
            ['nomi' => 'UZCARD Яккатут', 'holat' => 'faol'],
            ['nomi' => 'HUMO Яккатут', 'holat' => 'faol'],
            ['nomi' => 'UZCARD Навоий', 'holat' => 'faol'],
            ['nomi' => 'HUMO Навоий', 'holat' => 'faol'],
            ['nomi' => 'UZCARD Мингтут', 'holat' => 'faol'],
            ['nomi' => 'HUMO Мингтут', 'holat' => 'faol'],
            ['nomi' => 'UZCARD Туркистан', 'holat' => 'faol'],
            ['nomi' => 'HUMO Туркистан', 'holat' => 'faol'],

            // Online to'lovlar
            ['nomi' => 'Click', 'holat' => 'faol'],
            ['nomi' => 'CLICK', 'holat' => 'faol'],
            ['nomi' => 'Payme', 'holat' => 'faol'],
            ['nomi' => 'PayMe', 'holat' => 'faol'],
            ['nomi' => 'Apelsin', 'holat' => 'faol'],

            // Boshqa
            ['nomi' => 'Перечисление', 'holat' => 'faol'],
            ['nomi' => 'Город Касса', 'holat' => 'faol'],
            ['nomi' => 'Яккатут Касса', 'holat' => 'faol'],
            ['nomi' => 'Туркистан Касса', 'holat' => 'faol'],
            ['nomi' => 'Навоий Касса', 'holat' => 'faol'],
            ['nomi' => 'Мингтут Касса', 'holat' => 'faol'],
            ['nomi' => 'Noma\'lum', 'holat' => 'nofaol'],
        ];

        foreach ($tulovTurlari as $tur) {
            TulovTuri::firstOrCreate(['nomi' => $tur['nomi']], $tur);
        }

        $this->command->info("To'lov turlari (" . count($tulovTurlari) . " ta) seed qilindi.");
    }
}
