<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OmboraKirim extends Model
{
    protected $table    = 'omborga_kirim';
    protected $fillable = ['filial_id','xodim_id','sana','yetkazuvchi','hujjat_raqam','umumiy_summa','izoh','holat'];
    protected $casts    = ['sana' => 'date'];

    public function filial()    { return $this->belongsTo(Filial::class, 'filial_id'); }
    public function xodim()     { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tafsilot()  { return $this->hasMany(KirimTafsilot::class, 'kirim_id'); }
}
