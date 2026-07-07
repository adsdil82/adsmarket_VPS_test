@extends('layouts.app')
@section('title', 'Smena ochish')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item active">Smena ochish</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header text-center py-3" style="background:linear-gradient(90deg,#14532d,#15803d);color:#fff">
                <i class="bi bi-door-open fs-2 d-block mb-2"></i>
                <h5 class="fw-bold mb-0">Yangi smena ochish</h5>
                <div class="small opacity-75">Savdo qilishdan oldin smenani ochish shart</div>
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                <div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('pos.smena.ochish') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kassadagi dastlabki (naqd) qoldiq</label>
                        <div class="input-group">
                            <input type="number" name="dastlabki_qoldiq" class="form-control form-control-lg" step="1000" min="0" value="{{ old('dastlabki_qoldiq', $taklifQoldiq) }}" required autofocus>
                            <span class="input-group-text">so'm</span>
                        </div>
                        <div class="form-text">Oxirgi yopilgan smenaning yakuniy qoldig'i taklif qilindi — kerak bo'lsa o'zgartiring.</div>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-play-fill me-1"></i>Smenani ochish
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
