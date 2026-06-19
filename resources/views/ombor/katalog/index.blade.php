@extends('layouts.app')
@section('title','Tovar katalogi')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar katalogi</li>
@endsection

@section('content')

{{-- Statistika --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-4 fw-bold text-primary">{{ $tovarlar->total() }}</div>
            <div class="text-muted small">Jami tovarlar</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-4 fw-bold text-success">
                {{ \App\Models\TovarKatalog::where('qoldiq','>',0)->count() }}
            </div>
            <div class="text-muted small">Omborda bor</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-4 fw-bold text-danger">
                {{ \App\Models\TovarKatalog::whereColumn('qoldiq','<=','min_qoldiq')->where('min_qoldiq','>',0)->count() }}
            </div>
            <div class="text-muted small">Kam qoldiq</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-4 fw-bold text-warning">{{ $guruhlar->count() }}</div>
            <div class="text-muted small">Guruhlar</div>
        </div>
    </div>
</div>

{{-- Filter + tugmalar --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="search" name="qidiruv" class="form-control form-control-sm"
                       placeholder="Nomi yoki shtrix-kod..." value="{{ request('qidiruv') }}">
            </div>
            <div class="col-sm-3">
                <select name="guruh_id" class="form-select form-select-sm">
                    <option value="">Barcha guruhlar</option>
                    @foreach($guruhlar as $g)
                        <option value="{{ $g->id }}" {{ request('guruh_id')==$g->id?'selected':'' }}>{{ $g->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="holat" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    <option value="faol" {{ request('holat')==='faol'?'selected':'' }}>Faol</option>
                    <option value="nofaol" {{ request('holat')==='nofaol'?'selected':'' }}>Nofaol</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Qidirish
                </button>
                <a href="{{ route('katalog.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i>
                </a>
            </div>
            <div class="col-sm-auto ms-auto">
                <a href="{{ route('katalog.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg me-1"></i>Yangi tovar
                </a>
                <a href="{{ route('tovar-guruhlar.index') }}" class="btn btn-sm btn-outline-primary ms-1">
                    <i class="bi bi-tags me-1"></i>Guruhlar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Jadval --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tovar nomi</th>
                    <th>Guruh</th>
                    <th>Shtrix-kod</th>
                    <th class="text-end">Tan narx</th>
                    <th class="text-end">Sotish narx</th>
                    <th class="text-center">Qoldiq</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($tovarlar as $t)
                <tr class="{{ $t->kam_qoldiq ? 'table-warning' : '' }}">
                    <td>
                        <div class="fw-medium">{{ $t->nomi }}</div>
                        <small class="text-muted">{{ $t->birlik }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $t->guruh?->nomi ?? '—' }}</span></td>
                    <td class="text-muted small font-monospace">{{ $t->barkod ?? '—' }}</td>
                    <td class="text-end text-muted small">{{ number_format($t->tan_narx,0,'.',' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format($t->sotish_narx,0,'.',' ') }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $t->qoldiq>0?'success':'danger' }} fs-6">
                            {{ number_format($t->qoldiq,0,'.',' ') }}
                        </span>
                        @if($t->kam_qoldiq)
                            <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Kam qoldiq!"></i>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $t->holat==='faol'?'success':'secondary' }}">{{ $t->holat }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('katalog.edit', $t) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('katalog.destroy',$t) }}" class="d-inline"
                              onsubmit="return confirm('«{{$t->nomi}}» o\'chirilsinmi?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0" {{ $t->qoldiq>0?'disabled':'' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-box fs-3 d-block mb-2 opacity-25"></i>Tovarlar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tovarlar->hasPages())
    <div class="card-footer d-flex justify-content-between">
        <small class="text-muted">{{ $tovarlar->firstItem() }}–{{ $tovarlar->lastItem() }} / {{ $tovarlar->total() }}</small>
        {{ $tovarlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
