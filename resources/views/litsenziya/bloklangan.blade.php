<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Litsenziya muddati tugagan</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    body {
        min-height: 100vh;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at top left, #1e293b 0%, #0f172a 60%, #020617 100%);
        font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
        padding: 24px;
    }
    .blok-karta {
        max-width: 540px;
        width: 100%;
        background: #fff;
        border-radius: 20px;
        padding: 40px 36px;
        box-shadow: 0 25px 60px rgba(0,0,0,.35);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .blok-karta::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 6px;
        background: linear-gradient(90deg, #f59e0b, #ef4444, #f59e0b);
    }
    .qulf-doira {
        width: 84px; height: 84px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 8px 24px rgba(245,158,11,.35);
    }
    .qulf-doira i { font-size: 38px; color: #d97706; }
    h1 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
    .modul-belgi {
        display: inline-block;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        padding: 4px 14px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 18px;
    }
    .izoh { color: #64748b; font-size: 14.5px; line-height: 1.6; margin-bottom: 24px; }
    .muddat-info {
        background: #f8fafc;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 24px;
        font-size: 13.5px;
        color: #475569;
    }
    .aloqa-sarlavha {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .aloqa-tugma {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        text-decoration: none;
        color: #1e293b;
        font-weight: 600;
        font-size: 14.5px;
        margin-bottom: 10px;
        transition: all .15s;
    }
    .aloqa-tugma:hover { border-color: #6366f1; background: #f5f5ff; transform: translateY(-1px); }
    .aloqa-tugma .ico {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: #fff;
        flex-shrink: 0;
    }
    .ico-tg { background: #229ed9; }
    .ico-tel { background: #16a34a; }
    .ico-mail { background: #ef4444; }
    .aloqa-detail { font-size: 12px; color: #94a3b8; font-weight: 500; }
    hr.ajratuvchi { margin: 24px 0; border-color: #f1f5f9; }
    .dukon-blok {
        background: #f8fafc;
        border-radius: 14px;
        padding: 18px;
    }
    .dukon-blok .label { font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 8px; }
    .dukon-kod {
        font-family: 'Courier New', monospace;
        font-size: 19px;
        font-weight: 800;
        color: #4f46e5;
        background: #fff;
        border: 1px dashed #c7d2fe;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 12px;
        letter-spacing: 1px;
    }
    .dukon-tugmalar { display: flex; gap: 8px; }
    .dukon-tugmalar button, .dukon-tugmalar a {
        flex: 1; padding: 9px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .btn-nusxa { background: #e2e8f0; color: #334155; }
    .btn-nusxa:hover { background: #cbd5e1; }
    .btn-tg-yubor { background: #229ed9; color: #fff; }
    .btn-tg-yubor:hover { background: #1c87bd; color: #fff; }
    .toast-xabar {
        position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
        background: #1e293b; color: #fff; padding: 10px 20px; border-radius: 10px;
        font-size: 13px; opacity: 0; transition: opacity .2s; pointer-events: none;
    }
    .toast-xabar.korinadi { opacity: 1; }
    .yopish-x {
        position: absolute;
        top: 14px; right: 14px;
        width: 32px; height: 32px;
        border-radius: 50%;
        background: #f1f5f9;
        border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        color: #64748b;
        font-size: 16px;
        transition: all .15s;
    }
    .yopish-x:hover { background: #e2e8f0; color: #1e293b; }
    .orqaga-tugma {
        display: block;
        width: 100%;
        margin-top: 16px;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 14px;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
    }
    .orqaga-tugma:hover { background: #f1f5f9; color: #1e293b; }
</style>
</head>
<body>

<div class="blok-karta">
    <button type="button" class="yopish-x" onclick="orqagaQayt()" title="Yopish">
        <i class="bi bi-x-lg"></i>
    </button>
    <div class="qulf-doira"><i class="bi bi-lock-fill"></i></div>
    <div class="modul-belgi">{{ $modulNomi }} — bloklangan</div>
    <h1>Litsenziya muddati tugagan</h1>
    <div class="izoh">
        Dasturdan to'liq foydalanishni davom ettirish uchun litsenziya muddatini yangilang.
        Quyidagi aloqa orqali administratorga murojaat qiling — sizga yangi faollashtirish
        kodi yuboriladi.
    </div>

    @if($muddati)
    <div class="muddat-info">
        <i class="bi bi-calendar-x me-1"></i>
        Litsenziya muddati <b>{{ $muddati->format('d.m.Y') }}</b>da tugagan
    </div>
    @endif

    <div class="aloqa-sarlavha">Administrator bilan bog'lanish</div>

    <a href="https://t.me/ads253" target="_blank" class="aloqa-tugma">
        <span class="ico ico-tg"><i class="bi bi-telegram"></i></span>
        <span>
            Telegram<br>
            <span class="aloqa-detail">@ads253</span>
        </span>
    </a>
    <a href="tel:+998945510600" class="aloqa-tugma">
        <span class="ico ico-tel"><i class="bi bi-telephone-fill"></i></span>
        <span>
            Telefon<br>
            <span class="aloqa-detail">+998 94 551 06 00</span>
        </span>
    </a>
    <a href="mailto:adilshod.82@gmail.com?subject=Litsenziya%20faollashtirish&body=Salom!%20Dokon%20kodim%3A%20{{ urlencode($dukonKodi) }}" class="aloqa-tugma">
        <span class="ico ico-mail"><i class="bi bi-envelope-fill"></i></span>
        <span>
            Email<br>
            <span class="aloqa-detail">adilshod.82@gmail.com</span>
        </span>
    </a>

    <hr class="ajratuvchi">

    <div class="dukon-blok">
        <div class="label">Do'kon kodingiz</div>
        <div class="dukon-kod" id="dukon-kodi">{{ $dukonKodi }}</div>
        <div class="dukon-tugmalar">
            <button type="button" class="btn-nusxa" onclick="kodNusxa()">
                <i class="bi bi-clipboard"></i> Nusxa olish
            </button>
            <a href="#" class="btn-tg-yubor" onclick="telegramgaYubor(event)">
                <i class="bi bi-send-fill"></i> Telegram orqali yuborish
            </a>
        </div>
    </div>

    <a href="{{ $orqagaUrl }}" class="orqaga-tugma" onclick="return orqagaQayt(event)">
        <i class="bi bi-arrow-left"></i> Yopish va orqaga qaytish
    </a>
</div>

<div class="toast-xabar" id="toast">Nusxalandi!</div>

<script>
function toastKorsat(matn) {
    const t = document.getElementById('toast');
    t.textContent = matn;
    t.classList.add('korinadi');
    setTimeout(() => t.classList.remove('korinadi'), 2000);
}

function kodNusxa() {
    const kod = document.getElementById('dukon-kodi').textContent.trim();
    navigator.clipboard.writeText(kod).then(() => toastKorsat('Kod nusxalandi!'));
}

function orqagaQayt(e) {
    if (e) e.preventDefault();
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '{{ $orqagaUrl }}';
    }
    return false;
}

function telegramgaYubor(e) {
    e.preventDefault();
    const kod = document.getElementById('dukon-kodi').textContent.trim();
    const xabar = `Salom! Litsenziya muddati tugadi. Do'kon kodim: ${kod}`;
    navigator.clipboard.writeText(xabar).then(() => {
        toastKorsat('Xabar nusxalandi — Telegramda joylashtiring (Ctrl+V)');
        window.open('https://t.me/ads253', '_blank');
    });
}
</script>

</body>
</html>
