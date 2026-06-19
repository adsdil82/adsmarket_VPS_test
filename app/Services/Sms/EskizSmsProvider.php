<?php
namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * Eskiz.uz SMS Provider
 * Docs: https://eskiz.uz/
 */
class EskizSmsProvider implements SmsProviderInterface
{
    private string $apiUrl   = 'https://notify.eskiz.uz/api';
    private string $login;
    private string $password;
    private string $senderId;
    private ?string $token = null;

    public function __construct(array $config)
    {
        $this->apiUrl   = rtrim($config['api_url'] ?? 'https://notify.eskiz.uz/api', '/');
        $this->login    = $config['login'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->senderId = $config['sender_id'] ?? '4546';
    }

    public function send(string $phone, string $message): array
    {
        try {
            $token = $this->getToken();
            if (!$token) return ['success'=>false,'message_id'=>null,'response'=>null,'error'=>'Token olishda xato'];

            $phone   = $this->normalizePhone($phone);
            $response = Http::timeout(15)->withToken($token)->post($this->apiUrl . '/message/sms/send', [
                'mobile_phone' => $phone,
                'message'      => $message,
                'from'         => $this->senderId,
                'callback_url' => '',
            ]);

            $body = $response->json();
            if ($response->successful() && ($body['status'] ?? '') === 'waiting') {
                return ['success'=>true,'message_id'=>$body['id']??null,'response'=>$body,'error'=>null];
            }
            return ['success'=>false,'message_id'=>null,'response'=>$body,'error'=>$body['message']??'Noma\'lum xato'];

        } catch (\Exception $e) {
            return ['success'=>false,'message_id'=>null,'response'=>null,'error'=>$e->getMessage()];
        }
    }

    public function getName(): string { return 'eskiz'; }
    public function isTestMode(): bool { return false; }

    public function getStatus(): array
    {
        try {
            $token = $this->getToken();
            if (!$token) return ['status'=>'error','error'=>'Token olishda xato'];
            $response = Http::timeout(10)->withToken($token)->get($this->apiUrl . '/auth/user');
            return $response->json() + ['provider'=>'eskiz'];
        } catch (\Exception $e) {
            return ['status'=>'error','error'=>$e->getMessage()];
        }
    }

    private function getToken(): ?string
    {
        if ($this->token) return $this->token;

        return Cache::remember('eskiz_token', 86000, function() {
            $response = Http::timeout(10)->post($this->apiUrl . '/auth/login', [
                'email'    => $this->login,
                'password' => $this->password,
            ]);
            return $response->json()['data']['token'] ?? null;
        });
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 9) return '998' . $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) return $digits;
        return $digits;
    }
}