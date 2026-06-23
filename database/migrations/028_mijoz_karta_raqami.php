<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->string('karta_raqami', 20)->nullable()->after('pinfl');
        });
    }

    public function down(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->dropColumn('karta_raqami');
        });
    }
};
