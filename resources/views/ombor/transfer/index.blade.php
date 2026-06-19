@extends('layouts.app')
@section('title','Filiallar arasi transfer')
@section('breadcrumb')
<li class="breadcrumb-item active">Transfer</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2 text-info"></i>Filiallar arasi tovar transfer</h5>
    <a href="{{ route('transfer.create') }}" class="btn btn-info text-white">
        <i class="bi bi-plus-lg me-1"></i>Yangi transfer
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-3">
                <select name="holat" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    <option value="kutilmoqda" {{ request('holat')==='kutilmoqda'?'selected':'' }}>Kutilmoqda</option>
                    <option value="tasdiqlangan" {{ request('holat')==='tasdiqlangan'?'selected':'' }}>Tasdiqlangan</option>
                    <option value="bekor" {{ request('holat')==='bekor'?'selected':'' }}>Bekor</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('transfer.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sana</th>
                    <th>Jo'natuvchi</th>
                    <th>Qabul qiluvchi</th>
                    <th>Xodim</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transferlar as $t)
                <tr>
                    <td class="text-muted small">{{ $t->id }}</td>
                    <td>{{ $t->sana->format('d.m.Y') }}</td>
                    <td>
                        <span class="badge bg-danger">{{ $t->fromFilial?->nomi }}</span>
                    </td>
                    <td>
                        <span class="badge bg-success">{{ $t->toFilial?->nomi }}</span>
                    </td>
                    <td class="text-muted small">{{ $t->xodim?->ism_familiya }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $t->holat_rangi }}">{{ $t->holat }}</span>
                    </td>
                    <td>
                        <a href="{{ route('transfer.show',$t) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($t->holat === 'kutilmoqda')
                        @if(Auth::user()->isAdmin() || Auth::user()->filial_id === $t->to_filial_id)
                        <form method="POST" action="{{ route('transfer.tasdiqla',$t) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success py-0" title="Qabul qilish">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        @endif
                        @if(Auth::user()->isAdmin() || Auth::user()->filial_id === $t->from_filial_id)
                        <form method="POST" action="{{ route('transfer.bekor',$t) }}" class="d-inline"
                              onsubmit="return confirm('Bekor qilinsinmi?')">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger py-0" title="Bekor qilish">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                        @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Transferlar yo'q
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transferlar->hasPages())
    <div class="card-footer">{{ $transferlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
