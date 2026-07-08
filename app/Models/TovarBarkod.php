<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TovarBarkod extends Model
{
    protected $table    = 'tovar_barkodlar';
    protected $fillable = ['tovar_id', 'barkod'];

    public function tovar() { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
}
