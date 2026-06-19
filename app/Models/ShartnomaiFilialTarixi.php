<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShartnomaiFilialTarixi extends Model {
    protected $table = 'shartnoma_filial_tarixi';
    protected $fillable = ['shartnoma_id','eski_filial_id','yangi_filial_id','ozgartirgan_id',
        'sabab','izoh','tolovlar_yangi_filialda'];
    protected $casts = ['tolovlar_yangi_filialda'=>'boolean'];
    public function shartnoma(): BelongsTo   { return $this->belongsTo(RegKredit::class,'shartnoma_id'); }
    public function eskiFilial(): BelongsTo  { return $this->belongsTo(Filial::class,'eski_filial_id'); }
    public function yangiFilial(): BelongsTo { return $this->belongsTo(Filial::class,'yangi_filial_id'); }
    public function ozgartirgan(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class,'ozgartirgan_id'); }
}
