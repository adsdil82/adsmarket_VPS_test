<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Tovar extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tovarlar';

    protected $fillable = [
        'eski_id',
        'reg_kredit_id',
        'nomi',
        'soni',
        'narx',
        'jami_narx',
        'tovar_katalog_id',
        'barkod',
        'izoh',
    ];

    protected $casts = [
        'narx'      => 'decimal:2',
        'jami_narx' => 'decimal:2',
        'soni'      => 'integer',
    ];

    // ─── Model hodisalari ─────────────────────────────────────────

    /** Saqlashdan oldin jami_narx ni avtomatik hisoblash */
    protected static function booted(): void
    {
        static::saving(function (Tovar $tovar) {
            $tovar->jami_narx = $tovar->soni * $tovar->narx;
        });
    }

    // ─── Aloqalar ────────────────────────────────────────────────

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(RegKredit::class, 'reg_kredit_id');
    }
}
