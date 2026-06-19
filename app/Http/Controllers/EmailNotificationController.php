<?php
namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailNotificationController extends Controller
{
    public function index()
    {
        $sozlamalar = NotificationSetting::where('channel','email')->get()->keyBy('key');
        $loglar     = NotificationLog::where('channel','email')->latest()->take(20)->get();
        return view('xabarnoma.email.index', compact('sozlamalar','loglar'));
    }

    public function sozlamalarSaqla(Request $request)
    {
        $data = $request->only(['mailer','host','port','username','password','encryption','from_address','from_name','test_email','enabled']);
        $data['enabled'] = $request->boolean('enabled') ? '1' : '0';
        NotificationSetting::setChannel('email', $data);
        return back()->with('muvaffaqiyat', 'Email sozlamalari saqlandi.');
    }

    public function testEmail(Request $request)
    {
        $toEmail = NotificationSetting::get('email','test_email');
        if (!$toEmail) return response()->json(['error'=>'Test email manzil kiritilmagan.'],422);

        $log = NotificationLog::create([
            'channel'=>'email','recipient_type'=>'test',
            'email'=>$toEmail,'subject'=>'NasiyaPro Test Email',
            'message'=>'Test email xabari. ' . now()->format('d.m.Y H:i:s'),
            'status'=>'test','provider'=>'smtp',
        ]);

        return response()->json(['status'=>'test','message'=>"Test email '{$toEmail}' ga yozildi (log #{$log->id})"]);
    }
}