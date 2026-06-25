<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('litsenziya_faollashtirishlar', function (Blueprint $table) {
            $table->id();
            $table->string('kod', 40)->unique();
            $table->date('yangi_muddat');
            $table->unsignedBigInteger('xodim_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('litsenziya_faollashtirishlar');
    }
};
