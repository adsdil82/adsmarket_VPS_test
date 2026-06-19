<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FilialCheck — Foydalanuvchi faqat o'z filialidagi ma'lumotlarni ko'radi.
 *
 * Admin — barcha filiallarni ko'radi (filial_id = null).
 * Boshqalar — faqat o'z filialini ko'radi.
 *
 * Route parametrida {filial_id} bo'lsa, shu ID foydalanuvchi filialiga mos bo'lishi kerak.
 */
class FilialCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin — cheklов yo'q
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Agar route'da filial_id bor bo'lsa tekshirish
        $routeFilialId = $request->route('filial_id');
        if ($routeFilialId && (int)$routeFilialId !== (int)$user->filial_id) {
            abort(403, 'Siz faqat o\'z filialingiz ma\'lumotlarini ko\'ra olasiz.');
        }

        return $next($request);
    }
}
