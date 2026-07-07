@extends('layouts.app')
@section('title','Yangi chiqim')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chiqim.index') }}">Chiqim</a></li>
<li class="breadcrumb-item active">Yangi chiqim</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#7f1d1d,#b91c1c); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#7f1d1d,#b91c1c);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #fca5a5; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }
.bft-table { width:100%; margin-bottom:0 !important; font-size:.86rem; }
.bft-table td { padding:9px 12px; vertical-align:middle; border-bottom:1px solid #fee2e2; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#fef8f8; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#fef2f2; }
.bft-wide { width:100%; }

.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #fca5a5; }
.bank-table thead th {
    background:linear-gradient(180deg,#dc2626,#7f1d1d); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.02em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#fef8f8; }
.bank-table tbody td { padding:6px 8px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #fca5a5; border-radius:0 0 6px 6px; }

.jami-stat-card {
    border:1px solid #fca5a5; border-radius:6px; background:#fff; padding:16px; text-align:center; height:100%;
}
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('chiqim.store') }}">
@csrf

{{-- ── Sarlavha ─────────────────────────────────────────────────── --}}
<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-box-arrow-up fs-5"></i>
        <span class="fw-bold">Yangi tovar chiqim</span>
    </div>
    <button type="submit" class="btn btn-sm btn-light py-1 fw-bold">
        <i class="bi bi-check-circle me-1 text-danger"></i>Saqlash va ombordan chiqarish
    </button>
</div>

<div class="row g-3 mb-3">
    {{-- ── Asosiy ma'lumotlar ────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="bft-section-title mb-0"><span><i class="bi bi-card-list me-1"></i>Asosiy ma'lumotlar</span></div>
        <div class="bft-wrap">
            <table class="bft-table">
                <tbody>
                    <tr>
                        <td class="bft-label">Filial <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <select name="filial_id" class="form-select form-select-sm" required>
                                @foreach($filiallar as $f)
                                    <option value="{{ $f->id }}" {{ Auth::user()->filial_id==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="bft-label">Sana <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <input type="date" name="sana" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Sabab <span class="text-danger">*</span></td>
                        <td colspan="3">
                            <select name="sabab" class="form-select form-select-sm" required>
                                @foreach($sabablar as $k => $nom)
                                    <option value="{{ $k }}">{{ $nom }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Izoh</td>
                        <td colspan="3">
                            <textarea name="izoh" class="form-control form-control-sm" rows="2"></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="jami-stat-card">
            <div class="text-muted small text-uppercase fw-bold">Jami summa</div>
            <div class="display-6 fw-bold text-danger" id="jami-summa">0</div>
            <div class="text-muted small">so'm</div>
            <hr>
            <div class="text-muted small" id="qator-soni">0 ta pozitsiya</div>
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
<div class="bank-wrap mb-3">
    <table class="bank-table">
        <thead>
            <tr>
                <th style="width:36px">#</th>
                <th class="tl">Tovar</th>
                <th style="width:120px">Qoldiq</th>
                <th style="width:130px">Miqdor</th>
                <th style="width:140px">Narx (so'm)</th>
                <th style="width:140px">Jami</th>
                <th style="width:40px"></th>
            </tr>
        </thead>
        <tbody id="chiqim-tbody"></tbody>
    </table>
</div>

<div class="d-flex justify-content-between mb-4">
    <a href="{{ route('chiqim.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Bekor qilish
    </a>
    <button type="submit" class="btn btn-danger btn-lg">
        <i class="bi bi-check-circle me-1"></i>Saqlash va ombordan chiqarish
    </button>
</div>
</form>

@php
$tovarlarJson = $tovarlar->map(fn($t) => [
    'id'          => $t->id,
    'nomi'        => $t->nomi,
    'sotish_narx' => (float)$t->sotish_narx,
    'qoldiq'      => (float)$t->qoldiq,
    'birlik'      => $t->birlik,
]);
@endphp
<script>
const TOVARLAR = {!! json_encode($tovarlarJson) !!};

let qN = 0;

function qatorQosh() {
    const n = qN++;
    const opts = TOVARLAR.map(t =>
        `<option value="${t.id}" data-narx="${t.sotish_narx}" data-qoldiq="${t.qoldiq}" data-birlik="${t.birlik}">
            ${t.nomi} (${t.qoldiq} ${t.birlik})
        </option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-muted small">${qN}</td>
        <td class="tl">
            <select name="tovarlar[${n}][tovar_id]" class="form-select form-select-sm" required
                    onchange="tovarTanlandi(this,${n})">
                <option value="">— Tovar tanlang —</option>
                ${opts}
            </select>
        </td>
        <td class="text-muted small" id="qoldiq-${n}">—</td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="tovarlar[${n}][miqdor]" class="form-control"
                       value="1" min="0.001" step="0.001" required
                       onchange="jamiHisob(${n})" data-n="${n}">
                <span class="input-group-text" id="birlik-${n}">dona</span>
            </div>
        </td>
        <td>
            <input type="number" name="tovarlar[${n}][narx]" class="form-control form-control-sm"
                   value="0" min="0" step="100" required
                   onchange="jamiHisob(${n})">
        </td>
        <td class="num fw-bold text-danger" id="jami-${n}">0</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="this.closest('tr').remove(); jamiYangilash()">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    document.getElementById('chiqim-tbody').appendChild(tr);
    jamiYangilash();
}

function tovarTanlandi(sel, n) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) return;
    document.querySelector(`[name="tovarlar[${n}][narx]"]`).value = opt.dataset.narx;
    document.getElementById(`qoldiq-${n}`).textContent = `${opt.dataset.qoldiq} ${opt.dataset.birlik}`;
    document.getElementById(`birlik-${n}`).textContent = opt.dataset.birlik;
    jamiHisob(n);
}

function jamiHisob(n) {
    const miqdor = parseFloat(document.querySelector(`[name="tovarlar[${n}][miqdor]"]`)?.value) || 0;
    const narx   = parseFloat(document.querySelector(`[name="tovarlar[${n}][narx]"]`)?.value) || 0;
    const el = document.getElementById(`jami-${n}`);
    if (el) el.textContent = (miqdor * narx).toLocaleString('uz-UZ');
    jamiYangilash();
}

function jamiYangilash() {
    const cells = document.querySelectorAll('[id^="jami-"]');
    let total = 0;
    cells.forEach(el => total += parseFloat(el.textContent.replace(/\s/g,'')) || 0);
    document.getElementById('jami-summa').textContent = total.toLocaleString('uz-UZ');
    document.getElementById('qator-soni').textContent = cells.length + ' ta pozitsiya';
}

qatorQosh();
</script>
@endsection
