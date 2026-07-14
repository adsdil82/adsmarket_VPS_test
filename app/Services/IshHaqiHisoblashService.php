<?php

namespace App\Services;

use App\Models\Foydalanuvchi;
use App\Models\Harajat;
use App\Models\HarajatTuri;
use App\Models\IshHaqiAvans;
use App\Models\IshHaqiGlobalSozlama;
use App\Models\IshHaqiHisob;
use App\Models\RegKredit;
use App\Models\Tulov;
use App\Models\XodimBonus;
use App\Models\XodimDavomat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Xodimlar ish haqi hisob-kitobi:
 *  - Oklad qismi — davomat (tabel) foiziga proportsional.
 *  - Komissiya bonus — xodimga tegishli shartnomalardan shu oyda yig'ilgan
 *    to'lovlarning bonus_foizi qismi.
 *  - Reja bonusi — oylik reja bajarilsa (min/max oraliqda proporsional), bonus.
 *  - Soliq / boshqa ushlanma — jami hisoblangan (gross) summadan foiz sifatida
 *    avtomatik ushlanadi (xodim sozlamasi bo'lmasa — global stavka ishlatiladi,
 *    hisoblash vaqtida "suratga olinadi"/snapshot qilinadi).
 *  - Avans — oy davomida oldindan berilgan to'lovlar, yakuniy to'lashda ayiriladi.
 *  - To'lash — Harajatlar moduliga (va shu orqali Pul Oqimlariga) yozadi,
 *    xuddi HarajatController::store() bilan bir xil TulovService orqali.
 */
class IshHaqiHisoblashService
{
    public function __construct(private TulovService $tulovService) {}

    private const HARAJAT_TURI_NOMI = 'Ish haqi (avtomatik hisoblash)';

