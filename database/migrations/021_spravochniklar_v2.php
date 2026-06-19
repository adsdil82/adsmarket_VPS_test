<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Brendlar ───────────────────────────────────────────────
        Schema::create('brendlar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 100);
            $table->string('kod',  30)->unique()->nullable();
            $table->string('mamlakat', 60)->nullable();
            $table->string('logo_yol', 255)->nullable();
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->smallInteger('sort_order')->default(100);
            $table->timestamps();
        });

        // ── 2. Valyutalar ─────────────────────────────────────────────
        Schema::create('valyutalar', function (Blueprint $table) {
            $table->id();
            $table->string('kod',   10)->unique();
            $table->string('nomi',  80);
            $table->string('belgi', 10)->nullable();
            $table->decimal('kurs', 18, 4)->default(1.0000);
            $table->date('kurs_sana')->nullable();
            $table->boolean('asosiy')->default(false);
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->timestamps();
        });

        // ── 3. Tashkilot rekvizitlari ─────────────────────────────────
        Schema::create('tashkilot_rekvizitlari', function (Blueprint $table) {
            $table->id();
            $table->string('nomi',           250);
            $table->string('qisqa_nomi',     100)->nullable();
            $table->string('stir',           20)->nullable();
            $table->string('mfo',            9)->nullable();
            $table->string('bank_nomi',      200)->nullable();
            $table->string('hisob_raqam',    30)->nullable();
            $table->string('tranzit_hisob',  30)->nullable();
            $table->text('yuridik_manzil')->nullable();
            $table->text('haqiqiy_manzil')->nullable();
            $table->string('telefon',        30)->nullable();
            $table->string('email',          100)->nullable();
            $table->string('direktor_ism',   200)->nullable();
            $table->string('hisobchi_ism',   200)->nullable();
            $table->string('imzochi_ism',    200)->nullable();
            $table->string('imzochi_lavozim',200)->nullable();
            $table->string('logo_yol',       255)->nullable();
            $table->string('muhr_yol',       255)->nullable();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->boolean('asosiy')->default(false);
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->timestamps();
        });

        // ── 4. Shartnoma rekvizitlari ─────────────────────────────────
        Schema::create('shartnoma_rekvizitlari', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 200);
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->foreignId('tashkilot_rekvizit_id')->nullable()->constrained('tashkilot_rekvizitlari')->nullOnDelete();
            $table->string('prefiks',        20)->nullable()->comment('S-2025');
            $table->unsignedInteger('keyingi_raqam')->default(1);
            $table->string('raqam_formati',  80)->default('{PREFIX}-{RAQAM}');
            $table->string('imzochi_ism',    200)->nullable();
            $table->string('imzochi_lavozim',200)->nullable();
            $table->boolean('asosiy')->default(false);
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->timestamps();
        });

        // ── 5. Statuslar va sabablar ──────────────────────────────────
        Schema::create('statuslar_sabablar', function (Blueprint $table) {
            $table->id();
            $table->string('modul',  50)->comment('kredit, qurilma, tolov, qaytarish, umumiy');
            $table->enum('tur', ['status','sabab','holat'])->default('status');
            $table->string('kod',    50);
            $table->string('nomi',   200);
            $table->string('rang',   20)->default('secondary');
            $table->string('icon',   50)->nullable();
            $table->boolean('tizim_holati')->default(false)->comment('tizim tomonidan belgilanadi');
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->smallInteger('sort_order')->default(100);
            $table->timestamps();
            $table->unique(['modul','kod']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuslar_sabablar');
        Schema::dropIfExists('shartnoma_rekvizitlari');
        Schema::dropIfExists('tashkilot_rekvizitlari');
        Schema::dropIfExists('valyutalar');
        Schema::dropIfExists('brendlar');
    }
};
