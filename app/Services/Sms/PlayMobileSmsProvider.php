<?php
namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;

/**
 * PlayMobile SMS Provider skeleton
 * TODO: Real API credentials bilan to'ldirish
 */
class PlayMobileSmsProvider implements SmsProviderInterface
{
    private array $config;

    public function __construct(array $config) { $this->config = $config; }

    public function send(string $phone, string $message): array
    {
        // TODO: PlayMobile API integration
        return ['success'=>false,'message_id'=>null,'response'=>null,'error'=>'PlayMobile provider not configured yet.'];
    }

    public function getName(): string { return 'playmobile'; }
    public function isTestMode(): bool { return false; }
    public function getStatus(): array { return ['status'=>'not_configured','provider'=>'playmobile']; }
}