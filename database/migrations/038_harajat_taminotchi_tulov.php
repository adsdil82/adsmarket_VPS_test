<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harajatlar', function (Blueprint $table) {
            // Eski "Таъминотчилар:" harajat yozuvi aniq bir TaminotchiTulov
            // yozuviga migratsiya qilingan bo'lsa — shu yerda saqlanadi.
            // Bunday holatda harajatning o'zi Pul Oqimlariga ALOHIDA
            // yozilmaydi (manba_tur='taminotchi_tulov' orqali allaqachon
            // yozilgan) — ikki marta hisoblanmasligi uchun.
            $table->foreignId('taminotchi_tulov_id')->nullable()->after('tegishli_xodim_id')
                ->constrained('taminotchi_tulovlar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('harajatlar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('taminotchi_tulov_id');
        });
    }
};
