<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            // Reja bonusi proporsional hisoblanadigan oraliq: min% dan past — bonus 0,
            // max% dan yuqori (yoki teng) — bonus to'liq, oraliqda — proporsional.
            $table->decimal('reja_min_foizi', 5, 2)->default(80)->after('oylik_reja_summa');
            $table->decimal('reja_max_foizi', 5, 2)->default(100)->after('reja_min_foizi');
        });
    }

    public function down(): void
    {
        Schema::table('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            $table->dropColumn(['reja_min_foizi', 'reja_max_foizi']);
        });
    }
};
