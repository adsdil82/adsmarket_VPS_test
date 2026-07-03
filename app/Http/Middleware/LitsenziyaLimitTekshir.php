<?php

namespace App\Http\Middleware;

use App\Services\Litsenziya;
use Closure;
use Illuminate\Http\Request;

/**
 * Tarif bo'yicha SON limiti (mijoz/tovar/shartnoma) yoki MODUL ruxsati (pos, hisobot_advanced)
 * tekshiruvi — litsenziya muddati FAOL bo'lsa ham qo'llanadi (LitsenziyaTekshir esa faqat
 * muddat TUGAGANDA ishlaydi — bu ikkisi mustaqil mexanizmlar).
 *
 * Ishlatilishi: ->middleware('litsenziya.limit:mijoz_max') yoki ->middleware('litsenziya.limit:pos')
 */
class LitsenziyaLimitTekshir
{
    public function handle(Request $request, Closure $next, string $tur)
    {
        $sabab = null;

        if (in_array($tur, ['mijoz_max', 'tovar_max', 'shartnoma_max'], true)) {
            $modul = substr($tur, 0, -4);
            if (Litsenziya::limitOshganmi($modul)) {
                $sabab = [
                    'sarlavha' => Litsenziya::MODULLAR[$modul]['nomi'] ?? $modul,
                    'matn' => "Joriy tarifingizda {$modul} soni limiti (" . Litsenziya::limitQiymati($modul) . " ta) ga yetdingiz. "
                        . "Tarifingizni kengaytirish uchun administratorga murojaat qiling.",
                ];
            }
        } elseif ($tur === 'pos') {
            if (!Litsenziya::posOchiqmi()) {
                $sabab = [
                    'sarlavha' => 'Naqd sotish (POS)',
                    'matn' => "Joriy tarifingizda naqd sotish (POS) bo'limi yopiq. Bu funksiyani ochish uchun "
                        . "tarifingizni kengaytirish kerak.",
                ];
            }
        } elseif ($tur === 'hisobot_advanced') {
            if (Litsenziya::hisobotCheklanganmi()) {
                $sabab = [
                    'sarlavha' => 'Kengaytirilgan hisobotlar',
                    'matn' => "Joriy tarifingizda faqat asosiy hisobotlar mavjud. Konstruktor, Excel eksport va "
                        . "kechikish tahlili kabi kengaytirilgan hisobotlar uchun tarifingizni kengaytiring.",
                ];
            }
        }

        if ($sabab) {
            return response()->view('litsenziya.cheklov', [
                'sarlavha' => $sabab['sarlavha'],
                'matn' => $sabab['matn'],
                'dukonKodi' => Litsenziya::dukonKodiChiroyli(),
                'orqagaUrl' => url()->previous() !== $request->fullUrl() ? url()->previous() : route('dashboard'),
            ], 403);
        }

        return $next($request);
    }
}
