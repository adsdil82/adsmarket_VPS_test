<?php

return [
    /*
     * Qurilmalar nazorati moduli yoqilganmi?
     * Default: FALSE — ishga tushirish uchun .env ga qo'shing:
     *   DEVICE_CONTROL_ENABLED=true
     */
    'enabled' => env('DEVICE_CONTROL_ENABLED', false),

    /*
     * Avtomatik bloklash yoqilganmi?
     * XAVFLI: Faqat to'liq test qilingandan keyin yoqing!
     * Default: FALSE
     *   DEVICE_CONTROL_AUTO_LOCK_ENABLED=true
     */
    'auto_lock_enabled' => env('DEVICE_CONTROL_AUTO_LOCK_ENABLED', false),

    /*
     * Default provayder kodi (lock/unlock uchun)
     */
    'default_provider' => env('DEVICE_CONTROL_DEFAULT_PROVIDER', 'mock'),

    /*
     * API so'rov timeout (soniyada)
     */
    'api_timeout' => env('DEVICE_CONTROL_API_TIMEOUT', 15),

    /*
     * Unlock job qayta urinishlar soni
     */
    'unlock_max_tries' => 5,

    /*
     * Auto unlock kechikish (soniyada) — to'lovdan keyin qancha kutilsin
     */
    'unlock_delay_seconds' => env('DEVICE_CONTROL_UNLOCK_DELAY', 0),
];
