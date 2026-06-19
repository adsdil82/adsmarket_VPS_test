<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaminotchiQaytarish extends Model {
    protected $table = 'taminotchi_qaytarish';
    protected $fillable = ['hujjat_raqam','taminotchi_id','ombor_id','filial_id','xodim_id',
        'tasdiqlagan_id','sana','jami_summa','holat','sabab','izoh'];
    protected $casts = ['sana'=>'date'];
    public function taminotchi(): BelongsTo { return $this->belongsTo(Taminotchi::class); }
    public function ombor(): BelongsTo      { return $this->belongsTo(Ombor::class); }
    public function filial(): BelongsTo     { return $this->belongsTo(Filial::class); }
    public function xodim(): BelongsTo      { return $this->belongsTo(Foydalanuvchi::class,'xodim_id'); }
    public function qatorlar(): HasMany     { return $this->hasMany(TaminotchiQaytarishQator::class,'qaytarish_id'); }
    public function getHolatRangiAttribute(): string {
        return match($this->holat) {
            'tasdiqlangan'=>'success','qaytarildi'=>'info','bekor'=>'danger',default=>'secondary'
        };
    }
    protected static function booted(): void {
        static::creating(function ($m) {
            if (!$m->hujjat_raqam) {
                $m->hujjat_raqam = 'QAY-'.now()->format('Ymd').'-'.str_pad(
                    (static::whereDate('created_at',today())->count()+1),4,'0',STR_PAD_LEFT);
            }
        });
    }
}
