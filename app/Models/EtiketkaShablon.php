<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtiketkaShablon extends Model
{
    protected $table = 'etiketka_shablonlar';

    protected $fillable = [
        'nomi', 'turi', 'reng_fon', 'reng_matn', 'reng_urgu', 'belgi_matni', 'joylashuv', 'created_by',
    ];

    protected $casts = [
        'joylashuv' => 'array',
    ];
}
