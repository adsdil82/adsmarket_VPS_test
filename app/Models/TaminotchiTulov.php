<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaminotchiTulov extends Model
{
    protected $table = 'taminotchi_tulovlar';
    protected $fillable = [
        'taminotchi_id','kirim_id','kirim_taqsimot','xodim_id','filial_id',
        'summa','valyuta','kurs','summa_uzs',
        'tolov_sana','tolov_turi','hujjat_raqam','izoh',
    ];
    protected $casts = ['tolov_sana' => 'date', 'kirim_taqsimot' => 'array'];

    public function taminotchi(): BelongsTo { return $this->belongsTo(Taminotchi::class); }
    public function kirim(): BelongsTo     { return $this->belongsTo(TaminotKirim::class,'kirim_id'); }
    public function xodim(): BelongsTo    { return $this->belongsTo(Foydalanuvchi::class,'xodim_id'); }
    public function filial(): BelongsTo   { return $this->belongsTo(Filial::class); }
}
