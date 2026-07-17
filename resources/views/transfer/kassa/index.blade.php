@extends('layouts.app')
@section('title', 'Kassa transferlari')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Kassa transferlari</li>
@endsection

@push('styles')
<style>
.holat-tabs { display:flex; gap:5px; flex-wrap:nowrap; overflow-x:auto; padding-bottom:2px; }
.holat-tabs::-webkit-scrollbar { height:4px; }
.holat-tab {
    display:inline-flex; align-items:center; gap:5px; white-space:nowrap; text-decoration:none;
    padding:6px 13px; border-radius:7px; font-size:.74rem; font-weight:700;
    background:#eef4ff; color:#1e3a8a; border:1px solid #93c5fd;
}
.holat-tab:hover { background:#dbeafe; color:#1e3a8a; }
.holat-tab.active { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
</style>
@endpush

@php
$holatTablari = [
    ''              => ['label' => 'Barchasi',      'icon' => 'bi-list-ul'],
    'yuborildi'     => ['label' => 'Yuborildi',     'icon' => 'bi-send'],
    'qabul_qilindi' => ['label' => 'Qabul qilindi', 'icon' => 'bi-check-circle'],
    'bekor'         => ['label' => 'Bekor',         'icon' => 'bi-x-circle'],
    'qoralama'      => ['label' => 'Qoralama',      'icon' => 'bi-file-earmark'],
];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-cash-coin me-2 text-primary"></i>Kassa transferlari</h5>
    @if(Auth::user()->isTaminotKira())
    <a href="{{ route('transfer.kassa.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Yangi kassa transferi
    </a>
    @endif
</div>

{{-- Holat tab --}}
<div class="holat-tabs mb-2">
    @foreach($holatTablari as $key => $h)
    <a class="holat-tab {{ request('holat', '') === $key ? 'active' : '' }}"
       href="{{ route('transfer.kassa.index', array_merge(request()->except(['holat', 'page']), $key !== '' ? ['holat' => $key] : [])) }}">
        <i class="bi {{ $h['icon'] }}"></i> {{ $h['label'] }}
    </a>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="holat" value="{{ request('holat') }}">
            <div class="col-sm-2">
                <input type="date" name="dan_sana"   class="form-control form-control-sm" value="{{ request("dan_sana") }}">
            </div>
            <div class="col-sm-2">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ request("gacha_sana") }}">
            </div>
            <div class="col-sm-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route("transfer.kassa.index") }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
{{-- Mobile kassa transferlar --}}
<div class="d-md-none">
    @forelse($transferlar as $t)
    <div class="card border-0 shadow-sm mb-2 {{ $t->holat==='yuborildi' && Auth::user()->filial_id==$t->to_filial_id ? 'border-warning border-opacity-50' : '' }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-bold small">{{ $t->transfer_raqam }}</span>
                <span class="badge bg-{{ $t->holat_rangi }}" style="font-size:.7rem">{{ $t->holat }}</span>
            </div>
            <div class="fw-bold mb-1">{{ number_format($t->summa_uzs,0,".","  ") }} so'm</div>
            <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.82rem">
                <span class="badge bg-secondary">{{ $t->fromFilial->kod }}</span>
                <span class="text-muted small">{{ $t->fromKassa->nomi }}</span>
                <i class="bi bi-arrow-right"></i>
                <span class="badge bg-primary">{{ $t->toFilial->kod }}</span>
                <span class="text-muted small">{{ $t->toKassa->nomi }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <small class="text-muted">{{ $t->sana->format("d.m.Y") }}</small>
                <a href="{{ route("transfer.kassa.show", $t) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                    <i class="bi bi-eye me-1"></i>Ko'rish
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox fs-3 d-block mb-2"></i>Transferlar topilmadi
    </div>
    @endforelse
    @if($transferlar->hasPages())
    <div class="mt-2">{{ $transferlar->links("pagination::bootstrap-5") }}</div>
    @endif
</div>

<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Transfer #</th><th>Jo'natuvchi</th><th>Qabul qiluvchi</th>
                    <th class="text-end">Summa</th><th>Holat</th><th>Sana</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($transferlar as $t)
                <tr class="{{ $t->holat==="yuborildi" && Auth::user()->filial_id==$t->to_filial_id ? "table-warning" : "" }}">
                    <td class="fw-medium small">{{ $t->transfer_raqam }}</td>
                    <td><span class="badge bg-secondary">{{ $t->fromFilial->kod }}</span><br><small>{{ $t->fromKassa->nomi }}</small></td>
                    <td><span class="badge bg-primary">{{ $t->toFilial->kod }}</span><br><small>{{ $t->toKassa->nomi }}</small></td>
                    <td class="text-end fw-bold">
                        {{ number_format($t->summa,0,"."," ") }}
                        @if($t->valyuta!=="UZS") <small class="text-muted">{{ $t->valyuta }}</small>@endif
                    </td>
                    <td><span class="badge bg-{{ $t->holat_rangi }}" style="font-size:.7rem">{{ $t->holat }}</span></td>
                    <td class="text-muted small">{{ $t->sana->format("d.m.Y") }}</td>
                    <td><a href="{{ route("transfer.kassa.show",$t) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Transferlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transferlar->hasPages())
    <div class="card-footer d-flex justify-content-between py-2">
        <small>{{ $transferlar->firstItem() }}–{{ $transferlar->lastItem() }} / {{ $transferlar->total() }}</small>
        {{ $transferlar->links("pagination::bootstrap-5") }}
    </div>
    @endif
</div>
@endsection
