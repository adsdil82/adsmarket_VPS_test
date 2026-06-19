<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 004 — Mijozlar jadvali
// Eski SQL: mijoz_mysql.sql → bu jadvalga mapping qilinadi
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mijozlar', function (Blueprint $table) {
            $table->id();

            // Import uchun — eski bazadagi id_mj qiymati saqlanadi
            // Keyinchalik reg_kredit ni bog'lashda ishlatiladi
            $table->integer('eski_id')->nullable()->index();

            // Qaysi filialga tegishli mijoz
            $table->foreignId('filial_id')
                  ->constrained('filiallar')
                  ->restrictOnDelete();

            // Eski: Familiya_mj
            $table->string('familiya', 100);

            // Eski: Ismi_mj
            $table->string('ism', 100);

            // Eski: Otaismi_mj
            $table->string('otasining_ismi', 100)->nullable();

            // Eski: tel_mj — telefon raqami
            $table->string('telefon', 50)->index();

            // Eski: ser_mj — passport seriyasi (AA, AB, ...)
            $table->string('passport_seriya', 10)->nullable();

            // Eski: nomer_mj — passport raqami
            $table->string('passport_raqam', 20)->nullable()->index();

            // Eski: adres_mj — yashash manzili
            $table->text('manzil')->nullable();

            // Eski: tugilganyil — tug'ilgan sanasi
            $table->date('tug_sana')->nullable();

            // Eski: Ish_joyi_mj — ish joyi
            $table->string('ish_joyi', 200)->nullable();

            // Eski: lavozimi_mj — lavozimi
            $table->string('lavozimi', 200)->nullable();

            // Qo'shimcha izoh
            $table->text('izoh')->nullable();

            // Mijoz holati
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');

            $table->timestamps();

            // Kombinatsiyalangan index — tezkor qidiruv uchun
            $table->index(['filial_id', 'holat']);
            $table->index(['passport_seriya', 'passport_raqam']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mijozlar');
    }
};
