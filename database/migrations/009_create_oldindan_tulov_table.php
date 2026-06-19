<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 009 — Oldindan to'lovlar jadvali (Boshlang'ich/avans to'lovlar)
// Eski SQL: tulov_oldindan_KT_mysql.sql → bu jadvalga mapping qilinadi
// MUHIM: Shartnoma tuzilgan paytdagi boshlang'ich to'lov (down payment)
// Oddiy to'lovlardan farqi: grafik bilan bog'liq emas, shartnoma boshida to'lanadi
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oldindan_tulov', function (Blueprint $table) {
            $table->id();

            // Import uchun — eski bazadagi id
            $table->integer('eski_id')->nullable()->index();

            // Qaysi shartnomaga tegishli oldindan to'lov
            // RESTRICT: to'lov bo'lsa shartnomani o'chirib bo'lmaydi
            $table->foreignId('reg_kredit_id')
                  ->constrained('reg_kredit')
                  ->restrictOnDelete();

            // Kim to'lovni qabul qildi
            $table->foreignId('xodim_id')
                  ->constrained('foydalanuvchilar')
                  ->restrictOnDelete();

            // To'lov turi
            $table->foreignId('tulov_turi_id')
                  ->constrained('tulov_turlari')
                  ->restrictOnDelete();

            // ─── To'lov miqdori ───────────────────────────────────
            // Boshlang'ich to'lov summasi
            $table->decimal('summa', 15, 2);

            // To'lov sanasi (odatda shartnoma sanasi bilan bir xil)
            $table->date('tolov_sana');

            // To'lov qabul qilingan aniq vaqt
            $table->timestamp('qabul_vaqt')->useCurrent();

            // ─── Qo'shimcha ───────────────────────────────────────
            // Kvitansiya raqami
            $table->string('kvitansiya_raqam', 100)->nullable();

            // Izoh
            $table->text('izoh')->nullable();

            $table->timestamps();

            // Indeks
            $table->index('reg_kredit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oldindan_tulov');
    }
};
