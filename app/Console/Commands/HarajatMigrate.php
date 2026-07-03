<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HarajatMigrate extends Command
{
    protected $signature   = 'cashflow:harajat-migrate {--dry-run : Faqat hisoblab ko\'rsatish}';
    protected $description = 'Mavjud harajatlar jadvalini pul_oqimlari ga ko\'chirish';

    // Eski Kirill tur → CF kategoriya_id mapping
    private array $turMapping = [
        // Mehnat haqi → CF-2110
        'Иш хаки'        => 11,
        'Иш Хаки'        => 11,
        'Иш хақи'        => 11,
        // Bonus → CF-2120
        'Харажат: Бонус'  => 12,
        // Ijara → CF-2210
        'Харажат: Ижара'  => 14,
        // Elektr → CF-2220
        'Харажат: Электр' => 15,
        // Internet/Tel → CF-2230
        'Харажат: Телефон'=> 16,
        // Transport → CF-2510
        'Харажат: Транспорт'  => 22,
        'Харажат: Доставка'   => 22,
        'Фонд доставка'       => 22,
        // Bank → CF-2410
        'Харажат: Банкамат'   => 19,
        'Харажат: Банк учун'  => 19,
        'Хамкорбанк: Кредит'  => 20,
        // Ta'minotchi → CF-2300
        'Таъминотчилар'   => 17,
        // Soliq → CF-2610
        'Харажат: Солик'  => 25,
        // Dividend → CF-2620
        'Дивидент'        => 26,
        // Ehson → CF-2730
        'Харажат: Эхсон'  => 30,
        'Харажат: Закот'  => 30,
        // Kancellyariya → CF-2710
        'Харажат: Концел' => 28,
        // Ovqat → CF-2720
        'Харажат: Овқат'  => 29,
        'Харажат: Овкат'  => 29,
        // Sovgalar → CF-2730
        'Харажат: Совга'  => 30,
        // Inkasso (kirim sifatida)
        'Инкасса'         => 8,
        'Транзит счет'    => 8,
        // Default chiqim → CF-2790
    ];

    private int $defaultCategoriya = 31; // CF-2790 Boshqa operatsion

    public function handle(): int
    {
        $dryRun    = $this->option('dry-run');
        $filialId  = DB::table('filiallar')->value('id');
        $xodimId   = DB::table('foydalanuvchilar')->where('rol', 'admin')->value('id');

        // Eski bazada harajatlar FAQAT naqd kassadan amalga oshirilgan
        // (terminal/bank kassasi tushunchasi bo'lmagan) — shuning uchun har
        // bir filial uchun aniq naqd kassani tanlaymiz, "jadvaldagi birinchi
        // kassa" kabi tasodifiy tanlovga tayanmaymiz.
        $naqdKassalar       = DB::table('kassalar')->where('tur', 'naqd')->pluck('id', 'filial_id');
        $defaultNaqdKassaId = $naqdKassalar->first() ?? DB::table('kassalar')->value('id');

        $harajatlar = DB::table('harajatlar')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('pul_oqimlari')
                  ->whereColumn('pul_oqimlari.eski_harajat_id', 'harajatlar.id');
            })
            ->orderBy('id')
            ->get();

        $this->info("Ko'chirish kerak: {$harajatlar->count()} ta harajat");

        if ($dryRun) {
            $this->warn('--dry-run: hech narsa saqlanmaydi');
            $sample = $harajatlar->take(5);
            foreach ($sample as $h) {
                $katId = $this->kategoriyaTanlash($h->turi ?? '');
                $yn    = $this->yunalishAniqla($h->turi ?? '', (float) $h->summa);
                $this->line("  #{$h->id} | {$yn} | kat={$katId} | {$h->turi} | ".number_format(abs($h->summa)));
            }
            return 0;
        }

        $chunk   = 200;
        $total   = 0;
        $bar     = $this->output->createProgressBar($harajatlar->count());

        foreach ($harajatlar->chunk($chunk) as $batch) {
            $rows = [];
            foreach ($batch as $h) {
                $katId    = $this->kategoriyaTanlash($h->turi ?? '');
                $yunalish = $this->yunalishAniqla($h->turi ?? '', (float) $h->summa);
                $summa    = abs($h->summa);
                $hFilial  = $h->filial_id ?? $filialId;
                $kassaId  = $naqdKassalar[$hFilial] ?? $defaultNaqdKassaId;

                $rows[] = [
                    'filial_id'      => $hFilial,
                    'kassa_id'       => $kassaId,
                    'kategoriya_id'  => $katId,
                    'xodim_id'       => $h->xodim_id ?? $xodimId,
                    'yunalish'       => $yunalish,
                    'sana'           => $h->sana,
                    'summa'          => $summa,
                    'izoh'           => $h->mazmuni,
                    'manba_tur'      => 'harajat',
                    'manba_id'       => $h->id,
                    'holat'          => 'tasdiqlangan',
                    'eski_harajat_id'=> $h->id,
                    'created_at'     => $h->created_at ?? now(),
                    'updated_at'     => now(),
                ];
                $bar->advance();
            }
            DB::table('pul_oqimlari')->insert($rows);
            $total += count($rows);
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$total} ta harajat pul_oqimlari ga ko'chirildi.");
        return 0;
    }

    private function kategoriyaTanlash(string $tur): int
    {
        foreach ($this->turMapping as $kalit => $katId) {
            if (str_contains($tur, $kalit)) {
                return $katId;
            }
        }
        return $this->defaultCategoriya;
    }

    /**
     * Yo'nalishni (kirim/chiqim) aniqlash — eski bazada manfiy summa har doim
     * "kirim" degani, lekin ba'zi qatorlar (masalan "Инкасса: Дуконга кирим
     * килинган пуллар") MUSBAT summa bilan kiritilgan, holbuki TURI nomi
     * o'zi aniq "kirim" ekanini bildiradi. Shuning uchun avval turi matniga,
     * keyin summa belgisiga qaraymiz.
     */
    private function yunalishAniqla(string $tur, float $summa): string
    {
        $turKichik = mb_strtolower($tur);

        // Turi matnida aniq "kirim/qaytarildi" so'zi bo'lsa — summa belgisidan
        // qat'i nazar KIRIM (masalan "Дуконга кирим килинган пуллар").
        $kirimMatnlari = ['дуконга кирим', 'кирим килинган', 'кирим qilingan'];
        foreach ($kirimMatnlari as $m) {
            if (str_contains($turKichik, $m)) return 'kirim';
        }

        return $summa < 0 ? 'kirim' : 'chiqim';
    }
}
