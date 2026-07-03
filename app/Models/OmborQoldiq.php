<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OmborQoldiq extends Model
{
    protected $table = 'ombor_qoldiqlar';
    protected $fillable = ['ombor_id', 'tovar_id', 'miqdor'];
    protected $casts = ['miqdor' => 'decimal:3'];

    public function ombor(): BelongsTo { return $this->belongsTo(Ombor::class, 'ombor_id'); }
    public function tovar(): BelongsTo { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
}
