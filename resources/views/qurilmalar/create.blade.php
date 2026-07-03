@extends('layouts.app')
@section('title', isset($qurilma) ? 'Qurilmani tahrirlash' : 'Yangi qurilma')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.index') }}">Qurilmalar</a></li>
<li class="breadcrumb-item active">{{ isset($qurilma) ? 'Tahrirlash' : 'Yangi' }}</li>
@endsection

@push('styles')
<style>
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px 8px 0 0;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { max-width:900px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; }
.bft-table { width:100%; margin-bottom:0 !important; font-size:.85rem; }
.bft-table td { padding:8px 12px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:100%; }
</style>
@endpush

@section('content')

<div class="bft-header-card">
    <span class="fw-bold"><i class="bi bi-phone me-1"></i>{{ isset($qurilma) ? 'Qurilmani tahrirlash' : "Yangi qurilma qo'shish" }}</span>
</div>

<div class="bft-wrap mb-3">
    <div class="p-3">
        @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ isset($qurilma) ? route('qurilmalar.update',$qurilma) : route('qurilmalar.store') }}">
            @csrf
            @if(isset($qurilma)) @method('PUT') @endif

            <table class="bft-table">
                <tbody>
                    <tr>
                        <td class="bft-label">Tovar katalogi <small class="fw-normal">(ixtiyoriy)</small></td>
                        <td class="bft-wide">
                            <select name="tovar_katalog_id" class="form-select form-select-sm" style="max-width:400px">
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
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Filial <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            @if(Auth::user()->isAdmin())
                            <select name="filial_id" class="form-select form-select-sm @error('filial_id') is-invalid @enderror" style="max-width:280px" required>
                                <option value="">— tanlang —</option>
                                @foreach($filiallar as $f)
                                <option value="{{ $f->id }}" {{ old('filial_id', $qurilma->filial_id ?? Auth::user()->filial_id) == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                                @endforeach
                            </select>
                            @else
                            <input type="hidden" name="filial_id" value="{{ Auth::user()->filial_id }}">
                            <input type="text" class="form-control form-control-sm" style="max-width:280px" value="{{ $filiallar->first()?->nomi }}" readonly>
                            @endif
                            @error('filial_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Brend</td>
                        <td class="bft-wide">
                            <input type="text" name="brend" class="form-control form-control-sm @error('brend') is-invalid @enderror" id="brend-input"
                                   style="max-width:280px" value="{{ old('brend', $qurilma->brend ?? '') }}" placeholder="Samsung, Apple, Xiaomi...">
                            @error('brend')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Model nomi <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <input type="text" name="model_nomi" class="form-control form-control-sm @error('model_nomi') is-invalid @enderror" id="model-input"
                                   style="max-width:320px" value="{{ old('model_nomi', $qurilma->model_nomi ?? '') }}" required>
                            @error('model_nomi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Rang</td>
                        <td class="bft-wide">
                            <input type="text" name="rang" class="form-control form-control-sm" style="max-width:200px"
                                   value="{{ old('rang', $qurilma->rang ?? '') }}" placeholder="Qora, Oq...">
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Xotira</td>
                        <td class="bft-wide">
                            <input type="text" name="xotira" class="form-control form-control-sm" style="max-width:160px"
                                   value="{{ old('xotira', $qurilma->xotira ?? '') }}" placeholder="128GB">
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Serial raqam</td>
                        <td class="bft-wide">
                            <input type="text" name="serial_raqam" class="form-control form-control-sm font-monospace" style="max-width:280px"
                                   value="{{ old('serial_raqam', $qurilma->serial_raqam ?? '') }}" placeholder="SN...">
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Qo'shilgan sana</td>
                        <td class="bft-wide">
                            <input type="date" name="qoshilgan_sana" class="form-control form-control-sm" style="max-width:200px"
                                   value="{{ old('qoshilgan_sana', isset($qurilma) ? $qurilma->qoshilgan_sana?->format('Y-m-d') : today()->toDateString()) }}">
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Izoh</td>
                        <td class="bft-wide">
                            <textarea name="izoh" class="form-control form-control-sm" rows="2" style="max-width:420px">{{ old('izoh', $qurilma->izoh ?? '') }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- IMEI raqamlar --}}
            <div class="bft-section-title mt-3"><i class="bi bi-sim me-1"></i>IMEI raqamlar</div>
            <div class="bft-wrap mb-3">
                <div class="p-3">
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
                    <table class="bft-table" style="max-width:100%">
                        <tbody>
                            @foreach([1=>['Asosiy IMEI (IMEI 1)', 'majburiy emas'],2=>['IMEI 2','2-SIM/bo\'sh'],3=>['IMEI 3','ixtiyoriy'],4=>['IMEI 4','ixtiyoriy']] as $n => [$label, $hint])
                            <tr>
                                <td class="bft-label">{{ $label }}</td>
                                <td class="bft-wide">
                                    <input type="text" name="imei{{ $n }}"
                                           class="form-control form-control-sm font-monospace @error('imei'.$n) is-invalid @enderror"
                                           style="max-width:220px"
                                           value="{{ old('imei'.$n, $qurilma->{'imei'.$n} ?? '') }}"
                                           maxlength="15" inputmode="numeric"
                                           pattern="\d{15}"
                                           placeholder="{{ $hint }}"
                                           {{ isset($qurilma) && !Auth::user()->isAdmin() ? 'readonly' : '' }}>
                                    @error('imei'.$n)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary fw-semibold">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
                <a href="{{ isset($qurilma) ? route('qurilmalar.show',$qurilma) : route('qurilmalar.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
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
