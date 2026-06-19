<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Grafik extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'grafik';

    protected $fillable = [
        'eski_id',
        'reg_kredit_id',
        'oylik_tartib',
        'tolov_sana',
        'tolov_summa',
        'qoldiq_suma',
        'holat',
        'tolangan_summa',
        'tolangan_sana',
    ];

    protected $casts = [
        'tolov_sana'    => 'date',
        'tolangan_sana' => 'date',
        'tolov_summa'   => 'decimal:2',
        'qoldiq_suma'   => 'decimal:2',
        'tolangan_summa'=> 'decimal:2',
    ];

    // ─── Accessors ────────────────────────────────────────────────

    /** Holat badge rangi (Bootstrap) */
    public function getHolatRangiAttribute(): string
    {
        return match($this->holat) {
            'tolangan'      => 'success',
            'tolanmagan'    => 'secondary',
            'qisman'        => 'warning',
            'muddati_otgan' => 'danger',
            default         => 'secondary',
        };
    }

    /** Kechikkan kunlar soni */
    public function getKechikishKunlariAttribute(): int
    {
        if (!$this->tolov_sana || $this->holat === 'tolangan') return 0;
        $bugun = now()->startOfDay();
        $sana  = $this->tolov_sana->startOfDay();
        return max(0, $sana->diffInDays($bugun, false) * -1);
    }

    // ─── Aloqalar ────────────────────────────────────────────────

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(RegKredit::class, 'reg_kredit_id');
    }

    /** Bu grafik qatoriga tegishli to'lovlar */
    public function tulovlar(): HasMany
    {
        return $this->hasMany(Tulov::class, 'grafik_id');
    }

    // ─── Scope'lar ────────────────────────────────────────────────

    public function scopeTolanmagan($query)
    {
        return $query->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan']);
    }

    public function scopeMuddatiOtgan($query)
    {
        return $query->where('holat', 'muddati_otgan')
                     ->whereNotNull('tolov_sana')
                     ->where('tolov_sana', '<', now()->toDateString());
    }
}
