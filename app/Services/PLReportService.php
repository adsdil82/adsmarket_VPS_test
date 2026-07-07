<?php
namespace App\Services;

use App\Models\PLBolim;
use App\Models\PLQator;
use App\Models\PLQiymat;
use Illuminate\Support\Facades\DB;

/**
 * Foyda-Zarar (Profit & Loss) hisobotini hisoblaydi. Har bir qator uchun
 * 12 oylik qiymat va yillik jami qaytaradi. Avtomatik qatorlar mavjud
 * jadvallardan (POS, Nasiya, Harajatlar) hisoblanadi, "qolda" turdagi
 * qatorlar esa pl_qiymatlari jadvalidan o'qiladi (admin tomonidan
 * qo'lda kiritilgan/tahrirlangan).
 */
class PLReportService
{
    /**
     * @return array{bolimlar: \Illuminate\Support\Collection, jamilar: array, sof_daromad: array}
     */
    public function hisobot(int $yil, ?int $filialId = null): array
    {
        $bolimlar = PLBolim::with('qatorlar.harajatTurlari')->orderBy('sort_order')->get();

        // Har bir qator uchun oylik massiv (1..12) + yillik jami hisoblaymiz.
        foreach ($bolimlar as $bolim) {
            foreach ($bolim->qatorlar as $qator) {
                $qator->oylik = $this->qatorOylik($qator, $yil, $filialId);
                $qator->yillik = array_sum($qator->oylik);
            }
        }

        // Bo'lim jamilari (ishoraga qarab musbat/manfiy sifatida qo'shiladi ichki hisob-kitobda,
        // lekin ko'rsatishda har doim musbat son sifatida chiqariladi — belgisi bo'lim ishorasida).
        $bolimJami = [];
        foreach ($bolimlar as $bolim) {
            $oylik = array_fill(1, 12, 0.0);
            foreach ($bolim->qatorlar as $qator) {
                foreach ($qator->oylik as $oy => $summa) {
                    $oylik[$oy] += ($qator->ishora === 'manfiy' ? -1 : 1) * $summa;
                }
            }
            $bolimJami[$bolim->id] = ['oylik' => $oylik, 'yillik' => array_sum($oylik)];
        }

        // Formulaviy oraliq natijalar (bo'lim ishoralariga mos qo'shib/ayirib boriladi):
        //   Jami savdo hajmi brutto = Savdo hajmi bo'limi jamisi
        //   Marja daromadi qoldig'i = Savdo hajmi + Tannarx (tannarx ishorasi manfiy)
        //   Savdo daromadi qoldig'i = Marja + Savdo harajatlari
        //   Operatsion daromad = Savdo daromadi + Operatsion harajatlar
        //   Daromad soliq-rezervgacha = Operatsion daromad + Boshqa daromad/harajat
        //   Sof daromad = yuqoridagi + Soliq va rezerv
        $oraliq = array_fill(1, 12, 0.0);
        $formulalar = [];
        foreach ($bolimlar as $bolim) {
            $ishoraKoef = $bolim->ishora === 'manfiy' ? -1 : 1;
            foreach (range(1, 12) as $oy) {
                $oraliq[$oy] += $ishoraKoef * $bolimJami[$bolim->id]['oylik'][$oy];
            }
            // PHP massivlari qiymat bo'yicha nusxalanadi — shu yerdagi $oraliq
            // keyingi bo'limlar ustida o'zgarishidan qat'i nazar, saqlangan
            // nusxaga ta'sir qilmaydi.
            $formulalar[$bolim->nomi] = ['oylik' => $oraliq, 'yillik' => array_sum($oraliq)];
        }

        return [
            'bolimlar'     => $bolimlar,
            'bolim_jami'   => $bolimJami,
            'formulalar'   => $formulalar, // har bo'limdan keyingi kumulyativ natija
            'sof_daromad'  => end($formulalar),
        ];
    }

