<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global dam olish/bayram kunlari kalendari — Davomat tabidagi avtomatik
        // "dam olish kuni" belgisi endi qattiq kodlangan shanba/yakshanba emas,
        // shu jadvaldan olinadi (mijozlar orasida ish kunlari har xil bo'lishi mumkin).
        Schema::create('dam_olish_kunlari', function (Blueprint $table) {
            $table->id();
            $table->date('sana')->unique();
            $table->enum('turi', ['dam_olish', 'bayram'])->default('dam_olish');
            $table->foreignId('belgilagan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dam_olish_kunlari');
    }
};
