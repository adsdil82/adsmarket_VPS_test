<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foydalanuvchilar', function (Blueprint $table) {
            // 'uz' = O'zbek (default), 'ru' = Rus, 'en' = Ingliz
            $table->string('til', 5)->default('uz')->after('holat');
        });
    }

    public function down(): void
    {
        Schema::table('foydalanuvchilar', function (Blueprint $table) {
            $table->dropColumn('til');
        });
    }
};
