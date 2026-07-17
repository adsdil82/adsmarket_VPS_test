@extends('layouts.app')
@section('title', "Ta'minotchiga qaytarish")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Ta'minotchiga qaytarish</li>
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
    'qoralama'     => ['label' => 'Qoralama',     'icon' => 'bi-file-earmark'],
    'tasdiqlangan' => ['label' => 'Tasdiqlangan', 'icon' => 'bi-check-circle'],
    'qaytarildi'   => ['label' => 'Qaytarildi',   'icon' => 'bi-arrow-return-left'],
    'bekor'        => ['label' => 'Bekor',        'icon' => 'bi-x-circle'],
];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-return-left me-2 text-secondary"></i>Ta'minotchiga qaytarish</h5>
    @if(Auth::user()->isOmborchi())
    <a href="{{ route('transfer.supplier-return.create') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Yangi qaytarish
    </a>
    @endif
</div>

{{-- Holat tab --}}
<div class="holat-tabs mb-2">
    @foreach($holatTablari as $key => $h)
    <a class="holat-tab {{ request('holat', '') === $key ? 'active' : '' }}"
       href="{{ route('transfer.supplier-return.index', array_merge(request()->except(['holat', 'page']), $key !== '' ? ['holat' => $key] : [])) }}">
        <i class="bi {{ $h['icon'] }}"></i> {{ $h['label'] }}
    </a>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="holat" value="{{ request('holat') }}">
            <div class="col-sm-3">
                <select name="taminotchi_id" class="form-select form-select-sm">
                    <option value="">Barcha ta'minotchilar</option>
                    @foreach($taminotchilar as $t)
                    <option value="{{ $t->id }}" {{ request("taminotchi_id")==$t->id?"selected":"" }}>{{ $t->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route("transfer.supplier-return.index") }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Hujjat #</th><th>Ta'minotchi</th><th>Ombor</th><th>Sana</th><th class="text-end">Jami</th><th>Holat</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($qaytarishlar as $q)
                <tr>
                    <td class="fw-medium small">{{ $q->hujjat_raqam }}</td>
                    <td class="small">{{ $q->taminotchi->nomi }}</td>
                    <td class="small text-muted">{{ $q->ombor->nomi }}</td>
                    <td class="small text-muted">{{ $q->sana->format("d.m.Y") }}</td>
                    <td class="text-end small">{{ number_format($q->jami_summa,0,"."," ") }}</td>
                    <td><span class="badge bg-{{ $q->holat_rangi }}" style="font-size:.68rem">{{ $q->holat }}</span></td>
                    <td><a href="{{ route("transfer.supplier-return.show",$q) }}" class="btn btn-sm btn-outline-secondary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Qaytarishlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($qaytarishlar->hasPages())
    <div class="card-footer py-2">{{ $qaytarishlar->links("pagination::bootstrap-5") }}</div>
    @endif
</div>
@endsection
