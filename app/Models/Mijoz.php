<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Mijoz extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'mijozlar';

    protected $fillable = [
        'eski_id',
        'filial_id',
        'familiya',
        'ism',
        'otasining_ismi',
        'telefon',
        'passport_seriya',
        'passport_raqam',
        'pinfl',
        'passport_berilgan_joy',
        'manzil',
        'tug_sana',
        'ish_joyi',
        'lavozimi',
        'izoh',
        'holat',
    ];

    protected $casts = [
        'tug_sana' => 'date',
    ];

    // ─── Accessors ────────────────────────────────────────────────

    /** To'liq ismi sharifi */
    public function getTolikIsmAttribute(): string
    {
        return trim($this->familiya . ' ' . $this->ism . ' ' . $this->otasining_ismi);
    }

    /** Passport to'liq (AA 1234567) */
    public function getPassportTolikAttribute(): string
    {
        if ($this->passport_seriya && $this->passport_raqam) {
            return $this->passport_seriya . ' ' . $this->passport_raqam;
        }
        return $this->passport_raqam ?? '—';
    }

    // ─── Aloqalar ────────────────────────────────────────────────

    /** Mijoz qaysi filialga tegishli */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    /** Mijozning barcha nasiya shartnomalarni */
    public function kreditlar(): HasMany
    {
        return $this->hasMany(RegKredit::class, 'mijoz_id');
    }

    /** Faol (to'lanmagan) shartnomalar */
    public function faolKreditlar(): HasMany
    {
        return $this->hasMany(RegKredit::class, 'mijoz_id')
                    ->whereIn('holat', ['faol', 'muddati_otgan']);
    }

    // ─── Scope'lar ────────────────────────────────────────────────

    public function scopeFaol($query)
    {
        return $query->where('holat', 'faol');
    }

    public function scopeFilialda($query, int $filialId)
    {
        return $query->where('filial_id', $filialId);
    }

    /** Qidiruv: ism, familiya, telefon, passport bo'yicha */
    public function scopeQidirish($query, string $qidiruv)
    {
        return $query->where(function ($q) use ($qidiruv) {
            $q->where('familiya', 'like', "%{$qidiruv}%")
              ->orWhere('ism', 'like', "%{$qidiruv}%")
              ->orWhere('telefon', 'like', "%{$qidiruv}%")
              ->orWhere('passport_raqam', 'like', "%{$qidiruv}%");
        });
    }
}
