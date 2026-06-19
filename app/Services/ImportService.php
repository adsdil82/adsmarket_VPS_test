<?php

namespace App\Services;

use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\Grafik;
use App\Models\Harajat;
use App\Models\Mijoz;
use App\Models\OldinTulov;
use App\Models\RegKredit;
use App\Models\Taminotchi;
use App\Models\TaminotKirim;
use App\Models\TaminotKirimQator;
use App\Models\Tovar;
use App\Models\Tulov;
use App\Models\TulovTuri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ImportService — Eski MySQL bazasidan ma'lumotlarni yangi tizimga ko'chirish.
 *
 * Ishlatilish tartibi:
 *   1. php artisan nasiya:import filiallar
 *   2. php artisan nasiya:import tulov-turlari
 *   3. php artisan nasiya:import mijozlar
 *   4. php artisan nasiya:import kreditlar
 *   5. php artisan nasiya:import tovarlar
 *   6. php artisan nasiya:import grafik
 *   7. php artisan nasiya:import tulovlar
 *   8. php artisan nasiya:import oldin-tulovlar
 *   9. php artisan nasiya:import taminotchilar
 *  10. php artisan nasiya:import taminot-kirimlar
 *  11. php artisan nasiya:import harajatlar
 *
 * MUHIM: Har bir bosqich avvalgisiga bog'liq!
 * Jadvallar bo'shatiladi va qayta to'ldiriladi (idempotent).
 */
class ImportService
{
    // ─── Filiallar ────────────────────────────────────────────────

    /**
     * Filiallarni seed qilish (import emas — qo'lda kiritilgan).
     * FiliallarSeeder chaqiradi.
     */
    public function filiallarImport(array $filiallar): void
    {
        foreach ($filiallar as $filial) {
            Filial::updateOrCreate(['id' => $filial['id']], $filial);
        }
        Log::info('Filiallar import qilindi', ['soni' => count($filiallar)]);
    }

    // ─── To'lov turlari ───────────────────────────────────────────

    /**
     * tulov_turlari jadvalidagi noyob nomlarni import qilish.
     * SQL fayldagi tulov_turi_nomi ustunidan o'qiladi.
     *
     * @param array $nomlar ['Накд Город', 'UZCARD', ...]
     */
    public function tulovTurlariImport(array $nomlar): void
    {
        foreach ($nomlar as $nomi) {
            TulovTuri::firstOrCreate(['nomi' => $nomi], ['holat' => 'faol']);
        }
        Log::info('To\'lov turlari import qilindi', ['soni' => count($nomlar)]);
    }

    // ─── Mijozlar ─────────────────────────────────────────────────

    /**
     * mijoz_mysql.sql faylidagi ma'lumotlarni import qilish.
     *
     * Ustun mapping (eski → yangi):
     *   filial_kod        → filial_id  (Filial::where('id', filial_kod))
     *   id_mj             → eski_id
     *   Familiya_mj       → familiya
     *   Ismi_mj           → ism
     *   Otaismi_mj        → otasining_ismi
     *   tel_mj            → telefon
     *   ser_mj            → passport_seriya
     *   nomer_mj          → passport_raqam
     *   adres_mj          → manzil
     *   tugilganyil       → tug_sana
     *   Ish_joyi_mj       → ish_joyi
     *   lavozimi_mj       → lavozimi
     *
     * @param iterable $qatorlar — DB::table('temp_mijoz')->cursor()
     */
    public function mijozlarImport(iterable $qatorlar, int &$count = 0): void
    {
        // Filiallar id → id map (filial_kod == filial.id)
        $filialMap = Filial::pluck('id', 'id')->toArray();

        foreach ($qatorlar as $q) {
            $filialId = $filialMap[$q->filial_kod] ?? null;
            if (!$filialId) continue;

            Mijoz::updateOrCreate(
                ['eski_id' => $q->id_mj, 'filial_id' => $filialId],
                [
                    'filial_id'       => $filialId,
                    'familiya'        => $q->Familiya_mj ?? '',
                    'ism'             => $q->Ismi_mj ?? '',
                    'otasining_ismi'  => $q->Otaismi_mj ?? null,
                    'telefon'         => $q->tel_mj ?? '',
                    'passport_seriya' => $q->ser_mj ?? null,
                    'passport_raqam'  => $q->nomer_mj ?? null,
                    'manzil'          => $q->adres_mj ?? null,
                    'tug_sana'        => $this->sanaTuzat($q->tugilganyil ?? null),
                    'ish_joyi'        => $q->Ish_joyi_mj ?? null,
                    'lavozimi'        => $q->lavozimi_mj ?? null,
                    'holat'           => 'faol',
                ]
            );
            $count++;
        }

        Log::info('Mijozlar import qilindi', ['soni' => $count]);
    }

