<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosSotuv extends Model
{
    protected $table    = 'pos_sotuv';
    protected $fillable = ['filial_id','smena_id','xodim_id','sana','check_raqam','umumiy_summa','chegirma','jami_tolov','tolov_turi','tolov_usuli_id','naqd_summa','plastik_summa','qayta_pul','mijoz_ism','tolov_izoh','holat'];
    protected $casts    = ['sana' => 'date'];

    public function filial()   { return $this->belongsTo(Filial::class, 'filial_id'); }
    public function xodim()    { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tafsilot() { return $this->hasMany(PosTafsilot::class, 'sotuv_id'); }
    public function smena()    { return $this->belongsTo(PosSmena::class, 'smena_id'); }
    public function tolovUsuli() { return $this->belongsTo(PosTolovUsuli::class, 'tolov_usuli_id'); }

    public static function yangiCheckRaqam(int $filialId): string
    {
        $bugun = now()->format('Ymd');
        $oxirgi = static::whereDate('created_at', today())
            ->where('filial_id', $filialId)
            ->count();
        return "POS-{$filialId}-{$bugun}-" . str_pad($oxirgi + 1, 4, '0', STR_PAD_LEFT);
    }
}