    /** Bitta xodim uchun bitta oy hisob-kitobini hisoblaydi (yoki qayta hisoblaydi). */
    public function hisoblaOy(Foydalanuvchi $xodim, int $yil, int $oy): IshHaqiHisob
    {
        $mavjud = IshHaqiHisob::where('xodim_id', $xodim->id)
            ->where('yil', $yil)->where('oy', $oy)->first();

        if ($mavjud && $mavjud->holat === 'tolandi') {
            // To'langan hisobni qayta hisoblab o'zgartirib bo'lmaydi (moliyaviy yozuv allaqachon yaratilgan).
            return $mavjud;
        }

        $sozlama = $xodim->ishHaqiSozlama;
        $global  = IshHaqiGlobalSozlama::ol();

        $oklad        = (float) ($sozlama->oklad ?? 0);
        $bonusFoizi   = (float) ($sozlama->bonus_foizi ?? 0);
        $rejaSumma    = (float) ($sozlama->oylik_reja_summa ?? 0);
        $rejaMinFoizi = (float) ($sozlama->reja_min_foizi ?? 80);
        $rejaMaxFoizi = (float) ($sozlama->reja_max_foizi ?? 100);
        $rejaBonus    = (float) ($sozlama->reja_bonus_summa ?? 0);

        // Xodimda alohida belgilangan bo'lsa o'shani, aks holda global stavkani ishlatamiz.
        $soliqFoizi         = $sozlama?->soliq_foizi ?? $global->soliq_foizi;
        $boshqaUshlanmaFoizi = $sozlama?->boshqa_ushlanma_foizi ?? $global->boshqa_ushlanma_foizi;
        $soliqFoizi          = (float) $soliqFoizi;
        $boshqaUshlanmaFoizi = (float) $boshqaUshlanmaFoizi;

        $boshi  = Carbon::create($yil, $oy, 1)->startOfMonth();
        $oxiri  = (clone $boshi)->endOfMonth();

        // Davomat
        $davomatlar = XodimDavomat::where('xodim_id', $xodim->id)
            ->whereBetween('sana', [$boshi->toDateString(), $oxiri->toDateString()])
            ->get();

        $ishKunlari = $davomatlar->whereIn('holat', XodimDavomat::ISH_KUNI_HOLATLARI)->count();
        $kelganKunlar = $davomatlar->whereIn('holat', XodimDavomat::KELGAN_HOLATLARI)->count();
        $davomatFoizi = $ishKunlari > 0 ? round($kelganKunlar / $ishKunlari * 100, 2) : 0;
        $okladQismi   = round($oklad * $davomatFoizi / 100, 2);

        // Xodimga tegishli shartnomalardan shu oyda yig'ilgan to'lovlar
        $kreditIds = RegKredit::where(function ($q) use ($xodim) {
            $q->where('joriy_xodim_id', $xodim->id)
              ->orWhere(function ($qq) use ($xodim) {
                  $qq->whereNull('joriy_xodim_id')->where('xodim_id', $xodim->id);
              });
        })->pluck('id');

        $yigilganTolovlar = (float) Tulov::whereIn('reg_kredit_id', $kreditIds)
            ->whereBetween('tolov_sana', [$boshi->toDateString(), $oxiri->toDateString()])
            ->sum('summa');

        $komissiyaBonus = round($yigilganTolovlar * $bonusFoizi / 100, 2);

        // Reja bonusi — bajarilish foiziga proporsional: reja_min_foizi dan past — 0,
        // reja_max_foizi dan yuqori (yoki teng) — to'liq bonus, oraliqda — chiziqli proporsional.
        $bajarilishFoizi = $rejaSumma > 0 ? round($yigilganTolovlar / $rejaSumma * 100, 2) : 0;
        $rejaBajarildimi = $rejaSumma > 0 && $bajarilishFoizi >= $rejaMinFoizi;

        if ($rejaSumma <= 0 || $rejaMaxFoizi <= $rejaMinFoizi) {
            $rejaBonusSumma = 0;
        } elseif ($bajarilishFoizi >= $rejaMaxFoizi) {
            $rejaBonusSumma = $rejaBonus;
        } elseif ($bajarilishFoizi <= $rejaMinFoizi) {
            $rejaBonusSumma = 0;
        } else {
            $ulush = ($bajarilishFoizi - $rejaMinFoizi) / ($rejaMaxFoizi - $rejaMinFoizi);
            $rejaBonusSumma = round($rejaBonus * $ulush, 2);
        }

        // Muddatli qo'shimcha ish haqi (masalan lavozim ustamasi) — shu oy oralig'ida amalda bo'lsa.
        $qoshimchaIshHaqiSumma = $sozlama && $sozlama->qoshimchaAmaldaMi($boshi, $oxiri)
            ? (float) $sozlama->qoshimcha_ish_haqi
            : 0;

        // Xodimga biriktirilgan (shablon asosidagi) faol bonuslar — shu oy oralig'ida amalda bo'lganlari.
        $biriktirilganBonusSumma = 0;
        $biriktirilganBonuslar = XodimBonus::with('bonusTuri')
            ->where('xodim_id', $xodim->id)->where('holat', 'faol')->get();
        foreach ($biriktirilganBonuslar as $b) {
            if (!$b->amaldaMi($yil, $oy) || !$b->bonusTuri) {
                continue;
            }
            $qiymat = $b->qiymat !== null ? (float) $b->qiymat : (float) $b->bonusTuri->standart_qiymat;
            $biriktirilganBonusSumma += $b->bonusTuri->hisoblash_turi === 'foiz_okladdan'
                ? round($oklad * $qiymat / 100, 2)
                : $qiymat;
        }
        $biriktirilganBonusSumma = round($biriktirilganBonusSumma, 2);

        // Qo'lda kiritilgan qo'shimcha/jarima qiymatlarini saqlab qolamiz (qayta hisoblashda yo'qolmasin)
        $qoshimcha = (float) ($mavjud->qoshimcha_hisoblash ?? 0);
        $jarima    = (float) ($mavjud->ushlanma ?? 0);

        $jamiGross = round($okladQismi + $komissiyaBonus + $rejaBonusSumma + $qoshimchaIshHaqiSumma + $biriktirilganBonusSumma + $qoshimcha, 2);
        $soliqSumma          = round($jamiGross * $soliqFoizi / 100, 2);
        $boshqaUshlanmaSumma = round($jamiGross * $boshqaUshlanmaFoizi / 100, 2);

        $jami = round($jamiGross - $soliqSumma - $boshqaUshlanmaSumma - $jarima, 2);

        $avansJami = (float) IshHaqiAvans::where('xodim_id', $xodim->id)
            ->where('yil', $yil)->where('oy', $oy)->sum('summa');

        return IshHaqiHisob::updateOrCreate(
            ['xodim_id' => $xodim->id, 'yil' => $yil, 'oy' => $oy],
            [
                'ish_kunlari_jami'  => $ishKunlari,
                'kelgan_kunlar'     => $kelganKunlar,
                'davomat_foizi'     => $davomatFoizi,
                'oklad_qismi'       => $okladQismi,
                'yigilgan_tolovlar' => $yigilganTolovlar,
                'bonus_foizi'       => $bonusFoizi,
                'komissiya_bonus'   => $komissiyaBonus,
                'reja_bajarildimi'  => $rejaBajarildimi,
                'reja_bajarilish_foizi' => $bajarilishFoizi,
                'reja_bonus'        => $rejaBonusSumma,
                'qoshimcha_ish_haqi_summa'  => $qoshimchaIshHaqiSumma,
                'biriktirilgan_bonus_summa' => $biriktirilganBonusSumma,
                'soliq_foizi'          => $soliqFoizi,
                'soliq_summa'          => $soliqSumma,
                'boshqa_ushlanma_foizi' => $boshqaUshlanmaFoizi,
                'boshqa_ushlanma_summa' => $boshqaUshlanmaSumma,
                'avans_jami'        => $avansJami,
                'jami_hisoblangan'  => $jami,
                'holat'             => 'hisoblangan',
                'hisoblagan_id'     => Auth::id(),
            ]
        );
    }

