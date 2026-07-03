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

.olcham-card { border:2px solid #e2e8f4; border-radius:8px; padding:12px 16px; cursor:pointer; transition:all .15s; text-align:center; }
.olcham-card:hover { border-color:#93c5fd; }
.olcham-card.active { border-color:#2563eb; background:#eff6ff; }
.olcham-card .nomi { font-weight:700; font-size:.85rem; }
.olcham-card .ulcham { font-size:.7rem; color:#94a3b8; }

.wrap-panel { display:flex; gap:16px; height:calc(100vh - 220px); }
.left-panel { flex:1; display:flex; flex-direction:column; border:1px solid #c7d7f8; border-radius:8px; overflow:hidden; }
.right-panel { width:280px; flex-shrink:0; }

/* Print media — bosma sahifa uchun */
@media print {
    body * { visibility: hidden; }
    #print-area, #print-area * { visibility: visible; }
    #print-area { position: absolute; left:0; top:0; width:100%; }
    @page { margin: 4mm; }
}
#print-area { display:none; }
.etiketka {
    display:inline-flex; flex-direction:column; align-items:center; justify-content:center;
    border:1px dashed #ccc; margin:2mm; page-break-inside: avoid; overflow:hidden;
    padding:1mm;
}
.etiketka .e-nomi { font-size:2.2mm; font-weight:700; text-align:center; line-height:1.1; max-height:5mm; overflow:hidden; }
.etiketka .e-narx { font-size:2.6mm; font-weight:800; color:#000; margin-top:.5mm; }
.etiketka svg { max-width:100%; }

/* O'lcham sinflari (mm) */
.sz-kichik  { width:30mm; height:20mm; }
.sz-orta    { width:40mm; height:28mm; }
.sz-katta   { width:58mm; height:40mm; }
.sz-kichik .e-nomi { font-size:2mm; }
.sz-orta   .e-nomi { font-size:2.6mm; }
.sz-katta  .e-nomi { font-size:3.4mm; }
.sz-kichik .e-narx { font-size:2.4mm; }
.sz-orta   .e-narx { font-size:3.2mm; }
.sz-katta  .e-narx { font-size:4.2mm; }
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
            <input type="search" id="qidiruv" name="etiketka_qidiruv" class="form-control form-control-sm" style="max-width:220px" placeholder="Tovar yoki barkod qidirish..." oninput="yuklash()" autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore>
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

    {{-- O'ng: o'lcham va chop etish --}}
    <div class="right-panel">
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

<script>
const OLDINDAN_TANLANGAN = @json($oldindanTanlangan);
let tanlangan = {}; // {tovar_id: {nomi, barkod, narx, birlik, miqdor}}
let olcham = 'kichik';

document.addEventListener('DOMContentLoaded', () => {
    // Oldindan tanlanganlarni saqlab qo'yamiz — ro'yxat yuklanganda checkbox holatiga o'rnatiladi
    OLDINDAN_TANLANGAN.forEach(o => { tanlangan['pending_' + o.id] = o.miqdor; });
    yuklash();
});

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
            tanlangan[t.id] = { nomi: t.nomi, barkod: t.barkod, narx: t.sotish_narx, birlik: t.birlik, miqdor: oldinMiqdor };
        }
        return `
        <tr class="${belgilangan ? 'selected' : ''}" id="row-${t.id}" onclick="qatorBosildi(event, ${t.id}, '${(t.nomi||'').replace(/'/g,"\\'")}', '${t.barkod||''}', ${t.sotish_narx}, '${t.birlik}')">
            <td onclick="event.stopPropagation()"><input type="checkbox" class="form-check-input" id="chk-${t.id}" ${belgilangan?'checked':''} onchange="checkboxOzgardi(${t.id}, '${(t.nomi||'').replace(/'/g,"\\'")}', '${t.barkod||''}', ${t.sotish_narx}, '${t.birlik}')"></td>
            <td class="fw-semibold">${t.nomi}</td>
            <td class="text-muted" style="font-size:.75rem">${t.barkod || '—'}</td>
            <td class="num" style="color:#15803d">${Number(t.sotish_narx).toLocaleString('uz-UZ')}</td>
            <td onclick="event.stopPropagation()"><input type="number" class="form-control form-control-sm miqdor-inp" id="miqdor-${t.id}" min="1" value="${tanlangan[t.id]?.miqdor || 1}" oninput="miqdorOzgardi(${t.id})"></td>
        </tr>`;
    }).join('');

    hisoblaYangila();
}

function qatorBosildi(e, id, nomi, barkod, narx, birlik) {
    const chk = document.getElementById('chk-' + id);
    chk.checked = !chk.checked;
    checkboxOzgardi(id, nomi, barkod, narx, birlik);
}

function checkboxOzgardi(id, nomi, barkod, narx, birlik) {
    const chk = document.getElementById('chk-' + id);
    const row = document.getElementById('row-' + id);
    if (chk.checked) {
        const miqdor = parseInt(document.getElementById('miqdor-' + id).value) || 1;
        tanlangan[id] = { nomi, barkod, narx, birlik, miqdor };
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

function hisoblaYangila() {
    const ids = Object.keys(tanlangan).filter(k => !isNaN(k));
    document.getElementById('tanlangan-soni').textContent = ids.length + ' ta';
    const jami = ids.reduce((s, id) => s + (parseInt(tanlangan[id].miqdor) || 1), 0);
    document.getElementById('jami-etiketka').textContent = jami + ' ta';
}

function chopEtish() {
    const ids = Object.keys(tanlangan).filter(k => !isNaN(k));
    if (!ids.length) { alert("Kamida bitta tovar tanlang!"); return; }

    const printArea = document.getElementById('print-area');
    printArea.innerHTML = '';
    printArea.style.display = 'flex';
    printArea.style.flexWrap = 'wrap';

    let svgIndex = 0;
    ids.forEach(id => {
        const t = tanlangan[id];
        const miqdor = parseInt(t.miqdor) || 1;
        for (let i = 0; i < miqdor; i++) {
            const div = document.createElement('div');
            div.className = 'etiketka sz-' + olcham;
            div.innerHTML = `
                <div class="e-nomi">${t.nomi}</div>
                <svg id="barcode-${id}-${i}"></svg>
                <div class="e-narx">${Number(t.narx).toLocaleString('uz-UZ')} so'm</div>
            `;
            printArea.appendChild(div);
            if (t.barkod) {
                JsBarcode(`#barcode-${id}-${i}`, t.barkod, {
                    format: "EAN13", width: 1.3, height: olcham === 'kichik' ? 22 : (olcham === 'orta' ? 32 : 45),
                    fontSize: olcham === 'kichik' ? 8 : (olcham === 'orta' ? 10 : 13), margin: 2,
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
