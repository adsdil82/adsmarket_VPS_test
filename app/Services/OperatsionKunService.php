<?php

namespace App\Services;

use App\Models\Foydalanuvchi;
use App\Models\Grafik;
use App\Models\KunYopishLogi;
use App\Models\OperatsionKun;
use App\Models\RegKredit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * OperatsionKunService — filial bo'yicha kunlik yopish/ochish jarayonini
 * boshqaradi. davomat_oy_holati (Ish Haqi moduli) patterniga o'xshash,
 * lekin filial+kun granulyarligida va audit logi bilan.
 */
class OperatsionKunService
{
    /** Filial uchun joriy (bugungi) operatsion kun yozuvini qaytaradi — bo'lmasa yaratadi. */
    public function joriyKun(int $filialId): OperatsionKun
    {
        return OperatsionKun::firstOrCreate(
            ['filial_id' => $filialId, 'sana' => Carbon::today()->toDateString()],
            ['status' => 'ochiq']
        );
    }

    /**
     * Kunni yopish: kechikkan to'lovlarni belgilaydi, kunlik statistikani
     * yig'adi, operatsion_kunlar va kun_yopish_logi'ni yangilaydi.
     */
    public function kunniYop(int $filialId, string $sana, Foydalanuvchi $user): array
    {
        return DB::transaction(function () use ($filialId, $sana, $user) {
            $kun = OperatsionKun::where('filial_id', $filialId)->where('sana', $sana)->lockForUpdate()->first();

            if (!$kun) {
                $kun = OperatsionKun::create(['filial_id' => $filialId, 'sana' => $sana, 'status' => 'ochiq']);
            }

            if ($kun->status === 'yopiq') {
                throw new \RuntimeException("Bu kun ({$sana}) allaqachon yopilgan.");
            }

            $oldingiOchiq = OperatsionKun::where('filial_id', $filialId)
                ->where('sana', '<', $sana)
                ->where('status', 'ochiq')
                ->exists();

            if ($oldingiOchiq) {
                throw new \RuntimeException('Oldingi kun hali yopilmagan — ketma-ket yopish tartibi buzilishi mumkin emas.');
            }

            $kreditIds = RegKredit::where('filial_id', $filialId)
                ->whereIn('holat', ['faol', 'muddati_otgan'])
                ->pluck('id');

            $grafiklar = Grafik::whereIn('reg_kredit_id', $kreditIds)
                ->whereIn('holat', ['tolanmagan', 'qisman'])
                ->whereNotNull('tolov_sana')
                ->where('tolov_sana', '<', $sana)
                ->get();

            $kechikkanKreditIds = [];
            $jamiKechikkanSumma = 0;

            foreach ($grafiklar as $g) {
                $g->kechikish_kun = abs(Carbon::parse($sana)->diffInDays($g->tolov_sana));
                $g->holat = 'muddati_otgan';
                $g->save();

                $kechikkanKreditIds[$g->reg_kredit_id] = true;
                $jamiKechikkanSumma += ($g->tolov_summa - $g->tolangan_summa);
            }

            RegKredit::where('filial_id', $filialId)
                ->where('holat', 'faol')
                ->whereHas('grafik', fn ($q) => $q->where('holat', 'muddati_otgan'))
                ->update(['holat' => 'muddati_otgan']);

            $jamiShartnoma = RegKredit::where('filial_id', $filialId)
                ->whereIn('holat', ['faol', 'muddati_otgan'])
                ->count();

            $jamiTolov = DB::table('tulovlar')
                ->join('reg_kredit', 'reg_kredit.id', '=', 'tulovlar.reg_kredit_id')
                ->where('reg_kredit.filial_id', $filialId)
                ->whereDate('tulovlar.tolov_sana', $sana)
                ->sum('tulovlar.summa');

            $natija = [
                'sana'                   => $sana,
                'jami_shartnomalar'      => $jamiShartnoma,
                'jami_tolov'             => (float) $jamiTolov,
                'kechikkan_shartnomalar' => count($kechikkanKreditIds),
                'kechikkan_summa'        => (float) $jamiKechikkanSumma,
            ];

            $kun->update([
                'status'         => 'yopiq',
                'yopilgan_vaqt'  => now(),
                'yopgan_user_id' => $user->id,
            ]);

            KunYopishLogi::create([
                'operatsion_kun_id' => $kun->id,
                'amal'              => 'yopish',
                'user_id'           => $user->id,
                'vaqt'              => now(),
                'natija_json'       => $natija,
            ]);

            cache()->forget("operatsion_kun_{$filialId}");

            return $natija;
        });
    }

    /** Kunni qayta ochish — faqat admin yoki 'eski_tahrirlash' ruxsati bor foydalanuvchi. */
    public function kunniOch(int $filialId, string $sana, Foydalanuvchi $user, ?string $izoh = null): array
    {
        if (!$user->isAdmin() && !$user->ruxsat('operatsion_kun', 'eski_tahrirlash')) {
            throw new \RuntimeException("Bu amal uchun sizda ruxsat yo'q.");
        }

        return DB::transaction(function () use ($filialId, $sana, $user, $izoh) {
            $kun = OperatsionKun::where('filial_id', $filialId)->where('sana', $sana)->lockForUpdate()->first();

            if (!$kun || $kun->status !== 'yopiq') {
                throw new \RuntimeException('Bu kun yopilmagan — ochish shart emas.');
            }

            $kun->update([
                'status'         => 'ochiq',
                'ochilgan_vaqt'  => now(),
                'ochgan_user_id' => $user->id,
                'izoh'           => $izoh,
            ]);

            KunYopishLogi::create([
                'operatsion_kun_id' => $kun->id,
                'amal'              => 'ochish',
                'user_id'           => $user->id,
                'vaqt'              => now(),
                'natija_json'       => ['izoh' => $izoh],
            ]);

            cache()->forget("operatsion_kun_{$filialId}");

            return ['ok' => true, 'sana' => $sana];
        });
    }

    /**
     * Yopish tugmasi bosilishidan oldin AJAX orqali oldindan ko'rish
     * (nechta shartnoma, nechta kechikkan) — DB'ga hech narsa yozmaydi.
     */
    public function yopishOldinKorish(int $filialId, string $sana): array
    {
        $kreditIds = RegKredit::where('filial_id', $filialId)
            ->whereIn('holat', ['faol', 'muddati_otgan'])
            ->pluck('id');

        $kechikkanSoni = Grafik::whereIn('reg_kredit_id', $kreditIds)
            ->whereIn('holat', ['tolanmagan', 'qisman'])
            ->whereNotNull('tolov_sana')
            ->where('tolov_sana', '<', $sana)
            ->distinct('reg_kredit_id')
            ->count('reg_kredit_id');

        return [
            'jami_shartnomalar'      => $kreditIds->count(),
            'kechikkan_shartnomalar' => $kechikkanSoni,
        ];
    }
}
