<!DOCTYPE html>
<html lang="uz" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kassa terminaliga kirish</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
html, body { height:100%; margin:0; background:linear-gradient(135deg,#0f172a,#1e293b); }
#kirish-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
.kirish-box { width:380px; background:#fff; border-radius:16px; padding:32px; box-shadow:0 20px 60px rgba(0,0,0,.45); text-align:center; }
.kassir-select { width:100%; padding:10px; border-radius:8px; border:1px solid #d7e2f5; font-size:15px; font-weight:600; margin-bottom:16px; }
.pin-dots { display:flex; justify-content:center; gap:12px; margin:18px 0; }
.pin-dot { width:18px; height:18px; border-radius:50%; border:2px solid #93c5fd; }
.pin-dot.filled { background:#1d4ed8; border-color:#1d4ed8; }
.pin-keypad { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:16px; }
.pin-key { padding:18px 0; font-size:22px; font-weight:700; border-radius:12px; border:1px solid #d7e2f5; background:#f5f8fd; cursor:pointer; color:#1e293b; }
.pin-key:hover { background:#e0edff; }
#pin-xato { color:#dc2626; font-size:.85rem; font-weight:700; min-height:20px; margin-top:8px; }
    </style>
</head>
<body>
<div id="kirish-wrap">
    <div class="kirish-box">
        <i class="bi bi-cash-register fs-1 text-primary d-block mb-2"></i>
        <h5 class="fw-bold mb-3">Kassa terminaliga kirish</h5>

        <select id="kassir-select" class="kassir-select">
            <option value="">— Kassirni tanlang —</option>
            @foreach($kassirlar as $k)
            <option value="{{ $k->id }}">{{ $k->ism_familiya }}</option>
            @endforeach
        </select>

        <div class="text-muted small">PIN kodni kiriting</div>
        <div class="pin-dots" id="pin-dots"></div>
        <div id="pin-xato"></div>

        <div class="pin-keypad">
            <button type="button" class="pin-key" onclick="raqam(1)">1</button>
            <button type="button" class="pin-key" onclick="raqam(2)">2</button>
            <button type="button" class="pin-key" onclick="raqam(3)">3</button>
            <button type="button" class="pin-key" onclick="raqam(4)">4</button>
            <button type="button" class="pin-key" onclick="raqam(5)">5</button>
            <button type="button" class="pin-key" onclick="raqam(6)">6</button>
            <button type="button" class="pin-key" onclick="raqam(7)">7</button>
            <button type="button" class="pin-key" onclick="raqam(8)">8</button>
            <button type="button" class="pin-key" onclick="raqam(9)">9</button>
            <button type="button" class="pin-key" onclick="ortga()"><i class="bi bi-backspace"></i></button>
            <button type="button" class="pin-key" onclick="raqam(0)">0</button>
            <button type="button" class="pin-key" onclick="tozala()"><i class="bi bi-x-lg"></i></button>
        </div>

        <a href="{{ route('pos.index') }}" class="d-block small text-muted mt-4">
            <i class="bi bi-arrow-left me-1"></i>Oddiy POS ekraniga qaytish
        </a>
    </div>
</div>

<script>
let pin = '';

function dotlarniYangila() {
    const box = document.getElementById('pin-dots');
    box.innerHTML = '';
    for (let i = 0; i < 6; i++) {
        const dot = document.createElement('div');
        dot.className = 'pin-dot' + (i < pin.length ? ' filled' : '');
        box.appendChild(dot);
    }
}

function raqam(n) {
    if (pin.length >= 6) return;
    pin += n;
    dotlarniYangila();
    document.getElementById('pin-xato').textContent = '';
    if (pin.length >= 4) {
        yuborish();
    }
}
function ortga() { pin = pin.slice(0, -1); dotlarniYangila(); }
function tozala() { pin = ''; dotlarniYangila(); }

async function yuborish() {
    const xodimId = document.getElementById('kassir-select').value;
    if (!xodimId) {
        document.getElementById('pin-xato').textContent = 'Avval kassirni tanlang';
        pin = ''; dotlarniYangila();
        return;
    }

    const res = await fetch('{{ route("terminal.pin-tekshir") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ xodim_id: xodimId, pin: pin }),
    });
    const data = await res.json();

    if (!res.ok || !data.muvaffaqiyat) {
        document.getElementById('pin-xato').textContent = data.xato || "PIN noto'g'ri";
        pin = ''; dotlarniYangila();
        return;
    }

    window.location.href = data.yonalish;
}

document.addEventListener('keydown', e => {
    if (e.key >= '0' && e.key <= '9') raqam(parseInt(e.key));
    if (e.key === 'Backspace') ortga();
});
</script>
</body>
</html>
