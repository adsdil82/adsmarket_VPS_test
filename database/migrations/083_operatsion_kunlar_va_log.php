<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operatsion kun boshqaruvi — har bir filial uchun kunlik yopish/ochish.
 * davomat_oy_holati (Ish Haqi moduli) patterniga o'xshash, lekin
 * filial+kun granulyarligida va audit logi bilan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operatsion_kunlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiallar');
            $table->date('sana');
            $table->enum('status', ['ochiq', 'yopiq'])->default('ochiq');
            $table->timestamp('yopilgan_vaqt')->nullable();
            $table->foreignId('yopgan_user_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamp('ochilgan_vaqt')->nullable();
            $table->foreignId('ochgan_user_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->text('izoh')->nullable();
            $table->timestamps();

            $table->unique(['filial_id', 'sana']);
        });

        Schema::create('kun_yopish_logi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operatsion_kun_id')->constrained('operatsion_kunlar');
            $table->enum('amal', ['yopish', 'ochish']);
            $table->foreignId('user_id')->constrained('foydalanuvchilar');
            $table->timestamp('vaqt');
            $table->json('natija_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kun_yopish_logi');
        Schema::dropIfExists('operatsion_kunlar');
    }
};
