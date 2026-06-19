<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationBatch extends Model
{
    protected $table    = 'notification_batches';
    protected $fillable = [
        'channel','type','title','filters_json',
        'total_recipients','total_sent','total_failed','total_skipped',
        'status','created_by','started_at','finished_at',
    ];
    protected $casts = [
        'filters_json' => 'array',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
    ];

    public function items(): HasMany    { return $this->hasMany(NotificationBatchItem::class, 'batch_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(Foydalanuvchi::class, 'created_by'); }

    public function getStatusRangiAttribute(): string {
        return match($this->status) {
            'completed' => 'success', 'failed'    => 'danger',
            'sending'   => 'warning', 'cancelled' => 'secondary',
            'previewed' => 'info',    'draft'     => 'light',
            default     => 'secondary',
        };
    }
}