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

            // Birinchi to'lanmagan grafik qatorini topish (tartib bo'yicha)
            $grafikQator = Grafik::where('reg_kredit_id', $kredit->id)
                ->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan'])
                ->orderBy('oylik_tartib')
                ->first();

            // To'lovni saqlash
            $tulov = Tulov::create([
                'reg_kredit_id'    => $kredit->id,
                'grafik_id'        => $grafikQator?->id,
                'xodim_id'         => Auth::id(),
                'tulov_turi_id'    => $malumot['tulov_turi_id'],
                'summa'            => $malumot['summa'],
                'tolov_sana'       => $malumot['tolov_sana'],
                'kvitansiya_raqam' => $malumot['kvitansiya_raqam'] ?? $this->kvitansiyaRaqamYarat($malumot['tulov_turi_id'], $malumot['tolov_sana'] ?? now()->toDateString()),
                'izoh'             => $malumot['izoh'] ?? null,
            ]);

            // Grafik holatini yangilash
            if ($grafikQator) {
                $yangiTolangan = $grafikQator->tolangan_summa + $malumot['summa'];
                $grafikHolat   = $yangiTolangan >= ($grafikQator->tolov_summa ?? 0)
                    ? 'tolangan'
                    : 'qisman';

                $grafikQator->update([
                    'tolangan_summa' => $yangiTolangan,
                    'tolangan_sana'  => $malumot['tolov_sana'],
                    'holat'          => $grafikHolat,
                ]);
            }

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

    private function kvitansiyaRaqamYarat(int $tulovTuriId, string $tolovSana): string
    {
        $oy  = \Carbon\Carbon::parse($tolovSana)->format('Ym');
        $tur = TulovTuri::find($tulovTuriId);
        $nom = mb_strtolower((string)($tur->nomi ?? ''));

        // Naqd: lotincha "naqd" yoki kirilcha "накд"
        $isNaqd = str_contains($nom, 'naqd') || str_contains($nom, 'накд');
        $prefix = $isNaqd ? 'N' : 'B';

        $last = Tulov::where('kvitansiya_raqam', 'like', $prefix . '-' . $oy . '-%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('kvitansiya_raqam');

        $tartib = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $tartib = (int)$m[1] + 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $oy, $tartib);
    }
}
