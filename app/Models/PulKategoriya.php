<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PulKategoriya extends Model
{
    protected $table = 'pul_kategoriyalar';

    protected $fillable = ['ota_id','yunalish','kod','nomi','hisob_id','avtomatik','rang','sort_order','holat'];

    protected $casts = ['avtomatik' => 'boolean'];

    public function ota(): BelongsTo
    {
        return $this->belongsTo(PulKategoriya::class, 'ota_id');
    }

    public function bolalar(): HasMany
    {
        return $this->hasMany(PulKategoriya::class, 'ota_id')->orderBy('sort_order');
    }

    public function hisob(): BelongsTo
    {
        return $this->belongsTo(HisobRejasi::class, 'hisob_id');
    }

    public function scopeFaol($q) { return $q->where('holat', 'faol'); }
    public function scopeKirim($q) { return $q->where('yunalish', 'kirim'); }
    public function scopeChiqim($q) { return $q->where('yunalish', 'chiqim'); }
    public function scopeAsosiy($q) { return $q->whereNull('ota_id'); }

    public static function kirimRoyxat(): array
    {
        return static::faol()->kirim()->with('bolalar')->asosiy()->orderBy('sort_order')->get()
            ->flatMap(fn($k) => collect([$k->id => "{$k->kod} — {$k->nomi}"])
                ->merge($k->bolalar->mapWithKeys(fn($b) => [$b->id => "  {$b->kod} — {$b->nomi}"]))
            )->toArray();
    }

    public static function chiqimRoyxat(): array
    {
        return static::faol()->chiqim()->with('bolalar')->asosiy()->orderBy('sort_order')->get()
            ->flatMap(fn($k) => collect([$k->id => "{$k->kod} — {$k->nomi}"])
                ->merge($k->bolalar->mapWithKeys(fn($b) => [$b->id => "  {$b->kod} — {$b->nomi}"]))
            )->toArray();
    }
}
