<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 006 — Tovarlar jadvali (Shartnomadagi mahsulotlar ro'yxati)
// Eski SQL: ch_kt_tv_mysql.sql → bu jadvalga mapping qilinadi
// MUHIM: reg_kredit dan keyin yaratiladi (reg_kredit_id FK uchun)
// CASCADE DELETE — shartnoma o'chsa tovarlar ham o'chadi (ma'lumot yo'qolishi mumkin emas shuning uchun reg_kredit RESTRICT)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tovarlar', function (Blueprint $table) {
            $table->id();

            // Import uchun — eski bazadagi id
            $table->integer('eski_id')->nullable()->index();

            // Qaysi shartnomaga tegishli tovar
            // CASCADE: shartnoma o'chsa tovarlar ham o'chadi
            $table->foreignId('reg_kredit_id')
                  ->constrained('reg_kredit')
                  ->cascadeOnDelete();

            // ─── Tovar ma'lumotlari ───────────────────────────────
            // Eski: tovar_nomi — tovar nomi (text ko'rinishda, ID emas)
            $table->string('nomi', 300);

            // Eski: soni — nechta tovar sotilgan
            $table->unsignedSmallInteger('soni')->default(1);

            // Eski: narx — birlik narxi
            $table->decimal('narx', 15, 2)->default(0);

            // Eski: jami_narx = soni * narx
            $table->decimal('jami_narx', 15, 2)->default(0);

            // Eski: barkod — tovar barkodi (ixtiyoriy)
            $table->string('barkod', 100)->nullable();

            // Qo'shimcha izoh
            $table->text('izoh')->nullable();

            $table->timestamps();

            // Shartnoma bo'yicha tovarlarni tezkor qidirish
            $table->index('reg_kredit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tovarlar');
    }
};
