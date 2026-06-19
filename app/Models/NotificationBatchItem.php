<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBatchItem extends Model
{
    protected $table    = 'notification_batch_items';
    protected $fillable = [
        'batch_id','customer_id','contract_id','phone',
        'message','status','error_message','notification_log_id',
    ];

    public function batch(): BelongsTo    { return $this->belongsTo(NotificationBatch::class, 'batch_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Mijoz::class, 'customer_id'); }
    public function contract(): BelongsTo { return $this->belongsTo(RegKredit::class, 'contract_id'); }
    public function log(): BelongsTo      { return $this->belongsTo(NotificationLog::class, 'notification_log_id'); }
}