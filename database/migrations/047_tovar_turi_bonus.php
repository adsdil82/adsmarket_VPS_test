<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tovarlar', function (Blueprint $table) {
            $table->enum('turi', ['kredit', 'bonus'])->default('kredit')->after('nomi')
                ->comment("Kredit — nasiyaga sotilgan tovar (shartnoma/hisob-fakturada ko'rinadi). Bonus — mijozga qo'shib beriladigan, faqat ombordan kamayadigan tovar (shartnoma/hisob-fakturada ko'rinmaydi).");
        });
    }

    public function down(): void
    {
        Schema::table('tovarlar', function (Blueprint $table) {
            $table->dropColumn('turi');
        });
    }
};
