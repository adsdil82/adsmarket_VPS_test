<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taminotchilar', function (Blueprint $table) {
            $table->enum('asosiy_valyuta', ['UZS', 'USD'])->default('UZS')->after('mfo');
        });
    }

    public function down(): void
    {
        Schema::table('taminotchilar', function (Blueprint $table) {
            $table->dropColumn('asosiy_valyuta');
        });
    }
};
