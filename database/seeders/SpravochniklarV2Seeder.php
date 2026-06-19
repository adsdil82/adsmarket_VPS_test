<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpravochniklarV2Seeder extends Seeder
{
    public function run(): void
    {
        // ── Valyutalar ────────────────────────────────────────────────
        $valyutalar = [
            ['kod'=>'UZS','nomi'=>"O'zbek so'mi",'belgi'=>"so'm",'kurs'=>1.0000,'asosiy'=>true,'holat'=>'faol'],
            ['kod'=>'USD','nomi'=>'AQSh dollari','belgi'=>'$','kurs'=>12700.0000,'asosiy'=>false,'holat'=>'faol'],
            ['kod'=>'EUR','nomi'=>'Yevro','belgi'=>'€','kurs'=>13800.0000,'asosiy'=>false,'holat'=>'faol'],
            ['kod'=>'RUB','nomi'=>'Rossiya rubli','belgi'=>'₽','kurs'=>140.0000,'asosiy'=>false,'holat'=>'faol'],
        ];
        foreach ($valyutalar as $v) {
            DB::table('valyutalar')->insertOrIgnore($v + [
                'kurs_sana'  => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── Statuslar va sabablar ─────────────────────────────────────
        $statuses = [
            // kredit statuslari
            ['modul'=>'kredit','tur'=>'status','kod'=>'faol',       'nomi'=>'Faol',          'rang'=>'success','tizim_holati'=>true,'sort_order'=>10],
            ['modul'=>'kredit','tur'=>'status','kod'=>'muddati_otkn','nomi'=>'Muddati o\'tgan','rang'=>'danger','tizim_holati'=>true,'sort_order'=>20],
            ['modul'=>'kredit','tur'=>'status','kod'=>'yopilgan',   'nomi'=>'Yopilgan',       'rang'=>'secondary','tizim_holati'=>true,'sort_order'=>30],
            ['modul'=>'kredit','tur'=>'status','kod'=>'qaytarilgan','nomi'=>'Qaytarilgan',    'rang'=>'warning','tizim_holati'=>true,'sort_order'=>40],
            // kredit yopilish sabablari
            ['modul'=>'kredit','tur'=>'sabab','kod'=>'tolik_tolandi','nomi'=>'To\'liq to\'landi','rang'=>'success','tizim_holati'=>false,'sort_order'=>10],
            ['modul'=>'kredit','tur'=>'sabab','kod'=>'erta_tolandi', 'nomi'=>'Erta to\'landi',  'rang'=>'info',   'tizim_holati'=>false,'sort_order'=>20],
            ['modul'=>'kredit','tur'=>'sabab','kod'=>'qaytarildi',   'nomi'=>'Tovar qaytarildi','rang'=>'warning','tizim_holati'=>false,'sort_order'=>30],
            ['modul'=>'kredit','tur'=>'sabab','kod'=>'hisobdan_ochirish','nomi'=>'Hisobdan o\'chirish','rang'=>'danger','tizim_holati'=>false,'sort_order'=>40],
            // to'lov statuslari
            ['modul'=>'tolov','tur'=>'status','kod'=>'qabul_qilindi','nomi'=>'Qabul qilindi','rang'=>'success','tizim_holati'=>true,'sort_order'=>10],
            ['modul'=>'tolov','tur'=>'status','kod'=>'bekor_qilindi','nomi'=>'Bekor qilindi','rang'=>'danger', 'tizim_holati'=>true,'sort_order'=>20],
            // qaytarish sabablari
            ['modul'=>'qaytarish','tur'=>'sabab','kod'=>'nuqsonli',   'nomi'=>'Nuqsonli tovar',   'rang'=>'danger', 'tizim_holati'=>false,'sort_order'=>10],
            ['modul'=>'qaytarish','tur'=>'sabab','kod'=>'xato_model', 'nomi'=>'Xato model',        'rang'=>'warning','tizim_holati'=>false,'sort_order'=>20],
            ['modul'=>'qaytarish','tur'=>'sabab','kod'=>'mijoz_rad',  'nomi'=>'Mijoz rad etdi',     'rang'=>'secondary','tizim_holati'=>false,'sort_order'=>30],
            // qurilma holatlari
            ['modul'=>'qurilma','tur'=>'holat','kod'=>'band',       'nomi'=>'Band (kreditda)','rang'=>'warning','tizim_holati'=>true,'sort_order'=>10],
            ['modul'=>'qurilma','tur'=>'holat','kod'=>'ozod',        'nomi'=>'Ozod',           'rang'=>'success','tizim_holati'=>true,'sort_order'=>20],
            ['modul'=>'qurilma','tur'=>'holat','kod'=>'bloklangan',  'nomi'=>'Bloklangan',     'rang'=>'danger', 'tizim_holati'=>true,'sort_order'=>30],
            ['modul'=>'qurilma','tur'=>'holat','kod'=>'qaytarilgan', 'nomi'=>'Qaytarilgan',    'rang'=>'secondary','tizim_holati'=>false,'sort_order'=>40],
        ];

        foreach ($statuses as $s) {
            DB::table('statuslar_sabablar')->insertOrIgnore($s + [
                'holat'      => 'faol',
                'icon'       => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
