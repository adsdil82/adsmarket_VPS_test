<?php
namespace App\Http\Controllers;

use App\Models\FilialTransfer;
use App\Models\KassaTransfer;
use App\Models\ShartnomaxodimTarixi;
use App\Models\ShartnomaiFilialTarixi;
use App\Models\TaminotchiQaytarish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Transferlar moduli — bosh sahifa va umumiy audit jurnal
 */
class TransferHubController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;

        // So'nggi 7 kunlik statistika
        $stats = [
            'tovar_transfer'    => FilialTransfer::where('created_at', '>=', now()->subDays(7))->count(),
            'kassa_transfer'    => KassaTransfer::where('created_at', '>=', now()->subDays(7))->count(),
            'xodim_tayinlash'   => ShartnomaxodimTarixi::where('created_at', '>=', now()->subDays(7))->count(),
            'filial_kochirish'  => ShartnomaiFilialTarixi::where('created_at', '>=', now()->subDays(7))->count(),
            'supplier_return'   => TaminotchiQaytarish::where('created_at', '>=', now()->subDays(7))->count(),
            'kutilayotgan_tovar'=> FilialTransfer::where('holat','yuborildi')
                ->when($filialId, fn($q)=>$q->where('to_filial_id',$filialId))->count(),
            'kutilayotgan_kassa'=> KassaTransfer::where('holat','yuborildi')
                ->when($filialId, fn($q)=>$q->where('to_filial_id',$filialId))->count(),
        ];

        // Oxirgi harakatlar
        $oxirgi = collect();

        FilialTransfer::with(['fromFilial','toFilial'])->latest()->limit(5)->get()
            ->each(fn($t) => $oxirgi->push([
                'tur'=>'Tovar transfer','rang'=>'warning',
                'tavsif'=>"({$t->fromFilial->kod}→{$t->toFilial->kod})",
                'holat'=>$t->holat,'holat_rangi'=>$t->holat_rangi,
                'sana'=>$t->created_at,'url'=>route('transfer.tovar.show',$t),
            ]));

        KassaTransfer::with(['fromFilial','toFilial'])->latest()->limit(5)->get()
            ->each(fn($t) => $oxirgi->push([
                'tur'=>'Kassa transfer','rang'=>'primary',
                'tavsif'=>number_format($t->summa,0,'.',' ')." so'm ({$t->fromFilial->kod}→{$t->toFilial->kod})",
                'holat'=>$t->holat,'holat_rangi'=>$t->holat_rangi,
                'sana'=>$t->created_at,'url'=>route('transfer.kassa.show',$t),
            ]));

        $oxirgi = $oxirgi->sortByDesc('sana')->take(10)->values();

        return view('transfer.index', compact('stats','oxirgi'));
    }

    /** Umumiy audit jurnal — barcha transfer turlari */
    public function auditJurnal(Request $request)
    {
        $user     = Auth::user();
        $filialId = $user->isAdmin() ? null : $user->filial_id;
        $tur      = $request->tur ?? 'barchasi';

        $tovar = FilialTransfer::with(['fromFilial','toFilial','xodim'])
            ->when($filialId, fn($q)=>$q->where('from_filial_id',$filialId)->orWhere('to_filial_id',$filialId))
            ->when($request->holat, fn($q)=>$q->where('holat',$request->holat))
            ->when($request->dan_sana, fn($q)=>$q->whereDate('created_at','>=',$request->dan_sana))
            ->when($request->gacha_sana, fn($q)=>$q->whereDate('created_at','<=',$request->gacha_sana))
            ->latest()->limit(50)->get();

        $kassa = KassaTransfer::with(['fromFilial','toFilial','fromKassa','toKassa','xodim'])
            ->when($filialId, fn($q)=>$q->where('from_filial_id',$filialId)->orWhere('to_filial_id',$filialId))
            ->when($request->holat, fn($q)=>$q->where('holat',$request->holat))
            ->when($request->dan_sana, fn($q)=>$q->whereDate('created_at','>=',$request->dan_sana))
            ->when($request->gacha_sana, fn($q)=>$q->whereDate('created_at','<=',$request->gacha_sana))
            ->latest()->limit(50)->get();

        $xodimTayinlash = ShartnomaxodimTarixi::with(['shartnoma:id,shartnoma_raqam','eskiXodim:id,ism_familiya','yangiXodim:id,ism_familiya'])
            ->when($request->dan_sana, fn($q)=>$q->whereDate('created_at','>=',$request->dan_sana))
            ->latest()->limit(30)->get();

        $filialKochirish = ShartnomaiFilialTarixi::with(['shartnoma:id,shartnoma_raqam','eskiFilial:id,kod','yangiFilial:id,kod'])
            ->latest()->limit(30)->get();

        return view('transfer.audit', compact('tovar','kassa','xodimTayinlash','filialKochirish','tur'));
    }
}
