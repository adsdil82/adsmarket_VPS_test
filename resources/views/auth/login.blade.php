<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirish — NasiyaPro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:#f1f3f5; font-size:14px; min-height:100svh; }
        .login-card { max-width:420px; margin:0 auto; padding: 20px 16px 40px; }
        @media (min-width: 480px) { .login-card { margin: 40px auto; } }
        @media (min-width: 768px) { .login-card { margin: 60px auto; } }
        .login-header { border-radius:12px 12px 0 0; padding:24px; }
        .login-body { border-radius:0 0 12px 12px; padding:24px; }
        .form-control, .btn { min-height:42px; }
        .login-header { background:#212529; color:white; border-radius:12px 12px 0 0; padding:30px; text-align:center; }
        .login-body { background:white; border-radius:0 0 12px 12px; padding:32px; box-shadow:0 4px 24px rgba(0,0,0,.1); }
        .captcha-box { background:#f8f9fa; border:2px dashed #adb5bd; border-radius:10px; padding:14px; text-align:center; font-size:26px; font-weight:800; letter-spacing:6px; color:#212529; margin-bottom:8px; user-select:none; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <i class="bi bi-bank2 fs-2 mb-2 d-block" style="color:#ffc107"></i>
        <h4 class="fw-bold mb-0">ADSMARKET</h4>
        <small class="opacity-75">Nasiya boshqaruv tizimi</small>
    </div>
    <div class="login-body">

        @if($errors->any())
        <div class="alert alert-danger py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-medium">Login</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="login"
                           class="form-control @error('login') is-invalid @enderror"
                           value="{{ old('login') }}" placeholder="admin"
                           autocomplete="username" autofocus required>
                </div>
                @error('login')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Parol</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="pwd-inp"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••" autocomplete="current-password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()">
                        <i class="bi bi-eye" id="eye-ico"></i>
                    </button>
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">
                    <i class="bi bi-shield-check me-1 text-success"></i>Xavfsizlik tekshiruvi
                </label>
                <div class="captcha-box">{{ $a }} + {{ $b }} = ?</div>
                <input type="number" name="captcha"
                       class="form-control text-center fw-bold @error('captcha') is-invalid @enderror"
                       placeholder="Javob" min="0" max="20" required style="font-size:18px">
                @error('captcha')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="eslab_qol" id="eslab">
                <label class="form-check-label text-muted small" for="eslab">Meni eslab qol (30 kun)</label>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                <i class="bi bi-box-arrow-in-right me-1"></i>Tizimga kirish
            </button>
        </form>
    </div>
    <p class="text-center text-muted small mt-3">NasiyaPro &copy; {{ date('Y') }}</p>
</div>
<script>
function togglePwd() {
    var i = document.getElementById('pwd-inp');
    var e = document.getElementById('eye-ico');
    i.type = i.type==='password' ? 'text' : 'password';
    e.className = i.type==='password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
