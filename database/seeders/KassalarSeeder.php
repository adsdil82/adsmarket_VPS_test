<?php

namespace Database\Seeders;

use App\Models\Kassa;
use Illuminate\Database\Seeder;

class KassalarSeeder extends Seeder
{
    public function run(): void
    {
        $kassalar = [
            ['filial_id' => 1, 'nomi' => 'Asosiy kassa (naqd)',    'tur' => 'naqd',     'qoldiq' => 0, 'valyuta' => 'UZS'],
            ['filial_id' => 1, 'nomi' => 'HamkorBank Terminal',     'tur' => 'terminal', 'qoldiq' => 0, 'valyuta' => 'UZS'],
            ['filial_id' => 1, 'nomi' => 'AgroBank Terminal',       'tur' => 'terminal', 'qoldiq' => 0, 'valyuta' => 'UZS'],
            ['filial_id' => 1, 'nomi' => 'Bank hisob-raqami',       'tur' => 'bank',     'qoldiq' => 0, 'valyuta' => 'UZS'],
        ];

        foreach ($kassalar as $k) {
            Kassa::firstOrCreate(['nomi' => $k['nomi'], 'filial_id' => $k['filial_id']], $k);
        }

        $this->command->info('Kassalar (' . count($kassalar) . ' ta) seed qilindi.');
    }
}
