<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MijozTelefon extends Model
{
    protected $table = 'mijoz_telefonlar';

    protected $fillable = ['mijoz_id', 'telefon', 'egasi_ismi', 'sms_yuborilsin', 'tartib'];

    protected $casts = [
        'sms_yuborilsin' => 'boolean',
    ];

    public function mijoz(): BelongsTo
    {
        return $this->belongsTo(Mijoz::class, 'mijoz_id');
    }
}
