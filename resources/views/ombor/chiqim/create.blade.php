@extends('layouts.app')
@section('title','Yangi chiqim')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chiqim.index') }}">Chiqim</a></li>
<li class="breadcrumb-item active">Yangi chiqim</li>
@endsection

@section('content')
<form method="POST" action="{{ route('chiqim.store') }}">
@csrf

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-up me-2 text-danger"></i>Yangi tovar chiqim</h5>
    <button type="submit" class="btn btn-danger">
        <i class="bi bi-save me-1"></i>Saqlash va ombordan chiqarish
    </button>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Asosiy ma'lumotlar</h6></div>
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
                        <label class="form-label fw-medium">Sabab <span class="text-danger">*</span></label>
                        <select name="sabab" class="form-select" required>
                            @foreach($sabablar as $k => $nom)
                                <option value="{{ $k }}">{{ $nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Izoh</label>
                        <textarea name="izoh" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100">
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="text-muted mb-1">Jami summa</div>
                <div class="display-6 fw-bold text-danger" id="jami-summa">0</div>
                <div class="text-muted small">so'm</div>
                <hr>
                <div class="text-muted small" id="qator-soni">0 ta pozitsiya</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Tovarlar ro'yxati</h6>
        <button type="button" class="btn btn-sm btn-danger" onclick="qatorQosh()">
            <i class="bi bi-plus-lg me-1"></i>Qator qo'shish
        </button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Tovar</th>
                    <th style="width:150px">Qoldiq</th>
                    <th style="width:120px">Miqdor</th>
                    <th style="width:150px">Narx (so'm)</th>
                    <th style="width:150px" class="text-end">Jami</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="chiqim-tbody"></tbody>
        </table>
    </div>
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
        <td>
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
        <td class="text-end fw-bold text-danger" id="jami-${n}">0</td>
        <td>
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
