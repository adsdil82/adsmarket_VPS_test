<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mijozlar', function (Blueprint $table) {
            $table->date('passport_berilgan_sana')->nullable()->after('passport_berilgan_joy');
            $table->date('passport_amal_muddati')->nullable()->after('passport_berilgan_sana');
            $table->unsignedTinyInteger('oila_azolari_soni')->nullable()->after('lavozimi');
            $table->string('daromad_manbai', 200)->nullable()->after('oila_azolari_soni');
            $table->decimal('oylik_daromad', 15, 2)->nullable()->after('daromad_manbai');
            $table->decimal('oylik_harajat', 15, 2)->nullable()->after('oylik_daromad');
        });

        Schema::create('mijoz_kartalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mijoz_id')->constrained('mijozlar')->onDelete('cascade');
            $table->string('karta_raqami', 20);
            $table->unsignedTinyInteger('tartib')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mijoz_kartalar');

        Schema::table('mijozlar', function (Blueprint $table) {
            $table->dropColumn([
                'passport_berilgan_sana', 'passport_amal_muddati',
                'oila_azolari_soni', 'daromad_manbai', 'oylik_daromad', 'oylik_harajat',
            ]);
        });
    }
};
