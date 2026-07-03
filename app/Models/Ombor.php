<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ombor extends Model {
    protected $table = 'omborlar';
    protected $fillable = ['filial_id','nomi','manzil','mas_ul_shaxs','tur','holat','izoh'];
    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public function kirimlar(): HasMany  { return $this->hasMany(OmboraKirim::class,'ombor_id'); }
    public function chiqimlar(): HasMany { return $this->hasMany(OmbordanChiqim::class,'ombor_id'); }
    public function ledger(): HasMany    { return $this->hasMany(StockLedger::class,'ombor_id'); }
    public function qoldiqlar(): HasMany { return $this->hasMany(OmborQoldiq::class,'ombor_id'); }
    public function scopeFaol($q)        { return $q->where('holat','faol'); }
}
