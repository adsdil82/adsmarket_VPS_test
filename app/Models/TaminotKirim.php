<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaminotKirim extends Model
{
    protected $table = 'taminot_kirimlar';

    protected $fillable = [
        'taminotchi_id','filial_id','xodim_id',
        'hujjat_raqam','kirim_sana',
        'jami_summa','tolangan','qoldiq','holat','izoh',
    ];

    protected $casts = ['kirim_sana' => 'date'];

    public function taminotchi(): BelongsTo { return $this->belongsTo(Taminotchi::class); }
    public function filial(): BelongsTo    { return $this->belongsTo(Filial::class); }
    public function xodim(): BelongsTo    { return $this->belongsTo(Foydalanuvchi::class,'xodim_id'); }
    public function qatorlar(): HasMany   { return $this->hasMany(TaminotKirimQator::class,'kirim_id'); }
    public function tulovlar(): HasMany   { return $this->hasMany(TaminotchiTulov::class,'kirim_id'); }

    public function getHolatRangiAttribute(): string
    {
        return match($this->holat) {
            'toliq'     => 'success',
            'qisman'    => 'warning',
            default     => 'danger',
        };
    }
}
