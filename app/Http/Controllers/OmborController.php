<?php

namespace App\Http\Controllers;

use App\Models\Ombor;
use App\Models\OmborQoldiq;
use App\Models\StockLedger;
use App\Models\TovarKatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OmborController extends Controller
{
    /**
     * Ombor qoldig'i — har bir ombor bo'yicha real tovar zaxirasi.
     * Chap ustunda omborlar (filial bo'yicha), tanlangan ombor uchun
     * o'ng tomonda tovarlar va ularning aniq miqdori ko'rsatiladi.
     */
    public function index(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;

        $omborlar = Ombor::with('filial')
            ->when($filialId, fn($q) => $q->where('filial_id', $filialId))
            ->withCount(['qoldiqlar as tovar_turlari_soni' => fn($q) => $q->where('miqdor', '>', 0)])
            ->orderBy('filial_id')->orderBy('tur')
            ->get();

        $tanlanganOmborId = $request->ombor_id ?: $omborlar->first()?->id;
        $tanlanganOmbor   = $omborlar->firstWhere('id', $tanlanganOmborId);

        $qoldiqlar = collect();
        if ($tanlanganOmbor) {
            $qoldiqlar = OmborQoldiq::with('tovar.guruh')
                ->where('ombor_id', $tanlanganOmbor->id)
                ->when($request->qidiruv, fn($q) => $q->whereHas('tovar', fn($qq) =>
                    $qq->where('nomi', 'like', "%{$request->qidiruv}%")
                ))
                ->when($request->faqat_mavjud, fn($q) => $q->where('miqdor', '>', 0))
                ->orderByDesc('miqdor')
                ->get()
                ->filter(fn($oq) => $oq->tovar); // o'chirilgan tovarlarni chetlab o'tamiz
        }

        $jamiSumma = $qoldiqlar->sum(fn($oq) => $oq->miqdor * ($oq->tovar->tan_narx ?? 0));

        return view('ombor.index', compact('omborlar', 'tanlanganOmbor', 'qoldiqlar', 'jamiSumma'));
    }

    /** Bitta tovarning barcha omborlardagi taqsimoti + so'nggi harakatlari. */
    public function tovar(TovarKatalog $tovar)
    {
        $taqsimot = OmborQoldiq::with('ombor.filial')
            ->where('tovar_id', $tovar->id)
            ->where('miqdor', '>', 0)
            ->orderByDesc('miqdor')
            ->get();

        $harakatlar = StockLedger::with('ombor', 'xodim:id,ism_familiya')
            ->where('tovar_id', $tovar->id)
            ->latest()
            ->limit(50)
            ->get();

        return view('ombor.tovar', compact('tovar', 'taqsimot', 'harakatlar'));
    }
}
