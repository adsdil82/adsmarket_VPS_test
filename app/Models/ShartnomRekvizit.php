<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ShartnomRekvizit extends Model {
    protected $table = 'shartnoma_rekvizitlari';
    protected $fillable = [
        'nomi','filial_id','tashkilot_rekvizit_id','prefiks','keyingi_raqam',
        'raqam_formati','imzochi_ism','imzochi_lavozim','asosiy','holat',
    ];
    protected $casts = ['asosiy'=>'boolean'];
    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public function tashkilotRekvizit(): BelongsTo { return $this->belongsTo(TashkilotRekvizit::class,'tashkilot_rekvizit_id'); }
    public function keyingiShartnomaNomeri(): string {
        return str_replace(['{PREFIX}','{RAQAM}','{YIL}'],
            [$this->prefiks ?? '', str_pad($this->keyingi_raqam,4,'0',STR_PAD_LEFT), date('Y')],
            $this->raqam_formati);
    }
}
