<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FilialTransfer extends Model
{
    protected $table    = 'filiallar_transfer';
    protected $fillable = [
        'transfer_raqam',
        'from_filial_id','from_ombor_id',
        'to_filial_id','to_ombor_id',
        'xodim_id','sana','holat','izoh','sabab',
        'tasdiqlagan_xodim_id','tasdiqlangan_vaqt',
    ];
    protected $casts = ['sana'=>'date','tasdiqlangan_vaqt'=>'datetime'];

    public function fromFilial()    { return $this->belongsTo(Filial::class, 'from_filial_id'); }
    public function toFilial()      { return $this->belongsTo(Filial::class, 'to_filial_id'); }
    public function xodim()         { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tasdiqlagan()   { return $this->belongsTo(Foydalanuvchi::class, 'tasdiqlagan_xodim_id'); }
    public function tafsilot()      { return $this->hasMany(TransferTafsilot::class, 'transfer_id'); }

    public function getHolatRangiAttribute(): string
    {
        return match($this->holat) {
            'qoralama'      => 'secondary',
            'yuborildi'     => 'warning',
            'qabul_qilindi' => 'success',
            'bekor'         => 'danger',
            'kutilmoqda'    => 'warning',
            'tasdiqlangan'  => 'success',
            default         => 'secondary',
        };
    }
}