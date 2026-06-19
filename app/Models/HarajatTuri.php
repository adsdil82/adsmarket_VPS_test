<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HarajatTuri extends Model
{
    protected $table = 'harajat_turlari';
    protected $fillable = ['nomi', 'kod', 'ota_id', 'rang', 'icon', 'holat', 'sort_order'];

    public function ota(): BelongsTo  { return $this->belongsTo(HarajatTuri::class, 'ota_id'); }
    public function bolalar(): HasMany { return $this->hasMany(HarajatTuri::class, 'ota_id'); }

    public function scopeFaol($q) { return $q->where('holat', 'faol'); }
    public function scopeAsosiy($q) { return $q->whereNull('ota_id'); }
    public function scopeTartibli($q) { return $q->orderBy('sort_order')->orderBy('nomi'); }
}
