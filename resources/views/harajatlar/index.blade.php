@extends('layouts.app')
@section('title','Harajatlar')
@section('breadcrumb')
<li class="breadcrumb-item active">Harajatlar</li>
@endsection

@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
    <i class="bi bi-check-circle me-1"></i>{{ session('muvaffaqiyat') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-danger"></i>Harajatlar</h5>
    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
    <a href="{{ route('harajatlar.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Yangi harajat
    </a>
    @endif
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(Auth::user()->isAdmin())
            <div class="col-sm-2">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filial</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-2">
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-2">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            <div class="col-sm-3">
                <select name="turi" class="form-select form-select-sm">
                    <option value="">Barcha turlar</option>
                    @foreach($turlari as $t)
                        <option value="{{ $t }}" {{ request('turi')===$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="text" name="qidiruv" class="form-control form-control-sm"
                       placeholder="Mazmunda qidirish..." value="{{ request('qidiruv') }}">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('harajatlar.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row mb-3 g-2">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-2">
            <div class="text-muted small">Tanlangan davr jami chiqim</div>
            <div class="fw-bold fs-5 text-danger">{{ number_format($jami,0,'.',' ') }} so'm</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-2">
            <div class="text-muted small">Topilgan yozuvlar</div>
            <div class="fw-bold fs-5">{{ $harajatlar->total() }} ta</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-2">
            <div class="text-muted small">Davr</div>
            <div class="fw-bold small">{{ \Carbon\Carbon::parse($danSana)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sana</th>
                    <th>Tur</th>
                    <th>Mazmuni</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th>Xodim</th>
                    <th class="text-end">Summa</th>
                    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())<th></th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($harajatlar as $h)
                <tr>
                    <td class="text-muted small">{{ $h->id }}</td>
                    <td class="text-nowrap small">{{ $h->sana->format('d.m.Y') }}</td>
                    <td>
                        @php
                            $tur = $h->turi ?? '';
                            $rang = str_contains($tur,'Иш хаки') || str_contains($tur,'Иш Хаки') ? 'primary'
                                  : (str_contains($tur,'Харажат') ? 'warning'
                                  : (str_contains($tur,'Дивидент') ? 'info'
                                  : (str_contains($tur,'Инкасса') ? 'success'
                                  : (str_contains($tur,'Таъминот') ? 'secondary' : 'dark'))));
                        @endphp
                        <span class="badge bg-{{ $rang }} bg-opacity-75 text-dark" style="font-size:.72rem">
                            {{ Str::limit($tur, 35) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ Str::limit($h->mazmuni, 50) }}</td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary bg-opacity-50 text-dark small">{{ $h->filial?->kod }}</span></td>
                    @endif
                    <td class="text-muted small">{{ $h->xodim?->ism_familiya }}</td>
                    <td class="text-end fw-bold {{ $h->summa < 0 ? 'text-success' : 'text-danger' }} text-nowrap">
                        {{ $h->summa < 0 ? '+' : '' }}{{ number_format($h->summa,0,'.',' ') }}
                    </td>
                    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
                    <td class="text-nowrap">
                        <a href="{{ route('harajatlar.edit', $h) }}" class="btn btn-outline-secondary py-0 px-1" style="font-size:.75rem">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if(Auth::user()->isAdmin())
                        <form method="POST" action="{{ route('harajatlar.destroy', $h) }}" class="d-inline"
                              onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger py-0 px-1" style="font-size:.75rem">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Harajatlar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($harajatlar->hasPages())
    <div class="card-footer">{{ $harajatlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
