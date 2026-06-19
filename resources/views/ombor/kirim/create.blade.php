@extends('layouts.app')
@section('title','Yangi kirim')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('kirim.index') }}">Kirim</a></li>
<li class="breadcrumb-item active">Yangi kirim</li>
@endsection

@push('styles')
<style>
.tovar-qator td { vertical-align: middle; }
.tovar-select { min-width: 220px; }
.narx-input { max-width: 130px; }
.miqdor-input { max-width: 100px; }
.jami-cell { font-weight: 600; color: #198754; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('kirim.store') }}" id="kirim-forma">
@csrf

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Yangi tovar kirim</h5>
    <button type="submit" class="btn btn-success" id="saqlash-btn">
        <i class="bi bi-save me-1"></i>Saqlash va omborga qo'shish
    </button>
</div>

<div class="row g-3 mb-3">
    {{-- Asosiy ma'lumotlar --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header"><h6 class="mb-0">Hujjat ma'lumotlari</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Filial <span class="text-danger">*</span></label>
                        <select name="filial_id" class="form-select" required>
                            @foreach($filiallar as $f)
                                <option value="{{ $f->id }}" {{ Auth::user()->filial_id==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Sana <span class="text-danger">*</span></label>
                        <input type="date" name="sana" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Hujjat raqami</label>
                        <input type="text" name="hujjat_raqam" class="form-control" placeholder="KIR-001">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Yetkazuvchi</label>
                        <input type="text" name="yetkazuvchi" class="form-control" placeholder="Yetkazuvchi nomi">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Izoh</label>
                        <textarea name="izoh" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Jami --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="text-muted mb-1">Jami summa</div>
                <div class="display-6 fw-bold text-success" id="jami-summa">0</div>
                <div class="text-muted small">so'm</div>
                <hr>
                <div class="text-muted small" id="qator-soni">0 ta pozitsiya</div>
            </div>
        </div>
    </div>
</div>

{{-- Tovarlar jadvali --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Tovarlar ro'yxati</h6>
        <button type="button" class="btn btn-sm btn-primary" onclick="qatorQosh()">
            <i class="bi bi-plus-lg me-1"></i>Qator qo'shish
        </button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle" id="kirim-jadval">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Tovar</th>
                    <th style="width:120px">Miqdor</th>
                    <th style="width:150px">Tan narx (so'm)</th>
                    <th style="width:150px" class="text-end">Jami (so'm)</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="kirim-tbody">
                {{-- Dinamik qatorlar --}}
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('kirim.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Bekor qilish
    </a>
    <button type="submit" class="btn btn-success btn-lg">
        <i class="bi bi-check-circle me-1"></i>Saqlash va omborga qo'shish
    </button>
</div>
</form>

{{-- Tovarlar JSON (JavaScript uchun) --}}
@php
$tovarlarJson = $tovarlar->map(fn($t) => [
    'id'       => $t->id,
    'nomi'     => $t->nomi,
    'barkod'   => $t->barkod,
    'tan_narx' => (float)$t->tan_narx,
    'birlik'   => $t->birlik,
    'qoldiq'   => (float)$t->qoldiq,
]);
@endphp
<script>
const TOVARLAR = {!! json_encode($tovarlarJson) !!};

let qatorSanar = 0;

function qatorQosh(tovar = null) {
    const n = qatorSanar++;
    const options = TOVARLAR.map(t =>
        `<option value="${t.id}" data-narx="${t.tan_narx}" data-birlik="${t.birlik}">${t.nomi} (${t.qoldiq} ${t.birlik})</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'tovar-qator';
    tr.dataset.n = n;
    tr.innerHTML = `
        <td class="text-muted small">${qatorSanar}</td>
        <td>
            <select name="tovarlar[${n}][tovar_id]" class="form-select form-select-sm tovar-select tovar-sel"
                    required onchange="tovarTanlandi(this, ${n})">
                <option value="">— Tovar tanlang —</option>
                ${options}
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" name="tovarlar[${n}][miqdor]" class="form-control miqdor-input miqdor-inp"
                       value="1" min="0.001" step="0.001" required
                       onchange="jamiHisob(${n})" data-n="${n}">
                <span class="input-group-text birlik-txt" id="birlik-${n}">dona</span>
            </div>
        </td>
        <td>
            <input type="number" name="tovarlar[${n}][tan_narx]" class="form-control form-control-sm narx-input narx-inp"
                   value="0" min="0" step="100" required
                   onchange="jamiHisob(${n})" data-n="${n}">
        </td>
        <td class="text-end jami-cell" id="jami-${n}">0</td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="qatorOchir(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    document.getElementById('kirim-tbody').appendChild(tr);
    jamiYangilash();
}

function tovarTanlandi(sel, n) {
    const opt = sel.selectedOptions[0];
    if (!opt.value) return;
    const narx = parseFloat(opt.dataset.narx) || 0;
    const birlik = opt.dataset.birlik || 'dona';
    document.querySelector(`[name="tovarlar[${n}][tan_narx]"]`).value = narx;
    document.getElementById(`birlik-${n}`).textContent = birlik;
    jamiHisob(n);
}

function jamiHisob(n) {
    const miqdor = parseFloat(document.querySelector(`[name="tovarlar[${n}][miqdor]"]`)?.value) || 0;
    const narx   = parseFloat(document.querySelector(`[name="tovarlar[${n}][tan_narx]"]`)?.value) || 0;
    const jami   = miqdor * narx;
    const el     = document.getElementById(`jami-${n}`);
    if (el) el.textContent = jami.toLocaleString('uz-UZ');
    jamiYangilash();
}

function jamiYangilash() {
    const jamis = document.querySelectorAll('[id^="jami-"]');
    let total = 0;
    jamis.forEach(el => {
        total += parseFloat(el.textContent.replace(/\s/g,'')) || 0;
    });
    document.getElementById('jami-summa').textContent = total.toLocaleString('uz-UZ');
    document.getElementById('qator-soni').textContent = jamis.length + ' ta pozitsiya';
}

function qatorOchir(btn) {
    btn.closest('tr').remove();
    jamiYangilash();
    // Qayta raqamlash
    document.querySelectorAll('.tovar-qator').forEach((tr, i) => {
        tr.querySelector('td:first-child').textContent = i + 1;
    });
}

// Birinchi qator avtomatik
qatorQosh();
</script>
@endsection
