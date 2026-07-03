<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Harajat extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'harajatlar';

    protected $fillable = [
        'filial_id',
        'xodim_id',
        'sana',
        'turi',
        'harajat_turi_id',
        'tegishli_xodim_id',
        'taminotchi_tulov_id',
        'schetchik_raqami',
        'kassa_turi',
        'pul_kategoriya_id',
        'summa',
        'mazmuni',
        'eski_id',
    ];

    protected $casts = [
        'sana'  => 'date',
        'summa' => 'decimal:2',
    ];

    // ─── Aloqalar ────────────────────────────────────────────────

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class);
    }

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function kategoriya(): BelongsTo
    {
        return $this->belongsTo(PulKategoriya::class, 'pul_kategoriya_id');
    }

    public function harajatTuri(): BelongsTo
    {
        return $this->belongsTo(HarajatTuri::class, 'harajat_turi_id');
    }

    public function tegishliXodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'tegishli_xodim_id');
    }

    public function taminotchiTulov(): BelongsTo
    {
        return $this->belongsTo(TaminotchiTulov::class, 'taminotchi_tulov_id');
    }
}
