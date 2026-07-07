<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosQaytim extends Model
{
    protected $table = 'pos_qaytimlar';

    protected $fillable = [
        'qaytim_raqami', 'sotuv_id', 'smena_id', 'filial_id', 'xodim_id', 'sana',
        'tolov_turi', 'jami_summa', 'sabab', 'mijoz_ism', 'izoh', 'holat',
    ];

    protected $casts = [
        'sana'       => 'date',
        'jami_summa' => 'decimal:2',
    ];

    public function sotuv(): BelongsTo { return $this->belongsTo(PosSotuv::class, 'sotuv_id'); }
    public function smena(): BelongsTo { return $this->belongsTo(PosSmena::class, 'smena_id'); }
    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public function xodim(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tafsilot(): HasMany { return $this->hasMany(PosQaytimTafsilot::class, 'qaytim_id'); }

    public static function yangiQaytimRaqami(int $filialId): string
    {
        $bugun = now()->format('Ymd');
        $soni = static::whereDate('created_at', today())->where('filial_id', $filialId)->count();
        return "QR-{$filialId}-{$bugun}-" . str_pad($soni + 1, 3, '0', STR_PAD_LEFT);
    }
}
