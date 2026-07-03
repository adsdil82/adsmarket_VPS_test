@extends('layouts.app')
@section('title','Harajatlar')
@section('breadcrumb')
<li class="breadcrumb-item active">Harajatlar</li>
@endsection

@push('styles')
<style>
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; height:calc(100vh - 260px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 280px); } }
.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.hj-stat { text-align:center; padding:2px 8px; border-right:1px solid #93c5fd; }
.hj-stat:last-child { border-right:none; }
.hj-stat .lbl { font-size:.62rem; text-transform:uppercase; letter-spacing:.03em; color:#3b5fc0; font-weight:700; }
.hj-stat .val { font-size:.92rem; font-weight:800; color:#1e293b; }
</style>
@endpush

@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
    <i class="bi bi-check-circle me-1"></i>{{ session('muvaffaqiyat') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-wallet2" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Harajatlar</span>
        </div>
        <div class="d-flex align-items-center">
            <div class="hj-stat">
                <div class="lbl">Jami chiqim</div>
                <div class="val text-danger">{{ number_format($jami,0,'.',' ') }}</div>
            </div>
            <div class="hj-stat">
                <div class="lbl">Topilgan</div>
                <div class="val">{{ $harajatlar->total() }} ta</div>
            </div>
            <div class="hj-stat">
                <div class="lbl">Davr</div>
                <div class="val" style="font-size:.72rem">{{ \Carbon\Carbon::parse($danSana)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}</div>
            </div>
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <select name="filial_id" class="form-select" style="width:150px">
                <option value="">Barcha filial</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <input type="date" name="dan_sana" class="form-control" style="width:150px" value="{{ $danSana }}">
        </div>
        <div>
            <input type="date" name="gacha_sana" class="form-control" style="width:150px" value="{{ $gachaSana }}">
        </div>
        <div>
            <select name="turi" class="form-select" style="width:180px">
                <option value="">Barcha turlar</option>
                @foreach($turlari as $t)
                    <option value="{{ $t }}" {{ request('turi')===$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div style="width:180px">
            <input type="text" name="qidiruv" class="form-control"
                   placeholder="Mazmunda qidirish..." value="{{ request('qidiruv') }}">
        </div>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Filtr</button>
            <a href="{{ route('harajatlar.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        </div>
        @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
        <a href="{{ route('harajatlar.create') }}" class="btn btn-warning btn-sm ms-auto fw-bold">
            <i class="bi bi-plus-lg me-1"></i>Yangi harajat
        </a>
        @endif
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl" style="width:40px">#</th>
                <th class="tl">Sana</th>
                <th class="tl">Tur</th>
                <th class="tl">Mazmuni</th>
                @if(Auth::user()->isAdmin())<th class="tl">Filial</th>@endif
                <th class="tl">Xodim</th>
                <th>Summa</th>
                @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())<th style="width:70px"></th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($harajatlar as $h)
            <tr>
                <td class="tl text-muted">{{ $h->id }}</td>
                <td class="tl text-muted">{{ $h->sana->format('d.m.Y') }}</td>
                <td class="tl">
                    @php
                        $tur = $h->turi ?? '';
                        $turRang = str_contains($tur,'Иш хаки') || str_contains($tur,'Иш Хаки') ? '#2563eb'
                              : (str_contains($tur,'Харажат') ? '#f59e0b'
                              : (str_contains($tur,'Дивидент') ? '#06b6d4'
                              : (str_contains($tur,'Инкасса') ? '#22c55e'
                              : (str_contains($tur,'Таъминот') ? '#64748b' : '#1e293b'))));
                    @endphp
                    <span class="badge-modern" style="background:{{ $turRang }};color:#fff">{{ Str::limit($tur, 35) }}</span>
                </td>
                <td class="tl text-muted" style="white-space:normal;max-width:320px">
                    {{ Str::limit($h->mazmuni, 50) }}
                    @if($h->kategoriya)
                        <div class="mt-1">
                            <span class="badge bg-light text-dark border" style="font-size:.62rem">{{ $h->kategoriya->kod }} — {{ $h->kategoriya->nomi }}</span>
                            <span class="badge bg-light text-muted border" style="font-size:.62rem">{{ ucfirst($h->kassa_turi) }}</span>
                        </div>
                    @else
                        <div class="mt-1"><span class="badge bg-light text-danger border" style="font-size:.62rem" title="Pul Oqimlariga ulanmagan">⚠ Pul oqimiga bog'lanmagan</span></div>
                    @endif
                </td>
                @if(Auth::user()->isAdmin())
                <td class="tl"><span class="badge-modern" style="background:#1e293b;color:#fff">{{ $h->filial?->kod }}</span></td>
                @endif
                <td class="tl text-muted">{{ $h->xodim?->ism_familiya }}</td>
                <td class="num fw-bold" style="color:{{ $h->summa < 0 ? '#16a34a' : '#dc2626' }}">
                    {{ $h->summa < 0 ? '+' : '' }}{{ number_format($h->summa,0,'.',' ') }}
                </td>
                @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="{{ route('harajatlar.edit', $h) }}" class="btn btn-sm btn-outline-primary py-0 px-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if(Auth::user()->isAdmin())
                        <form method="POST" action="{{ route('harajatlar.destroy', $h) }}" class="d-inline"
                              onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-1">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Harajatlar topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($harajatlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $harajatlar->firstItem() }}–{{ $harajatlar->lastItem() }} / {{ $harajatlar->total() }} ta</small>
    {{ $harajatlar->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection
