<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// 032 — Rol bo'yicha "ustama ko'rinishi" sirligi va to'lov turlari cheklovi.
//
// 1) rollar.ustama_korish — to'lov grafigi/qabul qilish sahifalarida "Ustama"
//    ustunini ko'rish huquqi. Bu MOLIYAVIY MAXFIY ma'lumot (kredit foizi/ustama
//    qismi) — oddiy kassir/menejer ko'rmasligi, faqat admin (yoki ruxsat
//    berilgan rol) ko'rishi kerak. Standart: hammasi uchun yopiq, faqat admin
//    uchun ochiq qilib qo'yiladi.
//
// 2) rol_tulov_turlari — har bir rol uchun ADMIN tomonidan qaysi to'lov
//    turlari (NAQD, BANK, terminal va h.k.) to'lov qabul qilish sahifasida
//    ko'rinishini cheklash. Agar bir rol uchun bu jadvalda YOZUV BO'LMASA —
//    standart holatda barcha faol to'lov turlari ko'rinadi (orqaga mosligi
//    buzilmasligi uchun). Yozuv qo'shilgan zahoti — faqat shu yozuvlardagi
//    turlar ko'rinadi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rollar', function (Blueprint $table) {
            $table->boolean('ustama_korish')->default(false)->after('tartib');
        });

        DB::table('rollar')->where('kalit', 'admin')->update(['ustama_korish' => true]);

        Schema::create('rol_tulov_turlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('rollar')->cascadeOnDelete();
            $table->foreignId('tulov_turi_id')->constrained('tulov_turlari')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['rol_id', 'tulov_turi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_tulov_turlari');
        Schema::table('rollar', function (Blueprint $table) {
            $table->dropColumn('ustama_korish');
        });
    }
};
