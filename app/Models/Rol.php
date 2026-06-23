<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rollar';

    protected $fillable = ['kalit', 'nomi', 'icon', 'tizim', 'tartib'];

    protected $casts = [
        'tizim' => 'boolean',
    ];

    public function scopeTartibBoyicha($query)
    {
        return $query->orderBy('tartib')->orderBy('id');
    }
}
