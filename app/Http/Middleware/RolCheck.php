<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RolCheck — Rol asosida kirish huquqini tekshiradi.
 *
 * Ishlatilishi (route'da):
 *   ->middleware('rol.check:admin')          // faqat admin
 *   ->middleware('rol.check:admin,menejer')  // admin yoki menejer
 *
 * Rol ierarxiyasi:
 *   admin > menejer > kassir > hisobchi
 */
class RolCheck
{
    public function handle(Request $request, Closure $next, string ...$rollar): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->rol, $rollar)) {
            if ($request->expectsJson()) {
                return response()->json(['xato' => 'Ruxsat yo\'q'], 403);
            }
            abort(403, 'Bu amalni bajarish uchun sizda ruxsat yo\'q.');
        }

        return $next($request);
    }
}
