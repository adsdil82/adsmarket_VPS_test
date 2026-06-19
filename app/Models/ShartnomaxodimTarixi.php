<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShartnomaxodimTarixi extends Model {
    protected $table = 'shartnoma_xodim_tarixi';
    protected $fillable = ['shartnoma_id','eski_xodim_id','yangi_xodim_id','ozgartirgan_id','sabab','izoh'];
    public function shartnoma(): BelongsTo   { return $this->belongsTo(RegKredit::class,'shartnoma_id'); }
    public function eskiXodim(): BelongsTo   { return $this->belongsTo(Foydalanuvchi::class,'eski_xodim_id'); }
    public function yangiXodim(): BelongsTo  { return $this->belongsTo(Foydalanuvchi::class,'yangi_xodim_id'); }
    public function ozgartirgan(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class,'ozgartirgan_id'); }
}
