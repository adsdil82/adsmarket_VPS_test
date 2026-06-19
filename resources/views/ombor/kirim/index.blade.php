@extends('layouts.app')
@section('title','Tovar kirim')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar kirim</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Tovar kirim</h5>
    <a href="{{ route('kirim.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Yangi kirim
    </a>
</div>

{{-- Statistika --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Bugungi kirim</div>
            <div class="fs-5 fw-bold text-success">{{ number_format($bugun_jami, 0, '.', ' ') }} so'm</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Oy davomida</div>
            <div class="fs-5 fw-bold text-primary">{{ number_format($oy_jami, 0, '.', ' ') }} so'm</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Jami yozuvlar</div>
            <div class="fs-5 fw-bold">{{ $kirimlar->total() }} ta</div>
        </div>
    </div>
</div>

{{-- Filter --}}
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
                <input type="date" name="sana" class="form-control form-control-sm" value="{{ request('sana') }}">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('kirim.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
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
                    <th>#</th>
                    <th>Sana</th>
                    <th>Yetkazuvchi</th>
                    <th>Hujjat #</th>
                    <th>Xodim</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th class="text-end">Summa</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($kirimlar as $k)
                <tr>
                    <td class="text-muted small">{{ $k->id }}</td>
                    <td>{{ $k->sana->format('d.m.Y') }}</td>
                    <td>{{ $k->yetkazuvchi ?? '—' }}</td>
                    <td class="font-monospace small">{{ $k->hujjat_raqam ?? '—' }}</td>
                    <td class="text-muted small">{{ $k->xodim?->ism_familiya }}</td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary">{{ $k->filial?->kod }}</span></td>
                    @endif
                    <td class="text-end fw-bold text-success">{{ number_format($k->umumiy_summa,0,'.',' ') }}</td>
                    <td class="text-center">
                        @php $ranglar = ['tasdiqlangan'=>'success','qoralama'=>'warning','bekor'=>'danger']; @endphp
                        <span class="badge bg-{{ $ranglar[$k->holat] ?? 'secondary' }}">{{ $k->holat }}</span>
                    </td>
                    <td>
                        <a href="{{ route('kirim.show',$k) }}" class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($k->holat==='tasdiqlangan')
                        <form method="POST" action="{{ route('kirim.destroy',$k) }}" class="d-inline"
                              onsubmit="return confirm('Kirimni bekor qilish? Ombor qoldig\'i teskari yangilanadi!')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-x-circle"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Kirimlar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kirimlar->hasPages())
    <div class="card-footer">{{ $kirimlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
