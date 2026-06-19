<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

class RegKredit extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'reg_kredit';

    protected $fillable = [
        'eski_id',
        'shartnoma_raqam',
        'mijoz_id',
        'filial_id',
        'xodim_id',
        'jami_summa',
        'boshlangich_tolov',
        'kredit_summa',
        'tolov_qilingan',
        'qoldiq_qarz',
        'boshlanish_sana',
        'tugash_sana',
        'oylik_tolov_miqdori',
        'muddati_oy',
        'tolov_kuni',
        'foiz_stavka',
        'kafil_ism',
        'kafil_telefon',
        'kafil_manzil',
        'kafil_mijoz_id',
        'holat',
        'izoh',
        'joriy_filial_id',
        'joriy_xodim_id',
    ];

    protected $casts = [
        'boshlanish_sana'     => 'date',
        'tugash_sana'         => 'date',
        'jami_summa'          => 'decimal:2',
        'boshlangich_tolov'   => 'decimal:2',
        'kredit_summa'        => 'decimal:2',
        'tolov_qilingan'      => 'decimal:2',
        'qoldiq_qarz'         => 'decimal:2',
        'oylik_tolov_miqdori' => 'decimal:2',
        'foiz_stavka'         => 'decimal:2',
    ];

    // ─── Shartnoma raqami avtomatik generatsiya ──────────────────
    // Format: IST-2024-00001 (filial kodi + yil + tartib raqam)
    public static function yangiRaqamYaratish(Filial $filial, int $yil): string
    {
        $prefix   = strtoupper($filial->kod) . '-' . $yil . '-';
        $oxirgi   = static::where('shartnoma_raqam', 'like', $prefix . '%')
                          ->orderByDesc('id')
                          ->value('shartnoma_raqam');

        $tartib = 1;
        if ($oxirgi) {
            $tartib = (int) substr($oxirgi, strrpos($oxirgi, '-') + 1) + 1;
        }

        return $prefix . str_pad($tartib, 5, '0', STR_PAD_LEFT);
    }

    // ─── Accessors ────────────────────────────────────────────────

    /** To'lanish foizi (jami_summa ga nisbatan, max 100%) */
    public function getTolovFoiziAttribute(): float
    {
        $jami = (float)$this->jami_summa;
        if ($jami <= 0) return 100;

        // Jami to'langan = boshlangich to'lov + kredit to'lovlari
        $tolangan = (float)$this->boshlangich_tolov + (float)$this->tolov_qilingan;

        return min(100, round($tolangan / $jami * 100, 1));
    }

    /** Holat ko'rsatish nomi: faol→AKTIV, yopilgan→PASSIV */
    public function getHolatNomiAttribute(): string
    {
        return match($this->holat) {
            'faol'          => 'AKTIV',
            'yopilgan'      => 'PASSIV',
            'muddati_otgan' => "Muddati o'tgan",
            'muzlatilgan'   => 'Muzlatilgan',
            default         => $this->holat,
        };
    }

    /** Holat badge rangi (Bootstrap) */
    public function getHolatRangiAttribute(): string
    {
        return match($this->holat) {
            'faol'          => 'success',
            'yopilgan'      => 'secondary',
            'muddati_otgan' => 'danger',
            'muzlatilgan'   => 'warning',
            default         => 'secondary',
        };
    }

    // ─── Aloqalar ────────────────────────────────────────────────

    public function mijoz(): BelongsTo
    {
        return $this->belongsTo(Mijoz::class, 'mijoz_id');
    }

    /** Kafil — mijozlar jadvalidan to'liq ma'lumot */
    public function kafil(): BelongsTo
    {
        return $this->belongsTo(Mijoz::class, 'kafil_mijoz_id');
    }

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'filial_id');
    }

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    /** Ko'chirilgandan keyingi joriy xodim (agar qayta tayinlangan bo'lsa) */
    public function joriyXodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'joriy_xodim_id');
    }

    /** Ko'chirilgandan keyingi joriy filial (agar ko'chirilgan bo'lsa) */
    public function joriyFilial(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'joriy_filial_id');
    }

    public function tovarlar(): HasMany
    {
        return $this->hasMany(Tovar::class, 'reg_kredit_id');
    }

    public function grafik(): HasMany
    {
        return $this->hasMany(Grafik::class, 'reg_kredit_id')
                    ->orderBy('oylik_tartib');
    }

    public function tulovlar(): HasMany
    {
        return $this->hasMany(Tulov::class, 'reg_kredit_id')
                    ->orderByDesc('tolov_sana');
    }

    public function oldinTulovlar(): HasMany
    {
        return $this->hasMany(OldinTulov::class, 'reg_kredit_id');
    }

    public function versiyalar(): HasMany
    {
        return $this->hasMany(ShartnomavVersioniya::class, 'reg_kredit_id')
                    ->orderByDesc('versiya_raqam');
    }

    // ─── Scope'lar ────────────────────────────────────────────────

    public function scopeFaol($query)
    {
        return $query->where('holat', 'faol');
    }

    public function scopeMuddatiOtgan($query)
    {
        return $query->where('holat', 'muddati_otgan');
    }

    public function scopeFilialda($query, int $filialId)
    {
        return $query->where('filial_id', $filialId);
    }

    public function scopeQidirish($query, string $qidiruv)
    {
        return $query->where(function ($q) use ($qidiruv) {
            $q->where('shartnoma_raqam', 'like', "%{$qidiruv}%")
              ->orWhereHas('mijoz', fn($m) =>
                  $m->where('familiya', 'like', "%{$qidiruv}%")
                    ->orWhere('ism', 'like', "%{$qidiruv}%")
                    ->orWhere('telefon', 'like', "%{$qidiruv}%")
              );
        });
    }
}
