<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // FK'larni vaqtincha o'chirib, ustunlarni nullable qilamiz (qo'lda import
        // qilingan, hali bizning kredit tizimimizga biriktirilmagan kontraktlar
        // uchun reg_kredit_id/mijoz_id bo'sh bo'lishi kerak).
        $fk = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'autopay_shartnomalar'
              AND COLUMN_NAME IN ('reg_kredit_id', 'mijoz_id') AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        foreach ($fk as $row) {
            DB::statement("ALTER TABLE autopay_shartnomalar DROP FOREIGN KEY {$row->CONSTRAINT_NAME}");
        }

        DB::statement("ALTER TABLE autopay_shartnomalar MODIFY reg_kredit_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE autopay_shartnomalar MODIFY mijoz_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE autopay_shartnomalar ADD COLUMN pinfl VARCHAR(14) NULL AFTER mijoz_id");
        DB::statement("ALTER TABLE autopay_shartnomalar ADD COLUMN manba ENUM('api','qolda') NOT NULL DEFAULT 'api' AFTER pinfl");

        DB::statement("ALTER TABLE autopay_shartnomalar ADD CONSTRAINT autopay_shartnomalar_reg_kredit_id_foreign FOREIGN KEY (reg_kredit_id) REFERENCES reg_kredit(id) ON DELETE SET NULL");
        DB::statement("ALTER TABLE autopay_shartnomalar ADD CONSTRAINT autopay_shartnomalar_mijoz_id_foreign FOREIGN KEY (mijoz_id) REFERENCES mijozlar(id) ON DELETE SET NULL");
    }

    public function down(): void
    {
        $fk = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'autopay_shartnomalar'
              AND COLUMN_NAME IN ('reg_kredit_id', 'mijoz_id') AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        foreach ($fk as $row) {
            DB::statement("ALTER TABLE autopay_shartnomalar DROP FOREIGN KEY {$row->CONSTRAINT_NAME}");
        }

        DB::statement("ALTER TABLE autopay_shartnomalar DROP COLUMN manba");
        DB::statement("ALTER TABLE autopay_shartnomalar DROP COLUMN pinfl");
        DB::statement("ALTER TABLE autopay_shartnomalar MODIFY reg_kredit_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE autopay_shartnomalar MODIFY mijoz_id BIGINT UNSIGNED NOT NULL");

        DB::statement("ALTER TABLE autopay_shartnomalar ADD CONSTRAINT autopay_shartnomalar_reg_kredit_id_foreign FOREIGN KEY (reg_kredit_id) REFERENCES reg_kredit(id) ON DELETE CASCADE");
        DB::statement("ALTER TABLE autopay_shartnomalar ADD CONSTRAINT autopay_shartnomalar_mijoz_id_foreign FOREIGN KEY (mijoz_id) REFERENCES mijozlar(id) ON DELETE CASCADE");
    }
};
