<?php

namespace App\Http\Middleware;

use App\Services\Litsenziya;
use Closure;
use Illuminate\Http\Request;

/**
 * Litsenziya muddati tugagan va shu modul faollashtirish kodida "bloklansin"
 * deb belgilangan bo'lsa, foydalanuvchiga chiroyli bloklash sahifasini ko'rsatadi.
 *
 * Ishlatilishi: ->middleware('litsenziya.tekshir:mijoz') — parametr
 * App\Services\Litsenziya::MODULLAR dagi kalitlardan biri bo'lishi kerak.
 */
class LitsenziyaTekshir
{
    public function handle(Request $request, Closure $next, string $modul = 'shartnoma')
    {
        if (Litsenziya::moduleBloklanganmi($modul)) {
            $modulNomi = Litsenziya::MODULLAR[$modul]['nomi'] ?? $modul;

            return response()->view('litsenziya.bloklangan', [
                'modulNomi' => $modulNomi,
                'dukonKodi' => Litsenziya::dukonKodiChiroyli(),
                'muddati' => Litsenziya::muddati(),
                'orqagaUrl' => url()->previous() !== $request->fullUrl() ? url()->previous() : route('dashboard'),
            ], 403);
        }

        return $next($request);
    }
}
