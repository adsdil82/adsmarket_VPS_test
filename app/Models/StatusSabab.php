<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StatusSabab extends Model {
    protected $table = 'statuslar_sabablar';
    protected $fillable = ['modul','tur','kod','nomi','rang','icon','tizim_holati','holat','sort_order'];
    protected $casts = ['tizim_holati'=>'boolean'];
    public function scopeFaol($q) { return $q->where('holat','faol'); }
    public function scopeModulda($q, string $modul) { return $q->where('modul',$modul); }
    public function scopeStatuslar($q) { return $q->where('tur','status'); }
    public function scopeSabablar($q) { return $q->where('tur','sabab'); }
}
