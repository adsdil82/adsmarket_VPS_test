<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QurilmaProvayderSozlama extends Model
{
    protected $table = 'qurilma_provayder_sozlamalari';

    protected $fillable = ['provayder_id','kalit','qiymat','tur','sarlavha','tavsif','majburiy'];

    protected $casts = ['majburiy' => 'boolean'];

    public function provayder(): BelongsTo
    {
        return $this->belongsTo(QurilmaProvayder::class, 'provayder_id');
    }

    public function isSecret(): bool
    {
        return $this->tur === 'secret';
    }

    public function getMaskedQiymatAttribute(): string
    {
        if ($this->isSecret() && $this->qiymat) {
            return str_repeat('*', 8);
        }
        return $this->qiymat ?? '';
    }
}
