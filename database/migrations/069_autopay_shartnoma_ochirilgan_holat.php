<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE autopay_shartnomalar MODIFY holat ENUM('kutilmoqda', 'faol', 'toxtatilgan', 'xato', 'ochirilgan') DEFAULT 'kutilmoqda'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE autopay_shartnomalar MODIFY holat ENUM('kutilmoqda', 'faol', 'toxtatilgan', 'xato') DEFAULT 'kutilmoqda'");
    }
};
