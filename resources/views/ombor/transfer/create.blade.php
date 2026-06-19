@extends('layouts.app')
@section('title','Yangi transfer')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transfer</a></li>
<li class="breadcrumb-item active">Yangi transfer</li>
@endsection

@section('content')
<form method="POST" action="{{ route('transfer.store') }}">
@csrf
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-info"></i>Filiallar arasi tovar jo'natish</h5>
    <button type="submit" class="btn btn-info text-white"><i class="bi bi-save me-1"></i>Jo'natish</button>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Transfer ma'lumotlari</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Jo'natuvchi filial <span class="text-danger">*</span></label>
                    <select name="from_filial_id" class="form-select" required>
                        @foreach($filiallar as $f)
                            <option value="{{ $f->id }}" {{ $mening_filial==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end justify-content-center">
                    <i class="bi bi-arrow-right fs-2 text-info mb-2"></i>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Qabul qiluvchi filial <span class="text-danger">*</span></label>
                    <select name="to_filial_id" class="form-select" required>
                        <option value="">— Tanlang —</option>
                        @foreach($filiallar as $f)
                            @if($f->id !== $mening_filial)
                            <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Izoh</label>
                    <textarea name="izoh" class="form-control" rows="2" placeholder="Transfer sababi..."></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="bi bi-arrow-left-right fs-1 text-info mb-2"></i>
                <h6>Tovar transfer</h6>
                <p class="text-muted small">Jo'natuvchi filialdan tovarlar chiqariladi. Qabul qiluvchi filial tasdiqlasa, tovarlar kirim qilinadi.</p>
                <div class="badge bg-warning text-dark">Tasdiqlashni kutish</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between">
        <h6 class="mb-0">Tovarlar ro'yxati</h6>
        <button type="button" class="btn btn-sm btn-info text-white" onclick="qatorQosh()">
            <i class="bi bi-plus-lg me-1"></i>Qator
        </button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Tovar</th><th style="width:120px">Miqdor</th><th style="width:120px">Qoldiq</th><th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>
</div>

<a href="{{ route('transfer.index') }}" class="btn btn-outline-secondary mb-4">
    <i class="bi bi-arrow-left me-1"></i>Bekor qilish
</a>

@php
$tj = $tovarlar->map(fn($t) => ['id'=>$t->id,'nomi'=>$t->nomi,'qoldiq'=>(float)$t->qoldiq,'birlik'=>$t->birlik]);
@endphp
<script>
const TV = {!! json_encode($tj) !!};
let n = 0;
function qatorQosh() {
    const i = n++;
    const opts = TV.map(t => `<option value="${t.id}" data-q="${t.qoldiq}" data-b="${t.birlik}">${t.nomi} (${t.qoldiq} ${t.birlik})</option>`).join('');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="text-muted">${n}</td>
        <td><select name="tovarlar[${i}][tovar_id]" class="form-select form-select-sm" required onchange="selChange(this,${i})">
            <option value="">— Tovar —</option>${opts}
        </select></td>
        <td><div class="input-group input-group-sm">
            <input type="number" name="tovarlar[${i}][miqdor]" class="form-control" value="1" min="0.001" step="0.001" required>
            <span class="input-group-text" id="b-${i}">dona</span>
        </div></td>
        <td class="text-muted small" id="q-${i}">—</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger py-0" onclick="this.closest('tr').remove()"><i class="bi bi-trash"></i></button></td>
    `;
    document.getElementById('tbody').appendChild(tr);
}
function selChange(sel, i) {
    const opt = sel.selectedOptions[0];
    document.getElementById(`q-${i}`).textContent = opt.dataset.q + ' ' + opt.dataset.b;
    document.getElementById(`b-${i}`).textContent = opt.dataset.b || 'dona';
}
qatorQosh();
</script>
</form>
@endsection
