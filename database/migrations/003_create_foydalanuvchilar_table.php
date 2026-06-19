<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 003 — Foydalanuvchilar jadvali (Xodimlar/Users)
// filiallar dan keyin yaratiladi (filial_id FK uchun)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foydalanuvchilar', function (Blueprint $table) {
            $table->id();

            // Qaysi filialda ishlaydi (admin uchun NULL — hamma filiallarni ko'radi)
            $table->foreignId('filial_id')
                  ->nullable()
                  ->constrained('filiallar')
                  ->nullOnDelete();

            // Ism va familiya bitta maydonda
            $table->string('ism_familiya', 200);

            // Login uchun email
            $table->string('email', 150)->unique();

            // Shifrlangan parol
            $table->string('password', 255);

            // Rol va vakolatlar:
            // admin    — hamma filiallar, hamma amallar
            // menejer  — o'z filiali, shartnoma + to'lov
            // kassir   — o'z filiali, faqat to'lov qabul qilish
            // hisobchi — o'z filiali, faqat ko'rish + hisobot
            $table->enum('rol', ['admin', 'menejer', 'kassir', 'hisobchi'])
                  ->default('kassir');

            // Holati
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');

            // "Eslab qol" funksiyasi uchun token
            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foydalanuvchilar');
    }
};
