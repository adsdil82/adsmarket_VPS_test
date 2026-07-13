<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autopay_kartalar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mijoz_id');
            $table->string('uuid', 100)->unique();
            $table->string('pan', 30)->nullable();
            $table->string('turi', 10); // uzcard | humo
            $table->string('egasi', 150)->nullable();
            $table->string('telefon', 30)->nullable();
            $table->boolean('auto')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('mijoz_id')->references('id')->on('mijozlar')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autopay_kartalar');
    }
};
