<?php

namespace App\Services;

use App\Models\LitsenziyaFaollashtirish;
use App\Models\Mijoz;
use App\Models\RegKredit;
use App\Models\Sozlama;
use App\Models\TovarKatalog;
use Carbon\Carbon;

/**
 * Litsenziya/aktivatsiya xizmati.
 *
 * Ishlash tartibi:
 *  - Har bir o'rnatish o'ziga xos "do'kon kodi"ga ega (birinchi murojaatda avtomatik yaratiladi).
 *  - Sotuvchi (vendor) mijozdan do'kon kodini olib, o'zining mahalliy generator skripti orqali
 *    shu do'konga MOS, bir martalik "faollashtirish kodi" yaratadi (HMAC-SHA256, .env'dagi
 *    LITSENZIYA_MAXFIY_KALIT bilan imzolangan — bu kalit har bir o'rnatish uchun ALOHIDA bo'lishi shart).
 *    Kod ichida muddatdan tashqari ikki narsa kodlangan:
 *      1) ruxsatlar bitmask — litsenziya MUDDATI TUGAGANDA qaysi modullar bloklanishi
 *      2) tarif — litsenziya FAOL bo'lganda ham amal qiladigan son-limitlari va modul
 *         ruxsatlari (Demo/Yengil/Premium/Maxsus, config('litsenziya.tariflar'))
 *  - Mijoz shu kodni shu sahifada kiritadi — muddat, ruxsatlar va tarif shu kod asosida yangilanadi.
 *  - config('litsenziya.maxfiy_kalit') bo'sh bo'lsa (masalan lokal/test muhitda) yoki
 *    Sozlama::ol('litsenziya_yoqilgan') '1' bo'lmasa — tizim hech narsani bloklamaydi.
 */
class Litsenziya
{
    /** Boshqariladigan modullar ro'yxati (litsenziya MUDDATI TUGAGANDA bloklanishi mumkin bo'lganlar): kalit => [bit, nomi] */
    public const MODULLAR = [
        'mijoz'     => ['bit' => 1,  'nomi' => "Mijoz qo'shish"],
        'tovar'     => ['bit' => 2,  'nomi' => "Tovar qo'shish"],
        'shartnoma' => ['bit' => 4,  'nomi' => "Shartnoma qo'shish"],
        'hisobot'   => ['bit' => 8,  'nomi' => 'Hisobotlar'],
        'dashboard' => ['bit' => 16, 'nomi' => 'Bosh sahifa (dashboard)'],
        'pos'       => ['bit' => 32, 'nomi' => 'Naqd sotish (POS)'],
    ];

    /** Hech narsa belgilanmagan holatda — barcha modullar bloklanadi (xavfsiz tomonga moslash) */
    private const RUXSATLAR_DEFAULT = 0xFF;

    /** Faollashtirish kodida 1 xonali hex bilan kodlanadigan tarif identifikatorlari */
    public const TARIF_IDLAR = [
        0 => 'maxsus',
        1 => 'demo',
        2 => 'yengil',
        3 => 'premium',
    ];

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

    /** Oxirgi faollashtirish kodida belgilangan modul-bloklash bayroqlari (bitmask) */
    public static function ruxsatlarBitmask(): int
    {
        $qiymat = Sozlama::ol('litsenziya_ruxsatlar', '');
        return $qiymat === '' ? self::RUXSATLAR_DEFAULT : (int) $qiymat;
    }

    /** Joriy tarif kaliti: 'maxsus' | 'demo' | 'yengil' | 'premium' */
    public static function joriyTarif(): string
    {
        $tarif = Sozlama::ol('litsenziya_tarif', 'maxsus');
        return isset(config('litsenziya.tariflar')[$tarif]) ? $tarif : 'maxsus';
    }

