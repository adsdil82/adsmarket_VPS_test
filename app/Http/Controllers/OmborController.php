<?php

namespace App\Http\Controllers;

use App\Models\Tovar;
use Illuminate\Support\Facades\DB;

class OmborController extends Controller
{
    public function index()
    {
        $tovarlar = Tovar::select(
                'nomi',
                DB::raw('SUM(soni) as jami_soni'),
                DB::raw('AVG(narx) as ort_narx'),
                DB::raw('SUM(jami_narx) as jami_summa')
            )
            ->groupBy('nomi')
            ->orderByDesc('jami_summa')
            ->limit(200)
            ->get();

        $umumiy_soni  = Tovar::sum('soni');
        $umumiy_narx  = Tovar::sum('jami_narx');
        $tovar_turlari = Tovar::distinct('nomi')->count('nomi');

        return view('ombor.index', compact(
            'tovarlar', 'umumiy_soni', 'umumiy_narx', 'tovar_turlari'
        ));
    }

    public function kirim()
    {
        return view('ombor.kirim');
    }

    public function chiqim()
    {
        $tovarlar = Tovar::with('kredit:id,shartnoma_raqam')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('ombor.chiqim', compact('tovarlar'));
    }
}
