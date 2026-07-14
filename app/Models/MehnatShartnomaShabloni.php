<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MehnatShartnomaShabloni extends Model
{
    protected $table = 'mehnat_shartnoma_shablonlari';

    protected $fillable = ['nomi', 'matn', 'holat', 'sort_order'];

    public function shartnomalar(): HasMany
    {
        return $this->hasMany(XodimShartnoma::class, 'shablon_id');
    }

    public function scopeFaol($q)
    {
        return $q->where('holat', 'faol');
    }

    /** Matnda {{...}} o'zgaruvchilarni vars bilan almashtirish. */
    public function renderMatn(array $vars): string
    {
        $matn = $this->matn;
        foreach ($vars as $key => $value) {
            $matn = str_replace("{{{$key}}}", (string) $value, $matn);
        }
        return $matn;
    }

    /** Blade templateda ko'rsatiladigan o'zgaruvchilar ro'yxati. */
    public static function ozgaruvchilar(): array
    {
        return [
            'ism_familiya'      => "Xodim to'liq ismi (F.I.O)",
            'lavozim'           => 'Lavozimi',
            'oklad'             => 'Oylik oklad (so\'m)',
            'ishga_kirgan_sana' => 'Ishga kirgan sana',
            'tashkilot_nomi'    => 'Tashkilot nomi',
            'filial_nomi'       => 'Filial nomi',
            'manzil'            => 'Xodim manzili',
            'passport_malumot'  => 'Passport ma\'lumoti',
            'telefon'           => 'Telefon raqami',
            'shartnoma_raqami'  => 'Shartnoma raqami',
            'shartnoma_sana'    => 'Shartnoma tuzilgan sana',
        ];
    }
}
