<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KassaTransfer extends Model {
    protected $table = 'kassa_transferlar';
    protected $fillable = [
        'transfer_raqam','from_filial_id','from_kassa_id','to_filial_id','to_kassa_id',
        'summa','valyuta','kurs','summa_uzs','holat','sana','xodim_id',
        'tasdiqlagan_id','tasdiqlangan_vaqt','izoh','sabab',
    ];
    protected $casts = ['sana'=>'date','tasdiqlangan_vaqt'=>'datetime','summa'=>'decimal:2'];
    public function fromFilial(): BelongsTo  { return $this->belongsTo(Filial::class,'from_filial_id'); }
    public function toFilial(): BelongsTo    { return $this->belongsTo(Filial::class,'to_filial_id'); }
    public function fromKassa(): BelongsTo   { return $this->belongsTo(Kassa::class,'from_kassa_id'); }
    public function toKassa(): BelongsTo     { return $this->belongsTo(Kassa::class,'to_kassa_id'); }
    public function xodim(): BelongsTo       { return $this->belongsTo(Foydalanuvchi::class,'xodim_id'); }
    public function tasdiqlagan(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class,'tasdiqlagan_id'); }
    public function getHolatRangiAttribute(): string {
        return match($this->holat) {
            'yuborildi'=>'warning','qabul_qilindi'=>'success','bekor'=>'danger',default=>'secondary'
        };
    }
    protected static function booted(): void {
        static::creating(function ($m) {
            if (!$m->transfer_raqam) {
                $m->transfer_raqam = 'KT-'.now()->format('Ymd').'-'.str_pad(
                    (static::whereDate('created_at',today())->count()+1),4,'0',STR_PAD_LEFT
                );
            }
        });
    }
}
