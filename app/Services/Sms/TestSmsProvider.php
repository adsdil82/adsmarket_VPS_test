<?php
namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class TestSmsProvider implements SmsProviderInterface
{
    public function send(string $phone, string $message): array
    {
        $phone = $this->normalizePhone($phone);

        Log::channel('daily')->info('[TEST SMS] Yuborildi', [
            'phone'   => $phone,
            'message' => $message,
            'chars'   => mb_strlen($message),
            'segments'=> $this->segmentCount($message),
        ]);

        return [
            'success'    => true,
            'message_id' => 'TEST-' . date('YmdHis') . '-' . rand(1000, 9999),
            'response'   => ['status' => 'test_mode', 'phone' => $phone],
            'error'      => null,
        ];
    }

    public function getName(): string { return 'test_mode'; }
    public function isTestMode(): bool { return true; }

    public function getStatus(): array {
        return ['provider' => 'test_mode', 'status' => 'ok', 'balance' => 'N/A (test mode)'];
    }

    private function normalizePhone(string $phone): string {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 9) return '+998' . $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) return '+' . $digits;
        return '+' . $digits;
    }

    private function segmentCount(string $msg): int {
        $len = mb_strlen($msg);
        if ($len <= 160) return 1;
        return (int) ceil($len / 153);
    }
}