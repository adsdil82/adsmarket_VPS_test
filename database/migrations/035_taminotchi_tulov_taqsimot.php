<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taminotchi_tulovlar', function (Blueprint $table) {
            // To'lov bir nechta kirimga "kaskad" bo'lib taqsimlangan bo'lsa,
            // har bir kirimga necha so'm tegishli bo'lganini saqlaydi:
            // [{"kirim_id":1,"summa":500000}, {"kirim_id":2,"summa":200000}]
            // To'lovni o'chirishda aynan shu summalarni qaytarish uchun ishlatiladi.
            $table->json('kirim_taqsimot')->nullable()->after('kirim_id');
        });
    }

    public function down(): void
    {
        Schema::table('taminotchi_tulovlar', function (Blueprint $table) {
            $table->dropColumn('kirim_taqsimot');
        });
    }
};
