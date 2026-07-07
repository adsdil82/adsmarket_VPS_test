@extends('layouts.app')
@section('title', "Shtrix-kod etiketkalari")
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('ombor.index') }}">Ombor</a></li>
<li class="breadcrumb-item active">Etiketka chop etish</li>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<style>
.bank-table { border-collapse:collapse; font-size:.83rem; width:100%; }
.bank-table thead th { position:sticky; top:0; z-index:5; background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; font-weight:700; font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; padding:8px 10px; border-right:1px solid rgba(255,255,255,.15); white-space:nowrap; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { border-bottom:1px solid #e2e8f4; cursor:pointer; }
.bank-table tbody tr:hover { background:#eff6ff; }
.bank-table tbody tr:nth-child(even) { background:#f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background:#fff; }
.bank-table tbody tr.selected { background:#dbeafe !important; }
.bank-table tbody td { padding:6px 10px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.miqdor-inp { width:60px; }

.olcham-card { border:2px solid #e2e8f4; border-radius:8px; padding:10px 12px; cursor:pointer; transition:all .15s; text-align:center; }
.olcham-card:hover { border-color:#93c5fd; }
.olcham-card.active { border-color:#2563eb; background:#eff6ff; }
.olcham-card .nomi { font-weight:700; font-size:.8rem; }
.olcham-card .ulcham { font-size:.68rem; color:#94a3b8; }

.turi-card { border:2px solid #e2e8f4; border-radius:8px; padding:8px 10px; cursor:pointer; text-align:center; font-weight:700; font-size:.78rem; flex:1; }
.turi-card:hover { border-color:#93c5fd; }
.turi-card.active { border-color:#2563eb; background:#eff6ff; color:#1d4ed8; }

.shablon-row { display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:6px; cursor:pointer; border:1px solid transparent; }
.shablon-row:hover { background:#f5f8fd; }
.shablon-row.active { border-color:#2563eb; background:#eff6ff; }
.shablon-swatch { width:26px; height:18px; border-radius:3px; border:1px solid #cbd5e1; flex-shrink:0; }
.shablon-nomi { font-size:.78rem; font-weight:600; flex:1; }
.shablon-del { color:#dc2626; cursor:pointer; font-size:.8rem; }

.wrap-panel { display:flex; gap:16px; height:calc(100vh - 220px); }
.left-panel { flex:1; display:flex; flex-direction:column; border:1px solid #c7d7f8; border-radius:8px; overflow:hidden; }
.right-panel { width:300px; flex-shrink:0; overflow-y:auto; }

/* ── Konstruktor (drag & resize) ─────────────────────────────── */
#konstruktor-canvas { position:relative; border:2px dashed #94a3b8; margin:0 auto; overflow:hidden; }
.k-blok { position:absolute; border:1px solid rgba(37,99,235,.6); background:rgba(219,234,254,.55); display:flex; align-items:center; justify-content:center; font-size:.68rem; font-weight:700; color:#1d4ed8; cursor:move; user-select:none; text-align:center; line-height:1.1; }
.k-blok .k-resize { position:absolute; right:0; bottom:0; width:12px; height:12px; background:#2563eb; cursor:nwse-resize; border-radius:2px 0 6px 0; }
.k-fs-inp { width:60px; }

/* ── Print media — bosma sahifa uchun ────────────────────────── */
@media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { position: absolute; left:0; top:0; width:100%; }
    @page { margin: 4mm; }
}
#print-area { display:none; }
.etiketka-blok { display:inline-block; vertical-align:top; margin:2mm; border:1px dashed #ccc; page-break-inside: avoid; overflow:hidden; box-sizing:border-box; }
.e-blok { display:flex; align-items:center; justify-content:center; overflow:hidden; text-align:center; line-height:1.05; box-sizing:border-box; padding:.3mm; }
.e-top { font-weight:800; }
.e-inner { flex-direction:column; gap:.3mm; }
.e-asl { text-decoration:line-through; opacity:.65; font-size:.85em; }
.e-nasiya-narx { font-weight:800; }
.e-jadval { display:flex; justify-content:space-around; width:100%; font-weight:700; }
.e-jadval span { flex:1; }
.e-narx, .e-naqd { font-weight:800; width:100%; border-radius:1mm; padding:.3mm; }
.e-naqd { color:#fff; }
.e-badge { position:absolute; top:0; right:0; color:#fff; font-weight:800; font-size:1.8mm; padding:.5mm 1.5mm; border-radius:0 0 0 2mm; z-index:3; }
.e-barcode svg { max-width:100%; max-height:100%; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-upc-scan text-warning" style="font-size:1.2rem"></i>
    <h5 class="fw-bold mb-0">Shtrix-kod etiketkalari</h5>
    <span class="text-muted small">Tovar tanlang, o'lchamni belgilang va chop eting</span>
</div>

<div class="wrap-panel">
    {{-- Chap: tovarlar jadvali --}}
    <div class="left-panel">
        <div class="p-2 bg-light border-bottom d-flex gap-2 align-items-center">
            <input type="text" id="qidiruv" name="etiketka_qidiruv_maydon" class="form-control form-control-sm" style="max-width:220px" placeholder="Tovar yoki barkod qidirish..." oninput="yuklash()" autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore readonly onfocus="this.removeAttribute('readonly')">
            <select id="guruh-filter" class="form-select form-select-sm" style="max-width:180px" onchange="yuklash()">
                <option value="">Barcha guruhlar</option>
                @foreach($guruhlar as $g)
                <option value="{{ $g->id }}">{{ $g->nomi }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary btn-sm" onclick="hammasiniTanlash()">Hammasini tanlash</button>
            <button class="btn btn-outline-danger btn-sm" onclick="tanlovniTozala()">Tozalash</button>
        </div>
        <div style="flex:1;overflow-y:auto">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th style="width:30px"></th>
                        <th class="tl">Tovar</th>
                        <th class="tl" style="width:120px">Barkod</th>
                        <th style="width:100px">Narx</th>
                        <th style="width:80px">Soni</th>
                    </tr>
                </thead>
                <tbody id="tovar-tbody">
                    <tr><td colspan="5" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- O'ng: turi, o'lcham, shablon, chop etish --}}
    <div class="right-panel">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)"><span class="fw-bold small">Etiketka turi</span></div>
            <div class="card-body d-flex gap-2 p-2">
                <div class="turi-card active" data-turi="oddiy" onclick="turiTanlash(this,'oddiy')">Oddiy</div>
                <div class="turi-card" data-turi="nasiya" onclick="turiTanlash(this,'nasiya')">Nasiyaga</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)"><span class="fw-bold small">Etiketka o'lchami</span></div>
            <div class="card-body d-flex flex-column gap-2">
                <div class="olcham-card active" data-olcham="kichik" onclick="olchamTanlash(this,'kichik')">
                    <div class="nomi">📏 Kichik</div>
                    <div class="ulcham">30 × 20 mm</div>
                </div>
                <div class="olcham-card" data-olcham="orta" onclick="olchamTanlash(this,'orta')">
                    <div class="nomi">📏 O'rta</div>
                    <div class="ulcham">40 × 28 mm</div>
                </div>
                <div class="olcham-card" data-olcham="katta" onclick="olchamTanlash(this,'katta')">
                    <div class="nomi">📏 Katta</div>
                    <div class="ulcham">58 × 40 mm</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)">
                <span class="fw-bold small">Shablon</span>
                <button class="btn btn-sm btn-outline-primary py-0" onclick="konstruktorOch()"><i class="bi bi-sliders"></i> Konstruktor</button>
            </div>
            <div class="card-body p-2" id="shablon-list" style="max-height:200px;overflow-y:auto">
                <div class="text-center text-muted small py-2"><div class="spinner-border spinner-border-sm"></div></div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Tanlangan tovar:</span>
                    <span class="fw-bold" id="tanlangan-soni">0 ta</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">Jami etiketka:</span>
                    <span class="fw-bold text-success" id="jami-etiketka">0 ta</span>
                </div>
                <button class="btn btn-primary w-100" onclick="chopEtish()">
                    <i class="bi bi-printer me-1"></i>Chop etish
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Chop etish uchun yashirin blok --}}
<div id="print-area"></div>

{{-- ── Konstruktor modali ──────────────────────────────────────── --}}
<div class="modal fade" id="konstruktor-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(90deg,#1e3a8a,#1d4ed8);color:#fff">
                <h6 class="mb-0 fw-bold"><i class="bi bi-sliders me-2"></i>Etiketka konstruktori</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="small text-muted mb-2">Bloklarni sudrab joyini, burchagidan tortib o'lchamini o'zgartiring.</div>
                        <div id="konstruktor-canvas"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <label class="form-label fw-medium small">Shablon nomi</label>
                            <input type="text" id="k-nomi" class="form-control form-control-sm" placeholder="Masalan: Yozgi aksiya">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-4">
                                <label class="form-label fw-medium small">Fon</label>
                                <input type="color" id="k-reng-fon" class="form-control form-control-sm form-control-color w-100">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-medium small">Matn</label>
                                <input type="color" id="k-reng-matn" class="form-control form-control-sm form-control-color w-100">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-medium small">Urg'u</label>
                                <input type="color" id="k-reng-urgu" class="form-control form-control-sm form-control-color w-100">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium small">Burchak belgisi (ixtiyoriy)</label>
                            <input type="text" id="k-belgi" class="form-control form-control-sm" placeholder="Masalan: AKSIYA" maxlength="40">
                        </div>
                        <div class="mb-1 small fw-bold text-muted">Matn hajmi (mm, 58×40 asosida)</div>
                        <div class="row g-2 mb-3">
                            <div class="col-4"><label class="form-label small mb-0">Yuqori</label><input type="number" id="k-fs-top" class="form-control form-control-sm k-fs-inp" step="0.1" min="1"></div>
                            <div class="col-4"><label class="form-label small mb-0">Ichki</label><input type="number" id="k-fs-inner" class="form-control form-control-sm k-fs-inp" step="0.1" min="1"></div>
                            <div class="col-4"><label class="form-label small mb-0">Pastki</label><input type="number" id="k-fs-bottom" class="form-control form-control-sm k-fs-inp" step="0.1" min="1"></div>
                        </div>
                        <div class="small text-muted">Ko'k — Nomi &nbsp;|&nbsp; Yashil — Narx/jadval &nbsp;|&nbsp; Sariq — Pastki &nbsp;|&nbsp; Kulrang — Shtrix-kod</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="button" class="btn btn-primary" onclick="konstruktorSaqlash()"><i class="bi bi-save me-1"></i>Shablon sifatida saqlash</button>
            </div>
        </div>
    </div>
</div>

<script>
const OLDINDAN_TANLANGAN = @json($oldindanTanlangan);
const NASIYA_OYLAR = @json(\App\Http\Controllers\BarcodeLabelController::NASIYA_OYLAR);
const NAQD_CHEGIRMA_FOIZ = {{ \App\Http\Controllers\BarcodeLabelController::NAQD_CHEGIRMA_FOIZ }};
const OLCHAM_MM = { kichik: {w:30,h:20}, orta: {w:40,h:28}, katta: {w:58,h:40} };
const KATTA_H = 40;

let tanlangan = {}; // {tovar_id: {nomi, barkod, tan_narx, sotish_narx, nasiya_narx, birlik, miqdor}}
let olcham = 'kichik';
let etiketkaTuri = 'oddiy';
let SHABLONLAR = [];
let tanlanganShablonId = null;
let konstruktorDraft = null;

document.addEventListener('DOMContentLoaded', () => {
    OLDINDAN_TANLANGAN.forEach(o => { tanlangan['pending_' + o.id] = o.miqdor; });
    yuklash();
    shablonlarniYukla();
});

/* ── Tovarlar ro'yxati ────────────────────────────────────────── */
async function yuklash() {
    const tbody = document.getElementById('tovar-tbody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>';

    const url = new URL('{{ route("ombor.etiketka.tovarlar") }}', window.location.origin);
    const q = document.getElementById('qidiruv').value;
    const g = document.getElementById('guruh-filter').value;
    if (q) url.searchParams.set('qidiruv', q);
    if (g) url.searchParams.set('guruh_id', g);

    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Topilmadi</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(t => {
        const oldinMiqdor = tanlangan['pending_' + t.id];
        const belgilangan = !!tanlangan[t.id] || !!oldinMiqdor;
        if (oldinMiqdor && !tanlangan[t.id]) {
            tanlangan[t.id] = { nomi: t.nomi, barkod: t.barkod, tan_narx: t.tan_narx, sotish_narx: t.sotish_narx, nasiya_narx: t.nasiya_narx, birlik: t.birlik, miqdor: oldinMiqdor };
        }
        return `
        <tr class="${belgilangan ? 'selected' : ''}" id="row-${t.id}" onclick="qatorBosildi(event, ${t.id})" data-tovar='${JSON.stringify(t).replace(/'/g, "&#39;")}'>
            <td onclick="event.stopPropagation()"><input type="checkbox" class="form-check-input" id="chk-${t.id}" ${belgilangan?'checked':''} onchange="checkboxOzgardi(${t.id})"></td>
            <td class="fw-semibold">${t.nomi}</td>
            <td class="text-muted" style="font-size:.75rem">${t.barkod || '—'}</td>
            <td class="num" style="color:#15803d">${Number(t.sotish_narx).toLocaleString('uz-UZ')}</td>
            <td onclick="event.stopPropagation()"><input type="number" class="form-control form-control-sm miqdor-inp" id="miqdor-${t.id}" min="1" value="${tanlangan[t.id]?.miqdor || 1}" oninput="miqdorOzgardi(${t.id})"></td>
        </tr>`;
    }).join('');

    hisoblaYangila();
}

function qatorMalumot(id) {
    const row = document.getElementById('row-' + id);
    return JSON.parse(row.dataset.tovar.replace(/&#39;/g, "'"));
}

function qatorBosildi(e, id) {
    const chk = document.getElementById('chk-' + id);
    chk.checked = !chk.checked;
    checkboxOzgardi(id);
}

function checkboxOzgardi(id) {
    const chk = document.getElementById('chk-' + id);
    const row = document.getElementById('row-' + id);
    const t = qatorMalumot(id);
    if (chk.checked) {
        const miqdor = parseInt(document.getElementById('miqdor-' + id).value) || 1;
        tanlangan[id] = { nomi: t.nomi, barkod: t.barkod, tan_narx: t.tan_narx, sotish_narx: t.sotish_narx, nasiya_narx: t.nasiya_narx, birlik: t.birlik, miqdor };
        row.classList.add('selected');
    } else {
        delete tanlangan[id];
        row.classList.remove('selected');
    }
    hisoblaYangila();
}

function miqdorOzgardi(id) {
    if (tanlangan[id]) {
        tanlangan[id].miqdor = parseInt(document.getElementById('miqdor-' + id).value) || 1;
        hisoblaYangila();
    }
}

function hammasiniTanlash() {
    document.querySelectorAll('#tovar-tbody input[type=checkbox]').forEach(chk => {
        if (!chk.checked) chk.click();
    });
}

function tanlovniTozala() {
    tanlangan = {};
    document.querySelectorAll('#tovar-tbody tr').forEach(r => r.classList.remove('selected'));
    document.querySelectorAll('#tovar-tbody input[type=checkbox]').forEach(chk => chk.checked = false);
    hisoblaYangila();
}

function olchamTanlash(el, o) {
    document.querySelectorAll('.olcham-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    olcham = o;
}

function turiTanlash(el, t) {
    document.querySelectorAll('.turi-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    etiketkaTuri = t;
}

function hisoblaYangila() {
    const ids = Object.keys(tanlangan).filter(k => !isNaN(k));
    document.getElementById('tanlangan-soni').textContent = ids.length + ' ta';
    const jami = ids.reduce((s, id) => s + (parseInt(tanlangan[id].miqdor) || 1), 0);
    document.getElementById('jami-etiketka').textContent = jami + ' ta';
}

/* ── Narx hisoblash (nasiya jadvali) ──────────────────────────── */
function narxHisobla(t) {
    const nasiya = Number(t.nasiya_narx) || Number(t.sotish_narx) || 0;
    const asl = Number(t.tan_narx) || 0;
    const naqd = Math.round(nasiya * (1 - NAQD_CHEGIRMA_FOIZ / 100));
    const jadval = NASIYA_OYLAR.map(oy => ({ oy, summa: Math.round(nasiya / oy) }));
    return { asl, nasiya, naqd, jadval };
}

/* ── Shablonlar ───────────────────────────────────────────────── */
async function shablonlarniYukla() {
    const res = await fetch('{{ route("ombor.etiketka.shablonlar") }}', { headers: { 'Accept': 'application/json' } });
    SHABLONLAR = await res.json();
    if (SHABLONLAR.length) tanlanganShablonId = SHABLONLAR[0].id;
    renderShablonlar();
}

function renderShablonlar() {
    const wrap = document.getElementById('shablon-list');
    wrap.innerHTML = SHABLONLAR.map(s => `
        <div class="shablon-row ${s.id === tanlanganShablonId ? 'active' : ''}" onclick="shablonTanlash(${s.id})">
            <div class="shablon-swatch" style="background:${s.reng_fon};border-top:4px solid ${s.reng_urgu}"></div>
            <span class="shablon-nomi">${s.nomi}</span>
            ${s.turi === 'custom' ? `<i class="bi bi-trash shablon-del" onclick="event.stopPropagation();shablonOchirish(${s.id})"></i>` : ''}
        </div>
    `).join('');
}

function shablonTanlash(id) {
    tanlanganShablonId = id;
    renderShablonlar();
}

async function shablonOchirish(id) {
    if (!confirm("Bu shablonni o'chirishni tasdiqlaysizmi?")) return;
    const res = await fetch(`/ombor/etiketka/shablon/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    });
    const data = await res.json();
    if (!data.ok) { alert(data.xabar || "Xatolik yuz berdi"); return; }
    SHABLONLAR = SHABLONLAR.filter(s => s.id !== id);
    if (tanlanganShablonId === id) tanlanganShablonId = SHABLONLAR[0]?.id || null;
    renderShablonlar();
}

function joriyShablon() {
    return SHABLONLAR.find(s => s.id === tanlanganShablonId) || SHABLONLAR[0];
}

/* ── Konstruktor (drag & resize) ──────────────────────────────── */
function konstruktorOch() {
    const asos = joriyShablon();
    konstruktorDraft = JSON.parse(JSON.stringify(asos.joylashuv));

    document.getElementById('k-nomi').value = '';
    document.getElementById('k-reng-fon').value = asos.reng_fon;
    document.getElementById('k-reng-matn').value = asos.reng_matn;
    document.getElementById('k-reng-urgu').value = asos.reng_urgu;
    document.getElementById('k-belgi').value = '';
    document.getElementById('k-fs-top').value = konstruktorDraft.top.fs;
    document.getElementById('k-fs-inner').value = konstruktorDraft.inner.fs;
    document.getElementById('k-fs-bottom').value = konstruktorDraft.bottom.fs;

    konstruktorCanvasChiz();
    new bootstrap.Modal(document.getElementById('konstruktor-modal')).show();
}

function konstruktorCanvasChiz() {
    const dims = OLCHAM_MM[olcham];
    const canvasW = 480;
    const canvasH = Math.round(canvasW * (dims.h / dims.w));
    const canvas = document.getElementById('konstruktor-canvas');
    canvas.style.width = canvasW + 'px';
    canvas.style.height = canvasH + 'px';
    canvas.style.background = document.getElementById('k-reng-fon').value;
    canvas.innerHTML = '';

    const renglar = { top: '#2563eb', inner: '#16a34a', bottom: '#d97706', barcode: '#64748b' };
    const nomlar = { top: 'Nomi (yuqori matn)', inner: 'Narx / Jadval (ichki blok)', bottom: 'Pastki blok', barcode: 'Shtrix-kod' };

    ['top', 'inner', 'bottom', 'barcode'].forEach(key => {
        const b = konstruktorDraft[key];
        const el = document.createElement('div');
        el.className = 'k-blok';
        el.style.left = b.x + '%';
        el.style.top = b.y + '%';
        el.style.width = b.w + '%';
        el.style.height = b.h + '%';
        el.style.borderColor = renglar[key];
        el.style.background = renglar[key] + '22';
        el.style.color = renglar[key];
        el.textContent = nomlar[key];
        const handle = document.createElement('div');
        handle.className = 'k-resize';
        handle.style.background = renglar[key];
        el.appendChild(handle);
        canvas.appendChild(el);
        kBlokniSudrashgaUlash(el, canvas, key);
        kBlokniOlchamgaUlash(handle, el, canvas, key);
    });
}

function kBlokniSudrashgaUlash(el, canvas, key) {
    el.addEventListener('pointerdown', function (e) {
        if (e.target.classList.contains('k-resize')) return;
        e.preventDefault();
        const rect = canvas.getBoundingClientRect();
        const startX = e.clientX, startY = e.clientY;
        const b = konstruktorDraft[key];
        const startXPct = b.x, startYPct = b.y;
        function onMove(ev) {
            const dxPct = (ev.clientX - startX) / rect.width * 100;
            const dyPct = (ev.clientY - startY) / rect.height * 100;
            b.x = Math.max(0, Math.min(100 - b.w, startXPct + dxPct));
            b.y = Math.max(0, Math.min(100 - b.h, startYPct + dyPct));
            el.style.left = b.x + '%';
            el.style.top = b.y + '%';
        }
        function onUp() {
            document.removeEventListener('pointermove', onMove);
            document.removeEventListener('pointerup', onUp);
        }
        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup', onUp);
    });
}

function kBlokniOlchamgaUlash(handle, el, canvas, key) {
    handle.addEventListener('pointerdown', function (e) {
        e.stopPropagation();
        e.preventDefault();
        const rect = canvas.getBoundingClientRect();
        const startX = e.clientX, startY = e.clientY;
        const b = konstruktorDraft[key];
        const startW = b.w, startH = b.h;
        function onMove(ev) {
            const dwPct = (ev.clientX - startX) / rect.width * 100;
            const dhPct = (ev.clientY - startY) / rect.height * 100;
            b.w = Math.max(8, Math.min(100 - b.x, startW + dwPct));
            b.h = Math.max(5, Math.min(100 - b.y, startH + dhPct));
            el.style.width = b.w + '%';
            el.style.height = b.h + '%';
        }
        function onUp() {
            document.removeEventListener('pointermove', onMove);
            document.removeEventListener('pointerup', onUp);
        }
        document.addEventListener('pointermove', onMove);
        document.addEventListener('pointerup', onUp);
    });
}

document.addEventListener('input', function (e) {
    if (e.target.id === 'k-reng-fon') {
        const canvas = document.getElementById('konstruktor-canvas');
        if (canvas) canvas.style.background = e.target.value;
    }
});

async function konstruktorSaqlash() {
    const nomi = document.getElementById('k-nomi').value.trim();
    if (!nomi) { alert("Shablon nomini kiriting!"); return; }

    konstruktorDraft.top.fs = parseFloat(document.getElementById('k-fs-top').value) || konstruktorDraft.top.fs;
    konstruktorDraft.inner.fs = parseFloat(document.getElementById('k-fs-inner').value) || konstruktorDraft.inner.fs;
    konstruktorDraft.bottom.fs = parseFloat(document.getElementById('k-fs-bottom').value) || konstruktorDraft.bottom.fs;

    const body = {
        nomi,
        reng_fon: document.getElementById('k-reng-fon').value,
        reng_matn: document.getElementById('k-reng-matn').value,
        reng_urgu: document.getElementById('k-reng-urgu').value,
        belgi_matni: document.getElementById('k-belgi').value.trim() || null,
        joylashuv: konstruktorDraft,
    };

    const res = await fetch('{{ route("ombor.etiketka.shablon.saqlash") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!data.ok) { alert("Saqlashda xatolik yuz berdi."); return; }

    SHABLONLAR.push(data.shablon);
    tanlanganShablonId = data.shablon.id;
    renderShablonlar();
    bootstrap.Modal.getInstance(document.getElementById('konstruktor-modal')).hide();
}

/* ── Chop etish ───────────────────────────────────────────────── */
function etiketkaBlokHtml(shablon, t, uid) {
    const dims = OLCHAM_MM[olcham];
    const scaleH = dims.h / KATTA_H;
    const jl = shablon.joylashuv;
    const blokStil = (b, bilanFs) => `left:${b.x}%;top:${b.y}%;width:${b.w}%;height:${b.h}%;${bilanFs ? `font-size:${(b.fs * scaleH).toFixed(2)}mm;` : ''}color:${shablon.reng_matn}`;

    let innerHtml, bottomHtml;
    if (etiketkaTuri === 'nasiya') {
        const h = narxHisobla(t);
        innerHtml = `
            ${h.asl ? `<div class="e-asl">Asl: ${h.asl.toLocaleString('uz-UZ')}</div>` : ''}
            <div class="e-nasiya-narx" style="color:${shablon.reng_urgu}">Nasiya: ${h.nasiya.toLocaleString('uz-UZ')}</div>
            <div class="e-jadval">${h.jadval.map(j => `<span>${j.oy} oy<br>${j.summa.toLocaleString('uz-UZ')}</span>`).join('')}</div>
        `;
        bottomHtml = `<div class="e-naqd" style="background:${shablon.reng_urgu}">Naqd: ${h.naqd.toLocaleString('uz-UZ')} (-${NAQD_CHEGIRMA_FOIZ}%)</div>`;
    } else {
        innerHtml = '';
        bottomHtml = `<div class="e-narx">${Number(t.sotish_narx || 0).toLocaleString('uz-UZ')} so'm</div>`;
    }

    const badge = shablon.belgi_matni ? `<div class="e-badge" style="background:${shablon.reng_urgu}">${shablon.belgi_matni}</div>` : '';

    return `
    <div class="etiketka-blok" style="width:${dims.w}mm;height:${dims.h}mm;background:${shablon.reng_fon};position:relative;">
        ${badge}
        <div class="e-blok e-top" style="position:absolute;${blokStil(jl.top, true)}">${t.nomi}</div>
        <div class="e-blok e-inner" style="position:absolute;${blokStil(jl.inner, true)}">${innerHtml}</div>
        <div class="e-blok e-bottom" style="position:absolute;${blokStil(jl.bottom, true)}">${bottomHtml}</div>
        <div class="e-blok e-barcode" style="position:absolute;${blokStil(jl.barcode, false)}"><svg id="barcode-${uid}"></svg></div>
    </div>`;
}

function chopEtish() {
    const ids = Object.keys(tanlangan).filter(k => !isNaN(k));
    if (!ids.length) { alert("Kamida bitta tovar tanlang!"); return; }

    const shablon = joriyShablon();
    if (!shablon) { alert("Shablon topilmadi, sahifani yangilang."); return; }

    const printArea = document.getElementById('print-area');
    printArea.innerHTML = '';
    printArea.style.display = 'flex';
    printArea.style.flexWrap = 'wrap';

    const dims = OLCHAM_MM[olcham];
    const barcodeH = olcham === 'kichik' ? 20 : (olcham === 'orta' ? 28 : 38);
    const barcodeFs = olcham === 'kichik' ? 7 : (olcham === 'orta' ? 9 : 12);

    ids.forEach(id => {
        const t = tanlangan[id];
        const miqdor = parseInt(t.miqdor) || 1;
        for (let i = 0; i < miqdor; i++) {
            const uid = `${id}-${i}`;
            printArea.insertAdjacentHTML('beforeend', etiketkaBlokHtml(shablon, t, uid));
            if (t.barkod) {
                JsBarcode(`#barcode-${uid}`, t.barkod, {
                    format: "EAN13", width: 1.2, height: barcodeH, fontSize: barcodeFs, margin: 1,
                });
            }
        }
    });

    setTimeout(() => {
        window.print();
        printArea.style.display = 'none';
    }, 200);
}
</script>
@endsection
