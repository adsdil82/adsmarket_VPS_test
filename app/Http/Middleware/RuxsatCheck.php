<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RuxsatCheck — admin/ruxsatlar sahifasidagi granular (rol+resurs+amal)
 * ruxsat jadvaliga tayanib kirishni tekshiradi. RolCheck'dan farqi: RolCheck
 * qattiq (kod ichida yozilgan) rol ro'yxatini tekshiradi, bu esa admin panelda
 * "Ruxsatlar" sahifasidan sozlanadigan jadvalga (ruxsatlar jadvali) tayanadi —
 * shuning uchun har bir rol uchun alohida yoqish/o'chirish mumkin.
 *
 * Ishlatilishi (route'da):
 *   ->middleware('ruxsat.check:autopay')             // amal=korish (standart)
 *   ->middleware('ruxsat.check:hibrit_pochta,qoshish')
 */
class RuxsatCheck
{
    public function handle(Request $request, Closure $next, string $resurs, string $amal = 'korish'): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->ruxsat($resurs, $amal)) {
            if ($request->expectsJson()) {
                return response()->json(['xato' => "Ruxsat yo'q"], 403);
            }
            abort(403, "Bu bo'limga kirish uchun sizda ruxsat yo'q.");
        }

        return $next($request);
    }
}
