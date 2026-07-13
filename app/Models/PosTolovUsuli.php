<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PosTolovUsuli extends Model
{
    protected $table    = 'pos_tolov_usullari';
    protected $fillable = ['filial_id', 'nomi', 'turi', 'holat', 'tartib', 'izoh'];

    public function filial() { return $this->belongsTo(Filial::class, 'filial_id'); }
    public function scopeFaol($q) { return $q->where('holat', 'faol'); }
}
