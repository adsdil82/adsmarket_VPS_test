<?php

namespace App\Services\DeviceControl;

use App\Models\Qurilma;
use App\Models\QurilmaLog;
use App\Models\QurilmaProvayder;
use App\Models\QurilmaProvayderUlanish;
use Illuminate\Support\Facades\Auth;

class DeviceControlService
{
    public function __construct(private DeviceControlManager $manager) {}

    /**
     * Qurilmani bloklash.
     * Auto lock o'chiq bo'lsa yoki ruxsat bo'lmasa ishlamaydi.
     */
    public function lock(Qurilma $qurilma, string $provayderKod, string $sabab, bool $autoLock = false): DeviceControlResponse
    {
        // Xavfsizlik shartlari
        if ($autoLock && !config('device_control.auto_lock_enabled', false)) {
            return DeviceControlResponse::xato('Auto lock o\'chiq (DEVICE_CONTROL_AUTO_LOCK_ENABLED=false)', 'AUTO_LOCK_DISABLED');
        }
        if (!$qurilma->canBeLocked()) {
            return DeviceControlResponse::xato("Qurilma holati '{$qurilma->holat}' — bloklash mumkin emas", 'INVALID_STATE');
        }
        if ($qurilma->isReleased() || $qurilma->holat === Qurilma::HOLAT_RETURNED) {
            return DeviceControlResponse::xato('Ozod qilingan yoki qaytarilgan qurilmani bloklash mumkin emas', 'RELEASED');
        }

        $provayder = $this->manager->provayder($provayderKod);
        if (!$provayder) {
            return DeviceControlResponse::xato("Provider '{$provayderKod}' topilmadi", 'PROVIDER_NOT_FOUND');
        }
        if (!$provayder->lockQollab()) {
            return DeviceControlResponse::xato("{$provayder->nomi()} lock qilishni qo'llab-quvvatlamaydi", 'NOT_SUPPORTED');
        }
        if (!$provayder->tayyor()) {
            return DeviceControlResponse::xato("{$provayder->nomi()} sozlanmagan yoki faol emas", 'PROVIDER_NOT_READY');
        }

        $javob = $provayder->lock($qurilma, $sabab);

        $this->logYoz($qurilma, $provayderKod, QurilmaLog::AMAL_LOCKED, $javob, $sabab);

        if ($javob->muvaffaqiyat) {
            $qurilma->update(['holat' => Qurilma::HOLAT_LOCKED]);
        }

        return $javob;
    }

    /**
     * Qurilmani blokdan chiqarish — to'lov tiklansa ustuvor!
     */
    public function unlock(Qurilma $qurilma, string $provayderKod, string $sabab = ''): DeviceControlResponse
    {
        if (!$qurilma->canBeUnlocked()) {
            return DeviceControlResponse::xato("Qurilma holati '{$qurilma->holat}' — blokdan chiqarish mumkin emas", 'INVALID_STATE');
        }

        $provayder = $this->manager->provayder($provayderKod);
        if (!$provayder) {
            return DeviceControlResponse::xato("Provider '{$provayderKod}' topilmadi", 'PROVIDER_NOT_FOUND');
        }
        if (!$provayder->unlockQollab()) {
            return DeviceControlResponse::xato("{$provayder->nomi()} unlock qilishni qo'llab-quvvatlamaydi", 'NOT_SUPPORTED');
        }
        if (!$provayder->tayyor()) {
            return DeviceControlResponse::xato("{$provayder->nomi()} sozlanmagan yoki faol emas", 'PROVIDER_NOT_READY');
        }

        $qurilma->update(['holat' => Qurilma::HOLAT_UNLOCK_PENDING]);
        $javob = $provayder->unlock($qurilma, $sabab);

        $this->logYoz($qurilma, $provayderKod, QurilmaLog::AMAL_UNLOCKED, $javob, $sabab);

        if ($javob->muvaffaqiyat) {
            $qurilma->update(['holat' => Qurilma::HOLAT_ACTIVE]);
        } else {
            $qurilma->update(['holat' => Qurilma::HOLAT_LOCKED]);
        }

        return $javob;
    }

    /**
     * Ogohlantirish yuborish (lock emas)
     */
    public function ogohBerish(Qurilma $qurilma, string $provayderKod, string $xabar = ''): DeviceControlResponse
    {
        $provayder = $this->manager->provayder($provayderKod);
        if (!$provayder || !$provayder->ogohQollab()) {
            return DeviceControlResponse::xato('Provider ogohlantirish qo\'llab-quvvatlamaydi', 'NOT_SUPPORTED');
        }

        $javob = $provayder->ogohBerish($qurilma, $xabar);
        $this->logYoz($qurilma, $provayderKod, QurilmaLog::AMAL_WARNING, $javob, $xabar);

        if ($javob->muvaffaqiyat && !in_array($qurilma->holat, [Qurilma::HOLAT_LOCKED, Qurilma::HOLAT_WARNING])) {
            $qurilma->update(['holat' => Qurilma::HOLAT_WARNING]);
        }

        return $javob;
    }

    /**
     * Shartnoma yopilganda qurilmani ozod qilish
     */
    public function ozodQil(Qurilma $qurilma, string $provayderKod = 'mock'): DeviceControlResponse
    {
        $provayder = $this->manager->provayder($provayderKod);
        $javob = $provayder
            ? $provayder->ozodQil($qurilma)
            : DeviceControlResponse::ok('Qurilma ozod qilindi (provider yo\'q)');

        $this->logYoz($qurilma, $provayderKod, QurilmaLog::AMAL_RELEASED, $javob);
        $qurilma->update(['holat' => Qurilma::HOLAT_RELEASED]);

        return $javob;
    }

    private function logYoz(Qurilma $qurilma, string $provayderKod, string $amal, DeviceControlResponse $javob, string $sabab = ''): void
    {
        $provayderModel = QurilmaProvayder::where('kod', $provayderKod)->first();

        QurilmaLog::create([
            'qurilma_id'   => $qurilma->id,
            'provayder_id' => $provayderModel?->id,
            'reg_kredit_id'=> $qurilma->reg_kredit_id,
            'amal'         => $amal,
            'holat'        => $javob->muvaffaqiyat ? 'muvaffaqiyat' : 'xato',
            'sabab'        => $sabab ?: null,
            'javob'        => json_encode($javob->toArray()),
            'xodim_id'     => Auth::id(),
            'ip_manzil'    => request()->ip(),
        ]);
    }
}
