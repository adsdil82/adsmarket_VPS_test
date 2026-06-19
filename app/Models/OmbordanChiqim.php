<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OmbordanChiqim extends Model
{
    protected $table    = 'ombordan_chiqim';
    protected $fillable = ['filial_id','xodim_id','sana','sabab','umumiy_summa','izoh','holat'];
    protected $casts    = ['sana' => 'date'];

    public function filial()   { return $this->belongsTo(Filial::class, 'filial_id'); }
    public function xodim()    { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tafsilot() { return $this->hasMany(ChiqimTafsilot::class, 'chiqim_id'); }

    public static $sabablar = [
        'nasiya_sotish'    => 'Nasiya sotish',
        'naqd_sotish'      => 'Naqd sotish',
        'qaytarish'        => 'Qaytarish',
        'hisobdan_chiqish' => 'Hisobdan chiqish',
        'boshqa'           => 'Boshqa',
    ];
}
