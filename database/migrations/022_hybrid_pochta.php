<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pochta xat shablonlari (admin tomonidan sozlanadi)
        Schema::create('pochta_shablonlar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 100);
            $table->text('matn');                                   // {{ozgaruvchi}} lar bilan
            $table->unsignedInteger('qayta_yuborish_kun')->default(30); // min kunlar oralig'i
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Har bir xat yuborish uchun log
        Schema::create('pochta_loglar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reg_kredit_id');
            $table->unsignedBigInteger('mijoz_id');
            $table->unsignedBigInteger('shablon_id')->nullable();
            $table->integer('api_letter_id')->nullable();           // Hybrid Pochta API dan
            $table->string('receiver', 200);                        // Qabul qiluvchi FIO
            $table->text('address');                                // To'liq manzil
            $table->unsignedInteger('region_id')->nullable();       // API viloyat ID
            $table->unsignedInteger('area_id')->nullable();         // API tuman ID
            $table->enum('holat', ['kutilmoqda','yaratildi','yuborildi','xato'])->default('kutilmoqda');
            $table->text('xato_xabar')->nullable();
            $table->json('so_rov')->nullable();                     // API ga yuborilgan so'rov
            $table->json('javob')->nullable();                      // API javob
            $table->timestamp('yaratildi_vaqt')->nullable();        // createMail vaqti
            $table->timestamp('yuborildi_vaqt')->nullable();        // sendMail vaqti
            $table->timestamps();

            $table->foreign('reg_kredit_id')->references('id')->on('reg_kredit')->cascadeOnDelete();
            $table->foreign('mijoz_id')->references('id')->on('mijozlar')->cascadeOnDelete();
            $table->foreign('shablon_id')->references('id')->on('pochta_shablonlar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pochta_loglar');
        Schema::dropIfExists('pochta_shablonlar');
    }
};
