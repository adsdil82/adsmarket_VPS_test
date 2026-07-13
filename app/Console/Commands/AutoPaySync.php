<?php

namespace App\Console\Commands;

use App\Models\AutopayShartnoma;
use App\Services\AutoPayService;
use App\Services\TulovService;
use Illuminate\Console\Command;

class AutoPaySync extends Command
{
    protected $signature   = 'autopay:sync {--dry-run : Faqat natijani ko\'rsatadi, to\'lov yozilmasin}';
    protected $description = 'Faol AutoPay shartnomalari bo\'yicha tranzaksiyalarni olib, muvaffaqiyatli to\'lovlarni tizimga yozadi (webhook ishlamay qolgan holatlar uchun zaxira mexanizm)';

    public function handle(AutoPayService $autoPay, TulovService $tulovService): int
    {
        if (!$autoPay->yoqilganmi()) {
            $this->warn('AutoPay o\'chirilgan (sozlamalarda yoqilmagan) — sinxronizatsiya o\'tkazib yuborildi.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $shartnomalar = AutopayShartnoma::whereIn('holat', ['faol', 'toxtatilgan'])->with(['mijoz', 'kredit'])->get();
        $this->info("Tekshiriladigan shartnomalar: {$shartnomalar->count()} ta");

        $yangiTolov = 0;
        $xatolar    = 0;
        $ochirilgan = 0;

        foreach ($shartnomalar as $shartnoma) {
            // AutoPay tarafida shartnoma o'chirilgan bo'lsa (masalan admin AutoPay
            // kabinetidan o'chirgan bo'lsa) — bizning tomonda ham shu holatni aks ettiramiz.
            $holatNatija = $autoPay->shartnomaTop($shartnoma->loan_id);
            if (!$holatNatija['success'] && str_contains(strtolower($holatNatija['error'] ?? ''), 'not found')) {
                if (!$dryRun) {
                    $shartnoma->update(['holat' => 'ochirilgan', 'auto_yoqilgan' => false]);
                }
                $this->warn("[{$shartnoma->loan_id}] AutoPay'da topilmadi — o'chirilgan deb belgilandi.");
                $ochirilgan++;
                continue;
            }

            if ($shartnoma->holat !== 'faol' || !$shartnoma->mijoz?->pinfl) {
                continue;
            }

            $natija = $autoPay->tranzaksiyalarniOl($shartnoma->mijoz->pinfl, $shartnoma->loan_id, pageSize: 50);
            if (!$natija['success']) {
                $this->error("[{$shartnoma->loan_id}] tranzaksiya olishda xato: {$natija['error']}");
                $xatolar++;
                continue;
            }

            foreach (($natija['result']['data'] ?? []) as $tranz) {
                $yozuv = $autoPay->tranzaksiyaniQayta($shartnoma, $tranz, $tulovService, $dryRun);
                if (!$yozuv) {
                    continue; // allaqachon qayd qilingan yoki ext yo'q
                }
                if ($yozuv->tulov_id || ($dryRun && strtolower($tranz['status'] ?? '') === AutoPayService::HOLAT_MUVAFFAQIYATLI)) {
                    $yangiTolov++;
                    $prefiks = $dryRun ? '[DRY-RUN] ' : '';
                    $this->info("{$prefiks}[{$shartnoma->loan_id}] to'lov: {$yozuv->summa} so'm (ext: {$yozuv->ext_id})");
                }
            }
        }

        $this->info("Yakunlandi. Yangi to'lovlar: {$yangiTolov} ta, o'chirilgan deb belgilangan: {$ochirilgan} ta, xatolar: {$xatolar} ta.");
        return self::SUCCESS;
    }
}
