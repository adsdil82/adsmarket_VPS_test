<?php

namespace App\Services;

use App\Models\AutopayKarta;
use App\Models\AutopayTranzaksiya;
use App\Models\Foydalanuvchi;
use App\Models\NotificationSetting;
use App\Models\RegKredit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AutoPay (autopaygroup.uz) integratsiyasi — UniAccess Web Service (v4).
 *
 * Bu servis mijozning kartasidan TO'G'RIDAN-TO'G'RI pul yechmaydi — AutoPay'ning
 * o'zi shartnoma (contract) `auto=true` bilan ro'yxatdan o'tkazilgach, kartani
 * kuzatib, mablag' paydo bo'lganda avtomatik yechadi. Bizning vazifamiz: qarzni
 * ro'yxatdan o'tkazish/yangilash, auto-rejimni yoqish/o'chirish va natijalarni
 * (tranzaksiyalarni) kuzatish.
 *
 * MUHIM: rasmiy hujjatga ko'ra AutoPay barcha summalarni TIYIN da qabul qiladi
 * va qaytaradi (1 so'm = 100 tiyin). Shu sabab bu yerda so'm<->tiyin
 * konvertatsiyasi doim aniq amalga oshiriladi.
 */
class AutoPayService
{
    private string $apiUrl = 'https://autopaygroup.uz/api/v1/partners';

    /** Rasmiy hujjatga ko'ra tranzaksiya statusi faqat shu ikkitasidan biri bo'ladi. */
    public const HOLAT_MUVAFFAQIYATLI = 'success';
    public const HOLAT_BEKOR_QILINGAN = 'cancelled';

    public function yoqilganmi(): bool
    {
        return NotificationSetting::get('autopay', 'yoqilgan', '0') === '1';
    }

    /**
     * Pullik xizmatlar (Scoring/Monitoring/Processing/E-GOV) — har biri admin
     * sozlamalarida alohida yoqilishi kerak, chunki har biri AutoPay'ning
     * oylik hisobiga qo'shimcha xarajat qiladi.
     */
    public function pullikYoqilganmi(string $xizmat): bool
    {
        return NotificationSetting::get('autopay', "{$xizmat}_yoqilgan", '0') === '1';
    }

    /**
     * Avtomatik (cron/webhook orqali) qabul qilingan to'lovlarni "xodim_id"
     * sifatida biriktirish uchun tizim foydalanuvchisi — login qila olmaydi
     * (holat=nofaol), faqat audit/hisobot maqsadida ishlatiladi.
     */
    public static function systemXodim(): Foydalanuvchi
    {
        return Foydalanuvchi::firstOrCreate(
            ['email' => 'autopay@system.local'],
            [
                'ism_familiya' => 'AutoPay (tizim)',
                'password'     => Str::random(40),
                'rol'          => 'admin',
                'holat'        => 'nofaol',
                'til'          => 'uz',
            ]
        );
    }

    private function token(): string
    {
        return NotificationSetting::get('autopay', 'token');
    }

    private function merchantId(): ?int
    {
        $id = NotificationSetting::get('autopay', 'merchant_id');
        return $id !== '' ? (int) $id : null;
    }

    /** Webhook/verification so'rovlarini tasdiqlash uchun biz o'zimiz generatsiya qilgan token. */
    public function webhookToken(): string
    {
        $token = NotificationSetting::get('autopay', 'webhook_token');
        if (!$token) {
            $token = Str::random(48);
            NotificationSetting::setChannel('autopay', ['webhook_token' => $token]);
        }
        return $token;
    }

    /**
     * AutoPay'ga so'rov yuborish. Har doim ['success'=>bool, 'result'=>array|null,
     * 'error'=>string|null, 'raw'=>array|null] qaytaradi — hech qachon exception
     * otmaydi (chaqiruvchi tomon xatoni tekshirib, DB'ga yozadi).
     */
    private function sorov(string $method, array $params, int $timeout = 20): array
    {
        if (!$this->yoqilganmi()) {
            return ['success' => false, 'result' => null, 'error' => "AutoPay o'chirilgan (sozlamalarda yoqilmagan)", 'raw' => null];
        }

        try {
            $response = Http::timeout($timeout)
                ->withToken($this->token())
                ->post($this->apiUrl, ['method' => $method, 'params' => $params]);

            $body = $response->json();

            if (!$response->successful()) {
                return ['success' => false, 'result' => null, 'error' => "HTTP {$response->status()}", 'raw' => $body];
            }
            if (($body['status'] ?? false) !== true) {
                return ['success' => false, 'result' => null, 'error' => $body['error']['message'] ?? "Noma'lum API xatosi", 'raw' => $body];
            }

            return ['success' => true, 'result' => $body['result'] ?? null, 'error' => null, 'raw' => $body];
        } catch (\Exception $e) {
            Log::error('AutoPay so\'rov xatosi', ['method' => $method, 'xato' => $e->getMessage()]);
            return ['success' => false, 'result' => null, 'error' => $e->getMessage(), 'raw' => null];
        }
    }

    /**
     * Mijoz + shartnomani bir so'rovda AutoPay'ga yuborish (agar mijoz mavjud
     * bo'lsa — faqat shartnoma yaratiladi).
     */
    public function shartnomaYuborish(RegKredit $kredit, string $loanId): array
    {
        $mijoz = $kredit->mijoz;

        return $this->sorov('contract.create.wc.single', [
            'merchant_id'  => $this->merchantId(),
            'pinfl'        => $mijoz->pinfl,
            'loan_id'      => $loanId,
            'debt'         => $this->somDanTiyinga((float) $kredit->qoldiq_qarz),
            'ext'          => $loanId,
            'account'      => $kredit->shartnoma_raqam,
            'info'         => "NasiyaPro shartnoma {$kredit->shartnoma_raqam}",
            'auto'         => true,
            'passport'     => trim(($mijoz->passport_seriya ?? '') . ($mijoz->passport_raqam ?? '')),
            'first_name'   => $mijoz->ism,
            'last_name'    => $mijoz->familiya,
            'middle_name'  => $mijoz->otasining_ismi,
        ]);
    }

    /** Qarz summasi o'zgarganda (masalan qisman to'lov kirgan bo'lsa) AutoPay'dagi qarzni yangilash. */
    public function shartnomaYangilash(string $loanId, float $debtSom, ?string $info = null): array
    {
        return $this->sorov('contract.update', [
            'loan_id' => $loanId,
            'data'    => array_filter([
                'debt' => $this->somDanTiyinga($debtSom),
                'info' => $info,
            ], fn($v) => $v !== null),
        ]);
    }

    /** Avtomatik yechish rejimini yoqish/o'chirish (masalan mijoz to'liq to'laganda o'chirish uchun). */
    public function avtoToggle(string $loanId, bool $yoqish): array
    {
        return $this->sorov('contract.auto.toggle', [
            'auto'     => $yoqish,
            'loan_ids' => [$loanId],
        ]);
    }

    public function shartnomaOchirish(string $pinfl, string $loanId): array
    {
        return $this->sorov('contract.delete', ['pinfl' => $pinfl, 'loan_id' => $loanId]);
    }

    /**
     * Bir nechta shartnomani (yangi yoki mavjud) BITTA so'rovda AutoPay'ga
     * yuborish/yangilash (1000 tagacha) — checkbox orqali ko'p tanlab
     * yuborishda ketma-ket ko'p so'rov o'rniga shu ishlatiladi. Har bir
     * elementda: pinfl, passport, first_name, last_name, middle_name,
     * loan_id, debt(so'm), ext, account, info, auto — merchant_id avtomatik
     * qo'shiladi.
     */
    public function shartnomalarniOmmaviyYuborish(array $kontraktlar): array
    {
        $merchantId = $this->merchantId();
        foreach ($kontraktlar as &$k) {
            $k['merchant_id'] = $merchantId;
            if (isset($k['debt'])) {
                $k['debt'] = $this->somDanTiyinga((float) $k['debt']);
            }
        }
        unset($k);

        return $this->sorov('contract.createOrUpdate', ['contracts' => $kontraktlar]);
    }

    /**
     * Bir nechta shartnomaning qarzini BITTA so'rovda yangilash (250 tagacha).
     * Har bir element: ['loan_id' => ..., 'debt_som' => ...].
     */
    public function qarzlarniOmmaviyYangilash(array $shartnomalar): array
    {
        $updates = array_map(fn($s) => [
            'loan_id' => $s['loan_id'],
            'data'    => ['debt' => $this->somDanTiyinga((float) $s['debt_som'])],
        ], $shartnomalar);

        return $this->sorov('contract.bulk.update', ['updates' => $updates]);
    }

    /** Bir nechta shartnomani BITTA so'rovda o'chirish (250 tagacha). */
    public function shartnomalarniOmmaviyOchirish(array $loanIds): array
    {
        return $this->sorov('contract.bulk.delete', ['loan_ids' => $loanIds]);
    }

    /** Bitta shartnomaning AutoPay tarafidagi joriy holatini (qarz, to'langan summa) ko'rish. */
    public function shartnomaTop(string $loanId): array
    {
        $natija = $this->sorov('contract.find', ['loan_id' => $loanId]);
        if ($natija['success'] && $natija['result']) {
            $natija['result']['total_debt_som']   = $this->tiyinDanSomga($natija['result']['total_debt'] ?? 0);
            $natija['result']['current_debt_som'] = $this->tiyinDanSomga($natija['result']['current_debt'] ?? 0);
            $natija['result']['paid_amount_som']  = $this->tiyinDanSomga($natija['result']['paid_amount'] ?? 0);
        }
        return $natija;
    }

    /**
     * Bitta shartnoma bo'yicha tranzaksiyalarni olish (sahifalash bilan).
     * MUHIM: loan_id bilan birga filtrlanadi — aks holda mijozning boshqa
     * (masalan eski) shartnomalariga tegishli tranzaksiyalar ham qaytib,
     * noto'g'ri shartnomaga yozilib qolishi mumkin (bir xil pinfl bo'yicha).
     */
    public function tranzaksiyalarniOl(string $pinfl, string $loanId, int $pageSize = 50, int $pageNumber = 1): array
    {
        return $this->sorov('transaction.get', [
            'page_size'   => $pageSize,
            'page_number' => $pageNumber,
            'search'      => ['pinfl' => $pinfl, 'loan_id' => $loanId],
        ]);
    }

    /**
     * AutoPay hisobidagi barcha shartnomalarni (sahifalab) olish — bizning
     * tizim yaratmagan yoki bizning bazamizda holati eskirgan shartnomalarni
     * ham aniqlash (sinxronlash) uchun ishlatiladi.
     */
    public function barchaShartnomalarniOl(int $pageSize = 100, int $pageNumber = 1): array
    {
        return $this->sorov('contract.get', [
            'page_size'   => $pageSize,
            'page_number' => $pageNumber,
        ]);
    }

    /**
     * AutoPay hisobidagi BARCHA tranzaksiyalarni (sahifalab, hech qanday
     * pinfl/loan_id filtrisiz) olish — to'liq sinxronlash uchun ishlatiladi.
     */
    public function barchaTranzaksiyalarniOl(int $pageSize = 100, int $pageNumber = 1): array
    {
        return $this->sorov('transaction.get', [
            'page_size'   => $pageSize,
            'page_number' => $pageNumber,
        ]);
    }

    /**
     * Faqat bitta sana bo'yicha tranzaksiyalarni olish — davr bo'yicha
     * (bugun/shu oy/o'tgan oy) tezkor sinxronlash uchun ishlatiladi, chunki
     * AutoPay API sana oralig'ini emas, faqat aniq bitta sanani qabul qiladi.
     */
    public function tranzaksiyalarSanaBoyicha(string $sana, int $pageSize = 100, int $pageNumber = 1): array
    {
        return $this->sorov('transaction.get', [
            'page_size'   => $pageSize,
            'page_number' => $pageNumber,
            'search'      => ['date' => $sana],
        ]);
    }

    /** Mijozning Uzcard/Humo kartalari haqida ma'lumot. */
    public function kartaMalumoti(string $pinfl): array
    {
        return $this->sorov('card.info', ['pinfl' => $pinfl]);
    }

    /**
     * card.info natijasini (uzcard/humo massivlari) mijoz darajasidagi
     * autopay_kartalar jadvaliga saqlaydi/yangilaydi (uuid bo'yicha). Karta
     * PINFL'ga (mijozga) tegishli, biror shartnomaga emas — shuning uchun
     * mijoz yangi shartnoma ochsa ham qayta ro'yxatga olish shart bo'lmaydi.
     */
    public function kartalarniSaqla(int $mijozId, array $cardInfoResult): void
    {
        $uzcard = collect($cardInfoResult['uzcard']['data'] ?? [])->map(fn($k) => $k + ['turi' => 'uzcard']);
        $humo   = collect($cardInfoResult['humo']['data'] ?? [])->map(fn($k) => $k + ['turi' => 'humo']);

        foreach ($uzcard->merge($humo) as $k) {
            if (empty($k['uuid'])) {
                continue;
            }
            AutopayKarta::updateOrCreate(
                ['uuid' => $k['uuid']],
                [
                    'mijoz_id'     => $mijozId,
                    'pan'          => $k['pan'] ?? $k['card_pan'] ?? null,
                    'turi'         => $k['turi'],
                    'egasi'        => $k['owner'] ?? $k['card_owner'] ?? null,
                    'telefon'      => $k['phone'] ?? $k['card_phone'] ?? null,
                    'auto'         => (bool) ($k['auto'] ?? false),
                    'is_verified'  => (bool) ($k['is_verified'] ?? false),
                    'is_blocked'   => (bool) ($k['is_blocked'] ?? false),
                    'block_reason' => $k['block_reason'] ?? null,
                ]
            );
        }
    }

    /**
     * PULLIK — Uzcard processing tizimi orqali PINFL bo'yicha kartalarni
     * qidirish (30 tagacha PINFL bitta so'rovda, biz bitta mijoz uchun ishlatamiz).
     */
    public function processingUzcardQidir(array $pinfllar): array
    {
        if (!$this->pullikYoqilganmi('processing')) {
            return ['success' => false, 'result' => null, 'error' => "Processing xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('processing.uzcard.search', ['pinfl' => $pinfllar]);
    }

    /** PULLIK — Humo processing tizimi orqali PINFL bo'yicha kartalarni qidirish. */
    public function processingHumoQidir(array $pinfllar): array
    {
        if (!$this->pullikYoqilganmi('processing')) {
            return ['success' => false, 'result' => null, 'error' => "Processing xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('processing.humo.search', ['pinfl' => $pinfllar]);
    }

    /**
     * Bitta mijoz (PINFL) uchun Uzcard va Humo qidiruvlarini birga bajarib,
     * natijalarni bitta qulay ko'rinishga birlashtiradi.
     */
    public function processingQidiruv(string $pinfl): array
    {
        if (!$this->pullikYoqilganmi('processing')) {
            return ['success' => false, 'cards' => collect(), 'fails_key' => null, 'error' => "Processing xizmati admin sozlamalarida yoqilmagan."];
        }

        $uz   = $this->processingKartalarniAjrat($this->processingUzcardQidir([$pinfl]), $pinfl, 'uzcard');
        $humo = $this->processingKartalarniAjrat($this->processingHumoQidir([$pinfl]), $pinfl, 'humo');

        return [
            'success'   => true,
            'cards'     => $uz['cards']->merge($humo['cards']),
            'fails_key' => $uz['fails_key'] ?? $humo['fails_key'],
            'error'     => $uz['xato'] ?? $humo['xato'] ?? null,
        ];
    }

    /** processing.uzcard.search / processing.humo.search javobini {pinfl: {...}} yoki {ok:[...], fails_key} shaklidan kartalar ro'yxatiga aylantiradi. */
    private function processingKartalarniAjrat(array $sorovNatija, string $pinfl, string $turi): array
    {
        if (!($sorovNatija['success'] ?? false)) {
            return ['cards' => collect(), 'fails_key' => null, 'xato' => $sorovNatija['error'] ?? null];
        }

        $result = $sorovNatija['result'] ?? [];
        if (isset($result['ok'])) {
            $data     = $result['ok'][0] ?? [];
            $failsKey = $result['fails_key'] ?? null;
        } else {
            $data     = $result;
            $failsKey = null;
        }

        $mos   = $data[$pinfl] ?? (is_array($data) ? reset($data) : null) ?: null;
        $cards = collect($mos['cards'] ?? [])->map(fn($k) => $k + ['turi' => $turi]);

        return ['cards' => $cards, 'fails_key' => $failsKey, 'xato' => null];
    }

    /** PULLIK — fails_key orqali muvaffaqiyatsiz qidiruv so'rovlarini qayta tekshirish. */
    public function processingQaytaTekshirish(string $key): array
    {
        if (!$this->pullikYoqilganmi('processing')) {
            return ['success' => false, 'result' => null, 'error' => "Processing xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('processing.check', ['key' => $key]);
    }

    /** PULLIK — oldingi processing qidiruvlari tarixi (sahifalab, ixtiyoriy filtrlar bilan). */
    public function processingTarixi(int $pageNumber = 1, int $pageSize = 30, array $qidiruv = []): array
    {
        if (!$this->pullikYoqilganmi('processing')) {
            return ['success' => false, 'result' => null, 'error' => "Processing xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('processing.get.history', array_filter([
            'page_size'   => $pageSize,
            'page_number' => $pageNumber,
            'search'      => array_filter($qidiruv) ?: null,
        ]));
    }

    /**
     * PULLIK — Monitoring uchun kartani OTP orqali ro'yxatga olish (mijozga/pinfl'ga
     * bog'lanmagan, xom karta raqami bilan) — monitoring.humo/monitoring.uzcard
     * chaqirishdan oldin karta egasining roziligini tasdiqlash uchun kerak.
     * Diqqat: 3 marta noto'g'ri urinish kartani Uzcard'da 4 soatga, Humo'da 24 soatga bloklaydi.
     */
    public function monitoringKartaRoyxatgaOlish(string $cardNumber, string $expire, string $type, ?string $phone = null): array
    {
        if (!$this->pullikYoqilganmi('monitoring')) {
            return ['success' => false, 'result' => null, 'error' => "Monitoring xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        $params = ['card_number' => $cardNumber, 'expire' => $expire, 'type' => $type];
        if ($type === 'humo') {
            $params['phone'] = $phone;
        }

        // SMS-gateway orqali OTP yuborish AutoPay tomonida 20s'dan uzoqroq
        // cho'zilishi mumkin — bunda javob kechiksa ham SMS baribir jo'natilib
        // bo'lgan bo'ladi, shuning uchun uzunroq timeout beriladi.
        return $this->sorov('card.register', $params, 45);
    }

    /** PULLIK — Monitoring kartasini OTP kod bilan tasdiqlash. */
    public function monitoringKartaTasdiqlash(string $ext, string $otpCode, string $type): array
    {
        if (!$this->pullikYoqilganmi('monitoring')) {
            return ['success' => false, 'result' => null, 'error' => "Monitoring xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('card.verify', ['ext' => $ext, 'otp_code' => $otpCode, 'type' => $type], 45);
    }

    /** PULLIK — Humo kartaning berilgan sana oralig'idagi tranzaksiya tarixi. */
    public function monitoringHumo(string $cardNumber, string $dateFrom, ?string $dateTo = null): array
    {
        if (!$this->pullikYoqilganmi('monitoring')) {
            return ['success' => false, 'result' => null, 'error' => "Monitoring xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('monitoring.humo', array_filter([
            'card_number' => $cardNumber,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
        ]));
    }

    /** PULLIK — Uzcard kartaning berilgan sana oralig'idagi tranzaksiya tarixi (sahifalab). */
    public function monitoringUzcard(string $cardNumber, string $dateFrom, ?string $dateTo = null, int $pageNumber = 0): array
    {
        if (!$this->pullikYoqilganmi('monitoring')) {
            return ['success' => false, 'result' => null, 'error' => "Monitoring xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('monitoring.uzcard', array_filter([
            'card_number' => $cardNumber,
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'page_number' => $pageNumber,
        ], fn($v) => $v !== null));
    }

    /**
     * Yangi mijoz uchun kartani auto-yechish uchun ro'yxatga olish — mijozning
     * kartasiga (Humo) yoki ro'yxatdan o'tgan raqamiga (Uzcard) OTP yuboradi.
     * OTP 2 daqiqa amal qiladi, tasdiqlash uchun kartaniTasdiqlash() ishlatiladi.
     */
    public function kartaniRoyxatgaOlish(string $pinfl, string $cardNumber, string $expire, string $type, ?string $phone = null): array
    {
        $params = [
            'pinfl'       => $pinfl,
            'card_number' => $cardNumber,
            'expire'      => $expire,
            'type'        => $type,
        ];
        if ($type === 'humo') {
            $params['phone'] = $phone;
        }

        // SMS-gateway orqali OTP yuborish AutoPay tomonida 20s'dan uzoqroq
        // cho'zilishi mumkin — bunda javob kechiksa ham SMS baribir jo'natilib
        // bo'lgan bo'ladi, shuning uchun uzunroq timeout beriladi.
        return $this->sorov('card.loan.register', $params, 45);
    }

    /** Mijoz aytgan OTP kodni tasdiqlab, kartani auto-yechish uchun faollashtirish. */
    public function kartaniTasdiqlash(string $pinfl, string $ext, string $otpCode, string $type): array
    {
        return $this->sorov('card.loan.verify', [
            'pinfl'    => $pinfl,
            'ext'      => $ext,
            'otp_code' => $otpCode,
            'type'     => $type,
        ], 45);
    }

    /**
     * PULLIK — bitta PINFL bo'yicha barcha tashkilotlar kesimida to'liq
     * scoring ma'lumoti: shaxsiy ma'lumot, shartnomalar, kartalar soni,
     * so'nggi 6 oylik tranzaksiyalar. Admin sozlamalarida "Scoring" alohida
     * yoqilgan bo'lishi kerak (har bir so'rov AutoPay hisobiga xarajat qiladi).
     */
    public function scoringAutopayPinfl(string $pinfl): array
    {
        if (!$this->pullikYoqilganmi('scoring')) {
            return ['success' => false, 'result' => null, 'error' => "Scoring xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('scoring.autopay.pinfl', ['pinfl' => $pinfl]);
    }

    /** BEPUL — mavjud E-GOV xizmatlari ro'yxati (sahifalab). */
    public function egovXizmatlar(int $pageSize = 500, int $pageNumber = 1): array
    {
        return $this->sorov('egov.services', [
            'page_size'   => $pageSize,
            'page_number' => $pageNumber,
            'search'      => [],
        ]);
    }

    /**
     * PULLIK — yangi "fuqaro" ro'yxatdan o'tkazish: barcha E-GOV xizmatlariga
     * so'rov yuboriladi va javoblar AutoPay tarafida saqlanadi. Admin
     * sozlamalarida "E-GOV" alohida yoqilgan bo'lishi kerak.
     */
    public function egovSaqlash(string $pinfl, string $passport): array
    {
        if (!$this->pullikYoqilganmi('egov')) {
            return ['success' => false, 'result' => null, 'error' => "E-GOV xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('egov.store', ['pinfl' => $pinfl, 'passport' => $passport]);
    }

    /** BEPUL — avval saqlangan xizmat ma'lumotini PINFL+service_id bo'yicha olish. */
    public function egovOlish(string $pinfl, int $serviceId): array
    {
        return $this->sorov('egov.get', ['pinfl' => $pinfl, 'service_id' => $serviceId]);
    }

    /**
     * PULLIK — mavjud fuqaroning barcha E-GOV xizmatlaridagi ma'lumotini
     * qayta so'rab, yangilaydi. Admin sozlamalarida "E-GOV" yoqilgan bo'lishi kerak.
     */
    public function egovYangilash(string $pinfl, string $passport): array
    {
        if (!$this->pullikYoqilganmi('egov')) {
            return ['success' => false, 'result' => null, 'error' => "E-GOV xizmati admin sozlamalarida yoqilmagan.", 'raw' => null];
        }

        return $this->sorov('egov.update', ['pinfl' => $pinfl, 'passport' => $passport]);
    }

    /**
     * Bizning tomonimizdan "qabul qildim, ishladim" deb belgilash — AutoPay
     * shu tranzaksiyani keyingi safar `is_synced:false` bilan qidirsak
     * qaytarmaydi.
     */
    public function tranzaksiyaniSinxronlash(string $extId): array
    {
        return $this->sorov('transaction.synchronize', [
            'is_synced'    => true,
            'transactions' => [$extId],
        ]);
    }

    /**
     * Muvaffaqiyatli tranzaksiyani bekor qilish (masalan mijoz noto'g'ri
     * yechilgan deb da'vo qilsa). Diqqat: 3 oydan eski tranzaksiyani AutoPay
     * bekor qilishga ruxsat bermaydi.
     */
    public function tranzaksiyaniBekorQil(string $extId): array
    {
        return $this->sorov('transaction.cancel', ['ext' => $extId]);
    }

    /** Post-payment webhook ulanishini sozlash — AutoPay to'lovdan keyin shu manzilga xabar yuboradi. */
    public function webhookSozla(string $host, bool $status = true): array
    {
        return $this->sorov('webhook.set', [
            'host'   => $host,
            'token'  => $this->webhookToken(),
            'status' => $status,
        ]);
    }

    public function webhookHolati(): array
    {
        return $this->sorov('webhook.check', []);
    }

    /**
     * To'lovdan OLDIN tasdiqlash (prepayment verification) ulanishini sozlash —
     * AutoPay har bir yechishdan oldin shu manzildan joriy qarzni so'raydi.
     * Shu orqali qarz doim biz tomonidan aniqlanadi, alohida contract.update
     * chaqirish shart bo'lmay qoladi.
     */
    public function verificationSozla(string $host, int $delaySekund = 10, bool $status = true): array
    {
        return $this->sorov('transaction.verification.set', [
            'host'   => $host,
            'token'  => $this->webhookToken(),
            'status' => $status,
            'delay'  => $delaySekund,
        ]);
    }

    public function verificationHolati(): array
    {
        return $this->sorov('transaction.verification.check', []);
    }

    /**
     * Bitta AutoPay tranzaksiyasini (webhook orqali kelgan yoki transaction.get
     * javobidan olingan) tizimga qayta ishlash: takrorlanishni oldini olish
     * (ext_id bo'yicha), muvaffaqiyatli bo'lsa TulovService orqali to'lov
     * yozish. Ikkalasi (AutoPaySync command va webhook controller) shu bitta
     * metoddan foydalanadi — mantiq ikki joyda yozilmasin.
     *
     * @return AutopayTranzaksiya|null  null — allaqachon qayd qilingan yoki ext topilmagan
     */
    public function tranzaksiyaniQayta(
        \App\Models\AutopayShartnoma $shartnoma,
        array $tranz,
        TulovService $tulovService,
        bool $dryRun = false
    ): ?AutopayTranzaksiya {
        $extId = $tranz['ext'] ?? $tranz['rrn'] ?? null;
        if (!$extId) {
            return null;
        }
        if (AutopayTranzaksiya::where('ext_id', $extId)->exists()) {
            return null;
        }

        $holat      = strtolower((string) ($tranz['status'] ?? ''));
        $summaSom   = $this->tiyinDanSomga((float) ($tranz['amount'] ?? 0));
        $muvaffaqiyatli = $holat === self::HOLAT_MUVAFFAQIYATLI && $summaSom > 0;

        $yozuv = new AutopayTranzaksiya([
            'autopay_shartnoma_id' => $shartnoma->id,
            'ext_id'               => $extId,
            'rrn'                  => $tranz['rrn'] ?? null,
            'summa'                => $summaSom,
            'holat'                => $holat,
            'sana'                 => $tranz['date'] ?? now(),
            'karta_pan'            => $tranz['card']['pan'] ?? null,
            'karta_token'          => $tranz['card']['token'] ?? null,
            'karta_egasi'          => $tranz['card']['owner'] ?? null,
            'xom_javob'            => $tranz,
        ]);

        if ($dryRun) {
            return $yozuv;
        }

        // Shartnoma hali bizning kredit tizimimizga biriktirilmagan bo'lsa (masalan
        // AutoPay kabinetida qo'lda yaratilgan, hali "Shartnomaga biriktirish"
        // bosilmagan) — to'lov yozmaymiz, faqat tranzaksiya tarixini saqlaymiz.
        if ($muvaffaqiyatli && $shartnoma->kredit) {
            $tulov = $tulovService->tulovQabul($shartnoma->kredit, [
                'tulov_turi_id' => 143, // "AutoPay" to'lov turi
                'summa'         => $summaSom,
                'tolov_sana'    => now()->toDateString(),
                'izoh'          => "AutoPay orqali avtomatik yechildi (ext: {$extId})",
                'xodim_id'      => self::systemXodim()->id,
            ]);
            $yozuv->tulov_id = $tulov->id;
            $muvaffaqiyatliTolov = true;
        } else {
            $muvaffaqiyatliTolov = false;
        }

        $yozuv->save();

        if ($muvaffaqiyatliTolov) {
            // AutoPay tarafida ham "sinxronlandi" deb belgilaymiz (ixtiyoriy, xato bo'lsa e'tiborsiz qoldiramiz).
            $this->tranzaksiyaniSinxronlash($extId);
        }

        return $yozuv;
    }

    private function somDanTiyinga(float $som): int
    {
        return (int) round($som * 100);
    }

    private function tiyinDanSomga(float $tiyin): float
    {
        return round($tiyin / 100, 2);
    }
}
