<?php
namespace App\Http\Controllers;

use App\Models\TulovTuri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentTypeController extends Controller
{
    public function index(Request $request)
    {
        $yangilar  = TulovTuri::where('is_legacy', false)->orderBy('sort_order')->get();
        $legacylar = TulovTuri::where('is_legacy', true)
            ->when($request->qidiruv, fn($q)=>$q->where('nomi','LIKE',"%{$request->qidiruv}%"))
            ->orderBy('nomi')->paginate(30)->withQueryString();

        $statistika = DB::table('tulovlar as t')
            ->join('tulov_turlari as tt','tt.id','=','t.tulov_turi_id')
            ->selectRaw('tt.id, tt.nomi, tt.is_legacy, COUNT(*) as soni, COALESCE(SUM(t.summa),0) as summa')
            ->groupBy('tt.id','tt.nomi','tt.is_legacy')
            ->orderByDesc('soni')
            ->get()
            ->keyBy('id');

        return view('transfer.payment_types.index', compact('yangilar','legacylar','statistika'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomi'       => 'required|string|max:100',
            'kod'        => 'nullable|string|max:50|unique:tulov_turlari,kod',
            'kategoriya' => 'required|in:naqd,karta,bank,online,terminal,chegirma,tuzatish,oldindan,penya,asosiy_qarz,foiz,boshqa',
            'sort_order' => 'integer|min:1',
        ]);

        TulovTuri::create([
            'nomi'                    => $request->nomi,
            'kod'                     => $request->kod,
            'kategoriya'              => $request->kategoriya,
            'holat'                   => 'faol',
            'is_legacy'               => false,
            'affects_contract_balance'=> $request->boolean('affects_contract_balance', true),
            'affects_cash'            => $request->boolean('affects_cash', true),
            'affects_bank'            => $request->boolean('affects_bank', false),
            'sort_order'              => $request->sort_order ?? 100,
        ]);

        return back()->with('muvaffaqiyat', "To'lov turi qo'shildi.");
    }

    public function update(Request $request, TulovTuri $tulovTuri)
    {
        // Legacy to'lov turini nomini o'zgartirishga ruxsat emas
        if ($tulovTuri->is_legacy && $request->has('nomi')) {
            return back()->with('xato', "Eski (legacy) to'lov turi nomini o'zgartirish mumkin emas. Faqat holat va mapping o'zgartiriladi.");
        }

        $tulovTuri->update($request->only(['holat','sort_order','kategoriya','affects_contract_balance','affects_cash','affects_bank']));
        return back()->with('muvaffaqiyat', "To'lov turi yangilandi.");
    }

    /** Legacy → Yangi mapping */
    public function mappingStore(Request $request)
    {
        $request->validate([
            'legacy_id' => 'required|exists:tulov_turlari,id',
            'yangi_id'  => 'required|exists:tulov_turlari,id|different:legacy_id',
            'izoh'      => 'nullable|string|max:200',
        ]);

        DB::table('tulov_turi_mapping')->updateOrInsert(
            ['legacy_id' => $request->legacy_id],
            ['yangi_id' => $request->yangi_id, 'izoh' => $request->izoh, 'updated_at' => now()]
        );

        return back()->with('muvaffaqiyat', 'Mapping saqlandi.');
    }
}
