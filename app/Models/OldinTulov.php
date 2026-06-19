<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class OldinTulov extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'oldindan_tulov';

    protected $fillable = [
        'eski_id',
        'reg_kredit_id',
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

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function tulovTuri(): BelongsTo
    {
        return $this->belongsTo(TulovTuri::class, 'tulov_turi_id');
    }
}
