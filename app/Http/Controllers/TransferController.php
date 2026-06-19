<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\FilialTransfer;
use App\Models\TovarKatalog;
use App\Models\TransferTafsilot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;

        $transferlar = FilialTransfer::with(['fromFilial', 'toFilial', 'xodim'])
            ->when($filialId, fn($q) => $q->where('from_filial_id', $filialId)
                                           ->orWhere('to_filial_id', $filialId))
            ->when($request->holat, fn($q) => $q->where('holat', $request->holat))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('ombor.transfer.index', compact('transferlar'));
    }

    public function create()
    {
        $user     = Auth::user();
        $filiallar = Filial::faol()->get();
        $tovarlar  = TovarKatalog::faol()
            ->when(!$user->isAdmin(), fn($q) => $q->where('qoldiq', '>', 0))
            ->orderBy('nomi')->get();
        $mening_filial = $user->filial_id;

        return view('ombor.transfer.create', compact('filiallar', 'tovarlar', 'mening_filial'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'to_filial_id' => ['required', 'exists:filiallar,id', 'different:from_filial_id'],
            'from_filial_id' => 'required|exists:filiallar,id',
            'izoh'         => 'nullable|string',
            'tovarlar'     => 'required|array|min:1',
            'tovarlar.*.tovar_id' => 'required|exists:tovar_katalog,id',
            'tovarlar.*.miqdor'   => 'required|numeric|min:0.001',
        ]);

        // Qoldiq tekshiruvi
        foreach ($request->tovarlar as $q) {
            $t = TovarKatalog::find($q['tovar_id']);
            if ($t->qoldiq < $q['miqdor']) {
                return back()->withErrors(["Tovar «{$t->nomi}»: omborda {$t->qoldiq} {$t->birlik} bor."])->withInput();
            }
        }

        DB::transaction(function () use ($request, $user) {
            $transfer = FilialTransfer::create([
                'from_filial_id' => $request->from_filial_id,
                'to_filial_id'   => $request->to_filial_id,
                'xodim_id'       => $user->id,
                'sana'           => today(),
                'holat'          => 'kutilmoqda',
                'izoh'           => $request->izoh,
            ]);

            foreach ($request->tovarlar as $q) {
                $tovar = TovarKatalog::find($q['tovar_id']);
                TransferTafsilot::create([
                    'transfer_id' => $transfer->id,
                    'tovar_id'    => $q['tovar_id'],
                    'miqdor'      => $q['miqdor'],
                    'narx'        => $tovar->sotish_narx,
                ]);
                // Jo'natuvchi filialdan chiqarish
                $tovar->decrement('qoldiq', $q['miqdor']);
            }
        });

        return redirect()->route('transfer.index')
            ->with('muvaffaqiyat', 'Transfer so\'rovi yuborildi. Qabul qiluvchi filial tasdiqlashi kerak.');
    }

    public function show(FilialTransfer $transfer)
    {
        $transfer->load(['fromFilial', 'toFilial', 'xodim', 'tasdiqlagan', 'tafsilot.tovar.guruh']);
        return view('ombor.transfer.show', compact('transfer'));
    }

    /** Qabul qiluvchi filial tomonidan tasdiqlash */
    public function tasdiqlash(FilialTransfer $transfer)
    {
        $user = Auth::user();
        if ($transfer->holat !== 'kutilmoqda') {
            return back()->with('xato', 'Bu transfer allaqachon ' . $transfer->holat . '.');
        }
        if (!$user->isAdmin() && $user->filial_id !== $transfer->to_filial_id) {
            return back()->with('xato', 'Faqat qabul qiluvchi filial tasdiqlashi mumkin.');
        }

        DB::transaction(function () use ($transfer, $user) {
            foreach ($transfer->tafsilot as $t) {
                // Qabul qiluvchi filialga qo'shish
                TovarKatalog::find($t->tovar_id)?->increment('qoldiq', $t->miqdor);
            }
            $transfer->update([
                'holat'                => 'tasdiqlangan',
                'tasdiqlagan_xodim_id' => $user->id,
                'tasdiqlangan_vaqt'    => now(),
            ]);
        });

        return back()->with('muvaffaqiyat', 'Transfer tasdiqlandi va tovarlar qabul qilindi.');
    }

    /** Bekor qilish (faqat kutilmoqda holatda) */
    public function bekorQilish(FilialTransfer $transfer)
    {
        if ($transfer->holat !== 'kutilmoqda') {
            return back()->with('xato', 'Bu transfer bekor qilinmaydi.');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->tafsilot as $t) {
                // Tovarni qaytarish
                TovarKatalog::find($t->tovar_id)?->increment('qoldiq', $t->miqdor);
            }
            $transfer->update(['holat' => 'bekor']);
        });

        return back()->with('muvaffaqiyat', 'Transfer bekor qilindi, tovarlar qaytarildi.');
    }
}
