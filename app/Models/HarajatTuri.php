<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HarajatTuri extends Model
{
    protected $table = 'harajat_turlari';

    protected $fillable = [
        'nomi', 'pul_kategoriya_id', 'talab_xodim', 'talab_schetchik', 'holat', 'sort_order',
    ];

    protected $casts = [
        'talab_xodim'     => 'boolean',
        'talab_schetchik' => 'boolean',
    ];

    public function kategoriya(): BelongsTo
    {
        return $this->belongsTo(PulKategoriya::class, 'pul_kategoriya_id');
    }

    public function harajatlar(): HasMany
    {
        return $this->hasMany(Harajat::class, 'harajat_turi_id');
    }

    public function scopeFaol($q)
    {
        return $q->where('holat', 'faol');
    }
}
