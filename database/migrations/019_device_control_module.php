<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. tovar_guruhlar: IMEI talab belgisi ──────────────────
        Schema::table('tovar_guruhlar', function (Blueprint $table) {
            $table->boolean('imei_talab')->default(false)->after('holat')
                  ->comment('Ushbu guruh tovarlari uchun IMEI kiritish majburiy');
        });

        // ── 2. Qurilmalar (asosiy ro'yxat) ─────────────────────────
        Schema::create('qurilmalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tovar_katalog_id')->nullable()->constrained('tovar_katalog')->nullOnDelete();
            $table->foreignId('reg_kredit_id')->nullable()->constrained('reg_kredit')->nullOnDelete();
            $table->unsignedBigInteger('tovarlar_id')->nullable()->index();    // tovarlar (shartnoma qatori)
            $table->foreignId('mijoz_id')->nullable()->constrained('mijozlar')->nullOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->string('brend', 100)->nullable();
            $table->string('model_nomi', 200);
            $table->string('rang', 60)->nullable();
            $table->string('xotira', 50)->nullable();         // 128GB, 256GB
            $table->string('imei1', 15)->nullable()->unique();
            $table->string('imei2', 15)->nullable()->unique();
            $table->string('imei3', 15)->nullable()->unique();
            $table->string('imei4', 15)->nullable()->unique();
            $table->string('serial_raqam', 100)->nullable()->index();
            $table->enum('holat', [
                'in_stock','reserved','sold','active','warning',
                'locked','unlock_pending','released','returned','lost','failed'
            ])->default('in_stock')->index();
            $table->date('qoshilgan_sana')->nullable();
            $table->date('sotilgan_sana')->nullable();
            $table->date('qaytarilgan_sana')->nullable();
            $table->text('izoh')->nullable();
            $table->unsignedBigInteger('yaratdi_id')->nullable()->index();
            $table->unsignedBigInteger('yangiladi_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reg_kredit_id', 'holat']);
            $table->index(['mijoz_id', 'holat']);
            $table->index(['filial_id', 'holat']);
        });

        // ── 3. Qurilma provayderlar ─────────────────────────────────
        Schema::create('qurilma_provayderlar', function (Blueprint $table) {
            $table->id();
            $table->string('kod', 50)->unique();              // samsung_knox_guard, uzimei ...
            $table->string('nomi', 200);
            $table->enum('tur', ['mdm','imei_registry','cloud_lock','custom_app','manual'])->default('manual');
            $table->boolean('faol')->default(false);
            $table->boolean('mock_rejim')->default(true);     // real API'ga ulanmasdan test
            $table->boolean('lock_qollab')->default(false);
            $table->boolean('unlock_qollab')->default(false);
            $table->boolean('ogoh_qollab')->default(false);
            $table->boolean('sinx_qollab')->default(false);
            $table->text('tavsif')->nullable();
            $table->smallInteger('sort_order')->default(10);
            $table->timestamps();
        });

        // ── 4. Provayder sozlamalari (kalit-qiymat) ─────────────────
        Schema::create('qurilma_provayder_sozlamalari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provayder_id')->constrained('qurilma_provayderlar')->cascadeOnDelete();
            $table->string('kalit', 100);
            $table->text('qiymat')->nullable();               // DB::encrypt ishlatiladi
            $table->enum('tur', ['string','boolean','integer','json','secret'])->default('string');
            $table->string('sarlavha', 200);
            $table->text('tavsif')->nullable();
            $table->boolean('majburiy')->default(false);
            $table->timestamps();
            $table->unique(['provayder_id','kalit']);
        });

        // ── 5. Qurilma–provayder ulanish ────────────────────────────
        Schema::create('qurilma_provayder_ulanishlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurilma_id')->constrained('qurilmalar')->cascadeOnDelete();
            $table->foreignId('provayder_id')->constrained('qurilma_provayderlar')->cascadeOnDelete();
            $table->string('tashqi_id', 200)->nullable();     // Knox device ID, etc.
            $table->enum('holat', ['faol','nofaol','xato'])->default('nofaol');
            $table->timestamp('oxirgi_sinx')->nullable();
            $table->text('oxirgi_xato')->nullable();
            $table->timestamps();
            $table->unique(['qurilma_id','provayder_id']);
            $table->index(['provayder_id','holat']);
        });

        // ── 6. Nazorat qoidalari ─────────────────────────────────────
        Schema::create('qurilma_qoidalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provayder_id')->nullable()->constrained('qurilma_provayderlar')->nullOnDelete();
            $table->smallInteger('kechikish_kunlar')->unsigned()->default(7)
                  ->comment('Necha kun kechiksa trigger bo\'lsin');
            $table->enum('amal', ['ogoh_berish','lock','unlock','qolda_tekshirish'])->default('ogoh_berish');
            $table->enum('kanal', ['sms','telegram','provider','hammasi'])->default('hammasi');
            $table->boolean('faol')->default(false);
            $table->boolean('tasdiq_talab')->default(true)
                  ->comment('Bajarishdan oldin qo\'lda tasdiqlash kerakmi');
            $table->string('tavsif', 300)->nullable();
            $table->smallInteger('sort_order')->default(10);
            $table->timestamps();
        });

        // ── 7. Rozilik shablonlari ───────────────────────────────────
        Schema::create('qurilma_rozilik_shablonlari', function (Blueprint $table) {
            $table->id();
            $table->string('kod', 50)->unique();
            $table->string('sarlavha', 200);
            $table->text('matn');
            $table->string('versiya', 20)->default('1.0');
            $table->boolean('faol')->default(true);
            $table->string('til', 10)->default('uz');
            $table->timestamps();
        });

        // ── 8. Mijoz roziliklari ─────────────────────────────────────
        Schema::create('qurilma_roziliklar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurilma_id')->constrained('qurilmalar')->cascadeOnDelete();
            $table->foreignId('reg_kredit_id')->nullable()->constrained('reg_kredit')->nullOnDelete();
            $table->foreignId('mijoz_id')->nullable()->constrained('mijozlar')->nullOnDelete();
            $table->foreignId('shablon_id')->nullable()->constrained('qurilma_rozilik_shablonlari')->nullOnDelete();
            $table->text('matn');                              // imzolangan vaqtdagi matn
            $table->timestamp('imzolangan_sana')->nullable();
            $table->string('ip_manzil', 45)->nullable();
            $table->string('telefon', 20)->nullable();
            $table->enum('kanal', ['qolda','sms_kod','e_imzo','ilovada'])->default('qolda');
            $table->enum('holat', ['kutilmoqda','imzolangan','rad_etilgan'])->default('kutilmoqda');
            $table->timestamps();
            $table->index(['qurilma_id','holat']);
        });

        // ── 9. Qo'lda tasdiqlash navbati ────────────────────────────
        Schema::create('qurilma_tasdiqlashlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurilma_id')->constrained('qurilmalar')->cascadeOnDelete();
            $table->enum('amal', ['lock','unlock','release'])->default('lock');
            $table->text('sabab');
            $table->foreignId('soragan_id')->constrained('foydalanuvchilar');
            $table->foreignId('tasdiq_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->enum('holat', ['kutilmoqda','tasdiqlangan','rad_etilgan'])->default('kutilmoqda');
            $table->text('tasdiq_izoh')->nullable();
            $table->timestamp('muddati')->nullable();
            $table->timestamps();
            $table->index(['holat','muddati']);
        });

        // ── 10. Nazorat loglari ──────────────────────────────────────
        Schema::create('qurilma_loglar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qurilma_id')->constrained('qurilmalar')->cascadeOnDelete();
            $table->foreignId('provayder_id')->nullable()->constrained('qurilma_provayderlar')->nullOnDelete();
            $table->unsignedBigInteger('reg_kredit_id')->nullable()->index();
            $table->string('amal', 50);                       // lock, unlock, warning, sync, created ...
            $table->enum('holat', ['muvaffaqiyat','xato','kutilmoqda'])->default('muvaffaqiyat');
            $table->text('sabab')->nullable();
            $table->text('javob')->nullable();                // provider API response
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->string('ip_manzil', 45)->nullable();
            $table->timestamps();

            $table->index(['qurilma_id','created_at']);
            $table->index(['amal','holat']);
        });

        // ── .env'ga qo'shimcha kalitlar uchun eslatma ──────────────
        // DEVICE_CONTROL_ENABLED=false
        // DEVICE_CONTROL_AUTO_LOCK_ENABLED=false
    }

    public function down(): void
    {
        Schema::dropIfExists('qurilma_loglar');
        Schema::dropIfExists('qurilma_tasdiqlashlar');
        Schema::dropIfExists('qurilma_roziliklar');
        Schema::dropIfExists('qurilma_rozilik_shablonlari');
        Schema::dropIfExists('qurilma_qoidalar');
        Schema::dropIfExists('qurilma_provayder_ulanishlari');
        Schema::dropIfExists('qurilma_provayder_sozlamalari');
        Schema::dropIfExists('qurilma_provayderlar');
        Schema::dropIfExists('qurilmalar');
        Schema::table('tovar_guruhlar', function (Blueprint $table) {
            $table->dropColumn('imei_talab');
        });
    }
};
