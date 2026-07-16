<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KunYopishLogi extends Model
{
    protected $table = 'kun_yopish_logi';

    public $timestamps = true;

    protected $fillable = ['operatsion_kun_id', 'amal', 'user_id', 'vaqt', 'natija_json'];

    protected $casts = [
        'vaqt'        => 'datetime',
        'natija_json' => 'array',
    ];

    public function operatsionKun(): BelongsTo
    {
        return $this->belongsTo(OperatsionKun::class, 'operatsion_kun_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'user_id');
    }
}
