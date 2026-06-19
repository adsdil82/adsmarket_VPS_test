@extends('layouts.app')
@section('title', isset($qurilma) ? 'Qurilmani tahrirlash' : 'Yangi qurilma')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.index') }}">Qurilmalar</a></li>
<li class="breadcrumb-item active">{{ isset($qurilma) ? 'Tahrirlash' : 'Yangi' }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0">
        <h5 class="fw-bold mb-0">
            <i class="bi bi-phone me-2" style="color:#6366f1"></i>
            {{ isset($qurilma) ? 'Qurilmani tahrirlash' : 'Yangi qurilma qo\'shish' }}
        </h5>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ isset($qurilma) ? route('qurilmalar.update',$qurilma) : route('qurilmalar.store') }}">
            @csrf
            @if(isset($qurilma)) @method('PUT') @endif

            <div class="row g-3">

                {{-- Tovar katalogi --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Tovar katalogi (ixtiyoriy)</label>
                    <select name="tovar_katalog_id" class="form-select">
                        <option value="">— tanlang —</option>
                        @foreach($kataloglar as $k)
                        <option value="{{ $k->id }}"
                                data-brend="{{ explode(' ', $k->nomi)[0] ?? '' }}"
                                data-model="{{ $k->nomi }}"
                                {{ old('tovar_katalog_id', $qurilma->tovar_katalog_id ?? '') == $k->id ? 'selected' : '' }}>
                            {{ $k->guruh?->nomi ? '[' . $k->guruh->nomi . '] ' : '' }}{{ $k->nomi }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filial --}}
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Filial <span class="text-danger">*</span></label>
                    @if(Auth::user()->isAdmin())
                    <select name="filial_id" class="form-select @error('filial_id') is-invalid @enderror" required>
                        <option value="">— tanlang —</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ old('filial_id', $qurilma->filial_id ?? Auth::user()->filial_id) == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="hidden" name="filial_id" value="{{ Auth::user()->filial_id }}">
                    <input type="text" class="form-control" value="{{ $filiallar->first()?->nomi }}" readonly>
                    @endif
                    @error('filial_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Brend --}}
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Brend</label>
                    <input type="text" name="brend" class="form-control @error('brend') is-invalid @enderror" id="brend-input"
                           value="{{ old('brend', $qurilma->brend ?? '') }}" placeholder="Samsung, Apple, Xiaomi...">
                    @error('brend')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Model --}}
                <div class="col-sm-8">
                    <label class="form-label fw-semibold">Model nomi <span class="text-danger">*</span></label>
                    <input type="text" name="model_nomi" class="form-control @error('model_nomi') is-invalid @enderror" id="model-input"
                           value="{{ old('model_nomi', $qurilma->model_nomi ?? '') }}" required>
                    @error('model_nomi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Rang --}}
                <div class="col-sm-4">
                    <label class="form-label fw-semibold">Rang</label>
                    <input type="text" name="rang" class="form-control"
                           value="{{ old('rang', $qurilma->rang ?? '') }}" placeholder="Qora, Oq...">
                </div>

                {{-- Xotira --}}
                <div class="col-sm-4">
                    <label class="form-label fw-semibold">Xotira</label>
                    <input type="text" name="xotira" class="form-control"
                           value="{{ old('xotira', $qurilma->xotira ?? '') }}" placeholder="128GB">
                </div>

                {{-- Serial --}}
                <div class="col-sm-8">
                    <label class="form-label fw-semibold">Serial raqam</label>
                    <input type="text" name="serial_raqam" class="form-control font-monospace"
                           value="{{ old('serial_raqam', $qurilma->serial_raqam ?? '') }}" placeholder="SN...">
                </div>

                {{-- IMEI guruh --}}
                <div class="col-12">
                    <div class="p-3 rounded" style="background:#f8f9fa;border:1px solid #dee2e6">
                        <div class="fw-semibold mb-2"><i class="bi bi-sim me-1 text-primary"></i>IMEI raqamlar</div>
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Har bir IMEI 15 ta raqamdan iborat bo'lishi shart. IMEI1 telefon uchun tavsiya etiladi.
                        </div>
                        @if(isset($qurilma) && !Auth::user()->isAdmin())
                        <div class="alert alert-warning py-2 small mb-3">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            IMEI o'zgartirish faqat admin uchun. Yangi qurilma qo'shishda IMEI kiritiladi.
                        </div>
                        @endif
                        <div class="row g-2">
                            @foreach([1=>['Asosiy IMEI (IMEI 1)', 'majburiy emas'],2=>['IMEI 2','2-SIM/bo\'sh'],3=>['IMEI 3','ixtiyoriy'],4=>['IMEI 4','ixtiyoriy']] as $n => [$label, $hint])
                            <div class="col-sm-6 col-md-3">
                                <label class="form-label small fw-semibold">{{ $label }}</label>
                                <input type="text" name="imei{{ $n }}"
                                       class="form-control font-monospace @error('imei'.$n) is-invalid @enderror"
                                       value="{{ old('imei'.$n, $qurilma->{'imei'.$n} ?? '') }}"
                                       maxlength="15" inputmode="numeric"
                                       pattern="\d{15}"
                                       placeholder="{{ $hint }}"
                                       {{ isset($qurilma) && !Auth::user()->isAdmin() ? 'readonly' : '' }}>
                                @error('imei'.$n)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Qo'shilgan sana --}}
                <div class="col-sm-4">
                    <label class="form-label fw-semibold">Qo'shilgan sana</label>
                    <input type="date" name="qoshilgan_sana" class="form-control"
                           value="{{ old('qoshilgan_sana', isset($qurilma) ? $qurilma->qoshilgan_sana?->format('Y-m-d') : today()->toDateString()) }}">
                </div>

                {{-- Izoh --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Izoh</label>
                    <textarea name="izoh" class="form-control" rows="2">{{ old('izoh', $qurilma->izoh ?? '') }}</textarea>
                </div>

            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary fw-semibold">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
                <a href="{{ isset($qurilma) ? route('qurilmalar.show',$qurilma) : route('qurilmalar.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>

@push('scripts')
<script>
// Katalog tanlanganda brend va modelni avtomatik to'ldirish
document.querySelector('[name="tovar_katalog_id"]')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const brend = opt.dataset.brend || '';
    const model = opt.dataset.model || '';
    if (brend) document.getElementById('brend-input').value = brend;
    if (model) document.getElementById('model-input').value = model;
});
// IMEI faqat raqam
document.querySelectorAll('[name^="imei"]').forEach(input => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0,15);
    });
});
</script>
@endpush
@endsection
