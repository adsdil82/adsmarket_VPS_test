<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Balans hisoboti — P&L'dan farqli o'laroq davr uchun emas, MA'LUM
        // SANAGA (nuqtaga) hisoblanadi. Shuning uchun qiymatlar oy/yil emas,
        // "sana" bo'yicha saqlanadi.
        Schema::create('bl_bolimlar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 150);       // Aktivlar / Majburiyatlar / Kapital
            $table->enum('tur', ['aktiv', 'majburiyat', 'kapital']);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('bl_qatorlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bolim_id')->constrained('bl_bolimlar')->cascadeOnDelete();
            $table->string('nomi', 200);
            // avtomat_naqd_pul         -> pul_oqimlari kumulyativ balansi (sana <= tanlangan sana)
            // avtomat_ombor_qiymati    -> ombor_qoldiqlar * tan_narx (FAQAT joriy holat, tarix yo'q)
            // avtomat_nasiya_debitorlik-> reg_kredit.qoldiq_qarz (FAQAT joriy holat)
            // avtomat_taminotchi_qarz  -> taminot_kirimlar - taminotchi_tulovlar (sana <= tanlangan sana)
            // avtomat_jamgarilgan_foyda-> P&L barcha davr sof daromadlari yig'indisi (sana <= tanlangan sana)
            // qolda                    -> bl_qiymatlari jadvalidan qo'lda kiritilgan qiymat
            $table->enum('hisoblash_turi', [
                'avtomat_naqd_pul', 'avtomat_ombor_qiymati', 'avtomat_nasiya_debitorlik',
                'avtomat_taminotchi_qarz', 'avtomat_jamgarilgan_foyda', 'qolda',
            ])->default('qolda');
            $table->boolean('joriy_holat_faqat')->default(false); // true bo'lsa tarixiy sanalar uchun ham "bugungi" qiymat ko'rsatiladi
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->timestamps();
        });

        Schema::create('bl_qiymatlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qator_id')->constrained('bl_qatorlari')->cascadeOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->cascadeOnDelete();
            $table->date('sana');
            $table->decimal('summa', 18, 2)->default(0);
            $table->string('izoh', 300)->nullable();
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();

            $table->unique(['qator_id', 'filial_id', 'sana'], 'bl_qiymat_unique');
        });

        // ── Bo'limlar va qatorlarni urug'lash ────────────────────────
        $bolimlar = [
            ['nomi' => 'Aktivlar',       'tur' => 'aktiv',       'sort_order' => 1],
            ['nomi' => 'Majburiyatlar',  'tur' => 'majburiyat',  'sort_order' => 2],
            ['nomi' => 'Kapital',        'tur' => 'kapital',     'sort_order' => 3],
        ];
        $bolimIds = [];
        foreach ($bolimlar as $b) {
            $bolimIds[$b['nomi']] = DB::table('bl_bolimlar')->insertGetId(array_merge($b, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        $qatorlar = [
            // ── Aktivlar ──
            ['Aktivlar', "Naqd pul va bank (kassa qoldig'i)", 'avtomat_naqd_pul', false, 1],
            ['Aktivlar', "Ombordagi tovar qoldig'i (tan narxda)", 'avtomat_ombor_qiymati', true, 2],
            ['Aktivlar', "Nasiya mijozlardan debitorlik qarzi", 'avtomat_nasiya_debitorlik', true, 3],
            ['Aktivlar', "Asosiy vositalar (jihoz, mebel, transport)", 'qolda', false, 4],
            ['Aktivlar', "Boshqa aylanma aktivlar", 'qolda', false, 5],

            // ── Majburiyatlar ──
            ['Majburiyatlar', "Ta'minotchilarga kreditorlik qarzi", 'avtomat_taminotchi_qarz', false, 1],
            ['Majburiyatlar', "Bank krediti (qoldiq)", 'qolda', false, 2],
            ['Majburiyatlar', "Xodimlarga to'lanmagan ish haqi", 'qolda', false, 3],
            ['Majburiyatlar', "Boshqa qisqa muddatli majburiyatlar", 'qolda', false, 4],

            // ── Kapital ──
            ['Kapital', "Ustav kapitali (boshlang'ich investitsiya)", 'qolda', false, 1],
            ['Kapital', "Jamg'arilgan foyda (barcha davrlar)", 'avtomat_jamgarilgan_foyda', false, 2],
            ['Kapital', "Egalar tomonidan qo'shimcha kiritilgan mablag'", 'qolda', false, 3],
        ];

        foreach ($qatorlar as [$bolimNomi, $nomi, $hisoblashTuri, $joriyFaqat, $sort]) {
            DB::table('bl_qatorlari')->insert([
                'bolim_id'          => $bolimIds[$bolimNomi],
                'nomi'              => $nomi,
                'hisoblash_turi'    => $hisoblashTuri,
                'joriy_holat_faqat' => $joriyFaqat,
                'sort_order'        => $sort,
                'holat'             => 'faol',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bl_qiymatlari');
        Schema::dropIfExists('bl_qatorlari');
        Schema::dropIfExists('bl_bolimlar');
    }
};
