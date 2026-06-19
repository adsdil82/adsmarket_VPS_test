<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Brend extends Model {
    protected $table = 'brendlar';
    protected $fillable = ['nomi','kod','mamlakat','logo_yol','holat','sort_order'];
    public function scopeFaol($q) { return $q->where('holat','faol'); }
    public function scopeTartibli($q) { return $q->orderBy('sort_order')->orderBy('nomi'); }
}
