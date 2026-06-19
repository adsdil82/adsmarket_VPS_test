<?php
namespace App\Console\Commands;

use App\Models\NotificationBatch;
use App\Models\NotificationTemplate;
use App\Services\Notification\SmsService;
use App\Services\Notification\NotificationRecipientService;
use App\Services\Notification\NotificationTemplateService;
use Illuminate\Console\Command;

class SendOverdueSmsCommand extends Command
{
    protected $signature   = 'notification:send-overdue-sms
                              {--dry-run       : Faqat natijani ko\'rsatadi, SMS yubormasin}
                              {--branch=       : Filial ID}
                              {--days=1-30     : Kechikkan kunlar oralig\'i (masalan: 1-30)}
                              {--template=     : Shablon kodi}
                              {--limit=100     : Maksimal recipient soni}
                              {--min-amount=0  : Minimal qarz summasi}';

    protected $description = 'Kechikkan kreditlar bo\'yicha avtomatik SMS yuborish';

    public function handle(
        SmsService $smsService,
        NotificationRecipientService $recipientService
    ): int {
        $this->info('Kechikkan kreditlar SMS buyrug\'i boshlandi...');

        $dryRun     = $this->option('dry-run');
        $branchId   = $this->option('branch')     ?: null;
        $daysRange  = $this->option('days')        ?? '1-30';
        $limit      = (int)($this->option('limit') ?? 100);
        $minAmount  = (float)($this->option('min-amount') ?? 0);
        $tmplCode   = $this->option('template')    ?? 'sms_kechikkan_tolov';

        // Kunlar oralig\'ini ajratish (masalan: "1-30" → [1, 30])
        [$minDays, $maxDays] = str_contains($daysRange, '-')
            ? array_map('intval', explode('-', $daysRange, 2))
            : [(int)$daysRange, (int)$daysRange];

        // Shablon
        $template = NotificationTemplate::where('code', $tmplCode)->where('is_active', true)->first();
        if (!$template) {
            $this->error("Shablon topilmadi: {$tmplCode}");
            return self::FAILURE;
        }

        // Recipient'larni olish
        $filters = ['min_days'=>$minDays,'max_days'=>$maxDays,'min_amount'=>$minAmount,'filial_id'=>$branchId,'limit'=>$limit];
        $result  = $recipientService->getOverdueRecipients($filters);

        $this->info("Topilgan: {$result['total']} ta recipient");
        $this->info("Telefon yo'q: {$result['no_phone']} ta");
        $this->info("Noto'g'ri tel: {$result['bad_phone']} ta");

        if ($dryRun) {
            $this->warn('[DRY-RUN] Real SMS yuborilmadi.');
            $this->table(['Mijoz', 'Telefon', 'Shartnoma', 'Qarz', 'Kun'],
                collect($result['recipients'])->take(20)->map(fn($r) => [
                    $r['customer_name'], $r['phone'], $r['contract_number'],
                    number_format($r['total_debt']), $r['overdue_days']
                ])->toArray()
            );
            return self::SUCCESS;
        }

        if ($result['total'] === 0) {
            $this->info('Yuboriladigan recipient yo\'q.');
            return self::SUCCESS;
        }

        // Batch yaratish
        $batch = NotificationBatch::create([
            'channel'          => 'sms',
            'type'             => 'overdue',
            'title'            => "Kechikkan SMS: {$minDays}-{$maxDays} kun ({$limit} ta limit)",
            'filters_json'     => $filters,
            'total_recipients' => $result['total'],
            'status'           => 'sending',
            'started_at'       => now(),
        ]);

        // Xabarlarni tayyorlash
        $items = collect($result['recipients'])->map(function($r) use ($template) {
            return array_merge($r, ['message' => $template->render([
                'client_name'     => $r['customer_name'],
                'contract_number' => $r['contract_number'],
                'overdue_days'    => $r['overdue_days'],
                'overdue_amount'  => number_format($r['overdue_amount'], 0, '.', ' '),
                'total_debt'      => number_format($r['total_debt'], 0, '.', ' '),
                'monthly_payment' => number_format($r['monthly_payment'], 0, '.', ' '),
                'company_name'    => config('app.name', 'NasiyaPro'),
                'branch_name'     => $r['branch_name'],
            ])]);
        })->toArray();

        $this->withProgressBar($items, function($item) use ($smsService, $template, $batch) {
            $smsService->sendSingle(
                $item['phone'], $item['message'],
                $item['customer_id'] ?? null,
                $item['contract_id'] ?? null,
                $template->id, $batch->id
            );
        });

        $batch->refresh();
        $this->newLine();
        $this->info("Yuborildi: {$batch->total_sent} ta");
        $this->warn("Xato:     {$batch->total_failed} ta");
        $this->warn("O'tkazib: {$batch->total_skipped} ta");

        return self::SUCCESS;
    }
}