<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rollar';

    protected $fillable = ['kalit', 'nomi', 'icon', 'tizim', 'tartib', 'ustama_korish'];

    protected $casts = [
        'tizim'         => 'boolean',
        'ustama_korish' => 'boolean',
    ];

    public function scopeTartibBoyicha($query)
    {
        return $query->orderBy('tartib')->orderBy('id');
    }

    public function tulovTurlari()
    {
        return $this->belongsToMany(TulovTuri::class, 'rol_tulov_turlari');
    }

    /** Joriy foydalanuvchi roli "ustama" (markup/foiz) ma'lumotini ko'rishi mumkinmi */
    public static function ustamaKorishMumkinmi(?string $rolKaliti): bool
    {
        if (!$rolKaliti) return false;
        return (bool) static::where('kalit', $rolKaliti)->value('ustama_korish');
    }

    /**
     * Berilgan rol uchun ko'rinadigan to'lov turlari ID'lari ro'yxati.
     * Agar rol uchun cheklov sozlanmagan bo'lsa (rol_tulov_turlari'da yozuv
     * yo'q) — null qaytaradi, bu "cheklovsiz, hammasi ko'rinadi" degani.
     */
    public static function korinadiganTulovTurlari(?string $rolKaliti): ?array
    {
        if (!$rolKaliti) return null;
        $rol = static::where('kalit', $rolKaliti)->first();
        if (!$rol) return null;
        $ids = $rol->tulovTurlari()->pluck('tulov_turlari.id')->toArray();
        return $ids ? $ids : null;
    }
}
