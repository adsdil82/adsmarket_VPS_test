<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->string('rasm', 255)->nullable()->after('jinsi');
        });
    }

    public function down(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->dropColumn('rasm');
        });
    }
};
