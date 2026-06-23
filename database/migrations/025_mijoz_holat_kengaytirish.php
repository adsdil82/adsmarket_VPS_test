<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE mijozlar MODIFY holat ENUM('faol','nofaol','sudda','yomon') NOT NULL DEFAULT 'faol'");
    }

    public function down(): void
    {
        DB::statement("UPDATE mijozlar SET holat='nofaol' WHERE holat IN ('sudda','yomon')");
        DB::statement("ALTER TABLE mijozlar MODIFY holat ENUM('faol','nofaol') NOT NULL DEFAULT 'faol'");
    }
};
