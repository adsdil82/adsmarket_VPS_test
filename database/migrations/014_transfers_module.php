<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════
        // 1. OMBORLAR — filial ichida alohida ombor (mavjud yo'q)
        // ══════════════════════════════════════════════════════════════
        Schema::create('omborlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiallar')->cascadeOnDelete();
            $table->string('nomi', 100);
            $table->string('manzil', 200)->nullable();
            $table->string('mas_ul_shaxs', 150)->nullable();
            $table->enum('tur', ['asosiy','qoshimcha','karantin','qaytarish'])->default('asosiy');
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        // ── Har filial uchun default "Asosiy ombor" yaratamiz
        $filiallar = DB::table('filiallar')->get();
        foreach ($filiallar as $f) {
            DB::table('omborlar')->insert([
                'filial_id'  => $f->id,
                'nomi'       => 'Asosiy ombor',
                'tur'        => 'asosiy',
                'holat'      => 'faol',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── omborga_kirim va ombordan_chiqim ga ombor_id qo'shamiz
        Schema::table('omborga_kirim', function (Blueprint $table) {
            $table->foreignId('ombor_id')->nullable()->after('filial_id')
                  ->constrained('omborlar')->nullOnDelete();
        });
        Schema::table('ombordan_chiqim', function (Blueprint $table) {
            $table->foreignId('ombor_id')->nullable()->after('filial_id')
                  ->constrained('omborlar')->nullOnDelete();
        });

        // ══════════════════════════════════════════════════════════════
        // 2. OMBOR STOCK LEDGER — har bir tovar harakati yozuvi
        // ══════════════════════════════════════════════════════════════
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ombor_id')->constrained('omborlar')->cascadeOnDelete();
            $table->foreignId('tovar_id')->nullable()->constrained('tovar_katalog')->nullOnDelete();
            $table->string('tovar_nomi', 250);   // snapshot
            $table->enum('harakat', ['kirim','chiqim','transfer_out','transfer_in','qaytarish','tuzatish']);
            $table->decimal('miqdor', 15, 3);     // + yoki - emas, har doim musbat
            $table->decimal('qoldiq_oldin', 15, 3)->default(0);
            $table->decimal('qoldiq_keyin', 15, 3)->default(0);
            $table->decimal('tan_narx', 15, 2)->default(0);
            $table->string('manba_tur', 50)->nullable();   // 'omborga_kirim','filiallar_transfer','ombordan_chiqim'
            $table->unsignedBigInteger('manba_id')->nullable(); // shu jadval IDsi
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->string('izoh', 300)->nullable();
            $table->timestamps();

            $table->index(['ombor_id', 'created_at']);
            $table->index(['tovar_id', 'created_at']);
        });

        // ══════════════════════════════════════════════════════════════
        // 3. FILIALLAR TRANSFER — mavjud jadvalga ustun qo'shish
        //    (ombor_id, transfer raqami, sabab qo'shamiz)
        // ══════════════════════════════════════════════════════════════
        Schema::table('filiallar_transfer', function (Blueprint $table) {
            $table->string('transfer_raqam', 30)->nullable()->after('id')->unique();
            $table->foreignId('from_ombor_id')->nullable()->after('from_filial_id')
                  ->constrained('omborlar')->nullOnDelete();
            $table->foreignId('to_ombor_id')->nullable()->after('to_filial_id')
                  ->constrained('omborlar')->nullOnDelete();
            $table->string('sabab', 300)->nullable()->after('izoh');
            // holat enum ni kengaytirish
        });
        DB::statement("ALTER TABLE filiallar_transfer
            MODIFY COLUMN holat ENUM('qoralama','yuborildi','qabul_qilindi','bekor')
            NOT NULL DEFAULT 'qoralama'");

        // ══════════════════════════════════════════════════════════════
        // 4. KASSALAR — kassa pul ko'chirish uchun
        // ══════════════════════════════════════════════════════════════
        Schema::create('kassalar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('filial_id')->constrained('filiallar')->cascadeOnDelete();
            $table->string('nomi', 100);
            $table->enum('tur', ['naqd','bank','terminal','online'])->default('naqd');
            $table->decimal('qoldiq', 18, 2)->default(0);
            $table->string('valyuta', 5)->default('UZS');
            $table->enum('holat', ['faol','nofaol'])->default('faol');
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        // ── Har filial uchun default "Asosiy kassa"
        foreach ($filiallar as $f) {
            DB::table('kassalar')->insert([
                'filial_id'  => $f->id,
                'nomi'       => 'Asosiy kassa',
                'tur'        => 'naqd',
                'holat'      => 'faol',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // 5. KASSA TRANSFERLAR
        // ══════════════════════════════════════════════════════════════
        Schema::create('kassa_transferlar', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_raqam', 30)->unique();
            $table->foreignId('from_filial_id')->constrained('filiallar');
            $table->foreignId('from_kassa_id')->constrained('kassalar');
            $table->foreignId('to_filial_id')->constrained('filiallar');
            $table->foreignId('to_kassa_id')->constrained('kassalar');
            $table->decimal('summa', 18, 2);
            $table->string('valyuta', 5)->default('UZS');
            $table->decimal('kurs', 12, 2)->default(1);
            $table->decimal('summa_uzs', 18, 2)->default(0);
            $table->enum('holat', ['qoralama','yuborildi','qabul_qilindi','bekor'])->default('qoralama');
            $table->date('sana');
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar');
            $table->foreignId('tasdiqlagan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamp('tasdiqlangan_vaqt')->nullable();
            $table->string('izoh', 300)->nullable();
            $table->string('sabab', 300)->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════
        // 6. TA'MINOTCHIGA QAYTARISH (Supplier Return — transfer emas!)
        // ══════════════════════════════════════════════════════════════
        Schema::create('taminotchi_qaytarish', function (Blueprint $table) {
            $table->id();
            $table->string('hujjat_raqam', 50)->unique();
            $table->foreignId('taminotchi_id')->constrained('taminotchilar');
            $table->foreignId('ombor_id')->constrained('omborlar');
            $table->foreignId('filial_id')->constrained('filiallar');
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar');
            $table->foreignId('tasdiqlagan_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->date('sana');
            $table->decimal('jami_summa', 15, 2)->default(0);
            $table->enum('holat', ['qoralama','tasdiqlangan','qaytarildi','bekor'])->default('qoralama');
            $table->string('sabab', 300)->nullable();
            $table->text('izoh')->nullable();
            $table->timestamps();
        });

        Schema::create('taminotchi_qaytarish_qatorlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qaytarish_id')->constrained('taminotchi_qaytarish')->cascadeOnDelete();
            $table->foreignId('tovar_id')->nullable()->constrained('tovar_katalog')->nullOnDelete();
            $table->string('nomi', 250);
            $table->decimal('miqdor', 15, 3);
            $table->string('birlik', 20)->default('dona');
            $table->decimal('narx', 15, 2)->default(0);
            $table->decimal('jami', 15, 2)->default(0);
            $table->string('sabab', 300)->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════
        // 7. SHARTNOMA XODIM TARIXI (Assignment — transfer emas!)
        // ══════════════════════════════════════════════════════════════
        Schema::create('shartnoma_xodim_tarixi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shartnoma_id')->constrained('reg_kredit')->cascadeOnDelete();
            $table->foreignId('eski_xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->foreignId('yangi_xodim_id')->constrained('foydalanuvchilar');
            $table->foreignId('ozgartirgan_id')->constrained('foydalanuvchilar');
            $table->string('sabab', 500);
            $table->string('izoh', 500)->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════
        // 8. SHARTNOMA FILIAL TARIXI (Branch Move — ehtiyotkor!)
        // ══════════════════════════════════════════════════════════════
        Schema::create('shartnoma_filial_tarixi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shartnoma_id')->constrained('reg_kredit')->cascadeOnDelete();
            $table->foreignId('eski_filial_id')->constrained('filiallar');
            $table->foreignId('yangi_filial_id')->constrained('filiallar');
            $table->foreignId('ozgartirgan_id')->constrained('foydalanuvchilar');
            $table->string('sabab', 500);   // MAJBURIY
            $table->string('izoh', 500)->nullable();
            $table->boolean('tolovlar_yangi_filialda')->default(false);
            $table->timestamps();
        });

        // reg_kredit ga current_filial_id (ko'chirish uchun, asl filial_id saqlanadi)
        Schema::table('reg_kredit', function (Blueprint $table) {
            $table->foreignId('joriy_filial_id')->nullable()->after('filial_id')
                  ->constrained('filiallar')->nullOnDelete()
                  ->comment('Ko\'chirilgandan keyingi joriy filial. NULL = asl filialda');
            $table->foreignId('joriy_xodim_id')->nullable()->after('xodim_id')
                  ->constrained('foydalanuvchilar')->nullOnDelete()
                  ->comment('Qayta tayinlangandan keyingi joriy xodim. NULL = asl xodimda');
        });

        // ══════════════════════════════════════════════════════════════
        // 9. TOVAR GURUH TARIX (Kategoriya o'zgartirish audit)
        // ══════════════════════════════════════════════════════════════
        Schema::create('tovar_guruh_tarix', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tovar_id')->constrained('tovar_katalog')->cascadeOnDelete();
            $table->foreignId('eski_guruh_id')->nullable()->constrained('tovar_guruhlar')->nullOnDelete();
            $table->foreignId('yangi_guruh_id')->constrained('tovar_guruhlar');
            $table->foreignId('xodim_id')->constrained('foydalanuvchilar');
            $table->string('sabab', 300)->nullable();
            $table->timestamps();
        });

        // ══════════════════════════════════════════════════════════════
        // 10. TULOV TURLARI — kengaytirish (mavjud jadvalni ALTER)
        // ══════════════════════════════════════════════════════════════
        Schema::table('tulov_turlari', function (Blueprint $table) {
            $table->string('kod', 50)->nullable()->after('id');
            $table->enum('kategoriya', [
                'naqd','karta','bank','online','terminal',
                'chegirma','tuzatish','oldindan','penya',
                'asosiy_qarz','foiz','boshqa'
            ])->nullable()->after('nomi');
            $table->boolean('is_legacy')->default(false)->after('kategoriya')
                  ->comment('TRUE = eski tizimdan kelgan, yangi shartnomada default ko\'rinmasin');
            $table->boolean('affects_contract_balance')->default(true)->after('is_legacy');
            $table->boolean('affects_cash')->default(true)->after('affects_contract_balance');
            $table->boolean('affects_bank')->default(false)->after('affects_cash');
            $table->integer('sort_order')->default(100)->after('holat');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        // ── Mavjud tulov turlarini legacy deb belgilash
        DB::table('tulov_turlari')
            ->where('id', '>', 1)   // 1 = "Naqd" (asosiy)
            ->update(['is_legacy' => true, 'kategoriya' => 'naqd']);

        // ── Yangi standart to'lov turlarini qo'shamiz
        $yangilar = [
            ['kod'=>'cash',       'nomi'=>"Naqd to'lov",        'kategoriya'=>'naqd',     'sort_order'=>1],
            ['kod'=>'card_uzcard','nomi'=>'UZCARD',              'kategoriya'=>'karta',    'sort_order'=>2],
            ['kod'=>'card_humo',  'nomi'=>'HUMO',                'kategoriya'=>'karta',    'sort_order'=>3],
            ['kod'=>'bank',       'nomi'=>"Bank o'tkazma",       'kategoriya'=>'bank',     'sort_order'=>4],
            ['kod'=>'click',      'nomi'=>'Click/Payme',         'kategoriya'=>'online',   'sort_order'=>5],
            ['kod'=>'terminal',   'nomi'=>'POS Terminal',        'kategoriya'=>'terminal', 'sort_order'=>6],
            ['kod'=>'advance',    'nomi'=>"Oldindan to'lov",     'kategoriya'=>'oldindan', 'sort_order'=>7],
            ['kod'=>'discount',   'nomi'=>"Chegirma/Bonus",      'kategoriya'=>'chegirma', 'sort_order'=>8,
             'affects_contract_balance'=>false, 'affects_cash'=>false],
            ['kod'=>'correction', 'nomi'=>"Tuzatish to'lovi",   'kategoriya'=>'tuzatish', 'sort_order'=>9],
            ['kod'=>'penalty',    'nomi'=>"Penya to'lovi",       'kategoriya'=>'penya',    'sort_order'=>10],
        ];
        foreach ($yangilar as $t) {
            $exists = DB::table('tulov_turlari')->where('kod', $t['kod'])->exists();
            if (!$exists) {
                DB::table('tulov_turlari')->insert(array_merge([
                    'is_legacy'               => false,
                    'holat'                   => 'faol',
                    'affects_contract_balance'=> $t['affects_contract_balance'] ?? true,
                    'affects_cash'            => $t['affects_cash'] ?? true,
                    'affects_bank'            => false,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ], $t));
            }
        }

        // ══════════════════════════════════════════════════════════════
        // 11. TULOV TURI MAPPING — legacy → yangi
        // ══════════════════════════════════════════════════════════════
        Schema::create('tulov_turi_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legacy_id')->constrained('tulov_turlari');
            $table->foreignId('yangi_id')->constrained('tulov_turlari');
            $table->string('izoh', 200)->nullable();
            $table->timestamps();
        });

        // ── Avtomatik mapping: legacy Naqd → cash, Bank → bank, UZCARD → card_uzcard
        $mappings = [
            'Накд'        => 'cash',      'Naqd'        => 'cash',
            'Банк'        => 'bank',      'Bank'        => 'bank',
            'UZCARD'      => 'card_uzcard','HUMO'        => 'card_humo',
        ];
        foreach ($mappings as $legacyNomi => $yangiKod) {
            $legacyId = DB::table('tulov_turlari')
                ->where('nomi', 'LIKE', "%{$legacyNomi}%")->where('is_legacy', true)
                ->value('id');
            $yangiId  = DB::table('tulov_turlari')->where('kod', $yangiKod)->value('id');
            if ($legacyId && $yangiId) {
                DB::table('tulov_turi_mapping')->insertOrIgnore([
                    'legacy_id'  => $legacyId,
                    'yangi_id'   => $yangiId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('reg_kredit', fn($t) => $t->dropColumn(['joriy_filial_id','joriy_xodim_id']));
        Schema::dropIfExists('tulov_turi_mapping');
        Schema::dropIfExists('tovar_guruh_tarix');
        Schema::dropIfExists('shartnoma_filial_tarixi');
        Schema::dropIfExists('shartnoma_xodim_tarixi');
        Schema::dropIfExists('taminotchi_qaytarish_qatorlar');
        Schema::dropIfExists('taminotchi_qaytarish');
        Schema::dropIfExists('kassa_transferlar');
        Schema::dropIfExists('kassalar');
        Schema::dropIfExists('stock_ledger');
        Schema::dropIfExists('omborlar');
    }
};
