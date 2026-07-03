<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Har bir ombor uchun ALOHIDA tovar qoldig'i — ko'p-omborli tizimning
        // asosi. tovar_katalog.qoldiq endi bu jadvaldan hisoblangan JAMI
        // (barcha omborlar bo'yicha) sifatida saqlanadi — tezkor ko'rsatish
        // uchun keshlangan qiymat, lekin haqiqiy manba shu yerda.
        Schema::create('ombor_qoldiqlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ombor_id')->constrained('omborlar')->cascadeOnDelete();
            $table->foreignId('tovar_id')->constrained('tovar_katalog')->cascadeOnDelete();
            $table->decimal('miqdor', 15, 3)->default(0);
            $table->timestamps();

            $table->unique(['ombor_id', 'tovar_id']);
            $table->index('tovar_id');
        });

        // ── Demo/ishlatilmagan tovarlarni tozalash ──────────────────────
        // Xavfsiz o'chirish mezoni: qoldiq=0 VA hech qanday real yozuvda
        // ishlatilmagan (POS sotuv, Nasiya shartnoma, taminotchi kirimi,
        // qurilma biriktirilishi). Bular haqiqatda hech qachon ombordan
        // o'tmagan, faqat qo'lda/import orqali kiritilgan demo yozuvlar.
        $safeIds = DB::table('tovar_katalog as t')
            ->where('t.qoldiq', 0)
            ->whereNotExists(fn($q) => $q->selectRaw(1)->from('pos_tafsilot as p')->whereColumn('p.tovar_id','t.id'))
            ->whereNotExists(fn($q) => $q->selectRaw(1)->from('tovarlar as tv')->whereColumn('tv.tovar_katalog_id','t.id'))
            ->whereNotExists(fn($q) => $q->selectRaw(1)->from('taminot_kirim_qatorlar as k')->whereColumn('k.tovar_id','t.id'))
            ->whereNotExists(fn($q) => $q->selectRaw(1)->from('qurilmalar as qr')->whereColumn('qr.tovar_katalog_id','t.id'))
            ->pluck('t.id');

        foreach ($safeIds->chunk(500) as $chunk) {
            DB::table('tovar_katalog')->whereIn('id', $chunk)->delete();
        }

        // ── Qolgan (real) tovarlarning joriy qoldig'ini "Asosiy ombor"ga
        //    boshlang'ich saldo sifatida joylashtiramiz — hozirgacha global
        //    qoldiq qayerdan kelgani noaniq bo'lgani uchun, eng mantiqiy
        //    joy — har filialning o'z asosiy ombori.
        $asosiyOmborlar = DB::table('omborlar')->where('tur', 'asosiy')->pluck('id', 'filial_id');
        $birinchiOmborId = DB::table('omborlar')->where('tur', 'asosiy')->value('id');

        $qolganTovarlar = DB::table('tovar_katalog')->where('qoldiq', '>', 0)->get(['id', 'qoldiq']);
        foreach ($qolganTovarlar as $t) {
            DB::table('ombor_qoldiqlar')->insert([
                'ombor_id'   => $birinchiOmborId,
                'tovar_id'   => $t->id,
                'miqdor'     => $t->qoldiq,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ombor_qoldiqlar');
    }
};
