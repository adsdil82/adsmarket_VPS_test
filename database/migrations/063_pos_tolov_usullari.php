<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_tolov_usullari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('filial_id');
            $table->string('nomi', 100);
            $table->enum('turi', ['terminal', 'onlayn', 'boshqa'])->default('terminal');
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->unsignedInteger('tartib')->default(0);
            $table->string('izoh', 255)->nullable();
            $table->timestamps();

            $table->foreign('filial_id')->references('id')->on('filiallar')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_tolov_usullari');
    }
};
