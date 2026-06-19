<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceControlSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── 1. Provayderlar ───────────────────────────────────────
        $provayderlar = [
            [
                'kod'          => 'samsung_knox_guard',
                'nomi'         => 'Samsung Knox Guard',
                'tur'          => 'mdm',
                'faol'         => false,
                'mock_rejim'   => true,
                'lock_qollab'  => true,
                'unlock_qollab'=> true,
                'ogoh_qollab'  => true,
                'sinx_qollab'  => true,
                'tavsif'       => 'Samsung qurilmalarini masofadan boshqarish tizimi',
                'sort_order'   => 1,
            ],
            [
                'kod'          => 'uzimei',
                'nomi'         => 'UZIMEI (O\'zbekiston IMEI Registri)',
                'tur'          => 'imei_registry',
                'faol'         => true,
                'mock_rejim'   => true,
                'lock_qollab'  => false,
                'unlock_qollab'=> false,
                'ogoh_qollab'  => false,
                'sinx_qollab'  => true,
                'tavsif'       => 'O\'zbekiston IMEI registri — informatsion tekshiruv',
                'sort_order'   => 2,
            ],
            [
                'kod'          => 'apple_icloud',
                'nomi'         => 'Apple iCloud Lock',
                'tur'          => 'cloud_lock',
                'faol'         => false,
                'mock_rejim'   => true,
                'lock_qollab'  => false,
                'unlock_qollab'=> false,
                'ogoh_qollab'  => false,
                'sinx_qollab'  => false,
                'tavsif'       => 'Apple iCloud — qo\'lda nazorat rejimi',
                'sort_order'   => 3,
            ],
            [
                'kod'          => 'android_mdm',
                'nomi'         => 'Android Enterprise MDM',
                'tur'          => 'mdm',
                'faol'         => false,
                'mock_rejim'   => true,
                'lock_qollab'  => true,
                'unlock_qollab'=> true,
                'ogoh_qollab'  => true,
                'sinx_qollab'  => true,
                'tavsif'       => 'Android qurilmalar uchun MDM boshqaruvi',
                'sort_order'   => 4,
            ],
            [
                'kod'          => 'custom_lock_app',
                'nomi'         => 'Maxsus Lock Ilova',
                'tur'          => 'custom_app',
                'faol'         => false,
                'mock_rejim'   => true,
                'lock_qollab'  => true,
                'unlock_qollab'=> true,
                'ogoh_qollab'  => true,
                'sinx_qollab'  => false,
                'tavsif'       => 'O\'z Android ilovamiz (Faza 3)',
                'sort_order'   => 5,
            ],
        ];

        foreach ($provayderlar as $p) {
            DB::table('qurilma_provayderlar')->updateOrInsert(
                ['kod' => $p['kod']],
                array_merge($p, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // ── 2. Samsung Knox Guard sozlamalar ─────────────────────
        $knoxId = DB::table('qurilma_provayderlar')->where('kod','samsung_knox_guard')->value('id');
        if ($knoxId) {
            $sozlamalar = [
                ['kalit'=>'api_url',    'sarlavha'=>'API URL',        'tur'=>'string',  'majburiy'=>true,  'tavsif'=>'Knox Guard API bazaviy URL'],
                ['kalit'=>'api_token',  'sarlavha'=>'API Token',      'tur'=>'secret',  'majburiy'=>true,  'tavsif'=>'Knox Guard Bearer token'],
                ['kalit'=>'company_id', 'sarlavha'=>'Company ID',     'tur'=>'string',  'majburiy'=>false, 'tavsif'=>'Kompaniya identifikatori'],
                ['kalit'=>'webhook_url','sarlavha'=>'Webhook URL',    'tur'=>'string',  'majburiy'=>false, 'tavsif'=>'Callback uchun URL'],
            ];
            foreach ($sozlamalar as $s) {
                DB::table('qurilma_provayder_sozlamalari')->updateOrInsert(
                    ['provayder_id' => $knoxId, 'kalit' => $s['kalit']],
                    array_merge($s, ['provayder_id' => $knoxId, 'qiymat' => null, 'created_at' => $now, 'updated_at' => $now])
                );
            }
        }

        // ── 3. Nazorat qoidalari (default) ───────────────────────
        $qoidalar = [
            [
                'provayder_id'    => null,
                'kechikish_kunlar'=> 1,
                'amal'            => 'ogoh_berish',
                'kanal'           => 'hammasi',
                'faol'            => false,
                'tasdiq_talab'    => false,
                'tavsif'          => '1 kun kechikganda SMS/Telegram ogohlantirish',
                'sort_order'      => 1,
            ],
            [
                'provayder_id'    => $knoxId,
                'kechikish_kunlar'=> 3,
                'amal'            => 'ogoh_berish',
                'kanal'           => 'provider',
                'faol'            => false,
                'tasdiq_talab'    => false,
                'tavsif'          => '3 kun kechikganda provider ogohlantirishi',
                'sort_order'      => 2,
            ],
            [
                'provayder_id'    => null,
                'kechikish_kunlar'=> 5,
                'amal'            => 'qolda_tekshirish',
                'kanal'           => 'hammasi',
                'faol'            => false,
                'tasdiq_talab'    => true,
                'tavsif'          => '5 kun kechikganda qo\'lda ko\'rib chiqish',
                'sort_order'      => 3,
            ],
            [
                'provayder_id'    => $knoxId,
                'kechikish_kunlar'=> 7,
                'amal'            => 'lock',
                'kanal'           => 'provider',
                'faol'            => false,
                'tasdiq_talab'    => true,
                'tavsif'          => '7 kun kechikganda bloklash (qo\'lda tasdiq kerak, auto lock O\'CHIQ)',
                'sort_order'      => 4,
            ],
        ];

        foreach ($qoidalar as $q) {
            DB::table('qurilma_qoidalar')->insert(
                array_merge($q, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // ── 4. Rozilik shabloni (asosiy) ──────────────────────────
        $shablonMatn = <<<'TXT'
QURILMA NASIYA NAZORATI BO'YICHA ROZILIK

Men, ___________________________ (FIO), quyidagilarga roziligimni bildiraman:

1. IMEI/Serial raqam: ushbu shartnoma doirasida sotilgan qurilmaning IMEI va serial raqami ma'lumotlari saqlanadi va kreditni boshqarish maqsadlarida foydalaniladi.

2. Nazorat: Ushbu qurilma nasiya shartnomasi bo'yicha berilganligi sababli, to'lov muddati o'tganda (shartnomada belgilangan shartlarga muvofiq) qurilma funksiyalari vaqtincha cheklanishi mumkin.

3. Ogohlantirish: To'lov muddati yaqinlashganda va o'tganda SMS/Telegram orqali xabardor qilinaman.

4. Blokdan chiqarish: To'lov amalga oshirilgandan so'ng qurilma funksiyalari tiklandi.

5. Shartnoma yopilishi: Barcha to'lovlar amalga oshirilgandan so'ng qurilma to'liq nazoratdan ozod qilinadi.

6. Shaxsiy ma'lumotlar: IMEI, model va boshqa qurilma ma'lumotlari faqat ushbu shartnoma va nazorat maqsadlarida ishlatiladi, uchinchi shaxslarga berilmaydi.

Sana: _______________
Imzo: _______________
TXT;

        DB::table('qurilma_rozilik_shablonlari')->updateOrInsert(
            ['kod' => 'asosiy_rozilik'],
            [
                'sarlavha'   => 'Qurilma nazorati bo\'yicha rozilik',
                'matn'       => $shablonMatn,
                'versiya'    => '1.0',
                'faol'       => true,
                'til'        => 'uz',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('Device Control seeder: ' . count($provayderlar) . ' ta provayder, ' . count($qoidalar) . ' ta qoida seed qilindi.');
        $this->command->warn('ESLATMA: Barcha qoidalar NOFAOL (faol=false). Auto lock O\'CHIQ.');
        $this->command->warn('ESLATMA: Real API tokenlarni provayder sozlamalaridan kiriting.');
    }
}
