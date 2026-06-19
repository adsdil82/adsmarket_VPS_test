<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Sozlama extends Model
{
    protected $table    = 'sozlamalar';
    protected $fillable = ['kalit', 'qiymat'];
    public    $timestamps = false;

    /** Barcha sozlamalarni kalit → qiymat ko'rinishida olish (cached) */
    public static function barchasi(): array
    {
        return Cache::remember('sozlamalar_all', 3600, function () {
            return static::pluck('qiymat', 'kalit')->toArray();
        });
    }

    /** Bitta sozlamani olish */
    public static function ol(string $kalit, string $default = ''): string
    {
        return static::barchasi()[$kalit] ?? $default;
    }

    /** Bir yoki bir nechta sozlamani saqlash */
    public static function saqlash(array $data): void
    {
        foreach ($data as $kalit => $qiymat) {
            static::updateOrCreate(
                ['kalit' => $kalit],
                ['qiymat' => $qiymat]
            );
        }
        Cache::forget('sozlamalar_all');
    }
}
