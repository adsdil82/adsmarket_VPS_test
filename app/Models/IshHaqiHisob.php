<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IshHaqiHisob extends Model
{
    protected $table = 'ish_haqi_hisoblari';

    protected $fillable = [
        'xodim_id', 'yil', 'oy',
        'ish_kunlari_jami', 'kelgan_kunlar', 'davomat_foizi', 'oklad_qismi',
        'yigilgan_tolovlar', 'bonus_foizi', 'komissiya_bonus',
        'reja_bajarildimi', 'reja_bajarilish_foizi', 'reja_bonus',
        'qoshimcha_ish_haqi_summa', 'biriktirilgan_bonus_summa',
        'qoshimcha_hisoblash', 'qoshimcha_izoh',
        'ushlanma', 'ushlanma_izoh',
        'soliq_foizi', 'soliq_summa', 'boshqa_ushlanma_foizi', 'boshqa_ushlanma_summa', 'avans_jami',
        'jami_hisoblangan', 'holat', 'harajat_id', 'tolangan_vaqt', 'hisoblagan_id',
    ];

    protected $casts = [
        'davomat_foizi'       => 'decimal:2',
        'oklad_qismi'         => 'decimal:2',
        'yigilgan_tolovlar'   => 'decimal:2',
        'bonus_foizi'         => 'decimal:2',
        'komissiya_bonus'     => 'decimal:2',
        'reja_bajarildimi'    => 'boolean',
        'reja_bajarilish_foizi' => 'decimal:2',
        'reja_bonus'          => 'decimal:2',
        'qoshimcha_ish_haqi_summa'  => 'decimal:2',
        'biriktirilgan_bonus_summa' => 'decimal:2',
        'qoshimcha_hisoblash' => 'decimal:2',
        'ushlanma'            => 'decimal:2',
        'soliq_foizi'         => 'decimal:2',
        'soliq_summa'         => 'decimal:2',
        'boshqa_ushlanma_foizi' => 'decimal:2',
        'boshqa_ushlanma_summa' => 'decimal:2',
        'avans_jami'          => 'decimal:2',
        'jami_hisoblangan'    => 'decimal:2',
        'tolangan_vaqt'       => 'datetime',
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function hisoblagan(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'hisoblagan_id');
    }

    public function harajat(): BelongsTo
    {
        return $this->belongsTo(Harajat::class, 'harajat_id');
    }

    public function oyNomi(): string
    {
        $oylar = [1=>'Yanvar',2=>'Fevral',3=>'Mart',4=>'Aprel',5=>'May',6=>'Iyun',
                  7=>'Iyul',8=>'Avgust',9=>'Sentabr',10=>'Oktabr',11=>'Noyabr',12=>'Dekabr'];
        return ($oylar[$this->oy] ?? $this->oy) . ' ' . $this->yil;
    }

    /** Avans hisobga olingandan keyin yakuniy to'lashda beriladigan qolgan summa. */
    public function qolganTolash(): float
    {
        return round((float) $this->jami_hisoblangan - (float) $this->avans_jami, 2);
    }

    public function holatBadge(): string
    {
        return $this->holat === 'tolandi'
            ? '<span class="badge bg-success">To\'landi</span>'
            : '<span class="badge bg-warning text-dark">Hisoblangan</span>';
    }
}
