<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurilmaQoida extends Model
{
    protected $table = 'qurilma_qoidalar';

    protected $fillable = [
        'provayder_id','kechikish_kunlar','amal','kanal',
        'faol','tasdiq_talab','tavsif','sort_order',
    ];

    protected $casts = [
        'faol'         => 'boolean',
        'tasdiq_talab' => 'boolean',
    ];

    public static array $amalga = [
        'ogoh_berish'      => 'Ogohlantirish',
        'lock'             => 'Bloklash',
        'unlock'           => 'Blokdan chiqarish',
        'qolda_tekshirish' => 'Qo\'lda tekshirish',
    ];

    public static array $kanallar = [
        'sms'      => 'SMS',
        'telegram' => 'Telegram',
        'provider' => 'Provider',
        'hammasi'  => 'Hammasi',
    ];

    public function provayder(): BelongsTo
    {
        return $this->belongsTo(QurilmaProvayder::class, 'provayder_id');
    }

    public function scopeFaol($q) { return $q->where('faol', true)->orderBy('kechikish_kunlar'); }
}
