<?php

namespace Database\Seeders;

use App\Models\Filial;
use Illuminate\Database\Seeder;

class FiliallarSeeder extends Seeder
{
    /**
     * ImkonPlus — bitta filial, ID 1 bo'lishi MUHIM!
     * Import fayllarida filial_kod=1 bilan beriladi.
     */
    public function run(): void
    {
        Filial::updateOrCreate(['id' => 1], [
            'id'     => 1,
            'nomi'   => 'ImkonPlus',
            'kod'    => 'IP',
            'manzil' => 'Buvayda tumani Okkurgon',
            'holat'  => 'faol',
        ]);

        $this->command->info('Filiallar (1 ta) seed qilindi.');
    }
}
