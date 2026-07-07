<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosQaytimTafsilot extends Model
{
    public $timestamps = false;
    protected $table = 'pos_qaytim_tafsilot';

    protected $fillable = ['qaytim_id', 'tafsilot_id', 'tovar_id', 'miqdor', 'narx', 'jami_summa'];

    public function qaytim(): BelongsTo { return $this->belongsTo(PosQaytim::class, 'qaytim_id'); }
    public function tafsilot(): BelongsTo { return $this->belongsTo(PosTafsilot::class, 'tafsilot_id'); }
    public function tovar(): BelongsTo { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
}
