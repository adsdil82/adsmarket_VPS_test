<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XodimIshHaqiSozlama extends Model
{
    protected $table = 'xodim_ish_haqi_sozlama';

    protected $fillable = [
        'xodim_id', 'oklad', 'bonus_foizi', 'oylik_reja_summa', 'reja_min_foizi', 'reja_max_foizi', 'reja_bonus_summa', 'holat',
        'soliq_foizi', 'boshqa_ushlanma_foizi', 'dastlabki_qoldiq',
    ];

    protected $casts = [
        'oklad'            => 'decimal:2',
        'bonus_foizi'      => 'decimal:2',
        'oylik_reja_summa' => 'decimal:2',
        'reja_min_foizi'   => 'decimal:2',
        'reja_max_foizi'   => 'decimal:2',
        'reja_bonus_summa' => 'decimal:2',
        'soliq_foizi'      => 'decimal:2',
        'boshqa_ushlanma_foizi' => 'decimal:2',
        'dastlabki_qoldiq' => 'decimal:2',
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }
}
