@extends('layouts.app')
@section('title','Tovar chiqim')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar chiqim</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-up me-2 text-danger"></i>Tovar chiqim</h5>
    <a href="{{ route('chiqim.create') }}" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Yangi chiqim
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-3">
                <select name="sabab" class="form-select form-select-sm">
                    <option value="">Barcha sabablar</option>
                    @foreach($sabablar as $key => $nom)
                        <option value="{{ $key }}" {{ request('sabab')===$key?'selected':'' }}>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('chiqim.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
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
                    <th>Sabab</th>
                    <th>Xodim</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th class="text-end">Summa</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($chiqimlar as $c)
                <tr>
                    <td class="text-muted small">{{ $c->id }}</td>
                    <td>{{ $c->sana->format('d.m.Y') }}</td>
                    <td>
                        @php $sababRanglar = ['nasiya_sotish'=>'primary','naqd_sotish'=>'success','qaytarish'=>'warning','hisobdan_chiqish'=>'danger','boshqa'=>'secondary']; @endphp
                        <span class="badge bg-{{ $sababRanglar[$c->sabab]??'secondary' }}">
                            {{ $sabablar[$c->sabab] ?? $c->sabab }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $c->xodim?->ism_familiya }}</td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary">{{ $c->filial?->kod }}</span></td>
                    @endif
                    <td class="text-end fw-bold text-danger">{{ number_format($c->umumiy_summa,0,'.',' ') }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $c->holat==='tasdiqlangan'?'success':'danger' }}">{{ $c->holat }}</span>
                    </td>
                    <td>
                        <a href="{{ route('chiqim.show',$c) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Chiqimlar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($chiqimlar->hasPages())
    <div class="card-footer">{{ $chiqimlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
