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
];
