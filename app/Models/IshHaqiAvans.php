<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IshHaqiAvans extends Model
{
    protected $table = 'ish_haqi_avanslar';

    protected $fillable = ['xodim_id', 'yil', 'oy', 'summa', 'sana', 'izoh', 'harajat_id', 'created_by'];

    protected $casts = [
        'summa' => 'decimal:2',
        'sana'  => 'date',
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function harajat(): BelongsTo
    {
        return $this->belongsTo(Harajat::class, 'harajat_id');
    }

    public function yaratuvchi(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'created_by');
    }
}
