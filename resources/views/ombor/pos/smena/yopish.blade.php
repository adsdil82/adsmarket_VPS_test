@extends('layouts.app')
@section('title', 'Smena yopish')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item active">Smena yopish</li>
@endsection

@push('styles')
<style>
.pos-card { background:#fff; border:1px solid #d7e2f5; border-radius:8px; padding:12px 14px; text-align:center; }
.pos-card .label { font-size:.68rem; color:#7a89a8; text-transform:uppercase; letter-spacing:.03em; font-weight:700; }
.pos-card .value { font-size:1.15rem; font-weight:800; color:#0f172a; margin-top:2px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-door-closed me-2 text-danger"></i>Smenani yopish — {{ $smena->smena_raqami }}</h5>
    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-4 col-lg-2"><div class="pos-card"><div class="label">Dastlabki qoldiq</div><div class="value">{{ number_format($smena->dastlabki_qoldiq,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="pos-card"><div class="label">Cheklar soni</div><div class="value">{{ $sotuvlarSoni }}</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="pos-card"><div class="label">Jami savdo</div><div class="value text-primary">{{ number_format($jamiSavdo,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="pos-card"><div class="label">Naqd</div><div class="value text-success">{{ number_format($naqdJami,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="pos-card"><div class="label">Karta/terminal</div><div class="value text-primary">{{ number_format($kartaJami,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-4 col-lg-2"><div class="pos-card" style="background:#fef9c3;border-color:#fbbf24"><div class="label">Dastur hisoblagan qoldiq</div><div class="value" style="color:#92400e">{{ number_format($hisoblangan,0,'.',' ') }}</div></div></div>
</div>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(90deg,#7f1d1d,#b91c1c);color:#fff">
                <span class="fw-bold"><i class="bi bi-cash-coin me-1"></i>Sanoq (kassadagi haqiqiy naqd pul)</span>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('pos.smena.yopish', $smena) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kassadagi yakuniy (sanoq) naqd qoldiq</label>
                        <div class="input-group">
                            <input type="number" name="yakuniy_qoldiq" id="yakuniy-qoldiq" class="form-control form-control-lg" step="1000" min="0" value="{{ old('yakuniy_qoldiq') }}" required autofocus oninput="farqniHisobla()">
                            <span class="input-group-text">so'm</span>
                        </div>
                    </div>
                    <div class="alert py-2 mb-3" id="farq-alert" style="display:none"></div>
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Izoh (ixtiyoriy)</label>
                        <textarea name="izoh" class="form-control" rows="2">{{ old('izoh') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg w-100">
                        <i class="bi bi-lock-fill me-1"></i>Smenani yopish
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const HISOBLANGAN = {{ $hisoblangan }};
function farqniHisobla() {
    const kiritilgan = parseFloat(document.getElementById('yakuniy-qoldiq').value) || 0;
    const farq = kiritilgan - HISOBLANGAN;
    const box = document.getElementById('farq-alert');
    box.style.display = '';
    if (Math.abs(farq) < 1) {
        box.className = 'alert alert-success py-2 mb-3';
        box.innerHTML = '<i class="bi bi-check-circle me-1"></i>Farq yo\'q — qoldiq to\'g\'ri kelmoqda.';
    } else if (farq < 0) {
        box.className = 'alert alert-danger py-2 mb-3';
        box.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Kamomad: <strong>' + Math.abs(farq).toLocaleString('uz-UZ') + " so'm</strong>";
    } else {
        box.className = 'alert alert-warning py-2 mb-3';
        box.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Ortiqcha: <strong>' + farq.toLocaleString('uz-UZ') + " so'm</strong>";
    }
}
</script>
@endsection
