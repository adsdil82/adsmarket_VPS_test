<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hisoblar_rejasi', function (Blueprint $table) {
            $table->id();
            $table->string('hisob_raqam', 10)->unique()->comment('Masalan: 5010, 5110-1');
            $table->string('nomi', 250);
            $table->enum('turi', ['faol', 'passiv', 'faol-passiv'])->default('faol');
            $table->tinyInteger('daraja')->default(1)->comment('1=asosiy, 2=sub, 3=sub-sub');
            $table->foreignId('ota_id')->nullable()->constrained('hisoblar_rejasi')->nullOnDelete();
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        Schema::create('yangi_tulov_turlari', function (Blueprint $table) {
            $table->id();
            $table->string('kod', 30)->unique()->comment('Masalan: KASSA, TERMINAL1');
            $table->string('nomi', 150);
            $table->enum('kategoriya', ['kassa', 'terminal', 'bank', 'boshqa'])->default('boshqa');
            $table->foreignId('debet_hisob_id')->nullable()->constrained('hisoblar_rejasi')->nullOnDelete();
            $table->foreignId('kredit_hisob_id')->nullable()->constrained('hisoblar_rejasi')->nullOnDelete();
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->text('izoh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yangi_tulov_turlari');
        Schema::dropIfExists('hisoblar_rejasi');
    }
};
