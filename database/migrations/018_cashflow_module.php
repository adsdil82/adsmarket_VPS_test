<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Pul kategoriyalari (ierarxik) ──────────────────────
        Schema::create('pul_kategoriyalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ota_id')->nullable()->constrained('pul_kategoriyalar')->nullOnDelete();
            $table->enum('yunalish', ['kirim', 'chiqim']);
            $table->string('kod', 20)->unique();              // CF-1100, CF-2110 ...
            $table->string('nomi', 200);
            $table->foreignId('hisob_id')->nullable()->constrained('hisoblar_rejasi')->nullOnDelete();
            $table->boolean('avtomatik')->default(false);     // Observer orqali to'ldiriladi
            $table->string('rang', 20)->default('gray');      // green, red, blue...
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->timestamps();
        });

        // ── 2. Pul oqimlari (asosiy cashflow jadvali) ─────────────
        Schema::create('pul_oqimlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->foreignId('kassa_id')->nullable()->constrained('kassalar')->nullOnDelete();
            $table->foreignId('kategoriya_id')->nullable()->constrained('pul_kategoriyalar')->nullOnDelete();
            $table->foreignId('hisob_id')->nullable()->constrained('hisoblar_rejasi')->nullOnDelete();
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();

            $table->enum('yunalish', ['kirim', 'chiqim']);
            $table->date('sana');
            $table->decimal('summa', 18, 2);                  // Har doim musbat son
            $table->string('izoh', 500)->nullable();

            // Manba hujjat (polymorphic)
            $table->string('manba_tur', 30)->nullable();      // 'tulov','oldindan_tulov','harajat','manual'
            $table->unsignedBigInteger('manba_id')->nullable();
            $table->index(['manba_tur', 'manba_id'], 'idx_manba');

            $table->enum('holat', ['qoralama', 'tasdiqlangan', 'bekor'])->default('tasdiqlangan');
            $table->foreignId('tasdiqlagan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();

            // Migration uchun: eski harajatlar.id
            $table->unsignedBigInteger('eski_harajat_id')->nullable()->index();

            $table->timestamps();
            $table->index(['sana', 'yunalish']);
            $table->index(['filial_id', 'sana']);
            $table->index(['kategoriya_id', 'sana']);
            $table->index(['kassa_id', 'sana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pul_oqimlari');
        Schema::dropIfExists('pul_kategoriyalar');
    }
};
