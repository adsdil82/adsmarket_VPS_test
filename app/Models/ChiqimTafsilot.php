<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChiqimTafsilot extends Model
{
    public    $timestamps = false;
    protected $table    = 'chiqim_tafsilot';
    protected $fillable = ['chiqim_id','tovar_id','miqdor','narx','jami_summa'];

    public function tovar()  { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
    public function chiqim() { return $this->belongsTo(OmbordanChiqim::class, 'chiqim_id'); }
}
