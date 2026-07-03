<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tovar_katalog', function (Blueprint $table) {
            $table->decimal('nasiya_narx', 15, 2)->default(0)->after('sotish_narx');
        });

        // Mavjud tovarlar uchun — nasiya narxi boshlang'ich qiymati naqd/POS
        // narxiga teng qilib qo'yiladi (keyin admin alohida o'zgartirishi mumkin).
        DB::statement('UPDATE tovar_katalog SET nasiya_narx = sotish_narx WHERE nasiya_narx = 0');
    }

    public function down(): void
    {
        Schema::table('tovar_katalog', function (Blueprint $table) {
            $table->dropColumn('nasiya_narx');
        });
    }
};
