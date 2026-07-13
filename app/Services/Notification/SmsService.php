<?php
namespace App\Services\Notification;

use App\Models\NotificationLog;
use App\Models\NotificationBatch;
use App\Models\NotificationBatchItem;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Services\Sms\SmsProviderInterface;
use App\Services\Sms\TestSmsProvider;
use App\Services\Sms\EskizSmsProvider;
use App\Services\Sms\PlayMobileSmsProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private SmsProviderInterface $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
    }

    /** Provider yaratish (config asosida) */
    private function resolveProvider(): SmsProviderInterface
    {
        $providerName = NotificationSetting::get('sms', 'provider', 'test_mode');
        $testMode     = NotificationSetting::get('sms', 'test_mode', '1') === '1';

        if ($testMode || $providerName === 'test_mode') {
            return new TestSmsProvider();
        }

        $config = [
            'api_url'   => NotificationSetting::get('sms', 'api_url'),
            'login'     => NotificationSetting::get('sms', 'login'),
            'password'  => NotificationSetting::get('sms', 'password'),
            'sender_id' => NotificationSetting::get('sms', 'sender_id', 'NasiyaPro'),
        ];

        return match($providerName) {
            'eskiz'      => new EskizSmsProvider($config),
            'playmobile' => new PlayMobileSmsProvider($config),
            default      => new TestSmsProvider(),
        };
    }


    /**
     * Bugun shu TELEFON RAQAMga shu shablon (template) allaqachon yuborilganmi
     * tekshirish. Ataylab contract_id/customer_id emas, TELEFON bo'yicha
     * tekshiriladi — shunda bir xil raqam bir nechta mijoz/shartnomaga
     * bog'langan bo'lsa ham, kuniga bir shablon bir marta yuboriladi.
     */
    public function isDuplicateToday(?string $phone, ?int $templateId): bool
    {
        if (!$phone || !$templateId) return false;

        return NotificationLog::where('channel', 'sms')
            ->where('phone', $phone)
            ->where('template_id', $templateId)
            ->whereIn('status', ['sent', 'test'])
            ->whereDate('created_at', today())
            ->exists();
    }

    /** Yakka SMS yuborish */
    public function sendSingle(
        string $phone,
        string $message,
        ?int   $customerId  = null,
        ?int   $contractId  = null,
        ?int   $templateId  = null,
        ?int   $batchId     = null,
        string $recipientType = 'customer'
    ): NotificationLog {
        $phone = $this->normalizePhone($phone);

        if (!$this->isValidPhone($phone)) {
            return $this->logSkipped($phone, $message, $customerId, $contractId, $templateId, $batchId, 'Noto\'g\'ri telefon raqam');
        }

        if ($recipientType !== 'test' && NotificationSetting::get('sms', 'enabled', '1') !== '1') {
            return $this->logSkipped($phone, $message, $customerId, $contractId, $templateId, $batchId, "SMS moduli o'chirilgan (Sozlamalarda yoqilmagan)");
        }

        // Takroriy SMS oldini olish (bugun shu TELEFON raqamga shu shablon allaqachon yuborilganmi —
        // bir xil raqam bir nechta mijoz/shartnomaga bog'liq bo'lsa ham faqat bir marta yuboriladi)
        if ($recipientType !== 'test' && $this->isDuplicateToday($phone, $templateId)) {
            return $this->logSkipped($phone, $message, $customerId, $contractId, $templateId, $batchId, "Bu shablon ushbu telefon raqamiga bugun allaqachon yuborilgan");
        }

        $result = $this->provider->send($phone, $message);

        return NotificationLog::create([
            'channel'             => 'sms',
            'recipient_type'      => $recipientType,
            'customer_id'         => $customerId,
            'contract_id'         => $contractId,
            'phone'               => $phone,
            'template_id'         => $templateId,
            'message'             => $message,
            'status'              => $result['success'] ? ($this->provider->isTestMode() ? 'test' : 'sent') : 'failed',
            'provider'            => $this->provider->getName(),
            'provider_message_id' => $result['message_id'],
            'provider_response'   => json_encode($result['response']),
            'error_message'       => $result['error'],
            'batch_id'            => $batchId,
            'created_by'          => Auth::id(),
            'sent_at'             => $result['success'] ? now() : null,
        ]);
    }

    /** Guruhli SMS yuborish */
    public function sendBatch(NotificationBatch $batch, array $items, NotificationTemplate $template): void
    {
        $batch->update(['status' => 'sending', 'started_at' => now()]);

        $sent = $failed = $skipped = 0;

        foreach ($items as $item) {
            $batchItem = NotificationBatchItem::create([
                'batch_id'    => $batch->id,
                'customer_id' => $item['customer_id'] ?? null,
                'contract_id' => $item['contract_id'] ?? null,
                'phone'       => $item['phone'] ?? '',
                'message'     => $item['message'] ?? '',
                'status'      => 'pending',
            ]);

            $log = $this->sendSingle(
                $item['phone'] ?? '',
                $item['message'] ?? '',
                $item['customer_id'] ?? null,
                $item['contract_id'] ?? null,
                $template->id,
                $batch->id
            );

            $status = $log->status === 'skipped' ? 'skipped' : ($log->status === 'failed' ? 'failed' : 'sent');
            $batchItem->update(['status' => $status, 'error_message' => $log->error_message, 'notification_log_id' => $log->id]);

            match($status) {
                'sent','test' => $sent++,
                'failed'      => $failed++,
                'skipped'     => $skipped++,
                default       => $skipped++,
            };
        }

        $batch->update([
            'status'        => 'completed',
            'total_sent'    => $sent,
            'total_failed'  => $failed,
            'total_skipped' => $skipped,
            'finished_at'   => now(),
        ]);
    }

    /** Provider status tekshiruvi */
    public function getProviderStatus(): array
    {
        return $this->provider->getStatus() + ['provider_name' => $this->provider->getName(), 'test_mode' => $this->provider->isTestMode()];
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 9) return '+998' . $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) return '+' . $digits;
        if (strlen($digits) === 13 && str_starts_with($digits, '+998')) return $phone;
        return '+' . $digits;
    }

    private function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^\+998[0-9]{9}$/', $phone);
    }

    private function logSkipped(string $phone, string $message, ?int $customerId, ?int $contractId, ?int $templateId, ?int $batchId, string $reason): NotificationLog
    {
        return NotificationLog::create([
            'channel'        => 'sms',
            'recipient_type' => 'customer',
            'customer_id'    => $customerId,
            'contract_id'    => $contractId,
            'phone'          => $phone,
            'template_id'    => $templateId,
            'message'        => $message,
            'status'         => 'skipped',
            'error_message'  => $reason,
            'batch_id'       => $batchId,
            'created_by'     => Auth::id(),
        ]);
    }
}