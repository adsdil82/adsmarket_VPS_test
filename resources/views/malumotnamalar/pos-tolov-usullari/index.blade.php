@extends('layouts.app')
@section('title','POS to\'lov usullari')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item active">POS to'lov usullari</li>
@endsection
@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front text-success me-2"></i>POS to'lov usullari</h5>
        <small class="text-muted">Har bir filial uchun kassada tanlanadigan terminal/onlayn to'lov usullari (Naqd va Aralash har doim mavjud, bu yerga kiritilmaydi)</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
        <i class="bi bi-plus-lg me-1"></i>Yangi to'lov usuli
    </button>
</div>

@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('xato'))
<div class="alert alert-danger alert-dismissible fade show py-2">{{ session('xato') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="collapse mb-3" id="yangiForm">
    <div class="card border-success shadow-sm">
        <div class="card-header bg-success text-white py-2 fw-bold">Yangi to'lov usuli qo'shish</div>
        <div class="card-body">
            <form method="POST" action="{{ route('malumotnamalar.pos-tolov-usullari.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Filial <span class="text-danger">*</span></label>
                        <select name="filial_id" class="form-select form-select-sm" required>
                            <option value="">— tanlang —</option>
                            @foreach($filiallar as $filial)
                            <option value="{{ $filial->id }}">{{ $filial->nomi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
                        <input type="text" name="nomi" class="form-control form-control-sm" placeholder="Terminal — Humo" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Turi <span class="text-danger">*</span></label>
                        <select name="turi" class="form-select form-select-sm" required>
                            <option value="terminal">Terminal</option>
                            <option value="onlayn">Onlayn</option>
                            <option value="boshqa">Boshqa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tartib</label>
                        <input type="number" name="tartib" class="form-control form-control-sm" value="0" min="0">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-save"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@php $turRanglari = ['terminal'=>'warning','onlayn'=>'info','boshqa'=>'secondary']; @endphp

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Filial</th><th>Nomi</th><th>Turi</th>
                    <th class="text-end">Tartib</th><th>Holat</th><th class="text-end">Amallar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usullar as $u)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="small">{{ $u->filial?->nomi ?? '—' }}</td>
                    <td class="fw-semibold">{{ $u->nomi }}</td>
                    <td><span class="badge bg-{{ $turRanglari[$u->turi] ?? 'secondary' }}">{{ ucfirst($u->turi) }}</span></td>
                    <td class="text-end small text-muted">{{ $u->tartib }}</td>
                    <td>
                        <span class="badge bg-{{ $u->holat==='faol' ? 'success' : 'secondary' }}">
                            {{ $u->holat==='faol' ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $u->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('malumotnamalar.pos-tolov-usullari.destroy', $u) }}" class="d-inline"
                              onsubmit="return confirm('To\'lov usulini o\'chirish?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                <div class="modal fade" id="editModal{{ $u->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('malumotnamalar.pos-tolov-usullari.update', $u) }}" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h6 class="modal-title fw-bold">To'lov usulini tahrirlash</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Filial</label>
                                        <select name="filial_id" class="form-select form-select-sm" required>
                                            @foreach($filiallar as $filial)
                                            <option value="{{ $filial->id }}" {{ $u->filial_id==$filial->id ? 'selected' : '' }}>{{ $filial->nomi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label small fw-bold">Nomi</label>
                                        <input type="text" name="nomi" class="form-control form-control-sm" value="{{ $u->nomi }}" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Turi</label>
                                        <select name="turi" class="form-select form-select-sm">
                                            @foreach(['terminal','onlayn','boshqa'] as $t)
                                            <option value="{{ $t }}" {{ $u->turi===$t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Tartib</label>
                                        <input type="number" name="tartib" class="form-control form-control-sm" value="{{ $u->tartib }}" min="0">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Holat</label>
                                        <select name="holat" class="form-select form-select-sm">
                                            <option value="faol" {{ $u->holat==='faol' ? 'selected' : '' }}>Faol</option>
                                            <option value="nofaol" {{ $u->holat==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Izoh</label>
                                        <input type="text" name="izoh" class="form-control form-control-sm" value="{{ $u->izoh }}">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                                <button type="submit" class="btn btn-primary btn-sm">Saqlash</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">To'lov usullari yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
