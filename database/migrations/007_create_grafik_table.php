<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 007 — Grafik jadvali (Oylik to'lov jadvali — LONG/vertikal format)
// Eski SQL: grafik_mysql.sql → bu jadvalga mapping qilinadi
// MUHIM: Eski bazada WIDE format (12 oy = 12 ustun) — bu yerda LONG format (1 qator = 1 oy)
// LONG formatda: har bir oylik to'lov alohida qatorda saqlanadi
// NULL to'lovlar ham saqlanadi (to'lov qilinmagan oylar)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grafik', function (Blueprint $table) {
            $table->id();

            // Import uchun — eski bazadagi grafik_kod
            $table->integer('eski_id')->nullable()->index();

            // Qaysi shartnomaga tegishli to'lov grafigi
            // CASCADE: shartnoma o'chsa grafik ham o'chadi
            $table->foreignId('reg_kredit_id')
                  ->constrained('reg_kredit')
                  ->cascadeOnDelete();

            // ─── To'lov ma'lumotlari ──────────────────────────────
            // Oylik tartib raqami: 1 = birinchi oy, 2 = ikkinchi oy, ... 12 = o'n ikkinchi oy
            // Eski bazada: 1_oy → 12_oy ustunlar → bu yerda oylik_tartib maydon
            $table->unsignedTinyInteger('oylik_tartib'); // 1-36

            // To'lov to'lanishi kerak bo'lgan sana
            // Eski: tolov_sana_N → bu maydon
            $table->date('tolov_sana')->nullable();

            // Rejalashtirilgan to'lov miqdori
            // Eski: tolov_summa_N → bu maydon
            // NULL = bu oy uchun to'lov rejalashtirilmagan
            $table->decimal('tolov_summa', 15, 2)->nullable();

            // Qolgan qarz (ushbu oydan keyin)
            // Eski: qoldiq_suma_N → bu maydon
            $table->decimal('qoldiq_suma', 15, 2)->nullable();

            // ─── To'lov holati ────────────────────────────────────
            // tolanmagan  — hali to'lanmagan (standart)
            // tolangan    — to'liq to'langan
            // qisman      — qisman to'langan
            // muddati_otgan — muddati o'tib ketgan, to'lanmagan
            $table->enum('holat', ['tolanmagan', 'tolangan', 'qisman', 'muddati_otgan'])
                  ->default('tolanmagan');

            // Haqiqatda to'langan miqdor (to'lov paytida yangilanadi)
            $table->decimal('tolangan_summa', 15, 2)->default(0);

            // To'langan sana (to'lov qabul qilinganda)
            $table->date('tolangan_sana')->nullable();

            $table->timestamps();

            // Bitta shartnomaning to'lov grafigi — (reg_kredit_id + oylik_tartib) kombinatsiyasi UNIQUE
            $table->unique(['reg_kredit_id', 'oylik_tartib']);

            // Tezkor qidiruv indekslari
            $table->index(['reg_kredit_id', 'holat']);
            $table->index('tolov_sana'); // Kechikkan to'lovlarni topish uchun
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grafik');
    }
};