    /** Barcha faol (ish haqi sozlamasi bor) xodimlar uchun bir oyni hisoblaydi. */
    public function hisoblaBarchasi(int $yil, int $oy, ?int $filialId = null): array
    {
        $xodimlar = Foydalanuvchi::where('holat', 'faol')
            ->whereHas('ishHaqiSozlama')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->get();

        $soni = 0;
        foreach ($xodimlar as $xodim) {
            $this->hisoblaOy($xodim, $yil, $oy);
            $soni++;
        }

        return ['hisoblandi' => $soni];
    }

    /** Qo'lda qo'shimcha hisoblash / jarima kiritish va jamini qayta hisoblash (hali to'lanmagan bo'lsa). */
    public function qoshimchaVaUshlanmaSaqla(IshHaqiHisob $hisob, float $qoshimcha, ?string $qoshimchaIzoh, float $jarima, ?string $jarimaIzoh): IshHaqiHisob
    {
        if ($hisob->holat === 'tolandi') {
            return $hisob;
        }

        $jamiGross = round(
            (float) $hisob->oklad_qismi + (float) $hisob->komissiya_bonus + (float) $hisob->reja_bonus
                + (float) $hisob->qoshimcha_ish_haqi_summa + (float) $hisob->biriktirilgan_bonus_summa + $qoshimcha,
            2
        );
        $soliqSumma          = round($jamiGross * (float) $hisob->soliq_foizi / 100, 2);
        $boshqaUshlanmaSumma = round($jamiGross * (float) $hisob->boshqa_ushlanma_foizi / 100, 2);
        $jami = round($jamiGross - $soliqSumma - $boshqaUshlanmaSumma - $jarima, 2);

        $hisob->update([
            'qoshimcha_hisoblash' => $qoshimcha,
            'qoshimcha_izoh'      => $qoshimchaIzoh,
            'ushlanma'            => $jarima,
            'ushlanma_izoh'       => $jarimaIzoh,
            'soliq_summa'         => $soliqSumma,
            'boshqa_ushlanma_summa' => $boshqaUshlanmaSumma,
            'jami_hisoblangan'    => $jami,
        ]);

        return $hisob->fresh();
    }

