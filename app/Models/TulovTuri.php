<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TulovTuri extends Model implements \OwenIt\Auditing\Contracts\Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tulov_turlari';

    protected $fillable = [
        'kod', 'nomi', 'kategoriya', 'holat', 'is_legacy',
        'affects_contract_balance', 'affects_cash', 'affects_bank', 'sort_order',
    ];
    protected $casts = [
        'is_legacy'                => 'boolean',
        'affects_contract_balance' => 'boolean',
        'affects_cash'             => 'boolean',
        'affects_bank'             => 'boolean',
    ];
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    // ─── Aloqalar ────────────────────────────────────────────────

    /** Bu tur orqali qilingan barcha to'lovlar */
    public function tulovlar(): HasMany
    {
        return $this->hasMany(Tulov::class, 'tulov_turi_id');
    }

    /** Bu tur orqali qilingan barcha oldindan to'lovlar */
    public function oldinTulovlar(): HasMany
    {
        return $this->hasMany(OldinTulov::class, 'tulov_turi_id');
    }

    // ─── Scope'lar ────────────────────────────────────────────────

    /** Faqat faol to'lov turlarini qaytaradi */
    public function scopeFaol($query)
    {
        return $query->where('holat', 'faol');
    }

    /** Yangi (legacy bo'lmagan) to'lov turlari — yangi shartnomalar uchun */
    public function scopeYangi($query)
    {
        return $query->where('is_legacy', false)->where('holat', 'faol')->orderBy('sort_order');
    }

    /** Legacy to'lov turlari — eski shartnomalar uchun */
    public function scopeLegacy($query)
    {
        return $query->where('is_legacy', true);
    }
}
