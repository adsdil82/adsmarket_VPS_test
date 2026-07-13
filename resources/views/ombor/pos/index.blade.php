@extends('layouts.app')
@section('title','Kassa — Naqd savdo')
@section('breadcrumb')
<li class="breadcrumb-item active">Kassa (POS)</li>
@endsection

@push('styles')
<style>
html, body { overflow: hidden; }
#smena-bar {
    height: 38px; display: flex; align-items: center; justify-content: space-between;
    padding: 0 16px; margin: -24px -24px 0 -24px; background: linear-gradient(90deg,#14532d,#15803d);
    color: #fff; font-size: .8rem; font-weight: 600;
}
#smena-bar .btn { padding: 2px 10px; font-size: .74rem; }
#pos-wrap { height: calc(100vh - 56px - 38px); display: flex; gap: 10px; margin: 0 -24px -24px -24px; padding: 10px; background: #eef2f7; box-sizing: border-box; }

/* Chap panel — tovarlar (bank-style jadval) — alohida karta */
#panel-tovarlar {
    width: 62%;
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #d7e2f5;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(30,58,138,.08);
    overflow: hidden;
}
#tovar-search-bar { padding: 8px 12px; background: linear-gradient(90deg,#1e3a8a,#1d4ed8); border-bottom: none; }
#tovar-search-bar .input-group-text { background:#fff; border:none; }
#tovar-search-bar .form-control { border:none; }
#guruh-tabs { display: flex; gap: 6px; padding: 6px 12px; overflow-x: auto; background: #eef3ff; border-bottom: 1px solid #d7e2f5; flex-shrink: 0; }
.guruh-btn { border: 1px solid #93c5fd; border-radius: 20px; padding: 3px 12px; font-size: 12px; cursor: pointer; white-space: nowrap; background: white; color:#1e3a8a; font-weight:600; transition: all .2s; }
.guruh-btn.active { background: linear-gradient(90deg,#1e3a8a,#1d4ed8); border-color: #1d4ed8; color:#fff; font-weight: 700; }

#tovar-table-wrap { flex: 1; overflow-y: auto; background:#fff; }
.bank-table { border-collapse: collapse; font-size: .78rem; width: 100%; }
.bank-table thead { position: sticky; top: 0; z-index: 5; }
.bank-table thead th {
    background: linear-gradient(180deg,#2563eb,#1d4ed8); color: #fff; font-weight: 700;
    font-size: .68rem; letter-spacing: .05em; text-transform: uppercase; padding: 6px 10px;
    border-right: 1px solid rgba(255,255,255,.15); white-space: nowrap;
}
.bank-table thead th.tl { text-align: left; }
.bank-table thead th:not(.tl) { text-align: right; }
.bank-table tbody tr { border-bottom: 1px solid #e2e8f4; cursor: pointer; transition: background .1s; height:24px; }
.bank-table tbody tr:hover { background: #eff6ff; }
.bank-table tbody tr:nth-child(even) { background: #f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background: #fff; }
.bank-table tbody tr.yoq { opacity: .4; cursor: not-allowed; }
.bank-table tbody td { padding: 3px 10px; vertical-align: middle; white-space:nowrap; }
.num { font-family: 'Roboto Mono','Courier New',monospace; text-align: right; }
.qoldiq-badge { font-size: .64rem; font-weight: 700; padding: 1px 6px; border-radius: 3px; }
.qoldiq-ok  { background:#dcfce7; color:#15803d; }
.qoldiq-kam { background:#fee2e2; color:#b91c1c; }
.tovar-qosh-btn {
    width: 22px; height: 22px; flex-shrink: 0; border-radius: 50%; border: none;
    background: linear-gradient(180deg,#22c55e,#16a34a); color: #fff; font-size: 15px; font-weight: 700;
    line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.tovar-qosh-btn:hover { filter: brightness(1.1); transform: scale(1.08); }

/* O'ng panel — savat — alohida karta */
#panel-savat {
    width: 38%; display: flex; flex-direction: column;
    background: #fff; border: 1px solid #d7e2f5; border-radius: 10px;
    box-shadow: 0 2px 8px rgba(30,58,138,.08); overflow: hidden;
}
#savat-sarlavha {
    padding: 10px 16px; font-weight: 700; font-size: 15px;
    background: linear-gradient(90deg,#065f46,#059669); color:#fff;
    display: flex; justify-content: space-between; align-items: center;
}
#savat-body { flex: 1; overflow-y: auto; background:#fafcff; }
.savat-qator { display: flex; align-items: center; gap: 8px; padding: 8px 14px; border-bottom: 1px solid #eef3ff; background:#fff; margin:4px 8px; border-radius:6px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
.savat-qator:hover { background: #f0f9f4; }
.savat-nomi { flex: 1; font-size: 13px; font-weight: 600; color:#1e293b; }
.savat-narx { font-size: 12px; color: #6c757d; }
.qty-btn { width: 26px; height: 26px; border-radius: 50%; border: 1px solid #93c5fd; background: #eef3ff; color:#1e3a8a; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.qty-btn:hover { background: #dbeafe; }
.qty-val { width: 32px; text-align: center; font-weight: 700; font-size: 14px; }
.savat-jami-cell { font-weight: 700; color: #198754; width: 80px; text-align: right; font-size: 14px; }
.savat-del { color: #dc3545; cursor: pointer; font-size: 16px; }

#savat-footer { border-top: 2px solid #d7e2f5; padding: 12px 16px; background: #f5f8fd; }
.jami-qator { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; }
.jami-qator.katta { font-size: 20px; font-weight: 700; color: #198754; }
#tolov-blok { margin-top: 10px; background:#fff; border:1px solid #e2e8f4; border-radius:8px; padding:10px; }
#yetishmovchi-xabar { display:none; background:#fee2e2; border:1px solid #fca5a5; color:#b91c1c; border-radius:6px; padding:8px 12px; font-size:.82rem; font-weight:600; margin-bottom:8px; }
</style>
@endpush

@section('content')
<div id="smena-bar">
    <span><i class="bi bi-door-open me-1"></i>Smena: <strong>{{ $smena->smena_raqami }}</strong>
        &nbsp;|&nbsp; Kassir: {{ $smena->xodim->ism_familiya ?? '—' }}
        &nbsp;|&nbsp; Ochilgan: {{ $smena->ochilgan_vaqt->format('d.m.Y H:i') }}
        &nbsp;|&nbsp; Dastlabki qoldiq: {{ number_format($smena->dastlabki_qoldiq,0,'.',' ') }} so'm
    </span>
    <span class="d-flex gap-2">
        <a href="{{ route('pos.smena.royxat') }}" class="btn btn-sm btn-light"><i class="bi bi-clock-history"></i></a>
        <a href="{{ route('pos.smena.yopish-forma',$smena) }}" class="btn btn-sm btn-outline-light"><i class="bi bi-door-closed me-1"></i>Smenani yopish</a>
    </span>
</div>
<div id="pos-wrap">

{{-- ── Chap: Tovarlar (bank-style jadval) ─────────────────────────── --}}
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

{{-- ── O'ng: Savat ─────────────────────────────────────────────── --}}
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
        {{-- Jami --}}
        <div class="jami-qator"><span class="text-muted">Jami:</span><span id="sum-umumiy">0</span></div>

        {{-- Chegirma --}}
        <div class="jami-qator align-items-center">
            <span class="text-muted">Chegirma:</span>
            <div class="d-flex gap-1 align-items-center">
                <input type="number" id="chegirma" class="form-control form-control-sm text-end"
                       style="width:90px" value="0" min="0" step="100" oninput="jamiHisob()">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="chegirma-tur" id="chegirma-som" value="som" checked onchange="jamiHisob()">
                    <label class="btn btn-outline-secondary btn-sm" for="chegirma-som">so'm</label>
                    <input type="radio" class="btn-check" name="chegirma-tur" id="chegirma-foiz" value="foiz" onchange="jamiHisob()">
                    <label class="btn btn-outline-secondary btn-sm" for="chegirma-foiz">%</label>
                </div>
            </div>
        </div>

        <div class="jami-qator katta"><span>To'lov:</span><span id="sum-jami">0</span></div>

        <div id="yetishmovchi-xabar"></div>

        {{-- To'lov turi --}}
        <div id="tolov-blok">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <input type="radio" class="btn-check" name="tolov" id="t-naqd" value="naqd" checked onchange="tolovTuri()">
                <label class="btn btn-outline-success btn-sm" for="t-naqd"><i class="bi bi-cash me-1"></i>Naqd</label>

                <input type="radio" class="btn-check" name="tolov" id="t-plastik" value="plastik" onchange="tolovTuri()">
                <label class="btn btn-outline-primary btn-sm" for="t-plastik"><i class="bi bi-credit-card me-1"></i>Plastik</label>

                @foreach($tolovUsullari ?? [] as $usul)
                <input type="radio" class="btn-check" name="tolov" id="t-usul-{{ $usul->id }}" value="plastik"
                       data-usuli-id="{{ $usul->id }}" onchange="tolovTuri()">
                <label class="btn btn-outline-primary btn-sm" for="t-usul-{{ $usul->id }}">{{ $usul->nomi }}</label>
                @endforeach

                <input type="radio" class="btn-check" name="tolov" id="t-aralash" value="aralash" onchange="tolovTuri()">
                <label class="btn btn-outline-warning btn-sm" for="t-aralash"><i class="bi bi-cash-coin me-1"></i>Aralash</label>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small fw-medium mb-1">Naqd qabul qilindi:</label>
                    <input type="number" id="naqd-inp" class="form-control" placeholder="0" step="1000" oninput="naqdOzgardi()">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium mb-1">Plastik summa:</label>
                    <input type="number" id="plastik-inp" class="form-control" placeholder="0" step="1000" oninput="plastikOzgardi()" disabled>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-medium mb-1">Qayta pul:</label>
                <input type="text" id="qayta-pul" class="form-control fw-bold text-warning" readonly value="0 so'm">
            </div>

            <div class="mb-2">
                <input type="text" id="tolov-izoh" class="form-control form-control-sm" placeholder="To'lov izohi (ixtiyoriy)">
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
let chegirmaHisoblangan = 0;
let qolFieldQolgan = null; // aralashda oxirgi tahrirlangan maydon: 'naqd' yoki 'plastik'

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

/**
 * Shtrix-kod skaneri qo'llab-quvvatlash: skaner klaviaturaga tez matn
 * kiritib, oxirida Enter bosadi. Agar kiritilgan qiymat aniq bir tovarning
 * barkodiga TENG bo'lsa (qisman moslik emas) — savatga avtomat qo'shiladi
 * va qidiruv maydoni tozalanadi (keyingi skan uchun tayyor).
 */
async function barkodSkan() {
    const q = document.getElementById('qidiruv').value.trim();
    if (!q) return;

    const url = new URL('/pos/tovarlar', window.location.origin);
    url.searchParams.set('filial_id', FILIAL_ID);
    url.searchParams.set('qidiruv', q);
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    const aniqMos = data.find(t => (t.barkodlar_royxati || [t.barkod]).includes(q));
    if (aniqMos) {
        if (aniqMos.qoldiq > 0) {
            savatQosh(aniqMos.id, aniqMos.nomi, aniqMos.sotish_narx, aniqMos.birlik, aniqMos.qoldiq);
        } else {
            alert(`«${aniqMos.nomi}»: omborda qoldiq yo'q!`);
        }
        document.getElementById('qidiruv').value = '';
        barcha();
    } else {
        // Aniq barkod topilmadi — oddiy qidiruv natijasi sifatida ko'rsatamiz
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

/** Qoldiq miqdorini "0,00" formatda (vergul kasr ajratkichi, 2 xona) qaytaradi */
function qoldiqFmt(v) {
    return Number(v).toFixed(2).replace('.', ',');
}

/** Tovarlar ro'yxatidagi har bir qatorning "Qoldiq" ustunini savatdagi miqdorga qarab yangilaydi */
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

/** Chegirma inputidagi qiymatni (so'm yoki %) yakuniy so'm summasiga aylantiradi. */
function chegirmaSommaHisobla(umumiy) {
    const kiritilgan = parseFloat(document.getElementById('chegirma').value) || 0;
    const tur = document.querySelector('[name=chegirma-tur]:checked').value;
    return tur === 'foiz' ? Math.round(umumiy * kiritilgan / 100) : kiritilgan;
}

function jamiHisob() {
    const ids = Object.keys(savat);
    const umumiy = ids.reduce((s, id) => s + savat[id].narx * savat[id].miqdor, 0);
    const chegirma = chegirmaSommaHisobla(umumiy);
    const jami = Math.max(0, umumiy - chegirma);
    jamiBaza = jami;
    chegirmaHisoblangan = chegirma;

    document.getElementById('sum-umumiy').textContent = umumiy.toLocaleString('uz-UZ') + ' so\'m';
    document.getElementById('sum-jami').textContent   = jami.toLocaleString('uz-UZ') + ' so\'m';

    // To'lov turiga qarab summa maydonlarini avtomat yangilash
    tolovAvtoToldir();
}

/**
 * To'lov turi/jami o'zgarganda summa maydonlarini avtomat to'ldiradi:
 *  - naqd    → naqd-inp = jami (butunicha)
 *  - plastik → plastik-inp = jami
 *  - aralash → foydalanuvchi qaysi maydonni oxirgi tahrirlagan bo'lsa,
 *              ikkinchisi avtomat "jami - kiritilgan" ga to'ldiriladi.
 */
function tolovAvtoToldir() {
    const tur = document.querySelector('[name=tolov]:checked').value;
    const naqdInp = document.getElementById('naqd-inp');
    const plastikInp = document.getElementById('plastik-inp');

    if (tur === 'naqd') {
        naqdInp.value = jamiBaza || '';
        plastikInp.value = '';
    } else if (tur === 'plastik') {
        plastikInp.value = jamiBaza || '';
        naqdInp.value = '';
    } else if (tur === 'aralash') {
        // Aralashda oxirgi tahrirlangan maydonga qarab ikkinchisini to'ldiramiz
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
    // Plastik/aralashda ortiqcha pul qaytarilmaydi (karta/aralash to'lov aniq summada bo'ladi) — faqat sof naqd uchun ko'rsatamiz
    const qayta = tur === 'naqd' ? Math.max(0, naqd - jamiBaza) : 0;
    document.getElementById('qayta-pul').value = qayta.toLocaleString('uz-UZ') + ' so\'m';
}

/** Kiritilgan summalar jamidan kamligini tekshiradi va yetishmayotgan summani ko'rsatadi. */
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
    // Bloklarni yashirmaymiz (sakrash bo'lmasligi uchun) — faqat tegishli
    // bo'lmagan maydonlarni bloklab, xiralashtiramiz.
    document.getElementById('naqd-inp').disabled = (tur === 'plastik');
    document.getElementById('plastik-inp').disabled = (tur === 'naqd');
    document.getElementById('qayta-pul').disabled = (tur !== 'naqd');
    qolFieldQolgan = null;
    tolovAvtoToldir();
}

// ─── Sotuvni bajarish ─────────────────────────────────────────────
async function sotuvBajar() {
    const ids = Object.keys(savat);
    if (!ids.length) { alert("Savat bo'sh!"); return; }

    if (!yetishmovchiTekshir()) {
        return; // Summa yetarli emas — tasdiqlanmaydi
    }

    const btn = document.getElementById('sot-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saqlanmoqda...';

    const tolovInp = document.querySelector('[name=tolov]:checked');
    const tur      = tolovInp.value;
    const tolovUsuliId = tolovInp.dataset.usuliId ? parseInt(tolovInp.dataset.usuliId) : null;
    const naqdSumma    = parseFloat(document.getElementById('naqd-inp').value) || 0;
    const plastikSumma = parseFloat(document.getElementById('plastik-inp').value) || 0;

    const payload = {
        filial_id:      FILIAL_ID,
        tolov_turi:     tur,
        tolov_usuli_id: tolovUsuliId,
        chegirma:       chegirmaHisoblangan,
        naqd_summa:     tur==='plastik' ? 0 : naqdSumma,
        plastik_summa:  tur==='naqd' ? 0 : plastikSumma,
        mijoz_ism:      document.getElementById('mijoz-ism').value,
        tolov_izoh:     document.getElementById('tolov-izoh').value,
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
