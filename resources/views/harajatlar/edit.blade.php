@extends('layouts.app')
@section('title', isset($harajat) ? 'Harajatni tahrirlash' : 'Yangi harajat')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('harajatlar.index') }}">Harajatlar</a></li>
<li class="breadcrumb-item active">{{ isset($harajat) ? 'Tahrirlash' : 'Yangi' }}</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px 8px 0 0;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { max-width:760px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; }
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
    <span class="fw-bold"><i class="bi bi-wallet2 me-1"></i>{{ isset($harajat) ? 'Harajatni tahrirlash' : 'Yangi harajat kiritish' }}</span>
    @if(Auth::user()->isAdmin())
    <a href="{{ route('malumotnamalar.harajat-turlari.index') }}" class="btn btn-sm btn-light" target="_blank">
        <i class="bi bi-gear me-1"></i>Turlarni boshqarish
    </a>
    @endif
</div>

<div class="bft-wrap mb-3">
    <div class="p-3">
        @if($errors->any())
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ isset($harajat) ? route('harajatlar.update',$harajat) : route('harajatlar.store') }}">
            @csrf
            @if(isset($harajat)) @method('PUT') @endif

            <table class="bft-table">
                <tbody>
                    @if(Auth::user()->isAdmin())
                    <tr>
                        <td class="bft-label">Filial <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <select name="filial_id" class="form-select form-select-sm @error('filial_id') is-invalid @enderror" style="max-width:280px" required>
                                <option value="">— tanlang —</option>
                                @foreach($filiallar as $f)
                                    <option value="{{ $f->id }}" {{ old('filial_id', $harajat->filial_id ?? '') == $f->id ? 'selected' : '' }}>
                                        {{ $f->nomi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('filial_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                    @else
                    <input type="hidden" name="filial_id" value="{{ Auth::user()->filial_id }}">
                    @endif

                    <tr>
                        <td class="bft-label">Sana <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <input type="date" name="sana" class="form-control form-control-sm @error('sana') is-invalid @enderror"
                                   style="max-width:200px"
                                   value="{{ old('sana', isset($harajat) ? $harajat->sana->format('Y-m-d') : today()->toDateString()) }}" required>
                            @error('sana')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>

                    <tr>
                        <td class="bft-label">Harajat turi <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <select name="harajat_turi_id" id="turi-select"
                                    class="form-select form-select-sm @error('harajat_turi_id') is-invalid @enderror" style="max-width:400px" required>
                                <option value="">— tanlang —</option>
                                @foreach($harajatTurlari as $t)
                                    <option value="{{ $t->id }}"
                                            data-xodim="{{ $t->talab_xodim ? 1 : 0 }}"
                                            data-schetchik="{{ $t->talab_schetchik ? 1 : 0 }}"
                                            data-kod="{{ $t->kategoriya->kod ?? '' }}"
                                            {{ old('harajat_turi_id', $harajat->harajat_turi_id ?? '') == $t->id ? 'selected' : '' }}>
                                        {{ $t->nomi }} ({{ $t->kategoriya->kod ?? '?' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('harajat_turi_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text small mb-0">Pul oqimi moddasi shu tur bo'yicha avtomat aniqlanadi</div>
                        </td>
                    </tr>

                    <tr id="xodim-blok" style="display:none">
                        <td class="bft-label">Kimga (xodim) <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <select name="tegishli_xodim_id" class="form-select form-select-sm @error('tegishli_xodim_id') is-invalid @enderror" style="max-width:320px">
                                <option value="">— tanlang —</option>
                                @foreach($xodimlar as $x)
                                    <option value="{{ $x->id }}" {{ old('tegishli_xodim_id', $harajat->tegishli_xodim_id ?? '') == $x->id ? 'selected' : '' }}>
                                        {{ $x->ism_familiya }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tegishli_xodim_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>

                    <tr id="schetchik-blok" style="display:none">
                        <td class="bft-label">Schyotchik raqami</td>
                        <td class="bft-wide">
                            <input type="text" name="schetchik_raqami" class="form-control form-control-sm @error('schetchik_raqami') is-invalid @enderror"
                                   style="max-width:320px"
                                   value="{{ old('schetchik_raqami', $harajat->schetchik_raqami ?? '') }}" placeholder="Masalan: 0048213, ko'rsatkich 5340">
                            @error('schetchik_raqami')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>

                    <tr>
                        <td class="bft-label">Kassa turi <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <select name="kassa_turi" class="form-select form-select-sm @error('kassa_turi') is-invalid @enderror" style="max-width:220px" required>
                                <option value="">— tanlang —</option>
                                <option value="naqd"     {{ old('kassa_turi', $harajat->kassa_turi ?? '') === 'naqd'     ? 'selected' : '' }}>Naqd</option>
                                <option value="terminal" {{ old('kassa_turi', $harajat->kassa_turi ?? '') === 'terminal' ? 'selected' : '' }}>Terminal</option>
                                <option value="bank"     {{ old('kassa_turi', $harajat->kassa_turi ?? '') === 'bank'     ? 'selected' : '' }}>Bank</option>
                            </select>
                            @error('kassa_turi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>

                    <tr>
                        <td class="bft-label">Summa (so'm) <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <input type="number" name="summa" step="0.01"
                                   class="form-control form-control-sm @error('summa') is-invalid @enderror"
                                   style="max-width:220px"
                                   value="{{ old('summa', $harajat->summa ?? '') }}"
                                   placeholder="Masalan: 150000" required>
                            <div class="form-text small mb-0">Ijobiy son — chiqim, manfiy son — qaytarish/kirim</div>
                            @error('summa')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>

                    <tr>
                        <td class="bft-label">Izoh / Mazmun</td>
                        <td class="bft-wide">
                            <textarea name="mazmuni" class="form-control form-control-sm @error('mazmuni') is-invalid @enderror"
                                      style="max-width:420px" rows="3" placeholder="Harajat haqida qo'shimcha ma'lumot...">{{ old('mazmuni', $harajat->mazmuni ?? '') }}</textarea>
                            @error('mazmuni')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
                <a href="{{ route('harajatlar.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var sel = document.getElementById('turi-select');
    var xodimBlok = document.getElementById('xodim-blok');
    var schetchikBlok = document.getElementById('schetchik-blok');

    function yangila() {
        var opt = sel.options[sel.selectedIndex];
        var xodim = opt && opt.dataset.xodim === '1';
        var schetchik = opt && opt.dataset.schetchik === '1';
        xodimBlok.style.display = xodim ? 'table-row' : 'none';
        schetchikBlok.style.display = schetchik ? 'table-row' : 'none';
        xodimBlok.querySelector('select').required = xodim;
    }

    sel.addEventListener('change', yangila);
    yangila();
})();
</script>
@endpush
@endsection
