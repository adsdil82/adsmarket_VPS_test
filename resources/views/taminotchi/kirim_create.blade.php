@extends('layouts.app')
@section('title', 'Kirim kiritish')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.show',$taminotchi) }}">{{ $taminotchi->nomi }}</a></li>
    <li class="breadcrumb-item active">Kirim kiritish</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#14532d,#15803d); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#14532d,#15803d);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #86efac; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }
.bft-inforow { display:flex; flex-wrap:wrap; align-items:center; border-bottom:1px solid #dcfce7; }
.bft-inforow:last-child { border-bottom:none; }
.bft-inforow:nth-child(even) { background:#f0fdf4; }
.bft-cell-label { font-weight:700; color:#334155; white-space:nowrap; background:#f0fdf4; padding:9px 12px; border-right:1px solid #dcfce7; display:flex; align-items:center; }
.bft-cell-value { padding:9px 12px; display:flex; align-items:center; border-right:1px solid #dcfce7; }
.bft-cell-value:last-child { border-right:none; flex:1; }
.bft-cell-value.bft-grow { flex:1; }

.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #86efac; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#16a34a,#14532d); color:#fff; font-weight:800;
    font-size:.64rem; letter-spacing:.02em; text-transform:uppercase; padding:6px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#f0fdf4; }
.bank-table tbody td { padding:5px 6px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #86efac; border-radius:0 0 6px 6px; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#14532d,#15803d) !important; color:#fff; font-weight:800;
    padding:7px 8px; border-top:2px solid #16a34a;
}

.del-qator { cursor: pointer; color: #dc3545; font-size:1.05rem; }
.yangi-tovar-belgi { font-size:.65rem; color:#b45309; background:#fff7ed; border:1px solid #fdba74; border-radius:4px; padding:1px 5px; display:inline-flex; align-items:center; gap:3px; margin-top:3px; cursor:default; white-space:nowrap; }
.yangi-tovar-belgi .bekor-link { cursor:pointer; color:#dc3545; font-weight:bold; }
.ustama-bosh { background:#fef9c3 !important; min-width:80px; }
.ustama-bosh input { min-width:70px; }
#qatorlar-table th { white-space:nowrap; }
.auto-expand { overflow:hidden; resize:none; min-height:31px; line-height:1.4; }
</style>
@endpush

@section('content')

{{-- ── Sarlavha ─────────────────────────────────────────────────── --}}
<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-box-arrow-in-down fs-5"></i>
        <span class="fw-bold">{{ $taminotchi->nomi }} — Kirim kiritish</span>
    </div>
    <a href="{{ route('taminotchi.show',$taminotchi) }}" class="btn btn-sm btn-light py-1">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<form method="POST" action="{{ route('taminotchi.kirim.store',$taminotchi) }}" id="kirim-form">
@csrf

{{-- ── Hujjat ma'lumotlari ──────────────────────────────────────── --}}
<div class="bft-section-title mb-0"><span><i class="bi bi-card-list me-1"></i>Hujjat ma'lumotlari</span></div>
<div class="bft-wrap mb-3">
    <div class="bft-inforow">
        <div class="bft-cell-label">Hujjat raqami (Schyot-faktura)</div>
        <div class="bft-cell-value">
            <input type="text" name="hujjat_raqam" class="form-control form-control-sm" style="width:180px" value="{{ old('hujjat_raqam') }}" placeholder="SF-2025-001">
        </div>
        <div class="bft-cell-label">Kirim sanasi <span class="text-danger">*</span></div>
        <div class="bft-cell-value">
            <input type="date" name="kirim_sana" class="form-control form-control-sm" style="width:150px" value="{{ old('kirim_sana', now()->toDateString()) }}" required>
        </div>
        <div class="bft-cell-label">Umumiy savdo ustama %</div>
        <div class="bft-cell-value bft-grow">
            <div class="input-group input-group-sm" style="max-width:200px">
                <input type="number" id="umumiy-ustama-foiz" class="form-control" min="0" step="1" value="20" placeholder="Masalan: 20">
                <span class="input-group-text">%</span>
                <button type="button" class="btn btn-outline-warning fw-bold text-nowrap" onclick="umumiyUstamaQollash()" title="Barcha qatorlarga qo'llash">
                    <i class="bi bi-arrow-down-up"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="bft-inforow">
        <div class="bft-cell-label">Izoh</div>
        <div class="bft-cell-value bft-grow">
            <textarea name="izoh" class="form-control form-control-sm auto-expand" style="width:100%" rows="1" placeholder="Ixtiyoriy...">{{ old('izoh') }}</textarea>
        </div>
    </div>
</div>

{{-- ── Tovarlar ro'yxati ────────────────────────────────────────── --}}
<div class="bft-section-title mb-0">
    <span><i class="bi bi-box-seam me-1"></i>Tovarlar ro'yxati</span>
    <button type="button" class="btn btn-sm btn-light py-0 fw-bold" onclick="qatorQosh()">
        <i class="bi bi-plus-lg me-1"></i>Qator qo'shish
    </button>
</div>
<div class="small text-muted my-2">
    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">POS %/Nasiya %</span>
    ustunlari barcha qatorlarda tahrirlanadi — mavjud tovar uchun joriy narx ko'rsatiladi, o'zgartirsangiz katalogdagi narx ham yangilanadi.
</div>
<div class="bank-wrap mb-3">
    <table class="bank-table" id="qatorlar-table">
        <thead>
            <tr>
                <th class="tl" style="width:13%">Tovar guruhi</th>
                <th class="tl" style="width:17%">Tovar nomi <span class="text-danger">*</span></th>
                <th style="width:7%">Miqdor</th>
                <th style="width:7%">Birlik</th>
                <th style="width:9%">Kirim narxi</th>
                <th style="width:7%">POS %</th>
                <th style="width:9%">POS narxi</th>
                <th style="width:7%">Nasiya %</th>
                <th style="width:9%">Nasiya narxi</th>
                <th style="width:8%">Jami</th>
                <th style="width:30px"></th>
            </tr>
        </thead>
        <tbody id="qatorlar-body"></tbody>
        <tfoot>
            <tr>
                <td class="tl" colspan="9">Jami:</td>
                <td class="num" id="umumiy-jami">0</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-success fw-bold px-4">
        <i class="bi bi-check2 me-1"></i>Kirimni saqlash
    </button>
    <a href="{{ route('taminotchi.show',$taminotchi) }}" class="btn btn-outline-secondary">Bekor</a>
</div>
</form>
@endsection

@php
$tovarlarJs = $tovarlar->map(fn($t) => [
    'id' => $t->id, 'nomi' => $t->nomi, 'narx' => $t->sotish_narx, 'nasiya_narx' => $t->nasiya_narx,
    'tan_narx' => $t->tan_narx, 'birlik' => $t->birlik, 'guruh_id' => $t->guruh_id,
]);
@endphp
@push('scripts')
<script>
var qatorCount = 0;
var BARCHA_TOVARLAR = @json($tovarlarJs);
var GURUH_OPTIONS_HTML = @json($guruhlar)
    .map(function(g) { return '<option value="' + g.id + '">' + g.nomi + '</option>'; })
    .join('');

function qatorQosh() {
    var idx = qatorCount++;
    var tbody = document.getElementById('qatorlar-body');
    var tr = document.createElement('tr');
    tr.className = 'qator-row';
    tr.innerHTML = `
        <td class="tl">
            <select class="form-select form-select-sm guruh-select" data-idx="${idx}">
                <option value="">— Guruh tanlang —</option>
                ${GURUH_OPTIONS_HTML}
            </select>
            <input type="hidden" name="qatorlar[${idx}][guruh_id]" class="guruh-id-input">
        </td>
        <td class="tl">
            <input type="text" name="qatorlar[${idx}][nomi]" class="form-control form-control-sm nomi-input"
                   list="tovar-list-${idx}" required placeholder="Guruh tanlang yoki to'g'ridan-to'g'ri yozing" autocomplete="off">
            <datalist id="tovar-list-${idx}"></datalist>
            <input type="hidden" name="qatorlar[${idx}][tovar_id]" class="tovar-id-input">
            <div class="yangi-tovar-belgi" style="display:none">
                🆕 Yangi tovar
                <span class="bekor-link" title="Bekor qilish">✕</span>
            </div>
        </td>
        <td><input type="number" name="qatorlar[${idx}][miqdor]" class="form-control form-control-sm miqdor-input num" value="1" min="0.001" step="0.001" required></td>
        <td><input type="text" name="qatorlar[${idx}][birlik]" class="form-control form-control-sm birlik-input" value="dona"></td>
        <td><input type="number" name="qatorlar[${idx}][narx]" class="form-control form-control-sm narx-input num" min="0" step="100" required></td>
        <td class="ustama-bosh" style="display:none"><input type="number" class="form-control form-control-sm ustama-pos-foiz num" min="0" step="1" value="0"></td>
        <td class="ustama-bosh" style="display:none">
            <input type="number" class="form-control form-control-sm pos-narx-input num" min="0" step="100">
            <input type="hidden" name="qatorlar[${idx}][pos_narx]" class="pos-narx-hidden">
        </td>
        <td class="ustama-bosh" style="display:none"><input type="number" class="form-control form-control-sm ustama-nasiya-foiz num" min="0" step="1" value="20"></td>
        <td class="ustama-bosh" style="display:none">
            <input type="number" class="form-control form-control-sm nasiya-narx-input num" min="0" step="100">
            <input type="hidden" name="qatorlar[${idx}][nasiya_narx]" class="nasiya-narx-hidden">
        </td>
        <td class="num fw-bold jami-td">0</td>
        <td class="text-center"><i class="bi bi-x-circle del-qator" onclick="qatorOchir(this)"></i></td>
    `;
    tbody.appendChild(tr);
    jamiYangilash();
    bindEvents(tr);
    return tr;
}

function qatorOchir(el) {
    el.closest('tr').remove();
    jamiYangilash();
}

function jamiYangilash() {
    var jami = 0;
    document.querySelectorAll('.qator-row').forEach(function(tr) {
        var m = parseFloat(tr.querySelector('.miqdor-input').value) || 0;
        var n = parseFloat(tr.querySelector('.narx-input').value) || 0;
        var q = Math.round(m * n);
        tr.querySelector('.jami-td').textContent = q.toLocaleString('uz-UZ');
        jami += q;
    });
    document.getElementById('umumiy-jami').textContent = jami.toLocaleString('uz-UZ');
}

// Guruh tanlanganda — shu guruhga oid tovarlarni datalist'ga to'ldiradi
function guruhOzgardi(row) {
    var guruhSel  = row.querySelector('.guruh-select');
    var guruhId   = guruhSel.value;
    row.querySelector('.guruh-id-input').value = guruhId;

    var datalist = row.querySelector('datalist');
    datalist.innerHTML = '';
    var royxat = guruhId ? BARCHA_TOVARLAR.filter(t => String(t.guruh_id) === guruhId) : BARCHA_TOVARLAR;
    royxat.forEach(function(t) {
        var opt = document.createElement('option');
        opt.value = t.nomi;
        datalist.appendChild(opt);
    });

    // Guruh almashtirilsa — eski tanlovni tozalaymiz
    row.querySelector('.tovar-id-input').value = '';
    tovarMosligiTekshir(row);
}

// Yozilgan/tanlangan nom katalogda bor-yo'qligini tekshiradi
function tovarMosligiTekshir(row) {
    var nomiInput = row.querySelector('.nomi-input');
    var nomi = nomiInput.value.trim();
    var guruhId = row.querySelector('.guruh-id-input').value;
    var belgi = row.querySelector('.yangi-tovar-belgi');

    if (!nomi) { belgi.style.display = 'none'; ustamaUstunlarKorsat(row, false); return; }

    var royxat = guruhId ? BARCHA_TOVARLAR.filter(t => String(t.guruh_id) === guruhId) : BARCHA_TOVARLAR;
    var mos = royxat.find(t => t.nomi.toLowerCase() === nomi.toLowerCase());

    // Ustama (POS %/Nasiya %) ustunlari endi HAR DOIM ko'rinadi va tahrirlanadi —
    // mavjud tovarni qayta kirim qilganda ham narxini yangilash imkoni bo'lishi uchun.
    ustamaUstunlarKorsat(row, true);

    if (mos) {
        row.querySelector('.tovar-id-input').value = mos.id;
        row.querySelector('.narx-input').value = mos.tan_narx || mos.narx;
        row.querySelector('.birlik-input').value = mos.birlik || 'dona';
        belgi.style.display = 'none';
        katalogNarxlariniOldindanTuldirish(row, mos);
        jamiYangilash();
    } else {
        row.querySelector('.tovar-id-input').value = '';
        // Faqat guruh tanlangan bo'lsagina "yangi tovar" deb belgilaymiz —
        // guruhsiz holatda oddiy matn qator sifatida qoladi (katalogga
        // qo'shilmaydi).
        var yangiTovar = !!guruhId;
        belgi.style.display = yangiTovar ? 'inline-flex' : 'none';
        if (yangiTovar) {
            var umumiyFoiz = parseFloat(document.getElementById('umumiy-ustama-foiz').value) || 0;
            row.querySelector('.ustama-nasiya-foiz').value = umumiyFoiz;
            ustamaHisobla(row, 'foiz');
        }
    }
}

/** Mos kelgan (mavjud) tovarning katalogdagi joriy POS/Nasiya narxlarini
 *  foizga aylantirib, qatorga oldindan to'ldiradi (tahrirlash uchun). */
function katalogNarxlariniOldindanTuldirish(row, mos) {
    var tanNarx = parseFloat(row.querySelector('.narx-input').value) || 0;
    var posNarx = parseFloat(mos.narx) || tanNarx;
    var nasiyaNarx = parseFloat(mos.nasiya_narx) || tanNarx;

    row.querySelector('.ustama-pos-foiz').value = tanNarx > 0 ? Math.round(((posNarx - tanNarx) / tanNarx) * 100) : 0;
    row.querySelector('.pos-narx-input').value = posNarx;
    row.querySelector('.pos-narx-hidden').value = posNarx;

    row.querySelector('.ustama-nasiya-foiz').value = tanNarx > 0 ? Math.round(((nasiyaNarx - tanNarx) / tanNarx) * 100) : 0;
    row.querySelector('.nasiya-narx-input').value = nasiyaNarx;
    row.querySelector('.nasiya-narx-hidden').value = nasiyaNarx;
}

function ustamaUstunlarKorsat(row, korsat) {
    row.querySelectorAll('.ustama-bosh').forEach(function(td) {
        td.style.display = korsat ? 'table-cell' : 'none';
    });
}

// Kirim narxi + ustama % asosida POS va Nasiya narxlarini hisoblaydi.
// manba='foiz' — foizdan narx hisoblanadi; manba='narx' — narxdan foiz orqaga hisoblanadi.
function ustamaHisobla(row, manba) {
    var tanNarx = parseFloat(row.querySelector('.narx-input').value) || 0;

    ['pos', 'nasiya'].forEach(function(tur) {
        var foizInp = row.querySelector('.ustama-' + tur + '-foiz');
        var narxInp = row.querySelector('.' + tur + '-narx-input');
        var hiddenInp = row.querySelector('.' + tur + '-narx-hidden');
        if (!foizInp) return;

        if (manba === 'narx') {
            var narx = parseFloat(narxInp.value) || 0;
            var foiz = tanNarx > 0 ? Math.round(((narx - tanNarx) / tanNarx) * 100) : 0;
            foizInp.value = foiz;
        } else {
            var foiz = parseFloat(foizInp.value) || 0;
            var narx = Math.round(tanNarx * (1 + foiz / 100));
            narxInp.value = narx;
        }
        hiddenInp.value = narxInp.value;
    });
}

/** "Umumiy savdo ustama %" — barcha (yangi tovar) qatorlarning Nasiya % ustunini
 *  bitta qiymatga o'rnatib, narxlarni qayta hisoblaydi. Katalogdan mos kelgan
 *  (ustama ustunlari yashirin) qatorlarga ta'sir qilmaydi. */
function umumiyUstamaQollash() {
    var foiz = parseFloat(document.getElementById('umumiy-ustama-foiz').value) || 0;
    document.querySelectorAll('.qator-row').forEach(function(row) {
        var ustamaTd = row.querySelector('.ustama-bosh');
        if (!ustamaTd || ustamaTd.style.display === 'none') return;
        row.querySelector('.ustama-nasiya-foiz').value = foiz;
        ustamaHisobla(row, 'foiz');
    });
}

function bindEvents(row) {
    row.querySelectorAll('.miqdor-input,.narx-input').forEach(function(inp) {
        inp.addEventListener('input', jamiYangilash);
    });
    row.querySelector('.narx-input').addEventListener('input', function() { ustamaHisobla(row, 'foiz'); });

    var guruhSel = row.querySelector('.guruh-select');
    if (guruhSel) guruhSel.addEventListener('change', function() { guruhOzgardi(row); });

    var nomiInput = row.querySelector('.nomi-input');
    if (nomiInput) nomiInput.addEventListener('input', function() { tovarMosligiTekshir(row); });

    var bekorLink = row.querySelector('.bekor-link');
    if (bekorLink) bekorLink.addEventListener('click', function() {
        nomiInput.value = '';
        row.querySelector('.tovar-id-input').value = '';
        row.querySelector('.yangi-tovar-belgi').style.display = 'none';
        ustamaUstunlarKorsat(row, false);
        nomiInput.focus();
    });

    row.querySelectorAll('.ustama-pos-foiz,.ustama-nasiya-foiz').forEach(function(inp) {
        inp.addEventListener('input', function() { ustamaHisobla(row, 'foiz'); });
    });
    row.querySelectorAll('.pos-narx-input,.nasiya-narx-input').forEach(function(inp) {
        inp.addEventListener('input', function() { ustamaHisobla(row, 'narx'); });
    });
}

qatorQosh();

// "Izoh" maydoni — matn ko'payishiga qarab avtomatik kengayadigan (auto-expand) textarea
document.querySelectorAll('.auto-expand').forEach(function(el) {
    var kengaytir = function() {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    };
    el.addEventListener('input', kengaytir);
    kengaytir();
});
</script>
@endpush
