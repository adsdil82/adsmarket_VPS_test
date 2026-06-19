<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 008 — Tulovlar jadvali (Haqiqatda qabul qilingan to'lovlar)
// Eski SQL: tulov_kt_mysql.sql → bu jadvalga mapping qilinadi
// MUHIM: tulov_turlari dan keyin, reg_kredit dan keyin yaratiladi
// RESTRICT DELETE — tulov bo'lsa shartnoma o'chirib bo'lmaydi (moliyaviy xavfsizlik)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tulovlar', function (Blueprint $table) {
            $table->id();

            // Import uchun — eski bazadagi id
            $table->integer('eski_id')->nullable()->index();

            // Qaysi shartnomaga tegishli to'lov
            // RESTRICT: to'lov bo'lsa shartnomani o'chirib bo'lmaydi
            $table->foreignId('reg_kredit_id')
                  ->constrained('reg_kredit')
                  ->restrictOnDelete();

            // Qaysi grafik qatoriga tegishli (ixtiyoriy — oldindan to'lovlar bog'lanmasligi mumkin)
            $table->foreignId('grafik_id')
                  ->nullable()
                  ->constrained('grafik')
                  ->nullOnDelete();

            // Kim to'lovni qabul qildi (kassir/menejer)
            $table->foreignId('xodim_id')
                  ->constrained('foydalanuvchilar')
                  ->restrictOnDelete();

            // To'lov turi (UZCARD, CLICK, Naxt, Bank va h.k.)
            // tulov_turlari jadvalidagi yozuvga FK
            $table->foreignId('tulov_turi_id')
                  ->constrained('tulov_turlari')
                  ->restrictOnDelete();

            // ─── To'lov miqdori ───────────────────────────────────
            // To'langan summa
            $table->decimal('summa', 15, 2);

            // To'lov sanasi
            $table->date('tolov_sana');

            // To'lov qabul qilingan vaqt (aniq timestamp)
            $table->timestamp('qabul_vaqt')->useCurrent();

            // ─── Qo'shimcha ma'lumot ──────────────────────────────
            // Kvitansiya/chek raqami (ixtiyoriy)
            $table->string('kvitansiya_raqam', 100)->nullable();

            // Izoh yoki eslatma
            $table->text('izoh')->nullable();

            $table->timestamps();

            // Shartnoma bo'yicha barcha to'lovlarni tezkor olish
            $table->index(['reg_kredit_id', 'tolov_sana']);
            $table->index('tolov_sana'); // Kunlik to'lovlar hisoboti uchun
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tulovlar');
    }
};
