<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Valyuta extends Model {
    protected $table = 'valyutalar';
    protected $fillable = ['kod','nomi','belgi','kurs','kurs_sana','asosiy','holat'];
    protected $casts = ['asosiy'=>'boolean','kurs_sana'=>'date','kurs'=>'decimal:4'];
    public function scopeFaol($q) { return $q->where('holat','faol'); }
    public static function asosiy(): ?self { return static::where('asosiy',true)->where('holat','faol')->first(); }
}
