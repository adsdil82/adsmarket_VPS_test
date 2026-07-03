<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BLBolim extends Model
{
    protected $table = 'bl_bolimlar';
    protected $fillable = ['nomi', 'tur', 'sort_order'];

    public function qatorlar(): HasMany
    {
        return $this->hasMany(BLQator::class, 'bolim_id')->where('holat', 'faol')->orderBy('sort_order');
    }
}
