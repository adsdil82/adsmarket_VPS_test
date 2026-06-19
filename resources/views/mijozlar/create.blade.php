@extends('layouts.app')

@section('title', 'Yangi mijoz')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.index') }}">Mijozlar</a></li>
    <li class="breadcrumb-item active">Yangi mijoz</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-person-plus me-1"></i> Yangi mijoz qo'shish</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('mijozlar.store') }}">
            @csrf
            @include('mijozlar._form')

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Saqlash
                </button>
                <a href="{{ route('mijozlar.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
