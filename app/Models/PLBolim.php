<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PLBolim extends Model
{
    protected $table = 'pl_bolimlar';
    protected $fillable = ['nomi', 'ishora', 'sort_order'];

    public function qatorlar(): HasMany
    {
        return $this->hasMany(PLQator::class, 'bolim_id')->where('holat', 'faol')->orderBy('sort_order');
    }
}
