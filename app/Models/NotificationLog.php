<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $table    = 'notification_logs';
    protected $fillable = [
        'channel','recipient_type','customer_id','contract_id',
        'phone','email','telegram_chat_id','template_id',
        'subject','message','status','provider',
        'provider_message_id','provider_response','error_message',
        'batch_id','created_by','sent_at',
    ];
    protected $casts = ['sent_at'=>'datetime'];

    public function customer(): BelongsTo  { return $this->belongsTo(Mijoz::class, 'customer_id'); }
    public function contract(): BelongsTo  { return $this->belongsTo(RegKredit::class, 'contract_id'); }
    public function template(): BelongsTo  { return $this->belongsTo(NotificationTemplate::class, 'template_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'created_by'); }
    public function batch(): BelongsTo     { return $this->belongsTo(NotificationBatch::class, 'batch_id'); }

    public function getStatusRangiAttribute(): string {
        return match($this->status) {
            'sent'    => 'success',
            'failed'  => 'danger',
            'skipped' => 'warning',
            'test'    => 'info',
            'pending' => 'secondary',
            default   => 'secondary',
        };
    }
}