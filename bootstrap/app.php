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
            'litsenziya.tekshir' => \App\Http\Middleware\LitsenziyaTekshir::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
