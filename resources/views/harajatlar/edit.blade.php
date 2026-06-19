@extends('layouts.app')
@section('title', isset($harajat) ? 'Harajatni tahrirlash' : 'Yangi harajat')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('harajatlar.index') }}">Harajatlar</a></li>
<li class="breadcrumb-item active">{{ isset($harajat) ? 'Tahrirlash' : 'Yangi' }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 py-3">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-wallet2 me-2 text-danger"></i>
            {{ isset($harajat) ? 'Harajatni tahrirlash' : 'Yangi harajat kiritish' }}
        </h5>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ isset($harajat) ? route('harajatlar.update',$harajat) : route('harajatlar.store') }}">
            @csrf
            @if(isset($harajat)) @method('PUT') @endif

            @if(Auth::user()->isAdmin())
            <div class="mb-3">
                <label class="form-label fw-semibold">Filial <span class="text-danger">*</span></label>
                <select name="filial_id" class="form-select @error('filial_id') is-invalid @enderror" required>
                    <option value="">— tanlang —</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ old('filial_id', $harajat->filial_id ?? '') == $f->id ? 'selected' : '' }}>
                            {{ $f->nomi }}
                        </option>
                    @endforeach
                </select>
                @error('filial_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @else
            <input type="hidden" name="filial_id" value="{{ Auth::user()->filial_id }}">
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Sana <span class="text-danger">*</span></label>
                <input type="date" name="sana" class="form-control @error('sana') is-invalid @enderror"
                       value="{{ old('sana', isset($harajat) ? $harajat->sana->format('Y-m-d') : today()->toDateString()) }}" required>
                @error('sana')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Harajat turi <span class="text-danger">*</span></label>
                <input type="text" name="turi" id="turi-input"
                       class="form-control @error('turi') is-invalid @enderror"
                       value="{{ old('turi', $harajat->turi ?? '') }}"
                       list="turi-list" placeholder="Tur kiriting yoki tanlang..." required>
                <datalist id="turi-list">
                    @foreach($turlari as $t)
                    <option value="{{ $t }}">
                    @endforeach
                </datalist>
                @error('turi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Summa (so'm) <span class="text-danger">*</span></label>
                <input type="number" name="summa" step="0.01"
                       class="form-control @error('summa') is-invalid @enderror"
                       value="{{ old('summa', $harajat->summa ?? '') }}"
                       placeholder="Masalan: 150000" required>
                <div class="form-text text-muted">Ijobiy son — chiqim, manfiy son — qaytarish/kirim</div>
                @error('summa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Izoh / Mazmun</label>
                <textarea name="mazmuni" class="form-control @error('mazmuni') is-invalid @enderror"
                          rows="3" placeholder="Harajat haqida qo'shimcha ma'lumot...">{{ old('mazmuni', $harajat->mazmuni ?? '') }}</textarea>
                @error('mazmuni')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
                <a href="{{ route('harajatlar.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
