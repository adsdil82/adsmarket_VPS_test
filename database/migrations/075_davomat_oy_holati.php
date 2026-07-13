<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Har bir (yil, oy) uchun tabel yopiq/ochiqligini belgilaydi.
        // Yopilgan oyda davomat o'zgartirilmaydi.
        Schema::create('davomat_oy_holati', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('yil');
            $table->unsignedTinyInteger('oy');
            $table->enum('holat', ['ochiq', 'yopiq'])->default('ochiq');
            $table->foreignId('yopgan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamp('yopilgan_vaqt')->nullable();
            $table->timestamps();

            $table->unique(['yil', 'oy']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('davomat_oy_holati');
    }
};
