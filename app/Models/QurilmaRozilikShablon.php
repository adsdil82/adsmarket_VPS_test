<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QurilmaRozilikShablon extends Model
{
    protected $table = 'qurilma_rozilik_shablonlari';

    protected $fillable = ['kod','sarlavha','matn','versiya','faol','til'];

    protected $casts = ['faol' => 'boolean'];

    public function roziliklar(): HasMany
    {
        return $this->hasMany(QurilmaRozilik::class, 'shablon_id');
    }

    public function scopeFaol($q) { return $q->where('faol', true); }
}
