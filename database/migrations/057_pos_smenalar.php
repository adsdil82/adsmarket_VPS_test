<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_smenalar', function (Blueprint $table) {
            $table->id();
            $table->string('smena_raqami', 40)->unique();
            $table->foreignId('filial_id')->constrained('filiallar');
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar');
            $table->dateTime('ochilgan_vaqt');
            $table->dateTime('yopilgan_vaqt')->nullable();
            $table->decimal('dastlabki_qoldiq', 15, 2)->default(0);
            $table->decimal('hisoblangan_qoldiq', 15, 2)->nullable();
            $table->decimal('yakuniy_qoldiq', 15, 2)->nullable();
            $table->decimal('farq', 15, 2)->nullable();
            $table->decimal('topshirilgan_summa', 15, 2)->nullable();
            $table->enum('topshirish_holati', ['yoq', 'kutilmoqda', 'tasdiqlangan', 'rad_etildi'])->default('yoq');
            $table->foreignId('qabul_qilgan_id')->nullable()->constrained('foydalanuvchilar');
            $table->dateTime('qabul_vaqti')->nullable();
            $table->text('rad_sababi')->nullable();
            $table->enum('holat', ['ochiq', 'yopiq'])->default('ochiq');
            $table->text('izoh')->nullable();
            $table->timestamps();

            $table->index(['filial_id', 'holat']);
        });

        Schema::table('pos_sotuv', function (Blueprint $table) {
            $table->foreignId('smena_id')->nullable()->after('filial_id')->constrained('pos_smenalar');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sotuv', function (Blueprint $table) {
            $table->dropForeign(['smena_id']);
            $table->dropColumn('smena_id');
        });
        Schema::dropIfExists('pos_smenalar');
    }
};
