<?php

namespace App\Http\Middleware;

use App\Services\OperatsionKunService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OperatsionKunTekshirish — foydalanuvchining filiali uchun joriy operatsion
 * kun yopiq bo'lsa, yozish (to'lov qabul qilish, shartnoma kiritish/tahrirlash
 * kabi) amallarini bloklaydi. Hisobot/ko'rish route'lariga qo'shilmaydi.
 *
 * Admin yoki 'operatsion_kun','eski_tahrirlash' ruxsati bor foydalanuvchi
 * uchun cheklov qo'llanilmaydi.
 */
class OperatsionKunTekshirish
{
    public function __construct(private OperatsionKunService $svc) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isAdmin() || $user->ruxsat('operatsion_kun', 'eski_tahrirlash')) {
            return $next($request);
        }

        $filialId = $user->filial_id;

        if (!$filialId) {
            return $next($request);
        }

        $kun = cache()->remember("operatsion_kun_{$filialId}", 60, function () use ($filialId) {
            return $this->svc->joriyKun($filialId);
        });

        if ($kun->status === 'yopiq') {
            $xabar = 'Operatsion kun yopiq. Amal bajarilmaydi.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $xabar], 423);
            }

            return back()->with('xato', $xabar);
        }

        return $next($request);
    }
}
