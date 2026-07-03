<?php

return [
    /*
     * Har bir o'rnatish uchun ALOHIDA bo'lishi shart bo'lgan maxfiy kalit.
     * .env faylida saqlanadi (git'ga tushmaydi). Agar bu kalit bo'sh bo'lsa,
     * litsenziya tizimi DEVOLOPMENT rejimida ishlaydi (hech narsa bloklanmaydi) —
     * shu orqali lokal/test muhitlarda ishlash qulay bo'ladi.
     */
    'maxfiy_kalit' => env('LITSENZIYA_MAXFIY_KALIT', ''),

    // Muddat tugagandan keyin necha kun "yengillik muddati" (faqat ogohlantirish, blok yo'q)
    'yengillik_kun' => 7,

    // Muddat tugashidan necha kun oldin ogohlantirish banneri ko'rinishini boshlaydi
    'ogohlantirish_kun' => 14,

    /*
     * Tariflar — har biri faollashtirish kodida 1 ta hex raqam bilan kodlanadi
     * (App\Services\Litsenziya::TARIF_IDLAR). Limit qiymatlari null = cheklovsiz.
     * Bular faqat litsenziya FAOL bo'lganda qo'llaniladi (yoqilganmi()=false bo'lsa,
     * hech qanday limit ishlamaydi).
     */
    'tariflar' => [
        'maxsus' => [
            'nomi' => 'Maxsus',
            'mijoz_max' => null,
            'tovar_max' => null,
            'shartnoma_max' => null,
            'pos' => true,
            'hisobot_cheklangan' => false,
        ],
        'demo' => [
            'nomi' => 'Demo (sinov)',
            'mijoz_max' => 20,
            'tovar_max' => 50,
            'shartnoma_max' => 20,
            'pos' => false,
            'hisobot_cheklangan' => true,
        ],
        'yengil' => [
            'nomi' => 'Yengil',
            'mijoz_max' => null,
            'tovar_max' => null,
            'shartnoma_max' => null,
            'pos' => true,
            'hisobot_cheklangan' => false,
        ],
        'premium' => [
            'nomi' => 'Premium',
            'mijoz_max' => null,
            'tovar_max' => null,
            'shartnoma_max' => null,
            'pos' => true,
            'hisobot_cheklangan' => false,
        ],
    ],
];
