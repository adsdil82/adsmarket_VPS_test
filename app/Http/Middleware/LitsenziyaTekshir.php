<?php

namespace App\Http\Middleware;

use App\Services\Litsenziya;
use Closure;
use Illuminate\Http\Request;

/** Yangi mijoz/tovar/shartnoma qo'shishni litsenziya muddati tugaganda bloklaydi */
class LitsenziyaTekshir
{
    public function handle(Request $request, Closure $next)
    {
        if (Litsenziya::bloklanganmi()) {
            return back()->withErrors([
                'litsenziya' => "Litsenziya muddati tugagan. Yangi mijoz/tovar/shartnoma qo'shish vaqtincha to'xtatilgan. "
                    . "Faollashtirish uchun \"Litsenziya\" bo'limiga o'ting.",
            ])->withInput();
        }

        return $next($request);
    }
}
