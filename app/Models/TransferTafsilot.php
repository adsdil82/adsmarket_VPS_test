<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TransferTafsilot extends Model
{
    public    $timestamps = false;
    protected $table      = 'transfer_tafsilot';
    protected $fillable   = ['transfer_id','tovar_id','miqdor','narx'];

    public function tovar()    { return $this->belongsTo(TovarKatalog::class, 'tovar_id'); }
    public function transfer() { return $this->belongsTo(FilialTransfer::class, 'transfer_id'); }
}
