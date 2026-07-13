<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Xodim uchun global qiymatlarni bekor qiladigan (override) individual sozlamalar,
        // va tizimga qo'shilishidan oldingi (eski) qoldiqni bir martalik kiritish uchun maydon.
        Schema::table('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            $table->decimal('soliq_foizi', 5, 2)->nullable()->after('reja_bonus_summa')
                ->comment('NULL bo\'lsa global stavka ishlatiladi');
            $table->decimal('boshqa_ushlanma_foizi', 5, 2)->nullable()->after('soliq_foizi')
                ->comment('NULL bo\'lsa global stavka ishlatiladi');
            $table->decimal('dastlabki_qoldiq', 15, 2)->default(0)->after('boshqa_ushlanma_foizi')
                ->comment('Tizimga qo\'shilishidan oldingi eski qoldiq (bir martalik kiritiladi)');
        });

        // Hisoblash natijasiga soliq/boshqa ushlanma (foiz+summa, snapshot) va avans jamg'armasi.
        Schema::table('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->decimal('soliq_foizi', 5, 2)->default(0)->after('ushlanma_izoh');
            $table->decimal('soliq_summa', 15, 2)->default(0)->after('soliq_foizi');
            $table->decimal('boshqa_ushlanma_foizi', 5, 2)->default(0)->after('soliq_summa');
            $table->decimal('boshqa_ushlanma_summa', 15, 2)->default(0)->after('boshqa_ushlanma_foizi');
            $table->decimal('avans_jami', 15, 2)->default(0)->after('boshqa_ushlanma_summa')
                ->comment('Shu oy uchun oldindan berilgan avanslar yig\'indisi');
        });

        // Har bir avans (oldindan to'lov) alohida yozuv sifatida — darhol Harajatga yoziladi.
        Schema::create('ish_haqi_avanslar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->unsignedSmallInteger('yil');
            $table->unsignedTinyInteger('oy');
            $table->decimal('summa', 15, 2);
            $table->date('sana');
            $table->string('izoh', 300)->nullable();
            $table->foreignId('harajat_id')->nullable()->constrained('harajatlar')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ish_haqi_avanslar');

        Schema::table('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->dropColumn(['soliq_foizi', 'soliq_summa', 'boshqa_ushlanma_foizi', 'boshqa_ushlanma_summa', 'avans_jami']);
        });

        Schema::table('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            $table->dropColumn(['soliq_foizi', 'boshqa_ushlanma_foizi', 'dastlabki_qoldiq']);
        });
    }
};
