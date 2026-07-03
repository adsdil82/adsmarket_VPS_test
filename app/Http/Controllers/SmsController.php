<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Mijoz;
use App\Models\NotificationBatch;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Services\Notification\NotificationRecipientService;
use App\Services\Notification\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    public function __construct(
        private SmsService $smsService,
        private NotificationRecipientService $recipientService
    ) {}

    // ── Yakka yuborish formasi ──────────────────────────────
    public function yakka()
    {
        $shablonlar = NotificationTemplate::faol()->channel('sms')->orderBy('name')->get();
        $filiallar  = Auth::user()->isAdmin() ? Filial::faol()->get(['id','nomi','kod']) : collect();
        return view('xabarnoma.sms.yakka', compact('shablonlar', 'filiallar'));
    }

    // ── Yakka SMS yuborish ──────────────────────────────────
    public function yakkaSend(Request $request)
    {
        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string|min:5|max:800',
        ]);

        $log = $this->smsService->sendSingle(
            $request->phone,
            $request->message,
            $request->customer_id ?: null,
            $request->contract_id ?: null,
            $request->template_id ?: null,
            null,
            'manual'
        );

        $msg = match($log->status) {
            'sent'    => 'SMS muvaffaqiyatli yuborildi.',
            'test'    => 'SMS test rejimda yuborildi (real SMS ketmadi).',
            'skipped' => 'SMS yuborilmadi: ' . $log->error_message,
            'failed'  => 'SMS yuborishda xato: ' . $log->error_message,
            default   => 'Noma\'lum holat.',
        };

        return back()->with($log->status === 'failed' ? 'xato' : 'muvaffaqiyat', $msg);
    }

    // ── Guruhli yuborish formasi ────────────────────────────
    public function guruhli()
    {
        $shablonlar = NotificationTemplate::faol()->channel('sms')->orderBy('name')->get();
        $filiallar  = Filial::faol()->get(['id','nomi','kod']);
        return view('xabarnoma.sms.guruhli', compact('shablonlar', 'filiallar'));
    }

    // ── Preview (AJAX) ──────────────────────────────────────
    public function preview(Request $request)
    {
        $request->validate(['type' => 'required|string', 'template_id' => 'required|exists:notification_templates,id']);

        $template  = NotificationTemplate::findOrFail($request->template_id);
        $filters   = $request->except(['type','template_id','_token']);
        $filters['limit'] = min((int)($filters['limit'] ?? 100), 500);

        // filial_id normalize: form turga qarab boshqacha nom yuboradi
        $filialId = (int) ($filters['filial_id']
            ?? $filters['filial_id_upcoming']
            ?? $filters['filial_id_branch']
            ?? $filters['filial_id_custom']
            ?? 0);
        if ($filialId) $filters['filial_id'] = $filialId;

        $result = match($request->type) {
            'overdue'   => $this->recipientService->getOverdueRecipients($filters),
            'upcoming'  => $this->recipientService->getUpcomingPaymentRecipients($filters),
            'branch'    => $this->recipientService->getBranchRecipients($filialId, $filters),
            'custom'    => $this->recipientService->getCustomRecipients($filters),
            default     => ['recipients'=>[],'total'=>0,'no_phone'=>0,'bad_phone'=>0],
        };

        // Har bir recipient uchun xabar matnini tayyorla
        $preview = collect($result['recipients'])->take(5)->map(fn($r) => [
            'name'    => $r['customer_name'],
            'phone'   => $r['phone'],
            'message' => $template->render([
                'client_name'     => $r['customer_name'],
                'contract_number' => $r['contract_number'] ?? '',
                'branch_name'     => $r['branch_name']     ?? '',
                'overdue_days'    => $r['overdue_days']    ?? 0,
                'overdue_amount'  => number_format($r['overdue_amount'] ?? 0, 0, '.', ' '),
                'total_debt'      => number_format($r['total_debt']     ?? 0, 0, '.', ' '),
                'monthly_payment' => number_format($r['monthly_payment']?? 0, 0, '.', ' '),
                'company_name'    => config('app.name','NasiyaPro'),
            ]),
        ])->toArray();

        $segments = $this->segmentCount($template->body);

        return response()->json([
            'total'      => $result['total'],
            'no_phone'   => $result['no_phone'],
            'bad_phone'  => $result['bad_phone'],
            'preview'    => $preview,
            'segments'   => $segments,
            'template'   => ['body' => $template->body, 'name' => $template->name],
        ]);
    }

    // ── Guruhli SMS yuborish ────────────────────────────────
    public function guruhliSend(Request $request)
    {
        $request->validate([
            'type'        => 'required|string',
            'template_id' => 'required|exists:notification_templates,id',
        ]);

        $template = NotificationTemplate::findOrFail($request->template_id);
        $filters  = $request->except(['type','template_id','_token']);
        $filters['limit'] = min((int)($filters['limit'] ?? 200), 500);

        $filialId = (int) ($filters['filial_id']
            ?? $filters['filial_id_upcoming']
            ?? $filters['filial_id_branch']
            ?? $filters['filial_id_custom']
            ?? 0);
        if ($filialId) $filters['filial_id'] = $filialId;

        $result = match($request->type) {
            'overdue'  => $this->recipientService->getOverdueRecipients($filters),
            'upcoming' => $this->recipientService->getUpcomingPaymentRecipients($filters),
            'branch'   => $this->recipientService->getBranchRecipients($filialId, $filters),
            'custom'   => $this->recipientService->getCustomRecipients($filters),
            default    => ['recipients'=>[],'total'=>0,'no_phone'=>0,'bad_phone'=>0],
        };

        if ($result['total'] === 0) {
            return back()->with('xato', 'Yuboriladigan recipient topilmadi.');
        }

        $batch = NotificationBatch::create([
            'channel'          => 'sms',
            'type'             => $request->type,
            'title'            => $template->name . ' — ' . now()->format('d.m.Y H:i'),
            'filters_json'     => $filters,
            'total_recipients' => $result['total'],
            'status'           => 'draft',
            'created_by'       => Auth::id(),
        ]);

        $items = collect($result['recipients'])->map(fn($r) => array_merge($r, [
            'message' => $template->render([
                'client_name'     => $r['customer_name'],
                'contract_number' => $r['contract_number'] ?? '',
                'branch_name'     => $r['branch_name']     ?? '',
                'overdue_days'    => $r['overdue_days']    ?? 0,
                'overdue_amount'  => number_format($r['overdue_amount'] ?? 0, 0, '.', ' '),
                'total_debt'      => number_format($r['total_debt']     ?? 0, 0, '.', ' '),
                'monthly_payment' => number_format($r['monthly_payment']?? 0, 0, '.', ' '),
                'company_name'    => config('app.name','NasiyaPro'),
            ]),
        ]))->toArray();

        $this->smsService->sendBatch($batch, $items, $template);
        $batch->refresh();

        return redirect()->route('xabarnoma.sms.tarix')
            ->with('muvaffaqiyat', "Yuborildi: {$batch->total_sent} ta. Xato: {$batch->total_failed} ta.");
    }

    // ── Tarix ───────────────────────────────────────────────
    public function tarix(Request $request)
    {
        $user    = Auth::user();
        $loglar  = NotificationLog::with(['customer','template'])
            ->where('channel', 'sms')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->dan_sana, fn($q) => $q->whereDate('created_at', '>=', $request->dan_sana))
            ->when($request->gacha_sana, fn($q) => $q->whereDate('created_at', '<=', $request->gacha_sana))
            ->latest()->paginate(25)->withQueryString();

        $batchlar = NotificationBatch::where('channel','sms')->latest()->take(10)->get();
        return view('xabarnoma.sms.tarix', compact('loglar','batchlar'));
    }

    // ── Sozlamalar ──────────────────────────────────────────
    public function sozlamalar()
    {
        // SMS sozlamalari Boshqaruv paneli (Sozlamalar) sahifasiga ko'chirildi — admin'lar shu yerga yo'naltiriladi
        if (Auth::user()->isAdmin()) {
            return redirect(route('admin.sozlamalar') . '#collapseSms');
        }

        $sozlamalar = NotificationSetting::where('channel','sms')->get()->keyBy('key');
        $providerStatus = null;
        try { $providerStatus = $this->smsService->getProviderStatus(); } catch (\Exception $e) {}
        return view('xabarnoma.sms.sozlamalar', compact('sozlamalar','providerStatus'));
    }

    public function sozlamalarSaqla(Request $request)
    {
        $data = $request->only(['provider','api_url','login','password','sender_id','test_phone','enabled','test_mode']);
        $data['enabled']   = $request->boolean('enabled')   ? '1' : '0';
        $data['test_mode'] = $request->boolean('test_mode') ? '1' : '0';
        NotificationSetting::setChannel('sms', $data);
        return back()->with('muvaffaqiyat', 'SMS sozlamalari saqlandi.');
    }

    public function testSms(Request $request)
    {
        $phone = NotificationSetting::get('sms', 'test_phone');
        if (!$phone) return response()->json(['error' => 'Test telefon raqam kiritilmagan.'], 422);

        $log = $this->smsService->sendSingle($phone, 'NasiyaPro: Test SMS xabari. ' . now()->format('H:i:s'), null, null, null, null, 'test');
        return response()->json(['status' => $log->status, 'message' => $log->error_message ?? 'OK', 'provider' => $log->provider]);
    }

    private function segmentCount(string $msg): int
    {
        $len = mb_strlen($msg);
        if ($len <= 160) return 1;
        return (int) ceil($len / 153);
    }
}