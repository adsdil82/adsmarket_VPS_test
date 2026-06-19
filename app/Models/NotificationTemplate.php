<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    protected $table    = 'notification_templates';
    protected $fillable = ['channel','code','name','subject','body','is_active','is_default','created_by','updated_by'];
    protected $casts    = ['is_active'=>'boolean','is_default'=>'boolean'];

    /** O'zgaruvchilarni real qiymatlar bilan almashtirish */
    public function render(array $vars): string
    {
        $body = $this->body;
        foreach ($vars as $key => $val) {
            $body = str_replace('{'.$key.'}', $val ?? '', $body);
        }
        return $body;
    }

    /** Template da mavjud o'zgaruvchilar ro'yxati */
    public function getVariablesAttribute(): array
    {
        preg_match_all('/\{([a-z_]+)\}/', $this->body, $matches);
        return $matches[1] ?? [];
    }

    public function scopeFaol($q) { return $q->where('is_active', true); }
    public function scopeChannel($q, string $channel) { return $q->where('channel', $channel); }

    public function createdBy(): BelongsTo {
        return $this->belongsTo(Foydalanuvchi::class, 'created_by');
    }

    /** Barcha o'zgaruvchi nomlari (tavsiflar bilan) */
    public static function variables(): array {
        return [
            'client_name'       => 'Mijoz F.I.O.',
            'contract_number'   => 'Shartnoma raqami',
            'branch_name'       => 'Filial nomi',
            'payment_date'      => "To'lov sanasi",
            'monthly_payment'   => "Oylik to'lov summasi",
            'overdue_days'      => 'Kechikkan kunlar soni',
            'overdue_amount'    => 'Kechikkan qarz summasi',
            'total_debt'        => 'Umumiy qarz',
            'paid_amount'       => "To'langan summa",
            'remaining_amount'  => 'Qoldiq summa',
            'company_name'      => 'Kompaniya nomi',
            'manager_phone'     => 'Menejer telefoni',
        ];
    }
}