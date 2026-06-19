<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurilmaTasdiqlash extends Model
{
    protected $table = 'qurilma_tasdiqlashlar';

    protected $fillable = [
        'qurilma_id','amal','sabab','soragan_id',
        'tasdiq_id','holat','tasdiq_izoh','muddati',
    ];

    protected $casts = ['muddati' => 'datetime'];

    public static array $amalga = [
        'lock'    => 'Bloklash',
        'unlock'  => 'Blokdan chiqarish',
        'release' => 'Ozod qilish',
    ];

    public function qurilma(): BelongsTo  { return $this->belongsTo(Qurilma::class, 'qurilma_id'); }
    public function soragan(): BelongsTo  { return $this->belongsTo(Foydalanuvchi::class, 'soragan_id'); }
    public function tasdiqchi(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'tasdiq_id'); }

    public function muddatiOtdimi(): bool
    {
        return $this->muddati && $this->muddati->isPast();
    }

    public function scopeKutilmoqda($q) { return $q->where('holat', 'kutilmoqda'); }
    public function scopeFaollar($q)    { return $q->where('holat', 'kutilmoqda')->where(fn($q) => $q->whereNull('muddati')->orWhere('muddati', '>', now())); }
}
