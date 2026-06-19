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
}
