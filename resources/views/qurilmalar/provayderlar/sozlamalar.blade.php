@extends('layouts.app')
@section('title',$provayder->nomi.' — Sozlamalar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.index') }}">Qurilmalar</a></li>
<li class="breadcrumb-item"><a href="{{ route('qurilma-provayderlar.index') }}">Provayderlar</a></li>
<li class="breadcrumb-item active">{{ $provayder->nomi }}</li>
@endsection
@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-transparent border-0">
        <h6 class="fw-bold mb-0"><i class="bi bi-gear me-2"></i>{{ $provayder->nomi }} — Sozlamalar</h6>
    </div>
    <div class="card-body">
        @if($sozlamalar->isEmpty())
        <p class="text-muted">Bu provayder uchun sozlamalar yo'q.</p>
        @else
        <form method="POST" action="{{ route('qurilma-provayderlar.sozlama-saqlash',$provayder) }}">
            @csrf
            <div class="row g-3">
                @foreach($sozlamalar as $i => $s)
                <input type="hidden" name="sozlamalar[{{ $i }}][kalit]" value="{{ $s->kalit }}">
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ $s->sarlavha }}
                        @if($s->majburiy)<span class="text-danger">*</span>@endif
                        <span class="badge bg-secondary ms-1" style="font-size:.65rem">{{ $s->tur }}</span>
                    </label>
                    @if($s->isSecret())
                    <input type="password" name="sozlamalar[{{ $i }}][qiymat]"
                           class="form-control font-monospace"
                           placeholder="O'zgartirish uchun yozing (bo'sh qoldirsa o'zgarmaydi)"
                           autocomplete="new-password">
                    @else
                    <input type="text" name="sozlamalar[{{ $i }}][qiymat]"
                           class="form-control"
                           value="{{ $s->qiymat ?? '' }}">
                    @endif
                    @if($s->tavsif)<div class="form-text text-muted">{{ $s->tavsif }}</div>@endif
                </div>
                @endforeach
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check me-1"></i>Saqlash</button>
            </div>
        </form>
        @endif
    </div>
</div>

{{-- Yangi sozlama qo'shish --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Yangi sozlama qo'shish</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('qurilma-provayderlar.sozlama-qoshish',$provayder) }}" class="row g-2">
            @csrf
            <div class="col-sm-3">
                <input type="text" name="kalit" class="form-control form-control-sm" placeholder="kalit (masalan: api_url)" required>
            </div>
            <div class="col-sm-3">
                <input type="text" name="sarlavha" class="form-control form-control-sm" placeholder="Sarlavha" required>
            </div>
            <div class="col-sm-2">
                <select name="tur" class="form-select form-select-sm">
                    <option value="string">string</option>
                    <option value="secret">secret</option>
                    <option value="boolean">boolean</option>
                    <option value="integer">integer</option>
                    <option value="json">json</option>
                </select>
            </div>
            <div class="col-sm-3">
                <input type="text" name="qiymat" class="form-control form-control-sm" placeholder="Qiymat">
            </div>
            <div class="col-sm-1">
                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-plus"></i></button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
