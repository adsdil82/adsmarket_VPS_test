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
}
