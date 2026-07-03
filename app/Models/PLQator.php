<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PLQator extends Model
{
    protected $table = 'pl_qatorlari';
    protected $fillable = ['bolim_id', 'nomi', 'hisoblash_turi', 'ishora', 'subtotal', 'sort_order', 'holat'];
    protected $casts = ['subtotal' => 'boolean'];

    public function bolim(): BelongsTo { return $this->belongsTo(PLBolim::class, 'bolim_id'); }

    public function harajatTurlari(): BelongsToMany
    {
        return $this->belongsToMany(HarajatTuri::class, 'pl_qator_harajat_turlari', 'qator_id', 'harajat_turi_id');
    }

    public function qiymatlar(): HasMany { return $this->hasMany(PLQiymat::class, 'qator_id'); }

    public function scopeAvtomat($q) { return $q->where('hisoblash_turi', '!=', 'qolda'); }
    public function scopeQolda($q)   { return $q->where('hisoblash_turi', 'qolda'); }
}
