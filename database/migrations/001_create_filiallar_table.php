<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 001 — Filiallar jadvali
// Barcha boshqa jadvallar shu jadvalga bog'lanadi, shuning uchun BIRINCHI yaratiladi
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filiallar', function (Blueprint $table) {
            // Asosiy kalit — Import paytida ID 1-5 bo'lishi uchun seeder aniq beradi
            $table->id();

            // Filial nomi (masalan: "Istiqlol filiali")
            $table->string('nomi', 150);

            // Filial qisqa kodi — shartnoma raqami uchun ishlatiladi
            // Misol: IST, MIN, NAV, TUR, YAK
            $table->string('kod', 20)->unique();

            // Filial manzili
            $table->text('manzil')->nullable();

            // Filial telefoni
            $table->string('telefon', 20)->nullable();

            // Holati: faol yoki nofaol
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filiallar');
    }
};