    /** Joriy tarifning to'liq sozlamalari (limitlar, modul ruxsatlari) */
    public static function tarifMalumot(): array
    {
        return config('litsenziya.tariflar')[self::joriyTarif()] ?? config('litsenziya.tariflar')['maxsus'];
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

    /** Umumiy holat bo'yicha bloklanganmi (eski, modulga bog'liq bo'lmagan tekshiruv) */
    public static function bloklanganmi(): bool
    {
        return self::holati() === 'yopiq';
    }

    /**
     * Berilgan modul ($kalit — self::MODULLAR dagi kalit) litsenziya tugagani sababli
     * hozir bloklanganmi? Faqat holat 'yopiq' bo'lganda VA shu modulning biti
     * faollashtirish kodida "bloklansin" deb belgilangan bo'lsagina true qaytaradi.
     */
    public static function moduleBloklanganmi(string $modul): bool
    {
        if (self::holati() !== 'yopiq') {
            return false;
        }

        $bit = self::MODULLAR[$modul]['bit'] ?? null;
        if ($bit === null) {
            return false;
        }

        return (self::ruxsatlarBitmask() & $bit) === $bit;
    }

    /**
     * Tarif bo'yicha SON limitiga yetilganmi (litsenziya muddati tugashidan qat'iy nazar,
     * faqat litsenziya YOQILGAN bo'lganda amal qiladi). $modul: 'mijoz' | 'tovar' | 'shartnoma'.
     */
    public static function limitOshganmi(string $modul): bool
    {
        if (!self::yoqilganmi()) {
            return false;
        }

        $limit = self::tarifMalumot()[$modul . '_max'] ?? null;
        if ($limit === null) {
            return false;
        }

        $joriy = match ($modul) {
            'mijoz' => Mijoz::count(),
            'tovar' => TovarKatalog::count(),
            'shartnoma' => RegKredit::count(),
            default => 0,
        };

        return $joriy >= $limit;
    }

    /** Tarif bo'yicha shu modul uchun limit qiymati (null = cheklovsiz) */
    public static function limitQiymati(string $modul): ?int
    {
        return self::tarifMalumot()[$modul . '_max'] ?? null;
    }

    /** Joriy son (limitdan qat'iy nazar) — UI'da "12/20" kabi ko'rsatish uchun */
    public static function joriySon(string $modul): int
    {
        return match ($modul) {
            'mijoz' => Mijoz::count(),
            'tovar' => TovarKatalog::count(),
            'shartnoma' => RegKredit::count(),
            default => 0,
        };
    }

    /** Tarif bo'yicha POS (naqd sotish) ochiqmi (litsenziya yoqilgan bo'lishi sharti bilan) */
    public static function posOchiqmi(): bool
    {
        if (!self::yoqilganmi()) {
            return true;
        }
        return (bool) (self::tarifMalumot()['pos'] ?? true);
    }

    /** Tarif bo'yicha hisobotlar (murakkab: konstruktor/excel/kechikish-tahlili) cheklanganmi */
    public static function hisobotCheklanganmi(): bool
    {
        if (!self::yoqilganmi()) {
            return false;
        }
        return (bool) (self::tarifMalumot()['hisobot_cheklangan'] ?? false);
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
     * Format: "{YYYYMMDD}{FF}{T}-{10 hex imzo}" — masalan "20270625171-A1B2C3D4E5"
     * FF — 2 xonali hex bitmask (muddat tugaganda bloklanadigan modullar),
     * T — 1 xonali hex tarif identifikatori (self::TARIF_IDLAR).
     *
     * @return array{ok:bool, xabar:string}
     */
    public static function faollashtir(string $kod, ?int $xodimId = null): array
    {
        $kod = trim(strtoupper($kod));

        if (LitsenziyaFaollashtirish::where('kod', $kod)->exists()) {
            return ['ok' => false, 'xabar' => 'Bu kod avval ishlatilgan. Har bir kod faqat bir marta amal qiladi.'];
        }

        if (!preg_match('/^(\d{8})([A-F0-9]{2})([A-F0-9])-([A-F0-9]{10})$/', $kod, $m)) {
            return ['ok' => false, 'xabar' => 'Kod formati noto\'g\'ri.'];
        }

        [$hammasi, $muddatStr, $ruxsatlarHex, $tarifHex, $imzoBerilgan] = $m;

        $maxfiyKalit = config('litsenziya.maxfiy_kalit');
        if (!$maxfiyKalit) {
            return ['ok' => false, 'xabar' => 'Litsenziya tizimi sozlanmagan (maxfiy kalit yo\'q).'];
        }

        $kutilganImzo = strtoupper(substr(
            hash_hmac('sha256', self::dukonKodi() . ':' . $muddatStr . $ruxsatlarHex . $tarifHex, $maxfiyKalit),
            0,
            10
        ));

        if (!hash_equals($kutilganImzo, $imzoBerilgan)) {
            return ['ok' => false, 'xabar' => 'Kod noto\'g\'ri yoki boshqa do\'kon uchun yaratilgan.'];
        }

        $yangiMuddat = Carbon::createFromFormat('Ymd', $muddatStr)->endOfDay();
        $ruxsatlar = hexdec($ruxsatlarHex);
        $tarif = self::TARIF_IDLAR[hexdec($tarifHex)] ?? 'maxsus';

        LitsenziyaFaollashtirish::create([
            'kod' => $kod,
            'yangi_muddat' => $yangiMuddat,
            'xodim_id' => $xodimId,
        ]);

        Sozlama::saqlash([
            'litsenziya_muddati' => $yangiMuddat->format('Y-m-d'),
            'litsenziya_yoqilgan' => '1',
            'litsenziya_ruxsatlar' => (string) $ruxsatlar,
            'litsenziya_tarif' => $tarif,
        ]);

        $tarifNomi = config('litsenziya.tariflar')[$tarif]['nomi'] ?? $tarif;

        return ['ok' => true, 'xabar' => "Litsenziya muvaffaqiyatli faollashtirildi. Tarif: {$tarifNomi}. Yangi muddat: {$yangiMuddat->format('d.m.Y')}"];
    }
}
