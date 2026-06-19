<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TashkilotRekvizit extends Model {
    protected $table = 'tashkilot_rekvizitlari';
    protected $fillable = [
        'nomi','qisqa_nomi','stir','mfo','bank_nomi','hisob_raqam','tranzit_hisob',
        'yuridik_manzil','haqiqiy_manzil','telefon','email',
        'direktor_ism','hisobchi_ism','imzochi_ism','imzochi_lavozim',
        'logo_yol','muhr_yol','filial_id','asosiy','holat',
    ];
    protected $casts = ['asosiy'=>'boolean'];
    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public static function asosiyni(): ?self { return static::where('asosiy',true)->where('holat','faol')->first(); }
}
