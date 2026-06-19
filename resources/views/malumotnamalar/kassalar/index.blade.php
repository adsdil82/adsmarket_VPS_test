@extends('layouts.app')
@section('title','Kassalar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item active">Kassalar</li>
@endsection
@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Kassalar</h5>
        <small class="text-muted">Kassa va bank hisobraqamlari boshqaruvi</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
        <i class="bi bi-plus-lg me-1"></i>Yangi kassa
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
        <div class="card-header bg-success text-white py-2 fw-bold">Yangi kassa qo'shish</div>
        <div class="card-body">
            <form method="POST" action="{{ route('malumotnamalar.kassalar.store') }}">
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
                        <label class="form-label small fw-bold">Kassa nomi <span class="text-danger">*</span></label>
                        <input type="text" name="nomi" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Tur <span class="text-danger">*</span></label>
                        <select name="tur" class="form-select form-select-sm" required>
                            <option value="naqd">Naqd</option>
                            <option value="bank">Bank</option>
                            <option value="terminal">Terminal</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Valyuta</label>
                        <select name="valyuta" class="form-select form-select-sm">
                            <option value="UZS" selected>UZS</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-save"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@php $turRanglari = ['naqd'=>'success','bank'=>'primary','terminal'=>'warning','online'=>'info']; @endphp

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Filial</th><th>Kassa nomi</th><th>Tur</th>
                    <th class="text-end">Qoldiq</th><th>Valyuta</th><th>Holat</th><th class="text-end">Amallar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kassalar as $k)
                @php $filialNomi = $k->filial?->nomi ?? '—'; @endphp
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="small">{{ $filialNomi }}</td>
                    <td class="fw-semibold">{{ $k->nomi }}</td>
                    <td><span class="badge bg-{{ $turRanglari[$k->tur] ?? 'secondary' }}">{{ ucfirst($k->tur) }}</span></td>
                    <td class="text-end fw-bold {{ $k->qoldiq > 0 ? 'text-success' : '' }}">
                        {{ number_format($k->qoldiq, 0, '.', ' ') }}
                    </td>
                    <td class="small text-muted">{{ $k->valyuta }}</td>
                    <td>
                        <span class="badge bg-{{ $k->holat==='faol' ? 'success' : 'secondary' }}">
                            {{ $k->holat==='faol' ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $k->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('malumotnamalar.kassalar.destroy', $k) }}" class="d-inline"
                              onsubmit="return confirm('Kassani o\'chirish? Faqat qoldig\'i 0 bo\'lsa o\'chiriladi.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>

                <div class="modal fade" id="editModal{{ $k->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('malumotnamalar.kassalar.update', $k) }}" class="modal-content">
                            @csrf @method('PUT')
                            <div class="modal-header">
                                <h6 class="modal-title fw-bold">Kassani tahrirlash</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Filial</label>
                                        <select name="filial_id" class="form-select form-select-sm" required>
                                            @foreach($filiallar as $filial)
                                            <option value="{{ $filial->id }}" {{ $k->filial_id==$filial->id ? 'selected' : '' }}>{{ $filial->nomi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label small fw-bold">Nomi</label>
                                        <input type="text" name="nomi" class="form-control form-control-sm" value="{{ $k->nomi }}" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Tur</label>
                                        <select name="tur" class="form-select form-select-sm">
                                            @foreach(['naqd','bank','terminal','online'] as $t)
                                            <option value="{{ $t }}" {{ $k->tur===$t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Valyuta</label>
                                        <select name="valyuta" class="form-select form-select-sm">
                                            <option value="UZS" {{ $k->valyuta==='UZS' ? 'selected' : '' }}>UZS</option>
                                            <option value="USD" {{ $k->valyuta==='USD' ? 'selected' : '' }}>USD</option>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Holat</label>
                                        <select name="holat" class="form-select form-select-sm">
                                            <option value="faol" {{ $k->holat==='faol' ? 'selected' : '' }}>Faol</option>
                                            <option value="nofaol" {{ $k->holat==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Izoh</label>
                                        <input type="text" name="izoh" class="form-control form-control-sm" value="{{ $k->izoh }}">
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
                <tr><td colspan="8" class="text-center text-muted py-4">Kassalar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
