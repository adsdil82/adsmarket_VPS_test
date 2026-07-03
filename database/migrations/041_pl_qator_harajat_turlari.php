<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bir P&L qatori (masalan "Ish haqi") bir nechta harajat_turi'ni
        // birlashtirib hisoblashi kerak bo'lishi mumkin (masalan har bir
        // xodim uchun alohida "Иш хаки: X" turi bor). Shuning uchun
        // yagona nullable FK o'rniga ko'p-ko'pga pivot jadval qo'shamiz.
        Schema::create('pl_qator_harajat_turlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qator_id')->constrained('pl_qatorlari')->cascadeOnDelete();
            $table->foreignId('harajat_turi_id')->constrained('harajat_turlari')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['qator_id', 'harajat_turi_id']);
        });

        // Mavjud yagona harajat_turi_id qiymatlarini pivotga ko'chiramiz.
        $mavjud = DB::table('pl_qatorlari')->whereNotNull('harajat_turi_id')->get(['id', 'harajat_turi_id']);
        foreach ($mavjud as $q) {
            DB::table('pl_qator_harajat_turlari')->insert([
                'qator_id' => $q->id, 'harajat_turi_id' => $q->harajat_turi_id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // "Ish haqi (oklad)" qatoriga barcha "Иш хаки: *" turlarini bog'laymiz.
        $ishHaqiQatorId = DB::table('pl_qatorlari')->where('nomi', 'Ish haqi (oklad)')->value('id');
        $ishHaqiTurlari = DB::table('harajat_turlari')->where('nomi', 'like', '%ш хаки%')->pluck('id');
        if ($ishHaqiQatorId) {
            DB::table('pl_qatorlari')->where('id', $ishHaqiQatorId)->update(['hisoblash_turi' => 'avtomat_harajat_turi']);
            foreach ($ishHaqiTurlari as $tid) {
                DB::table('pl_qator_harajat_turlari')->insertOrIgnore([
                    'qator_id' => $ishHaqiQatorId, 'harajat_turi_id' => $tid,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pl_qator_harajat_turlari');
    }
};
