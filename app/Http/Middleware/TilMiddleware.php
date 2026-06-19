<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;

class TilMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Autentifikatsiya qilingan foydalanuvchining tili
        if (Auth::check()) {
            $til = Auth::user()->til ?? 'uz';
            App::setLocale($til);
            return $next($request);
        }

        // 2. Session dan (login qilgunga qadar)
        $til = $request->session()->get('til', 'uz');
        App::setLocale($til);

        return $next($request);
    }
}
