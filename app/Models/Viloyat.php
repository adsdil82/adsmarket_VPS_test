<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Viloyat extends Model
{
    protected $table = 'viloyatlar';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['id', 'nomi', 'kirill_nomi', 'sort_order'];

    public function tumanlar(): HasMany
    {
        return $this->hasMany(Tuman::class, 'viloyat_id')->orderBy('sort_order');
    }

    public static function royhati(): \Illuminate\Database\Eloquent\Collection
    {
        return static::orderBy('sort_order')->get(['id', 'nomi']);
    }
}
