<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Ombor;
use App\Models\Taminotchi;
use App\Models\TaminotchiQaytarish;
use App\Models\TaminotchiQaytarishQator;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierReturnController extends Controller
{
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;

        $qaytarishlar = TaminotchiQaytarish::with(['taminotchi','ombor','filial','xodim'])
            ->when($filialId, fn($q) => $q->where('filial_id',$filialId))
            ->when($request->holat,       fn($q) => $q->where('holat',$request->holat))
            ->when($request->taminotchi_id, fn($q)=> $q->where('taminotchi_id',$request->taminotchi_id))
            ->when($request->dan_sana,    fn($q) => $q->whereDate('sana','>=',$request->dan_sana))
            ->when($request->gacha_sana,  fn($q) => $q->whereDate('sana','<=',$request->gacha_sana))
            ->latest()->paginate(25)->withQueryString();

        $taminotchilar = Taminotchi::faol()->orderBy('nomi')->get(['id','nomi']);

        return view('transfer.supplier_return.index', compact('qaytarishlar','taminotchilar'));
    }

    public function create()
    {
        $user         = Auth::user();
        $filialId     = $user->filial_id;
        $taminotchilar = Taminotchi::faol()->orderBy('nomi')->get();
        $omborlar     = Ombor::faol()
            ->when($filialId, fn($q) => $q->where('filial_id',$filialId))
            ->with('filial')->get();
        $tovarlar     = TovarKatalog::faol()->where('qoldiq','>',0)->orderBy('nomi')
            ->get(['id','nomi','birlik','qoldiq','tan_narx']);

        return view('transfer.supplier_return.create', compact('taminotchilar','omborlar','tovarlar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'taminotchi_id' => 'required|exists:taminotchilar,id',
            'ombor_id'      => 'required|exists:omborlar,id',
            'sana'          => 'required|date',
            'sabab'         => 'required|string|min:5|max:300',
            'izoh'          => 'nullable|string|max:500',
            'qatorlar'      => 'required|array|min:1',
            'qatorlar.*.tovar_id' => 'nullable|exists:tovar_katalog,id',
            'qatorlar.*.nomi'     => 'required|string',
            'qatorlar.*.miqdor'   => 'required|numeric|min:0.001',
            'qatorlar.*.narx'     => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $jami = collect($request->qatorlar)->sum(fn($q) => $q['miqdor'] * $q['narx']);

            $qaytarish = TaminotchiQaytarish::create([
                'taminotchi_id' => $request->taminotchi_id,
                'ombor_id'      => $request->ombor_id,
                'filial_id'     => Auth::user()->filial_id ?? Ombor::find($request->ombor_id)->filial_id,
                'xodim_id'      => Auth::id(),
                'sana'          => $request->sana,
                'jami_summa'    => $jami,
                'holat'         => 'qoralama',
                'sabab'         => $request->sabab,
                'izoh'          => $request->izoh,
            ]);

            foreach ($request->qatorlar as $q) {
                TaminotchiQaytarishQator::create([
                    'qaytarish_id' => $qaytarish->id,
                    'tovar_id'     => $q['tovar_id'] ?? null,
                    'nomi'         => $q['nomi'],
                    'miqdor'       => $q['miqdor'],
                    'birlik'       => $q['birlik'] ?? 'dona',
                    'narx'         => $q['narx'],
                    'jami'         => $q['miqdor'] * $q['narx'],
                    'sabab'        => $q['sabab'] ?? null,
                ]);
            }
        });

        return redirect()->route('transfer.supplier-return.index')
            ->with('muvaffaqiyat', 'Qaytarish qoralamasi yaratildi. Tasdiqlang.');
    }

    public function show(TaminotchiQaytarish $supplierReturn)
    {
        $supplierReturn->load(['taminotchi','ombor','filial','xodim','qatorlar.tovar']);
        return view('transfer.supplier_return.show', ['qaytarish' => $supplierReturn]);
    }

    public function tasdiqlash(TaminotchiQaytarish $supplierReturn)
    {
        if ($supplierReturn->holat !== 'qoralama') {
            return back()->with('xato', "Bu qaytarish {$supplierReturn->holat} holatida.");
        }
        DB::transaction(function () use ($supplierReturn) {
            // Ombor qoldig'ini kamaytirish
            foreach ($supplierReturn->qatorlar as $q) {
                if ($q->tovar_id) {
                    $tovar = TovarKatalog::find($q->tovar_id);
                    if ($tovar && $tovar->qoldiq >= $q->miqdor) {
                        $tovar->decrement('qoldiq', $q->miqdor);
                    }
                }
            }
            $supplierReturn->update([
                'holat'          => 'tasdiqlangan',
                'tasdiqlagan_id' => Auth::id(),
            ]);
        });
        return back()->with('muvaffaqiyat', 'Qaytarish tasdiqlandi. Ombor qoldig\'i yangilandi.');
    }

    public function qaytarildi(TaminotchiQaytarish $supplierReturn)
    {
        if ($supplierReturn->holat !== 'tasdiqlangan') {
            return back()->with('xato', 'Avval tasdiqlangan bo\'lishi kerak.');
        }
        $supplierReturn->update(['holat' => 'qaytarildi']);
        return back()->with('muvaffaqiyat', 'Ta\'minotchiga fizik qaytarish tasdiqlandi.');
    }
}
