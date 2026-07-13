<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autopay_shartnomalar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reg_kredit_id');
            $table->unsignedBigInteger('mijoz_id');
            $table->string('loan_id', 100)->unique();
            $table->enum('holat', ['kutilmoqda', 'faol', 'toxtatilgan', 'xato'])->default('kutilmoqda');
            $table->boolean('auto_yoqilgan')->default(false);
            $table->decimal('oxirgi_debt', 15, 2)->default(0);
            $table->text('xato_matni')->nullable();
            $table->unsignedBigInteger('yuborgan_id')->nullable();
            $table->timestamp('yuborilgan_vaqt')->nullable();
            $table->timestamps();

            $table->foreign('reg_kredit_id')->references('id')->on('reg_kredit')->onDelete('cascade');
            $table->foreign('mijoz_id')->references('id')->on('mijozlar')->onDelete('cascade');
            $table->foreign('yuborgan_id')->references('id')->on('foydalanuvchilar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autopay_shartnomalar');
    }
};
