<?php
namespace App\Services;

use App\Models\BLBolim;
use App\Models\BLQiymat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Balans hisoboti — P&L'dan farqli o'laroq MA'LUM SANAGA (nuqtaga)
 * hisoblanadi, davr uchun emas. Tenglama: Aktivlar = Majburiyatlar + Kapital.
 *
 * Ba'zi qatorlar (ombor qiymati, nasiya debitorlik) faqat JORIY holatni
 * aniq bera oladi — o'tmish sanalar uchun tarixiy kuzatuv hali yo'q,
 * bu servis va view shu cheklovni ochiq ko'rsatadi ("joriy holat" belgisi).
 */
class BLReportService
{
    public function hisobot(string $sana, ?int $filialId = null): array
    {
        $bolimlar = BLBolim::with('qatorlar')->orderBy('sort_order')->get();

        foreach ($bolimlar as $bolim) {
            foreach ($bolim->qatorlar as $qator) {
                $qator->qiymat = $this->qatorQiymat($qator, $sana, $filialId);
            }
        }

        $bolimJami = [];
        foreach ($bolimlar as $bolim) {
            $bolimJami[$bolim->id] = $bolim->qatorlar->sum('qiymat');
        }

        $aktivlarBolim = $bolimlar->firstWhere('tur', 'aktiv');
        $majburiyatBolim = $bolimlar->firstWhere('tur', 'majburiyat');
        $kapitalBolim = $bolimlar->firstWhere('tur', 'kapital');

        $jamiAktivlar = $bolimJami[$aktivlarBolim->id] ?? 0;
        $jamiMajburiyat = $bolimJami[$majburiyatBolim->id] ?? 0;
        $jamiKapital = $bolimJami[$kapitalBolim->id] ?? 0;
        $jamiPassiv = $jamiMajburiyat + $jamiKapital; // Majburiyatlar + Kapital

        return [
            'bolimlar'        => $bolimlar,
            'bolim_jami'      => $bolimJami,
            'jami_aktivlar'   => $jamiAktivlar,
            'jami_majburiyat' => $jamiMajburiyat,
            'jami_kapital'    => $jamiKapital,
            'jami_passiv'     => $jamiPassiv,
            'balans_farqi'    => $jamiAktivlar - $jamiPassiv, // 0 bo'lishi kerak — bo'lmasa nomuvofiqlik bor
        ];
    }

    private function qatorQiymat($qator, string $sana, ?int $filialId): float
    {
        switch ($qator->hisoblash_turi) {
            case 'avtomat_naqd_pul':
                $kirim = DB::table('pul_oqimlari')
                    ->where('holat', 'tasdiqlangan')->where('yunalish', 'kirim')
                    ->whereDate('sana', '<=', $sana)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->sum('summa');
                $chiqim = DB::table('pul_oqimlari')
                    ->where('holat', 'tasdiqlangan')->where('yunalish', 'chiqim')
                    ->whereDate('sana', '<=', $sana)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->sum('summa');
                return (float) $kirim - (float) $chiqim;

            case 'avtomat_ombor_qiymati':
                // Cheklov: faqat JORIY qoldiq (tarixiy sanalar uchun ham shu
                // qiymat qaytariladi — stock_ledger hali yetarlicha tarix
                // to'plamagan).
                $q = DB::table('ombor_qoldiqlar as oq')
                    ->join('tovar_katalog as t', 't.id', '=', 'oq.tovar_id')
                    ->when($filialId, fn($qq) => $qq->join('omborlar as o', 'o.id', '=', 'oq.ombor_id')->where('o.filial_id', $filialId));
                return (float) $q->sum(DB::raw('oq.miqdor * t.tan_narx'));

            case 'avtomat_nasiya_debitorlik':
                // Cheklov: faqat JORIY qoldiq_qarz (tarixiy nuqtadagi qarz
                // holatini reconstruct qilish hali qo'llab-quvvatlanmaydi).
                return (float) DB::table('reg_kredit')
                    ->whereIn('holat', ['faol', 'muddati_otgan'])
                    ->when($filialId, fn($qq) => $qq->where('filial_id', $filialId))
                    ->sum('qoldiq_qarz');

            case 'avtomat_taminotchi_qarz':
                $kirim = DB::table('taminot_kirimlar')
                    ->whereDate('kirim_sana', '<=', $sana)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->sum('jami_summa');
                $tolov = DB::table('taminotchi_tulovlar')
                    ->whereDate('tolov_sana', '<=', $sana)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
                    ->sum('summa_uzs');
                return (float) $kirim - (float) $tolov;

            case 'avtomat_jamgarilgan_foyda':
                return $this->jamgarilganFoyda($sana, $filialId);

            case 'qolda':
                // Carry-forward: shu sanagacha bo'lgan ENG SO'NGGI kiritilgan
                // qiymat qo'llaniladi (masalan "Asosiy vositalar" har oy
                // qayta kiritilmaydi, faqat o'zgarganda yangilanadi).
                $row = BLQiymat::where('qator_id', $qator->id)
                    ->whereDate('sana', '<=', $sana)
                    ->when($filialId, fn($q) => $q->where('filial_id', $filialId), fn($q) => $q->whereNull('filial_id'))
                    ->orderByDesc('sana')
                    ->first();
                return $row ? (float) $row->summa : 0.0;
        }
        return 0.0;
    }

