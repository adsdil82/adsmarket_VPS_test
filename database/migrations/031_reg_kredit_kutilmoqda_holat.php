<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// 031 — reg_kredit.holat enum'iga "kutilmoqda" qiymatini qo'shish.
// Yangi shartnoma avval "kutilmoqda" holatida saqlanadi (ombor/to'lov
// operatsiyalarisiz), keyin admin/menejer "Aktivlashtirish" bosgandagina
// "faol"ga o'tadi. Mavjud shartnomalarga ta'sir qilmaydi (default qiymat
// o'zgarmaydi, faqat ruxsat etilgan qiymatlar ro'yxati kengayadi).
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reg_kredit MODIFY holat ENUM('kutilmoqda','faol','yopilgan','muddati_otgan','muzlatilgan') NOT NULL DEFAULT 'faol'");
    }

    public function down(): void
    {
        DB::statement("UPDATE reg_kredit SET holat='faol' WHERE holat='kutilmoqda'");
        DB::statement("ALTER TABLE reg_kredit MODIFY holat ENUM('faol','yopilgan','muddati_otgan','muzlatilgan') NOT NULL DEFAULT 'faol'");
    }
};
