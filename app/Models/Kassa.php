<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kassa extends Model {
    protected $table = 'kassalar';
    protected $fillable = ['filial_id','nomi','tur','qoldiq','valyuta','holat','izoh'];
    protected $casts   = ['qoldiq'=>'decimal:2'];
    public function filial(): BelongsTo   { return $this->belongsTo(Filial::class); }
    public function transferlar(): HasMany { return $this->hasMany(KassaTransfer::class,'from_kassa_id'); }
    public function scopeFaol($q)         { return $q->where('holat','faol'); }
}
