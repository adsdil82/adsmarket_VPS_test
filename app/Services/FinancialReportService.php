<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Moliyaviy hisobotlar (Balans, Cash Flow, Foyda-zarar, Xarajatlar,
 * Daromadlar, Filiallar kesimida, Solishtirma) uchun yagona xizmat qatlami —
 * "Hisobot konstruktori"dagi "Moliyaviy" tab shu servis orqali ishlaydi.
 *
 * Har bir metod bir xil "bank hisobot" shaklida qaytaradi:
 *   ['bolimlar' => [['nomi'=>, 'qatorlar'=>[['nomi'=>,'summa'=>,'mock'=>bool]], 'jami'=>]],
 *    'yakuniy'  => [['nomi'=>,'summa'=>]],
 *    'ogohlantirish' => ?string]
 *
 * MUHIM: Balans, Cash Flow va Foyda-zarar — o'z algoritmini o'ylab topmaydi,
 * balki lug'aviy "Pul Oqimlari" bo'limidagi mavjud, haqiqiy tizimlarni
 * qayta ishlatadi: BLReportService (Balans hisoboti), PLReportService
 * (Moliyaviy natija), va real pul_oqimlari/pul_kategoriyalar ledgeri
 * (Pul oqimi hisoboti). Shu sabab bu yerdagi va alohida "Pul Oqimlari"
 * menyusidagi sonlar har doim bir xil bo'lishi kerak — bittasi o'zgarsa,
 * ikkinchisi ham avtomatik yangilanadi. 'mock' => true faqat BLQator'ning
 * "qolda" (qo'lda kiritiladigan) moddalarini bildiradi — bu haqiqiy
 * ma'lumot, shunchaki avtomatik hisoblanmaydi (masalan Asosiy vositalar).
 */
class FinancialReportService
{
    private const XARAJAT_KATEGORIYALARI = [
        'ish_haqi'  => ['Ish haqi harajatlari',      ['иш хаки']],
        'ijara'     => ['Ijara harajatlari',         ['ижара']],
        'kommunal'  => ['Kommunal harajatlar',       ['электр', 'сув', 'коммунал', 'телефон', 'интернет']],
        'soliq'     => ['Soliq harajatlari',         ['солик', 'закот']],
        'marketing' => ['Marketing harajatlari',     ['аксия', 'реклама', 'маркетинг', 'совга']],
        'transport' => ['Transport harajatlari',     ['транспорт', 'доставка']],
        'dasturiy'  => ["Dasturiy ta'minot harajatlari", ['дастур', 'софт', 'лицензия', 'установка']],
        'xojalik'   => ["Xo'jalik harajatlari",      ['концелария', 'овкат', 'хужалик']],
    ];

    private function xarajatKategoriyasi(string $nomi): array
    {
        $n = mb_strtolower($nomi);
        foreach (self::XARAJAT_KATEGORIYALARI as $key => $def) {
            [$label, $sozlar] = $def;
            foreach ($sozlar as $soz) {
                if (str_contains($n, $soz)) return [$key, $label];
            }
        }
        return ['boshqa', 'Boshqa harajatlar'];
    }

    /** ── 1. Xarajatlar hisoboti (haqiqiy ma'lumot: harajatlar jadvali) ─── */
    public function xarajatlar(string $dan, string $gacha, ?int $filialId): array
    {
        $rows = DB::table('harajatlar as h')
            ->leftJoin('harajat_turlari as ht', 'ht.id', '=', 'h.harajat_turi_id')
            ->leftJoin('foydalanuvchilar as x', 'x.id', '=', 'h.xodim_id')
            ->leftJoin('filiallar as f', 'f.id', '=', 'h.filial_id')
            ->when($filialId, fn($q) => $q->where('h.filial_id', $filialId))
            ->whereBetween('h.sana', [$dan, $gacha])
            ->selectRaw("h.sana, COALESCE(ht.nomi,'Nomsiz') as modda, h.kassa_turi, f.kod as filial,
                COALESCE(x.ism_familiya,'—') as xodim, h.summa, h.mazmuni as izoh")
            ->orderByDesc('h.sana')->limit(5000)->get();

        $guruhlangan = [];
        foreach ($rows as $r) {
            [$key, $label] = $this->xarajatKategoriyasi($r->modda);
            $guruhlangan[$key]['nomi'] ??= $label;
            $guruhlangan[$key]['summa'] = ($guruhlangan[$key]['summa'] ?? 0) + (float) $r->summa;
        }

        $bolimQatorlari = [];
        foreach (self::XARAJAT_KATEGORIYALARI as $key => $def) {
            $label = is_array($def) ? $def[0] : $def;
            $bolimQatorlari[] = ['nomi' => $label, 'summa' => $guruhlangan[$key]['summa'] ?? 0, 'mock' => false];
        }
        $bolimQatorlari[] = ['nomi' => 'Boshqa harajatlar', 'summa' => $guruhlangan['boshqa']['summa'] ?? 0, 'mock' => false];

        $jami = array_sum(array_column($bolimQatorlari, 'summa'));

        return [
            'bolimlar' => [['nomi' => 'Harajat kategoriyalari', 'qatorlar' => $bolimQatorlari, 'jami' => $jami]],
            'yakuniy'  => [['nomi' => 'Jami harajat', 'summa' => $jami]],
            'tafsilot' => $rows,
            'ogohlantirish' => null,
        ];
    }

    /** ── 2. Daromadlar hisoboti (real: savdo; boshqalar hali kuzatilmaydi) ── */
    public function daromadlar(string $dan, string $gacha, ?int $filialId): array
    {
        $naqdSavdo = (float) DB::table('pos_sotuv')
            ->where('holat', 'tugallangan')
            ->whereBetween('sana', [$dan, $gacha])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->sum('jami_tolov');

        $nasiyaSavdo = (float) DB::table('reg_kredit')
            ->whereBetween('boshlanish_sana', [$dan, $gacha])
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->sum('jami_summa');

        $qatorlar = [
            ['nomi' => 'Savdo daromadlari (naqd)',   'summa' => $naqdSavdo,   'mock' => false],
            ['nomi' => 'Savdo daromadlari (nasiya)', 'summa' => $nasiyaSavdo, 'mock' => false],
            ['nomi' => 'Foiz daromadlari',           'summa' => 0, 'mock' => true],
            ['nomi' => 'Komissiya daromadlari',      'summa' => 0, 'mock' => true],
            ['nomi' => 'Xizmat ko\'rsatish daromadlari', 'summa' => 0, 'mock' => true],
            ['nomi' => 'Boshqa daromadlar',          'summa' => 0, 'mock' => true],
        ];
        $jami = array_sum(array_column($qatorlar, 'summa'));

        return [
            'bolimlar' => [['nomi' => 'Daromad turlari', 'qatorlar' => $qatorlar, 'jami' => $jami]],
            'yakuniy'  => [['nomi' => 'Jami daromad', 'summa' => $jami]],
            'ogohlantirish' => "Foiz/komissiya/xizmat daromadlari alohida hisobda kuzatilmayapti — hozircha 0 ko'rsatilgan.",
        ];
    }

    /** ── 3. Foyda va zarar — mavjud PLReportService'ga asoslanadi, har bir
     *  PL bo'limi (Savdo hajmi, Tannarx, Harajatlar va h.k.) alohida bo'lim
     *  sifatida, o'z moddalari va jami bilan chiqadi. ── */
    public function foydaZarar(string $dan, string $gacha, ?int $filialId): array
    {
        $pl = new PLReportService();
        $yillik = $this->oyGaBolingan($dan, $gacha);

        $bolimlarNatija = []; // bolim_id => ['nomi','ishora','qatorlar'=>[nomi=>summa],'jami']
        foreach ($yillik as $yil => $oylar) {
            $hisobot = $pl->hisobot($yil, $filialId);
            foreach ($hisobot['bolimlar'] as $bolim) {
                $bolimlarNatija[$bolim->id]['nomi']   ??= $bolim->nomi;
                $bolimlarNatija[$bolim->id]['ishora'] ??= $bolim->ishora;
                $bolimlarNatija[$bolim->id]['jami']   ??= 0.0;
                foreach ($bolim->qatorlar as $qator) {
                    $summa = 0.0;
                    foreach ($oylar as $oy) $summa += ($qator->oylik[$oy] ?? 0);
                    $bolimlarNatija[$bolim->id]['qatorlar'][$qator->nomi]
                        = ($bolimlarNatija[$bolim->id]['qatorlar'][$qator->nomi] ?? 0) + $summa;
                }
                foreach ($oylar as $oy) {
                    $bolimlarNatija[$bolim->id]['jami'] += ($hisobot['bolim_jami'][$bolim->id]['oylik'][$oy] ?? 0);
                }
            }
        }

        $bolimlar = [];
        $sofFoyda = 0.0;
        foreach ($bolimlarNatija as $b) {
            $qatorlar = [];
            foreach ($b['qatorlar'] as $nomi => $summa) {
                $qatorlar[] = ['nomi' => $nomi, 'summa' => $summa, 'mock' => false];
            }
            $sofFoyda += ($b['ishora'] === 'manfiy' ? -1 : 1) * $b['jami'];
            $bolimlar[] = ['nomi' => $b['nomi'], 'qatorlar' => $qatorlar, 'jami' => $b['jami']];
        }

        return [
            'bolimlar' => $bolimlar,
            'yakuniy'  => [['nomi' => 'Sof foyda / zarar', 'summa' => $sofFoyda]],
            'ogohlantirish' => null,
        ];
    }

    /** ── 4. Cash Flow — mavjud "Pul oqimi hisoboti" bilan bir xil manba: real pul_oqimlari
     *  ledgeri + pul_kategoriyalar ierarxiyasi (Pul Oqimlari > Pul oqimi hisoboti sahifasi). ── */
    public function cashFlow(string $dan, string $gacha, ?int $filialId): array
    {
        $asosiyKategoriyalar = DB::table('pul_kategoriyalar')
            ->whereNull('ota_id')->where('holat', 'faol')->orderBy('sort_order')->get();

        $bolimlar = [];
        $sofKirim = 0.0;
        $sofChiqim = 0.0;

        foreach ($asosiyKategoriyalar as $kat) {
            $bolalar = DB::table('pul_kategoriyalar')->where('ota_id', $kat->id)->orderBy('sort_order')->get();
            $qatorManbalari = $bolalar->isEmpty() ? collect([$kat]) : $bolalar;

            $qatorlar = [];
            $bolimJami = 0.0;
            foreach ($qatorManbalari as $q) {
                $summa = (float) DB::table('pul_oqimlari')
                    ->where('holat', 'tasdiqlangan')
                    ->where('kategoriya_id', $q->id)
                    ->whereBetween('sana', [$dan, $gacha])
                    ->when($filialId, fn($qq) => $qq->where('filial_id', $filialId))
                    ->sum('summa');
                $qatorlar[] = ['nomi' => "{$q->kod} — {$q->nomi}", 'summa' => $summa, 'mock' => false];
                $bolimJami += $summa;
            }

            if ($kat->yunalish === 'kirim') $sofKirim += $bolimJami; else $sofChiqim += $bolimJami;

            $bolimlar[] = [
                'nomi'     => "{$kat->kod} — {$kat->nomi}" . ($kat->yunalish === 'chiqim' ? ' (chiqim)' : ' (kirim)'),
                'qatorlar' => $qatorlar,
                'jami'     => $bolimJami,
            ];
        }

        $boshlangichQoldiq = (float) (DB::table('pul_oqimlari')
            ->where('holat', 'tasdiqlangan')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->where('sana', '<', $dan)
            ->selectRaw("SUM(CASE WHEN yunalish='kirim' THEN summa ELSE -summa END) as q")
            ->value('q') ?? 0);

        $yakuniyQoldiq = $boshlangichQoldiq + $sofKirim - $sofChiqim;

        return [
            'bolimlar' => $bolimlar,
            'yakuniy'  => [
                ['nomi' => 'Davr boshidagi qoldiq', 'summa' => $boshlangichQoldiq],
                ['nomi' => 'Davr kirimi',           'summa' => $sofKirim],
                ['nomi' => 'Davr chiqimi',          'summa' => -$sofChiqim],
                ['nomi' => 'Sof pul oqimi',         'summa' => $sofKirim - $sofChiqim],
                ['nomi' => 'Davr oxiridagi qoldiq', 'summa' => $yakuniyQoldiq],
            ],
            'ogohlantirish' => null,
        ];
    }

    /** ── 5. Balans — mavjud "Balans hisoboti" bilan bir xil manba: real BLReportService
     *  (Pul Oqimlari > Balans hisoboti sahifasidagi bo'lim/moddalar bilan aynan bir xil). ── */
    public function balans(string $sana, ?int $filialId): array
    {
        $bl = app(BLReportService::class);
        $h  = $bl->hisobot($sana, $filialId);

        $bolimlar = [];
        foreach ($h['bolimlar'] as $bolim) {
            $qatorlar = $bolim->qatorlar->map(fn($q) => [
                'nomi'  => $q->nomi,
                'summa' => $q->qiymat,
                'mock'  => $q->hisoblash_turi === 'qolda',
            ])->toArray();
            $bolimlar[] = ['nomi' => $bolim->nomi, 'qatorlar' => $qatorlar, 'jami' => $h['bolim_jami'][$bolim->id] ?? 0];
        }

        $ogohlantirish = null;
        if (abs($h['balans_farqi']) > 0.01) {
            $ogohlantirish = "Aktiv = Majburiyat + Kapital tenglamasi farq bilan chiqdi ("
                . number_format($h['balans_farqi'], 0, '.', ' ') . " so'm). \"Pul oqimlari > Balans "
                . "hisoboti\" sahifasida qo'lda kiritiladigan moddalarga (Asosiy vositalar, Ustav "
                . "kapitali va h.k.) tegishli qiymatlarni kiritib, farqni tekshiring.";
        }

        return [
            'bolimlar' => $bolimlar,
            'yakuniy'  => [
                ['nomi' => 'Aktivlar jami',                 'summa' => $h['jami_aktivlar']],
                ['nomi' => 'Majburiyatlar jami',             'summa' => $h['jami_majburiyat']],
                ['nomi' => 'Kapital jami',                   'summa' => $h['jami_kapital']],
                ['nomi' => 'Passivlar jami (Majburiyat + Kapital)', 'summa' => $h['jami_passiv']],
                ['nomi' => 'Nazorat farqi (Aktiv - Majburiyat - Kapital)', 'summa' => $h['balans_farqi']],
            ],
            'ogohlantirish' => $ogohlantirish,
        ];
    }

    /** ── 6. Filiallar kesimida moliyaviy natija ─────────────────────── */
    public function filiallarKesimida(string $dan, string $gacha): array
    {
        $filiallar = DB::table('filiallar')->where('holat', 'faol')->get(['id', 'nomi', 'kod']);
        $qatorlar = [];
        foreach ($filiallar as $f) {
            $fz = $this->foydaZarar($dan, $gacha, $f->id);
            $qatorlar[] = [
                'nomi'  => $f->nomi . " ({$f->kod})",
                'summa' => $fz['yakuniy'][0]['summa'] ?? 0,
                'mock'  => false,
            ];
        }
        $jami = array_sum(array_column($qatorlar, 'summa'));

        return [
            'bolimlar' => [['nomi' => "Filiallar bo'yicha sof foyda/zarar", 'qatorlar' => $qatorlar, 'jami' => $jami]],
            'yakuniy'  => [['nomi' => 'Barcha filiallar jami', 'summa' => $jami]],
            'ogohlantirish' => null,
        ];
    }

    /** ── 7. Solishtirma hisobot — istalgan hisobot turini ikki davr uchun solishtiradi ── */
    public function solishtirma(string $hisobotTuri, string $dan, string $gacha, string $oldinDan, string $oldinGacha, ?int $filialId): array
    {
        $joriy   = $this->hisobotniChaqir($hisobotTuri, $dan, $gacha, $filialId);
        $oldingi = $this->hisobotniChaqir($hisobotTuri, $oldinDan, $oldinGacha, $filialId);

        $oldingiXarita = [];
        $oldingiBolimJami = [];
        foreach ($oldingi['bolimlar'] as $b) {
            foreach ($b['qatorlar'] as $q) {
                $oldingiXarita[$q['nomi']] = $q['summa'];
            }
            $oldingiBolimJami[$b['nomi']] = $b['jami'];
        }

        $bolimlar = [];
        foreach ($joriy['bolimlar'] as $b) {
            $qatorlar = [];
            foreach ($b['qatorlar'] as $q) {
                $joriySumma   = (float) $q['summa'];
                $oldingiSumma = (float) ($oldingiXarita[$q['nomi']] ?? 0);
                $farq         = $joriySumma - $oldingiSumma;
                $farqFoizi    = $oldingiSumma != 0 ? round($farq / abs($oldingiSumma) * 100, 1) : null;
                $qatorlar[] = [
                    'nomi' => $q['nomi'], 'joriy' => $joriySumma, 'oldingi' => $oldingiSumma,
                    'farq' => $farq, 'farq_foizi' => $farqFoizi, 'mock' => $q['mock'] ?? false,
                ];
            }

            $bolimJami = null;
            if ($b['jami'] !== null) {
                $bJoriy   = (float) $b['jami'];
                $bOldingi = (float) ($oldingiBolimJami[$b['nomi']] ?? 0);
                $bFarq    = $bJoriy - $bOldingi;
                $bolimJami = [
                    'joriy' => $bJoriy, 'oldingi' => $bOldingi, 'farq' => $bFarq,
                    'farq_foizi' => $bOldingi != 0 ? round($bFarq / abs($bOldingi) * 100, 1) : null,
                ];
            }

            $bolimlar[] = ['nomi' => $b['nomi'], 'qatorlar' => $qatorlar, 'jami' => $bolimJami];
        }

        return [
            'bolimlar' => $bolimlar,
            'yakuniy'  => [],
            'ogohlantirish' => $joriy['ogohlantirish'] ?? null,
        ];
    }

    private function hisobotniChaqir(string $turi, string $dan, string $gacha, ?int $filialId): array
    {
        return match ($turi) {
            'xarajatlar' => $this->xarajatlar($dan, $gacha, $filialId),
            'daromadlar' => $this->daromadlar($dan, $gacha, $filialId),
            'cash_flow'  => $this->cashFlow($dan, $gacha, $filialId),
            'balans'     => $this->balans($gacha, $filialId),
            default      => $this->foydaZarar($dan, $gacha, $filialId),
        };
    }

    /** Sana oralig'ini yillar bo'yicha [yil => [oy1, oy2, ...]] shakliga bo'ladi. */
    private function oyGaBolingan(string $dan, string $gacha): array
    {
        $natija = [];
        $joriy = strtotime(date('Y-m-01', strtotime($dan)));
        $oxiri = strtotime(date('Y-m-01', strtotime($gacha)));
        while ($joriy <= $oxiri) {
            $yil = (int) date('Y', $joriy);
            $oy  = (int) date('n', $joriy);
            $natija[$yil][] = $oy;
            $joriy = strtotime('+1 month', $joriy);
        }
        return $natija;
    }
}
