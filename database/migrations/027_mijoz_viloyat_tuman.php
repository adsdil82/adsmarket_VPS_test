<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->unsignedSmallInteger('viloyat_id')->nullable()->after('manzil');
            $table->unsignedSmallInteger('tuman_id')->nullable()->after('viloyat_id');
            $table->foreign('viloyat_id')->references('id')->on('viloyatlar')->onDelete('set null');
            $table->foreign('tuman_id')->references('id')->on('tumanlar')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->dropForeign(['viloyat_id']);
            $table->dropForeign(['tuman_id']);
            $table->dropColumn(['viloyat_id', 'tuman_id']);
        });
    }
};
