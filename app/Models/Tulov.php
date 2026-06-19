<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Tulov extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tulovlar';

    protected $fillable = [
        'eski_id',
        'reg_kredit_id',
        'grafik_id',
        'xodim_id',
        'tulov_turi_id',
        'summa',
        'tolov_sana',
        'qabul_vaqt',
        'kvitansiya_raqam',
        'izoh',
    ];

    protected $casts = [
        'tolov_sana'  => 'date',
        'qabul_vaqt'  => 'datetime',
        'summa'       => 'decimal:2',
    ];

    // ─── Aloqalar ────────────────────────────────────────────────

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(RegKredit::class, 'reg_kredit_id');
    }

    public function grafik(): BelongsTo
    {
        return $this->belongsTo(Grafik::class, 'grafik_id');
    }

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function tulovTuri(): BelongsTo
    {
        return $this->belongsTo(TulovTuri::class, 'tulov_turi_id');
    }

    // ─── Scope'lar ────────────────────────────────────────────────

    public function scopeBugun($query)
    {
        // whereDate() → DATE() funksiya → indeks o'ladi! Range ishlatamiz
        $bugun = today();
        return $query->where('tolov_sana', '>=', $bugun)
                     ->where('tolov_sana', '<', $bugun->copy()->addDay());
    }

    public function scopeFilialda($query, int $filialId)
    {
        // whereHas() subquery → sekin. JOIN ishlatamiz
        return $query->join('reg_kredit as rk_f', 'rk_f.id', '=', 'tulovlar.reg_kredit_id')
                     ->where('rk_f.filial_id', $filialId)
                     ->select('tulovlar.*');
    }

    public function scopeSanada($query, string $dan, string $gacha)
    {
        return $query->whereBetween('tolov_sana', [$dan, $gacha]);
    }
}
