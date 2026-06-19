<?php
namespace App\Services\Sms;

interface SmsProviderInterface
{
    /**
     * SMS yuborish
     * @return array ['success'=>bool, 'message_id'=>string|null, 'response'=>mixed, 'error'=>string|null]
     */
    public function send(string $phone, string $message): array;

    /** Provider nomi */
    public function getName(): string;

    /** Test mode yoqilganmi */
    public function isTestMode(): bool;

    /** Balans / status (agar API qo'llab-quvvatlasa) */
    public function getStatus(): array;
}