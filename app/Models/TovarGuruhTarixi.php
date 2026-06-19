<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TovarGuruhTarixi extends Model {
    protected $table = 'tovar_guruh_tarix';
    protected $fillable = ['tovar_id','eski_guruh_id','yangi_guruh_id','xodim_id','sabab'];
    public function tovar(): BelongsTo      { return $this->belongsTo(TovarKatalog::class,'tovar_id'); }
    public function eskiGuruh(): BelongsTo  { return $this->belongsTo(TovarGuruh::class,'eski_guruh_id'); }
    public function yangiGuruh(): BelongsTo { return $this->belongsTo(TovarGuruh::class,'yangi_guruh_id'); }
    public function xodim(): BelongsTo      { return $this->belongsTo(Foydalanuvchi::class,'xodim_id'); }
}
