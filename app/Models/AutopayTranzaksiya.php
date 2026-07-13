<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AutopayTranzaksiya extends Model
{
    protected $table    = 'autopay_tranzaksiyalar';
    protected $fillable = ['autopay_shartnoma_id', 'ext_id', 'rrn', 'summa', 'holat', 'sana', 'karta_pan', 'karta_token', 'karta_egasi', 'tulov_id', 'xom_javob'];
    protected $casts    = ['sana' => 'datetime', 'xom_javob' => 'array'];

    public function shartnoma() { return $this->belongsTo(AutopayShartnoma::class, 'autopay_shartnoma_id'); }
    public function tulov()     { return $this->belongsTo(Tulov::class, 'tulov_id'); }
}
