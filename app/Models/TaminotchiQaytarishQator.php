<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaminotchiQaytarishQator extends Model {
    protected $table = 'taminotchi_qaytarish_qatorlar';
    protected $fillable = ['qaytarish_id','tovar_id','nomi','miqdor','birlik','narx','jami','sabab'];
    public function qaytarish(): BelongsTo { return $this->belongsTo(TaminotchiQaytarish::class,'qaytarish_id'); }
    public function tovar(): BelongsTo     { return $this->belongsTo(TovarKatalog::class,'tovar_id'); }
}
