<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notification_settings MODIFY channel ENUM('sms','telegram','email','hybrid_mail','autopay') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notification_settings MODIFY channel ENUM('sms','telegram','email','hybrid_mail') NOT NULL");
    }
};
