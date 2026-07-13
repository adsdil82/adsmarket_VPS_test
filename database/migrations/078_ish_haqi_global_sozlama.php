<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Yagona (singleton) qator — barcha xodimlar uchun standart soliq/ushlanma foizlari.
        // Har bir xodim o'z profilida buni bekor qilib, boshqa qiymat qo'yishi mumkin.
        Schema::create('ish_haqi_global_sozlama', function (Blueprint $table) {
            $table->id();
            $table->decimal('soliq_foizi', 5, 2)->default(12);
            $table->decimal('boshqa_ushlanma_foizi', 5, 2)->default(0);
            $table->timestamps();
        });

        DB::table('ish_haqi_global_sozlama')->insert([
            'soliq_foizi' => 12, 'boshqa_ushlanma_foizi' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ish_haqi_global_sozlama');
    }
};
