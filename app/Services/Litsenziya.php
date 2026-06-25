<?php

namespace App\Services;

use App\Models\LitsenziyaFaollashtirish;
use App\Models\Sozlama;
use Carbon\Carbon;

/**
 * Litsenziya/aktivatsiya xizmati.
 *
 * Ishlash tartibi:
 *  - Har bir o'rnatish o'ziga xos "do'kon kodi"ga ega (birinchi murojaatda avtomatik yaratiladi).
 *  - Sotuvchi (vendor) mijozdan do'kon kodini olib, o'zining mahalliy generator skripti orqali
 *    shu do'konga MOS, bir martalik "faollashtirish kodi" yaratadi (HMAC-SHA256, .env'dagi
 *    LITSENZIYA_MAXFIY_KALIT bilan imzolangan — bu kalit har bir o'rnatish uchun ALOHIDA bo'lishi shart).
 *  - Mijoz shu kodni shu sahifada kiritadi — muddat config('litsenziya.yengillik_kun') siljiydi.
 *  - config('litsenziya.maxfiy_kalit') bo'sh bo'lsa (masalan lokal/test muhitda) yoki
 *    Sozlama::ol('litsenziya_yoqilgan') '1' bo'lmasa — tizim hech narsani bloklamaydi.
 */
class Litsenziya
{
    /** Joriy o'rnatishning o'ziga xos kodi — yo'q bo'lsa avtomatik yaratiladi va saqlanadi */
    public static function dukonKodi(): string
    {
        $kod = Sozlama::ol('litsenziya_dukon_kodi', '');
        if ($kod) {
            return $kod;
        }

        $kod = strtoupper(bin2hex(random_bytes(6))); // 12 belgili
        Sozlama::saqlash(['litsenziya_dukon_kodi' => $kod]);
        return $kod;
    }

    /** Do'kon kodini o'qishga qulay shaklda qaytaradi: XXXX-XXXX-XXXX */
    public static function dukonKodiChiroyli(): string
    {
        return implode('-', str_split(self::dukonKodi(), 4));
    }

    public static function yoqilganmi(): bool
    {
        return Sozlama::ol('litsenziya_yoqilgan', '0') === '1'
            && config('litsenziya.maxfiy_kalit') !== '';
    }

    /** Joriy litsenziya muddati (sana) — belgilanmagan bo'lsa null */
    public static function muddati(): ?Carbon
    {
        $sana = Sozlama::ol('litsenziya_muddati', '');
        return $sana ? Carbon::parse($sana) : null;
    }

    /** 'faol' | 'ogohlantirish' | 'yengillik' | 'yopiq' */
    public static function holati(): string
    {
        if (!self::yoqilganmi()) {
            return 'faol';
        }

        $muddat = self::muddati();
        if (!$muddat) {
            return 'yopiq';
        }

        $bugun = Carbon::today();
        if ($bugun->lte($muddat)) {
            $qolganKun = $bugun->diffInDays($muddat, false);
            return $qolganKun <= config('litsenziya.ogohlantirish_kun', 14) ? 'ogohlantirish' : 'faol';
        }

        $yengillikChegara = $muddat->copy()->addDays(config('litsenziya.yengillik_kun', 7));
        return $bugun->lte($yengillikChegara) ? 'yengillik' : 'yopiq';
    }

    /** Yangi mijoz/tovar/shartnoma qo'shish bloklanishi kerakmi? */
    public static function bloklanganmi(): bool
    {
        return self::holati() === 'yopiq';
    }

    public static function qolganKun(): ?int
    {
        $muddat = self::muddati();
        if (!$muddat) {
            return null;
        }
        return Carbon::today()->diffInDays($muddat, false);
    }

    /**
     * Faollashtirish kodini tekshiradi va qo'llaydi.
     * Format: "{YYYYMMDD}-{10 hex imzo}" — masalan "20270625-A1B2C3D4E5"
     *
     * @return array{ok:bool, xabar:string}
     */
    public static function faollashtir(string $kod, ?int $xodimId = null): array
    {
        $kod = trim(strtoupper($kod));

        if (LitsenziyaFaollashtirish::where('kod', $kod)->exists()) {
            return ['ok' => false, 'xabar' => 'Bu kod avval ishlatilgan. Har bir kod faqat bir marta amal qiladi.'];
        }

        if (!preg_match('/^(\d{8})-([A-F0-9]{10})$/', $kod, $m)) {
            return ['ok' => false, 'xabar' => 'Kod formati noto\'g\'ri.'];
        }

        [$hammasi, $muddatStr, $imzoBerilgan] = $m;

        $maxfiyKalit = config('litsenziya.maxfiy_kalit');
        if (!$maxfiyKalit) {
            return ['ok' => false, 'xabar' => 'Litsenziya tizimi sozlanmagan (maxfiy kalit yo\'q).'];
        }

        $kutilganImzo = strtoupper(substr(
            hash_hmac('sha256', self::dukonKodi() . ':' . $muddatStr, $maxfiyKalit),
            0,
            10
        ));

        if (!hash_equals($kutilganImzo, $imzoBerilgan)) {
            return ['ok' => false, 'xabar' => 'Kod noto\'g\'ri yoki boshqa do\'kon uchun yaratilgan.'];
        }

        $yangiMuddat = Carbon::createFromFormat('Ymd', $muddatStr)->endOfDay();

        LitsenziyaFaollashtirish::create([
            'kod' => $kod,
            'yangi_muddat' => $yangiMuddat,
            'xodim_id' => $xodimId,
        ]);

        Sozlama::saqlash([
            'litsenziya_muddati' => $yangiMuddat->format('Y-m-d'),
            'litsenziya_yoqilgan' => '1',
        ]);

        return ['ok' => true, 'xabar' => "Litsenziya muvaffaqiyatli faollashtirildi. Yangi muddat: {$yangiMuddat->format('d.m.Y')}"];
    }
}
