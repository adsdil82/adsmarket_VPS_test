<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('autopay_tranzaksiyalar', function (Blueprint $table) {
            $table->string('karta_pan', 30)->nullable()->after('sana');
            $table->string('karta_token', 100)->nullable()->after('karta_pan');
            $table->string('karta_egasi', 150)->nullable()->after('karta_token');
        });
    }

    public function down(): void
    {
        Schema::table('autopay_tranzaksiyalar', function (Blueprint $table) {
            $table->dropColumn(['karta_pan', 'karta_token', 'karta_egasi']);
        });
    }
};
