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
        'karta_raqami',
        'passport_berilgan_joy',
        'manzil',
        'viloyat_id',
        'tuman_id',
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

    /** Holat nomi (UI uchun) */
    public function getHolatNomiAttribute(): string
    {
        return match ($this->holat) {
            'faol'   => 'AKTIV',
            'nofaol' => 'PASSIV',
            'sudda'  => 'SUDDA',
            'yomon'  => 'YOMON',
            default  => $this->holat,
        };
    }

    /** Holat rangi (badge uchun) */
    public function getHolatRangiAttribute(): string
    {
        return match ($this->holat) {
            'faol'   => 'success',
            'nofaol' => 'secondary',
            'sudda'  => 'warning',
            'yomon'  => 'danger',
            default  => 'secondary',
        };
    }

    /** Shartnoma tuzish taqiqlangan holatlar (sudda yoki yomon mijoz) */
    public function shartnomaTaqiqlanganmi(): bool
    {
        return in_array($this->holat, ['sudda', 'yomon']);
    }

    // ─── Aloqalar ────────────────────────────────────────────────

    /** Mijoz qaysi filialga tegishli */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function viloyat(): BelongsTo
    {
        return $this->belongsTo(Viloyat::class, 'viloyat_id');
    }

    public function tuman(): BelongsTo
    {
        return $this->belongsTo(Tuman::class, 'tuman_id');
    }

    /** Mijozning barcha nasiya shartnomalarni */
    public function kreditlar(): HasMany
    {
        return $this->hasMany(RegKredit::class, 'mijoz_id');
    }

    /** Qo'shimcha telefon raqamlari (asosiy $telefon dan tashqari, 3 tagacha) */
    public function telefonlar(): HasMany
    {
        return $this->hasMany(MijozTelefon::class, 'mijoz_id')->orderBy('tartib');
    }

    /** SMS yuborish uchun barcha raqamlar (asosiy + "SMS yuborilsin" belgilangan qo'shimchalar) */
    public function getSmsRaqamlariAttribute(): array
    {
        $raqamlar = $this->telefon ? [$this->telefon] : [];
        foreach ($this->telefonlar as $t) {
            if ($t->sms_yuborilsin && $t->telefon) {
                $raqamlar[] = $t->telefon;
            }
        }
        return array_values(array_unique(array_filter($raqamlar)));
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
