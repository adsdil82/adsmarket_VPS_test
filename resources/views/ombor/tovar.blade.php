@extends('layouts.app')
@section('title', $tovar->nomi)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('ombor.index') }}">Ombor qoldig'i</a></li>
<li class="breadcrumb-item active">{{ $tovar->nomi }}</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.83rem; width:100%; }
.bank-table thead th { background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; font-weight:700; font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; padding:8px 10px; border-right:1px solid rgba(255,255,255,.15); white-space:nowrap; text-align:right; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { border-bottom:1px solid #e2e8f4; }
.bank-table tbody tr:hover { background:#eff6ff; }
.bank-table tbody tr:nth-child(even) { background:#f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background:#fff; }
.bank-table tbody td { padding:6px 10px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-table tfoot td { background:linear-gradient(90deg,#1e3a8a,#1e40af); color:#fff; font-weight:700; font-size:.8rem; padding:7px 10px; border-top:2px solid #60a5fa; }
.harakat-badge { font-size:.66rem; font-weight:700; padding:1px 7px; border-radius:3px; text-transform:uppercase; }
.h-kirim { background:#dcfce7; color:#15803d; }
.h-chiqim { background:#fee2e2; color:#b91c1c; }
.h-transfer_out { background:#fef9c3; color:#a16207; }
.h-transfer_in { background:#dbeafe; color:#1d4ed8; }
.h-qaytarish { background:#f3e8ff; color:#7e22ce; }
.h-tuzatish { background:#f1f5f9; color:#64748b; }
.stat-card { background:#fff; border:1px solid #e2e8f4; border-radius:8px; padding:12px 16px; }
.stat-card .lbl { font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; font-weight:600; }
.stat-card .val { font-size:1.15rem; font-weight:800; margin:2px 0; }
</style>
@endpush

@section('content')
@php $n = fn($v) => number_format((float)$v,0,'.',' '); @endphp

<div class="d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-box-seam text-warning" style="font-size:1.2rem"></i>
    <h5 class="fw-bold mb-0">{{ $tovar->nomi }}</h5>
    <span class="text-muted small">{{ $tovar->guruh->nomi ?? '' }} · {{ $tovar->birlik }}</span>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left:4px solid #2563eb">
            <div class="lbl">Jami (barcha omborlar)</div>
            <div class="val" style="color:#2563eb">{{ $n($tovar->qoldiq) }} {{ $tovar->birlik }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left:4px solid #16a34a">
            <div class="lbl">Tan narx</div>
            <div class="val" style="color:#16a34a">{{ $n($tovar->tan_narx) }} so'm</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left:4px solid #d97706">
            <div class="lbl">Sotish narx (POS)</div>
            <div class="val" style="color:#d97706">{{ $n($tovar->sotish_narx) }} so'm</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="border-left:4px solid #7c3aed">
            <div class="lbl">Nasiya narx</div>
            <div class="val" style="color:#7c3aed">{{ $n($tovar->nasiya_narx) }} so'm</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)">
                <span class="fw-bold small">Omborlar bo'yicha taqsimot</span>
            </div>
            <div class="table-responsive">
                <table class="bank-table">
                    <thead><tr><th class="tl">Ombor</th><th>Miqdor</th></tr></thead>
                    <tbody>
                        @forelse($taqsimot as $t)
                        <tr>
                            <td class="tl">
                                <a href="{{ route('ombor.index', ['ombor_id'=>$t->ombor_id]) }}" class="text-decoration-none">{{ $t->ombor->nomi }}</a>
                                <div class="text-muted" style="font-size:.7rem">{{ $t->ombor->filial->nomi ?? '' }}</div>
                            </td>
                            <td class="num fw-bold">{{ $n($t->miqdor) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Hech qayerda qoldiq yo'q</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)">
                <span class="fw-bold small">So'nggi harakatlar (oxirgi 50 ta)</span>
            </div>
            <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                <table class="bank-table">
                    <thead>
                        <tr>
                            <th class="tl">Sana</th><th class="tl">Ombor</th><th class="tl">Harakat</th>
                            <th>Miqdor</th><th>Oldin</th><th>Keyin</th><th class="tl">Izoh</th><th class="tl">Kim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($harakatlar as $h)
                        <tr>
                            <td class="tl text-muted" style="font-size:.75rem">{{ $h->created_at->format('d.m.Y H:i') }}</td>
                            <td class="tl">{{ $h->ombor->nomi ?? '—' }}</td>
                            <td class="tl"><span class="harakat-badge h-{{ $h->harakat }}">{{ $h->harakat }}</span></td>
                            <td class="num" style="color:{{ in_array($h->harakat,['kirim','transfer_in']) ? '#15803d' : '#b91c1c' }}">
                                {{ in_array($h->harakat,['kirim','transfer_in']) ? '+' : '−' }}{{ $n($h->miqdor) }}
                            </td>
                            <td class="num text-muted">{{ $n($h->qoldiq_oldin) }}</td>
                            <td class="num fw-bold">{{ $n($h->qoldiq_keyin) }}</td>
                            <td class="tl text-muted" style="font-size:.75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $h->izoh }}">{{ $h->izoh }}</td>
                            <td class="tl text-muted" style="font-size:.75rem">{{ $h->xodim->ism_familiya ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Harakatlar tarixi yo'q</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
