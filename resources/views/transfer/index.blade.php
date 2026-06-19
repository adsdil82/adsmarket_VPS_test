@extends('layouts.app')
@section('title', 'Transferlar')
@section('breadcrumb')
    <li class="breadcrumb-item active">Transferlar</li>
@endsection

@push('styles')
<style>
.tr-modul-card {
    border-radius:12px;border:2px solid transparent;
    padding:16px;text-decoration:none;display:block;
    transition:all .2s;
}
.tr-modul-card:hover { transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.12); }
.tr-modul-card .tm-icon { font-size:2rem;margin-bottom:8px; }
.tr-modul-card .tm-title { font-weight:700;font-size:.92rem; }
.tr-modul-card .tm-desc  { font-size:.75rem;opacity:.8;margin-top:3px; }
.kutilayotgan-badge {
    position:absolute;top:-8px;right:-8px;
    background:#dc3545;color:#fff;border-radius:50%;
    width:22px;height:22px;font-size:.68rem;
    display:flex;align-items:center;justify-content:center;font-weight:700;
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">
        <i class="bi bi-arrow-left-right me-2 text-warning"></i>Transferlar moduli
    </h4>
    <a href="{{ route('transfer.audit') }}" class="btn btn-sm btn-outline-info">
        <i class="bi bi-journal-text me-1"></i>Audit jurnali
    </a>
</div>

{{-- ── Kutilayotganlar alert ─────────────────────────────── --}}
@if($stats['kutilayotgan_tovar'] > 0 || $stats['kutilayotgan_kassa'] > 0)
<div class="alert alert-warning py-2 mb-3">
    <i class="bi bi-exclamation-triangle me-1"></i>
    <strong>Tasdiqlash kutilmoqda:</strong>
    @if($stats['kutilayotgan_tovar'] > 0)
        <span class="badge bg-warning text-dark ms-2">{{ $stats['kutilayotgan_tovar'] }} ta tovar transferi</span>
    @endif
    @if($stats['kutilayotgan_kassa'] > 0)
        <span class="badge bg-warning text-dark ms-2">{{ $stats['kutilayotgan_kassa'] }} ta kassa transferi</span>
    @endif
</div>
@endif

{{-- ── Modullar ──────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Tovar transferi --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="position-relative">
            <a href="{{ route('transfer.tovar.index') }}"
               class="tr-modul-card bg-success bg-opacity-10 border-success text-success text-center">
                <div class="tm-icon">📦</div>
                <div class="tm-title">Tovar transferi</div>
                <div class="tm-desc">Filial/ombor orasida</div>
                <div class="mt-2 small">{{ $stats['tovar_transfer'] }} ta (7 kun)</div>
            </a>
            @if($stats['kutilayotgan_tovar'] > 0)
            <span class="kutilayotgan-badge">{{ $stats['kutilayotgan_tovar'] }}</span>
            @endif
        </div>
    </div>

    {{-- Kassa transferi --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="position-relative">
            <a href="{{ route('transfer.kassa.index') }}"
               class="tr-modul-card bg-primary bg-opacity-10 border-primary text-primary text-center">
                <div class="tm-icon">💵</div>
                <div class="tm-title">Kassa transferi</div>
                <div class="tm-desc">Kassalar orasida pul</div>
                <div class="mt-2 small">{{ $stats['kassa_transfer'] }} ta (7 kun)</div>
            </a>
            @if($stats['kutilayotgan_kassa'] > 0)
            <span class="kutilayotgan-badge">{{ $stats['kutilayotgan_kassa'] }}</span>
            @endif
        </div>
    </div>

    {{-- Xodim tayinlash --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <a href="{{ route('transfer.shartnoma.xodim_tarixi') }}"
           class="tr-modul-card bg-warning bg-opacity-10 border-warning text-warning text-center">
            <div class="tm-icon">👤</div>
            <div class="tm-title">Xodim tayinlash</div>
            <div class="tm-desc">Shartnomani qayta tayinlash</div>
            <div class="mt-2 small">{{ $stats['xodim_tayinlash'] }} ta (7 kun)</div>
        </a>
    </div>

    {{-- Filial ko'chirish --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <a href="{{ route('transfer.shartnoma.filial_tarixi') }}"
           class="tr-modul-card bg-danger bg-opacity-10 border-danger text-danger text-center">
            <div class="tm-icon">🏢</div>
            <div class="tm-title">Filial ko'chirish</div>
            <div class="tm-desc">Shartnomani boshqa filialga</div>
            <div class="mt-2 small">{{ $stats['filial_kochirish'] }} ta (7 kun)</div>
        </a>
    </div>

    {{-- Supplier return --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <a href="{{ route('transfer.supplier-return.index') }}"
           class="tr-modul-card bg-secondary bg-opacity-10 border-secondary text-secondary text-center">
            <div class="tm-icon">↩️</div>
            <div class="tm-title">Ta'minotchiga qaytarish</div>
            <div class="tm-desc">Supplier return</div>
            <div class="mt-2 small">{{ $stats['supplier_return'] }} ta (7 kun)</div>
        </a>
    </div>

    {{-- To'lov turlari --}}
    @if(Auth::user()->isAdmin())
    <div class="col-6 col-sm-4 col-lg-3">
        <a href="{{ route('transfer.tolov_turi.index') }}"
           class="tr-modul-card text-center"
           style="background:linear-gradient(135deg,#6366f1,#7c3aed);color:#fff;border-color:#6366f1">
            <div class="tm-icon">💳</div>
            <div class="tm-title">To'lov turlari</div>
            <div class="tm-desc">Yangi + Legacy boshqaruv</div>
        </a>
    </div>
    @endif

    {{-- Audit --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <a href="{{ route('transfer.audit') }}"
           class="tr-modul-card bg-info bg-opacity-10 border-info text-info text-center">
            <div class="tm-icon">📋</div>
            <div class="tm-title">Audit jurnali</div>
            <div class="tm-desc">Barcha harakatlar tarixi</div>
        </a>
    </div>
</div>

{{-- ── Oxirgi harakatlar ─────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header py-2">
        <h6 class="mb-0 small fw-bold"><i class="bi bi-clock-history me-1"></i>Oxirgi harakatlar</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Tur</th><th>Tafsilot</th><th>Holat</th><th>Sana</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($oxirgi as $h)
                <tr>
                    <td>
                        <span class="badge bg-{{ $h['rang'] }} bg-opacity-15 text-{{ $h['rang'] }}" style="font-size:.7rem">
                            {{ $h['tur'] }}
                        </span>
                    </td>
                    <td class="small">{{ $h['tavsif'] }}</td>
                    <td>
                        <span class="badge bg-{{ $h['holat_rangi'] }}" style="font-size:.65rem">
                            {{ $h['holat'] }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($h['sana'])->diffForHumans() }}</td>
                    <td>
                        <a href="{{ $h['url'] }}" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:.7rem">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Harakatlar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
