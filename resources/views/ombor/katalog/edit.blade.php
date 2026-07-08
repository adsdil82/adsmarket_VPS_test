@extends('layouts.app')
@section('title','Tovarni tahrirlash')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('katalog.index') }}">Tovar katalogi</a></li>
<li class="breadcrumb-item active">{{ $katalog->nomi }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-warning"></i>Tovarni tahrirlash</h6>
        <div>
            <span class="badge bg-{{ $katalog->qoldiq>0?'success':'danger' }} me-1">
                Qoldiq: {{ $katalog->qoldiq }} {{ $katalog->birlik }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('katalog.update', $katalog) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Tovar nomi <span class="text-danger">*</span></label>
                    <input type="text" name="nomi" class="form-control @error('nomi') is-invalid @enderror"
                           value="{{ old('nomi', $katalog->nomi) }}" required>
                    @error('nomi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Guruh</label>
                    <select name="guruh_id" class="form-select">
                        <option value="">— Guruhsiz —</option>
                        @foreach($guruhlar as $g)
                            <option value="{{ $g->id }}" {{ old('guruh_id',$katalog->guruh_id)==$g->id?'selected':'' }}>{{ $g->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Shtrix-kod (asosiy)</label>
                    <input type="text" name="barkod" class="form-control"
                           value="{{ old('barkod', $katalog->barkod) }}">
                    @error('barkod')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Qo'shimcha shtrix-kodlar</label>
                    <div id="qoshimcha-barkodlar-list">
                        @foreach($katalog->barkodlar as $b)
                        <div class="input-group mb-2">
                            <input type="text" name="qoshimcha_barkodlar[]" class="form-control" value="{{ $b->barkod }}">
                            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()"><i class="bi bi-trash"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="qoshimchaBarkodQosh()">
                        <i class="bi bi-plus-lg me-1"></i>Yana bitta barkod
                    </button>
                    @error('qoshimcha_barkodlar.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">O'lchov birligi <span class="text-danger">*</span></label>
                    <select name="birlik" class="form-select">
                        @foreach(['dona','kg','litr','metr','m²','m³','quti','juft','to\'plam'] as $b)
                            <option value="{{ $b }}" {{ old('birlik',$katalog->birlik)===$b?'selected':'' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Tan narx</label>
                    <input type="number" name="tan_narx" class="form-control"
                           value="{{ old('tan_narx', $katalog->tan_narx) }}" min="0" step="100">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Naqd/POS narxi <span class="text-danger">*</span></label>
                    <input type="number" name="sotish_narx" class="form-control"
                           value="{{ old('sotish_narx', $katalog->sotish_narx) }}" min="0" step="100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-medium">Nasiya narxi <span class="text-danger">*</span></label>
                    <input type="number" name="nasiya_narx" class="form-control"
                           value="{{ old('nasiya_narx', $katalog->nasiya_narx) }}" min="0" step="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Minimal qoldiq</label>
                    <input type="number" name="min_qoldiq" class="form-control"
                           value="{{ old('min_qoldiq', $katalog->min_qoldiq) }}" min="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Holat</label>
                    <select name="holat" class="form-select">
                        <option value="faol" {{ old('holat',$katalog->holat)==='faol'?'selected':'' }}>Faol</option>
                        <option value="nofaol" {{ old('holat',$katalog->holat)==='nofaol'?'selected':'' }}>Nofaol</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Izoh</label>
                    <textarea name="izoh" class="form-control" rows="2">{{ old('izoh', $katalog->izoh) }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Yangilash
                    </button>
                    <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>

@push('scripts')
<script>
function qoshimchaBarkodQosh() {
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `<input type="text" name="qoshimcha_barkodlar[]" class="form-control">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.input-group').remove()"><i class="bi bi-trash"></i></button>`;
    document.getElementById('qoshimcha-barkodlar-list').appendChild(div);
}
</script>
@endpush
@endsection
