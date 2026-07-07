<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hisobot_shablonlari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foydalanuvchi_id');
            $table->string('nomi', 150);
            $table->string('modul', 40);
            $table->json('ustunlar')->nullable();
            $table->json('shartlar')->nullable();
            $table->string('sana_turi', 30)->default('bu_oy'); // bugun/kecha/bu_oy/otgan_oy/bu_chorak/bu_yil/maxsus
            $table->date('dan_sana')->nullable();
            $table->date('gacha_sana')->nullable();
            $table->string('guruhlash', 40)->nullable();
            $table->timestamps();

            $table->index(['foydalanuvchi_id', 'modul']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hisobot_shablonlari');
    }
};
