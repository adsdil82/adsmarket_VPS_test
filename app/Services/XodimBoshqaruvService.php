<?php

namespace App\Services;

use App\Models\BonusTuri;
use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\MehnatShartnomaShabloni;
use App\Models\XodimBonus;
use App\Models\XodimDavomat;
use App\Models\XodimIshHaqiSozlama;
use App\Models\XodimShartnoma;
use App\Models\XodimTatil;
use App\Models\DavomatOyHolati;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * "Xodimlar" tabi uchun — xodim qo'shish/tahrirlash (tizimdan yoki qo'lda),
 * ta'til, bonus biriktirish va mehnat shartnomasi bilan bog'liq operatsiyalar.
 */
class XodimBoshqaruvService
{
    /**
     * Yangi xodim qo'shish. manba='qolda' bo'lsa — loginsiz Foydalanuvchi qatori
     * ham yaratiladi (avtomatik email/parol bilan, tizimga_kirish_bormi=false).
     * manba='tizim' bo'lsa — mavjud Foydalanuvchi uchun ish haqi profili yaratiladi.
     */
    public function xodimQoshish(array $data): Foydalanuvchi
    {
        return DB::transaction(function () use ($data) {
            if ($data['manba'] === 'qolda') {
                $xodim = Foydalanuvchi::create([
                    'filial_id'             => $data['filial_id'] ?? null,
                    'ism_familiya'          => $data['ism_familiya'],
                    'email'                 => $this->vaqtinchalikEmail(),
                    'password'              => Str::random(40),
                    'rol'                   => 'hisobchi',
                    'holat'                 => 'faol',
                    'tizimga_kirish_bormi'  => false,
                ]);
            } else {
                $xodim = Foydalanuvchi::findOrFail($data['xodim_id']);
            }

            XodimIshHaqiSozlama::updateOrCreate(
                ['xodim_id' => $xodim->id],
                [
                    'oklad'             => $data['oklad'],
                    'lavozim'           => $data['lavozim'] ?? null,
                    'telefon'           => $data['telefon'] ?? null,
                    'manzil'            => $data['manzil'] ?? null,
                    'passport_malumot'  => $data['passport_malumot'] ?? null,
                    'ishga_kirgan_sana' => $data['ishga_kirgan_sana'],
                    'holat'             => 'faol',
                ]
            );

            return $xodim->fresh();
        });
    }

    /** Xodim profili (lavozim/aloqa/ishga-bo'shash sana/qo'shimcha ish haqi) tahrirlash. */
    public function xodimTahrirlash(Foydalanuvchi $xodim, array $data): XodimIshHaqiSozlama
    {
        if (!$xodim->tizimga_kirish_bormi && array_key_exists('ism_familiya', $data) && $data['ism_familiya']) {
            $xodim->update(['ism_familiya' => $data['ism_familiya']]);
        }

        $ishdanBoshagan = $data['ishdan_boshagan_sana'] ?? null;

        return tap(
            XodimIshHaqiSozlama::firstOrCreate(['xodim_id' => $xodim->id])
        )->update([
            'lavozim'                    => $data['lavozim'] ?? null,
            'telefon'                    => $data['telefon'] ?? null,
            'manzil'                     => $data['manzil'] ?? null,
            'passport_malumot'           => $data['passport_malumot'] ?? null,
            'ishga_kirgan_sana'          => $data['ishga_kirgan_sana'] ?? null,
            'ishdan_boshagan_sana'       => $ishdanBoshagan,
            'holat'                      => $ishdanBoshagan ? 'nofaol' : 'faol',
            'qoshimcha_ish_haqi'         => $data['qoshimcha_ish_haqi'] ?? 0,
            'qoshimcha_boshlanish_sana'  => $data['qoshimcha_boshlanish_sana'] ?? null,
            'qoshimcha_tugash_sana'      => $data['qoshimcha_tugash_sana'] ?? null,
        ]);
    }

    // ─── Ta'til ─────────────────────────────────────────────────────

