<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Birliklar ─────────────────────────────────────────────
        Schema::create('birliklar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 80);
            $table->string('qisqa_nomi', 20)->nullable();
            $table->string('kod', 20)->unique()->nullable();
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->smallInteger('sort_order')->default(100);
            $table->timestamps();
        });

        // ── 2. Harajat turlari ────────────────────────────────────────
        Schema::create('harajat_turlari', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 150);
            $table->string('kod', 30)->unique()->nullable();
            $table->foreignId('ota_id')->nullable()->constrained('harajat_turlari')->nullOnDelete();
            $table->string('rang', 20)->default('secondary');
            $table->string('icon', 50)->nullable();
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->smallInteger('sort_order')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harajat_turlari');
        Schema::dropIfExists('birliklar');
    }
};
