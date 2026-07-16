<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sozlama;
use App\Models\PochtaLog;
use App\Models\PochtaShablon;
use App\Models\RegKredit;

class HybridPochtaService
{
    private const BASE_URL   = 'https://hybrid.pochta.uz';
    private const TOKEN_KEY  = 'hybrid_pochta_token';
    private const CACHE_DAYS = 6; // token ~7 kun amal qiladi

    private ?string $login;
    private ?string $password;
    private bool    $enabled;

    public function __construct()
    {
        $this->login    = Sozlama::ol('hybrid_pochta_login')    ?: null;
        $this->password = Sozlama::ol('hybrid_pochta_password') ?: null;
        $this->enabled  = Sozlama::ol('hybrid_pochta_yoqilgan', '0') === '1';
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->login && $this->password;
    }

    // ─── Token ────────────────────────────────────────────────────────────────

    public function getToken(): ?string
    {
        if (Cache::has(self::TOKEN_KEY)) {
            return Cache::get(self::TOKEN_KEY);
        }
        if (!$this->login || !$this->password) {
            return null;
        }

        try {
            $resp = Http::timeout(15)
                ->asForm()
                ->post(self::BASE_URL . '/token', [
                    'grant_type' => 'password',
                    'username'   => $this->login,
                    'password'   => $this->password,
                ]);

            if ($resp->ok()) {
                $token = $resp->json('access_token');
                Cache::put(self::TOKEN_KEY, $token, now()->addDays(self::CACHE_DAYS));
                return $token;
            }

            Log::warning('HybridPochta: token olishda xato', [
                'status' => $resp->status(),
                'body'   => $resp->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('HybridPochta token exception: ' . $e->getMessage());
        }

        return null;
    }

    public function clearToken(): void
    {
        Cache::forget(self::TOKEN_KEY);
    }

    // ─── Spravochniklar (cached) ──────────────────────────────────────────────

    public function getRegions(): array
    {
        return Cache::remember('hp_regions', now()->addDay(),
            fn() => $this->request('GET', '/api/region') ?? []
        );
    }

    public function getAreas(): array
    {
        return Cache::remember('hp_areas', now()->addDay(),
            fn() => $this->request('GET', '/api/area') ?? []
        );
    }

    // ─── Xat operatsiyalari ───────────────────────────────────────────────────

    /** QADAM 1: Xatni API da yaratish (hali yuborilmaydi) */
    public function createMail(
        string $receiver,
        string $address,
        int    $regionId,
        int    $areaId,
        string $pdfBase64
    ): ?array {
        return $this->request('POST', '/api/PdfMail', [
            'Receiver'   => $receiver,
            'Address'    => $address,
            'Region'     => $regionId,
            'Area'       => $areaId,
            'Document64' => $pdfBase64,
        ]);
    }

    /**
     * QADAM 2 (Variant A): Brauzer E-IMZO uchun hash olish
     * Brauzer bu hashni E-IMZO plugin orqali imzolaydi
     */
    public function getHashForSign(int $letterId): ?string
    {
        $resp = $this->request('GET', "/api/SendMail/{$letterId}");
        return is_string($resp) ? $resp : null;
    }

    /**
     * QADAM 3 (Variant A): Brauzer E-IMZO imzosi bilan yuborish
     * signature — E-IMZO JS plugin createPkcs7() dan olingan PKCS7 base64
     */
    public function sendMailVariantA(int $letterId, string $signature): ?array
    {
        return $this->request('PUT', "/api/SendMail/{$letterId}", [
            'signature' => $signature,
        ]);
    }

    /**
     * QADAM 3 (Variant B): Server sertifikat bilan yuborish (brauzersiz)
     *
     * Setup:
     *  1. E-IMZO dan sertifikatni .pfx formatida eksport qiling
     *  2. Serverga yuklang: storage/app/certs/hp_cert.pfx
     *  3. Sozlamalar da: hybrid_pochta_cert_parol → sertifikat paroli
     *
     * Eslatma: E-IMZO PKCS7 formati OpenSSL PKCS7 dan farq qilishi mumkin.
     * Agar imzo rad etilsa — Hybrid Pochta texnik jamoasi bilan bog'laning.
     */
    public function sendMailVariantB(int $letterId): ?array
    {
        $certPath = storage_path('app/certs/hp_cert.pfx');
        $certPass = Sozlama::ol('hybrid_pochta_cert_parol');

        if (!file_exists($certPath)) {
            Log::error('HybridPochta Variant B: sertifikat fayl yo\'q: ' . $certPath);
            return null;
        }
        if (!$certPass) {
            Log::error('HybridPochta Variant B: hybrid_pochta_cert_parol sozlamada yo\'q');
            return null;
        }

        $hashBase64 = $this->getHashForSign($letterId);
        if (!$hashBase64) return null;

        try {
            $pkcs12 = file_get_contents($certPath);
            if (!openssl_pkcs12_read($pkcs12, $certData, $certPass)) {
                Log::error('HybridPochta Variant B: sertifikatni o\'qib bo\'lmadi (noto\'g\'ri parol?)');
                return null;
            }

            $hashBytes = base64_decode($hashBase64);
            $tmpIn     = tempnam(sys_get_temp_dir(), 'hp_');
            $tmpOut    = tempnam(sys_get_temp_dir(), 'hp_');

            file_put_contents($tmpIn, $hashBytes);
            openssl_pkcs7_sign(
                $tmpIn, $tmpOut,
                $certData['cert'], $certData['pkey'],
                [], PKCS7_BINARY | PKCS7_DETACHED
            );

            $raw = file_get_contents($tmpOut);
            @unlink($tmpIn);
            @unlink($tmpOut);

            if (!preg_match('/-----BEGIN PKCS7-----(.+?)-----END PKCS7-----/s', $raw, $m)) {
                Log::error('HybridPochta Variant B: PKCS7 parse xatosi');
                return null;
            }

            $signature = trim(str_replace("\n", '', $m[1]));
            return $this->sendMailVariantA($letterId, $signature);

        } catch (\Exception $e) {
            Log::error('HybridPochta Variant B exception: ' . $e->getMessage());
            return null;
        }
    }

    /** Kvitansiya PDF bytes (base64 encode qilib saqlash mumkin) */
    public function getReceipt(int $letterId): ?string
    {
        $token = $this->getToken();
        if (!$token) return null;

        try {
            $resp = Http::timeout(30)
                ->withToken($token)
                ->get(self::BASE_URL . '/api/Receipt', ['id' => $letterId]);

            if ($resp->ok()) return $resp->body();
        } catch (\Exception $e) {
            Log::error('HybridPochta getReceipt: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Xat holati va tafsilotlari. Hujjatda yo'l "/api/mail/:id" deb yozilgan
     * (Express uslubidagi path-parametr), lekin "id" parametri "query" deb
     * belgilangan — bu ehtimol hujjatdagi beparvolik. Ikkalasini ham
     * qo'llab-quvvatlash uchun id both path segment VA query sifatida yuboriladi.
     */
    public function getMailById(int $letterId): ?array
    {
        return $this->request('GET', "/api/mail/{$letterId}", ['id' => $letterId]);
    }

    /**
     * Holat-sinxronlash uchun: 404 va "topilmadi" (masalan tarmoq/auth xatosi)
     * holatlarini bir-biridan ajratadi — chunki 404 kabinetda o'chirilgan
     * xat uchun ham qaytadi (o'chirilgan xat butunlay API'dan yo'qoladi,
     * IsDeleted bayrog'i emas), buni "API xatosi" bilan aralashtirmaslik kerak.
     *
     * @return array{topildi: bool, ochirilgan: bool, data: ?array}
     */
    public function mailHolatiTekshir(int $letterId): array
    {
        $token = $this->getToken();
        if (!$token) {
            return ['topildi' => false, 'ochirilgan' => false, 'data' => null];
        }

        try {
            $resp = Http::timeout(20)->withToken($token)
                ->get(self::BASE_URL . "/api/mail/{$letterId}", ['id' => $letterId]);

            if ($resp->status() === 401) {
                $this->clearToken();
                $token = $this->getToken();
                if (!$token) return ['topildi' => false, 'ochirilgan' => false, 'data' => null];
                $resp = Http::timeout(20)->withToken($token)
                    ->get(self::BASE_URL . "/api/mail/{$letterId}", ['id' => $letterId]);
            }

            if ($resp->status() === 404) {
                return ['topildi' => true, 'ochirilgan' => true, 'data' => null];
            }
            if ($resp->ok()) {
                return ['topildi' => true, 'ochirilgan' => false, 'data' => $resp->json()];
            }

            Log::warning("HybridPochta mailHolatiTekshir [{$letterId}] {$resp->status()}", ['body' => $resp->body()]);
        } catch (\Exception $e) {
            Log::error('HybridPochta mailHolatiTekshir exception: ' . $e->getMessage());
        }

        return ['topildi' => false, 'ochirilgan' => false, 'data' => null];
    }

    /**
     * Sana/filtr bo'yicha xatlar ro'yxatini bevosita Hybrid Pochta API'sining
     * o'zidan olish (bizning lokal pochta_loglar jadvalimizdan mustaqil —
     * solishtirish/audit uchun foydali: masalan API real holatini tekshirish).
     *
     * @param array $filtr  uid, receiver, region, area, creatorUser, senderUser,
     *                      isSent, createdStart/End, sentStart/End, pagesCount*,
     *                      isPerformed, performType, sort, desc
     */
    public function mailRoyxati(int $skip = 0, int $limit = 50, array $filtr = []): ?array
    {
        return $this->request('GET', '/api/mail', array_merge(
            ['skip' => $skip, 'limit' => $limit],
            array_filter($filtr, fn($v) => $v !== null && $v !== '')
        ));
    }

    // ─── PDF generatsiya ─────────────────────────────────────────────────────

    /**
     * Kredit + Shablon asosida PDF yaratib base64 va HAQIQIY sahifa sonini
     * qaytaradi. Hybrid Pochta 1 varaqdan ortiq xat uchun qo'shimcha to'lov
     * oladi — shuning uchun chaqiruvchi tomon 'sahifalar' > 1 bo'lsa xatni
     * API'ga yubormasligi kerak (PochXatController shu tekshiruvni qiladi).
     *
     * @return array{base64: string, sahifalar: int}
     */
    public function generatePdfBase64(RegKredit $kredit, PochtaShablon $shablon): array
    {
        $vars = $this->buildVars($kredit);
        $matn = $shablon->renderMatn($vars);

        $pdf = Pdf::loadView('pochta.xat_shablon', [
            'matn'   => $matn,
            'kredit' => $kredit,
            'vars'   => $vars,
        ])->setPaper('A4', 'portrait');

        $bytes     = $pdf->output();
        $sahifalar = $pdf->getDomPDF()->getCanvas()->get_page_count();

        return [
            'base64'    => base64_encode($bytes),
            'sahifalar' => $sahifalar,
        ];
    }

    // ─── Template vars ────────────────────────────────────────────────────────

    public function buildVars(RegKredit $kredit): array
    {
        $mijoz        = $kredit->mijoz;
        // Carbon 3'da diffInDays($absolute=true) ham ba'zan manfiy/kasr son qaytarishi
        // mumkin ekan (kuzatilgan xato) — abs()+int bilan har doim toza kun soni olinadi.
        $kechikishKun = $kredit->tugash_sana ? max(0, (int) abs(now()->diffInDays($kredit->tugash_sana, true))) : 0;

        return [
            'mijoz_fio'       => trim("{$mijoz->familiya} {$mijoz->ism} {$mijoz->otasining_ismi}"),
            'shartnoma_raqam' => $kredit->shartnoma_raqam ?? "K-{$kredit->id}",
            'eski_raqam'      => $kredit->eskiRaqamKorinishi() ?? '',
            'kechikish_kun'   => $kechikishKun,
            'jami_qarz'       => number_format(
                $kredit->kechikkanSummaHisobla(), 0, '.', ' '
            ) . " so'm",
            'yuborish_sana'  => now()->format('d.m.Y'),
            'tashkilot_nomi' => Sozlama::ol('kompaniya_nomi', config('app.name')),
        ];
    }

    // ─── Limit tekshirish ─────────────────────────────────────────────────────

    /** Shu shablon bu kreditga yana yuborish mumkinmi? (qayta_yuborish_kun tekshirish) */
    public function isYuborishMumkin(int $kreditId, int $shablonId): bool
    {
        $shablon = PochtaShablon::find($shablonId);
        if (!$shablon || $shablon->qayta_yuborish_kun <= 0) return true;

        $oxirgi = PochtaLog::where('reg_kredit_id', $kreditId)
            ->where('shablon_id', $shablonId)
            ->where('holat', 'yuborildi')
            ->latest('yuborildi_vaqt')
            ->first();

        if (!$oxirgi?->yuborildi_vaqt) return true;

        return $oxirgi->yuborildi_vaqt->diffInDays(now()) >= $shablon->qayta_yuborish_kun;
    }

    /** Sozlamalar sahifasidan ulanish tekshirish */
    public function testConnection(): array
    {
        $this->clearToken();
        $token = $this->getToken();

        return $token
            ? ['ok' => true,  'xabar' => 'Hybrid Pochta ga muvaffaqiyatli ulandi.']
            : ['ok' => false, 'xabar' => 'Ulanib bo\'lmadi. Login/parol tekshiring.'];
    }

    // ─── HTTP wrapper ─────────────────────────────────────────────────────────

    private function request(string $method, string $path, array $data = []): mixed
    {
        $token = $this->getToken();
        if (!$token) return null;

        try {
            $result = $this->doRequest($method, $path, $data, $token);

            if ($result === '__401__') {
                // Token eskirgan — yangilash
                $this->clearToken();
                $token = $this->getToken();
                if (!$token) return null;
                $result = $this->doRequest($method, $path, $data, $token);
            }

            return ($result === '__401__') ? null : $result;

        } catch (\Exception $e) {
            Log::error("HybridPochta [{$method} {$path}]: " . $e->getMessage());
            return null;
        }
    }

    private function doRequest(string $method, string $path, array $data, string $token): mixed
    {
        $http = Http::timeout(20)->withToken($token);

        $resp = match (strtoupper($method)) {
            'GET'  => $http->get(self::BASE_URL . $path, $data),
            'POST' => $http->post(self::BASE_URL . $path, $data),
            'PUT'  => $http->put(self::BASE_URL . $path, $data),
            default => null,
        };

        if (!$resp) return null;
        if ($resp->status() === 401) return '__401__';

        if (!$resp->ok()) {
            Log::warning("HybridPochta [{$method} {$path}] {$resp->status()}", ['body' => $resp->body()]);
            return null;
        }

        if (str_contains($resp->header('Content-Type'), 'application/pdf')) {
            return $resp->body();
        }

        return $resp->json() ?? $resp->body();
    }
}
