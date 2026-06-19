<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KirimTafsilot extends Model
{
    public    $timestamps = false;
    protected $table    = 'kirim_tafsilot';
    protected $fillable = ['kirim_id','tovar_id','miqdor','tan_narx','jami_summa'];
    protected $casts    = ['miqdor'=>'decimal:3','tan_narx'=>'decimal:2','jami_summa'=>'decimal:2'];

    public function tovar() { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
    public function kirim() { return $this->belongsTo(OmboraKirim::class, 'kirim_id'); }
}
