<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Birlik extends Model
{
    protected $table = 'birliklar';
    protected $fillable = ['nomi', 'qisqa_nomi', 'kod', 'holat', 'sort_order'];

    public function scopeFaol($q) { return $q->where('holat', 'faol'); }
    public function scopeTartibli($q) { return $q->orderBy('sort_order')->orderBy('nomi'); }
}
