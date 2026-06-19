<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 011 — Shartnoma versiyalar jadvali (Shartnoma o'zgartirish tarixi)
// MUHIM: Bu jadval audits jadvalidan farqli — faqat reg_kredit o'zgarishlarining
// to'liq "snapshot" ini saqlaydi. Har qanday o'zgarishda yangi versiya yaratiladi.
// Maqsad: shartnomaning avvalgi holatini to'liq tiklash imkoniyati
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shartnoma_versiyalar', function (Blueprint $table) {
            $table->id();

            // Qaysi shartnomaning versiyasi
            $table->foreignId('reg_kredit_id')
                  ->constrained('reg_kredit')
                  ->cascadeOnDelete();

            // Versiya raqami (1, 2, 3, ...)
            $table->unsignedSmallInteger('versiya_raqam')->default(1);

            // Kim o'zgartirdi
            $table->foreignId('xodim_id')
                  ->constrained('foydalanuvchilar')
                  ->restrictOnDelete();

            // O'zgarish sababi/izohi
            $table->string('sabab', 500)->nullable();

            // ─── Shartnomaning to'liq snapshot'i (JSON) ──────────
            // Shartnomaning o'zgarishdan oldingi to'liq holati
            $table->json('eski_holat')->nullable();

            // Shartnomaning o'zgarishdan keyingi to'liq holati
            $table->json('yangi_holat');

            // O'zgargan maydonlar ro'yxati (qisqa ko'rinish uchun)
            // Misol: ["kredit_summa", "muddati_oy", "tugash_sana"]
            $table->json('ozgargan_maydonlar')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Bitta shartnomaning versiyalari
            $table->unique(['reg_kredit_id', 'versiya_raqam']);

            // Versiya tarixini tezkor olish
            $table->index('reg_kredit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shartnoma_versiyalar');
    }
};
