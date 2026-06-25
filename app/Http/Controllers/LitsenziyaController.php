<?php

namespace App\Http\Controllers;

use App\Services\Litsenziya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LitsenziyaController extends Controller
{
    public function index()
    {
        return view('admin.litsenziya', [
            'dukonKodi'  => Litsenziya::dukonKodiChiroyli(),
            'holati'     => Litsenziya::holati(),
            'muddati'    => Litsenziya::muddati(),
            'qolganKun'  => Litsenziya::qolganKun(),
            'yoqilganmi' => Litsenziya::yoqilganmi(),
        ]);
    }

    public function faollashtir(Request $request)
    {
        $data = $request->validate([
            'kod' => 'required|string|max:40',
        ]);

        $natija = Litsenziya::faollashtir($data['kod'], Auth::id());

        if (!$natija['ok']) {
            return back()->withErrors(['kod' => $natija['xabar']]);
        }

        return back()->with('muvaffaqiyat', $natija['xabar']);
    }
}
