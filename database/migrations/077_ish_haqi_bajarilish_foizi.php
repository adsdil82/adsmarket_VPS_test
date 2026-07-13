<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->decimal('reja_bajarilish_foizi', 6, 2)->default(0)->after('reja_bajarildimi')
                ->comment("Yig'ilgan to'lov / oylik reja * 100");
        });
    }

    public function down(): void
    {
        Schema::table('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->dropColumn('reja_bajarilish_foizi');
        });
    }
};
