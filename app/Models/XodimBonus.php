<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XodimBonus extends Model
{
    protected $table = 'xodim_bonuslari';

    protected $fillable = [
        'xodim_id', 'bonus_turi_id', 'qiymat',
        'boshlanish_oy', 'boshlanish_yil', 'tugash_oy', 'tugash_yil',
        'izoh', 'holat', 'created_by',
    ];

    protected $casts = [
        'qiymat' => 'decimal:2',
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function bonusTuri(): BelongsTo
    {
        return $this->belongsTo(BonusTuri::class, 'bonus_turi_id');
    }

    public function yaratuvchi(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'created_by');
    }

    /** Berilgan yil/oy shu biriktirishning amal qilish oralig'ida ekanligini tekshiradi. */
    public function amaldaMi(int $yil, int $oy): bool
    {
        $boshlandi = ($yil > $this->boshlanish_yil) || ($yil === $this->boshlanish_yil && $oy >= $this->boshlanish_oy);

        $tugadi = $this->tugash_yil !== null && (
            ($yil > $this->tugash_yil) || ($yil === $this->tugash_yil && $oy > $this->tugash_oy)
        );

        return $boshlandi && !$tugadi;
    }
}
