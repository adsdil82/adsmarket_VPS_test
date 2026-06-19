<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaminotKirimQator extends Model
{
    protected $table = 'taminot_kirim_qatorlar';
    protected $fillable = ['kirim_id','tovar_id','nomi','miqdor','birlik','narx','jami'];

    public function kirim(): BelongsTo { return $this->belongsTo(TaminotKirim::class,'kirim_id'); }
    public function tovar(): BelongsTo { return $this->belongsTo(TovarKatalog::class,'tovar_id'); }
}
