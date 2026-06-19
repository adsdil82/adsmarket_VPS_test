<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Filial extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'filiallar';

    protected $fillable = [
        'nomi',
        'kod',
        'manzil',
        'telefon',
        'holat',
    ];

    protected $casts = [
        'holat' => 'string',
    ];

    // ─── Aloqalar ────────────────────────────────────────────────

    /** Filialga tegishli barcha foydalanuvchilar */
    public function foydalanuvchilar(): HasMany
    {
        return $this->hasMany(Foydalanuvchi::class, 'filial_id');
    }

    /** Filialga tegishli barcha mijozlar */
    public function mijozlar(): HasMany
    {
        return $this->hasMany(Mijoz::class, 'filial_id');
    }

    /** Filialda tuzilgan barcha shartnomalar */
    public function kreditlar(): HasMany
    {
        return $this->hasMany(RegKredit::class, 'filial_id');
    }

    // ─── Scope'lar ────────────────────────────────────────────────

    /** Faqat faol filiallarni qaytaradi */
    public function scopeFaol($query)
    {
        return $query->where('holat', 'faol');
    }
}
