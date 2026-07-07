@extends('layouts.app')
@section('title', 'POS hisobotlar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item active">Hisobotlar</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px; white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:6px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; }
.bank-table tfoot td { background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800; padding:7px 8px; }
.bank-wrap { overflow:auto; max-height:calc(100vh - 320px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:10px 14px; }
.filter-bar .form-select, .filter-bar .form-control { background:#fff; border:1px solid #60a5fa; font-size:.82rem; height:34px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2" style="color:#1d4ed8"></i>POS hisobotlar</h5>
    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label class="form-label small mb-1">Dan</label>
            <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
        </div>
        <div>
            <label class="form-label small mb-1">Gacha</label>
            <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <label class="form-label small mb-1">Filial</label>
            <select name="filial_id" class="form-select form-select-sm" style="width:170px">
                <option value="">Barchasi</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label small mb-1">Kassir</label>
            <select name="kassir_id" class="form-select form-select-sm" style="width:170px">
                <option value="">Barchasi</option>
                @foreach($kassirlar as $k)
                <option value="{{ $k->id }}" {{ request('kassir_id') == $k->id ? 'selected' : '' }}>{{ $k->ism_familiya }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">To'lov turi</label>
            <select name="tolov_turi" class="form-select form-select-sm" style="width:140px">
                <option value="">Barchasi</option>
                <option value="naqd" {{ request('tolov_turi')=='naqd'?'selected':'' }}>Naqd</option>
                <option value="plastik" {{ request('tolov_turi')=='plastik'?'selected':'' }}>Plastik</option>
                <option value="aralash" {{ request('tolov_turi')=='aralash'?'selected':'' }}>Aralash</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrlash</button>
        <a href="{{ route('pos.hisobotlar', array_merge(request()->query(), ['format'=>'excel'])) }}" class="btn btn-success btn-sm ms-auto">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i></button>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Sana</th>
                <th>Cheklar soni</th>
                <th>Jami summa</th>
                <th>Naqd</th>
                <th>Karta/terminal</th>
                <th>Chegirma</th>
            </tr>
        </thead>
        <tbody>
            @forelse($qator as $r)
            <tr>
                <td class="tl">{{ \Carbon\Carbon::parse($r->sana)->format('d.m.Y') }}</td>
                <td class="num">{{ $r->soni }}</td>
                <td class="num">{{ number_format($r->jami_summa,0,'.',' ') }}</td>
                <td class="num">{{ number_format($r->naqd,0,'.',' ') }}</td>
                <td class="num">{{ number_format($r->plastik,0,'.',' ') }}</td>
                <td class="num">{{ number_format($r->chegirma,0,'.',' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Ma'lumot topilmadi</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="tl">JAMI</td>
                <td class="num">{{ $jami->soni }}</td>
                <td class="num">{{ number_format($jami->jami_summa,0,'.',' ') }}</td>
                <td class="num">{{ number_format($jami->naqd,0,'.',' ') }}</td>
                <td class="num">{{ number_format($jami->plastik,0,'.',' ') }}</td>
                <td class="num">{{ number_format($jami->chegirma,0,'.',' ') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
