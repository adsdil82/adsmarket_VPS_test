<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('viloyatlar', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->string('nomi', 100);
            $table->string('kirill_nomi', 150)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('tumanlar', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();
            $table->unsignedSmallInteger('viloyat_id');
            $table->string('nomi', 150);
            $table->string('kirill_nomi', 150)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('viloyat_id')->references('id')->on('viloyatlar')->cascadeOnDelete();
            $table->index('viloyat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tumanlar');
        Schema::dropIfExists('viloyatlar');
    }
};
