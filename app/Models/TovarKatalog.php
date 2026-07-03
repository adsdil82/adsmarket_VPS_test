<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TovarKatalog extends Model
{
    protected $table    = 'tovar_katalog';
    protected $fillable = ['guruh_id','nomi','barkod','birlik','tan_narx','sotish_narx','nasiya_narx','qoldiq','min_qoldiq','holat','izoh'];
    protected $casts    = ['tan_narx'=>'decimal:2','sotish_narx'=>'decimal:2','nasiya_narx'=>'decimal:2','qoldiq'=>'decimal:3'];

    public function guruh()    { return $this->belongsTo(TovarGuruh::class, 'guruh_id'); }
    public function omborQoldiqlar() { return $this->hasMany(OmborQoldiq::class, 'tovar_id'); }
    public function scopeFaol($q) { return $q->where('holat', 'faol'); }

    /** Qoldiq kamligi belgisi */
    public function getKamQoldiqAttribute(): bool
    {
        return $this->qoldiq <= $this->min_qoldiq && $this->min_qoldiq > 0;
    }

    /** Omborda bor yoki yo'q */
    public function getOmbordaMavjudAttribute(): bool
    {
        return $this->qoldiq > 0;
    }
}
