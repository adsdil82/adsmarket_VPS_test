<?php
namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HybridMailController extends Controller
{
    public function index()
    {
        $sozlamalar = NotificationSetting::where('channel','hybrid_mail')->get()->keyBy('key');
        $loglar     = NotificationLog::where('channel','hybrid_mail')->latest()->take(20)->get();
        return view('xabarnoma.hybrid_mail.index', compact('sozlamalar','loglar'));
    }

    public function sozlamalarSaqla(Request $request)
    {
        $data = $request->only(['api_url','login','token','sender_name','test_mode','enabled']);
        $data['enabled']   = $request->boolean('enabled')   ? '1' : '0';
        $data['test_mode'] = $request->boolean('test_mode') ? '1' : '0';
        NotificationSetting::setChannel('hybrid_mail', $data);
        return back()->with('muvaffaqiyat', 'Gibrid Pochta sozlamalari saqlandi.');
    }

    public function testSend(Request $request)
    {
        $testMode = NotificationSetting::get('hybrid_mail','test_mode','1') === '1';
        $apiUrl   = NotificationSetting::get('hybrid_mail','api_url');

        $log = NotificationLog::create([
            'channel'        => 'hybrid_mail',
            'recipient_type' => 'test',
            'subject'        => 'NasiyaPro Gibrid Pochta Test',
            'message'        => 'Gibrid Pochta test xabari. ' . now()->format('d.m.Y H:i:s'),
            'status'         => 'test',
            'provider'       => 'hybrid_mail',
            'provider_response' => json_encode(['test_mode' => $testMode, 'api_url' => $apiUrl ?: 'not set']),
        ]);

        if ($testMode || !$apiUrl) {
            Log::channel('daily')->info('[HYBRID MAIL TEST] Test mode', ['log_id' => $log->id]);
            return response()->json(['status' => 'test', 'message' => 'Test mode: log #' . $log->id . ' yozildi. Real xat yuborilmadi.']);
        }

        // TODO: Real API integration
        return response()->json(['status' => 'test', 'message' => 'API sozlanmagan. Test mode yoqing yoki API ulang.']);
    }
}