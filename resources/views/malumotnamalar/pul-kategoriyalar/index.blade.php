@extends('layouts.app')
@section('title','Pul oqimi kategoriyalari')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item active">Pul oqimi kategoriyalari</li>
@endsection
@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-arrow-left-right text-primary me-2"></i>Pul oqimi kategoriyalari</h5>
        <small class="text-muted">Pul oqimlari uchun kirim va chiqim kategoriyalari</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
        <i class="bi bi-plus-lg me-1"></i>Yangi kategoriya
    </button>
</div>

@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('xato'))
<div class="alert alert-danger alert-dismissible fade show py-2">{{ session('xato') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@php
$ranglar = ['gray','green','blue','red','yellow','purple','orange','teal','pink'];
$barcha = \App\Models\PulKategoriya::whereNull('ota_id')->orderBy('sort_order')->get(['id','nomi','yunalish']);
@endphp

<div class="collapse mb-3" id="yangiForm">
    <div class="card border-success shadow-sm">
        <div class="card-header bg-success text-white py-2 fw-bold">Yangi kategoriya qo'shish</div>
        <div class="card-body">
            <form method="POST" action="{{ route('malumotnamalar.pul-kategoriyalar.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Yunalish <span class="text-danger">*</span></label>
                        <select name="yunalish" class="form-select form-select-sm" required>
                            <option value="kirim">Kirim</option>
                            <option value="chiqim">Chiqim</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
                        <input type="text" name="nomi" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Kod <span class="text-danger">*</span></label>
                        <input type="text" name="kod" class="form-control form-control-sm font-monospace" required placeholder="KIR-001">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Ota kategoriya</label>
                        <select name="ota_id" class="form-select form-select-sm">
                            <option value="">— asosiy —</option>
                            @foreach($barcha as $a)
                            <option value="{{ $a->id }}">[{{ $a->yunalish }}] {{ $a->nomi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Rang</label>
                        <select name="rang" class="form-select form-select-sm">
                            @foreach($ranglar as $r)
                            <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-save"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3">
{{-- KIRIM --}}
<div class="col-md-6">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success bg-opacity-10 border-0">
            <h6 class="mb-0 fw-bold text-success"><i class="bi bi-arrow-down-circle me-1"></i>Kirim kategoriyalari</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.85rem">
                <thead class="table-light"><tr><th>Kod</th><th>Nomi</th><th>Holat</th><th></th></tr></thead>
                <tbody>
                    @forelse($kirims as $k)
                    @include('malumotnamalar.pul-kategoriyalar._satir', ['kat'=>$k,'ranglar'=>$ranglar,'barcha'=>$barcha])
                    @foreach($k->bolalar as $b)
                    @include('malumotnamalar.pul-kategoriyalar._satir', ['kat'=>$b,'ranglar'=>$ranglar,'barcha'=>$barcha,'child'=>true])
                    @endforeach
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Kirim kategoriyasi yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
{{-- CHIQIM --}}
<div class="col-md-6">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-danger bg-opacity-10 border-0">
            <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-arrow-up-circle me-1"></i>Chiqim kategoriyalari</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.85rem">
                <thead class="table-light"><tr><th>Kod</th><th>Nomi</th><th>Holat</th><th></th></tr></thead>
                <tbody>
                    @forelse($chiqims as $c)
                    @include('malumotnamalar.pul-kategoriyalar._satir', ['kat'=>$c,'ranglar'=>$ranglar,'barcha'=>$barcha])
                    @foreach($c->bolalar as $b)
                    @include('malumotnamalar.pul-kategoriyalar._satir', ['kat'=>$b,'ranglar'=>$ranglar,'barcha'=>$barcha,'child'=>true])
                    @endforeach
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Chiqim kategoriyasi yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>

</div>
@endsection
