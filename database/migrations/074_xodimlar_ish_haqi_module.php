<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Har bir xodim uchun ish haqi sozlamalari (oklad, bonus foizi, oylik reja)
        Schema::create('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->unique()->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->decimal('oklad', 15, 2)->default(0)->comment("Oylik asosiy oklad (100% davomatda to'liq olinadi)");
            $table->decimal('bonus_foizi', 5, 2)->default(5)->comment("Shartnomalardan yig'ilgan to'lovlardan komissiya foizi");
            $table->decimal('oylik_reja_summa', 15, 2)->default(0)->comment("Oylik savdo (yig'ilgan to'lov) rejasi — 0 bo'lsa reja belgilanmagan");
            $table->decimal('reja_bonus_summa', 15, 2)->default(0)->comment("Reja bajarilganda beriladigan qat'iy bonus summasi");
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->timestamps();
        });

        // Kunlik davomat (tabel)
        Schema::create('xodim_davomat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->date('sana');
            $table->enum('holat', ['keldi', 'kech_qoldi', 'kelmadi', 'tatil', 'kasal', 'dam_olish'])->default('keldi');
            $table->string('izoh', 300)->nullable();
            $table->foreignId('belgilagan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();

            $table->unique(['xodim_id', 'sana']);
        });

        // Oylik ish haqi hisob-kitobi (har xodim + yil + oy uchun bitta yozuv)
        Schema::create('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->unsignedSmallInteger('yil');
            $table->unsignedTinyInteger('oy');

            $table->unsignedTinyInteger('ish_kunlari_jami')->default(0)->comment('Oydagi jami ish kunlari (dam olish/tatil kunlarisiz)');
            $table->unsignedTinyInteger('kelgan_kunlar')->default(0)->comment("Keldi + kech qoldi bo'lgan kunlar soni");
            $table->decimal('davomat_foizi', 5, 2)->default(0);
            $table->decimal('oklad_qismi', 15, 2)->default(0)->comment('Oklad * davomat_foizi/100');

            $table->decimal('yigilgan_tolovlar', 15, 2)->default(0)->comment("Shu oyda xodimga tegishli shartnomalardan yig'ilgan to'lovlar summasi");
            $table->decimal('bonus_foizi', 5, 2)->default(0)->comment('Hisoblash vaqtidagi bonus foizi (snapshot)');
            $table->decimal('komissiya_bonus', 15, 2)->default(0);

            $table->boolean('reja_bajarildimi')->default(false);
            $table->decimal('reja_bonus', 15, 2)->default(0);

            $table->decimal('qoshimcha_hisoblash', 15, 2)->default(0)->comment("Qo'lda qo'shiladigan qo'shimcha summa");
            $table->string('qoshimcha_izoh', 300)->nullable();
            $table->decimal('ushlanma', 15, 2)->default(0)->comment("Qo'lda kiritiladigan ushlab qolish (avans/jarima)");
            $table->string('ushlanma_izoh', 300)->nullable();

            $table->decimal('jami_hisoblangan', 15, 2)->default(0);

            $table->enum('holat', ['hisoblangan', 'tolandi'])->default('hisoblangan');
            $table->foreignId('harajat_id')->nullable()->constrained('harajatlar')->nullOnDelete();
            $table->timestamp('tolangan_vaqt')->nullable();
            $table->foreignId('hisoblagan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();

            $table->timestamps();

            $table->unique(['xodim_id', 'yil', 'oy']);
        });

        // "Ish haqi" moduli avtomatik yaratadigan yozuvlar uchun generic harajat turlari
        // (mavjud "Иш хаки: <ism>" — legacy, qo'lda yaratilgan yozuvlar; bular bilan aralashmasligi uchun alohida).
        $kategoriyalar = DB::table('pul_kategoriyalar')->pluck('id', 'kod');
        $ishHaqiKat = $kategoriyalar['CF-2110'] ?? null;
        $bonusKat   = $kategoriyalar['CF-2120'] ?? $ishHaqiKat;

        if ($ishHaqiKat) {
            DB::table('harajat_turlari')->insertOrIgnore([
                'nomi'              => 'Ish haqi (avtomatik hisoblash)',
                'pul_kategoriya_id' => $ishHaqiKat,
                'talab_xodim'       => true,
                'talab_schetchik'   => false,
                'holat'             => 'faol',
                'sort_order'        => 900,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('harajat_turlari')->where('nomi', 'Ish haqi (avtomatik hisoblash)')->delete();
        Schema::dropIfExists('ish_haqi_hisoblari');
        Schema::dropIfExists('xodim_davomat');
        Schema::dropIfExists('xodim_ish_haqi_sozlama');
    }
};
