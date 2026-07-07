<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foydalanuvchilar', function (Blueprint $table) {
            $table->string('pin_kod')->nullable()->after('password');
            $table->timestamp('pin_bloklangan_gacha')->nullable()->after('pin_kod');
            $table->unsignedTinyInteger('pin_xato_soni')->default(0)->after('pin_bloklangan_gacha');
        });
    }

    public function down(): void
    {
        Schema::table('foydalanuvchilar', function (Blueprint $table) {
            $table->dropColumn(['pin_kod', 'pin_bloklangan_gacha', 'pin_xato_soni']);
        });
    }
};
