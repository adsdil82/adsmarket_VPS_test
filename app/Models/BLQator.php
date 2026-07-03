<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BLQator extends Model
{
    protected $table = 'bl_qatorlari';
    protected $fillable = ['bolim_id', 'nomi', 'hisoblash_turi', 'joriy_holat_faqat', 'sort_order', 'holat'];
    protected $casts = ['joriy_holat_faqat' => 'boolean'];

    public function bolim(): BelongsTo { return $this->belongsTo(BLBolim::class, 'bolim_id'); }
    public function qiymatlar(): HasMany { return $this->hasMany(BLQiymat::class, 'qator_id'); }
}
