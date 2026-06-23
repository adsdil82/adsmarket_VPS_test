<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grafik', function (Blueprint $table) {
            $table->decimal('ustama_summa', 15, 2)->default(0)->after('tolov_summa');
        });

        Schema::create('hujjat_qoshimcha_bandlar', function (Blueprint $table) {
            $table->id();
            $table->enum('turi', ['shartnoma', 'kafillik']);
            $table->longText('matn');
            $table->boolean('faol')->default(true);
            $table->unsignedBigInteger('xodim_id')->nullable();
            $table->timestamps();
            $table->index(['turi', 'faol']);
        });

        Schema::table('reg_kredit', function (Blueprint $table) {
            $table->unsignedBigInteger('shartnoma_band_versiya_id')->nullable()->after('izoh');
            $table->unsignedBigInteger('kafillik_band_versiya_id')->nullable()->after('shartnoma_band_versiya_id');
        });
    }

    public function down(): void
    {
        Schema::table('grafik', function (Blueprint $table) {
            $table->dropColumn('ustama_summa');
        });
        Schema::table('reg_kredit', function (Blueprint $table) {
            $table->dropColumn(['shartnoma_band_versiya_id', 'kafillik_band_versiya_id']);
        });
        Schema::dropIfExists('hujjat_qoshimcha_bandlar');
    }
};
