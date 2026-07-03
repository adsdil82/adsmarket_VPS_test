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
        'ustama_summa',
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

    /**
     * Kechikkan kunlar soni.
     *  - To'langan qator: reja sanasidan necha kun KECH to'langanini ko'rsatadi
     *    (tolangan_sana - tolov_sana). Vaqtida yoki erta to'langan bo'lsa — 0.
     *  - To'lanmagan/qisman qator: faqat reja sanasi O'TIB KETGAN bo'lsa
     *    (o'tmishda), bugungacha necha kun o'tganini ko'rsatadi. Reja sanasi
     *    hali kelmagan (kelajakdagi) bo'lsa — 0 (hali kechikish yo'q).
     */
    public function getKechikishKunlariAttribute(): int
    {
        if (!$this->tolov_sana) return 0;
        $sana = $this->tolov_sana->copy()->startOfDay();

        if ($this->holat === 'tolangan') {
            if (!$this->tolangan_sana) return 0;
            return max(0, abs($sana->diffInDays($this->tolangan_sana->copy()->startOfDay())));
        }

        if ($sana->isFuture()) return 0;
        // sana o'tmishda — bugungacha (haligacha to'lanmagan/qisman) necha
        // kun o'tganini hisoblaymiz (absolyut farq, Carbon versiyasi default
        // ishorasiga qaramay).
        return abs(now()->startOfDay()->diffInDays($sana));
    }

    /** "Kechikkan kun" ni o'qish uchun qulay matn: 1 oydan ortiq bo'lsa "N oy M kun" */
    public function getKechikishMatniAttribute(): string
    {
        $kun = $this->kechikish_kunlari;
        if ($kun <= 0) return '—';
        if ($kun < 30) return "{$kun} kun";

        $oy = intdiv($kun, 30);
        $qolganKun = $kun % 30;
        return $qolganKun > 0 ? "{$oy} oy {$qolganKun} kun" : "{$oy} oy";
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
        // tolov_sana NULL bo'lgan qatorlar — ba'zi eski (legacy) shartnomalarda
        // haqiqiy muddatdan ortiq "bo'sh" qator sifatida saqlangan, haqiqiy oy
        // emas, shuning uchun ko'rinmasligi kerak.
        return $query->whereIn('holat', ['tolanmagan', 'qisman', 'muddati_otgan'])
                     ->whereNotNull('tolov_sana');
    }

    /** Faqat haqiqiy (bo'sh bo'lmagan) grafik qatorlari */
    public function scopeMavjud($query)
    {
        return $query->whereNotNull('tolov_sana');
    }

    public function scopeMuddatiOtgan($query)
    {
        return $query->where('holat', 'muddati_otgan')
                     ->whereNotNull('tolov_sana')
                     ->where('tolov_sana', '<', now()->toDateString());
    }
}
