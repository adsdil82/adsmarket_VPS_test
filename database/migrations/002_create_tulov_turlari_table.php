<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 002 — To'lov turlari jadvali
// MUHIM: ENUM ishlatilmadi — eski bazada 60+ to'lov turi matn ko'rinishida bor
// (Накд Город, UZCARD, Банк va h.k.) — bularni ENUM ga sig'dirib bo'lmaydi
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tulov_turlari', function (Blueprint $table) {
            $table->id();

            // To'lov turi nomi — eski bazadagi matn qiymatlar shu yerga keladi
            // Misol: "Накд Город", "Эркин UZCARD", "Банк", "Накд Яккатут"
            $table->string('nomi', 150);

            // Holati: faol (ishlatilmoqda) yoki nofaol (eskirgan)
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tulov_turlari');
    }
};