    /**
     * Xodimga avans (oldindan to'lov) berish — darhol Harajatlar moduliga yoziladi
     * (kassadan chiqim), va agar shu oy uchun hisob allaqachon mavjud bo'lsa (hali
     * to'lanmagan), uning avans_jami maydoni yangilanadi.
     */
    public function avansBer(Foydalanuvchi $xodim, int $yil, int $oy, float $summa, string $kassaTuri, ?string $izoh = null): IshHaqiAvans
    {
        if ($summa <= 0) {
            throw new \RuntimeException("Avans summasi 0 dan katta bo'lishi kerak.");
        }

        $turi = HarajatTuri::where('nomi', self::HARAJAT_TURI_NOMI)->first();
        if (!$turi) {
            throw new \RuntimeException('"' . self::HARAJAT_TURI_NOMI . '" harajat turi topilmadi.');
        }

        return DB::transaction(function () use ($xodim, $yil, $oy, $summa, $kassaTuri, $izoh, $turi) {
            $mazmuni = "Avans — {$xodim->ism_familiya}" . ($izoh ? " — {$izoh}" : '');

            $harajat = Harajat::create([
                'filial_id'         => $xodim->filial_id,
                'xodim_id'          => Auth::id(),
                'sana'              => now()->toDateString(),
                'turi'              => $turi->nomi,
                'harajat_turi_id'   => $turi->id,
                'tegishli_xodim_id' => $xodim->id,
                'kassa_turi'        => $kassaTuri,
                'pul_kategoriya_id' => $turi->pul_kategoriya_id,
                'summa'             => $summa,
                'mazmuni'           => $mazmuni,
            ]);

            if ($turi->kategoriya) {
                $this->tulovService->pulOqimigaYozKassaTuri(
                    filialId: $xodim->filial_id,
                    kassaTuri: $kassaTuri,
                    summa: $summa,
                    sana: now()->toDateString(),
                    kategoriyaKodi: $turi->kategoriya->kod,
                    izoh: $mazmuni,
                    manbaTur: 'harajat',
                    manbaId: $harajat->id,
                    yunalish: 'chiqim',
                );
            }

            $avans = IshHaqiAvans::create([
                'xodim_id'   => $xodim->id,
                'yil'        => $yil,
                'oy'         => $oy,
                'summa'      => $summa,
                'sana'       => now()->toDateString(),
                'izoh'       => $izoh,
                'harajat_id' => $harajat->id,
                'created_by' => Auth::id(),
            ]);

            // Shu oy uchun hisob allaqachon bor va hali to'lanmagan bo'lsa — avans_jami'ni yangilaymiz.
            $hisob = IshHaqiHisob::where('xodim_id', $xodim->id)
                ->where('yil', $yil)->where('oy', $oy)
                ->where('holat', 'hisoblangan')
                ->first();

            if ($hisob) {
                $yangiAvansJami = (float) IshHaqiAvans::where('xodim_id', $xodim->id)
                    ->where('yil', $yil)->where('oy', $oy)->sum('summa');
                $hisob->update(['avans_jami' => $yangiAvansJami]);
            }

            return $avans;
        });
    }

    /**
     * Ish haqini "to'landi" deb belgilash — faqat AVANSDAN keyin qolgan summani
     * Harajatlar moduliga yozadi (avans allaqachon o'z Harajat yozuvi bilan
     * berilgan). Agar avans jami butun ish haqini qoplagan bo'lsa, Harajat
     * yaratilmaydi, faqat holat "to'landi"ga o'tadi.
     */
    public function tolash(IshHaqiHisob $hisob, string $kassaTuri): IshHaqiHisob
    {
        if ($hisob->holat === 'tolandi') {
            return $hisob;
        }

        $qolgan = $hisob->qolganTolash();

        $xodim = $hisob->xodim;

        return DB::transaction(function () use ($hisob, $xodim, $kassaTuri, $qolgan) {
            $harajatId = null;

            if ($qolgan > 0) {
                $turi = HarajatTuri::where('nomi', self::HARAJAT_TURI_NOMI)->first();
                if (!$turi) {
                    throw new \RuntimeException('"' . self::HARAJAT_TURI_NOMI . '" harajat turi topilmadi.');
                }

                $harajat = Harajat::create([
                    'filial_id'         => $xodim->filial_id,
                    'xodim_id'          => Auth::id(),
                    'sana'              => now()->toDateString(),
                    'turi'              => $turi->nomi,
                    'harajat_turi_id'   => $turi->id,
                    'tegishli_xodim_id' => $xodim->id,
                    'kassa_turi'        => $kassaTuri,
                    'pul_kategoriya_id' => $turi->pul_kategoriya_id,
                    'summa'             => $qolgan,
                    'mazmuni'           => "Ish haqi — {$hisob->oyNomi()} — {$xodim->ism_familiya}",
                ]);

                if ($turi->kategoriya) {
                    $this->tulovService->pulOqimigaYozKassaTuri(
                        filialId: $xodim->filial_id,
                        kassaTuri: $kassaTuri,
                        summa: $qolgan,
                        sana: now()->toDateString(),
                        kategoriyaKodi: $turi->kategoriya->kod,
                        izoh: $harajat->mazmuni,
                        manbaTur: 'harajat',
                        manbaId: $harajat->id,
                        yunalish: 'chiqim',
                    );
                }

                $harajatId = $harajat->id;
            }

            $hisob->update([
                'holat'         => 'tolandi',
                'harajat_id'    => $harajatId,
                'tolangan_vaqt' => now(),
            ]);

            return $hisob->fresh();
        });
    }
}
