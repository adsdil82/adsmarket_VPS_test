<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rollar', function (Blueprint $table) {
            $table->id();
            $table->string('kalit', 30)->unique();
            $table->string('nomi', 100);
            $table->string('icon', 30)->default('person');
            $table->boolean('tizim')->default(false);
            $table->unsignedSmallInteger('tartib')->default(0);
            $table->timestamps();
        });

        // Mavjud 6 ta tizim roli (kod ichida isAdmin()/isKassir() kabi joylarda ishlatiladi — o'chirib bo'lmaydi)
        $tizimRollar = [
            ['kalit' => 'admin',    'nomi' => 'Administrator', 'icon' => 'shield-lock',  'tartib' => 1],
            ['kalit' => 'menejer',  'nomi' => 'Menejer',       'icon' => 'briefcase',    'tartib' => 2],
            ['kalit' => 'kassir',   'nomi' => 'Kassir',        'icon' => 'cash-coin',    'tartib' => 3],
            ['kalit' => 'omborchi', 'nomi' => 'Omborchi',      'icon' => 'boxes',        'tartib' => 4],
            ['kalit' => 'hisobchi', 'nomi' => 'Hisobchi',      'icon' => 'calculator',   'tartib' => 5],
            ['kalit' => 'auditor',  'nomi' => 'Auditor',       'icon' => 'search',       'tartib' => 6],
        ];
        foreach ($tizimRollar as $r) {
            DB::table('rollar')->insert($r + ['tizim' => true, 'created_at' => now(), 'updated_at' => now()]);
        }

        // rol ustuni ENUM -> VARCHAR (yangi rollarni qabul qila olishi uchun)
        DB::statement("ALTER TABLE foydalanuvchilar MODIFY rol VARCHAR(30) NOT NULL DEFAULT 'hisobchi'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE foydalanuvchilar MODIFY rol ENUM('admin','menejer','kassir','hisobchi','omborchi','auditor') NOT NULL DEFAULT 'hisobchi'");
        Schema::dropIfExists('rollar');
    }
};