    /** Tanlangan sanagacha bo'lgan barcha yillarning P&L sof daromadlari yig'indisi. */
    private function jamgarilganFoyda(string $sana, ?int $filialId): float
    {
        $pl = app(PLReportService::class);
        $sanaObj = Carbon::parse($sana);
        $jami = 0.0;

        foreach (range(2020, $sanaObj->year) as $yil) {
            $natija = $pl->hisobot($yil, $filialId);
            if ($yil < $sanaObj->year) {
                $jami += $natija['sof_daromad']['yillik'];
            } else {
                // Joriy yil uchun faqat tanlangan sanagacha bo'lgan oylarni qo'shamiz
                foreach (range(1, $sanaObj->month) as $oy) {
                    $jami += $natija['sof_daromad']['oylik'][$oy] ?? 0;
                }
            }
        }

        return $jami;
    }

    /**
     * P&L kabi 12 oylik ko'rinish — har oy OXIRIDAGI (snapshot) holatni
     * hisoblaydi. Joriy yil bo'lsa, hali kelmagan oylar hisoblanmaydi
     * (kelajak sanaga snapshot ma'nosiz).
     */
    public function hisobotOylik(int $yil, ?int $filialId = null): array
    {
        $bolimlar = BLBolim::with('qatorlar')->orderBy('sort_order')->get();
        $bugun = Carbon::today();
        $oxirgiOy = $yil == $bugun->year ? $bugun->month : 12;

        foreach ($bolimlar as $bolim) {
            foreach ($bolim->qatorlar as $qator) {
                // Eloquent modelning magic __get/__set orqali massiv
                // elementiga to'g'ridan-to'g'ri yozish ("$qator->oylik[$oy]=..")
                // ishlamaydi (nusxa ustida ishlaydi) — shuning uchun avval
                // lokal massivda yig'ib, oxirida BUTUN massivni bir yo'la
                // biriktiramiz.
                $oylik = array_fill(1, 12, null); // null = kelajak oy (hali yo'q)
                foreach (range(1, $oxirgiOy) as $oy) {
                    $oxiriSana = Carbon::create($yil, $oy, 1)->endOfMonth();
                    if ($oxiriSana->gt($bugun)) $oxiriSana = $bugun; // joriy oy uchun "bugun" holatiga
                    $oylik[$oy] = $this->qatorQiymat($qator, $oxiriSana->toDateString(), $filialId);
                }
                $qator->oylik = $oylik;
            }
        }

        $bolimJamiOylik = [];
        foreach ($bolimlar as $bolim) {
            $oylik = array_fill(1, 12, null);
            foreach (range(1, $oxirgiOy) as $oy) {
                $oylik[$oy] = $bolim->qatorlar->sum(fn($q) => $q->oylik[$oy] ?? 0);
            }
            $bolimJamiOylik[$bolim->id] = $oylik;
        }

        $aktivlarBolim = $bolimlar->firstWhere('tur', 'aktiv');
        $majburiyatBolim = $bolimlar->firstWhere('tur', 'majburiyat');
        $kapitalBolim = $bolimlar->firstWhere('tur', 'kapital');

        $balansFarqiOylik = array_fill(1, 12, null);
        foreach (range(1, $oxirgiOy) as $oy) {
            $aktiv = $bolimJamiOylik[$aktivlarBolim->id][$oy] ?? 0;
            $passiv = ($bolimJamiOylik[$majburiyatBolim->id][$oy] ?? 0) + ($bolimJamiOylik[$kapitalBolim->id][$oy] ?? 0);
            $balansFarqiOylik[$oy] = $aktiv - $passiv;
        }

        return [
            'bolimlar'          => $bolimlar,
            'bolim_jami_oylik'  => $bolimJamiOylik,
            'balans_farqi_oylik'=> $balansFarqiOylik,
            'oxirgi_oy'         => $oxirgiOy,
            'aktivlar_bolim_id' => $aktivlarBolim->id,
            'majburiyat_bolim_id' => $majburiyatBolim->id,
            'kapital_bolim_id'  => $kapitalBolim->id,
        ];
    }

    public function qiymatSaqlash(int $qatorId, ?int $filialId, string $sana, float $summa, ?string $izoh, ?int $xodimId): BLQiymat
    {
        return BLQiymat::updateOrCreate(
            ['qator_id' => $qatorId, 'filial_id' => $filialId, 'sana' => $sana],
            ['summa' => $summa, 'izoh' => $izoh, 'xodim_id' => $xodimId]
        );
    }
}
