<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DavomatOyHolati extends Model
{
    protected $table = 'davomat_oy_holati';

    protected $fillable = ['yil', 'oy', 'holat', 'yopgan_id', 'yopilgan_vaqt'];

    protected $casts = [
        'yopilgan_vaqt' => 'datetime',
    ];

    public function yopgan(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'yopgan_id');
    }

    public static function yopiqmi(int $yil, int $oy): bool
    {
        return static::where('yil', $yil)->where('oy', $oy)->where('holat', 'yopiq')->exists();
    }
}
