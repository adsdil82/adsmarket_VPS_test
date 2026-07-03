<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BLQiymat extends Model
{
    protected $table = 'bl_qiymatlari';
    protected $fillable = ['qator_id', 'filial_id', 'sana', 'summa', 'izoh', 'xodim_id'];
    protected $casts = ['sana' => 'date', 'summa' => 'decimal:2'];

    public function qator(): BelongsTo  { return $this->belongsTo(BLQator::class, 'qator_id'); }
    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public function xodim(): BelongsTo  { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
}
