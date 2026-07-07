<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etiketka_shablonlar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 100);
            $table->enum('turi', ['built_in', 'custom'])->default('custom');
            $table->string('reng_fon', 20)->default('#ffffff');
            $table->string('reng_matn', 20)->default('#000000');
            $table->string('reng_urgu', 20)->default('#dc2626');
            $table->string('belgi_matni', 40)->nullable(); // "AKSIYA", "YANGI", "CHEGIRMA" kabi burchak yorlig'i
            $table->json('joylashuv'); // {top:{x,y,w,h,fs}, inner:{...}, bottom:{...}, barcode:{...}}
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        $endi = now();
        $standartJoylashuv = json_encode([
            'top'     => ['x' => 1, 'y' => 1, 'w' => 28, 'h' => 6, 'fs' => 2.6],
            'inner'   => ['x' => 1, 'y' => 7, 'w' => 28, 'h' => 8, 'fs' => 2.2],
            'bottom'  => ['x' => 1, 'y' => 15, 'w' => 28, 'h' => 4, 'fs' => 3.2],
            'barcode' => ['x' => 1, 'y' => 19, 'w' => 28, 'h' => 9],
        ]);

        $shablonlar = [
            ['Standart',        '#ffffff', '#000000', '#15803d', null],
            ['Aksiya (qizil)',  '#fff1f2', '#000000', '#dc2626', 'AKSIYA'],
            ["Yangi mahsulot",  '#eff6ff', '#000000', '#2563eb', "YANGI"],
            ['Chegirma',        '#fffbeb', '#000000', '#d97706', 'CHEGIRMA'],
            ['Premium (oltin)', '#1c1917', '#fbbf24', '#fbbf24', 'PREMIUM'],
            ['Yashil (eko)',    '#f0fdf4', '#14532d', '#16a34a', null],
            ['Qora-oq minimal', '#ffffff', '#111111', '#111111', null],
            ["Nasiya bo'yicha", '#eef2ff', '#000000', '#4338ca', "NASIYA"],
            ['Bayram/sovg\'a',  '#fdf2f8', '#000000', '#db2777', "SOVG'A"],
            ["Super narx",      '#fef2f2', '#000000', '#b91c1c', "SUPER NARX"],
        ];

        foreach ($shablonlar as $s) {
            DB::table('etiketka_shablonlar')->insert([
                'nomi'        => $s[0],
                'turi'        => 'built_in',
                'reng_fon'    => $s[1],
                'reng_matn'   => $s[2],
                'reng_urgu'   => $s[3],
                'belgi_matni' => $s[4],
                'joylashuv'   => $standartJoylashuv,
                'created_by'  => null,
                'created_at'  => $endi,
                'updated_at'  => $endi,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('etiketka_shablonlar');
    }
};
