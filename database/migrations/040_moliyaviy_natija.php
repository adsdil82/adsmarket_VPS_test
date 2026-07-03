<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ══ 1. Bo'limlar — Foyda-Zarar hisobotining yirik bloklari ══════
        Schema::create('pl_bolimlar', function (Blueprint $table) {
            $table->id();
            $table->string('nomi', 150);
            $table->enum('ishora', ['musbat', 'manfiy'])->default('musbat'); // jamiga qo'shiladimi yoki ayiriladimi
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ══ 2. Qatorlar — har bo'lim ichidagi aniq ko'rsatkichlar ═══════
        Schema::create('pl_qatorlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bolim_id')->constrained('pl_bolimlar')->cascadeOnDelete();
            $table->string('nomi', 200);
            // Qanday hisoblanadi:
            //  avtomat_naqd_savdo    -> pos_sotuv.jami_tolov
            //  avtomat_naqd_tannarx  -> pos_tafsilot.miqdor * tovar_katalog.tan_narx
            //  avtomat_nasiya_savdo  -> reg_kredit.jami_summa (boshlanish_sana bo'yicha)
            //  avtomat_harajat_turi  -> harajatlar.summa WHERE harajat_turi_id=X
            //  qolda                 -> pl_qiymatlar jadvalidan qo'lda kiritilgan qiymat
            $table->enum('hisoblash_turi', [
                'avtomat_naqd_savdo', 'avtomat_naqd_tannarx', 'avtomat_nasiya_savdo',
                'avtomat_harajat_turi', 'qolda',
            ])->default('qolda');
            $table->foreignId('harajat_turi_id')->nullable()->constrained('harajat_turlari')->nullOnDelete();
            $table->enum('ishora', ['musbat', 'manfiy'])->default('musbat');
            $table->boolean('subtotal')->default(false); // "= Jami ..." qatorlari — o'zi kiritilmaydi, yig'indidan hisoblanadi
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('holat', ['faol', 'nofaol'])->default('faol');
            $table->timestamps();
        });

        // ══ 3. Qo'lda kiritilgan qiymatlar (oy-yil-filial bo'yicha) ═════
        Schema::create('pl_qiymatlari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qator_id')->constrained('pl_qatorlari')->cascadeOnDelete();
            $table->foreignId('filial_id')->nullable()->constrained('filiallar')->cascadeOnDelete();
            $table->unsignedSmallInteger('yil');
            $table->unsignedTinyInteger('oy'); // 1-12
            $table->decimal('summa', 18, 2)->default(0);
            $table->string('izoh', 300)->nullable();
            $table->foreignId('xodim_id')->nullable()->constrained('foydalanuvchilar')->nullOnDelete();
            $table->timestamps();

            $table->unique(['qator_id', 'filial_id', 'yil', 'oy'], 'pl_qiymat_unique');
        });

        // ══ 4. Bo'lim va qatorlarni Excel namunasiga mos urug'lash ═════
        $bolimlar = [
            ['nomi' => "Savdo hajmi (brutto)",        'ishora' => 'musbat', 'sort_order' => 1],
            ['nomi' => "Tannarx",                      'ishora' => 'manfiy', 'sort_order' => 2],
            ['nomi' => "Savdo harajatlari",             'ishora' => 'manfiy', 'sort_order' => 3],
            ['nomi' => "Operatsion harajatlar",         'ishora' => 'manfiy', 'sort_order' => 4],
            ['nomi' => "Boshqa daromad va harajatlar",  'ishora' => 'manfiy', 'sort_order' => 5],
            ['nomi' => "Soliq va rezerv",               'ishora' => 'manfiy', 'sort_order' => 6],
        ];
        $bolimIds = [];
        foreach ($bolimlar as $b) {
            $bolimIds[$b['nomi']] = DB::table('pl_bolimlar')->insertGetId(array_merge($b, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }

        $harajatTuri = fn($nomi) => DB::table('harajat_turlari')->where('nomi', $nomi)->value('id');

        $qatorlar = [
            // ── Savdo hajmi ──
            ["Savdo hajmi (brutto)", "Nasiya savdo hajmi", 'avtomat_nasiya_savdo', null, 'musbat', 1],
            ["Savdo hajmi (brutto)", "(-) Qaytgan tovarlar nasiya savdosidan", 'qolda', null, 'manfiy', 2],
            ["Savdo hajmi (brutto)", "Naqd savdo hajmi", 'avtomat_naqd_savdo', null, 'musbat', 3],
            ["Savdo hajmi (brutto)", "(-) Qaytgan tovar naqd savdosidan", 'qolda', null, 'manfiy', 4],

            // ── Tannarx ──
            ["Tannarx", "Nasiya sotilgan tovar tannarxi", 'qolda', null, 'musbat', 1],
            ["Tannarx", "Naqd sotilgan tovar tannarxi", 'avtomat_naqd_tannarx', null, 'musbat', 2],
            ["Tannarx", "Tannarx harajati (еtkazib kelish bilan bog'liq)", 'qolda', null, 'musbat', 3],

            // ── Savdo harajatlari ──
            ["Savdo harajatlari", "Mijozga oldindan yechganda kilingan chegirma", 'qolda', null, 'musbat', 1],
            ["Savdo harajatlari", "Bonusga berilgan tovar summasi", 'qolda', null, 'musbat', 2],
            ["Savdo harajatlari", "Mijozga еtkazib berish va urnatish", 'avtomat_harajat_turi', $harajatTuri('Харажат: Доставка Мижоз етказиш'), 'musbat', 3],
            ["Savdo harajatlari", "Savdoga bog'langan bonus ish haqi", 'qolda', null, 'musbat', 4],
            ["Savdo harajatlari", "Aksiya chegirmasi", 'qolda', null, 'musbat', 5],
            ["Savdo harajatlari", "Dukon xarajat fondi", 'avtomat_harajat_turi', $harajatTuri("Фонд доставка ва дукон харажат"), 'musbat', 6],
            ["Savdo harajatlari", "Sotilgan tovar remonti uchun harajat", 'qolda', null, 'musbat', 7],

            // ── Operatsion harajatlar ──
            ["Operatsion harajatlar", "Ish haqi (oklad)", 'qolda', null, 'musbat', 1],
            ["Operatsion harajatlar", "Bayram va tug'ilgan kun harajati", 'qolda', null, 'musbat', 2],
            ["Operatsion harajatlar", "Ijara harajati", 'avtomat_harajat_turi', $harajatTuri("Харажат: Ижара дукон"), 'musbat', 3],
            ["Operatsion harajatlar", "Ovqat puli", 'avtomat_harajat_turi', $harajatTuri("Харажат: Овкат пули"), 'musbat', 4],
            ["Operatsion harajatlar", "Bankamat komissiyasi", 'avtomat_harajat_turi', $harajatTuri("Харажат: Банкамат комиссяси"), 'musbat', 5],
            ["Operatsion harajatlar", "Bank xizmati", 'avtomat_harajat_turi', $harajatTuri("Харажат: Банк учун килинган харажат"), 'musbat', 6],
            ["Operatsion harajatlar", "Kommunal harajat (elektr, suv, gaz)", 'avtomat_harajat_turi', $harajatTuri("Харажат: Электр учун"), 'musbat', 7],
            ["Operatsion harajatlar", "Elektron xizmat komissiyasi", 'qolda', null, 'musbat', 8],
            ["Operatsion harajatlar", "Kantselyariya va ofis harajati", 'avtomat_harajat_turi', $harajatTuri("Харажат: Концелария:"), 'musbat', 9],
            ["Operatsion harajatlar", "Dukon remont va qayta jihozlash", 'qolda', null, 'musbat', 10],
            ["Operatsion harajatlar", "Dukon uchun mayda xarajatlar", 'avtomat_harajat_turi', $harajatTuri("Харажат: Бошка харажат"), 'musbat', 11],
            ["Operatsion harajatlar", "Telefon, SMS, Internet, Payme", 'avtomat_harajat_turi', $harajatTuri("Харажат: Телефон ва интернет"), 'musbat', 12],

            // ── Boshqa daromad va harajatlar ──
            ["Boshqa daromad va harajatlar", "Ehson puli", 'avtomat_harajat_turi', $harajatTuri("Харажат: Эхсон"), 'musbat', 1],
            ["Boshqa daromad va harajatlar", "Zakot puli", 'avtomat_harajat_turi', $harajatTuri("Харажат: Закот пули"), 'musbat', 2],
            ["Boshqa daromad va harajatlar", "Safar xarajatlari", 'qolda', null, 'musbat', 3],
            ["Boshqa daromad va harajatlar", "Dividend", 'qolda', null, 'musbat', 4],
            ["Boshqa daromad va harajatlar", "Bank kredit foizi", 'qolda', null, 'musbat', 5],
            ["Boshqa daromad va harajatlar", "Kredit sug'urtasi uchun to'lov", 'qolda', null, 'musbat', 6],
            ["Boshqa daromad va harajatlar", "Sovg'alar (yangi yil va h.k.)", 'avtomat_harajat_turi', $harajatTuri("Харажат: Совгалар учун"), 'musbat', 7],

            // ── Soliq va rezerv ──
            ["Soliq va rezerv", "Soliq to'lovi va ijtimoiy soliq", 'avtomat_harajat_turi', $harajatTuri("Харажат: Солик"), 'musbat', 1],
            ["Soliq va rezerv", "Kechirilgan kredit rezervi", 'qolda', null, 'musbat', 2],
        ];

        foreach ($qatorlar as [$bolimNomi, $nomi, $hisoblashTuri, $harajatTuriId, $ishora, $sort]) {
            DB::table('pl_qatorlari')->insert([
                'bolim_id'        => $bolimIds[$bolimNomi],
                'nomi'            => $nomi,
                'hisoblash_turi'  => $hisoblashTuri,
                'harajat_turi_id' => $harajatTuriId,
                'ishora'          => $ishora,
                'subtotal'        => false,
                'sort_order'      => $sort,
                'holat'           => 'faol',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pl_qiymatlari');
        Schema::dropIfExists('pl_qatorlari');
        Schema::dropIfExists('pl_bolimlar');
    }
};
