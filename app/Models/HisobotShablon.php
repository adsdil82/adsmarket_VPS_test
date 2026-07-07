<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HisobotShablon extends Model
{
    protected $table = 'hisobot_shablonlari';

    protected $fillable = [
        'foydalanuvchi_id', 'nomi', 'modul', 'ustunlar', 'shartlar',
        'sana_turi', 'dan_sana', 'gacha_sana', 'guruhlash',
    ];

    protected $casts = [
        'ustunlar'   => 'array',
        'shartlar'   => 'array',
        'dan_sana'   => 'date',
        'gacha_sana' => 'date',
    ];

    public function foydalanuvchi()
    {
        return $this->belongsTo(Foydalanuvchi::class, 'foydalanuvchi_id');
    }
}
