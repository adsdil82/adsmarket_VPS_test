<?php

namespace App\Http\Controllers;

use App\Models\RegKredit;
use App\Models\ShartnomavVersioniya;
use Illuminate\Support\Facades\Auth;

class VersionController extends Controller
{
    /** Shartnoma versiyalari ro'yxati */
    public function index(RegKredit $kredit)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->filial_id !== $kredit->filial_id) {
            abort(403);
        }

        $versiyalar = $kredit->versiyalar()->with('xodim')->get();

        return view('versiyalar.index', compact('kredit', 'versiyalar'));
    }

    /** Versiya batafsil ko'rish */
    public function show(RegKredit $kredit, ShartnomavVersioniya $versiya)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $user->filial_id !== $kredit->filial_id) {
            abort(403);
        }

        if ($versiya->reg_kredit_id !== $kredit->id) {
            abort(404);
        }

        $versiya->load('xodim');

        return view('versiyalar.show', compact('kredit', 'versiya'));
    }
}
