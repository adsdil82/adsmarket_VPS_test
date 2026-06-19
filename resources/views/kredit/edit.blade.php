@extends('layouts.app')
@section('title', 'Shartnomani tahrirlash')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.show', $kredit) }}">{{ $kredit->shartnoma_raqam }}</a></li>
    <li class="breadcrumb-item active">Tahrirlash</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center gap-2 py-2">
        <i class="bi bi-pencil-square text-warning fs-5"></i>
        <h6 class="mb-0 fw-bold">{{ $kredit->shartnoma_raqam }} — Tahrirlash</h6>
        <span class="badge bg-{{ $kredit->holat_rangi }} ms-auto">{{ $kredit->holatNomi }}</span>
    </div>
    <div class="card-body p-0">
        @if($errors->any())
        <div class="alert alert-danger mx-3 mt-3 mb-0 py-2">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif
        <form method="POST" action="{{ route('kreditlar.update', $kredit) }}" id="kredit-form">
            @csrf
            @method('PUT')
            @php $isEdit = true; @endphp
            @include('kredit._form_tabs')
        </form>
    </div>
</div>
@include('kredit._mijoz_modal')
@include('kredit._tovar_modal')
@include('kredit._modal_js')
@endsection
