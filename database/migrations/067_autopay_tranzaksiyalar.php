<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autopay_tranzaksiyalar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('autopay_shartnoma_id');
            $table->string('ext_id', 150)->unique();
            $table->string('rrn', 50)->nullable();
            $table->decimal('summa', 15, 2)->default(0);
            $table->string('holat', 30)->nullable();
            $table->dateTime('sana')->nullable();
            $table->unsignedBigInteger('tulov_id')->nullable();
            $table->json('xom_javob')->nullable();
            $table->timestamps();

            $table->foreign('autopay_shartnoma_id')->references('id')->on('autopay_shartnomalar')->onDelete('cascade');
            $table->foreign('tulov_id')->references('id')->on('tulovlar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autopay_tranzaksiyalar');
    }
};
