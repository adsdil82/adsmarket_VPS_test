<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mijoz_telefonlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mijoz_id')->constrained('mijozlar')->onDelete('cascade');
            $table->string('telefon', 50);
            $table->boolean('sms_yuborilsin')->default(true);
            $table->unsignedTinyInteger('tartib')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mijoz_telefonlar');
    }
};
