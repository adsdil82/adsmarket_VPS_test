<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_terminal_loglar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('xodim_id')->nullable();
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->enum('hodisa', ['kirish', 'xato_pin', 'bloklandi', 'qulflash', 'yechish', 'chiqish']);
            $table->string('ip', 45)->nullable();
            $table->text('izoh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_terminal_loglar');
    }
};
