<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShartnomavVersioniya extends Model
{
    protected $table = 'shartnoma_versiyalar';

    // Faqat created_at bor
    public $timestamps  = false;
    const CREATED_AT    = 'created_at';

    protected $fillable = [
        'reg_kredit_id',
        'versiya_raqam',
        'xodim_id',
        'sabab',
        'eski_holat',
        'yangi_holat',
        'ozgargan_maydonlar',
    ];

    protected $casts = [
        'eski_holat'         => 'array',
        'yangi_holat'        => 'array',
        'ozgargan_maydonlar' => 'array',
        'created_at'         => 'datetime',
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
}
