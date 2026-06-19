<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurilmaProvayderUlanish extends Model
{
    protected $table = 'qurilma_provayder_ulanishlari';

    protected $fillable = ['qurilma_id','provayder_id','tashqi_id','holat','oxirgi_sinx','oxirgi_xato'];

    protected $casts = ['oxirgi_sinx' => 'datetime'];

    public function qurilma(): BelongsTo   { return $this->belongsTo(Qurilma::class, 'qurilma_id'); }
    public function provayder(): BelongsTo { return $this->belongsTo(QurilmaProvayder::class, 'provayder_id'); }

    public function scopeFaol($q)  { return $q->where('holat', 'faol'); }
    public function scopeXato($q)  { return $q->where('holat', 'xato'); }
}
