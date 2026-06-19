<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PochtaLog extends Model
{
    protected $table = 'pochta_loglar';

    protected $fillable = [
        'reg_kredit_id', 'mijoz_id', 'shablon_id', 'api_letter_id',
        'receiver', 'address', 'region_id', 'area_id', 'holat',
        'xato_xabar', 'so_rov', 'javob', 'yaratildi_vaqt', 'yuborildi_vaqt',
    ];

    protected $casts = [
        'so_rov'         => 'array',
        'javob'          => 'array',
        'yaratildi_vaqt' => 'datetime',
        'yuborildi_vaqt' => 'datetime',
    ];

    public function kredit(): BelongsTo
    {
        return $this->belongsTo(RegKredit::class, 'reg_kredit_id');
    }

    public function mijoz(): BelongsTo
    {
        return $this->belongsTo(Mijoz::class, 'mijoz_id');
    }

    public function shablon(): BelongsTo
    {
        return $this->belongsTo(PochtaShablon::class, 'shablon_id');
    }

    public function holatBadge(): string
    {
        return match ($this->holat) {
            'yuborildi'  => '<span class="badge bg-success">Yuborildi</span>',
            'yaratildi'  => '<span class="badge bg-info text-dark">Yaratildi</span>',
            'kutilmoqda' => '<span class="badge bg-warning text-dark">Kutilmoqda</span>',
            'xato'       => '<span class="badge bg-danger">Xato</span>',
            default      => '<span class="badge bg-secondary">' . e($this->holat) . '</span>',
        };
    }
}
