<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Rol tekshiruv middleware larini ro'yxatdan o'tkazish
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'filial.check'      => \App\Http\Middleware\FilialCheck::class,
            'rol.check'         => \App\Http\Middleware\RolCheck::class,
            'ruxsat.check'      => \App\Http\Middleware\RuxsatCheck::class,
            'litsenziya.tekshir' => \App\Http\Middleware\LitsenziyaTekshir::class,
            'litsenziya.limit' => \App\Http\Middleware\LitsenziyaLimitTekshir::class,
        ]);

        // AutoPay serverlari o'z tomonidan to'g'ridan-to'g'ri POST qiladi —
        // sessiya/CSRF tokeni yo'q, Bearer token orqali autentifikatsiya qilinadi.
        $middleware->validateCsrfTokens(except: [
            'autopay/webhook',
            'autopay/verify',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
