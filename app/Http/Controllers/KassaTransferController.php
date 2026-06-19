<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Kassa;
use App\Models\KassaTransfer;
use App\Models\Sozlama;
use App\Services\CashTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KassaTransferController extends Controller
{
    public function __construct(private CashTransferService $service) {}

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;
        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        $transferlar = KassaTransfer::with(['fromFilial','toFilial','fromKassa','toKassa','xodim'])
            ->when($filialId, fn($q) => $q->where('from_filial_id',$filialId)->orWhere('to_filial_id',$filialId))
            ->when($request->holat,     fn($q) => $q->where('holat',$request->holat))
            ->when($request->dan_sana,  fn($q) => $q->whereDate('created_at','>=',$request->dan_sana))
            ->when($request->gacha_sana,fn($q) => $q->whereDate('created_at','<=',$request->gacha_sana))
            ->latest()->paginate(25)->withQueryString();

        return view('transfer.kassa.index', compact('transferlar','filiallar'));
    }

    public function create()
    {
        $user     = Auth::user();
        $filiallar = Filial::faol()->get();
        $kassalar  = Kassa::faol()->with('filial')->get()->groupBy('filial_id');
        $soz       = Sozlama::barchasi();
        $kurslar   = [
            'UZS'=>1,
            'USD'=>(float)($soz['usd_sotish_kurs']??12700),
            'EUR'=>(float)($soz['eur_sotish_kurs']??13800),
            'RUB'=>(float)($soz['rub_sotish_kurs']??140),
        ];
        $mening_filial_id = $user->filial_id;

        return view('transfer.kassa.create', compact('filiallar','kassalar','kurslar','mening_filial_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_filial_id' => 'required|exists:filiallar,id',
            'from_kassa_id'  => 'required|exists:kassalar,id',
            'to_filial_id'   => 'required|exists:filiallar,id',
            'to_kassa_id'    => 'required|exists:kassalar,id|different:from_kassa_id',
            'summa'          => 'required|numeric|min:1',
            'valyuta'        => 'in:UZS,USD,EUR,RUB,CNY',
            'kurs'           => 'nullable|numeric|min:1',
            'sana'           => 'required|date',
            'sabab'          => 'nullable|string|max:300',
            'izoh'           => 'nullable|string|max:500',
        ]);

        $valyuta  = $request->valyuta ?? 'UZS';
        $kurs     = (float)($request->kurs ?? 1);
        $summaUzs = $valyuta === 'UZS' ? $request->summa : round($request->summa * $kurs, 2);

        try {
            $transfer = $this->service->yuborish(array_merge($request->only([
                'from_filial_id','from_kassa_id','to_filial_id','to_kassa_id',
                'summa','valyuta','sana','sabab','izoh'
            ]), ['kurs' => $kurs, 'summa_uzs' => $summaUzs]));

            return redirect()->route('transfer.kassa.show', $transfer)
                ->with('muvaffaqiyat', "Kassa transferi #{$transfer->transfer_raqam} yuborildi.");
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    public function show(KassaTransfer $kassaTransfer)
    {
        $kassaTransfer->load(['fromFilial','toFilial','fromKassa','toKassa','xodim','tasdiqlagan']);
        return view('transfer.kassa.show', ['transfer' => $kassaTransfer]);
    }

    public function qabulQilish(KassaTransfer $kassaTransfer)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->filial_id !== $kassaTransfer->to_filial_id) {
            return back()->with('xato', 'Faqat qabul qiluvchi filial xodimi tasdiqlaydi.');
        }
        try {
            $this->service->qabulQilish($kassaTransfer);
            return back()->with('muvaffaqiyat', 'Pul transferi qabul qilindi. Kassa qoldig\'i yangilandi.');
        } catch (\Exception $e) {
            return back()->with('xato', $e->getMessage());
        }
    }

    public function bekorQilish(Request $request, KassaTransfer $kassaTransfer)
    {
        $request->validate(['sabab' => 'required|string|min:5|max:500']);
        try {
            $this->service->bekorQilish($kassaTransfer, $request->sabab);
            return back()->with('muvaffaqiyat', 'Transfer bekor qilindi. Pul qaytarildi.');
        } catch (\Exception $e) {
            return back()->with('xato', $e->getMessage());
        }
    }
}
