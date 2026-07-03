<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('harajatlar', function (Blueprint $table) {
            // Qaysi kassadan (naqd/terminal/bank) chiqqani — Pul Oqimlariga
            // avtomatik yozish uchun zarur. Mavjud (eski) yozuvlar uchun
            // NULL qoladi — ular Pul Oqimlariga bog'lanmaydi.
            $table->enum('kassa_turi', ['naqd','terminal','bank'])->nullable()->after('turi');
            // Pul Oqimlari kategoriyasi (CF-modda) — harajat qaysi moddaga
            // tegishli ekanini bildiradi, hisobotda harajat turi kabi
            // ko'rinishi uchun.
            $table->foreignId('pul_kategoriya_id')->nullable()->after('kassa_turi')
                ->constrained('pul_kategoriyalar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('harajatlar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pul_kategoriya_id');
            $table->dropColumn('kassa_turi');
        });
    }
};
