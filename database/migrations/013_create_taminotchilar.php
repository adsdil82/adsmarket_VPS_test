<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Ta'minotchilar (Suppliers) ──────────────────────────
        Schema::create('taminotchilar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 200);
            $table->string('kontakt_shaxs', 150)->nullable();
            $table->string('telefon', 100)->nullable();
            $table->string('telefon2', 100)->nullable();
            $table->string('manzil', 300)->nullable();
            $table->string('inn', 30)->nullable()->comment('INN raqami');
            $table->string('bank_hisob', 50)->nullable()->comment('Hisob raqami');
            $table->string('bank_nomi', 200)->nullable();
            $table->string('mfo', 20)->nullable();
            $table->text('izoh')->nullable();
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->timestamps();
        });

        // ── 2. Ta'minotchilardan kirim (supply receipts) ─────────────
        Schema::create('taminot_kirimlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taminotchi_id')->constrained('taminotchilar')->cascadeOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->string('hujjat_raqam', 50)->nullable()->comment('Schyot-faktura raqami');
            $table->date('kirim_sana');
            $table->decimal('jami_summa', 15, 2)->default(0);
            $table->decimal('tolangan', 15, 2)->default(0);
            $table->decimal('qoldiq', 15, 2)->default(0)->comment('Hali tolanmagan qism');
            $table->enum('holat', ['kutilmoqda', 'qisman', 'toliq'])->default('kutilmoqda');
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        // ── 3. Kirim tarkibi (mahsulotlar) ────────────────────────
        Schema::create('taminot_kirim_qatorlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kirim_id')->constrained('taminot_kirimlar')->cascadeOnDelete();
            $table->foreignId('tovar_id')->nullable()->constrained('tovar_katalog')->nullOnDelete();
            $table->string('nomi', 250);
            $table->decimal('miqdor', 10, 3)->default(1);
            $table->string('birlik', 20)->default('dona');
            $table->decimal('narx', 15, 2)->default(0);
            $table->decimal('jami', 15, 2)->default(0);
            $table->timestamps();
        });

        // ── 4. Ta'minotchilarga to'lovlar ─────────────────────────
        Schema::create('taminotchi_tulovlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taminotchi_id')->constrained('taminotchilar')->cascadeOnDelete();
            $table->foreignId('kirim_id')->nullable()->constrained('taminot_kirimlar')->nullOnDelete();
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->decimal('summa', 15, 2);
            $table->date('tolov_sana');
            $table->string('tolov_turi', 50)->default('naqd')
                  ->comment('naqd, plastik, bank, offset');
            $table->string('hujjat_raqam', 50)->nullable();
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        // ── 5. Omborchi roli uchun foydalanuvchilar ─────────────
        // Rol enum ni kengaytirish
        \DB::statement("ALTER TABLE foydalanuvchilar MODIFY COLUMN rol
            ENUM('admin','menejer','kassir','hisobchi','omborchi','auditor')
            NOT NULL DEFAULT 'hisobchi'");
    }

    public function down(): void
    {
        Schema::dropIfExists('taminotchi_tulovlar');
        Schema::dropIfExists('taminot_kirim_qatorlar');
        Schema::dropIfExists('taminot_kirimlar');
        Schema::dropIfExists('taminotchilar');
    }
};
