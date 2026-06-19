<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TilController extends Controller
{
    private const RUXSAT_ETILGAN = ['uz', 'ru', 'en'];

    /**
     * Foydalanuvchi tilini o'zgartirish va saqlash
     */
    public function ozgartir(Request $request)
    {
        $til = $request->til;

        if (!in_array($til, self::RUXSAT_ETILGAN)) {
            return back()->with('xato', 'Noto\'g\'ri til tanlandi.');
        }

        // 1. DB ga saqlash (login bo'lsa)
        if (Auth::check()) {
            Auth::user()->update(['til' => $til]);
        }

        // 2. Session ga saqlash (hamma vaqt)
        $request->session()->put('til', $til);

        return back();
    }
}
