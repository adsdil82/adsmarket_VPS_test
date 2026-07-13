<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AutopayKarta extends Model
{
    protected $table    = 'autopay_kartalar';
    protected $fillable = ['mijoz_id', 'uuid', 'pan', 'turi', 'egasi', 'telefon', 'auto', 'is_verified', 'is_blocked', 'block_reason'];
    protected $casts    = ['auto' => 'boolean', 'is_verified' => 'boolean', 'is_blocked' => 'boolean'];

    public function mijoz() { return $this->belongsTo(Mijoz::class, 'mijoz_id'); }
}
