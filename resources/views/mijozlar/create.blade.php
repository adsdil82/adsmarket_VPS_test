@extends('layouts.app')

@section('title', 'Yangi mijoz')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.index') }}">Mijozlar</a></li>
    <li class="breadcrumb-item active">Yangi mijoz</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-2">
    <h6 class="fw-bold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Yangi mijoz qo'shish</h6>
    <a href="{{ route('mijozlar.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Mijozlar ro'yxati
    </a>
</div>

<form method="POST" action="{{ route('mijozlar.store') }}" enctype="multipart/form-data">
    @csrf
    @include('mijozlar._form')

    <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Saqlash
        </button>
        <a href="{{ route('mijozlar.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
    </div>
</form>
@endsection
