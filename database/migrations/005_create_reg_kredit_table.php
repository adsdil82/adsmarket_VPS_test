<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 005 — Reg_kredit jadvali (Nasiya shartnomasi — asosiy jadval)
// Eski SQL: reg_kredit_mysql.sql → bu jadvalga mapping qilinadi
// MUHIM: mijozlar, filiallar, foydalanuvchilar dan keyin yaratiladi
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reg_kredit', function (Blueprint $table) {
            $table->id();

            // Import uchun — eski bazadagi id_kredit
            $table->integer('eski_id')->nullable()->index();

            // Noyob shartnoma raqami — avtomatik yaratiladi
            // Format: IST-2024-00001 (filial kodi + yil + tartib raqam)
            $table->string('shartnoma_raqam', 50)->unique();

            // Qaysi mijozga tegishli — MUHIM: FK orqali bog'lanish
            // Bu FK tufayli bitta to'lov faqat bitta shartnomaga tegishli bo'ladi
            $table->foreignId('mijoz_id')
                  ->constrained('mijozlar')
                  ->restrictOnDelete();

            // Qaysi filialda tuzilgan
            $table->foreignId('filial_id')
                  ->constrained('filiallar')
                  ->restrictOnDelete();

            // Kim tomonidan tuzilgan (xodim)
            $table->foreignId('xodim_id')
                  ->constrained('foydalanuvchilar')
                  ->restrictOnDelete();

            // ─── Moliyaviy ma'lumotlar ───────────────────────────
            // Tovarlarning umumiy qiymati
            $table->decimal('jami_summa', 15, 2)->default(0);

            // Boshlang'ich (oldindan) to'lov miqdori
            $table->decimal('boshlangich_tolov', 15, 2)->default(0);

            // Nasiya miqdori = jami_summa - boshlangich_tolov
            $table->decimal('kredit_summa', 15, 2)->default(0);

            // Hozirga qadar to'langan jami miqdor (har to'lovda yangilanadi)
            $table->decimal('tolov_qilingan', 15, 2)->default(0);

            // Qolgan qarz = kredit_summa - tolov_qilingan (har to'lovda yangilanadi)
            $table->decimal('qoldiq_qarz', 15, 2)->default(0);

            // ─── Muddatlar ───────────────────────────────────────
            // Shartnoma boshlanish sanasi
            $table->date('boshlanish_sana')->nullable();

            // Shartnoma tugash sanasi
            $table->date('tugash_sana')->nullable();

            // Oylik to'lov miqdori = kredit_summa / muddati_oy
            $table->decimal('oylik_tolov_miqdori', 15, 2)->default(0);

            // Necha oyga berilgan (1-36 oy)
            $table->unsignedTinyInteger('muddati_oy')->default(12);

            // Yillik foiz stavkasi (0 bo'lsa foizsiz)
            $table->decimal('foiz_stavka', 5, 2)->default(0.00);

            // ─── Kafil ma'lumotlari ──────────────────────────────
            $table->string('kafil_ism', 200)->nullable();
            $table->string('kafil_telefon', 50)->nullable();
            $table->text('kafil_manzil')->nullable();

            // ─── Holat ───────────────────────────────────────────
            // faol          — to'lovlar davom etmoqda
            // yopilgan      — to'liq to'lab bo'lindi
            // muddati_otgan — to'lov muddati o'tib ketgan
            // muzlatilgan   — vaqtincha to'xtatilgan
            $table->enum('holat', ['faol', 'yopilgan', 'muddati_otgan', 'muzlatilgan'])
                  ->default('faol');

            $table->text('izoh')->nullable();
            $table->timestamps();

            // Tezkor qidiruv indekslari
            $table->index(['mijoz_id', 'holat']);
            $table->index(['filial_id', 'holat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reg_kredit');
    }
};
