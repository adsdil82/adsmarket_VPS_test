<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ishlatilgan faollashtirish kodlari tarixi — bir xil kodning qayta ishlatilishini oldini olish uchun */
class LitsenziyaFaollashtirish extends Model
{
    protected $table = 'litsenziya_faollashtirishlar';

    protected $fillable = ['kod', 'yangi_muddat', 'xodim_id'];

    protected $casts = [
        'yangi_muddat' => 'date',
    ];
}
