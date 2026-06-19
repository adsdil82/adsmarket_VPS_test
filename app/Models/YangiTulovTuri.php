<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YangiTulovTuri extends Model
{
    protected $table = 'yangi_tulov_turlari';

    protected $fillable = [
        'kod', 'nomi', 'kategoriya', 'debet_hisob_id', 'kredit_hisob_id', 'holat', 'izoh',
    ];

    public function debetHisob(): BelongsTo
    {
        return $this->belongsTo(HisobRejasi::class, 'debet_hisob_id');
    }

    public function kreditHisob(): BelongsTo
    {
        return $this->belongsTo(HisobRejasi::class, 'kredit_hisob_id');
    }

    public function scopeFaol($query)
    {
        return $query->where('holat', 'faol');
    }
}
