<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Birlik;
use App\Models\HarajatTuri;

class MalumotnomalarSeeder extends Seeder
{
    public function run(): void
    {
        // ── Birliklar ────────────────────────────────────────────────
        $birliklar = [
            ['nomi'=>'Dona',      'qisqa_nomi'=>'don',  'kod'=>'PCS',  'sort_order'=>10],
            ['nomi'=>'Kilogram',  'qisqa_nomi'=>'kg',   'kod'=>'KG',   'sort_order'=>20],
            ['nomi'=>'Litr',      'qisqa_nomi'=>'l',    'kod'=>'L',    'sort_order'=>30],
            ['nomi'=>'Metr',      'qisqa_nomi'=>'m',    'kod'=>'M',    'sort_order'=>40],
            ['nomi'=>'Metr kv.',  'qisqa_nomi'=>'m²',   'kod'=>'M2',   'sort_order'=>50],
            ['nomi'=>'Komplekt',  'qisqa_nomi'=>'kpl',  'kod'=>'SET',  'sort_order'=>60],
            ['nomi'=>'Quti',      'qisqa_nomi'=>'qt',   'kod'=>'BOX',  'sort_order'=>70],
            ['nomi'=>'Juft',      'qisqa_nomi'=>'jft',  'kod'=>'PAIR', 'sort_order'=>80],
        ];
        foreach ($birliklar as $b) {
            Birlik::firstOrCreate(['kod' => $b['kod']], $b);
        }

        // ── Harajat turlari ──────────────────────────────────────────
        $turlar = [
            ['nomi'=>'Ijara',            'kod'=>'IJARA',    'rang'=>'primary',   'sort_order'=>10],
            ['nomi'=>'Oylik maosh',      'kod'=>'OYLIK',    'rang'=>'success',   'sort_order'=>20],
            ['nomi'=>'Transport',        'kod'=>'TRANSPORT','rang'=>'info',      'sort_order'=>30],
            ['nomi'=>'Internet va aloqa','kod'=>'INTERNET', 'rang'=>'info',      'sort_order'=>40],
            ['nomi'=>'Reklama',          'kod'=>'REKLAMA',  'rang'=>'warning',   'sort_order'=>50],
            ['nomi'=>'Kommunal',         'kod'=>'KOMMUNAL', 'rang'=>'secondary', 'sort_order'=>60],
            ['nomi'=>'Soliq va yig\'im', 'kod'=>'SOLIK',    'rang'=>'danger',    'sort_order'=>70],
            ['nomi'=>'Moddiy xarajat',   'kod'=>'MODDIY',   'rang'=>'secondary', 'sort_order'=>80],
            ['nomi'=>'Boshqa harajat',   'kod'=>'BOSHQA',   'rang'=>'secondary', 'sort_order'=>990],
        ];
        foreach ($turlar as $t) {
            HarajatTuri::firstOrCreate(['kod' => $t['kod']], $t);
        }

        $this->command->info('MalumotnomalarSeeder: ' . count($birliklar) . ' birlik, ' . count($turlar) . ' harajat turi seed qilindi.');
    }
}
