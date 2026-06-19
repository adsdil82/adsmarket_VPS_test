<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * ImkonPlus uchun:
     *   1. Filiallar (1 ta, id=1)
     *   2. Foydalanuvchilar (admin + import xodimi)
     *
     *   To'lov turlari import paytida avtomatik yaratiladi (firstOrCreate).
     *
     * Import uchun:
     *   php artisan nasiya:import hammasi --xodim-id={import xodimi ID}
     */
    public function run(): void
    {
        $this->call([
            FiliallarSeeder::class,
            FoydalanuvchilarSeeder::class,
        ]);
    }
}
