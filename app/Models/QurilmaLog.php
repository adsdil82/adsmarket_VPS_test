<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurilmaLog extends Model
{
    protected $table = 'qurilma_loglar';

    protected $fillable = [
        'qurilma_id','provayder_id','reg_kredit_id',
        'amal','holat','sabab','javob','xodim_id','ip_manzil',
    ];

    // Amal nomlari
    const AMAL_CREATED         = 'yaratildi';
    const AMAL_ATTACHED        = 'shartnomaga_biriktirildi';
    const AMAL_WARNING         = 'ogoh_berildi';
    const AMAL_LOCKED          = 'bloklandi';
    const AMAL_UNLOCK_PENDING  = 'blok_ochilmoqda';
    const AMAL_UNLOCKED        = 'blok_ochildi';
    const AMAL_RELEASED        = 'ozod_qilindi';
    const AMAL_SYNCED          = 'sinxronlandi';
    const AMAL_IMEI_CHANGED    = 'imei_ozgartirildi';
    const AMAL_STATUS_CHANGED  = 'holat_ozgartirildi';

    public static array $amalga_nomlari = [
        'yaratildi'                 => 'Qurilma qo\'shildi',
        'shartnomaga_biriktirildi'  => 'Shartnomaga biriktirildi',
        'ogoh_berildi'              => 'Ogohlantirish yuborildi',
        'bloklandi'                 => 'Bloklandi',
        'blok_ochilmoqda'           => 'Blok ochilmoqda',
        'blok_ochildi'              => 'Blokdan chiqarildi',
        'ozod_qilindi'              => 'Ozod qilindi',
        'sinxronlandi'              => 'Sinxronlandi',
        'imei_ozgartirildi'         => 'IMEI o\'zgartirildi',
        'holat_ozgartirildi'        => 'Holat o\'zgartirildi',
    ];

    public static array $holatRanglari = [
        'muvaffaqiyat' => 'success',
        'xato'         => 'danger',
        'kutilmoqda'   => 'warning',
    ];

    public function qurilma(): BelongsTo    { return $this->belongsTo(Qurilma::class, 'qurilma_id'); }
    public function provayder(): BelongsTo  { return $this->belongsTo(QurilmaProvayder::class, 'provayder_id'); }
    public function xodim(): BelongsTo      { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }

    public function getAmalNomiAttribute(): string
    {
        return static::$amalga_nomlari[$this->amal] ?? $this->amal;
    }

    public function getHolatRangiAttribute(): string
    {
        return static::$holatRanglari[$this->holat] ?? 'secondary';
    }

    public function scopeMuvaffaqiyat($q) { return $q->where('holat', 'muvaffaqiyat'); }
    public function scopeXato($q)         { return $q->where('holat', 'xato'); }
}
