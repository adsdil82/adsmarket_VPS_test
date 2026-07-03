<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PLQiymat extends Model
{
    protected $table = 'pl_qiymatlari';
    protected $fillable = ['qator_id', 'filial_id', 'yil', 'oy', 'summa', 'izoh', 'xodim_id'];
    protected $casts = ['summa' => 'decimal:2'];

    public function qator(): BelongsTo  { return $this->belongsTo(PLQator::class, 'qator_id'); }
    public function filial(): BelongsTo { return $this->belongsTo(Filial::class); }
    public function xodim(): BelongsTo  { return $this->belongsTo(Foydalanuvchi::class, 'xodim_id'); }
}
