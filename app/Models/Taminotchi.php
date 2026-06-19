<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taminotchi extends Model
{
    protected $table = 'taminotchilar';

    protected $fillable = [
        'nomi','kontakt_shaxs','telefon','telefon2',
        'manzil','inn','bank_hisob','bank_nomi','mfo',
        'izoh','holat','filial_id',
    ];

    // ── Munosabatlar ─────────────────────────────────────────────

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class);
    }

    public function kirimlar(): HasMany
    {
        return $this->hasMany(TaminotKirim::class, 'taminotchi_id');
    }

    public function tulovlar(): HasMany
    {
        return $this->hasMany(TaminotchiTulov::class, 'taminotchi_id');
    }

    // ── Hisob ─────────────────────────────────────────────────────

    /** Jami qabul qilingan tovarlar summasi */
    public function getJamiKirimAttribute(): float
    {
        return (float)$this->kirimlar()->sum('jami_summa');
    }

    /** Jami to'langan summa */
    public function getJamiTolovAttribute(): float
    {
        return (float)$this->tulovlar()->sum('summa');
    }

    /** Qoldiq qarz (biz ta'minotchiga qarazdormiz) */
    public function getQoldiqQarzAttribute(): float
    {
        return $this->jami_kirim - $this->jami_tolov;
    }

    /** Balans holati matnda */
    public function getBalansHolatAttribute(): string
    {
        $q = $this->qoldiq_qarz;
        if ($q > 0)  return 'qarazdor';   // biz ta'minotchiga qarazdormiz
        if ($q < 0)  return 'hakdor';     // ta'minotchi bizga qarazdor
        return 'teng';
    }

    // ── Scope'lar ────────────────────────────────────────────────

    public function scopeFaol($q) { return $q->where('holat','faol'); }

    public function scopeFilialda($q, int $filialId)
    {
        return $q->where(fn($s) => $s->where('filial_id', $filialId)
                                     ->orWhereNull('filial_id'));
    }
}
