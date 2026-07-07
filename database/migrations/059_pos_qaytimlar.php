<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_qaytimlar', function (Blueprint $table) {
            $table->id();
            $table->string('qaytim_raqami', 40)->unique();
            $table->foreignId('sotuv_id')->constrained('pos_sotuv');
            $table->foreignId('smena_id')->constrained('pos_smenalar'); // qaytim QILINAYOTGAN paytdagi joriy ochiq smena
            $table->foreignId('filial_id')->constrained('filiallar');
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar');
            $table->date('sana');
            $table->enum('tolov_turi', ['naqd', 'plastik'])->default('naqd');
            $table->decimal('jami_summa', 15, 2);
            $table->enum('sabab', ['fikr_ozgardi', 'nosoz_mahsulot', 'notogri_mahsulot', 'boshqa'])->default('boshqa');
            $table->string('mijoz_ism', 200)->nullable();
            $table->text('izoh')->nullable();
            $table->enum('holat', ['tugallangan', 'bekor'])->default('tugallangan');
            $table->timestamps();
        });

        Schema::create('pos_qaytim_tafsilot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qaytim_id')->constrained('pos_qaytimlar')->cascadeOnDelete();
            $table->foreignId('tafsilot_id')->constrained('pos_tafsilot'); // asl sotuv qatori
            $table->foreignId('tovar_id')->constrained('tovar_katalog');
            $table->decimal('miqdor', 10, 3);
            $table->decimal('narx', 15, 2);
            $table->decimal('jami_summa', 15, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_qaytim_tafsilot');
        Schema::dropIfExists('pos_qaytimlar');
    }
};
