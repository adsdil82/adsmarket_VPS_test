<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Qurilma extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'qurilmalar';

    protected $fillable = [
        'tovar_katalog_id','reg_kredit_id','tovarlar_id','mijoz_id','filial_id',
        'brend','model_nomi','rang','xotira',
        'imei1','imei2','imei3','imei4','serial_raqam',
        'holat','qoshilgan_sana','sotilgan_sana','qaytarilgan_sana',
        'izoh','yaratdi_id','yangiladi_id',
    ];

    protected $casts = [
        'qoshilgan_sana'   => 'date',
        'sotilgan_sana'    => 'date',
        'qaytarilgan_sana' => 'date',
    ];

    // ─── Holat doimiylari ─────────────────────────────────────────
    const HOLAT_IN_STOCK       = 'in_stock';
    const HOLAT_RESERVED       = 'reserved';
    const HOLAT_SOLD           = 'sold';
    const HOLAT_ACTIVE         = 'active';
    const HOLAT_WARNING        = 'warning';
    const HOLAT_LOCKED         = 'locked';
    const HOLAT_UNLOCK_PENDING = 'unlock_pending';
    const HOLAT_RELEASED       = 'released';
    const HOLAT_RETURNED       = 'returned';
    const HOLAT_LOST           = 'lost';
    const HOLAT_FAILED         = 'failed';

    public static array $holatlar = [
        'in_stock'       => 'Omborда',
        'reserved'       => 'Rezerv',
        'sold'           => 'Sotilgan',
        'active'         => 'Faol',
        'warning'        => 'Ogoh',
        'locked'         => 'Bloklangan',
        'unlock_pending' => 'Blok ochilmoqda',
        'released'       => 'Shartnoma yopildi',
        'returned'       => 'Qaytarilgan',
        'lost'           => 'Yo\'qolgan',
        'failed'         => 'Xato',
    ];

    public static array $holatRanglari = [
        'in_stock'       => 'secondary',
        'reserved'       => 'info',
        'sold'           => 'primary',
        'active'         => 'success',
        'warning'        => 'warning',
        'locked'         => 'danger',
        'unlock_pending' => 'warning',
        'released'       => 'success',
        'returned'       => 'secondary',
        'lost'           => 'dark',
        'failed'         => 'danger',
    ];

    // ─── Aloqalar ────────────────────────────────────────────────
    public function tovarKatalog(): BelongsTo
    {
        return $this->belongsTo(TovarKatalog::class, 'tovar_katalog_id');
    }

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(RegKredit::class, 'reg_kredit_id');
    }

    public function tovar(): BelongsTo
    {
        return $this->belongsTo(Tovar::class, 'tovarlar_id');
    }

    public function mijoz(): BelongsTo
    {
        return $this->belongsTo(Mijoz::class, 'mijoz_id');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function yaratdi(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'yaratdi_id');
    }

    public function provayderUlanishlari(): HasMany
    {
        return $this->hasMany(QurilmaProvayderUlanish::class, 'qurilma_id');
    }

    public function loglar(): HasMany
    {
        return $this->hasMany(QurilmaLog::class, 'qurilma_id')->latest();
    }

    public function roziliklar(): HasMany
    {
        return $this->hasMany(QurilmaRozilik::class, 'qurilma_id');
    }

    public function tasdiqlashlar(): HasMany
    {
        return $this->hasMany(QurilmaTasdiqlash::class, 'qurilma_id');
    }

    // ─── Accessors ───────────────────────────────────────────────
    public function getHolatNomiAttribute(): string
    {
        return static::$holatlar[$this->holat] ?? $this->holat;
    }

    public function getHolatRangiAttribute(): string
    {
        return static::$holatRanglari[$this->holat] ?? 'secondary';
    }

    public function getToliqNomiAttribute(): string
    {
        return trim(($this->brend ? $this->brend . ' ' : '') . $this->model_nomi);
    }

    public function getImeilarAttribute(): array
    {
        return array_filter([$this->imei1, $this->imei2, $this->imei3, $this->imei4]);
    }

    public function getRozilikImzolanganAttribute(): bool
    {
        return $this->roziliklar()->where('holat', 'imzolangan')->exists();
    }

    // ─── Helper metodlar ─────────────────────────────────────────
    public function isLocked(): bool       { return $this->holat === self::HOLAT_LOCKED; }
    public function isActive(): bool       { return in_array($this->holat, [self::HOLAT_ACTIVE, self::HOLAT_SOLD]); }
    public function isReleased(): bool     { return $this->holat === self::HOLAT_RELEASED; }
    public function canBeLocked(): bool    { return !in_array($this->holat, [self::HOLAT_RELEASED, self::HOLAT_RETURNED, self::HOLAT_LOST, self::HOLAT_LOCKED, self::HOLAT_UNLOCK_PENDING]); }
    public function canBeUnlocked(): bool  { return in_array($this->holat, [self::HOLAT_LOCKED, self::HOLAT_UNLOCK_PENDING]); }

    // ─── Scopes ──────────────────────────────────────────────────
    public function scopeFilialda($q, ?int $filialId)
    {
        return $filialId ? $q->where('filial_id', $filialId) : $q;
    }

    public function scopeHolatda($q, string $holat) { return $q->where('holat', $holat); }
    public function scopeOmborda($q)    { return $q->whereIn('holat', ['in_stock','reserved']); }
    public function scopeSotilgan($q)   { return $q->whereIn('holat', ['sold','active','warning','locked','unlock_pending']); }
    public function scopeBloklangan($q) { return $q->where('holat', 'locked'); }
}
