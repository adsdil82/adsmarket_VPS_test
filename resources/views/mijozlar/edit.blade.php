@extends('layouts.app')

@section('title', 'Mijozni tahrirlash')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.index') }}">Mijozlar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.show', $mijoz) }}">{{ $mijoz->familiya }} {{ $mijoz->ism }}</a></li>
    <li class="breadcrumb-item active">Tahrirlash</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-pencil me-1"></i> Mijozni tahrirlash</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('mijozlar.update', $mijoz) }}">
            @csrf
            @method('PUT')
            @include('mijozlar._form')

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Yangilash
                </button>
                <a href="{{ route('mijozlar.show', $mijoz) }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
