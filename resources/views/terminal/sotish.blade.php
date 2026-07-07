<!DOCTYPE html>
<html lang="uz" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kassa POS — {{ $smena->filial->nomi ?? '' }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
html, body { overflow: hidden; height:100%; margin:0; background:#eef2f7; }
#smena-bar {
    height: 42px; display: flex; align-items: center; justify-content: space-between;
    padding: 0 16px; background: linear-gradient(90deg,#14532d,#15803d);
    color: #fff; font-size: .82rem; font-weight: 600;
}
#smena-bar .btn { padding: 3px 12px; font-size: .78rem; }
#pos-wrap { height: calc(100vh - 42px); display: flex; gap: 10px; padding: 10px; background: #eef2f7; box-sizing: border-box; }

#panel-tovarlar { width: 62%; display: flex; flex-direction: column; background: #fff; border: 1px solid #d7e2f5; border-radius: 10px; box-shadow: 0 2px 8px rgba(30,58,138,.08); overflow: hidden; }
#tovar-search-bar { padding: 8px 12px; background: linear-gradient(90deg,#1e3a8a,#1d4ed8); border-bottom: none; }
#tovar-search-bar .input-group-text { background:#fff; border:none; }
#tovar-search-bar .form-control { border:none; }
#guruh-tabs { display: flex; gap: 6px; padding: 6px 12px; overflow-x: auto; background: #eef3ff; border-bottom: 1px solid #d7e2f5; flex-shrink: 0; }
.guruh-btn { border: 1px solid #93c5fd; border-radius: 20px; padding: 5px 14px; font-size: 13px; cursor: pointer; white-space: nowrap; background: white; color:#1e3a8a; font-weight:600; transition: all .2s; }
.guruh-btn.active { background: linear-gradient(90deg,#1e3a8a,#1d4ed8); border-color: #1d4ed8; color:#fff; font-weight: 700; }

#tovar-table-wrap { flex: 1; overflow-y: auto; background:#fff; }
.bank-table { border-collapse: collapse; font-size: .85rem; width: 100%; }
.bank-table thead { position: sticky; top: 0; z-index: 5; }
.bank-table thead th { background: linear-gradient(180deg,#2563eb,#1d4ed8); color: #fff; font-weight: 700; font-size: .7rem; letter-spacing: .05em; text-transform: uppercase; padding: 8px 10px; border-right: 1px solid rgba(255,255,255,.15); white-space: nowrap; }
.bank-table thead th.tl { text-align: left; }
.bank-table thead th:not(.tl) { text-align: right; }
.bank-table tbody tr { border-bottom: 1px solid #e2e8f4; cursor: pointer; transition: background .1s; height:36px; }
.bank-table tbody tr:hover { background: #eff6ff; }
.bank-table tbody tr:nth-child(even) { background: #f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background: #fff; }
.bank-table tbody tr.yoq { opacity: .4; cursor: not-allowed; }
.bank-table tbody td { padding: 6px 10px; vertical-align: middle; white-space:nowrap; }
.num { font-family: 'Roboto Mono','Courier New',monospace; text-align: right; }
.qoldiq-badge { font-size: .7rem; font-weight: 700; padding: 2px 8px; border-radius: 3px; }
.qoldiq-ok  { background:#dcfce7; color:#15803d; }
.qoldiq-kam { background:#fee2e2; color:#b91c1c; }
.tovar-qosh-btn { width: 28px; height: 28px; flex-shrink: 0; border-radius: 50%; border: none; background: linear-gradient(180deg,#22c55e,#16a34a); color: #fff; font-size: 18px; font-weight: 700; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.tovar-qosh-btn:hover { filter: brightness(1.1); transform: scale(1.08); }

#panel-savat { width: 38%; display: flex; flex-direction: column; background: #fff; border: 1px solid #d7e2f5; border-radius: 10px; box-shadow: 0 2px 8px rgba(30,58,138,.08); overflow: hidden; }
#savat-sarlavha { padding: 12px 16px; font-weight: 700; font-size: 16px; background: linear-gradient(90deg,#065f46,#059669); color:#fff; display: flex; justify-content: space-between; align-items: center; }
#savat-body { flex: 1; overflow-y: auto; background:#fafcff; }
.savat-qator { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-bottom: 1px solid #eef3ff; background:#fff; margin:4px 8px; border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.savat-qator:hover { background: #f0f9f4; }
.savat-nomi { flex: 1; font-size: 14px; font-weight: 600; color:#1e293b; }
.savat-narx { font-size: 12px; color: #6c757d; }
.qty-btn { width: 32px; height: 32px; border-radius: 50%; border: 1px solid #93c5fd; background: #eef3ff; color:#1e3a8a; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.qty-btn:hover { background: #dbeafe; }
.qty-val { width: 32px; text-align: center; font-weight: 700; font-size: 15px; }
.savat-jami-cell { font-weight: 700; color: #198754; width: 85px; text-align: right; font-size: 15px; }
.savat-del { color: #dc3545; cursor: pointer; font-size: 18px; }

#savat-footer { border-top: 2px solid #d7e2f5; padding: 14px 16px; background: #f5f8fd; }
.jami-qator { display: flex; justify-content: space-between; font-size: 15px; margin-bottom: 6px; }
.jami-qator.katta { font-size: 22px; font-weight: 700; color: #198754; }
#tolov-blok { margin-top: 10px; background:#fff; border:1px solid #e2e8f4; border-radius:8px; padding:10px; }
#yetishmovchi-xabar { display:none; background:#fee2e2; border:1px solid #fca5a5; color:#b91c1c; border-radius:6px; padding:8px 12px; font-size:.84rem; font-weight:600; margin-bottom:8px; }

/* ── Lock overlay ─────────────────────────────────────────────── */
#lock-overlay {
    display:none; position:fixed; inset:0; z-index:2000; background:linear-gradient(135deg,#0f172a,#1e293b);
    align-items:center; justify-content:center;
}
#lock-overlay .lock-box { width:340px; background:#fff; border-radius:14px; padding:28px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.4); }
.pin-dots { display:flex; justify-content:center; gap:10px; margin:16px 0; }
.pin-dot { width:16px; height:16px; border-radius:50%; border:2px solid #93c5fd; }
.pin-dot.filled { background:#1d4ed8; border-color:#1d4ed8; }
.pin-keypad { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:14px; }
.pin-key { padding:16px 0; font-size:20px; font-weight:700; border-radius:10px; border:1px solid #d7e2f5; background:#f5f8fd; cursor:pointer; color:#1e293b; }
.pin-key:hover { background:#e0edff; }
.pin-key.wide { grid-column:span 1; }
#pin-xato { color:#dc2626; font-size:.82rem; font-weight:700; min-height:18px; margin-top:6px; }
    </style>
</head>
<body>

<div id="smena-bar">
    <span><i class="bi bi-cash-register me-1"></i><strong>{{ $smena->filial->nomi ?? '' }}</strong>
        &nbsp;|&nbsp; Smena: <strong>{{ $smena->smena_raqami }}</strong>
        &nbsp;|&nbsp; Kassir: <strong id="kassir-nomi-label">{{ $smena->xodim->ism_familiya ?? '—' }}</strong>
        &nbsp;|&nbsp; <span id="soat"></span>
    </span>
    <span class="d-flex gap-2">
        <a href="{{ route('pos.smena.yopish-forma',$smena) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-door-closed me-1"></i>Smenani yopish</a>
        <button type="button" class="btn btn-sm btn-warning fw-bold" onclick="qulflash()"><i class="bi bi-lock-fill me-1"></i>Qulflash</button>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="terminaldanChiqish()"><i class="bi bi-box-arrow-right"></i></button>
    </span>
</div>

<div id="pos-wrap">
<div id="panel-tovarlar">
    <div id="tovar-search-bar">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="qidiruv" class="form-control" placeholder="Tovar nomi yoki shtrix-kod (skaner)..." autofocus
                   oninput="qidiruvlar()" onkeydown="if(event.key==='Enter'){event.preventDefault();barkodSkan();}" autocomplete="off">
            <button class="btn btn-outline-secondary" onclick="document.getElementById('qidiruv').value=''; barcha()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <div id="guruh-tabs">
        <button class="guruh-btn active" onclick="guruhTanlash(null, this)">Barchasi</button>
        @foreach($guruhlar as $g)
        <button class="guruh-btn" onclick="guruhTanlash({{ $g->id }}, this)">{{ $g->nomi }} ({{ $g->tovarlar_count }})</button>
        @endforeach
    </div>
    <div id="tovar-table-wrap">
        <table class="bank-table">
            <thead>
                <tr>
                    <th class="tl">Tovar</th>
                    <th class="tl" style="width:100px">Guruh</th>
                    <th class="tl" style="width:110px">Shtrix</th>
                    <th style="width:110px">Narx</th>
                    <th style="width:90px">Qoldiq</th>
                </tr>
            </thead>
            <tbody id="tovar-tbody">
                <tr><td colspan="5" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div> Yuklanmoqda...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="panel-savat">
    <div id="savat-sarlavha">
        <span><i class="bi bi-cart3 me-2"></i>Savat</span>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary" id="savat-count">0 ta</span>
            <button class="btn btn-sm btn-outline-danger py-0" onclick="savatTozala()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    <div class="text-center text-muted py-5" id="savat-bosh">
        <i class="bi bi-cart-x fs-3 d-block mb-2 opacity-25"></i>
        Savat bo'sh
    </div>
    <div id="savat-body"></div>

    <div id="savat-footer">
        <div class="jami-qator"><span class="text-muted">Jami:</span><span id="sum-umumiy">0</span></div>
        <div class="jami-qator align-items-center">
            <span class="text-muted">Chegirma (so'm):</span>
            <input type="number" id="chegirma" class="form-control form-control-sm text-end" style="width:120px" value="0" min="0" step="1000" oninput="jamiHisob()">
        </div>
        <div class="jami-qator katta"><span>To'lov:</span><span id="sum-jami">0</span></div>
        <div id="yetishmovchi-xabar"></div>
        <div id="tolov-blok">
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tolov" id="t-naqd" value="naqd" checked onchange="tolovTuri()">
                        <label class="form-check-label fw-medium" for="t-naqd"><i class="bi bi-cash me-1 text-success"></i>Naqd</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tolov" id="t-plastik" value="plastik" onchange="tolovTuri()">
                        <label class="form-check-label fw-medium" for="t-plastik"><i class="bi bi-credit-card me-1 text-primary"></i>Plastik</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="tolov" id="t-aralash" value="aralash" onchange="tolovTuri()">
                        <label class="form-check-label fw-medium" for="t-aralash"><i class="bi bi-cash-coin me-1 text-warning"></i>Aralash</label>
                    </div>
                </div>
            </div>

            <div id="naqd-blok" class="mb-2">
                <label class="form-label small fw-medium mb-1">Naqd qabul qilindi:</label>
                <input type="number" id="naqd-inp" class="form-control" placeholder="0" step="1000" oninput="naqdOzgardi()">
                <div class="mt-1 d-flex justify-content-between small">
                    <span class="text-muted">Qayta pul:</span>
                    <span class="fw-bold text-warning" id="qayta-pul">0 so'm</span>
                </div>
            </div>
            <div id="plastik-blok" class="mb-2 d-none">
                <label class="form-label small fw-medium mb-1">Plastik summa:</label>
                <input type="number" id="plastik-inp" class="form-control" placeholder="0" step="1000" oninput="plastikOzgardi()">
            </div>

            <div class="mb-2">
                <input type="text" id="mijoz-ism" class="form-control form-control-sm" placeholder="Mijoz ismi (ixtiyoriy)">
            </div>

            <button class="btn btn-success w-100 btn-lg fw-bold" onclick="sotuvBajar()" id="sot-btn">
                <i class="bi bi-check-circle me-2"></i>TO'LOV QABUL QILISH
            </button>
        </div>
    </div>
</div>
</div>

{{-- Modal: Chek --}}
<div class="modal fade" id="chek-modal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
                <h5 class="fw-bold">Sotuv bajarildi!</h5>
                <div class="fs-4 fw-bold text-success mb-1" id="chek-jami"></div>
                <div class="text-muted small mb-1">Qayta pul: <strong id="chek-qayta"></strong></div>
                <div class="text-muted small mb-3">Chek #: <code id="chek-raqam"></code></div>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-outline-secondary" onclick="chekModal.hide(); savatTozala()">Yopish</button>
                    <a id="chek-link" href="#" target="_blank" class="btn btn-primary">Chekni ko'rish</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Lock overlay (PIN qayta kiritish) ────────────────────────── --}}
<div id="lock-overlay">
    <div class="lock-box">
        <i class="bi bi-lock-fill fs-1 text-warning d-block mb-2"></i>
        <div class="fw-bold fs-5" id="lock-kassir-nomi">{{ $smena->xodim->ism_familiya ?? '' }}</div>
        <div class="text-muted small mb-2">PIN kodni kiriting</div>
        <div class="pin-dots" id="lock-pin-dots"></div>
        <div id="pin-xato"></div>
        <div class="pin-keypad" id="lock-keypad">
            <button type="button" class="pin-key" onclick="pinRaqam(1)">1</button>
            <button type="button" class="pin-key" onclick="pinRaqam(2)">2</button>
            <button type="button" class="pin-key" onclick="pinRaqam(3)">3</button>
            <button type="button" class="pin-key" onclick="pinRaqam(4)">4</button>
            <button type="button" class="pin-key" onclick="pinRaqam(5)">5</button>
            <button type="button" class="pin-key" onclick="pinRaqam(6)">6</button>
            <button type="button" class="pin-key" onclick="pinRaqam(7)">7</button>
            <button type="button" class="pin-key" onclick="pinRaqam(8)">8</button>
            <button type="button" class="pin-key" onclick="pinRaqam(9)">9</button>
            <button type="button" class="pin-key" onclick="pinOrtga()"><i class="bi bi-backspace"></i></button>
            <button type="button" class="pin-key" onclick="pinRaqam(0)">0</button>
            <button type="button" class="pin-key" onclick="pinTozala()"><i class="bi bi-x-lg"></i></button>
        </div>
        <a href="{{ route('terminal.chiqish') }}" class="d-block small text-muted mt-3">Boshqa kassir sifatida kirish</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const FILIAL_ID = {{ $smena->filial_id }};
const JORIY_XODIM_ID = {{ $smena->xodim_id }};
let savat = {};
let chekModal;
let jamiBaza = 0;
let qolFieldQolgan = null;

document.addEventListener('DOMContentLoaded', () => {
    chekModal = new bootstrap.Modal(document.getElementById('chek-modal'));
    barcha();
    soatYangila();
    setInterval(soatYangila, 1000);
    faollikKuzatish();
});

function soatYangila() {
    document.getElementById('soat').textContent = new Date().toLocaleString('uz-UZ', { dateStyle:'short', timeStyle:'medium' });
}

// ─── Tovarlarni yuklash ───────────────────────────────────────────
async function barcha() { await tovarlarYukla({}); }

async function guruhTanlash(guruhId, btn) {
    document.querySelectorAll('.guruh-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    await tovarlarYukla(guruhId ? { guruh_id: guruhId } : {});
}

async function qidiruvlar() {
    const q = document.getElementById('qidiruv').value;
    if (q.length === 0) { barcha(); return; }
    await tovarlarYukla({ qidiruv: q });
}

async function barkodSkan() {
    const q = document.getElementById('qidiruv').value.trim();
    if (!q) return;

    const url = new URL('/pos/tovarlar', window.location.origin);
    url.searchParams.set('filial_id', FILIAL_ID);
    url.searchParams.set('qidiruv', q);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    const aniqMos = data.find(t => t.barkod === q);
    if (aniqMos) {
        if (aniqMos.qoldiq > 0) {
            savatQosh(aniqMos.id, aniqMos.nomi, aniqMos.sotish_narx, aniqMos.birlik, aniqMos.qoldiq);
        } else {
            alert(`«${aniqMos.nomi}»: omborda qoldiq yo'q!`);
        }
        document.getElementById('qidiruv').value = '';
        barcha();
    } else {
        await tovarlarYukla({ qidiruv: q });
    }
}

async function tovarlarYukla(params) {
    const tbody = document.getElementById('tovar-tbody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>';

    const url = new URL('/pos/tovarlar', window.location.origin);
    url.searchParams.set('filial_id', FILIAL_ID);
    Object.entries(params).forEach(([k,v]) => url.searchParams.set(k, v));

    const res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-search fs-4 d-block mb-2 opacity-25"></i>Topilmadi</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(t => `
        <tr class="${t.qoldiq <= 0 ? 'yoq' : ''}" data-tovar-id="${t.id}" data-qoldiq="${t.qoldiq}" data-birlik="${t.birlik}"
            onclick="${t.qoldiq > 0 ? `savatQosh(${t.id},'${(t.nomi||'').replace(/'/g,"\\'")}',${t.sotish_narx},'${t.birlik}',${t.qoldiq})` : ''}">
            <td class="fw-semibold">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <span>${t.nomi}</span>
                    ${t.qoldiq > 0 ? `<button type="button" class="tovar-qosh-btn" title="Savatga qo'shish"
                        onclick="event.stopPropagation();savatQosh(${t.id},'${(t.nomi||'').replace(/'/g,"\\'")}',${t.sotish_narx},'${t.birlik}',${t.qoldiq})">+</button>` : ''}
                </div>
            </td>
            <td class="text-muted" style="font-size:.76rem">${t.guruh?.nomi ?? '—'}</td>
            <td class="text-muted font-monospace" style="font-size:.72rem">${t.barkod ?? '—'}</td>
            <td class="num" style="color:#15803d;font-weight:700">${Number(t.sotish_narx).toLocaleString('uz-UZ')}</td>
            <td class="text-end"><span class="qoldiq-badge ${t.qoldiq <= 3 ? 'qoldiq-kam' : 'qoldiq-ok'}">${qoldiqFmt(t.qoldiq)} ${t.birlik}</span></td>
        </tr>
    `).join('');
    qoldiqBadgalarniYangila();
}

// ─── Savat ───────────────────────────────────────────────────────
function savatQosh(id, nomi, narx, birlik, qoldiq) {
    if (savat[id]) {
        if (savat[id].miqdor >= qoldiq) {
            alert(`Omborda faqat ${qoldiqFmt(qoldiq)} ${birlik} bor!`);
            return;
        }
        savat[id].miqdor++;
    } else {
        savat[id] = { nomi, narx, miqdor: 1, birlik, qoldiq };
    }
    savatRender();
}

function miqdorOzgartir(id, delta) {
    if (!savat[id]) return;
    savat[id].miqdor += delta;
    if (savat[id].miqdor <= 0) delete savat[id];
    savatRender();
}

function savatOchir(id) { delete savat[id]; savatRender(); }
function savatTozala() { savat = {}; document.getElementById('naqd-inp').value=''; document.getElementById('plastik-inp').value=''; savatRender(); }

function qoldiqFmt(v) { return Number(v).toFixed(2).replace('.', ','); }

function qoldiqBadgalarniYangila() {
    document.querySelectorAll('#tovar-tbody tr[data-tovar-id]').forEach(tr => {
        const id = tr.dataset.tovarId;
        const asli = parseFloat(tr.dataset.qoldiq) || 0;
        const savatda = savat[id]?.miqdor || 0;
        const qolgan = Math.max(0, asli - savatda);
        const badge = tr.querySelector('.qoldiq-badge');
        if (!badge) return;
        badge.textContent = qoldiqFmt(qolgan) + ' ' + tr.dataset.birlik;
        badge.classList.toggle('qoldiq-kam', qolgan <= 3);
        badge.classList.toggle('qoldiq-ok', qolgan > 3);
    });
}

function savatRender() {
    const body   = document.getElementById('savat-body');
    const bosh   = document.getElementById('savat-bosh');
    const ids    = Object.keys(savat);
    const count  = ids.length;

    document.getElementById('savat-count').textContent = count + ' ta';
    qoldiqBadgalarniYangila();

    if (!count) {
        body.innerHTML = '';
        bosh.style.display = 'block';
        jamiHisob();
        return;
    }

    bosh.style.display = 'none';
    body.innerHTML = ids.map(id => {
        const t = savat[id];
        return `
        <div class="savat-qator">
            <div style="flex:1">
                <div class="savat-nomi">${t.nomi}</div>
                <div class="savat-narx">${t.narx.toLocaleString('uz-UZ')} so'm × ${t.miqdor} ${t.birlik}</div>
            </div>
            <button class="qty-btn" onclick="miqdorOzgartir(${id},-1)">−</button>
            <span class="qty-val">${t.miqdor}</span>
            <button class="qty-btn" onclick="miqdorOzgartir(${id},1)">+</button>
            <div class="savat-jami-cell">${(t.narx*t.miqdor).toLocaleString('uz-UZ')}</div>
            <i class="bi bi-x-circle savat-del" onclick="savatOchir(${id})"></i>
        </div>`;
    }).join('');

    jamiHisob();
}

function jamiHisob() {
    const ids = Object.keys(savat);
    const umumiy = ids.reduce((s, id) => s + savat[id].narx * savat[id].miqdor, 0);
    const chegirma = parseFloat(document.getElementById('chegirma').value) || 0;
    const jami = Math.max(0, umumiy - chegirma);
    jamiBaza = jami;

    document.getElementById('sum-umumiy').textContent = umumiy.toLocaleString('uz-UZ') + ' so\'m';
    document.getElementById('sum-jami').textContent   = jami.toLocaleString('uz-UZ') + ' so\'m';
    tolovAvtoToldir();
}

function tolovAvtoToldir() {
    const tur = document.querySelector('[name=tolov]:checked').value;
    const naqdInp = document.getElementById('naqd-inp');
    const plastikInp = document.getElementById('plastik-inp');

    if (tur === 'naqd') {
        naqdInp.value = jamiBaza || '';
    } else if (tur === 'plastik') {
        plastikInp.value = jamiBaza || '';
    } else if (tur === 'aralash') {
        if (qolFieldQolgan === 'plastik') {
            const plastik = parseFloat(plastikInp.value) || 0;
            naqdInp.value = Math.max(0, jamiBaza - plastik) || '';
        } else {
            const naqd = parseFloat(naqdInp.value) || 0;
            plastikInp.value = Math.max(0, jamiBaza - naqd) || '';
        }
    }
    qaytaPulHisob();
    yetishmovchiTekshir();
}

function naqdOzgardi() {
    qolFieldQolgan = 'naqd';
    const tur = document.querySelector('[name=tolov]:checked').value;
    if (tur === 'aralash') {
        const naqd = parseFloat(document.getElementById('naqd-inp').value) || 0;
        document.getElementById('plastik-inp').value = Math.max(0, jamiBaza - naqd) || '';
    }
    qaytaPulHisob();
    yetishmovchiTekshir();
}

function plastikOzgardi() {
    qolFieldQolgan = 'plastik';
    const tur = document.querySelector('[name=tolov]:checked').value;
    if (tur === 'aralash') {
        const plastik = parseFloat(document.getElementById('plastik-inp').value) || 0;
        document.getElementById('naqd-inp').value = Math.max(0, jamiBaza - plastik) || '';
    }
    qaytaPulHisob();
    yetishmovchiTekshir();
}

function qaytaPulHisob() {
    const tur = document.querySelector('[name=tolov]:checked').value;
    const naqd = parseFloat(document.getElementById('naqd-inp').value) || 0;
    const qayta = tur === 'naqd' ? Math.max(0, naqd - jamiBaza) : 0;
    document.getElementById('qayta-pul').textContent = qayta.toLocaleString('uz-UZ') + ' so\'m';
}

function yetishmovchiTekshir() {
    const tur = document.querySelector('[name=tolov]:checked').value;
    const naqd = parseFloat(document.getElementById('naqd-inp').value) || 0;
    const plastik = parseFloat(document.getElementById('plastik-inp').value) || 0;
    const xabarEl = document.getElementById('yetishmovchi-xabar');
    const btn = document.getElementById('sot-btn');

    let kiritilgan = 0;
    if (tur === 'naqd') kiritilgan = naqd;
    else if (tur === 'plastik') kiritilgan = plastik;
    else kiritilgan = naqd + plastik;

    const yetishmayapti = Math.max(0, jamiBaza - kiritilgan);

    if (jamiBaza > 0 && yetishmayapti > 0.01) {
        xabarEl.style.display = 'block';
        xabarEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i>Yetarli emas — yana <strong>${yetishmayapti.toLocaleString('uz-UZ')} so'm</strong> kerak`;
        btn.disabled = true;
        return false;
    }

    xabarEl.style.display = 'none';
    btn.disabled = false;
    return true;
}

function tolovTuri() {
    const tur = document.querySelector('[name=tolov]:checked').value;
    document.getElementById('naqd-blok').classList.toggle('d-none', tur === 'plastik');
    document.getElementById('plastik-blok').classList.toggle('d-none', tur !== 'aralash' && tur !== 'plastik');
    qolFieldQolgan = null;
    tolovAvtoToldir();
}

// ─── Sotuvni bajarish ─────────────────────────────────────────────
async function sotuvBajar() {
    const ids = Object.keys(savat);
    if (!ids.length) { alert("Savat bo'sh!"); return; }
    if (!yetishmovchiTekshir()) return;

    const btn = document.getElementById('sot-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saqlanmoqda...';

    const tur      = document.querySelector('[name=tolov]:checked').value;
    const chegirma = parseFloat(document.getElementById('chegirma').value) || 0;
    const naqdSumma    = parseFloat(document.getElementById('naqd-inp').value) || 0;
    const plastikSumma = parseFloat(document.getElementById('plastik-inp').value) || 0;

    const payload = {
        filial_id: FILIAL_ID, tolov_turi: tur, chegirma: chegirma,
        naqd_summa: tur==='plastik' ? 0 : naqdSumma,
        plastik_summa: tur==='naqd' ? 0 : plastikSumma,
        mijoz_ism: document.getElementById('mijoz-ism').value,
        tovarlar: ids.map(id => ({ tovar_id: parseInt(id), miqdor: savat[id].miqdor, narx: savat[id].narx })),
    };

    try {
        const res  = await fetch('/pos/saqlash', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok) { alert(data.xato || data.message || 'Xato yuz berdi'); return; }

        document.getElementById('chek-jami').textContent   = data.jami_tolov.toLocaleString('uz-UZ') + ' so\'m';
        document.getElementById('chek-qayta').textContent  = data.qayta_pul.toLocaleString('uz-UZ') + ' so\'m';
        document.getElementById('chek-raqam').textContent  = data.check_raqam;
        document.getElementById('chek-link').href = `/pos/chek/${data.sotuv_id}`;
        chekModal.show();

        savatTozala();
        barcha();
    } catch(e) {
        alert('Server bilan aloqa xatosi: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>TO\'LOV QABUL QILISH';
    }
}

document.addEventListener('keydown', e => {
    if (e.key === 'F2') { document.getElementById('qidiruv').focus(); e.preventDefault(); }
    if (e.key === 'F9') { sotuvBajar(); e.preventDefault(); }
    if (e.key === 'Delete' && e.ctrlKey) { savatTozala(); e.preventDefault(); }
});

// ─── Qulflash / PIN qayta kirish ────────────────────────────────
let pinKiritilgan = '';
const AUTO_LOCK_MS = {{ (int) ($autoLockDaqiqa ?? 10) }} * 60 * 1000;
let faollikTaymer = null;

function faollikKuzatish() {
    const qaytaBoshlash = () => {
        clearTimeout(faollikTaymer);
        faollikTaymer = setTimeout(qulflash, AUTO_LOCK_MS);
    };
    ['click', 'keydown', 'mousemove', 'touchstart'].forEach(ev => document.addEventListener(ev, qaytaBoshlash));
    qaytaBoshlash();
}

function qulflash() {
    document.getElementById('lock-overlay').style.display = 'flex';
    document.getElementById('lock-kassir-nomi').textContent = document.getElementById('kassir-nomi-label').textContent;
    pinKiritilgan = '';
    pinDotlarniYangila();
    fetch('{{ route("terminal.qulflash") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
}

function pinDotlarniYangila() {
    const box = document.getElementById('lock-pin-dots');
    box.innerHTML = '';
    for (let i = 0; i < 6; i++) {
        const dot = document.createElement('div');
        dot.className = 'pin-dot' + (i < pinKiritilgan.length ? ' filled' : '');
        box.appendChild(dot);
    }
}

function pinRaqam(n) {
    if (pinKiritilgan.length >= 6) return;
    pinKiritilgan += n;
    pinDotlarniYangila();
    document.getElementById('pin-xato').textContent = '';
    if (pinKiritilgan.length >= 4) {
        pinYuborish();
    }
}
function pinOrtga() { pinKiritilgan = pinKiritilgan.slice(0, -1); pinDotlarniYangila(); }
function pinTozala() { pinKiritilgan = ''; pinDotlarniYangila(); }

async function pinYuborish() {
    const res = await fetch('{{ route("terminal.yechish") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ pin: pinKiritilgan }),
    });
    const data = await res.json();
    if (!res.ok || !data.muvaffaqiyat) {
        document.getElementById('pin-xato').textContent = data.xato || "PIN noto'g'ri";
        pinKiritilgan = '';
        pinDotlarniYangila();
        return;
    }
    if (data.boshqa_kassir) {
        savatTozala();
        document.getElementById('kassir-nomi-label').textContent = data.kassir_nomi;
        location.reload();
        return;
    }
    document.getElementById('lock-overlay').style.display = 'none';
    pinKiritilgan = '';
}

function terminaldanChiqish() {
    if (!confirm("Terminaldan chiqishni tasdiqlaysizmi?")) return;
    window.location.href = '{{ route("terminal.chiqish") }}';
}
</script>
</body>
</html>
