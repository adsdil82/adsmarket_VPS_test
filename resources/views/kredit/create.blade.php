@extends('layouts.app')
@section('title', 'Yangi shartnoma')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item active">Yangi shartnoma</li>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center gap-2 py-2">
        <i class="bi bi-file-earmark-plus text-primary fs-5"></i>
        <h6 class="mb-0 fw-bold">Yangi nasiya shartnomasi</h6>
    </div>
    <div class="card-body p-0">
        @if($errors->any())
        <div class="alert alert-danger mx-3 mt-3 mb-0 py-2">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif
        <form method="POST" action="{{ route('kreditlar.store') }}" id="kredit-form">
            @csrf
            @php $isEdit = false; $kredit = null; @endphp
            @include('kredit._form_tabs')
        </form>
    </div>
</div>
@include('kredit._mijoz_modal')
@include('kredit._tovar_modal')
@include('kredit._modal_js')
@endsection
