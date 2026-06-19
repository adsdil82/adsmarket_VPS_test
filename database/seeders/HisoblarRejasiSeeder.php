<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HisoblarRejasiSeeder extends Seeder
{
    public function run(): void
    {
        // UzR milliy buxgalteriya hisoblar rejasi (asosiy hisoblar)
        // Manba: O'zbekiston Respublikasi Moliya vazirligi buyrug'i

        $hisoblar = [
            // ── UZOQ MUDDATLI AKTIVLAR ─────────────────────────────
            ['hisob_raqam' => '0100', 'nomi' => 'Asosiy vositalar',               'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '0110', 'nomi' => 'Yer uchastkasi',                 'turi' => 'faol',          'daraja' => 2, 'ota_id' => '0100'],
            ['hisob_raqam' => '0120', 'nomi' => 'Binolar va inshootlar',          'turi' => 'faol',          'daraja' => 2, 'ota_id' => '0100'],
            ['hisob_raqam' => '0150', 'nomi' => 'Mashinalar va uskunalar',        'turi' => 'faol',          'daraja' => 2, 'ota_id' => '0100'],
            ['hisob_raqam' => '0160', 'nomi' => 'Transport vositalari',           'turi' => 'faol',          'daraja' => 2, 'ota_id' => '0100'],
            ['hisob_raqam' => '0190', 'nomi' => 'Boshqa asosiy vositalar',        'turi' => 'faol',          'daraja' => 2, 'ota_id' => '0100'],
            ['hisob_raqam' => '0200', 'nomi' => 'Nomoddiy aktivlar',              'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '0900', 'nomi' => 'Kapital qo\'yilmalar',           'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],

            // ── TOVAR-MODDIY ZAXIRALAR ─────────────────────────────
            ['hisob_raqam' => '1000', 'nomi' => 'Xom ashyo va materiallar',       'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '2800', 'nomi' => 'Tovarlar',                       'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '2810', 'nomi' => 'Savdo korxonalaridagi tovarlar', 'turi' => 'faol',          'daraja' => 2, 'ota_id' => '2800'],

            // ── DEBITORLIK ─────────────────────────────────────────
            ['hisob_raqam' => '4000', 'nomi' => 'Debitorlik qarzlari',            'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '4010', 'nomi' => 'Xaridor va buyurtmachilardan debitorlik', 'turi' => 'faol', 'daraja' => 2, 'ota_id' => '4000'],
            ['hisob_raqam' => '4110', 'nomi' => 'Berilgan bo\'naklar',            'turi' => 'faol',          'daraja' => 2, 'ota_id' => '4000'],
            ['hisob_raqam' => '4200', 'nomi' => 'Turli debitorlar',               'turi' => 'faol',          'daraja' => 2, 'ota_id' => '4000'],
            ['hisob_raqam' => '4800', 'nomi' => 'Kechiktirilgan xarajatlar',      'turi' => 'faol',          'daraja' => 2, 'ota_id' => '4000'],

            // ── PULMABLAG'LAR ──────────────────────────────────────
            ['hisob_raqam' => '5000', 'nomi' => 'Pul mablag\'lari',               'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '5010', 'nomi' => 'Kassa (so\'m)',                  'turi' => 'faol',          'daraja' => 2, 'ota_id' => '5000'],
            ['hisob_raqam' => '5020', 'nomi' => 'Kassa (valyuta)',                'turi' => 'faol',          'daraja' => 2, 'ota_id' => '5000'],
            ['hisob_raqam' => '5110', 'nomi' => 'Hisob-kitob scheti',             'turi' => 'faol',          'daraja' => 2, 'ota_id' => '5000'],
            ['hisob_raqam' => '5110-1', 'nomi' => 'Terminal 1 (HamkorBank)',      'turi' => 'faol',          'daraja' => 3, 'ota_id' => '5110'],
            ['hisob_raqam' => '5110-2', 'nomi' => 'Terminal 2 (AgroBank)',        'turi' => 'faol',          'daraja' => 3, 'ota_id' => '5110'],
            ['hisob_raqam' => '5110-3', 'nomi' => 'Bank 1 (asosiy hisob)',        'turi' => 'faol',          'daraja' => 3, 'ota_id' => '5110'],
            ['hisob_raqam' => '5210', 'nomi' => 'Valyuta scheti',                 'turi' => 'faol',          'daraja' => 2, 'ota_id' => '5000'],
            ['hisob_raqam' => '5500', 'nomi' => 'Maxsus bank schetlari',          'turi' => 'faol',          'daraja' => 2, 'ota_id' => '5000'],

            // ── KREDITORLIK (MAJBURIYATLAR) ────────────────────────
            ['hisob_raqam' => '6000', 'nomi' => 'Kreditorlik qarzlari',           'turi' => 'passiv',        'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '6010', 'nomi' => 'Ta\'minotchilar bilan hisob-kitob', 'turi' => 'passiv',    'daraja' => 2, 'ota_id' => '6000'],
            ['hisob_raqam' => '6100', 'nomi' => 'Olingan bo\'naklar (oldindan to\'lov)', 'turi' => 'passiv', 'daraja' => 2, 'ota_id' => '6000'],
            ['hisob_raqam' => '6200', 'nomi' => 'Byudjet bilan hisob-kitob',      'turi' => 'passiv',        'daraja' => 2, 'ota_id' => '6000'],
            ['hisob_raqam' => '6300', 'nomi' => 'Mehnat haqi bo\'yicha hisob-kitob', 'turi' => 'passiv',    'daraja' => 2, 'ota_id' => '6000'],
            ['hisob_raqam' => '6410', 'nomi' => 'JSHSHIR bo\'yicha hisob-kitob',  'turi' => 'passiv',        'daraja' => 2, 'ota_id' => '6000'],
            ['hisob_raqam' => '6900', 'nomi' => 'Boshqa kreditorlar',             'turi' => 'passiv',        'daraja' => 2, 'ota_id' => '6000'],
            ['hisob_raqam' => '6990', 'nomi' => 'Boshqa joriy majburiyatlar',     'turi' => 'passiv',        'daraja' => 2, 'ota_id' => '6000'],

            // ── KAPITAL ────────────────────────────────────────────
            ['hisob_raqam' => '7000', 'nomi' => 'Ustav kapital',                  'turi' => 'passiv',        'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '7300', 'nomi' => 'Taqsimlanmagan foyda (qoplanmagan zarar)', 'turi' => 'faol-passiv', 'daraja' => 1, 'ota_id' => null],

            // ── DAROMADLAR ─────────────────────────────────────────
            ['hisob_raqam' => '9000', 'nomi' => 'Daromadlar',                     'turi' => 'passiv',        'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '9010', 'nomi' => 'Tovarlar va xizmatlardan savdo tushumi', 'turi' => 'passiv', 'daraja' => 2, 'ota_id' => '9000'],
            ['hisob_raqam' => '9020', 'nomi' => 'Qaytarilgan tovarlar va chegirmalar', 'turi' => 'faol',     'daraja' => 2, 'ota_id' => '9000'],
            ['hisob_raqam' => '9060', 'nomi' => 'Boshqa operatsion daromadlar',   'turi' => 'passiv',        'daraja' => 2, 'ota_id' => '9000'],

            // ── XARAJATLAR ─────────────────────────────────────────
            ['hisob_raqam' => '9400', 'nomi' => 'Davr xarajatlari',               'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '9410', 'nomi' => 'Xodimlarga haq to\'lash xarajatlari', 'turi' => 'faol',    'daraja' => 2, 'ota_id' => '9400'],
            ['hisob_raqam' => '9420', 'nomi' => 'Amortizatsiya xarajatlari',      'turi' => 'faol',          'daraja' => 2, 'ota_id' => '9400'],
            ['hisob_raqam' => '9430', 'nomi' => 'Boshqa operatsion xarajatlar (spisat)', 'turi' => 'faol',  'daraja' => 2, 'ota_id' => '9400'],
            ['hisob_raqam' => '9500', 'nomi' => 'Moliyaviy xarajatlar',           'turi' => 'faol',          'daraja' => 1, 'ota_id' => null],
            ['hisob_raqam' => '9900', 'nomi' => 'Sof foyda (zarar)',              'turi' => 'faol-passiv',   'daraja' => 1, 'ota_id' => null],
        ];

        // ota_id ni hisob_raqam bo'yicha ID ga aylantirish
        $idMap = [];
        $now   = now();

        foreach ($hisoblar as $row) {
            $otaId = null;
            if ($row['ota_id'] !== null) {
                $otaId = $idMap[$row['ota_id']] ?? null;
            }

            $id = DB::table('hisoblar_rejasi')->insertGetId([
                'hisob_raqam' => $row['hisob_raqam'],
                'nomi'        => $row['nomi'],
                'turi'        => $row['turi'],
                'daraja'      => $row['daraja'],
                'ota_id'      => $otaId,
                'holat'       => 'faol',
                'izoh'        => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            $idMap[$row['hisob_raqam']] = $id;
        }

        // ── YANGI TO'LOV TURLARI ──────────────────────────────────
        $tulovlar = [
            [
                'kod'       => 'KASSA',
                'nomi'      => 'Kassa (naqd pul)',
                'kategoriya'=> 'kassa',
                'debet'     => '5010',  // Kassa
                'kredit'    => '4010',  // Debitorlik
                'izoh'      => 'Naqd pul orqali to\'lov',
            ],
            [
                'kod'       => 'TERMINAL1',
                'nomi'      => 'Terminal 1 (HamkorBank)',
                'kategoriya'=> 'terminal',
                'debet'     => '5110-1',
                'kredit'    => '4010',
                'izoh'      => 'HamkorBank POS-terminali',
            ],
            [
                'kod'       => 'TERMINAL2',
                'nomi'      => 'Terminal 2 (AgroBank)',
                'kategoriya'=> 'terminal',
                'debet'     => '5110-2',
                'kredit'    => '4010',
                'izoh'      => 'AgroBank POS-terminali',
            ],
            [
                'kod'       => 'BANK1',
                'nomi'      => 'Bank (naqd pulsiz)',
                'kategoriya'=> 'bank',
                'debet'     => '5110-3',
                'kredit'    => '4010',
                'izoh'      => 'Bank o\'tkazmasi (asosiy hisob)',
            ],
            [
                'kod'       => 'SPISAT',
                'nomi'      => 'Spisat (hisobdan chiqarish)',
                'kategoriya'=> 'boshqa',
                'debet'     => '9430',  // Boshqa operatsion xarajatlar
                'kredit'    => '4010',
                'izoh'      => 'Umidsiz qarzdorlikni hisobdan chiqarish',
            ],
            [
                'kod'       => 'TOVAR_QAYTDI',
                'nomi'      => 'Tovar qaytdi',
                'kategoriya'=> 'boshqa',
                'debet'     => '2810',  // Tovarlar
                'kredit'    => '4010',
                'izoh'      => 'Mijoz tovarni qaytarganda debitorlik kamaytirish',
            ],
        ];

        foreach ($tulovlar as $t) {
            DB::table('yangi_tulov_turlari')->insert([
                'kod'            => $t['kod'],
                'nomi'           => $t['nomi'],
                'kategoriya'     => $t['kategoriya'],
                'debet_hisob_id' => $idMap[$t['debet']] ?? null,
                'kredit_hisob_id'=> $idMap[$t['kredit']] ?? null,
                'holat'          => 'faol',
                'izoh'           => $t['izoh'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
