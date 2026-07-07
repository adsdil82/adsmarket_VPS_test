<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosTafsilot extends Model
{
    public    $timestamps = false;
    protected $table    = 'pos_tafsilot';
    protected $fillable = ['sotuv_id','tovar_id','miqdor','narx','chegirma','jami_summa'];

    public function tovar()  { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
    public function sotuv()  { return $this->belongsTo(PosSotuv::class, 'sotuv_id'); }
    public function qaytimTafsilotlari() { return $this->hasMany(PosQaytimTafsilot::class, 'tafsilot_id'); }

    /** Shu sotuv qatoridan hozirgacha necha dona qaytarilgan (bekor qilinmagan qaytimlar bo'yicha). */
    public function qaytarilganMiqdor(): float
    {
        return (float) $this->qaytimTafsilotlari()
            ->whereHas('qaytim', fn($q) => $q->where('holat', 'tugallangan'))
            ->sum('miqdor');
    }
}
