@extends('layouts.app')
@section('title','Yangi kirim — ta\'minotchi tanlash')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('kirim.index') }}">Kirim</a></li>
<li class="breadcrumb-item active">Ta'minotchi tanlash</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#14532d,#15803d); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.taminotchi-card {
    display:block; text-decoration:none; color:#334155; border:1px solid #86efac; border-radius:8px;
    padding:14px; background:#fff; transition:.15s; height:100%;
}
.taminotchi-card:hover { background:#f0fdf4; border-color:#16a34a; box-shadow:0 2px 8px rgba(22,163,74,.15); }
.taminotchi-card .nomi { font-weight:700; color:#14532d; font-size:.95rem; }
.taminotchi-card .tafsilot { font-size:.78rem; color:#64748b; margin-top:4px; }
</style>
@endpush

@section('content')

<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-truck fs-5"></i>
        <span class="fw-bold">Yangi kirim — avval ta'minotchini tanlang</span>
    </div>
    <a href="{{ route('kirim.index') }}" class="btn btn-sm btn-light py-1">
        <i class="bi bi-arrow-left me-1"></i>Orqaga
    </a>
</div>

<div class="alert alert-light border py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Kirim faqat ta'minotchiga bog'langan holda qayd etiladi (qarz/to'lov kuzatuvi bilan). Ro'yxatda yo'q ta'minotchini
    avval <a href="{{ route('taminotchi.create') }}">Ta'minotchilar</a> bo'limida qo'shing.
</div>

<div class="row g-3">
    @forelse($taminotchilar as $t)
    <div class="col-md-4 col-lg-3">
        <a href="{{ route('taminotchi.kirim.create', $t) }}" class="taminotchi-card">
            <div class="nomi"><i class="bi bi-building me-1"></i>{{ $t->nomi }}</div>
            <div class="tafsilot">
                @if($t->telefon) <i class="bi bi-telephone me-1"></i>{{ $t->telefon }}<br>@endif
                @if($t->filial) <span class="badge bg-secondary">{{ $t->filial->kod }}</span> @endif
            </div>
        </a>
    </div>
    @empty
    <div class="col-12 text-center text-muted py-5">
        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Faol ta'minotchilar topilmadi
    </div>
    @endforelse
</div>
@endsection
