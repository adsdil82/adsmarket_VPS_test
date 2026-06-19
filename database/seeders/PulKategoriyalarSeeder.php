<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PulKategoriyalarSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $h = fn(string $raqam) => DB::table('hisoblar_rejasi')->where('hisob_raqam', $raqam)->value('id');

        $kategoriyalar = [
            // KIRIM (CF-1xxx)
            ['id'=>1,  'ota_id'=>null, 'yunalish'=>'kirim',  'kod'=>'CF-1000', 'nomi'=>"Operatsion kirimlar",       'rang'=>'green',  'sort_order'=>10, 'avtomatik'=>false],
            ['id'=>2,  'ota_id'=>1,    'yunalish'=>'kirim',  'kod'=>'CF-1100', 'nomi'=>"Nasiya to'lovlari",          'rang'=>'green',  'sort_order'=>11, 'avtomatik'=>true],
            ['id'=>3,  'ota_id'=>1,    'yunalish'=>'kirim',  'kod'=>'CF-1200', 'nomi'=>"Oldindan to'lovlar",         'rang'=>'green',  'sort_order'=>12, 'avtomatik'=>true],
            ['id'=>4,  'ota_id'=>1,    'yunalish'=>'kirim',  'kod'=>'CF-1300', 'nomi'=>'Naqd savdo (POS)',            'rang'=>'green',  'sort_order'=>13, 'avtomatik'=>true],
            ['id'=>5,  'ota_id'=>1,    'yunalish'=>'kirim',  'kod'=>'CF-1900', 'nomi'=>'Boshqa kirimlar',             'rang'=>'green',  'sort_order'=>19, 'avtomatik'=>false],
            ['id'=>6,  'ota_id'=>null, 'yunalish'=>'kirim',  'kod'=>'CF-1500', 'nomi'=>'Moliyaviy kirimlar',          'rang'=>'blue',   'sort_order'=>50, 'avtomatik'=>false],
            ['id'=>7,  'ota_id'=>6,    'yunalish'=>'kirim',  'kod'=>'CF-1510', 'nomi'=>'Bank kreditlari (olingan)',   'rang'=>'blue',   'sort_order'=>51, 'avtomatik'=>false],
            ['id'=>8,  'ota_id'=>6,    'yunalish'=>'kirim',  'kod'=>'CF-1520', 'nomi'=>'Inkasso va qaytarmalar',      'rang'=>'blue',   'sort_order'=>52, 'avtomatik'=>false],
            // CHIQIM (CF-2xxx)
            ['id'=>10, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2100', 'nomi'=>'Mehnat haqi',                'rang'=>'red',    'sort_order'=>10, 'avtomatik'=>false],
            ['id'=>11, 'ota_id'=>10,   'yunalish'=>'chiqim', 'kod'=>'CF-2110', 'nomi'=>'Asosiy maosh',               'rang'=>'red',    'sort_order'=>11, 'avtomatik'=>false],
            ['id'=>12, 'ota_id'=>10,   'yunalish'=>'chiqim', 'kod'=>'CF-2120', 'nomi'=>'Bonus va mukofot',            'rang'=>'red',    'sort_order'=>12, 'avtomatik'=>false],
            ['id'=>13, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2200', 'nomi'=>'Ijara va kommunal',           'rang'=>'orange', 'sort_order'=>20, 'avtomatik'=>false],
            ['id'=>14, 'ota_id'=>13,   'yunalish'=>'chiqim', 'kod'=>'CF-2210', 'nomi'=>"Do'kon ijarasi",              'rang'=>'orange', 'sort_order'=>21, 'avtomatik'=>false],
            ['id'=>15, 'ota_id'=>13,   'yunalish'=>'chiqim', 'kod'=>'CF-2220', 'nomi'=>'Elektr, Gaz, Suv',           'rang'=>'orange', 'sort_order'=>22, 'avtomatik'=>false],
            ['id'=>16, 'ota_id'=>13,   'yunalish'=>'chiqim', 'kod'=>'CF-2230', 'nomi'=>'Internet va Telefon',         'rang'=>'orange', 'sort_order'=>23, 'avtomatik'=>false],
            ['id'=>17, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2300', 'nomi'=>"Ta'minotchilarga to'lov",    'rang'=>'purple', 'sort_order'=>30, 'avtomatik'=>true],
            ['id'=>18, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2400', 'nomi'=>'Bank xarajatlari',            'rang'=>'blue',   'sort_order'=>40, 'avtomatik'=>false],
            ['id'=>19, 'ota_id'=>18,   'yunalish'=>'chiqim', 'kod'=>'CF-2410', 'nomi'=>'Bank komissiyasi',            'rang'=>'blue',   'sort_order'=>41, 'avtomatik'=>false],
            ['id'=>20, 'ota_id'=>18,   'yunalish'=>'chiqim', 'kod'=>'CF-2420', 'nomi'=>"Bank kredit to'lovi",         'rang'=>'blue',   'sort_order'=>42, 'avtomatik'=>false],
            ['id'=>21, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2500', 'nomi'=>'Transport va logistika',      'rang'=>'yellow', 'sort_order'=>50, 'avtomatik'=>false],
            ['id'=>22, 'ota_id'=>21,   'yunalish'=>'chiqim', 'kod'=>'CF-2510', 'nomi'=>'Mijoz etkazish xarajati',    'rang'=>'yellow', 'sort_order'=>51, 'avtomatik'=>false],
            ['id'=>23, 'ota_id'=>21,   'yunalish'=>'chiqim', 'kod'=>'CF-2520', 'nomi'=>"Ta'minotchidan tovar olish", 'rang'=>'yellow', 'sort_order'=>52, 'avtomatik'=>false],
            ['id'=>24, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2600', 'nomi'=>'Moliyaviy chiqimlar',         'rang'=>'red',    'sort_order'=>60, 'avtomatik'=>false],
            ['id'=>25, 'ota_id'=>24,   'yunalish'=>'chiqim', 'kod'=>'CF-2610', 'nomi'=>'Soliq va ZBF',               'rang'=>'red',    'sort_order'=>61, 'avtomatik'=>false],
            ['id'=>26, 'ota_id'=>24,   'yunalish'=>'chiqim', 'kod'=>'CF-2620', 'nomi'=>'Dividend',                   'rang'=>'red',    'sort_order'=>62, 'avtomatik'=>false],
            ['id'=>27, 'ota_id'=>null, 'yunalish'=>'chiqim', 'kod'=>'CF-2700', 'nomi'=>'Boshqa chiqimlar',            'rang'=>'gray',   'sort_order'=>90, 'avtomatik'=>false],
            ['id'=>28, 'ota_id'=>27,   'yunalish'=>'chiqim', 'kod'=>'CF-2710', 'nomi'=>'Kancellyariya',              'rang'=>'gray',   'sort_order'=>91, 'avtomatik'=>false],
            ['id'=>29, 'ota_id'=>27,   'yunalish'=>'chiqim', 'kod'=>'CF-2720', 'nomi'=>'Ovqat (xodimlar)',           'rang'=>'gray',   'sort_order'=>92, 'avtomatik'=>false],
            ['id'=>30, 'ota_id'=>27,   'yunalish'=>'chiqim', 'kod'=>'CF-2730', 'nomi'=>"Sovg'alar va Ehson",         'rang'=>'gray',   'sort_order'=>93, 'avtomatik'=>false],
            ['id'=>31, 'ota_id'=>27,   'yunalish'=>'chiqim', 'kod'=>'CF-2790', 'nomi'=>'Boshqa operatsion',          'rang'=>'gray',   'sort_order'=>99, 'avtomatik'=>false],
        ];

        foreach ($kategoriyalar as $k) {
            DB::table('pul_kategoriyalar')->updateOrInsert(
                ['id' => $k['id']],
                array_merge($k, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('Pul kategoriyalari (' . count($kategoriyalar) . ' ta) seed qilindi.');
    }
}
