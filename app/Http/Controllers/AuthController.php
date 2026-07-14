<?php

namespace App\Http\Controllers;

use App\Models\Foydalanuvchi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        // Captcha raqamlar yaratish (1-10 oralig'ida)
        $a = rand(1, 10);
        $b = rand(1, 10);
        session(['captcha_a' => $a, 'captcha_b' => $b, 'captcha_ans' => $a + $b]);
        return view('auth.login', compact('a', 'b'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
            'captcha'  => ['required', 'integer'],
        ], [
            'login.required'    => 'Login kiritilishi shart.',
            'password.required' => 'Parol kiritilishi shart.',
            'captcha.required'  => 'Captcha javobini kiriting.',
        ]);

        // Captcha tekshirish
        if ((int)$request->captcha !== (int)session('captcha_ans')) {
            $a = rand(1, 10); $b = rand(1, 10);
            session(['captcha_a' => $a, 'captcha_b' => $b, 'captcha_ans' => $a + $b]);
            throw ValidationException::withMessages([
                'captcha' => 'Captcha xato. Qayta urinib ko\'ring.',
            ]);
        }

        // Login yoki email orqali topish
        $user = Foydalanuvchi::where(function($q) use ($request) {
                $q->where('login', $request->login)
                  ->orWhere('email', $request->login);
            })
            ->where('holat', 'faol')
            ->where('tizimga_kirish_bormi', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $a = rand(1, 10); $b = rand(1, 10);
            session(['captcha_a' => $a, 'captcha_b' => $b, 'captcha_ans' => $a + $b]);
            throw ValidationException::withMessages([
                'login' => 'Login yoki parol noto\'g\'ri.',
            ]);
        }

        Auth::login($user, $request->boolean('eslab_qol'));
        $request->session()->regenerate();
        session()->forget(['captcha_a', 'captcha_b', 'captcha_ans']);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function profil()
    {
        return view('auth.profil', ['user' => Auth::user()]);
    }

    public function parolOzgartirish(Request $request)
    {
        $request->validate([
            'joriy_parol' => ['required'],
            'yangi_parol' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        if (!Hash::check($request->joriy_parol, $user->password)) {
            return back()->withErrors(['joriy_parol' => 'Joriy parol noto\'g\'ri.']);
        }

        $user->update(['password' => Hash::make($request->yangi_parol)]);
        return back()->with('muvaffaqiyat', 'Parol muvaffaqiyatli o\'zgartirildi.');
    }
}