    /** Ta'til berish — oraliqdagi kunlar (yopilmagan oylarda) xodim_davomat'ga sinxronlanadi. */
    public function tatilBer(Foydalanuvchi $xodim, array $data): XodimTatil
    {
        return DB::transaction(function () use ($xodim, $data) {
            $tatil = XodimTatil::create([
                'xodim_id'                     => $xodim->id,
                'turi'                          => $data['turi'],
                'boshlanish_sana'               => $data['boshlanish_sana'],
                'rejalashtirilgan_qaytish_sana' => $data['rejalashtirilgan_qaytish_sana'],
                'izoh'                          => $data['izoh'] ?? null,
                'holat'                         => 'rejalashtirilgan',
                'created_by'                    => Auth::id(),
            ]);

            $this->tatilDavomatgaSinxronla($tatil);

            return $tatil;
        });
    }

    /** Ta'tildan qaytishni belgilash. */
    public function tatilQaytdi(XodimTatil $tatil, ?string $haqiqiySana = null): XodimTatil
    {
        $tatil->update([
            'haqiqiy_qaytgan_sana' => $haqiqiySana ?? now()->toDateString(),
            'holat'                => 'yakunlandi',
        ]);

        return $tatil->fresh();
    }

    /** Hali boshlanmagan ta'tilni bekor qilish — sinxronlangan davomat kunlari tozalanadi. */
    public function tatilBekorQil(XodimTatil $tatil): XodimTatil
    {
        if ($tatil->boshlanish_sana->isPast()) {
            throw new \RuntimeException("Bu ta'til allaqachon boshlangan, bekor qilib bo'lmaydi.");
        }

        $holatKutilgan = XodimTatil::DAVOMAT_HOLATI[$tatil->turi] ?? 'tatil';

        XodimDavomat::where('xodim_id', $tatil->xodim_id)
            ->whereBetween('sana', [$tatil->boshlanish_sana->toDateString(), $tatil->rejalashtirilgan_qaytish_sana->toDateString()])
            ->where('holat', $holatKutilgan)
            ->delete();

        $tatil->update(['holat' => 'bekor_qilindi']);

        return $tatil->fresh();
    }

    private function tatilDavomatgaSinxronla(XodimTatil $tatil): void
    {
        $holat = XodimTatil::DAVOMAT_HOLATI[$tatil->turi] ?? 'tatil';
        $sana  = $tatil->boshlanish_sana->copy();

        while ($sana->lte($tatil->rejalashtirilgan_qaytish_sana)) {
            if (!DavomatOyHolati::yopiqmi($sana->year, $sana->month)) {
                XodimDavomat::updateOrCreate(
                    ['xodim_id' => $tatil->xodim_id, 'sana' => $sana->toDateString()],
                    ['holat' => $holat, 'belgilagan_id' => Auth::id()]
                );
            }
            $sana->addDay();
        }
    }

    // ─── Bonus ──────────────────────────────────────────────────────

    public function bonusBiriktirish(Foydalanuvchi $xodim, array $data): XodimBonus
    {
        if (!empty($data['tugash_yil'])) {
            $boshlanadi = $data['boshlanish_yil'] * 12 + $data['boshlanish_oy'];
            $tugaydi    = $data['tugash_yil'] * 12 + $data['tugash_oy'];
            if ($tugaydi < $boshlanadi) {
                throw new \RuntimeException("Tugash oyi boshlanish oyidan oldin bo'lishi mumkin emas.");
            }
        }

        return XodimBonus::create([
            'xodim_id'       => $xodim->id,
            'bonus_turi_id'  => $data['bonus_turi_id'],
            'qiymat'         => $data['qiymat'] !== null && $data['qiymat'] !== '' ? $data['qiymat'] : null,
            'boshlanish_oy'  => $data['boshlanish_oy'],
            'boshlanish_yil' => $data['boshlanish_yil'],
            'tugash_oy'      => $data['tugash_oy'] ?: null,
            'tugash_yil'     => $data['tugash_yil'] ?: null,
            'izoh'           => $data['izoh'] ?? null,
            'holat'          => 'faol',
            'created_by'     => Auth::id(),
        ]);
    }

