@extends('layouts.app')
@section('title','Kassa — Naqd savdo')
@section('breadcrumb')
<li class="breadcrumb-item active">Kassa (POS)</li>
@endsection

@push('styles')
<style>
html, body { overflow: hidden; }
#pos-wrap { height: calc(100vh - 56px); display: flex; gap: 0; }

/* Chap panel — tovarlar */
#panel-tovarlar {
    width: 60%;
    display: flex;
    flex-direction: column;
    border-right: 2px solid #e9ecef;
    background: #f8f9fa;
}
#tovar-search-bar { padding: 12px; background: white; border-bottom: 1px solid #e9ecef; }
#guruh-tabs { display: flex; gap: 6px; padding: 8px 12px; overflow-x: auto; background: white; border-bottom: 1px solid #e9ecef; flex-shrink: 0; }
.guruh-btn { border: 1px solid #dee2e6; border-radius: 20px; padding: 4px 14px; font-size: 13px; cursor: pointer; white-space: nowrap; background: white; transition: all .2s; }
.guruh-btn.active { background: var(--accent-color,#ffc107); border-color: var(--accent-color,#ffc107); font-weight: 600; }
#tovar-grid { flex: 1; overflow-y: auto; padding: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(140px,1fr)); gap: 10px; align-content: start; }
.tovar-karta {
    background: white; border: 1px solid #dee2e6; border-radius: 10px;
    padding: 12px; cursor: pointer; transition: all .15s; user-select: none;
    display: flex; flex-direction: column; align-items: center; text-align: center;
}
.tovar-karta:hover { border-color: #0d6efd; box-shadow: 0 2px 8px rgba(13,110,253,.2); transform: translateY(-1px); }
.tovar-karta.yoq { opacity: .45; cursor: not-allowed; }
.tovar-karta .narx { font-size: 15px; font-weight: 700; color: #198754; }
.tovar-karta .nomi { font-size: 12px; margin-top: 6px; line-height: 1.3; }
.tovar-karta .qoldiq { font-size: 11px; color: #6c757d; margin-top: 3px; }
.tovar-karta .qoldiq.kam { color: #dc3545; }

/* O'ng panel — savat */
#panel-savat {
    width: 40%;
    display: flex;
    flex-direction: column;
    background: white;
}
#savat-sarlavha { padding: 12px 16px; font-weight: 700; font-size: 15px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
#savat-body { flex: 1; overflow-y: auto; }
.savat-qator { display: flex; align-items: center; gap: 8px; padding: 10px 14px; border-bottom: 1px solid #f0f0f0; }
.savat-qator:hover { background: #f8f9fa; }
.savat-nomi { flex: 1; font-size: 13px; font-weight: 500; }
.savat-narx { font-size: 12px; color: #6c757d; }
.qty-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid #dee2e6; background: white; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.qty-btn:hover { background: #e9ecef; }
.qty-val { width: 36px; text-align: center; font-weight: 700; font-size: 14px; }
.savat-jami-cell { font-weight: 700; color: #198754; width: 80px; text-align: right; font-size: 14px; }
.savat-del { color: #dc3545; cursor: pointer; font-size: 16px; }

#savat-footer { border-top: 2px solid #e9ecef; padding: 14px 16px; }
.jami-qator { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; }
.jami-qator.katta { font-size: 20px; font-weight: 700; color: #198754; }
#tolov-blok { margin-top: 12px; }
</style>
@endpush

@section('content')
<div id="pos-wrap">

{{-- ── Chap: Tovarlar ──────────────────────────────────────────── --}}
<div id="panel-tovarlar">
    <div id="tovar-search-bar">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="qidiruv" class="form-control" placeholder="Tovar nomi yoki shtrix-kod..." autofocus
                   oninput="qidiruvlar()" autocomplete="off">
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
    <div id="tovar-grid">
        <div class="text-center text-muted p-4 col-span-all">
            <div class="spinner-border spinner-border-sm"></div> Yuklanmoqda...
        </div>
    </div>
</div>

{{-- ── O'ng: Savat ─────────────────────────────────────────────── --}}
<div id="panel-savat">
    <div id="savat-sarlavha">
        <span><i class="bi bi-cart3 me-2 text-success"></i>Savat</span>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary" id="savat-count">0 ta</span>
            <button class="btn btn-sm btn-outline-danger py-0" onclick="savatTozala()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    <div id="savat-body">
        <div class="text-center text-muted py-5" id="savat-bosh">
            <i class="bi bi-cart-x fs-3 d-block mb-2 opacity-25"></i>
            Savat bo'sh
        </div>
    </div>

    <div id="savat-footer">
        {{-- Jami --}}
        <div class="jami-qator"><span class="text-muted">Jami:</span><span id="sum-umumiy">0</span></div>

        {{-- Chegirma --}}
        <div class="jami-qator align-items-center">
            <span class="text-muted">Chegirma (so'm):</span>
            <input type="number" id="chegirma" class="form-control form-control-sm text-end"
                   style="width:120px" value="0" min="0" step="1000" oninput="jamiHisob()">
        </div>

        <div class="jami-qator katta"><span>To'lov:</span><span id="sum-jami">0</span></div>

        {{-- To'lov turi --}}
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
                <input type="number" id="naqd-inp" class="form-control" placeholder="0" step="1000" oninput="qaytaPulHisob()">
                <div class="mt-1 d-flex justify-content-between small">
                    <span class="text-muted">Qayta pul:</span>
                    <span class="fw-bold text-warning" id="qayta-pul">0 so'm</span>
                </div>
            </div>
            <div id="plastik-blok" class="mb-2 d-none">
                <label class="form-label small fw-medium mb-1">Plastik summa:</label>
                <input type="number" id="plastik-inp" class="form-control" placeholder="0" step="1000">
            </div>

            <div class="mb-2">
                <input type="text" id="mijoz-ism" class="form-control form-control-sm" placeholder="Mijoz ismi (ixtiyoriy)">
            </div>

            <button class="btn btn-success w-100 btn-lg fw-bold" onclick="sotuvBajar()" id="sot-btn">
                <i class="bi bi-check-circle me-2"></i>TO'LOV QABUL QILISH
            </button>

            <div class="mt-2 text-center">
                <a href="{{ route('pos.tarix') }}" class="text-muted small">
                    <i class="bi bi-clock-history me-1"></i>Sotuv tarixi
                </a>
                <span class="text-muted mx-2">·</span>
                <span class="text-muted small">Bugun: <strong class="text-success">{{ number_format($bugun_sotuv,0,'.',' ') }} so'm</strong> ({{ $bugun_checklar }} ta)</span>
            </div>
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
                    <a id="chek-link" href="#" class="btn btn-primary">Chekni ko'rish</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const FILIAL_ID = {{ $filialId }};
let savat = {}; // {tovar_id: {nomi, narx, miqdor, birlik, qoldiq}}
let chekModal;
let jamiBaza = 0;

document.addEventListener('DOMContentLoaded', () => {
    chekModal = new bootstrap.Modal(document.getElementById('chek-modal'));
    barcha();
});

// ─── Tovarlarni yuklash ───────────────────────────────────────────
async function barcha() {
    await tovarlarYukla({});
}

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

async function tovarlarYukla(params) {
    const grid = document.getElementById('tovar-grid');
    grid.innerHTML = '<div class="text-center text-muted py-4 w-100"><div class="spinner-border spinner-border-sm"></div></div>';

    const url = new URL('/pos/tovarlar', window.location.origin);
    Object.entries(params).forEach(([k,v]) => url.searchParams.set(k, v));

    const res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();

    if (!data.length) {
        grid.innerHTML = '<div class="text-center text-muted py-4 w-100"><i class="bi bi-search fs-3 d-block mb-2 opacity-25"></i>Topilmadi</div>';
        return;
    }

    grid.innerHTML = data.map(t => `
        <div class="tovar-karta ${t.qoldiq <= 0 ? 'yoq' : ''}" onclick="${t.qoldiq > 0 ? `savatQosh(${t.id},'${t.nomi.replace(/'/g,"\\'")}',${t.sotish_narx},'${t.birlik}',${t.qoldiq})` : ''}">
            <i class="bi bi-box fs-4 text-secondary"></i>
            <div class="narx">${t.sotish_narx.toLocaleString('uz-UZ')}</div>
            <div class="nomi">${t.nomi}</div>
            <div class="qoldiq ${t.qoldiq <= 3 ? 'kam' : ''}">${t.qoldiq} ${t.birlik}</div>
        </div>
    `).join('');
}

// ─── Savat ───────────────────────────────────────────────────────
function savatQosh(id, nomi, narx, birlik, qoldiq) {
    if (savat[id]) {
        if (savat[id].miqdor >= qoldiq) {
            alert(`Omborda faqat ${qoldiq} ${birlik} bor!`);
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
function savatTozala() { savat = {}; savatRender(); }

function savatRender() {
    const body   = document.getElementById('savat-body');
    const bosh   = document.getElementById('savat-bosh');
    const ids    = Object.keys(savat);
    const count  = ids.length;

    document.getElementById('savat-count').textContent = count + ' ta';

    if (!count) {
        body.innerHTML = '';
        bosh.style.display = '';
        document.getElementById('savat-bosh').style.display = 'block';
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
    qaytaPulHisob();
}

function qaytaPulHisob() {
    const naqd = parseFloat(document.getElementById('naqd-inp').value) || 0;
    const qayta = Math.max(0, naqd - jamiBaza);
    document.getElementById('qayta-pul').textContent = qayta.toLocaleString('uz-UZ') + ' so\'m';
}

function tolovTuri() {
    const tur = document.querySelector('[name=tolov]:checked').value;
    document.getElementById('naqd-blok').classList.toggle('d-none', tur === 'plastik');
    document.getElementById('plastik-blok').classList.toggle('d-none', tur !== 'aralash' && tur !== 'plastik');
}

// ─── Sotuvni bajarish ─────────────────────────────────────────────
async function sotuvBajar() {
    const ids = Object.keys(savat);
    if (!ids.length) { alert("Savat bo'sh!"); return; }

    const btn = document.getElementById('sot-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saqlanmoqda...';

    const tur      = document.querySelector('[name=tolov]:checked').value;
    const chegirma = parseFloat(document.getElementById('chegirma').value) || 0;
    const naqdSumma    = parseFloat(document.getElementById('naqd-inp').value) || 0;
    const plastikSumma = parseFloat(document.getElementById('plastik-inp').value) || 0;

    const payload = {
        filial_id:      FILIAL_ID,
        tolov_turi:     tur,
        chegirma:       chegirma,
        naqd_summa:     tur==='plastik' ? 0 : naqdSumma,
        plastik_summa:  tur==='naqd' ? 0 : plastikSumma,
        mijoz_ism:      document.getElementById('mijoz-ism').value,
        tovarlar: ids.map(id => ({
            tovar_id: parseInt(id),
            miqdor:   savat[id].miqdor,
            narx:     savat[id].narx,
        })),
        _token: document.querySelector('[name=csrf-token]')?.content || '{{ csrf_token() }}',
    };

    try {
        const res  = await fetch('/pos/saqlash', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok) {
            alert(data.xato || data.message || 'Xato yuz berdi');
            return;
        }

        // Muvaffaqiyat — chek ko'rsatish
        document.getElementById('chek-jami').textContent   = data.jami_tolov.toLocaleString('uz-UZ') + ' so\'m';
        document.getElementById('chek-qayta').textContent  = data.qayta_pul.toLocaleString('uz-UZ') + ' so\'m';
        document.getElementById('chek-raqam').textContent  = data.check_raqam;
        document.getElementById('chek-link').href = `/pos/chek/${data.sotuv_id}`;
        chekModal.show();

        savatTozala();
        barcha(); // Tovar qoldiqlarini yangilash

    } catch(e) {
        alert('Server bilan aloqa xatosi: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>TO\'LOV QABUL QILISH';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', e => {
    if (e.key === 'F2') { document.getElementById('qidiruv').focus(); e.preventDefault(); }
    if (e.key === 'F9') { sotuvBajar(); e.preventDefault(); }
    if (e.key === 'Delete' && e.ctrlKey) { savatTozala(); e.preventDefault(); }
});
</script>
@endsection
