@extends('errors.layout')
@section('content')
<div class="xato-card" style="max-width:500px">
    <div class="xato-header" style="background:#fd7e14">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-lock-fill fs-4"></i>
            <h5 class="mb-0">403 — Kirish taqiqlangan</h5>
        </div>
    </div>
    <div class="xato-body text-center py-5">
        <i class="bi bi-shield-x fs-1 text-warning d-block mb-3"></i>
        <h5>Bu sahifaga kirishga ruxsatingiz yo'q</h5>
        <p class="text-muted">{{ $exception?->getMessage() ?? 'Sizning rolingizda bu amal bajarishga ruxsat berilmagan.' }}</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary me-2">
            <i class="bi bi-arrow-left me-1"></i>Orqaga
        </a>
        <a href="/" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i>Bosh sahifa
        </a>
    </div>
</div>
@endsection
