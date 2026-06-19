<?php
namespace App\Services\Notification;

use App\Models\Mijoz;
use App\Models\RegKredit;
use App\Models\Grafik;
use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationRecipientService
{
    /**
     * Kechikkan kreditlar bo'yicha recipient'lar
     */
    public function getOverdueRecipients(array $filters = []): array
    {
        $minDays     = (int) ($filters['min_days']    ?? 1);
        $maxDays     = (int) ($filters['max_days']    ?? 9999);
        $minAmount   = (float)($filters['min_amount'] ?? 0);
        $filialId    = $filters['filial_id']    ?? null;
        $limit       = (int) ($filters['limit']       ?? 500);

        $query = RegKredit::with(['mijoz', 'filial'])
            ->whereIn('holat', ['faol', 'muddati_otgan'])
            ->where('qoldiq_qarz', '>', $minAmount)
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereHas('grafik', function($q) use ($minDays, $maxDays) {
                $q->whereIn('holat', ['muddati_otgan', 'qisman'])
                  ->whereNotNull('tolov_sana')
                  ->where('tolov_sana', '<', today())
                  ->havingRaw('MIN(DATEDIFF(CURDATE(), tolov_sana)) BETWEEN ? AND ?', [$minDays, $maxDays]);
            })
            ->limit($limit)
            ->get();

        return $this->buildRecipients($query, 'overdue');
    }

    /**
     * To'lov muddati yaqinlashayotgan recipient'lar
     */
    public function getUpcomingPaymentRecipients(array $filters = []): array
    {
        $days     = (int) ($filters['days']      ?? 3);
        $filialId = $filters['filial_id'] ?? null;
        $limit    = (int) ($filters['limit']     ?? 500);

        $targetDate = Carbon::today()->addDays($days)->toDateString();

        $query = RegKredit::with(['mijoz', 'filial'])
            ->whereIn('holat', ['faol', 'muddati_otgan'])
            ->where('qoldiq_qarz', '>', 0)
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->whereHas('grafik', function($q) use ($targetDate) {
                $q->whereIn('holat', ['tolanmagan', 'qisman'])
                  ->whereDate('tolov_sana', $targetDate);
            })
            ->limit($limit)
            ->get();

        return $this->buildRecipients($query, 'upcoming');
    }

    /**
     * Filial bo'yicha recipient'lar
     */
    public function getBranchRecipients(int $filialId, array $filters = []): array
    {
        $statusList = $filters['statuses'] ?? ['faol', 'muddati_otgan'];
        $limit      = (int)($filters['limit'] ?? 500);

        $query = RegKredit::with(['mijoz', 'filial'])
            ->where('filial_id', $filialId)
            ->whereIn('holat', $statusList)
            ->when(!empty($filters['only_debtors']), fn($q) => $q->where('qoldiq_qarz', '>', 0))
            ->limit($limit)
            ->get();

        return $this->buildRecipients($query, 'branch');
    }

    /**
     * Tanlangan mijoz ID lari bo'yicha
     */
    public function getSelectedRecipients(array $customerIds): array
    {
        $query = RegKredit::with(['mijoz', 'filial'])
            ->whereIn('mijoz_id', $customerIds)
            ->whereIn('holat', ['faol', 'muddati_otgan'])
            ->where('qoldiq_qarz', '>', 0)
            ->get();

        return $this->buildRecipients($query, 'selected');
    }

    /**
     * Custom filter bo'yicha
     */
    public function getCustomRecipients(array $filters): array
    {
        $query = RegKredit::with(['mijoz', 'filial'])
            ->when($filters['filial_id'] ?? null, fn($q, $v) => $q->where('filial_id', $v))
            ->when($filters['statuses']   ?? null, fn($q, $v) => $q->whereIn('holat', $v))
            ->when($filters['min_debt']   ?? null, fn($q, $v) => $q->where('qoldiq_qarz', '>=', $v))
            ->when($filters['max_debt']   ?? null, fn($q, $v) => $q->where('qoldiq_qarz', '<=', $v))
            ->when($filters['dan_sana']   ?? null, fn($q, $v) => $q->where('boshlanish_sana', '>=', $v))
            ->when($filters['gacha_sana'] ?? null, fn($q, $v) => $q->where('tugash_sana', '<=', $v))
            ->limit((int)($filters['limit'] ?? 500))
            ->get();

        return $this->buildRecipients($query, 'custom');
    }

    /**
     * Shartnomalardan recipient array yasash
     */
    private function buildRecipients(Collection $kreditlar, string $type): array
    {
        $recipients = [];
        $noPhone    = 0;
        $badPhone   = 0;

        foreach ($kreditlar as $k) {
            $mijoz = $k->mijoz;
            if (!$mijoz) continue;

            $phone = $this->normalizePhone($mijoz->telefon ?? '');

            if (!$phone) { $noPhone++; continue; }
            if (!$this->isValidPhone($phone)) { $badPhone++; continue; }

            $recipients[] = [
                'customer_id'    => $mijoz->id,
                'contract_id'    => $k->id,
                'customer_name'  => trim($mijoz->familiya . ' ' . $mijoz->ism),
                'phone'          => $phone,
                'contract_number'=> $k->shartnoma_raqam,
                'branch_name'    => $k->filial?->nomi ?? '',
                'overdue_days'   => $this->getOverdueDays($k),
                'overdue_amount' => $this->getOverdueAmount($k),
                'total_debt'     => (float)$k->qoldiq_qarz,
                'monthly_payment'=> (float)$k->oylik_tolov_miqdori,
                'type'           => $type,
            ];
        }

        return [
            'recipients'  => $recipients,
            'total'       => count($recipients),
            'no_phone'    => $noPhone,
            'bad_phone'   => $badPhone,
        ];
    }

    private function getOverdueDays(RegKredit $k): int
    {
        return (int) ($k->grafik()
            ->whereIn('holat', ['muddati_otgan', 'qisman'])
            ->whereNotNull('tolov_sana')
            ->where('tolov_sana', '<', today())
            ->selectRaw('MIN(DATEDIFF(CURDATE(), tolov_sana)) as min_days')
            ->value('min_days') ?? 0);
    }

    private function getOverdueAmount(RegKredit $k): float
    {
        return (float) ($k->grafik()
            ->whereIn('holat', ['muddati_otgan', 'qisman'])
            ->where('tolov_sana', '<', today())
            ->selectRaw('COALESCE(SUM(tolov_summa - COALESCE(tolangan_summa,0)), 0) as jami')
            ->value('jami') ?? 0);
    }

    private function normalizePhone(string $phone): string
    {
        if (!$phone) return '';
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 9) return '+998' . $digits;
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) return '+' . $digits;
        if (strlen($digits) > 13) return '';
        return '+' . $digits;
    }

    private function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^\+998[0-9]{9}$/', $phone);
    }
}