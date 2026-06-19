<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QurilmaProvayder extends Model
{
    protected $table = 'qurilma_provayderlar';

    protected $fillable = [
        'kod','nomi','tur','faol','mock_rejim',
        'lock_qollab','unlock_qollab','ogoh_qollab','sinx_qollab',
        'tavsif','sort_order',
    ];

    protected $casts = [
        'faol'          => 'boolean',
        'mock_rejim'    => 'boolean',
        'lock_qollab'   => 'boolean',
        'unlock_qollab' => 'boolean',
        'ogoh_qollab'   => 'boolean',
        'sinx_qollab'   => 'boolean',
    ];

    public static array $turlar = [
        'mdm'           => 'MDM (Mobile Device Management)',
        'imei_registry' => 'IMEI Registr',
        'cloud_lock'    => 'Cloud Lock',
        'custom_app'    => 'Maxsus ilova',
        'manual'        => 'Qo\'lda boshqaruv',
    ];

    public function sozlamalar(): HasMany
    {
        return $this->hasMany(QurilmaProvayderSozlama::class, 'provayder_id');
    }

    public function ulanishlari(): HasMany
    {
        return $this->hasMany(QurilmaProvayderUlanish::class, 'provayder_id');
    }

    public function qoidalar(): HasMany
    {
        return $this->hasMany(QurilmaQoida::class, 'provayder_id');
    }

    public function sozlamaQiymati(string $kalit, mixed $default = null): mixed
    {
        $sozlama = $this->sozlamalar()->where('kalit', $kalit)->first();
        return $sozlama ? $sozlama->qiymat : $default;
    }

    public function scopeFaol($q) { return $q->where('faol', true); }
    public function scopeKodBilan($q, string $kod) { return $q->where('kod', $kod); }
}
