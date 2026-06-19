<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TovarGuruh extends Model
{
    protected $table    = 'tovar_guruhlar';
    protected $fillable = ['nomi', 'tavsif', 'holat'];

    public function tovarlar() { return $this->hasMany(TovarKatalog::class, 'guruh_id'); }
    public function scopeFaol($q) { return $q->where('holat', 'faol'); }
}
