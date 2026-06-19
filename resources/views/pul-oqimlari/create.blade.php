@extends('layouts.app')
@section('title', 'Yangi operatsiya')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pul-oqimlari.index') }}">Pul Oqimlari</a></li>
<li class="breadcrumb-item active">Yangi</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-6">

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 py-3">
        <h5 class="fw-bold mb-0" id="sahifa-sarlavha">
            <i class="bi bi-plus-circle me-2"></i>Yangi operatsiya
        </h5>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- Yunalish switcher --}}
        <div class="mb-4">
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="yunalish_toggle" id="toggle_kirim"
                       value="kirim" {{ $yunalish === 'kirim' ? 'checked' : '' }}>
                <label class="btn btn-outline-success fw-semibold" for="toggle_kirim">
                    <i class="bi bi-arrow-up-circle me-1"></i>Kirim
                </label>
                <input type="radio" class="btn-check" name="yunalish_toggle" id="toggle_chiqim"
                       value="chiqim" {{ $yunalish === 'chiqim' ? 'checked' : '' }}>
                <label class="btn btn-outline-danger fw-semibold" for="toggle_chiqim">
                    <i class="bi bi-arrow-down-circle me-1"></i>Chiqim
                </label>
            </div>
        </div>

        <form method="POST" action="{{ route('pul-oqimlari.store') }}" id="oqim-form">
            @csrf
            <input type="hidden" name="yunalish" id="yunalish-hidden" value="{{ old('yunalish', $yunalish) }}">

            @if(Auth::user()->isAdmin())
            <div class="mb-3">
                <label class="form-label fw-semibold">Filial <span class="text-danger">*</span></label>
                <select name="filial_id" class="form-select @error('filial_id') is-invalid @enderror" required>
                    <option value="">— tanlang —</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ old('filial_id') == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
                @error('filial_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @else
            <input type="hidden" name="filial_id" value="{{ Auth::user()->filial_id }}">
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Kassa <span class="text-danger">*</span></label>
                <select name="kassa_id" class="form-select @error('kassa_id') is-invalid @enderror" required>
                    <option value="">— tanlang —</option>
                    @foreach($kassalar as $k)
                        <option value="{{ $k->id }}" {{ old('kassa_id') == $k->id ? 'selected' : '' }}>{{ $k->nomi }}</option>
                    @endforeach
                </select>
                @error('kassa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kategoriya <span class="text-danger">*</span></label>
                <select name="kategoriya_id" id="kategoriya-select" class="form-select @error('kategoriya_id') is-invalid @enderror" required>
                    <option value="">— tanlang —</option>
                    <optgroup label="── Kirim ──" class="grup-kirim">
                        @foreach($kirimKategoriyalar as $grup => $bolalar)
                            <optgroup label="  {{ $grup }}" class="grup-kirim">
                                @foreach($bolalar as $id => $nomi)
                                <option value="{{ $id }}" data-yunalish="kirim" {{ old('kategoriya_id') == $id ? 'selected' : '' }}>
                                    {{ $nomi }}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </optgroup>
                    <optgroup label="── Chiqim ──" class="grup-chiqim">
                        @foreach($chiqimKategoriyalar as $grup => $bolalar)
                            <optgroup label="  {{ $grup }}" class="grup-chiqim">
                                @foreach($bolalar as $id => $nomi)
                                <option value="{{ $id }}" data-yunalish="chiqim" {{ old('kategoriya_id') == $id ? 'selected' : '' }}>
                                    {{ $nomi }}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </optgroup>
                </select>
                @error('kategoriya_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sana <span class="text-danger">*</span></label>
                <input type="date" name="sana" class="form-control @error('sana') is-invalid @enderror"
                       value="{{ old('sana', today()->toDateString()) }}" required>
                @error('sana')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Summa (so'm) <span class="text-danger">*</span></label>
                <input type="number" name="summa" step="1" min="1"
                       class="form-control @error('summa') is-invalid @enderror"
                       value="{{ old('summa') }}" placeholder="Masalan: 150000" required>
                @error('summa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Izoh</label>
                <textarea name="izoh" class="form-control @error('izoh') is-invalid @enderror"
                          rows="3" placeholder="Operatsiya haqida qo'shimcha ma'lumot...">{{ old('izoh') }}</textarea>
                @error('izoh')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary fw-semibold" id="saqlash-btn">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
                <a href="{{ route('pul-oqimlari.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

</div>
</div>

@push('scripts')
<script>
(function() {
    const toggleKirim  = document.getElementById('toggle_kirim');
    const toggleChiqim = document.getElementById('toggle_chiqim');
    const hiddenInput  = document.getElementById('yunalish-hidden');
    const sarlavha     = document.getElementById('sahifa-sarlavha');
    const saqlashBtn   = document.getElementById('saqlash-btn');
    const katSelect    = document.getElementById('kategoriya-select');

    function applyYunalish(val) {
        hiddenInput.value = val;
        if (val === 'kirim') {
            sarlavha.innerHTML = '<i class="bi bi-arrow-up-circle me-2 text-success"></i>Yangi Kirim';
            saqlashBtn.className = 'btn btn-success fw-semibold';
        } else {
            sarlavha.innerHTML = '<i class="bi bi-arrow-down-circle me-2 text-danger"></i>Yangi Chiqim';
            saqlashBtn.className = 'btn btn-danger fw-semibold';
        }
        // Kategoriya options ko'rinishini filter qilish
        const opts = katSelect.querySelectorAll('option[data-yunalish]');
        opts.forEach(o => {
            o.style.display = (o.dataset.yunalish === val) ? '' : 'none';
        });
        // optgroup visibility (CSS trick — reset value if wrong direction selected)
        const curOpt = katSelect.selectedOptions[0];
        if (curOpt && curOpt.dataset.yunalish && curOpt.dataset.yunalish !== val) {
            katSelect.value = '';
        }
    }

    toggleKirim.addEventListener('change',  () => applyYunalish('kirim'));
    toggleChiqim.addEventListener('change', () => applyYunalish('chiqim'));

    // Init on load
    applyYunalish(hiddenInput.value || 'chiqim');
})();
</script>
@endpush
@endsection
