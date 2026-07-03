<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harajat_turlari', function (Blueprint $table) {
            $table->id();
            $table->string('nomi')->unique();
            $table->foreignId('pul_kategoriya_id')->constrained('pul_kategoriyalar')->cascadeOnDelete();
            // Tur tanlanganda forma qaysi qo'shimcha maydonlarni so'rashi kerakligini bildiradi.
            $table->boolean('talab_xodim')->default(false);     // Masalan: "Ish haqi" — kimga to'langani
            $table->boolean('talab_schetchik')->default(false); // Masalan: "Elektr" — schyotchik raqami/ko'rsatkichi
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('harajatlar', function (Blueprint $table) {
            $table->foreignId('harajat_turi_id')->nullable()->after('turi')
                ->constrained('harajat_turlari')->nullOnDelete();
            // "Ish haqi" turi uchun — to'lov kimga tegishli ekani (xodimni
            // kiritgan kassir emas, balki maosh oluvchi xodim).
            $table->foreignId('tegishli_xodim_id')->nullable()->after('harajat_turi_id')
                ->constrained('foydalanuvchilar')->nullOnDelete();
            // "Elektr/Gaz/Suv" kabi kommunal turlar uchun schyotchik raqami.
            $table->string('schetchik_raqami', 100)->nullable()->after('tegishli_xodim_id');
        });

        // Mavjud (eski) harajat "turi" matnlariga mos standart harajat
        // turlarini avtomatik yaratamiz va CF-moddasiga bog'laymiz. Bu yerda
        // FAQAT harajat_turlari katalogi to'ldiriladi — mavjud harajatlar
        // o'ziga harajat_turi_id biriktirmaydi (buni admin "Bog'lash" sahifasidan
        // ataylab tasdiqlab bog'laydi, chunki bu Pul Oqimlariga tarixiy yozuv
        // qo'shish degani — avtomatik/sukut bo'yicha qilinmasligi kerak).
        $kategoriyalar = DB::table('pul_kategoriyalar')->pluck('id', 'kod');

        $xarita = [
            'Таъминотчилар:'                                      => ['CF-2300', false, false],
            'Дивидент: Дилшод'                                    => ['CF-2620', false, false],
            'Дивидент: Мухаббат амма'                              => ['CF-2620', false, false],
            'Дивидент: Элшоджон'                                  => ['CF-2620', false, false],
            'Инкасса:Дуконга кирим килинган пуллар'                => ['CF-1900', false, false],
            'Иш хаки: ?'                                          => ['CF-2110', true, false],
            'Иш хаки: Гуломжон'                                   => ['CF-2110', true, false],
            'Иш хаки: Дилшод'                                     => ['CF-2110', true, false],
            'Иш хаки: Жавохир'                                    => ['CF-2110', true, false],
            'Иш Хаки: Умиджон'                                    => ['CF-2110', true, false],
            'Иш хаки: Фаррош'                                     => ['CF-2110', true, false],
            'Иш хаки: Элшод'                                      => ['CF-2110', true, false],
            'Транзит счет: Мижозга пул кайтарилди'                 => ['CF-2700', false, false],
            'Фонд доставка ва дукон харажат'                       => ['CF-2510', false, false],
            'Хамкорбанк: Кредит'                                  => ['CF-2420', false, false],
            'Харажат: Банк учун килинган харажат'                  => ['CF-2410', false, false],
            'Харажат: Банкамат комиссяси'                          => ['CF-2410', false, false],
            'Харажат: Бонус ва премия'                             => ['CF-2120', true, false],
            'Харажат: Бошка харажат'                               => ['CF-2790', false, false],
            'Харажат: Доставка Мижоз етказиш'                      => ['CF-2510', false, false],
            'Харажат: Доставка таъминотчи оркал олинган товар'      => ['CF-2520', false, false],
            'Харажат: Закот пули'                                  => ['CF-2730', false, false],
            'Харажат: Ижара дукон'                                 => ['CF-2210', false, false],
            'Харажат: Концелария:'                                 => ['CF-2710', false, false],
            'Харажат: Овкат пули'                                  => ['CF-2720', false, false],
            'Харажат: Совгалар учун'                               => ['CF-2730', false, false],
            'Харажат: Солик'                                       => ['CF-2610', false, false],
            'Харажат: Телефон ва интернет'                         => ['CF-2230', false, true],
            'Харажат: Транспорт'                                   => ['CF-2510', false, false],
            'Харажат: Ундириш учун килинган харажат'                => ['CF-2790', false, false],
            'Харажат: Установка учун тулови'                       => ['CF-2790', false, false],
            'Харажат: Электр учун'                                 => ['CF-2220', false, true],
            'Харажат: Эхсон'                                       => ['CF-2730', false, false],
            'Харажат: Юристга'                                     => ['CF-2790', false, false],
        ];

        $sort = 0;
        foreach ($xarita as $nomi => [$kod, $talabXodim, $talabSchetchik]) {
            $kategoriyaId = $kategoriyalar[$kod] ?? $kategoriyalar['CF-2790'];
            DB::table('harajat_turlari')->insertOrIgnore([
                'nomi'              => $nomi,
                'pul_kategoriya_id' => $kategoriyaId,
                'talab_xodim'       => $talabXodim,
                'talab_schetchik'   => $talabSchetchik,
                'holat'             => 'faol',
                'sort_order'        => $sort++,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('harajatlar', function (Blueprint $table) {
            $table->dropColumn('schetchik_raqami');
            $table->dropConstrainedForeignId('tegishli_xodim_id');
            $table->dropConstrainedForeignId('harajat_turi_id');
        });
        Schema::dropIfExists('harajat_turlari');
    }
};
