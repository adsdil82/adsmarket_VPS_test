@extends('layouts.app')
@section('title', 'Tovar kirim')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ombor.index') }}">Ombor</a></li>
    <li class="breadcrumb-item active">Tovar kirim</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Tovar kirim</h5>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-box-arrow-in-down fs-1 text-success opacity-25 d-block mb-3"></i>
        <h5 class="text-muted">Tovar kirim moduli</h5>
        <p class="text-muted small">Bu bo'lim yaqin orada ishga tushiriladi.<br>
        Hozirda tovarlar shartnomalar orqali kirim qilinmoqda.</p>
        <a href="{{ route('kreditlar.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Yangi shartnoma (tovar sotish)
        </a>
    </div>
</div>
@endsection