    /** Bitta qator uchun 1..12 oy bo'yicha summalar massivi. */
    private function qatorOylik(PLQator $qator, int $yil, ?int $filialId): array
    {
        $oylik = array_fill(1, 12, 0.0);

        switch ($qator->hisoblash_turi) {
            case 'avtomat_naqd_savdo':
                $rows = DB::table('pos_sotuv')
                    ->selectRaw('MONTH(sana) as oy, SUM(jami_tolov) as summa')
                    ->where('holat', 'tugallangan')
                    ->whereYear('sana', $yil)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->groupBy('oy')->pluck('summa', 'oy');
                foreach ($rows as $oy => $summa) $oylik[(int) $oy] = (float) $summa;
                break;

            case 'avtomat_naqd_tannarx':
                $rows = DB::table('pos_tafsilot as pt')
                    ->join('pos_sotuv as ps', 'ps.id', '=', 'pt.sotuv_id')
                    ->join('tovar_katalog as t', 't.id', '=', 'pt.tovar_id')
                    ->selectRaw('MONTH(ps.sana) as oy, SUM(pt.miqdor * t.tan_narx) as summa')
                    ->where('ps.holat', 'tugallangan')
                    ->whereYear('ps.sana', $yil)
                    ->when($filialId, fn($q) => $q->where('ps.filial_id', $filialId))
                    ->groupBy('oy')->pluck('summa', 'oy');
                foreach ($rows as $oy => $summa) $oylik[(int) $oy] = (float) $summa;
                break;

            case 'avtomat_nasiya_savdo':
                $rows = DB::table('reg_kredit')
                    ->selectRaw('MONTH(boshlanish_sana) as oy, SUM(jami_summa) as summa')
                    ->whereYear('boshlanish_sana', $yil)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->groupBy('oy')->pluck('summa', 'oy');
                foreach ($rows as $oy => $summa) $oylik[(int) $oy] = (float) $summa;
                break;

            case 'avtomat_harajat_turi':
                $turiIds = $qator->harajatTurlari->pluck('id');
                if ($turiIds->isEmpty()) break;
                $rows = DB::table('harajatlar')
                    ->selectRaw('MONTH(sana) as oy, SUM(summa) as summa')
                    ->whereIn('harajat_turi_id', $turiIds)
                    ->whereYear('sana', $yil)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->groupBy('oy')->pluck('summa', 'oy');
                foreach ($rows as $oy => $summa) $oylik[(int) $oy] = (float) abs($summa);
                break;

            case 'avtomat_bonus_tovar':
                // Mijozga qo'shib beriladigan (ombordan kamayadigan, lekin shartnoma/hisob-fakturada
                // ko'rsatilmaydigan) bonus tovarlar — bu haqiqiy xarajat, shartnoma yaratilgan oy bo'yicha.
                $rows = DB::table('tovarlar as t')
                    ->join('reg_kredit as rk', 'rk.id', '=', 't.reg_kredit_id')
                    ->where('t.turi', 'bonus')
                    ->selectRaw('MONTH(rk.boshlanish_sana) as oy, SUM(t.jami_narx) as summa')
                    ->whereYear('rk.boshlanish_sana', $yil)
                    ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
                    ->groupBy('oy')->pluck('summa', 'oy');
                foreach ($rows as $oy => $summa) $oylik[(int) $oy] = (float) $summa;
                break;

            case 'qolda':
                $rows = PLQiymat::where('qator_id', $qator->id)
                    ->where('yil', $yil)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId), fn($q) => $q->whereNull('filial_id'))
                    ->pluck('summa', 'oy');
                foreach ($rows as $oy => $summa) $oylik[(int) $oy] = (float) $summa;
                break;
        }

        return $oylik;
    }

    /** Qo'lda kiritilgan qiymatni saqlaydi (yaratadi yoki yangilaydi). */
    public function qiymatSaqlash(int $qatorId, ?int $filialId, int $yil, int $oy, float $summa, ?string $izoh, ?int $xodimId): PLQiymat
    {
        return PLQiymat::updateOrCreate(
            ['qator_id' => $qatorId, 'filial_id' => $filialId, 'yil' => $yil, 'oy' => $oy],
            ['summa' => $summa, 'izoh' => $izoh, 'xodim_id' => $xodimId]
        );
    }
}
