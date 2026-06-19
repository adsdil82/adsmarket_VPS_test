<?php
namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function index()
    {
        $sozlamalar = NotificationSetting::where('channel','telegram')->get()->keyBy('key');
        $loglar     = NotificationLog::where('channel','telegram')->latest()->take(20)->get();
        return view('xabarnoma.telegram.index', compact('sozlamalar','loglar'));
    }

    public function sozlamalarSaqla(Request $request)
    {
        $data = $request->only(['bot_token','bot_username','test_chat_id','enabled','parse_mode']);
        $data['enabled'] = $request->boolean('enabled') ? '1' : '0';
        NotificationSetting::setChannel('telegram', $data);
        return back()->with('muvaffaqiyat', 'Telegram sozlamalari saqlandi.');
    }

    public function testTelegram(Request $request)
    {
        $chatId    = NotificationSetting::get('telegram', 'test_chat_id');
        $botToken  = NotificationSetting::get('telegram', 'bot_token');
        if (!$chatId || !$botToken) {
            return response()->json(['error' => 'Bot token yoki test chat ID kiritilmagan.'], 422);
        }
        $response = \Illuminate\Support\Facades\Http::timeout(10)->post(
            "https://api.telegram.org/bot{$botToken}/sendMessage",
            ['chat_id' => $chatId, 'text' => 'NasiyaPro: Test xabar. ' . now()->format('H:i:s'), 'parse_mode' => 'HTML']
        );
        $body = $response->json();
        $ok   = $body['ok'] ?? false;

        NotificationLog::create([
            'channel' => 'telegram', 'recipient_type' => 'test',
            'telegram_chat_id' => $chatId, 'message' => 'Test xabar',
            'status' => $ok ? 'test' : 'failed',
            'provider_response' => json_encode($body),
            'error_message' => $ok ? null : ($body['description'] ?? 'Xato'),
        ]);

        return response()->json(['ok' => $ok, 'result' => $body]);
    }
}