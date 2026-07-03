@extends('layouts.app')
@section('title', 'Operatsiyani tahrirlash')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pul-oqimlari.index') }}">Pul Oqimlari</a></li>
<li class="breadcrumb-item active">Tahrirlash</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">
            @if($pulOqim->yunalish === 'kirim')
                <i class="bi bi-arrow-up-circle me-2 text-success"></i>Kirim operatsiyani tahrirlash
            @else
                <i class="bi bi-arrow-down-circle me-2 text-danger"></i>Chiqim operatsiyani tahrirlash
            @endif
        </h5>
        <span class="badge bg-secondary text-white" style="font-size:.7rem">
            #{{ $pulOqim->id }} &bull; {{ $pulOqim->sana->format('d.m.Y') }}
        </span>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        @if($pulOqim->manba_tur !== 'manual')
        <div class="alert alert-warning py-2 mb-3" style="font-size:.83rem">
            <i class="bi bi-info-circle me-1"></i>Bu yozuv <strong>{{ $pulOqim->manba_tur }}</strong> manbasidan avtomatik yaratilgan. Faqat izoh va sana tahrirlash tavsiya etiladi.
        </div>
        @endif

        <form method="POST" action="{{ route('pul-oqimlari.update', $pulOqim) }}">
            @csrf @method('PUT')
            <input type="hidden" name="yunalish" value="{{ $pulOqim->yunalish }}">

            @if(Auth::user()->isAdmin())
            <div class="mb-3">
                <label class="form-label fw-semibold">Filial <span class="text-danger">*</span></label>
                <select name="filial_id" class="form-select @error('filial_id') is-invalid @enderror" required>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ old('filial_id', $pulOqim->filial_id) == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
                @error('filial_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @else
            <input type="hidden" name="filial_id" value="{{ $pulOqim->filial_id }}">
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Kassa <span class="text-danger">*</span></label>
                <select name="kassa_id" class="form-select @error('kassa_id') is-invalid @enderror" required>
                    <option value="">— tanlang —</option>
                    @foreach($kassalar as $k)
                        <option value="{{ $k->id }}" {{ old('kassa_id', $pulOqim->kassa_id) == $k->id ? 'selected' : '' }}>{{ $k->nomi }}</option>
                    @endforeach
                </select>
                @error('kassa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kategoriya <span class="text-danger">*</span></label>
                @php $yunalish = $pulOqim->yunalish; @endphp
                <select name="kategoriya_id" class="form-select @error('kategoriya_id') is-invalid @enderror" required>
                    <option value="">— tanlang —</option>
                    @if($yunalish === 'kirim')
                        @foreach($kirimKategoriyalar as $id => $nomi)
                        <option value="{{ $id }}" {{ old('kategoriya_id', $pulOqim->kategoriya_id) == $id ? 'selected' : '' }}>{{ $nomi }}</option>
                        @endforeach
                    @else
                        @foreach($chiqimKategoriyalar as $id => $nomi)
                        <option value="{{ $id }}" {{ old('kategoriya_id', $pulOqim->kategoriya_id) == $id ? 'selected' : '' }}>{{ $nomi }}</option>
                        @endforeach
                    @endif
                </select>
                @error('kategoriya_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sana <span class="text-danger">*</span></label>
                <input type="date" name="sana" class="form-control @error('sana') is-invalid @enderror"
                       value="{{ old('sana', $pulOqim->sana->format('Y-m-d')) }}" required>
                @error('sana')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Summa (so'm) <span class="text-danger">*</span></label>
                <input type="number" name="summa" step="1" min="1"
                       class="form-control @error('summa') is-invalid @enderror"
                       value="{{ old('summa', $pulOqim->summa) }}" required>
                @error('summa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Izoh</label>
                <textarea name="izoh" class="form-control @error('izoh') is-invalid @enderror"
                          rows="3">{{ old('izoh', $pulOqim->izoh) }}</textarea>
                @error('izoh')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn {{ $yunalish === 'kirim' ? 'btn-success' : 'btn-danger' }} fw-semibold">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
                <a href="{{ route('pul-oqimlari.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
