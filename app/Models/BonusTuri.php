<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusTuri extends Model
{
    protected $table = 'bonus_turlari';

    protected $fillable = ['nomi', 'tavsif', 'hisoblash_turi', 'standart_qiymat', 'holat', 'sort_order'];

    protected $casts = [
        'standart_qiymat' => 'decimal:2',
    ];

    public function biriktirishlar(): HasMany
    {
        return $this->hasMany(XodimBonus::class, 'bonus_turi_id');
    }

    public function scopeFaol($q)
    {
        return $q->where('holat', 'faol');
    }
}