    public function bonusBekorQil(XodimBonus $bonus): XodimBonus
    {
        $bonus->update(['holat' => 'bekor_qilingan']);
        return $bonus->fresh();
    }

    public function bonusTuriSaqla(array $data, ?int $id = null): BonusTuri
    {
        return BonusTuri::updateOrCreate(['id' => $id], [
            'nomi'            => $data['nomi'],
            'tavsif'          => $data['tavsif'] ?? null,
            'hisoblash_turi'  => $data['hisoblash_turi'],
            'standart_qiymat' => $data['standart_qiymat'] ?? 0,
            'holat'           => $data['holat'] ?? 'faol',
            'sort_order'      => $data['sort_order'] ?? 0,
        ]);
    }

    // ─── Mehnat shartnomasi ─────────────────────────────────────────

    public function shartnomaShabloniSaqla(array $data, ?int $id = null): MehnatShartnomaShabloni
    {
        return MehnatShartnomaShabloni::updateOrCreate(['id' => $id], [
            'nomi'       => $data['nomi'],
            'matn'       => $data['matn'],
            'holat'      => $data['holat'] ?? 'faol',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /** Tanlangan shablondan xodim uchun shartnoma matnini render qilib, loyiha sifatida yaratadi. */
    public function shartnomaYarat(Foydalanuvchi $xodim, MehnatShartnomaShabloni $shablon, array $data): XodimShartnoma
    {
        $sozlama = $xodim->ishHaqiSozlama;
        $sana    = $data['sana'] ?? now()->toDateString();

        $vars = [
            'ism_familiya'      => $xodim->ism_familiya,
            'lavozim'           => $sozlama->lavozim ?? '',
            'oklad'             => number_format((float) ($sozlama->oklad ?? 0), 0, '.', ' '),
            'ishga_kirgan_sana' => optional($sozlama?->ishga_kirgan_sana)->format('d.m.Y') ?? '',
            'tashkilot_nomi'    => config('app.name'),
            'filial_nomi'       => $xodim->filial?->nomi ?? '',
            'manzil'            => $sozlama->manzil ?? '',
            'passport_malumot'  => $sozlama->passport_malumot ?? '',
            'telefon'           => $sozlama->telefon ?? '',
            'shartnoma_raqami'  => $data['shartnoma_raqami'] ?? '',
            'shartnoma_sana'    => Carbon::parse($sana)->format('d.m.Y'),
        ];

        return XodimShartnoma::create([
            'xodim_id'               => $xodim->id,
            'shablon_id'             => $shablon->id,
            'shartnoma_raqami'       => $data['shartnoma_raqami'] ?? null,
            'matn'                   => $shablon->renderMatn($vars),
            'sana'                   => $sana,
            'amal_qilish_boshlanish' => $data['amal_qilish_boshlanish'] ?? null,
            'amal_qilish_tugash'     => $data['amal_qilish_tugash'] ?? null,
            'holat'                  => 'loyiha',
            'created_by'             => Auth::id(),
        ]);
    }

    public function shartnomaSaqla(XodimShartnoma $shartnoma, array $data): XodimShartnoma
    {
        $shartnoma->update([
            'shartnoma_raqami'       => $data['shartnoma_raqami'] ?? null,
            'matn'                   => $data['matn'],
            'sana'                   => $data['sana'],
            'amal_qilish_boshlanish' => $data['amal_qilish_boshlanish'] ?? null,
            'amal_qilish_tugash'     => $data['amal_qilish_tugash'] ?? null,
        ]);

        return $shartnoma->fresh();
    }

    public function shartnomaHolatOzgartir(XodimShartnoma $shartnoma, string $holat): XodimShartnoma
    {
        if (!in_array($holat, ['loyiha', 'imzolangan', 'bekor_qilingan'], true)) {
            throw new \RuntimeException('Noto\'g\'ri holat.');
        }

        $shartnoma->update(['holat' => $holat]);
        return $shartnoma->fresh();
    }

    private function vaqtinchalikEmail(): string
    {
        do {
            $email = 'xodim.' . Str::random(10) . '@ichki.local';
        } while (Foydalanuvchi::where('email', $email)->exists());

        return $email;
    }
}
