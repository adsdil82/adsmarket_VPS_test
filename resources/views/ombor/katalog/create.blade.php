@extends('layouts.app')
@section('title','Yangi tovar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('katalog.index') }}">Tovar katalogi</a></li>
<li class="breadcrumb-item active">Yangi tovar</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Yangi tovar qo'shish</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('katalog.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Tovar nomi <span class="text-danger">*</span></label>
                    <input type="text" name="nomi" class="form-control @error('nomi') is-invalid @enderror"
                           value="{{ old('nomi') }}" placeholder="Tovar nomini kiriting" required>
                    @error('nomi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Guruh</label>
                    <select name="guruh_id" class="form-select">
                        <option value="">— Guruh tanlang —</option>
                        @foreach($guruhlar as $g)
                            <option value="{{ $g->id }}" {{ old('guruh_id')==$g->id?'selected':'' }}>{{ $g->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Shtrix-kod</label>
                    <div class="input-group">
                        <input type="text" name="barkod" class="form-control @error('barkod') is-invalid @enderror"
                               value="{{ old('barkod') }}" placeholder="1234567890123" id="barkod-input">
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('barkod-input').value = Date.now().toString().slice(-13)">
                            <i class="bi bi-upc-scan"></i>
                        </button>
                    </div>
                    @error('barkod')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">O'lchov birligi <span class="text-danger">*</span></label>
                    <select name="birlik" class="form-select">
                        @foreach(['dona','kg','litr','metr','m²','m³','quti','juft','to\'plam'] as $b)
                            <option value="{{ $b }}" {{ old('birlik','dona')===$b?'selected':'' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Tan narx (so'm) <span class="text-danger">*</span></label>
                    <input type="number" name="tan_narx" class="form-control @error('tan_narx') is-invalid @enderror"
                           value="{{ old('tan_narx', 0) }}" min="0" step="100" required>
                    @error('tan_narx')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Naqd/POS narxi (so'm) <span class="text-danger">*</span></label>
                    <input type="number" name="sotish_narx" class="form-control @error('sotish_narx') is-invalid @enderror"
                           value="{{ old('sotish_narx', 0) }}" min="0" step="100" required>
                    @error('sotish_narx')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Nasiya narxi (so'm) <span class="text-danger">*</span></label>
                    <input type="number" name="nasiya_narx" class="form-control @error('nasiya_narx') is-invalid @enderror"
                           value="{{ old('nasiya_narx', 0) }}" min="0" step="100" required>
                    @error('nasiya_narx')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Bo'lib-bo'lib to'lashda ishlatiladigan narx (odatda naqd narxdan yuqori)</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Minimal qoldiq (ogohlantirish uchun)</label>
                    <input type="number" name="min_qoldiq" class="form-control"
                           value="{{ old('min_qoldiq', 0) }}" min="0" step="1">
                    <div class="form-text">Qoldiq shu raqamdan kam bo'lsa ogohlantirish ko'rinadi</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Holat</label>
                    <select name="holat" class="form-select">
                        <option value="faol" {{ old('holat','faol')==='faol'?'selected':'' }}>Faol</option>
                        <option value="nofaol" {{ old('holat')==='nofaol'?'selected':'' }}>Nofaol</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Izoh</label>
                    <textarea name="izoh" class="form-control" rows="2">{{ old('izoh') }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Saqlash
                    </button>
                    <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
