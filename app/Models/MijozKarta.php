<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MijozKarta extends Model
{
    protected $table = 'mijoz_kartalar';

    protected $fillable = ['mijoz_id', 'karta_raqami', 'tartib'];

    public function mijoz(): BelongsTo
    {
        return $this->belongsTo(Mijoz::class, 'mijoz_id');
    }
}
