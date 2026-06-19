<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\FilialTransfer;
use App\Models\Ombor;
use App\Models\TovarKatalog;
use App\Services\StockTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{
    public function __construct(private StockTransferService $service) {}

    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;

        $transferlar = FilialTransfer::with(['fromFilial','toFilial','xodim','tafsilot'])
            ->when($filialId, fn($q) => $q->where('from_filial_id',$filialId)->orWhere('to_filial_id',$filialId))
            ->when($request->holat,     fn($q) => $q->where('holat',$request->holat))
            ->when($request->filial_id, fn($q) => $q->where('from_filial_id',$request->filial_id)
                ->orWhere('to_filial_id',$request->filial_id))
            ->when($request->dan_sana,  fn($q) => $q->whereDate('created_at','>=',$request->dan_sana))
            ->when($request->gacha_sana,fn($q) => $q->whereDate('created_at','<=',$request->gacha_sana))
            ->latest()->paginate(25)->withQueryString();

        $filiallar = $user->isAdmin() ? Filial::faol()->get() : collect();

        // Mening filialimga kelayotgan kutilayotganlar
        $kutilayotgan = FilialTransfer::with(['fromFilial'])
            ->where('holat','yuborildi')
            ->when($filialId, fn($q) => $q->where('to_filial_id',$filialId))
            ->count();

        return view('transfer.tovar.index', compact('transferlar','filiallar','kutilayotgan'));
    }

    public function create()
    {
        $user      = Auth::user();
        $filiallar = Filial::faol()->get();
        $omborlar  = Ombor::faol()->with('filial')->get()->groupBy('filial_id');
        $tovarlar  = TovarKatalog::faol()->where('qoldiq','>',0)->orderBy('nomi')->get(['id','nomi','birlik','qoldiq','tan_narx','guruh_id']);
        $mening_filial_id = $user->filial_id;

        return view('transfer.tovar.create', compact('filiallar','omborlar','tovarlar','mening_filial_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_filial_id'   => 'required|exists:filiallar,id',
            'to_filial_id'     => 'required|exists:filiallar,id|different:from_filial_id',
            'from_ombor_id'    => 'nullable|exists:omborlar,id',
            'to_ombor_id'      => 'nullable|exists:omborlar,id',
            'izoh'             => 'nullable|string|max:500',
            'tovarlar'         => 'required|array|min:1',
            'tovarlar.*.tovar_id' => 'required|exists:tovar_katalog,id',
            'tovarlar.*.miqdor'   => 'required|numeric|min:0.001',
        ]);

        try {
            $transfer = $this->service->yaratish($request->only([
                'from_filial_id','to_filial_id','from_ombor_id','to_ombor_id','izoh'
            ]), $request->tovarlar);

            return redirect()->route('transfer.tovar.show', $transfer)
                ->with('muvaffaqiyat', "Transfer #{$transfer->transfer_raqam} yuborildi.");
        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }
    }

    public function show(FilialTransfer $transfer)
    {
        $transfer->load(['fromFilial','toFilial','xodim','tasdiqlagan','tafsilot.tovar']);
        $fromOmbor = $transfer->from_ombor_id ? Ombor::find($transfer->from_ombor_id) : null;
        $toOmbor   = $transfer->to_ombor_id   ? Ombor::find($transfer->to_ombor_id)   : null;

        return view('transfer.tovar.show', compact('transfer','fromOmbor','toOmbor'));
    }

    public function qabulQilish(FilialTransfer $transfer)
    {
        $user = Auth::user();

        // Faqat qabul qiluvchi filial xodimi yoki admin
        if (!$user->isAdmin() && $user->filial_id !== $transfer->to_filial_id) {
            return back()->with('xato', 'Faqat qabul qiluvchi filial xodimi tasdiqlaydi.');
        }

        try {
            $this->service->qabulQilish($transfer);
            return back()->with('muvaffaqiyat', 'Transfer qabul qilindi. Tovarlar omborga kiritildi.');
        } catch (\Exception $e) {
            return back()->with('xato', $e->getMessage());
        }
    }

    public function bekorQilish(Request $request, FilialTransfer $transfer)
    {
        $request->validate(['sabab' => 'required|string|min:5|max:500']);

        try {
            $this->service->bekorQilish($transfer, $request->sabab);
            return back()->with('muvaffaqiyat', 'Transfer bekor qilindi.');
        } catch (\Exception $e) {
            return back()->with('xato', $e->getMessage());
        }
    }
}
