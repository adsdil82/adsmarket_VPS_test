<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XodimShartnoma extends Model
{
    protected $table = 'xodim_shartnomalari';

    protected $fillable = [
        'xodim_id', 'shablon_id', 'shartnoma_raqami', 'matn', 'sana',
        'amal_qilish_boshlanish', 'amal_qilish_tugash', 'holat', 'created_by',
    ];

    protected $casts = [
        'sana'                    => 'date',
        'amal_qilish_boshlanish'  => 'date',
        'amal_qilish_tugash'      => 'date',
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function shablon(): BelongsTo
    {
        return $this->belongsTo(MehnatShartnomaShabloni::class, 'shablon_id');
    }

    public function yaratuvchi(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'created_by');
    }

    public function holatBadge(): string
    {
        return match ($this->holat) {
            'loyiha'         => '<span class="badge bg-warning text-dark">Loyiha</span>',
            'imzolangan'     => '<span class="badge bg-success">Imzolangan</span>',
            'bekor_qilingan' => '<span class="badge bg-secondary">Bekor qilingan</span>',
            default          => '<span class="badge bg-secondary">' . e($this->holat) . '</span>',
        };
    }
}
