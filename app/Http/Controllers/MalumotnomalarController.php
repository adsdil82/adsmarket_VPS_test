<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class MalumotnomalarController extends Controller
{
    public function index()
    {
        $stats = [
            'filiallar'              => DB::table('filiallar')->count(),
            'foydalanuvchilar'       => DB::table('foydalanuvchilar')->count(),
            'kassalar'               => DB::table('kassalar')->count(),
            'tovar_guruhlar'         => DB::table('tovar_guruhlar')->count(),
            'tovar_katalog'          => DB::table('tovar_katalog')->count(),
            'birliklar'              => DB::table('birliklar')->count(),
            'tulov_turlari'          => DB::table('yangi_tulov_turlari')->count(),
            'harajat_turlari'        => DB::table('harajat_turlari')->count(),
            'pul_kategoriyalar'      => DB::table('pul_kategoriyalar')->count(),
            'hisoblar_rejasi'        => DB::table('hisoblar_rejasi')->count(),
            'notification_templates' => DB::table('notification_templates')->count(),
            'qurilma_provayderlar'   => DB::table('qurilma_provayderlar')->count(),
        ];
        return view('malumotnamalar.index', compact('stats'));
    }
}
