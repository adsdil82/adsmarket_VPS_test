<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PochtaShablon extends Model
{
    protected $table = 'pochta_shablonlar';

    protected $fillable = ['nomi', 'matn', 'qayta_yuborish_kun', 'holat', 'sort_order'];

    public function loglar(): HasMany
    {
        return $this->hasMany(PochtaLog::class, 'shablon_id');
    }

    public function scopeFaol($q)
    {
        return $q->where('holat', 'faol');
    }

    /** Matnda {{...}} o'zgaruvchilarni vars bilan almashtirish */
    public function renderMatn(array $vars): string
    {
        $matn = $this->matn;
        foreach ($vars as $key => $value) {
            $matn = str_replace("{{{$key}}}", (string) $value, $matn);
        }
        return $matn;
    }

    /** Blade templateda ko'rsatiladigan o'zgaruvchilar ro'yxati */
    public static function ozgaruvchilar(): array
    {
        return [
            'mijoz_fio'       => 'Mijoz to\'liq ismi (F.I.O)',
            'shartnoma_raqam' => 'Shartnoma raqami',
            'eski_raqam'      => 'Eski (qog\'ozdagi) shartnoma raqami — "raqam/yil" ko\'rinishida, faqat eski migratsiyadan o\'tgan shartnomalarda mavjud',
            'kechikish_kun'   => 'Kechikish kunlari soni',
            'jami_qarz'       => 'Jami qoldiq qarz (so\'m)',
            'yuborish_sana'   => 'Xat yuborilgan sana',
            'tashkilot_nomi'  => 'Tashkilot nomi',
        ];
    }
}
