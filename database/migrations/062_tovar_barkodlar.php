<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tovar_barkodlar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tovar_id');
            $table->string('barkod', 50)->unique();
            $table->timestamps();

            $table->foreign('tovar_id')->references('id')->on('tovar_katalog')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tovar_barkodlar');
    }
};
