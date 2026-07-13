<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XodimDavomat extends Model
{
    protected $table = 'xodim_davomat';

    protected $fillable = [
        'xodim_id', 'sana', 'holat', 'izoh', 'belgilagan_id',
    ];

    protected $casts = [
        'sana' => 'date',
    ];

    /** Ish kuni hisoblanadigan holatlar (dam olish/tatil/kasal hisobga kirmaydi) */
    public const ISH_KUNI_HOLATLARI = ['keldi', 'kech_qoldi', 'kelmadi'];

    /** Kelgan hisoblanadigan holatlar (davomat foizi shular asosida hisoblanadi) */
    public const KELGAN_HOLATLARI = ['keldi', 'kech_qoldi'];

    /** Tabel gridida har bir holat uchun icon + rang (kalit tartibi = tanlov tartibi) */
    public const ICON_HOLATLARI = [
        'keldi'      => ['icon' => '✓', 'rang' => '#16a34a', 'nomi' => 'Keldi'],
        'kech_qoldi' => ['icon' => '◷', 'rang' => '#d97706', 'nomi' => "Kech qoldi"],
        'kelmadi'    => ['icon' => '✕', 'rang' => '#dc2626', 'nomi' => 'Kelmadi'],
        'tatil'      => ['icon' => '✈', 'rang' => '#2563eb', 'nomi' => "Ta'til"],
        'kasal'      => ['icon' => '+',  'rang' => '#7c3aed', 'nomi' => 'Kasal'],
        'dam_olish'  => ['icon' => '—', 'rang' => '#94a3b8', 'nomi' => 'Dam olish kuni'],
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function belgilagan(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'belgilagan_id');
    }

    public function holatBadge(): string
    {
        return match ($this->holat) {
            'keldi'      => '<span class="badge bg-success">Keldi</span>',
            'kech_qoldi' => '<span class="badge bg-warning text-dark">Kech qoldi</span>',
            'kelmadi'    => '<span class="badge bg-danger">Kelmadi</span>',
            'tatil'      => '<span class="badge bg-info text-dark">Ta\'til</span>',
            'kasal'      => '<span class="badge bg-secondary">Kasal</span>',
            'dam_olish'  => '<span class="badge bg-light text-dark border">Dam olish</span>',
            default      => '<span class="badge bg-secondary">' . e($this->holat) . '</span>',
        };
    }
}
