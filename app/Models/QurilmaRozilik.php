<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurilmaRozilik extends Model
{
    protected $table = 'qurilma_roziliklar';

    protected $fillable = [
        'qurilma_id','reg_kredit_id','mijoz_id','shablon_id',
        'matn','imzolangan_sana','ip_manzil','telefon','kanal','holat',
    ];

    protected $casts = ['imzolangan_sana' => 'datetime'];

    public function qurilma(): BelongsTo  { return $this->belongsTo(Qurilma::class, 'qurilma_id'); }
    public function mijoz(): BelongsTo    { return $this->belongsTo(Mijoz::class, 'mijoz_id'); }
    public function kredit(): BelongsTo   { return $this->belongsTo(RegKredit::class, 'reg_kredit_id'); }
    public function shablon(): BelongsTo  { return $this->belongsTo(QurilmaRozilikShablon::class, 'shablon_id'); }

    public function scopeImzolangan($q)  { return $q->where('holat', 'imzolangan'); }
    public function scopeKutilmoqda($q)  { return $q->where('holat', 'kutilmoqda'); }
}
