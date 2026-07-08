<?php
namespace App\Http\Controllers;

use App\Models\TovarBarkod;
use App\Models\TovarGuruh;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TovarKatalogController extends Controller
{
    public function __construct(private \App\Services\BarcodeService $barcodeService) {}

    public function index(Request $request)
    {
        $query = TovarKatalog::with('guruh')
            ->when($request->guruh_id, fn($q) => $q->where('guruh_id', $request->guruh_id))
            ->when($request->holat,    fn($q) => $q->where('holat', $request->holat))
            ->when($request->qidiruv,  fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('nomi', 'like', "%{$request->qidiruv}%")
                   ->orWhere('barkod', 'like', "%{$request->qidiruv}%");
            }));

        $tovarlar = $query->orderBy('nomi')->paginate(25)->withQueryString();
        $guruhlar = TovarGuruh::faol()->orderBy('nomi')->get();

        // Ajax (POS uchun)
        if ($request->expectsJson()) {
            return response()->json($tovarlar->map(fn($t) => [
                'id'          => $t->id,
                'nomi'        => $t->nomi,
                'barkod'      => $t->barkod,
                'sotish_narx' => $t->sotish_narx,
                'qoldiq'      => $t->qoldiq,
                'birlik'      => $t->birlik,
            ]));
        }

        return view('ombor.katalog.index', compact('tovarlar', 'guruhlar'));
    }

    public function create()
    {
        $guruhlar = TovarGuruh::faol()->orderBy('nomi')->get();
        return view('ombor.katalog.create', compact('guruhlar'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guruh_id'                => 'nullable|exists:tovar_guruhlar,id',
            'nomi'                    => 'required|string|max:200',
            'barkod'                  => 'nullable|string|max:50|unique:tovar_katalog,barkod',
            'birlik'                  => 'required|string|max:20',
            'tan_narx'                => 'required|numeric|min:0',
            'sotish_narx'             => 'required|numeric|min:0',
            'nasiya_narx'             => 'required|numeric|min:0',
            'min_qoldiq'              => 'nullable|numeric|min:0',
            'holat'                   => 'in:faol,nofaol',
            'izoh'                    => 'nullable|string',
            'qoshimcha_barkodlar'     => 'nullable|array',
            'qoshimcha_barkodlar.*'   => 'nullable|string|max:50|distinct|unique:tovar_barkodlar,barkod|unique:tovar_katalog,barkod',
        ]);

        // Shtrix-kod kiritilmagan bo'lsa — avtomatik EAN-13 generatsiya qilinadi
        // (yangi tovarni darhol chop etish/sotish mumkin bo'lishi uchun).
        $data['barkod'] = $data['barkod'] ?: $this->barcodeService->generate();

        $qoshimchaBarkodlar = array_filter($data['qoshimcha_barkodlar'] ?? []);
        unset($data['qoshimcha_barkodlar']);

        $tovar = TovarKatalog::create($data);
        foreach ($qoshimchaBarkodlar as $barkod) {
            TovarBarkod::create(['tovar_id' => $tovar->id, 'barkod' => $barkod]);
        }

        return redirect()->route('katalog.index')->with('muvaffaqiyat', "Tovar «{$data['nomi']}» qo'shildi.");
    }

    public function edit(TovarKatalog $katalog)
    {
        $guruhlar = TovarGuruh::faol()->orderBy('nomi')->get();
        return view('ombor.katalog.edit', compact('katalog', 'guruhlar'));
    }

    public function update(Request $request, TovarKatalog $katalog)
    {
        $data = $request->validate([
            'guruh_id'                => 'nullable|exists:tovar_guruhlar,id',
            'nomi'                    => 'required|string|max:200',
            'barkod'                  => "nullable|string|max:50|unique:tovar_katalog,barkod,{$katalog->id}",
            'birlik'                  => 'required|string|max:20',
            'tan_narx'                => 'required|numeric|min:0',
            'sotish_narx'             => 'required|numeric|min:0',
            'nasiya_narx'             => 'required|numeric|min:0',
            'min_qoldiq'              => 'nullable|numeric|min:0',
            'holat'                   => 'in:faol,nofaol',
            'izoh'                    => 'nullable|string',
            'qoshimcha_barkodlar'     => 'nullable|array',
            'qoshimcha_barkodlar.*'   => ['nullable', 'string', 'max:50', 'distinct',
                Rule::unique('tovar_barkodlar', 'barkod')->where(fn($q) => $q->where('tovar_id', '!=', $katalog->id)),
                Rule::unique('tovar_katalog', 'barkod')->ignore($katalog->id),
            ],
        ]);

        $qoshimchaBarkodlar = array_filter($data['qoshimcha_barkodlar'] ?? []);
        unset($data['qoshimcha_barkodlar']);

        $katalog->update($data);

        // To'liq ro'yxat qayta yuboriladi — mavjudlarini tozalab qayta yozamiz.
        $katalog->barkodlar()->delete();
        foreach ($qoshimchaBarkodlar as $barkod) {
            TovarBarkod::create(['tovar_id' => $katalog->id, 'barkod' => $barkod]);
        }

        return redirect()->route('katalog.index')->with('muvaffaqiyat', 'Tovar yangilandi.');
    }

    public function destroy(TovarKatalog $katalog)
    {
        if ($katalog->qoldiq > 0) {
            return back()->with('xato', "Omborda {$katalog->qoldiq} {$katalog->birlik} qoldiq bor — o'chirib bo'lmaydi.");
        }
        $katalog->delete();
        return redirect()->route('katalog.index')->with('muvaffaqiyat', 'Tovar o\'chirildi.');
    }
}
