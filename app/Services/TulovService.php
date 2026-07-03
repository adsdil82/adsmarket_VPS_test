<?php

namespace App\Services;

use App\Models\Grafik;
use App\Models\OldinTulov;
use App\Models\RegKredit;
use App\Models\Tulov;
use App\Models\TulovTuri;
use Carbon\Carbon;
use App\Models\ShartnomavVersioniya;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TulovService
{
    /**
     * Yangi to'lov qabul qilish.
     *
     * Jarayon:
     *  1. Tranzaksiya ichida bajaradi (xatolik bo'lsa rollback)
     *  2. Grafikdan birinchi to'lanmagan oyni topadi
     *  3. tulovlar jadvaliga yozadi
     *  4. Grafik holatini yangilaydi
     *  5. reg_kredit.tolov_qilingan va qoldiq_qarz ni yangilaydi
     *  6. Agar qoldiq_qarz = 0 bo'lsa, holat → yopilgan
     *
     * @param RegKredit $kredit
     * @param array $malumot ['tulov_turi_id', 'summa', 'tolov_sana', 'kvitansiya_raqam', 'izoh']
     * @return Tulov
     */
    public function tulovQabul(RegKredit $kredit, array $malumot): Tulov
    {
        return DB::transaction(function () use ($kredit, $malumot) {

            // Versiya saqlash uchun oldingi holat
            $eskiHolat = $kredit->only([
                'tolov_qilingan', 'qoldiq_qarz', 'holat'
            ]);

            // Barcha to'lanmagan grafik qatorlari (tartib bo'yicha) — to'lov
            // summasi bitta oyning rejasidan katta bo'lsa (masalan mijoz bir
            // necha oylik to'lovni birdaniga to'lasa), ortiqcha qism KEYINGI
            // oy(lar)ga "oqib o'tishi" kerak — aks holda bitta qatorga butun
            // summa yopishib qolib, qolgan oylar "to'lanmagan"ligicha qoladi
            // va har bir qatordagi "qoldiq" noto'g'ri ko'rinadi.
            $qatorlar = Grafik::where('reg_kredit_id', $kredit->id)
                ->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan'])
                ->orderBy('oylik_tartib')
                ->get();

            $birinchiQator = $qatorlar->first();

            // To'lovni saqlash
            $tulov = Tulov::create([
                'reg_kredit_id'    => $kredit->id,
                'grafik_id'        => $birinchiQator?->id,
                'xodim_id'         => Auth::id(),
                'tulov_turi_id'    => $malumot['tulov_turi_id'],
                'summa'            => $malumot['summa'],
                'tolov_sana'       => $malumot['tolov_sana'],
                'kvitansiya_raqam' => $malumot['kvitansiya_raqam'] ?? $this->kvitansiyaRaqamYarat($malumot['tulov_turi_id'], $malumot['tolov_sana'] ?? now()->toDateString()),
                'izoh'             => $malumot['izoh'] ?? null,
            ]);

            // To'lov summasini qatorlar bo'yicha ketma-ket (FIFO) tarqatish —
            // har bir qatorning faqat o'z rejadagi summasigacha bo'lgan qismi
            // to'ldiriladi, ortig'i keyingi qatorga o'tadi.
            $qolganSumma = (float) $malumot['summa'];
            foreach ($qatorlar as $g) {
                if ($qolganSumma <= 0) break;

                $kerak = max(0, (float) ($g->tolov_summa ?? 0) - (float) $g->tolangan_summa);
                if ($kerak <= 0) continue;

                $qoshiladi     = min($kerak, $qolganSumma);
                $yangiTolangan = (float) $g->tolangan_summa + $qoshiladi;
                $grafikHolat   = $yangiTolangan >= ($g->tolov_summa ?? 0) ? 'tolangan' : 'qisman';

                $g->update([
                    'tolangan_summa' => $yangiTolangan,
                    'tolangan_sana'  => $malumot['tolov_sana'],
                    'holat'          => $grafikHolat,
                ]);

                $qolganSumma -= $qoshiladi;
            }
            // Eslatma: agar $qolganSumma jadvaldagi BARCHA qatorlarni to'ldirgandan
            // keyin ham qolsa (rejadan ortiq to'lov) — bu holat reg_kredit.qoldiq_qarz
            // darajasida hisobga olinadi (pastda), grafik qatorlariga taqsimlanmaydi.

            // Shartnomaning moliyaviy ko'rsatkichlarini yangilash
            $yangiTolovQilingan = $kredit->tolov_qilingan + $malumot['summa'];
            $yangiQoldiq        = max(0, $kredit->kredit_summa - $yangiTolovQilingan);

            // Shartnoma holati
            $yangiHolat = $kredit->holat;
            if ($yangiQoldiq == 0) {
                $yangiHolat = 'yopilgan';
            }

            $kredit->update([
                'tolov_qilingan' => $yangiTolovQilingan,
                'qoldiq_qarz'    => $yangiQoldiq,
                'holat'          => $yangiHolat,
            ]);

            // Kechikkan to'lovlarni muddati_otgan deb belgilash
            $this->muddatiOtganniYangilash($kredit->id);

            // Naqd/terminal/bank — pulga oid to'lov turlari uchun avtomatik
            // "Pul oqimlari" kirim yozuvi (CF-1100 — Nasiya to'lovlari).
            $this->pulOqimigaYoz(
                filialId: $kredit->filial_id,
                tulovTuriId: $malumot['tulov_turi_id'],
                summa: (float) $malumot['summa'],
                sana: $malumot['tolov_sana'],
                kategoriyaKodi: 'CF-1100',
                izoh: "Shartnoma {$kredit->shartnoma_raqam} bo'yicha to'lov (kv. {$tulov->kvitansiya_raqam})",
                manbaTur: 'tulov',
                manbaId: $tulov->id,
            );

            Log::info("To'lov qabul qilindi", [
                'kredit_id'  => $kredit->id,
                'summa'      => $malumot['summa'],
                'xodim_id'   => Auth::id(),
            ]);

            return $tulov;
        });
    }

    /**
     * Boshlang'ich (oldindan) to'lovni saqlash.
     *
     * @param RegKredit $kredit
     * @param array $malumot
     * @return OldinTulov
     */
    public function oldinTulovSaqlash(RegKredit $kredit, array $malumot): OldinTulov
    {
        return DB::transaction(function () use ($kredit, $malumot) {

            $oldinTulov = OldinTulov::create([
                'reg_kredit_id'    => $kredit->id,
                'xodim_id'         => Auth::id(),
                'tulov_turi_id'    => $malumot['tulov_turi_id'],
                'summa'            => $malumot['summa'],
                'tolov_sana'       => $malumot['tolov_sana'],
                'kvitansiya_raqam' => $malumot['kvitansiya_raqam'] ?? null,
                'izoh'             => $malumot['izoh'] ?? null,
            ]);

            // boshlangich_tolov ni yangilash (agar kerak bo'lsa)
            // Odatda shartnoma tuzilganda bir marta kiritiladi, keyinchalik o'zgarmasligi kerak
            // Lekin tuzatish imkoniyati uchun qoldiramiz

            return $oldinTulov;
        });
    }

    /**
     * Kechikkan grafik qatorlarini muddati_otgan deb belgilash.
     * Har kuni scheduler chaqiradi, lekin to'lov qabul qilganda ham ishlaydi.
     */
    public function muddatiOtganniYangilash(int $kreditId): int
    {
        return Grafik::where('reg_kredit_id', $kreditId)
            ->whereIn('holat', ['tolanmagan', 'qisman'])
            ->whereNotNull('tolov_sana')
            ->where('tolov_sana', '<', now()->toDateString())
            ->update(['holat' => 'muddati_otgan']);
    }

    /**
     * Hamma shartnomalar uchun muddati o'tgan grafiklarni yangilash.
     * Scheduler: daily.
     */
    public function barchaMuddatiOtganYangilash(): int
    {
        $yangilandi = Grafik::whereIn('holat', ['tolanmagan', 'qisman'])
            ->whereNotNull('tolov_sana')
            ->where('tolov_sana', '<', now()->toDateString())
            ->update(['holat' => 'muddati_otgan']);

        // Tegishli shartnomalarning holatini ham yangilash
        RegKredit::where('holat', 'faol')
            ->whereHas('grafik', fn($q) => $q->where('holat', 'muddati_otgan'))
            ->update(['holat' => 'muddati_otgan']);

        return $yangilandi;
    }

    /**
     * Shartnoma versiyasini saqlash (o'zgarishdan oldin chaqiriladi).
     *
     * @param RegKredit $kredit
     * @param string $sabab
     * @param array $yangiMalumot
     */
    public function versiyaSaqlash(RegKredit $kredit, string $sabab, array $yangiMalumot): void
    {
        $oxirgiVersiya = $kredit->versiyalar()->max('versiya_raqam') ?? 0;

        $ozgarganlar = array_keys(array_diff_assoc(
            $yangiMalumot,
            $kredit->only(array_keys($yangiMalumot))
        ));

        ShartnomavVersioniya::create([
            'reg_kredit_id'      => $kredit->id,
            'versiya_raqam'      => $oxirgiVersiya + 1,
            'xodim_id'           => Auth::id(),
            'sabab'              => $sabab,
            'eski_holat'         => $kredit->toArray(),
            'yangi_holat'        => array_merge($kredit->toArray(), $yangiMalumot),
            'ozgargan_maydonlar' => $ozgarganlar,
        ]);
    }

    /**
     * Kvitansiya raqamini avtomatik yaratish — har bir to'lov turi bo'yicha,
     * har oy uchun ALOHIDA tartib raqami (oy boshlanganda 1dan boshlanadi).
     * Prefiks to'lov turining ID'siga bog'langan — shu sababli ikki xil
     * to'lov turi (masalan "NAQD" va "UzCard termi.") bir-biriga aralashib
     * ketmaydi, har biri o'z mustaqil hisobiga ega.
     */
    private function kvitansiyaRaqamYarat(int $tulovTuriId, string $tolovSana): string
    {
        return $this->kvitansiyaHisobla($tulovTuriId, $tolovSana, true);
    }

    /**
     * "To'lov qabul qilish" formasida — to'lov hali saqlanmasdan oldin,
     * tanlangan to'lov turi bo'yicha keyingi kvitansiya raqami qanday
     * bo'lishini OLDINDAN ko'rsatish uchun (lockForUpdate'siz, faqat o'qish).
     * Haqiqiy raqam saqlash paytida kvitansiyaRaqamYarat() orqali (lock bilan,
     * poyga holatidan himoyalangan) qayta hisoblanadi — bu yerdagi qiymat
     * faqat taxminiy ko'rsatma, foydalanuvchi tomonidan o'zgartirilmaydi.
     */
    public function keyingiKvitansiyaOldindanKorish(int $tulovTuriId): string
    {
        return $this->kvitansiyaHisobla($tulovTuriId, today()->toDateString(), false);
    }

    /**
     * To'lov turi nomiga qarab kvitansiya prefiksini aniqlash:
     *   NQ — naqd pul, TR — terminal/karta orqali, BK — bank o'tkazmasi
     *   (yoki boshqa — chegirma, qaytarish, write-off va h.k.).
     */
    private function kvitansiyaPrefiks(int $tulovTuriId): string
    {
        $nomi = mb_strtolower((string) (TulovTuri::find($tulovTuriId)?->nomi ?? ''));

        if (str_contains($nomi, 'накд') || str_contains($nomi, 'naqd')) {
            return 'NQ';
        }
        if (str_contains($nomi, 'терм') || str_contains($nomi, 'term')) {
            return 'TR';
        }
        return 'BK';
    }

    private function kvitansiyaHisobla(int $tulovTuriId, string $tolovSana, bool $lock): string
    {
        $oy     = \Carbon\Carbon::parse($tolovSana)->format('Ym');
        $prefix = $this->kvitansiyaPrefiks($tulovTuriId);

        $query = Tulov::where('kvitansiya_raqam', 'like', $prefix . '-' . $oy . '-%');
        if ($lock) {
            $query->lockForUpdate();
        }
        $last = $query->orderByDesc('id')->value('kvitansiya_raqam');

        $tartib = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $tartib = (int)$m[1] + 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $oy, $tartib);
    }

    /**
     * To'lov turi nomi bo'yicha tegishli kassa turini aniqlaydi (naqd/terminal/bank)
     * va shu turdagi REAL pul harakati ekanini tekshiradi. Chegirma, tovar
     * qaytarilishi, hisobdan chiqarish kabi pul harakati bo'lmagan "to'lov
     * turlari" pul oqimiga yozilmasligi kerak.
     */
    private function tulovTuriKassaTuri(int $tulovTuriId): ?string
    {
        $nomi = mb_strtolower((string) (TulovTuri::find($tulovTuriId)?->nomi ?? ''));

        // Pul harakati BO'LMAGAN (faqat hisob-kitob/tuzatish) turlar — pul
        // oqimiga yozilmaydi.
        $polMasMatnlar = ['чегирма', 'кайтиб', 'қайтиб', 'списат', 'акция', 'spisaniye', 'discount'];
        foreach ($polMasMatnlar as $m) {
            if (str_contains($nomi, $m)) return null;
        }

        if (str_contains($nomi, 'накд') || str_contains($nomi, 'naqd')) {
            return 'naqd';
        }
        if (str_contains($nomi, 'терм') || str_contains($nomi, 'term')) {
            return 'terminal';
        }
        // Qolgan barcha real to'lov kanallari (bank, klik, autopay, paynet
        // va h.k.) — bank kassasiga yoziladi.
        return 'bank';
    }

    /**
     * Shartnoma bo'yicha qabul qilingan naqd/terminal/bank to'lovini
     * "Pul oqimlari" jadvaliga avtomatik kirim sifatida yozadi — kassir
     * qo'lda kirim kiritishiga hojat qolmaydi va sverka osonlashadi.
     * Pul harakati bo'lmagan to'lov turlari (chegirma va h.k.) uchun
     * hech narsa yozilmaydi.
     */
    public function pulOqimigaYoz(
        int $filialId,
        int $tulovTuriId,
        float $summa,
        string $sana,
        string $kategoriyaKodi,
        string $izoh,
        string $manbaTur,
        int $manbaId,
    ): void {
        $kassaTuri = $this->tulovTuriKassaTuri($tulovTuriId);
        if (!$kassaTuri) return;

        $this->pulOqimigaYozKassaTuri(
            filialId: $filialId,
            kassaTuri: $kassaTuri,
            summa: $summa,
            sana: $sana,
            kategoriyaKodi: $kategoriyaKodi,
            izoh: $izoh,
            manbaTur: $manbaTur,
            manbaId: $manbaId,
        );
    }

    /**
     * Pul oqimlariga avtomatik kirim yozish — kassa turi (naqd/terminal/bank)
     * to'g'ridan-to'g'ri beriladi (masalan POS savdosi kabi to'lov turi
     * tulov_turlari jadvaliga bog'liq bo'lmagan joylarda foydalaniladi).
     */
    public function pulOqimigaYozKassaTuri(
        int $filialId,
        string $kassaTuri,
        float $summa,
        string $sana,
        string $kategoriyaKodi,
        string $izoh,
        string $manbaTur,
        int $manbaId,
        string $yunalish = 'kirim',
    ): void {
        if ($summa <= 0) return;

        // Shu xil yozuv allaqachon mavjud bo'lsa (masalan qayta urinish) —
        // ikki marta yozilmasin.
        $mavjud = \App\Models\PulOqim::where('manba_tur', $manbaTur)
            ->where('manba_id', $manbaId)->exists();
        if ($mavjud) return;

        $kategoriya = \App\Models\PulKategoriya::where('kod', $kategoriyaKodi)->first();
        if (!$kategoriya) return;

        $kassa = \App\Models\Kassa::where('filial_id', $filialId)
            ->where('tur', $kassaTuri)->faol()->first()
            ?? \App\Models\Kassa::where('filial_id', $filialId)->faol()->first();
        if (!$kassa) return;

        \App\Models\PulOqim::create([
            'filial_id'      => $filialId,
            'kassa_id'       => $kassa->id,
            'kategoriya_id'  => $kategoriya->id,
            'xodim_id'       => Auth::id(),
            'yunalish'       => $yunalish,
            'sana'           => $sana,
            'summa'          => $summa,
            'izoh'           => $izoh,
            'manba_tur'      => $manbaTur,
            'manba_id'       => $manbaId,
            'holat'          => 'tasdiqlangan',
            'tasdiqlagan_id' => Auth::id(),
        ]);
    }

    /**
     * Manba o'chirilganda (shartnoma to'lovi, ta'minotchiga to'lov va h.k.)
     * shu manbadan avtomatik yaratilgan "Pul oqimlari" yozuvini ham
     * o'chiradi — aks holda kassa qoldig'ida "osilib qolgan" yozuv qoladi.
     * Pul oqimlariga TO'G'RIDAN-TO'G'RI ("qo'lda") kiritilgan yozuvlar
     * bunga aloqasi yo'q — ular faqat shu modulning o'zidan o'chiriladi.
     */
    public function pulOqiminiOchir(string $manbaTur, int $manbaId): void
    {
        \App\Models\PulOqim::where('manba_tur', $manbaTur)
            ->where('manba_id', $manbaId)
            ->delete();
    }
}
