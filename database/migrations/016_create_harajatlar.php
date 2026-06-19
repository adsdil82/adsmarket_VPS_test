<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('harajatlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->nullOnDelete();
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->date('sana');
            $table->string('turi', 255)->nullable()->comment('Harajat turi (masalan: Transport, Ijara)');
            $table->decimal('summa', 15, 2)->default(0);
            $table->string('mazmuni', 500)->nullable()->comment('Izoh/tafsilot');
            $table->unsignedInteger('eski_id')->nullable()->comment('Eski bazadagi ID (import uchun)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harajatlar');
    }
};
