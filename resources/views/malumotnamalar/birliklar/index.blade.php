@extends('layouts.app')
@section('title','Birliklar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item active">Birliklar</li>
@endsection
@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-rulers text-info me-2"></i>O'lchov birliklari</h5>
        <small class="text-muted">Dona, kg, litr, metr va boshqa birliklar</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
        <i class="bi bi-plus-lg me-1"></i>Yangi birlik
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

<div class="collapse mb-3" id="yangiForm">
    <div class="card border-success shadow-sm">
        <div class="card-header bg-success text-white py-2 fw-bold">Yangi birlik qo'shish</div>
        <div class="card-body">
            <form method="POST" action="{{ route('malumotnamalar.birliklar.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
                        <input type="text" name="nomi" class="form-control form-control-sm" placeholder="Dona" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Qisqa nomi</label>
                        <input type="text" name="qisqa_nomi" class="form-control form-control-sm" placeholder="don">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Kod</label>
                        <input type="text" name="kod" class="form-control form-control-sm font-monospace" placeholder="PCS">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tartib</label>
                        <input type="number" name="sort_order" class="form-control form-control-sm" value="100">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-save me-1"></i>Saqlash
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Nomi</th><th>Qisqa nomi</th><th>Kod</th>
                    <th>Tartib</th><th>Holat</th><th class="text-end">Amallar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($birliklar as $b)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $b->nomi }}</td>
                    <td>{{ $b->qisqa_nomi ?? '—' }}</td>
                    <td><code>{{ $b->kod ?? '—' }}</code></td>
                    <td>{{ $b->sort_order }}</td>
                    <td>
                        <span class="badge bg-{{ $b->holat==='faol' ? 'success' : 'secondary' }}">
                            {{ $b->holat==='faol' ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $b->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('malumotnamalar.birliklar.destroy', $b) }}" class="d-inline"
                              onsubmit="return confirm('Birlikni o\'chirish?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                <div class="modal fade" id="editModal{{ $b->id }}" tabindex="-1">
                    <div class="modal-dialog modal-md">
                        <form method="POST" action="{{ route('malumotnamalar.birliklar.update', $b) }}" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h6 class="modal-title fw-bold">Birlikni tahrirlash</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Nomi</label>
                                        <input type="text" name="nomi" class="form-control form-control-sm" value="{{ $b->nomi }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Qisqa nomi</label>
                                        <input type="text" name="qisqa_nomi" class="form-control form-control-sm" value="{{ $b->qisqa_nomi }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Kod</label>
                                        <input type="text" name="kod" class="form-control form-control-sm font-monospace" value="{{ $b->kod }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Holat</label>
                                        <select name="holat" class="form-select form-select-sm">
                                            <option value="faol" {{ $b->holat==='faol' ? 'selected' : '' }}>Faol</option>
                                            <option value="nofaol" {{ $b->holat==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Tartib</label>
                                        <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ $b->sort_order }}">
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
                <tr><td colspan="7" class="text-center text-muted py-4">Birliklar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
