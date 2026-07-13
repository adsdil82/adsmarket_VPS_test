<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pochta_loglar MODIFY holat ENUM('kutilmoqda','yaratildi','yuborildi','xato','ochirilgan') DEFAULT 'kutilmoqda'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pochta_loglar MODIFY holat ENUM('kutilmoqda','yaratildi','yuborildi','xato') DEFAULT 'kutilmoqda'");
    }
};
