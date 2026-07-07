@extends('layouts.app')
@section('title', 'Smena — '.$smena->smena_raqami)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item"><a href="{{ route('pos.smena.royxat') }}">Kassir smenalari</a></li>
<li class="breadcrumb-item active">{{ $smena->smena_raqami }}</li>
@endsection

@push('styles')
<style>
.pos-card { background:#fff; border:1px solid #d7e2f5; border-radius:8px; padding:12px 14px; text-align:center; }
.pos-card .label { font-size:.68rem; color:#7a89a8; text-transform:uppercase; letter-spacing:.03em; font-weight:700; }
.pos-card .value { font-size:1.1rem; font-weight:800; color:#0f172a; margin-top:2px; }
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead th { background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800; font-size:.66rem; text-transform:uppercase; padding:6px 8px; text-align:right; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:5px 8px; white-space:nowrap; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; }
.bank-wrap { overflow:auto; max-height:340px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.badge-modern { font-size:.68rem; font-weight:800; padding:3px 8px; border-radius:4px; }
.b-ochiq { background:#22c55e; color:#fff; } .b-yopiq { background:#64748b; color:#fff; }
</style>
@endpush

@section('content')
@if(session('muvaffaqiyat'))<div class="alert alert-success py-2">{{ session('muvaffaqiyat') }}</div>@endif
@if(session('xato'))<div class="alert alert-danger py-2">{{ session('xato') }}</div>@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-receipt-cutoff me-2 text-primary"></i>Smena — {{ $smena->smena_raqami }}
        <span class="badge-modern b-{{ $smena->holat }} ms-2">{{ $smena->holat }}</span>
    </h5>
    <div class="d-flex gap-2">
        @if($smena->holat === 'ochiq')
        <a href="{{ route('pos.smena.yopish-forma',$smena) }}" class="btn btn-sm btn-danger"><i class="bi bi-door-closed me-1"></i>Smenani yopish</a>
        @endif
        <a href="{{ route('pos.smena.royxat') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Filial</div><div class="value">{{ $smena->filial->nomi ?? '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Kassir</div><div class="value">{{ $smena->xodim->ism_familiya ?? '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Ochilgan</div><div class="value">{{ $smena->ochilgan_vaqt->format('d.m.Y H:i') }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Yopilgan</div><div class="value">{{ $smena->yopilgan_vaqt?->format('d.m.Y H:i') ?? '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Dastlabki qoldiq</div><div class="value">{{ number_format($smena->dastlabki_qoldiq,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Dastur hisoblagan</div><div class="value">{{ $smena->hisoblangan_qoldiq !== null ? number_format($smena->hisoblangan_qoldiq,0,'.',' ') : '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Yakuniy (sanoq)</div><div class="value">{{ $smena->yakuniy_qoldiq !== null ? number_format($smena->yakuniy_qoldiq,0,'.',' ') : '—' }}</div></div></div>
    <div class="col-6 col-md-3">
        <div class="pos-card" style="{{ $smena->farq === null ? '' : (abs($smena->farq) < 1 ? 'background:#f0fdf4;border-color:#86efac' : 'background:#fef2f2;border-color:#fecaca') }}">
            <div class="label">Farq {{ $smena->farq !== null && $smena->farq < 0 ? '(kamomad)' : ($smena->farq > 0 ? '(ortiqcha)' : '') }}</div>
            <div class="value" style="color:{{ $smena->farq === null ? '#0f172a' : (abs($smena->farq) < 1 ? '#15803d' : '#b91c1c') }}">
                {{ $smena->farq !== null ? number_format($smena->farq,0,'.',' ') : '—' }}
            </div>
        </div>
    </div>
</div>

{{-- ── Asosiy kassaga topshirish ────────────────────────────────── --}}
@if($smena->holat === 'yopiq')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2" style="background:linear-gradient(90deg,#1e3a8a,#1d4ed8);color:#fff">
        <span class="fw-bold"><i class="bi bi-arrow-left-right me-1"></i>Asosiy kassaga topshirish</span>
    </div>
    <div class="card-body">
        @if($smena->topshirish_holati === 'yoq' || $smena->topshirish_holati === 'rad_etildi')
            @if($smena->topshirish_holati === 'rad_etildi')
            <div class="alert alert-danger py-2 small">
                <strong>Oldingi so'rov rad etildi:</strong> {{ $smena->rad_sababi }}
            </div>
            @endif
            <form method="POST" action="{{ route('pos.smena.topshirish',$smena) }}" class="d-flex gap-2 align-items-end flex-wrap">
                @csrf
                <div>
                    <label class="form-label small mb-1">Topshirilayotgan summa</label>
                    <input type="number" name="topshirilgan_summa" class="form-control form-control-sm" step="1000" min="1" max="{{ $smena->yakuniy_qoldiq }}" value="{{ $smena->yakuniy_qoldiq }}" style="width:200px" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i>Topshirish so'rovini yuborish</button>
            </form>
        @elseif($smena->topshirish_holati === 'kutilmoqda')
            <div class="alert alert-warning py-2 mb-2">
                <i class="bi bi-hourglass-split me-1"></i>
                <strong>{{ number_format($smena->topshirilgan_summa,0,'.',' ') }} so'm</strong> topshirilishi kutilmoqda.
            </div>
            @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('pos.smena.tasdiqlash',$smena) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i>Tasdiqlash</button>
                </form>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#radModal"><i class="bi bi-x-lg me-1"></i>Rad etish</button>
            </div>
            @endif
        @elseif($smena->topshirish_holati === 'tasdiqlangan')
            <div class="alert alert-success py-2 mb-0">
                <i class="bi bi-check-circle me-1"></i>
                <strong>{{ number_format($smena->topshirilgan_summa,0,'.',' ') }} so'm</strong> topshirildi va tasdiqlandi
                ({{ $smena->qabulQilgan->ism_familiya ?? '—' }}, {{ $smena->qabul_vaqti?->format('d.m.Y H:i') }}).
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="radModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('pos.smena.rad',$smena) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title fw-bold">Topshirishni rad etish</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><textarea name="sabab" class="form-control" rows="3" placeholder="Sababini yozing..." required></textarea></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bekor</button><button type="submit" class="btn btn-danger">Rad etish</button></div>
        </form>
    </div>
</div>
@endif

@if($smena->izoh)
<div class="alert alert-secondary py-2 small"><strong>Izoh:</strong> {{ $smena->izoh }}</div>
@endif

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead><tr><th class="tl">Chek №</th><th class="tl">Vaqt</th><th>Summa</th><th class="tl">To'lov turi</th></tr></thead>
        <tbody>
            @forelse($smena->sotuvlar as $s)
            <tr>
                <td class="tl"><a href="{{ route('pos.chek',$s) }}">{{ $s->check_raqam }}</a></td>
                <td class="tl">{{ $s->created_at->format('d.m.Y H:i') }}</td>
                <td class="num">{{ number_format($s->jami_tolov,0,'.',' ') }}</td>
                <td class="tl">{{ $s->tolov_turi }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted py-3">Sotuv yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
