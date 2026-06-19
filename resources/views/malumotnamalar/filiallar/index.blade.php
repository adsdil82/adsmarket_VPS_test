@extends('layouts.app')
@section('title','Filiallar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item active">Filiallar</li>
@endsection
@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-building text-primary me-2"></i>Filiallar</h5>
        <small class="text-muted">Tizim filiallari ro'yxati</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
        <i class="bi bi-plus-lg me-1"></i>Yangi filial
    </button>
</div>

@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('xato'))
<div class="alert alert-danger alert-dismissible fade show py-2">{{ session('xato') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
<div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

{{-- Qo'shish formasi --}}
<div class="collapse mb-3" id="yangiForm">
    <div class="card border-success shadow-sm">
        <div class="card-header bg-success text-white py-2 fw-bold">Yangi filial qo'shish</div>
        <div class="card-body">
            <form method="POST" action="{{ route('malumotnamalar.filiallar.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
                        <input type="text" name="nomi" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Kod <span class="text-danger">*</span></label>
                        <input type="text" name="kod" class="form-control form-control-sm text-uppercase" required placeholder="F01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Manzil</label>
                        <input type="text" name="manzil" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Telefon</label>
                        <input type="text" name="telefon" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-save"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Jadval --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
            <thead class="table-light">
                <tr>
                    <th style="width:50px">#</th>
                    <th>Nomi</th>
                    <th>Kod</th>
                    <th>Manzil</th>
                    <th>Telefon</th>
                    <th>Foydalanuvchilar</th>
                    <th>Holat</th>
                    <th class="text-end">Amallar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filiallar as $f)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $f->nomi }}</td>
                    <td><code>{{ $f->kod }}</code></td>
                    <td class="text-muted small">{{ $f->manzil ?? '—' }}</td>
                    <td class="text-muted small">{{ $f->telefon ?? '—' }}</td>
                    <td><span class="badge bg-primary">{{ $f->foydalanuvchilar_count }}</span></td>
                    <td>
                        <span class="badge bg-{{ $f->holat === 'faol' ? 'success' : 'secondary' }}">
                            {{ $f->holat === 'faol' ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $f->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('malumotnamalar.filiallar.destroy', $f) }}" class="d-inline"
                              onsubmit="return confirm('Filialni o\'chirish?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                {{-- Edit modal --}}
                <div class="modal fade" id="editModal{{ $f->id }}" tabindex="-1">
                    <div class="modal-dialog modal-md">
                        <form method="POST" action="{{ route('malumotnamalar.filiallar.update', $f) }}" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h6 class="modal-title fw-bold">Filialni tahrirlash</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-8">
                                        <label class="form-label small fw-bold">Nomi</label>
                                        <input type="text" name="nomi" class="form-control form-control-sm" value="{{ $f->nomi }}" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Kod</label>
                                        <input type="text" name="kod" class="form-control form-control-sm text-uppercase" value="{{ $f->kod }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Manzil</label>
                                        <input type="text" name="manzil" class="form-control form-control-sm" value="{{ $f->manzil }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Telefon</label>
                                        <input type="text" name="telefon" class="form-control form-control-sm" value="{{ $f->telefon }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Holat</label>
                                        <select name="holat" class="form-select form-select-sm">
                                            <option value="faol" {{ $f->holat==='faol' ? 'selected' : '' }}>Faol</option>
                                            <option value="nofaol" {{ $f->holat==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                                        </select>
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
                <tr><td colspan="8" class="text-center text-muted py-4">Filiallar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
