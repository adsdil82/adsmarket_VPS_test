<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Qo'lda qo'shilgan (tizimga kirmaydigan) xodimlar uchun — login huquqi bormi.
        Schema::table('foydalanuvchilar', function (Blueprint $table) {
            $table->boolean('tizimga_kirish_bormi')->default(true)->after('holat');
        });

        // Xodim profili — lavozim/aloqa/shartnoma uchun ma'lumotlar, ishga kirish-bo'shash
        // sanalari va muddatli qo'shimcha ish haqi.
        Schema::table('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            $table->string('lavozim', 150)->nullable()->after('xodim_id');
            $table->string('telefon', 20)->nullable()->after('lavozim');
            $table->string('manzil', 300)->nullable()->after('telefon');
            $table->string('passport_malumot', 100)->nullable()->after('manzil');
            $table->date('ishga_kirgan_sana')->nullable()->after('passport_malumot');
            $table->date('ishdan_boshagan_sana')->nullable()->after('ishga_kirgan_sana');

            $table->decimal('qoshimcha_ish_haqi', 15, 2)->default(0)->after('dastlabki_qoldiq')
                ->comment("Oklad ustiga muddatli qo'shimcha (masalan lavozim ustamasi)");
            $table->date('qoshimcha_boshlanish_sana')->nullable()->after('qoshimcha_ish_haqi');
            $table->date('qoshimcha_tugash_sana')->nullable()->after('qoshimcha_boshlanish_sana')
                ->comment("NULL — muddatsiz");
        });

        // Hisoblash natijasiga muddatli qo'shimcha va biriktirilgan bonuslar yig'indisi (snapshot).
        Schema::table('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->decimal('qoshimcha_ish_haqi_summa', 15, 2)->default(0)->after('reja_bonus');
            $table->decimal('biriktirilgan_bonus_summa', 15, 2)->default(0)->after('qoshimcha_ish_haqi_summa');
        });

        // Ta'til (yillik/kasallik/haq to'lanmaydigan) — kunlik davomatga sinxronlanadi.
        Schema::create('xodim_tatillar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->enum('turi', ['yillik', 'haq_tolanmaydigan', 'kasallik', 'boshqa'])->default('yillik');
            $table->date('boshlanish_sana');
            $table->date('rejalashtirilgan_qaytish_sana');
            $table->date('haqiqiy_qaytgan_sana')->nullable();
            $table->string('izoh', 300)->nullable();
            $table->enum('holat', ['rejalashtirilgan', 'yakunlandi', 'bekor_qilindi'])->default('rejalashtirilgan');
            $table->foreignId('created_by')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();
        });

        // Bonus turlari — shablon, bir marta yaratiladi, ko'p xodimga biriktiriladi.
        Schema::create('bonus_turlari', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 150);
            $table->string('tavsif', 300)->nullable();
            $table->enum('hisoblash_turi', ['summa', 'foiz_okladdan'])->default('summa');
            $table->decimal('standart_qiymat', 15, 2)->default(0);
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Xodimga biriktirilgan bonuslar — oy/yil oralig'ida amal qiladi.
        Schema::create('xodim_bonuslari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->foreignId('bonus_turi_id')->constrained('bonus_turlari')->cascadeOnDelete();
            $table->decimal('qiymat', 15, 2)->nullable()->comment("NULL — bonus turining standart qiymati ishlatiladi");
            $table->unsignedTinyInteger('boshlanish_oy');
            $table->unsignedSmallInteger('boshlanish_yil');
            $table->unsignedTinyInteger('tugash_oy')->nullable();
            $table->unsignedSmallInteger('tugash_yil')->nullable()->comment("NULL — muddatsiz");
            $table->string('izoh', 300)->nullable();
            $table->enum('holat', ['faol', 'bekor_qilingan'])->default('faol');
            $table->foreignId('created_by')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();
        });

        // Mehnat shartnomasi shablonlari — {{var}} o'zgaruvchilar bilan.
        Schema::create('mehnat_shartnoma_shablonlari', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 150);
            $table->longText('matn');
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Har bir xodim uchun yaratilgan (render qilingan, keyin tahrirlanadigan) shartnoma.
        Schema::create('xodim_shartnomalari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar')->cascadeOnDelete();
            $table->foreignId('shablon_id')->nullable()->constrained('mehnat_shartnoma_shablonlari')->nullOnDelete();
            $table->string('shartnoma_raqami', 50)->nullable();
            $table->longText('matn');
            $table->date('sana');
            $table->date('amal_qilish_boshlanish')->nullable();
            $table->date('amal_qilish_tugash')->nullable()->comment("NULL — muddatsiz");
            $table->enum('holat', ['loyiha', 'imzolangan', 'bekor_qilingan'])->default('loyiha');
            $table->foreignId('created_by')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('mehnat_shartnoma_shablonlari')->insert([
            'nomi'   => 'Standart mehnat shartnomasi',
            'matn'   => <<<'MATN'
MEHNAT SHARTNOMASI № {{shartnoma_raqami}}

{{tashkilot_nomi}} (bundan buyon — "Ish beruvchi") nomidan rahbar, bir tomondan, va
{{ism_familiya}} (bundan buyon — "Xodim"), passport ma'lumotlari: {{passport_malumot}},
yashash manzili: {{manzil}}, ikkinchi tomondan, ushbu mehnat shartnomasini quyidagilar
to'g'risida tuzdilar:

1. SHARTNOMA PREDMETI
1.1. Ish beruvchi Xodimni {{lavozim}} lavozimiga ishga qabul qiladi, Xodim esa o'z
zimmasiga yuklatilgan mehnat vazifalarini vijdonan bajarishga majburiyat oladi.
1.2. Xodimning ish boshlash sanasi: {{ishga_kirgan_sana}}.
1.3. Xodim {{filial_nomi}} filialida ishlaydi.

2. TOMONLARNING HUQUQ VA MAJBURIYATLARI
2.1. Xodim mehnat intizomiga, ichki tartib qoidalariga va mehnat muhofazasi
qoidalariga rioya qilishga majburdir.
2.2. Ish beruvchi Xodimga ish sharoitini, o'z vaqtida ish haqini to'lashni va
qonun hujjatlarida nazarda tutilgan boshqa kafolatlarni ta'minlashga majburdir.

3. ISH HAQI
3.1. Xodimning oylik lavozim oklad: {{oklad}} so'mni tashkil etadi.
3.2. Ish haqi har oy, qonun hujjatlarida belgilangan tartibda to'lanadi.

4. SHARTNOMA MUDDATI
4.1. Ushbu shartnoma {{shartnoma_sana}} sanasidan kuchga kiradi.

5. TOMONLARNING REKVIZITLARI VA IMZOLARI
Ish beruvchi: {{tashkilot_nomi}}
Xodim: {{ism_familiya}}, passport: {{passport_malumot}}, tel: {{telefon}}
MATN,
            'holat'      => 'faol',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('xodim_shartnomalari');
        Schema::dropIfExists('mehnat_shartnoma_shablonlari');
        Schema::dropIfExists('xodim_bonuslari');
        Schema::dropIfExists('bonus_turlari');
        Schema::dropIfExists('xodim_tatillar');

        Schema::table('ish_haqi_hisoblari', function (Blueprint $table) {
            $table->dropColumn(['qoshimcha_ish_haqi_summa', 'biriktirilgan_bonus_summa']);
        });

        Schema::table('xodim_ish_haqi_sozlama', function (Blueprint $table) {
            $table->dropColumn([
                'lavozim', 'telefon', 'manzil', 'passport_malumot',
                'ishga_kirgan_sana', 'ishdan_boshagan_sana',
                'qoshimcha_ish_haqi', 'qoshimcha_boshlanish_sana', 'qoshimcha_tugash_sana',
            ]);
        });

        Schema::table('foydalanuvchilar', function (Blueprint $table) {
            $table->dropColumn('tizimga_kirish_bormi');
        });
    }
};
