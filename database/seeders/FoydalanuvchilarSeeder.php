<?php

namespace Database\Seeders;

use App\Models\Foydalanuvchi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FoydalanuvchilarSeeder extends Seeder
{
    /**
     * Boshlang'ich foydalanuvchilar.
     * MUHIM: Admin parolini birinchi kirishdan keyin ozgartiring!
     */
    public function run(): void
    {
        $foydalanuvchilar = [
            [
                'filial_id'    => null,
                'ism_familiya' => 'Tizim Admin',
                'email'        => 'admin@imkonplus.uz',
                'password'     => Hash::make('2p7UDJmRxUHGpB3k'),
                'rol'          => 'admin',
                'holat'        => 'faol',
            ],
            [
                'filial_id'    => null,
                'ism_familiya' => 'Import Xodimi',
                'email'        => 'import@imkonplus.uz',
                'password'     => Hash::make('Import@2024!'),
                'rol'          => 'admin',
                'holat'        => 'faol',
            ],
        ];

        foreach ($foydalanuvchilar as $f) {
            Foydalanuvchi::updateOrCreate(
                ['email' => $f['email']],
                $f
            );
        }

        $this->command->info('Foydalanuvchilar (' . count($foydalanuvchilar) . ' ta) seed qilindi.');
        $this->command->warn('MUHIM: Admin parolini darhol ozgartiring!');
    }
}
