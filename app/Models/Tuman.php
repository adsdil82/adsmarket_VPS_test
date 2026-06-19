<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tuman extends Model
{
    protected $table = 'tumanlar';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['id', 'viloyat_id', 'nomi', 'kirill_nomi', 'sort_order'];

    public function viloyat(): BelongsTo
    {
        return $this->belongsTo(Viloyat::class, 'viloyat_id');
    }
}
