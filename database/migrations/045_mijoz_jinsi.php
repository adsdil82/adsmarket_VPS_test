<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->enum('jinsi', ['erkak', 'ayol'])->nullable()->after('otasining_ismi');
        });
    }

    public function down(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->dropColumn('jinsi');
        });
    }
};
