<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sotuv', function (Blueprint $table) {
            $table->string('tolov_izoh', 500)->nullable()->after('mijoz_ism');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sotuv', function (Blueprint $table) {
            $table->dropColumn('tolov_izoh');
        });
    }
};
