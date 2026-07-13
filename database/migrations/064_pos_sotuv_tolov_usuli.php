<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sotuv', function (Blueprint $table) {
            $table->unsignedBigInteger('tolov_usuli_id')->nullable()->after('tolov_turi');
            $table->foreign('tolov_usuli_id')->references('id')->on('pos_tolov_usullari')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_sotuv', function (Blueprint $table) {
            $table->dropForeign(['tolov_usuli_id']);
            $table->dropColumn('tolov_usuli_id');
        });
    }
};
