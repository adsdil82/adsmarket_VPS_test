@extends('errors.layout')
@section('content')
<div class="xato-card" style="max-width:500px">
    <div class="xato-header" style="background:#6c757d">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-search fs-4"></i>
            <h5 class="mb-0">404 — Sahifa topilmadi</h5>
        </div>
    </div>
    <div class="xato-body text-center py-5">
        <i class="bi bi-file-earmark-x fs-1 text-secondary d-block mb-3"></i>
        <h5>So'ralgan sahifa mavjud emas</h5>
        <p class="text-muted small">URL: <code>{{ request()->url() }}</code></p>
        <a href="{{ url()->previous() }}" class="btn btn-primary me-2">
            <i class="bi bi-arrow-left me-1"></i>Orqaga
        </a>
        <a href="/" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i>Bosh sahifa
        </a>
    </div>
</div>
@endsection
