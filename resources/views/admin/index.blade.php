@extends('layouts.app')
@section('title', 'Admin panel')
@section('breadcrumb')
    <li class="breadcrumb-item active">Admin panel</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2 text-danger"></i>Admin panel</h5>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-people fs-2 text-primary mb-2"></i>
            <div class="fs-4 fw-bold">{{ $statistika['foydalanuvchilar'] }}</div>
            <div class="text-muted small">Jami foydalanuvchilar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-person-check fs-2 text-success mb-2"></i>
            <div class="fs-4 fw-bold text-success">{{ $statistika['faol_users'] }}</div>
            <div class="text-muted small">Faol foydalanuvchilar</div>
        </div>
    </div>
    @foreach(['admin'=>'danger','menejer'=>'primary','kassir'=>'success','hisobchi'=>'secondary'] as $rol => $rang)
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <span class="badge bg-{{ $rang }} mb-2">{{ $rol }}</span>
            <div class="fs-4 fw-bold">{{ $statistika['rollar'][$rol] ?? 0 }}</div>
            <div class="text-muted small">ta</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Sozlamalar --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-gear-wide-connected fs-2 text-secondary mb-2 d-block"></i>
                <h6 class="fw-bold">Sozlamalar</h6>
                <p class="text-muted small">Brend nomi, kompaniya rekvizitlari, interfeys temasi</p>
                <a href="{{ route('admin.sozlamalar') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-gear me-1"></i> Sozlash
                </a>
            </div>
        </div>
    </div>
    {{-- Ruxsatlar --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-key fs-2 text-warning mb-2 d-block"></i>
                <h6 class="fw-bold">Ruxsatlar</h6>
                <p class="text-muted small">Har bir rol uchun CRUD ruxsatlarini sozlang</p>
                <a href="{{ route('admin.ruxsatlar') }}" class="btn btn-warning btn-sm">
                    <i class="bi bi-sliders me-1"></i> Boshqarish
                </a>
            </div>
        </div>
    </div>
    {{-- Foydalanuvchilar --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-people fs-2 text-primary mb-2 d-block"></i>
                <h6 class="fw-bold">Foydalanuvchilar</h6>
                <p class="text-muted small">{{ $statistika['foydalanuvchilar'] }} ta foydalanuvchi, {{ $statistika['faol_users'] }} ta faol</p>
                <a href="{{ route('admin.foydalanuvchilar') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-gear me-1"></i> Ko'rish
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Hozirgi sozlamalar --}}
@if(!empty($sozlamalar['kompaniya_nomi']))
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-building me-2"></i>Hozirgi kompaniya</h6></div>
    <div class="card-body">
        <strong>{{ $sozlamalar['kompaniya_nomi'] }}</strong>
        @if($sozlamalar['kompaniya_inn'] ?? '') · STIR: {{ $sozlamalar['kompaniya_inn'] }} @endif
        @if($sozlamalar['kompaniya_telefon'] ?? '') · {{ $sozlamalar['kompaniya_telefon'] }} @endif
    </div>
</div>
@endif
@endsection
