<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temp_ch_kt_tv', function (Blueprint $table) {
            $table->string('turi', 20)->nullable()->after('tovar_nomi')
                ->comment("Eski bazadagi Ch_KT_tv.TvVid qiymati — 'Kredit' yoki 'Bonus'");
        });
    }

    public function down(): void
    {
        Schema::table('temp_ch_kt_tv', function (Blueprint $table) {
            $table->dropColumn('turi');
        });
    }
};
