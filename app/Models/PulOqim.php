<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class PulOqim extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'pul_oqimlari';

    protected $fillable = [
        'filial_id','kassa_id','kategoriya_id','hisob_id','xodim_id',
        'yunalish','sana','summa','izoh',
        'manba_tur','manba_id',
        'holat','tasdiqlagan_id','eski_harajat_id',
    ];

    protected $casts = [
        'sana'  => 'date',
        'summa' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function filial(): BelongsTo    { return $this->belongsTo(Filial::class); }
    public function kassa(): BelongsTo     { return $this->belongsTo(Kassa::class); }
    public function kategoriya(): BelongsTo { return $this->belongsTo(PulKategoriya::class, 'kategoriya_id'); }
    public function hisob(): BelongsTo     { return $this->belongsTo(HisobRejasi::class, 'hisob_id'); }
    public function xodim(): BelongsTo     { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
    public function tasdiqlagan(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'tasdiqlagan_id'); }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeKirim($q)         { return $q->where('yunalish', 'kirim'); }
    public function scopeChiqim($q)        { return $q->where('yunalish', 'chiqim'); }
    public function scopeTasdiqlangan($q)  { return $q->where('holat', 'tasdiqlangan'); }
    public function scopeSanada($q, string $dan, string $gacha) {
        return $q->whereBetween('sana', [$dan, $gacha]);
    }
    public function scopeFilialda($q, ?int $filialId) {
        return $filialId ? $q->where('filial_id', $filialId) : $q;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isKirim(): bool  { return $this->yunalish === 'kirim'; }
    public function isChiqim(): bool { return $this->yunalish === 'chiqim'; }

    /** Oylik statistika: kirim, chiqim, sof */
    public static function oylikStatistika(?int $filialId, string $dan, string $gacha): array
    {
        $q = static::tasdiqlangan()->sanada($dan, $gacha)->filialda($filialId);
        return [
            'kirim'  => (clone $q)->kirim()->sum('summa'),
            'chiqim' => (clone $q)->chiqim()->sum('summa'),
        ];
    }
}
