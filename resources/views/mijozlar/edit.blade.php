@extends('layouts.app')

@section('title', 'Mijozni tahrirlash')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.index') }}">Mijozlar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.show', $mijoz) }}">{{ $mijoz->familiya }} {{ $mijoz->ism }}</a></li>
    <li class="breadcrumb-item active">Tahrirlash</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-10">

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-primary"></i>Mijozni tahrirlash</h5>
    <a href="{{ route('mijozlar.show', $mijoz) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Mijoz kartochkasi
    </a>
</div>

<form method="POST" action="{{ route('mijozlar.update', $mijoz) }}">
    @csrf
    @method('PUT')
    @include('mijozlar._form')

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Yangilash
        </button>
        <a href="{{ route('mijozlar.show', $mijoz) }}" class="btn btn-outline-secondary">Bekor qilish</a>
    </div>
</form>

</div>
</div>
@endsection
