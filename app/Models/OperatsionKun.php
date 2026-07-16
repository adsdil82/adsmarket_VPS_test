<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperatsionKun extends Model
{
    protected $table = 'operatsion_kunlar';

    protected $fillable = [
        'filial_id', 'sana', 'status',
        'yopilgan_vaqt', 'yopgan_user_id',
        'ochilgan_vaqt', 'ochgan_user_id', 'izoh',
    ];

    protected $casts = [
        'sana'           => 'date',
        'yopilgan_vaqt'  => 'datetime',
        'ochilgan_vaqt'  => 'datetime',
    ];

    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class);
    }

    public function yopganUser(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'yopgan_user_id');
    }

    public function ochganUser(): BelongsTo
    {
        return $this->belongsTo(Foydalanuvchi::class, 'ochgan_user_id');
    }

    public function loglar(): HasMany
    {
        return $this->hasMany(KunYopishLogi::class, 'operatsion_kun_id');
    }

    public function yopiqmi(): bool
    {
        return $this->status === 'yopiq';
    }

    public static function filialSanaYopiqmi(int $filialId, string $sana): bool
    {
        return static::where('filial_id', $filialId)
            ->where('sana', $sana)
            ->where('status', 'yopiq')
            ->exists();
    }
}
