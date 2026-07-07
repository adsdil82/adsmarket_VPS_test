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

        $aktivlarBolim   = $bolimlar->firstWhere('tur', 'aktiv');
        $majburiyatBolim = $bolimlar->firstWhere('tur', 'majburiyat');
        $kapitalBolim    = $bolimlar->firstWhere('tur', 'kapital');

        // 1-bosqich: Aktivlar va Majburiyatlar — oddiy tartibda hisoblanadi.
        foreach ([$aktivlarBolim, $majburiyatBolim] as $bolim) {
            foreach ($bolim->qatorlar as $qator) {
                $qator->qiymat = $this->qatorQiymat($qator, $sana, $filialId);
            }
        }
        $jamiAktivlar   = $aktivlarBolim->qatorlar->sum('qiymat');
        $jamiMajburiyat = $majburiyatBolim->qatorlar->sum('qiymat');

        // 2-bosqich: Kapitalning "tenglashtiruvchi (plug)" moddasidan boshqa
        // barcha qatorlari — oddiy tartibda hisoblanadi.
        $plugQator = $kapitalBolim->qatorlar->firstWhere('hisoblash_turi', 'avtomat_balans_tenglashtiruvchi');
        $boshqaKapitalQatorlari = $kapitalBolim->qatorlar->reject(fn($q) => $plugQator && $q->id === $plugQator->id);
        foreach ($boshqaKapitalQatorlari as $qator) {
            $qator->qiymat = $this->qatorQiymat($qator, $sana, $filialId);
        }
        $kapitalNonPlug = $boshqaKapitalQatorlari->sum('qiymat');

        // 3-bosqich: plug moddasi = Aktiv - Majburiyat - (Kapitalning boshqa
        // moddalari) — natijada tenglama har doim aniq mos keladi.
        if ($plugQator) {
            $plugQator->qiymat = $jamiAktivlar - $jamiMajburiyat - $kapitalNonPlug;
        }
        $jamiKapital = $kapitalNonPlug + ($plugQator->qiymat ?? 0);

        $bolimJami = [
            $aktivlarBolim->id   => $jamiAktivlar,
            $majburiyatBolim->id => $jamiMajburiyat,
            $kapitalBolim->id    => $jamiKapital,
        ];

        $jamiPassiv = $jamiMajburiyat + $jamiKapital; // Majburiyatlar + Kapital

        return [
            'bolimlar'        => $bolimlar,
            'bolim_jami'      => $bolimJami,
            'jami_aktivlar'   => $jamiAktivlar,
            'jami_majburiyat' => $jamiMajburiyat,
            'jami_kapital'    => $jamiKapital,
            'jami_passiv'     => $jamiPassiv,
            'balans_farqi'    => $jamiAktivlar - $jamiPassiv, // "Balans tenglashtiruvchi farq" moddasi mavjud bo'lsa — doim 0
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
                return $this->omborQiymatiSanada($sana, $filialId);

            case 'avtomat_nasiya_debitorlik':
                return $this->nasiyaDebitorlikSanada($sana, $filialId);

            case 'avtomat_rezerv_nasiya':
                return $this->rezervNasiyaSanada($sana, $filialId);

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

    /**
     * Ombordagi tovar qoldig'ining (tan narxda) qiymati.
     *
     * MUHIM: Tranzaksiya-tarixidan (taminotchi kirim - POS/nasiya chiqim)
     * qayta tiklashga urinildi, lekin bu tizimda ombordagi tovarning katta
     * qismi ilgari "Dastlabki Qoldiq" import orqali kiritilgan va o'sha
     * boshlang'ich zaxiraning jurnal yozuvlari keyinchalik o'chirilgan
     * (foydalanuvchi so'rovi bilan) — shu sabab faqat kirim-chiqim
     * tranzaksiyalaridan hisoblash haqiqiy qiymatdan SEZILARLI KAM chiqadi
     * (tekshiruvda 661M o'rniga 9.7M chiqdi). Noto'g'ri kam ko'rsatilgan
     * moliyaviy raqamdan ko'ra, faqat JORIY (lekin haqiqiy) qiymatni
     * ko'rsatish xavfsizroq — shuning uchun ataylab shu usulga qaytarilgan.
     */
    private function omborQiymatiSanada(string $sana, ?int $filialId): float
    {
        return (float) DB::table('ombor_qoldiqlar as oq')
            ->join('tovar_katalog as t', 't.id', '=', 'oq.tovar_id')
            ->when($filialId, fn($qq) => $qq->join('omborlar as o', 'o.id', '=', 'oq.ombor_id')->where('o.filial_id', $filialId))
            ->sum(DB::raw('oq.miqdor * t.tan_narx'));
    }

    /**
     * "Rezerv nasiya" — kechikkan/muddati o'tgan nasiya shartnomalari uchun
     * 100% zaxira, aktivda KONTR-SCHYOT sifatida manfiy qiymatda ko'rsatiladi
     * (Shartnomalar ro'yxatidagi "kechikkan_summa" formula bilan bir xil,
     * faqat balans sanasiga moslashtirilgan):
     *   - muddati o'tgan shartnoma: butun qoldiq qarzning hammasi;
     *   - hali faol, lekin kechikkan shartnoma: faqat kechikkan (muddati
     *     o'tgan, to'lanmagan) grafik oylarining yig'indisi.
     *
     * Cheklov: reg_kredit.holat faqat JORIY holatni saqlaydi (tarixiy holat
     * o'zgarishi kuzatilmaydi) — o'tgan sanalar uchun ham joriy holat
     * ishlatiladi, bu joriy yilga yaqin sanalar uchun amaliyotda yetarlicha
     * aniq taxmin hisoblanadi.
     */
    private function rezervNasiyaSanada(string $sana, ?int $filialId): float
    {
        $kechikkanJami = (float) DB::table('reg_kredit as rk')
            ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
            ->where('rk.boshlanish_sana', '<=', $sana)
            ->selectRaw("
                CASE WHEN rk.holat = 'muddati_otgan' THEN rk.qoldiq_qarz ELSE COALESCE((
                    SELECT SUM(g.tolov_summa - g.tolangan_summa) FROM grafik g
                    WHERE g.reg_kredit_id = rk.id
                      AND g.holat IN ('tolanmagan','qisman','muddati_otgan')
                      AND g.tolov_sana IS NOT NULL
                      AND g.tolov_sana < ?
                ), 0) END as kechikkan
            ", [$sana])
            ->get()
            ->sum('kechikkan');

        return -$kechikkanJami; // kontr-schyot — aktivni kamaytiradi
    }

    /**
     * Nasiya mijozlardan debitorlik qarzining sanaga nisbatan haqiqiy qiymati.
     * Har bir shartnoma uchun: kredit_summa - (shu sanagacha qilingan barcha
     * to'lovlar + oldindan to'lovlar). reg_kredit/tulovlar/oldindan_tulov
     * to'liq tarixiy (sanalangan) jadvallar bo'lgani uchun bu aniq hisoblanadi.
     */
    private function nasiyaDebitorlikSanada(string $sana, ?int $filialId): float
    {
        $tulovlar = DB::table('reg_kredit as rk')
            ->leftJoin('tulovlar as t', function ($j) use ($sana) {
                $j->on('t.reg_kredit_id', '=', 'rk.id')->where('t.tolov_sana', '<=', $sana);
            })
            ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
            ->where('rk.boshlanish_sana', '<=', $sana)
            ->groupBy('rk.id', 'rk.kredit_summa')
            ->selectRaw('rk.id, rk.kredit_summa, COALESCE(SUM(t.summa),0) as tulov_summa')
            ->get();

        $oldindanXarita = DB::table('oldindan_tulov as ot')
            ->join('reg_kredit as rk', 'rk.id', '=', 'ot.reg_kredit_id')
            ->when($filialId, fn($q) => $q->where('rk.filial_id', $filialId))
            ->where('ot.tolov_sana', '<=', $sana)
            ->groupBy('ot.reg_kredit_id')
            ->selectRaw('ot.reg_kredit_id, SUM(ot.summa) as summa')
            ->pluck('summa', 'reg_kredit_id');

        $jami = 0.0;
        foreach ($tulovlar as $r) {
            $tolangan = (float) $r->tulov_summa + (float) ($oldindanXarita[$r->id] ?? 0);
            $jami += max((float) $r->kredit_summa - $tolangan, 0);
        }

        return $jami;
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

        $aktivlarBolim   = $bolimlar->firstWhere('tur', 'aktiv');
        $majburiyatBolim = $bolimlar->firstWhere('tur', 'majburiyat');
        $kapitalBolim    = $bolimlar->firstWhere('tur', 'kapital');
        $plugQator = $kapitalBolim->qatorlar->firstWhere('hisoblash_turi', 'avtomat_balans_tenglashtiruvchi');

        foreach ($bolimlar as $bolim) {
            foreach ($bolim->qatorlar as $qator) {
                if ($plugQator && $qator->id === $plugQator->id) continue; // pastda alohida hisoblanadi

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
                $oylik[$oy] = $bolim->qatorlar
                    ->reject(fn($q) => $plugQator && $q->id === $plugQator->id)
                    ->sum(fn($q) => $q->oylik[$oy] ?? 0);
            }
            $bolimJamiOylik[$bolim->id] = $oylik;
        }

        // Plug moddasini endi hisoblab, Kapital bo'limi jamisiga qo'shamiz —
        // natijada Aktiv = Majburiyat + Kapital tenglamasi har oy uchun aniq mos keladi.
        if ($plugQator) {
            $plugOylik = array_fill(1, 12, null);
            foreach (range(1, $oxirgiOy) as $oy) {
                $aktiv          = $bolimJamiOylik[$aktivlarBolim->id][$oy] ?? 0;
                $majburiyat     = $bolimJamiOylik[$majburiyatBolim->id][$oy] ?? 0;
                $kapitalNonPlug = $bolimJamiOylik[$kapitalBolim->id][$oy] ?? 0;
                $plugOylik[$oy] = $aktiv - $majburiyat - $kapitalNonPlug;
            }
            $plugQator->oylik = $plugOylik;
            foreach (range(1, $oxirgiOy) as $oy) {
                $bolimJamiOylik[$kapitalBolim->id][$oy] = ($bolimJamiOylik[$kapitalBolim->id][$oy] ?? 0) + $plugOylik[$oy];
            }
        }

        $balansFarqiOylik = array_fill(1, 12, null);
        $jamiPassivOylik  = array_fill(1, 12, null);
        foreach (range(1, $oxirgiOy) as $oy) {
            $aktiv  = $bolimJamiOylik[$aktivlarBolim->id][$oy] ?? 0;
            $passiv = ($bolimJamiOylik[$majburiyatBolim->id][$oy] ?? 0) + ($bolimJamiOylik[$kapitalBolim->id][$oy] ?? 0);
            $jamiPassivOylik[$oy]  = $passiv;
            $balansFarqiOylik[$oy] = $aktiv - $passiv;
        }

        return [
            'bolimlar'            => $bolimlar,
            'bolim_jami_oylik'    => $bolimJamiOylik,
            'jami_passiv_oylik'   => $jamiPassivOylik, // Majburiyat + Kapital, oy bo'yicha
            'balans_farqi_oylik'  => $balansFarqiOylik,
            'oxirgi_oy'           => $oxirgiOy,
            'aktivlar_bolim_id'   => $aktivlarBolim->id,
            'majburiyat_bolim_id' => $majburiyatBolim->id,
            'kapital_bolim_id'    => $kapitalBolim->id,
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
