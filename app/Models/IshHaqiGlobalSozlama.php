<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IshHaqiGlobalSozlama extends Model
{
    protected $table = 'ish_haqi_global_sozlama';

    protected $fillable = ['soliq_foizi', 'boshqa_ushlanma_foizi'];

    protected $casts = [
        'soliq_foizi'           => 'decimal:2',
        'boshqa_ushlanma_foizi' => 'decimal:2',
    ];

    /** Yagona (singleton) qatorni oladi — mavjud bo'lmasa standart qiymatlar bilan yaratadi. */
    public static function ol(): self
    {
        return static::first() ?? static::create(['soliq_foizi' => 12, 'boshqa_ushlanma_foizi' => 0]);
    }
}
