<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Foydalanuvchi extends Authenticatable implements Auditable
{
    use Notifiable;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'foydalanuvchilar';

    protected $fillable = [
        'filial_id',
        'ism_familiya',
        'email',
        'password',
        'pin_kod',
        'pin_xato_soni',
        'pin_bloklangan_gacha',
        'rol',
        'holat',
        'til',
        'tizimga_kirish_bormi',
    ];

    protected $hidden = [
        'password',
        'pin_kod',
        'remember_token',
    ];

    protected $casts = [
        'password'              => 'hashed',
        'pin_kod'                => 'hashed',
        'pin_bloklangan_gacha'   => 'datetime',
        'tizimga_kirish_bormi'   => 'boolean',
    ];

    // ─── Aloqalar ────────────────────────────────────────────────

    /** Foydalanuvchi qaysi filialda ishlaydi */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    /** Foydalanuvchi tuzgan shartnomalar */
    public function kreditlar(): HasMany
    {
        return $this->hasMany(RegKredit::class, 'xodim_id');
    }

    /** Foydalanuvchi qabul qilgan to'lovlar */
    public function tulovlar(): HasMany
    {
        return $this->hasMany(Tulov::class, 'xodim_id');
    }

    /** Ish haqi sozlamalari (oklad, bonus foizi, oylik reja) */
    public function ishHaqiSozlama(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(XodimIshHaqiSozlama::class, 'xodim_id');
    }

    /** Ish haqidan oldindan olingan avanslar */
    public function ishHaqiAvanslari(): HasMany
    {
        return $this->hasMany(IshHaqiAvans::class, 'xodim_id');
    }

    /** Kunlik davomat (tabel) yozuvlari */
    public function davomatlar(): HasMany
    {
        return $this->hasMany(XodimDavomat::class, 'xodim_id');
    }

    /** Oylik ish haqi hisob-kitoblari tarixi */
    public function ishHaqiHisoblari(): HasMany
    {
        return $this->hasMany(IshHaqiHisob::class, 'xodim_id');
    }

    /** Ta'til (yillik/kasallik/boshqa) yozuvlari */
    public function tatillar(): HasMany
    {
        return $this->hasMany(XodimTatil::class, 'xodim_id');
    }

    /** Biriktirilgan bonuslar */
    public function bonuslar(): HasMany
    {
        return $this->hasMany(XodimBonus::class, 'xodim_id');
    }

    /** Mehnat shartnomalari tarixi */
    public function shartnomalar(): HasMany
    {
        return $this->hasMany(XodimShartnoma::class, 'xodim_id');
    }

    // ─── Rol tekshiruvlari ────────────────────────────────────────

    /** Admin ekanligi */
    public function isAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /** Menejer yoki yuqori */
    public function isMenejerYoki(): bool
    {
        return in_array($this->rol, ['admin', 'menejer']);
    }

    /** Kassir — to'lov qabul qila oladi */
    public function isKassir(): bool
    {
        return in_array($this->rol, ['admin', 'menejer', 'kassir']);
    }

    /** Hisobchi — faqat ko'rish */
    public function isHisobchi(): bool
    {
        return $this->rol === 'hisobchi';
    }

    public function isAuditor(): bool
    {
        return $this->rol === 'auditor';
    }

    /** Omborchi yoki yuqori */
    public function isOmborchi(): bool
    {
        return in_array($this->rol, ['admin','menejer','omborchi']);
    }

    /** Taminotchi moduliga kirish (kassir + omborchi + admin + menejer) */
    public function isTaminotKira(): bool
    {
        return in_array($this->rol, ['admin','menejer','kassir','omborchi']);
    }

    /**
     * Rol uchun resurs/amal bo'yicha ruxsat tekshiruvi (admin/ruxsatlar sahifasidagi jadval asosida).
     * Admin har doim to'liq ruxsatga ega. Natija so'rov davomida cache qilinadi.
     */
    public function ruxsat(string $resurs, string $amal = 'korish'): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $hammasi = cache()->rememberForever('ruxsatlar_all', function () {
            return \Illuminate\Support\Facades\DB::table('ruxsatlar')->get();
        });

        $qator = $hammasi->first(
            fn($r) => $r->rol === $this->rol && $r->resurs === $resurs && $r->amal === $amal
        );

        return (bool) ($qator->ruxsat ?? false);
    }

    // ─── POS PIN ────────────────────────────────────────────────

    public function pinTogri(string $pin): bool
    {
        return $this->pin_kod && \Illuminate\Support\Facades\Hash::check($pin, $this->pin_kod);
    }

    public function pinBloklanganmi(): bool
    {
        return $this->pin_bloklangan_gacha && $this->pin_bloklangan_gacha->isFuture();
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
}
