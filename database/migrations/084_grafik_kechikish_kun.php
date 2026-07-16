<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operatsion kun yopilganda hisoblanadigan kechikish kunlari snapshot'i.
 * Diqqat: bu ustun ro'yxatlardagi real-time DATEDIFF hisob-kitobini
 * almashtirmaydi — u kun yopish paytidagi tarixiy holatni saqlaydi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grafik', function (Blueprint $table) {
            $table->unsignedInteger('kechikish_kun')->nullable()->after('holat');
        });
    }

    public function down(): void
    {
        Schema::table('grafik', function (Blueprint $table) {
            $table->dropColumn('kechikish_kun');
        });
    }
};
