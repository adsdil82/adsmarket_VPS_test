<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 010 — Activity Log jadvali (Audit log — barcha o'zgarishlar tarixi)
// owen-it/laravel-auditing paketi uchun jadval
// Qaysi foydalanuvchi, qaysi model, qaysi vaqtda, nima o'zgartirdi — hammasi saqlanadi
// Bu jadval avtomatik to'ldiriladi (laravel-auditing orqali)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();

            // Qaysi foydalanuvchi amal qildi (NULL = tizim/import)
            $table->unsignedBigInteger('user_id')->nullable();

            // Foydalanuvchi turi (ko'p model bo'lishi mumkin)
            $table->string('user_type')->nullable();

            // Qaysi model o'zgartirildi (masalan: App\Models\RegKredit)
            $table->string('auditable_type');

            // O'zgartirilgan model ID si
            $table->unsignedBigInteger('auditable_id');

            // Amal turi: created, updated, deleted, restored
            $table->string('event');

            // O'zgarishdan oldingi qiymatlar (JSON)
            $table->text('old_values')->nullable();

            // O'zgarishdan keyingi qiymatlar (JSON)
            $table->text('new_values')->nullable();

            // Qo'shimcha ma'lumot (URL, IP, User-Agent)
            $table->text('url')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 1023)->nullable();

            // Teglash uchun (ixtiyoriy)
            $table->string('tags')->nullable();

            $table->timestamp('created_at')->nullable();

            // Qidiruv indekslari
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'user_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