    // ─── Kreditlar (reg_kredit) ───────────────────────────────────

    /**
     * reg_kredit_mysql.sql → reg_kredit jadvali.
     *
     * Ustun mapping:
     *   filial_kod        → filial_id
     *   id_kredit         → eski_id
     *   id_mj             → mijoz_id (eski_id orqali)
     *   ...
     *
     * @param iterable $qatorlar
     * @param int $defaultXodimId — import paytida foydalanuvchi ID
     */
    public function kreditlarImport(iterable $qatorlar, int $defaultXodimId, int &$count = 0): void
    {
        // Eski mijoz id → yangi mijoz id map
        $mijozMap = Mijoz::pluck('id', 'eski_id')->toArray();

        // Filiallar map
        $filialMap = Filial::pluck('id', 'id')->toArray();

        $now   = now()->toDateTimeString();
        $batch = [];

        foreach ($qatorlar as $q) {
            $filialId = $filialMap[$q->filial_kod] ?? null;
            $mijozId  = $mijozMap[$q->mijoz_id] ?? null;
            if (!$filialId || !$mijozId) continue;

            $jamiSumma  = (float) ($q->summa ?? 0);
            $qoldiqQarz = (float) ($q->qoldiq_suma ?? 0);

            $boshlangichTolov = is_array($q->oldidan_tulov ?? null) ? 0.0 : (float) ($q->oldidan_tulov ?? 0);
            $kreditSumma = max(0, $jamiSumma - $boshlangichTolov);

            $muddatiOy = (int) ($q->kr_produkt ?? 0);
            if ($muddatiOy < 1 || $muddatiOy > 12) $muddatiOy = 12;
            $foizStavka = 10 + $muddatiOy * 5;

            $batch[] = [
                'eski_id'             => $q->id_kredit,
                'shartnoma_raqam'     => 'IMP-' . intval($q->filial_kod) . '-' . intval($q->id_kredit),
                'mijoz_id'            => $mijozId,
                'filial_id'           => $filialId,
                'xodim_id'            => $defaultXodimId,
                'jami_summa'          => $jamiSumma,
                'boshlangich_tolov'   => $boshlangichTolov,
                'kredit_summa'        => $kreditSumma,
                'tolov_qilingan'      => max(0, $jamiSumma - $qoldiqQarz),
                'qoldiq_qarz'         => $qoldiqQarz,
                'boshlanish_sana'     => $this->sanaTuzat($q->sha_sana ?? null),
                'tugash_sana'         => $this->sanaTuzat($q->oxir_sana ?? $q->end_sana ?? null),
                'oylik_tolov_miqdori' => round($kreditSumma / $muddatiOy, 2),
                'muddati_oy'          => $muddatiOy,
                'foiz_stavka'         => $foizStavka,
                'holat'               => $this->kreditHolatMap($q->sts ?? null),
                'created_at'          => $now,
                'updated_at'          => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('reg_kredit')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('reg_kredit')->insertOrIgnore($batch);
            $count += count($batch);
        }

        Log::info('Kreditlar import qilindi', ['soni' => $count]);
    }

    // ─── Tovarlar ─────────────────────────────────────────────────

    /**
     * ch_kt_tv_mysql.sql → tovarlar jadvali.
     *
     * @param iterable $qatorlar
     */
    public function tovarlarImport(iterable $qatorlar, int &$count = 0): void
    {
        $kreditMap = RegKredit::pluck('id', 'eski_id')->toArray();
        $now       = now()->toDateTimeString();
        $batch     = [];

        foreach ($qatorlar as $q) {
            $kreditId = $kreditMap[$q->ch_reg_kt] ?? null;
            if (!$kreditId) continue;

            $soni = (float) ($q->soni ?? 1);
            $narx = (float) ($q->narx ?? 0);

            $batch[] = [
                'eski_id'       => $q->id_op ?? null,
                'reg_kredit_id' => $kreditId,
                'nomi'          => $q->tovar_nomi ?? 'Nomalum tovar',
                'soni'          => $soni,
                'narx'          => $narx,
                'jami_narx'     => $soni * $narx,
                'barkod'        => $q->BarKodKRMKt ?? null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('tovarlar')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('tovarlar')->insertOrIgnore($batch);
            $count += count($batch);
        }

        Log::info('Tovarlar import qilindi', ['soni' => $count]);
    }

    // ─── Grafik ───────────────────────────────────────────────────

    /**
     * grafik_mysql.sql → grafik jadvali.
     * NULL qiymatli to'lovlar ham saqlanadi.
     *
     * @param iterable $qatorlar
     */
    public function grafikImport(iterable $qatorlar, int &$count = 0): void
    {
        $kreditMap = RegKredit::pluck('id', 'eski_id')->toArray();
        $now       = now()->toDateTimeString();
        $batch     = [];

        foreach ($qatorlar as $q) {
            $kreditId = $kreditMap[$q->reg_kt_id] ?? null;
            if (!$kreditId) continue;

            $batch[] = [
                'eski_id'        => $q->grafik_kod ?? null,
                'reg_kredit_id'  => $kreditId,
                'oylik_tartib'   => $q->oylik_tartib ?? 0,
                'tolov_sana'     => $this->sanaTuzat($q->tolov_sana ?? null),
                'tolov_summa'    => $q->tolov_summa ?? null,
                'qoldiq_suma'    => $q->qoldiq_suma ?? null,
                'holat'          => 'tolanmagan',
                'tolangan_summa' => 0,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            if (count($batch) >= 1000) {
                DB::table('grafik')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('grafik')->insertOrIgnore($batch);
            $count += count($batch);
        }

        Log::info('Grafik import qilindi', ['soni' => $count]);
    }

    // ─── To'lovlar ────────────────────────────────────────────────

    /**
     * tulov_kt_mysql.sql → tulovlar jadvali.
     *
     * @param iterable $qatorlar
     */
    public function tulovlarImport(iterable $qatorlar, int $defaultXodimId, int &$count = 0): void
    {
        $kreditMap    = RegKredit::pluck('id', 'eski_id')->toArray();
        $tulovTuriMap = TulovTuri::pluck('id', 'nomi')->toArray();
        $now          = now()->toDateTimeString();
        $batch        = [];

        foreach ($qatorlar as $q) {
            $kreditId    = $kreditMap[$q->id_kr_reg] ?? null;
            if (!$kreditId) continue;

            $tulovTuriId = $tulovTuriMap[$q->tulov_turi_nomi] ?? null;
            if (!$tulovTuriId && !empty($q->tulov_turi_nomi)) {
                $tur = TulovTuri::firstOrCreate(['nomi' => $q->tulov_turi_nomi], ['holat' => 'nofaol']);
                $tulovTuriId = $tur->id;
                $tulovTuriMap[$q->tulov_turi_nomi] = $tulovTuriId;
            }
            if (!$tulovTuriId) continue;

            $sana = $this->sanaTuzat($q->sana ?? null);
            $batch[] = [
                'eski_id'       => $q->id_tl_kt ?? null,
                'reg_kredit_id' => $kreditId,
                'grafik_id'     => null,
                'xodim_id'      => $defaultXodimId,
                'tulov_turi_id' => $tulovTuriId,
                'summa'         => $q->summa ?? 0,
                'tolov_sana'    => $sana,
                'qabul_vaqt'    => $sana,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('tulovlar')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('tulovlar')->insertOrIgnore($batch);
            $count += count($batch);
        }

        Log::info('To\'lovlar import qilindi', ['soni' => $count]);
    }

    // ─── Oldindan to'lovlar ───────────────────────────────────────

    /**
     * tulov_oldindan_KT_mysql.sql → oldindan_tulov jadvali.
     *
     * @param iterable $qatorlar
     */
    public function oldinTulovlarImport(iterable $qatorlar, int $defaultXodimId, int &$count = 0): void
    {
        $kreditMap    = RegKredit::pluck('id', 'eski_id')->toArray();
        $tulovTuriMap = TulovTuri::pluck('id', 'nomi')->toArray();
        $now          = now()->toDateTimeString();
        $batch        = [];

        foreach ($qatorlar as $q) {
            $kreditId = $kreditMap[$q->reg_kredit] ?? null;
            if (!$kreditId) continue;

            $tulovTuriId = $tulovTuriMap[$q->tulov_turi_nomi] ?? null;
            if (!$tulovTuriId && !empty($q->tulov_turi_nomi)) {
                $tur = TulovTuri::firstOrCreate(['nomi' => $q->tulov_turi_nomi], ['holat' => 'nofaol']);
                $tulovTuriId = $tur->id;
                $tulovTuriMap[$q->tulov_turi_nomi] = $tulovTuriId;
            }
            if (!$tulovTuriId) continue;

            $sana = $this->sanaTuzat($q->sana ?? null);
            $batch[] = [
                'eski_id'       => $q->idOl ?? null,
                'reg_kredit_id' => $kreditId,
                'xodim_id'      => $defaultXodimId,
                'tulov_turi_id' => $tulovTuriId,
                'summa'         => $q->Tulov_old_sum ?? 0,
                'tolov_sana'    => $sana,
                'qabul_vaqt'    => $sana,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('oldindan_tulov')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('oldindan_tulov')->insertOrIgnore($batch);
            $count += count($batch);
        }

        Log::info('Oldindan to\'lovlar import qilindi', ['soni' => $count]);
    }

    // ─── Ta'minotchilar (kONTR_AGENT) ───────────────────────────────

    /**
     * temp_taminotchi → taminotchilar jadvali.
     *
     * Ustun mapping:
     *   nomi          → nomi
     *   manzil        → manzil
     *   bank_nomi     → bank_nomi
     *   bank_hisob    → bank_hisob
     *   mfo           → mfo
     *   inn           → inn
     *   telefon       → telefon
     *   kontakt_shaxs → kontakt_shaxs
     *   izoh          → izoh
     *
     * @param iterable $qatorlar — DB::table('temp_taminotchi')->cursor()
     */
    public function taminotchilarImport(iterable $qatorlar, int &$count = 0): void
    {
        $filialMap = Filial::pluck('id', 'id')->toArray();

        foreach ($qatorlar as $q) {
            $filialId = $filialMap[$q->filial_kod] ?? null;
            if (!$filialId) continue;

            Taminotchi::updateOrCreate(
                ['nomi' => $q->nomi, 'filial_id' => $filialId],
                [
                    'nomi'          => $q->nomi,
                    'manzil'        => $q->manzil ?? null,
                    'bank_nomi'     => $q->bank_nomi ?? null,
                    'bank_hisob'    => $q->bank_hisob ?? null,
                    'mfo'           => $q->mfo ?? null,
                    'inn'           => $q->inn ?? null,
                    'telefon'       => $q->telefon ?? null,
                    'kontakt_shaxs' => $q->kontakt_shaxs ?? null,
                    'izoh'          => $q->izoh ?? null,
                    'holat'         => 'faol',
                    'filial_id'     => $filialId,
                ]
            );
            $count++;
        }

        Log::info('Ta\'minotchilar import qilindi', ['soni' => $count]);
    }

    // ─── Ta'minotdan kirimlar (reg_prixod + kirim_tv) ───────────────

    /**
     * temp_taminot_kirim → taminot_kirimlar,
     * temp_taminot_kirim_qator → taminot_kirim_qatorlar.
     *
     * Ta'minotchi eski_id → yangi id mosligi temp_taminotchi orqali
     * (nomi ustuni bo'yicha) topiladi. taminot_kirimlar.hujjat_raqam
     * "IMP-PRIXOD-{eski_id}" ko'rinishida saqlanadi — keyinroq
     * qatorlarni bog'lash uchun ishlatiladi.
     *
     * @param iterable $kirimQatorlar — DB::table('temp_taminot_kirim')->cursor()
     * @param iterable $tafsilotQatorlar — DB::table('temp_taminot_kirim_qator')->cursor()
     */
    public function taminotKirimlarImport(iterable $kirimQatorlar, iterable $tafsilotQatorlar, int $defaultXodimId, int &$count = 0, int &$qatorCount = 0): void
    {
        $filialMap = Filial::pluck('id', 'id')->toArray();

        // Eski taminotchi eski_id → nomi → yangi taminotchi id
        $eskiNomiMap   = DB::table('temp_taminotchi')->pluck('nomi', 'eski_id')->toArray();
        $taminotchiMap = Taminotchi::pluck('id', 'nomi')->toArray();

        $now = now()->toDateTimeString();

        foreach ($kirimQatorlar as $q) {
            $filialId = $filialMap[$q->filial_kod] ?? null;
            if (!$filialId) continue;

            $nomi = $eskiNomiMap[$q->taminotchi_eski_id] ?? null;
            $taminotchiId = $nomi ? ($taminotchiMap[$nomi] ?? null) : null;
            if (!$taminotchiId) continue;

            $jamiSumma = (float) ($q->summa ?? 0);

            TaminotKirim::updateOrCreate(
                ['hujjat_raqam' => 'IMP-PRIXOD-' . intval($q->eski_id)],
                [
                    'taminotchi_id' => $taminotchiId,
                    'filial_id'     => $filialId,
                    'xodim_id'      => $defaultXodimId,
                    'hujjat_raqam'  => 'IMP-PRIXOD-' . intval($q->eski_id),
                    'kirim_sana'    => $this->sanaTuzat($q->sana ?? null) ?? now()->toDateString(),
                    'jami_summa'    => $jamiSumma,
                    'tolangan'      => 0,
                    'qoldiq'        => $jamiSumma,
                    'holat'         => 'kutilmoqda',
                    'izoh'          => $q->izoh ?? null,
                ]
            );
            $count++;
        }

        // Qatorlarni bog'lash uchun hujjat_raqam → kirim_id map
        $kirimIdMap = TaminotKirim::where('hujjat_raqam', 'like', 'IMP-PRIXOD-%')
            ->pluck('id', 'hujjat_raqam')
            ->toArray();

        $batch = [];
        foreach ($tafsilotQatorlar as $q) {
            $hujjat = 'IMP-PRIXOD-' . intval($q->prixod_eski_id);
            $kirimId = $kirimIdMap[$hujjat] ?? null;
            if (!$kirimId) continue;

            $soni = (float) ($q->soni ?? 0);
            $narx = (float) ($q->narx ?? 0);

            $batch[] = [
                'kirim_id'   => $kirimId,
                'tovar_id'   => null,
                'nomi'       => $q->tovar_nomi ?? 'Nomalum tovar',
                'miqdor'     => $soni,
                'birlik'     => 'dona',
                'narx'       => $narx,
                'jami'       => $soni * $narx,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('taminot_kirim_qatorlar')->insert($batch);
                $qatorCount += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('taminot_kirim_qatorlar')->insert($batch);
            $qatorCount += count($batch);
        }

        Log::info('Ta\'minot kirimlari import qilindi', ['kirim' => $count, 'qatorlar' => $qatorCount]);
    }

    // ─── Harajatlar (XARAJAT) ───────────────────────────────────────

    /**
     * temp_harajat → harajatlar jadvali.
     *
     * @param iterable $qatorlar — DB::table('temp_harajat')->cursor()
     */
    public function harajatlarImport(iterable $qatorlar, int $defaultXodimId, int &$count = 0): void
    {
        $filialMap = Filial::pluck('id', 'id')->toArray();
        $now       = now()->toDateTimeString();
        $batch     = [];

        foreach ($qatorlar as $q) {
            $filialId = $filialMap[$q->filial_kod] ?? null;
            if (!$filialId) continue;

            $batch[] = [
                'filial_id'  => $filialId,
                'xodim_id'   => $defaultXodimId,
                'sana'       => $this->sanaTuzat($q->sana ?? null) ?? now()->toDateString(),
                'turi'       => $q->turi ?? null,
                'summa'      => $q->summa ?? 0,
                'mazmuni'    => $q->mazmuni ?? null,
                'eski_id'    => $q->eski_id ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= 500) {
                DB::table('harajatlar')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            DB::table('harajatlar')->insertOrIgnore($batch);
            $count += count($batch);
        }

        Log::info('Harajatlar import qilindi', ['soni' => $count]);
    }

    // ─── Yordamchi metodlar ───────────────────────────────────────

    /** Sana formatini tekshirish va tuzatish */
    private function sanaTuzat(?string $sana): ?string
    {
        if (empty($sana)) return null;
        try {
            $dt = \Carbon\Carbon::parse($sana);
            // 1900 yildan oldingi sanalar noto'g'ri
            if ($dt->year < 1900 || $dt->year > 2100) return null;
            return $dt->toDateString();
        } catch (\Exception) {
            return null;
        }
    }

    /** Eski holat qiymatlarini yangi ENUM ga moslashtirish */
    private function kreditHolatMap(?string $eskiHolat): string
    {
        return match(mb_strtolower(trim($eskiHolat ?? ''))) {
            'faol', 'active', '1'  => 'faol',
            'yopiq', 'closed', '0' => 'yopilgan',
            'muzlatilgan', 'frozen' => 'muzlatilgan',
            default                => 'faol',
        };
    }
}
