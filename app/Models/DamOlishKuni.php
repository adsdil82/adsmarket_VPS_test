<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class DamOlishKuni extends Model
{
    protected $table = 'dam_olish_kunlari';

    protected $fillable = ['sana', 'turi', 'belgilagan_id'];

    protected $casts = [
        'sana' => 'date',
    ];

    public function belgilagan(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'belgilagan_id');
    }

    /** Berilgan yil uchun barcha belgilangan kunlar, sana ('Y-m-d') bo'yicha kalitlangan. */
    public static function shuYilgi(int $yil): Collection
    {
        return static::whereYear('sana', $yil)->get()->keyBy(fn (self $d) => $d->sana->format('Y-m-d'));
    }
}
