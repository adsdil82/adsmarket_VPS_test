@extends('layouts.app')
@section('title','Filiallar arasi transfer')
@section('breadcrumb')
<li class="breadcrumb-item active">Transfer</li>
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
    ''             => ['label' => 'Barchasi',     'icon' => 'bi-list-ul'],
    'kutilmoqda'   => ['label' => 'Kutilmoqda',   'icon' => 'bi-hourglass-split'],
    'tasdiqlangan' => ['label' => 'Tasdiqlangan', 'icon' => 'bi-check-circle'],
    'bekor'        => ['label' => 'Bekor',        'icon' => 'bi-x-circle'],
];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-info"></i>Filiallar arasi tovar transfer</h5>
    <a href="{{ route('transfer.create') }}" class="btn btn-info text-white">
        <i class="bi bi-plus-lg me-1"></i>Yangi transfer
    </a>
</div>

<div class="holat-tabs mb-2">
    @foreach($holatTablari as $key => $h)
    <a class="holat-tab {{ request('holat', '') === $key ? 'active' : '' }}"
       href="{{ route('transfer.index', array_merge(request()->except(['holat', 'page']), $key !== '' ? ['holat' => $key] : [])) }}">
        <i class="bi {{ $h['icon'] }}"></i> {{ $h['label'] }}
    </a>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="holat" value="{{ request('holat') }}">
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('transfer.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sana</th>
                    <th>Jo'natuvchi</th>
                    <th>Qabul qiluvchi</th>
                    <th>Xodim</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transferlar as $t)
                <tr>
                    <td class="text-muted small">{{ $t->id }}</td>
                    <td>{{ $t->sana->format('d.m.Y') }}</td>
                    <td>
                        <span class="badge bg-danger">{{ $t->fromFilial?->nomi }}</span>
                    </td>
                    <td>
                        <span class="badge bg-success">{{ $t->toFilial?->nomi }}</span>
                    </td>
                    <td class="text-muted small">{{ $t->xodim?->ism_familiya }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $t->holat_rangi }}">{{ $t->holat }}</span>
                    </td>
                    <td>
                        <a href="{{ route('transfer.show',$t) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($t->holat === 'kutilmoqda')
                        @if(Auth::user()->isAdmin() || Auth::user()->filial_id === $t->to_filial_id)
                        <form method="POST" action="{{ route('transfer.tasdiqla',$t) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success py-0" title="Qabul qilish">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        @endif
                        @if(Auth::user()->isAdmin() || Auth::user()->filial_id === $t->from_filial_id)
                        <form method="POST" action="{{ route('transfer.bekor',$t) }}" class="d-inline"
                              onsubmit="return confirm('Bekor qilinsinmi?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger py-0" title="Bekor qilish">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                        @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Transferlar yo'q
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transferlar->hasPages())
    <div class="card-footer">{{ $transferlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
