<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HisobRejasi extends Model
{
    protected $table = 'hisoblar_rejasi';

    protected $fillable = [
        'hisob_raqam', 'nomi', 'turi', 'daraja', 'ota_id', 'holat', 'izoh',
    ];

    public function ota(): BelongsTo
    {
        return $this->belongsTo(HisobRejasi::class, 'ota_id');
    }

    public function bolalar(): HasMany
    {
        return $this->hasMany(HisobRejasi::class, 'ota_id');
    }

    public function tulovTurlariDebet(): HasMany
    {
        return $this->hasMany(YangiTulovTuri::class, 'debet_hisob_id');
    }

    public function tulovTurlariKredit(): HasMany
    {
        return $this->hasMany(YangiTulovTuri::class, 'kredit_hisob_id');
    }

    /** Barcha faol hisoblar tekis ro'yxat */
    public static function faollar()
    {
        return static::where('holat', 'faol')->orderBy('hisob_raqam')->get();
    }

    /** To'liq nomi: "5010 — Kassa" */
    public function getTolikNomiAttribute(): string
    {
        return $this->hisob_raqam . ' — ' . $this->nomi;
    }
}
