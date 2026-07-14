<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XodimTatil extends Model
{
    protected $table = 'xodim_tatillar';

    protected $fillable = [
        'xodim_id', 'turi', 'boshlanish_sana', 'rejalashtirilgan_qaytish_sana',
        'haqiqiy_qaytgan_sana', 'izoh', 'holat', 'created_by',
    ];

    protected $casts = [
        'boshlanish_sana'               => 'date',
        'rejalashtirilgan_qaytish_sana' => 'date',
        'haqiqiy_qaytgan_sana'          => 'date',
    ];

    /** Ta'til turiga mos xodim_davomat holati. */
    public const DAVOMAT_HOLATI = [
        'yillik'             => 'tatil',
        'haq_tolanmaydigan'  => 'tatil',
        'kasallik'           => 'kasal',
        'boshqa'             => 'tatil',
    ];

    public function xodim(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'xodim_id');
    }

    public function yaratuvchi(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'created_by');
    }

    public function holatBadge(): string
    {
        return match ($this->holat) {
            'rejalashtirilgan' => '<span class="badge bg-info text-dark">Davom etmoqda</span>',
            'yakunlandi'       => '<span class="badge bg-success">Yakunlandi</span>',
            'bekor_qilindi'    => '<span class="badge bg-secondary">Bekor qilindi</span>',
            default            => '<span class="badge bg-secondary">' . e($this->holat) . '</span>',
        };
    }
}
