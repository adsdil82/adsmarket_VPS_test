<?php
namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationTemplateController extends Controller
{
    public function index()
    {
        $shablonlar = NotificationTemplate::latest()->paginate(20)->withQueryString();
        $channels   = ['sms'=>'SMS','telegram'=>'Telegram','email'=>'Email'];
        $variables  = NotificationTemplate::variables();
        return view('xabarnoma.shablonlar.index', compact('shablonlar','channels','variables'));
    }

    public function create()
    {
        $channels  = ['sms'=>'SMS','telegram'=>'Telegram','email'=>'Email'];
        $variables = NotificationTemplate::variables();
        return view('xabarnoma.shablonlar.create', compact('channels','variables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel'    => 'required|in:sms,telegram,email',
            'code'       => 'required|string|max:50|unique:notification_templates,code|alpha_dash',
            'name'       => 'required|string|max:200',
            'subject'    => 'nullable|string|max:300',
            'body'       => 'required|string|min:5',
            'is_active'  => 'boolean',
            'is_default' => 'boolean',
        ]);
        $data['created_by'] = Auth::id();
        $data['is_active']  = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        NotificationTemplate::create($data);
        return redirect()->route('xabarnoma.shablonlar.index')
            ->with('muvaffaqiyat', "Shablon '{$data['name']}' yaratildi.");
    }

    public function edit(NotificationTemplate $shablon)
    {
        $channels  = ['sms'=>'SMS','telegram'=>'Telegram','email'=>'Email'];
        $variables = NotificationTemplate::variables();
        return view('xabarnoma.shablonlar.edit', compact('shablon','channels','variables'));
    }

    public function update(Request $request, NotificationTemplate $shablon)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'subject'    => 'nullable|string|max:300',
            'body'       => 'required|string|min:5',
            'is_active'  => 'boolean',
            'is_default' => 'boolean',
        ]);
        $data['updated_by'] = Auth::id();
        $data['is_active']  = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        $shablon->update($data);
        return redirect()->route('xabarnoma.shablonlar.index')
            ->with('muvaffaqiyat', "Shablon yangilandi.");
    }

    /** Preview AJAX */
    public function preview(Request $request, NotificationTemplate $shablon)
    {
        $sampleVars = [
            'client_name'     => 'Aliyev Jasur',
            'contract_number' => 'IST-2026-00123',
            'branch_name'     => 'Istiqlol filiali',
            'payment_date'    => now()->addDays(3)->format('d.m.Y'),
            'monthly_payment' => '375 000',
            'overdue_days'    => '5',
            'overdue_amount'  => '375 000',
            'total_debt'      => '1 500 000',
            'paid_amount'     => '500 000',
            'remaining_amount'=> '1 000 000',
            'company_name'    => config('app.name','NasiyaPro'),
            'manager_phone'   => '+998901234567',
        ];
        return response()->json([
            'preview' => $shablon->render($sampleVars),
            'variables' => $shablon->variables,
            'chars'   => mb_strlen($shablon->body),
        ]);
    }
}