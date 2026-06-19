<?php
namespace App\Http\Controllers;

use App\Models\Filial;
use App\Models\Foydalanuvchi;
use App\Models\RegKredit;
use App\Models\ShartnomaxodimTarixi;
use App\Models\ShartnomaiFilialTarixi;
use App\Services\ContractTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractReassignController extends Controller
{
    public function __construct(private ContractTransferService $service) {}

    // ── Xodim qayta tayinlash ────────────────────────────────────

    public function xodimIndex(Request $request)
    {
        $tarixi = ShartnomaxodimTarixi::with([
            'shartnoma:id,shartnoma_raqam,mijoz_id',
            'shartnoma.mijoz:id,familiya,ism',
            'eskiXodim:id,ism_familiya',
            'yangiXodim:id,ism_familiya',
            'ozgartirgan:id,ism_familiya',
        ])
        ->when($request->dan_sana,   fn($q)=>$q->whereDate('created_at','>=',$request->dan_sana))
        ->when($request->gacha_sana, fn($q)=>$q->whereDate('created_at','<=',$request->gacha_sana))
        ->latest()->paginate(30)->withQueryString();

        return view('transfer.shartnoma.xodim_tarixi', compact('tarixi'));
    }

    public function xodimQaytaTayin(Request $request)
    {
        $request->validate([
            'shartnoma_id'   => 'required|exists:reg_kredit,id',
            'yangi_xodim_id' => 'required|exists:foydalanuvchilar,id',
            'sabab'          => 'required|string|min:5|max:500',
            'izoh'           => 'nullable|string|max:500',
        ]);

        $shartnoma = RegKredit::findOrFail($request->shartnoma_id);

        try {
            $this->service->xodimniQaytaTayin($shartnoma, $request->yangi_xodim_id, $request->sabab, $request->izoh);
            return back()->with('muvaffaqiyat', 'Shartnoma yangi xodimga tayinlandi.');
        } catch (\Exception $e) {
            return back()->with('xato', $e->getMessage());
        }
    }

    // ── Filial ko'chirish ────────────────────────────────────────

    public function filialIndex(Request $request)
    {
        $tarixi = ShartnomaiFilialTarixi::with([
            'shartnoma:id,shartnoma_raqam,mijoz_id',
            'shartnoma.mijoz:id,familiya,ism',
            'eskiFilial:id,kod,nomi',
            'yangiFilial:id,kod,nomi',
            'ozgartirgan:id,ism_familiya',
        ])
        ->when($request->dan_sana,   fn($q)=>$q->whereDate('created_at','>=',$request->dan_sana))
        ->when($request->gacha_sana, fn($q)=>$q->whereDate('created_at','<=',$request->gacha_sana))
        ->latest()->paginate(30)->withQueryString();

        return view('transfer.shartnoma.filial_tarixi', compact('tarixi'));
    }

    public function filialKochirish(Request $request)
    {
        $request->validate([
            'shartnoma_id'            => 'required|exists:reg_kredit,id',
            'yangi_filial_id'         => 'required|exists:filiallar,id',
            'sabab'                   => 'required|string|min:10|max:500',
            'izoh'                    => 'nullable|string|max:500',
            'tolovlar_yangi_filialda' => 'boolean',
        ]);

        $shartnoma = RegKredit::with('mijoz','filial')->findOrFail($request->shartnoma_id);

        try {
            $this->service->filialgaKochirish(
                $shartnoma,
                $request->yangi_filial_id,
                $request->sabab,
                $request->boolean('tolovlar_yangi_filialda'),
                $request->izoh
            );
            return back()->with('muvaffaqiyat', 'Shartnoma boshqa filialga ko\'chirildi.');
        } catch (\Exception $e) {
            return back()->with('xato', $e->getMessage());
        }
    }

    /** Shartnoma kartochkasidan tezkor tayinlash — AJAX */
    public function ajaxXodimTayin(Request $request, RegKredit $kredit)
    {
        $request->validate([
            'yangi_xodim_id' => 'required|exists:foydalanuvchilar,id',
            'sabab'          => 'required|string|min:5',
        ]);
        try {
            $this->service->xodimniQaytaTayin($kredit, $request->yangi_xodim_id, $request->sabab);
            return response()->json(['muvaffaqiyat' => true]);
        } catch (\Exception $e) {
            return response()->json(['xato' => $e->getMessage()], 422);
        }
    }

    /** Shartnoma kartochkasidan tezkor filial ko'chirish — AJAX */
    public function ajaxFilialKochir(Request $request, RegKredit $kredit)
    {
        $request->validate([
            'yangi_filial_id' => 'required|exists:filiallar,id',
            'sabab'           => 'required|string|min:10',
        ]);
        try {
            $this->service->filialgaKochirish($kredit, $request->yangi_filial_id, $request->sabab, false, null);
            return response()->json(['muvaffaqiyat' => true]);
        } catch (\Exception $e) {
            return response()->json(['xato' => $e->getMessage()], 422);
        }
    }
}
