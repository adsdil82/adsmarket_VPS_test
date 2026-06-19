<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model {
    protected $table = 'stock_ledger';
    protected $fillable = [
        'ombor_id','tovar_id','tovar_nomi','harakat','miqdor',
        'qoldiq_oldin','qoldiq_keyin','tan_narx','manba_tur','manba_id','xodim_id','izoh',
    ];
    public function ombor(): BelongsTo { return $this->belongsTo(Ombor::class,'ombor_id'); }
    public function tovar(): BelongsTo { return $this->belongsTo(TovarKatalog::class,'tovar_id'); }
    public function xodim(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class,'xodim_id'); }
}
