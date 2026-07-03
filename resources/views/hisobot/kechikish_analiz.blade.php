@extends('layouts.app')
@section('title', 'Kechikish analizi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Kechikish analizi</li>
@endsection

@push('styles')
<style>
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:8px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#1e3a8a,#1d4ed8);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.ag-stat { border:1px solid #93c5fd; border-radius:8px; overflow:hidden; background:#fff; }
.ag-stat-head { color:#fff; font-weight:700; font-size:.66rem; letter-spacing:.03em; text-transform:uppercase; text-align:center; padding:5px; }
.ag-stat-body { text-align:center; padding:8px 6px; }
.ag-stat-val { font-size:1rem; font-weight:800; }
.bucket-bar { height: 4px; border-radius: 2px; margin-top: 6px; background:#eee; }

.bank-table { border-collapse:collapse; font-size:.78rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.64rem; letter-spacing:.02em; text-transform:uppercase; padding:6px 6px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 6px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-table tbody td.d30-col  { background: rgba(255,193,7,.1) !important; }
.bank-table tbody td.d60-col  { background: rgba(255,152,0,.12) !important; }
.bank-table tbody td.d90-col  { background: rgba(255,87,34,.14) !important; }
.bank-table tbody td.d120-col { background: rgba(244,67,54,.15) !important; }
.bank-table tbody td.d150-col { background: rgba(211,47,47,.16) !important; }
.bank-table tbody td.d180-col { background: rgba(183,28,28,.17) !important; }
.bank-table tbody td.d180p-col{ background: rgba(100,0,0,.17) !important; }
.bank-table tbody td.jami-kech{ background: rgba(183,28,28,.12) !important; font-weight:800 !important; }
.bank-table thead th.d30-col, .bank-table thead th.d60-col, .bank-table thead th.d90-col,
.bank-table thead th.d120-col, .bank-table thead th.d150-col, .bank-table thead th.d180-col,
.bank-table thead th.d180p-col, .bank-table thead th.jami-kech {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8) !important; color:#fff !important;
}
.bank-table tfoot { position:sticky; bottom:0; z-index:6; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800;
    padding:6px 6px; border-top:2px solid #60a5fa;
}
.bank-table tfoot td.jami-kech { color:#fde68a !important; }
.bank-wrap { overflow:auto; height:calc(100vh - 360px); min-height:260px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

<div class="filter-bar mb-3">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-clock-history" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Kechikish analizi (Aging)</span>
        </div>
        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Hisobot sanasi</label>
            <input type="date" name="sana" class="form-control" style="width:170px" value="{{ $sana }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Filial</label>
            <select name="filial_id" class="form-select" style="width:180px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="btn btn-sm btn-danger px-3" style="height:32px">
            <i class="bi bi-funnel me-1"></i>Hisoblash
        </button>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('hisobotlar.excel','aging') }}?sana={{ $sana }}&filial_id={{ $filialId }}"
               class="btn btn-sm btn-success px-3" style="height:32px">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel
            </a>
            <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-outline-secondary" style="height:32px">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </form>
</div>

{{-- Jami satrlar --}}
<div class="row g-2 mb-3">
    @php
    $buckets = [
        ['d30','1-30 kun','#ffc107'],
        ['d60','31-60 kun','#ff9800'],
        ['d90','61-90 kun','#f44336'],
        ['d120','91-120 kun','#e53935'],
        ['d150','121-150 kun','#c62828'],
        ['d180','151-180 kun','#b71c1c'],
        ['d180p','180+ kun','#6d0000'],
    ];
    $jamiAll = max(1, $jami['jami']);
    @endphp

    @foreach($buckets as [$key,$label,$hex])
    <div class="col-6 col-sm-3 col-lg">
        <div class="ag-stat">
            <div class="ag-stat-head" style="background:{{ $hex }}">{{ $label }}</div>
            <div class="ag-stat-body">
                <div class="ag-stat-val" style="color:{{ $hex }}">{{ number_format($jami[$key]/1000000,1) }} mln</div>
                <div class="bucket-bar"><div style="height:100%;border-radius:2px;background:{{ $hex }};width:{{ min(100,$jami[$key]/$jamiAll*100) }}%"></div></div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="col-6 col-sm-3 col-lg">
        <div class="ag-stat" style="border-color:#dc2626">
            <div class="ag-stat-head" style="background:linear-gradient(90deg,#1e3a8a,#1d4ed8)">JAMI</div>
            <div class="ag-stat-body">
                <div class="ag-stat-val text-danger">{{ number_format($jami['jami']/1000000,1) }} mln</div>
                <div class="text-muted" style="font-size:.68rem">{{ $jami['soni'] }} ta kredit</div>
            </div>
        </div>
    </div>
</div>

{{-- Aging jadval --}}
<div class="bft-section-title">
    <span><i class="bi bi-table me-1"></i>Kechikkan kreditlar — {{ \Carbon\Carbon::parse($sana)->format('d.m.Y') }} sanasiga</span>
    <span class="badge bg-danger">{{ $jami['soni'] }} ta</span>
</div>
<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl" style="width:36px">#</th>
                <th class="tl">Familiya</th>
                <th class="tl">Shartnoma</th>
                @if(Auth::user()->isAdmin())
                <th class="tl">Filial</th>
                @endif
                <th>Kredit</th>
                <th>Qoldiq</th>
                <th class="d30-col">1-30</th>
                <th class="d60-col">31-60</th>
                <th class="d90-col">61-90</th>
                <th class="d120-col">91-120</th>
                <th class="d150-col">121-150</th>
                <th class="d180-col">151-180</th>
                <th class="d180p-col">180+</th>
                <th class="jami-kech">Jami kechikkan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $r)
            <tr>
                <td class="tl text-muted">{{ $i+1 }}</td>
                <td class="tl">
                    <div class="fw-semibold">{{ $r->familiya }}</div>
                    <div class="text-muted" style="font-size:.68rem">{{ $r->telefon }}</div>
                </td>
                <td class="tl">
                    <a href="{{ route('kreditlar.show',$r->id) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                        {{ $r->shartnoma_raqam }}
                    </a>
                    @if($r->min_kun > 180)
                        <span class="badge bg-dark" style="font-size:.6rem">{{ $r->min_kun }}k</span>
                    @elseif($r->min_kun > 90)
                        <span class="badge bg-danger" style="font-size:.6rem">{{ $r->min_kun }}k</span>
                    @endif
                </td>
                @if(Auth::user()->isAdmin())
                <td class="tl"><span class="badge bg-secondary" style="font-size:.65rem">{{ $r->filial_kod }}</span></td>
                @endif
                <td class="num text-muted">{{ number_format($r->kredit_summa/1000000,1) }}m</td>
                <td class="num">{{ number_format($r->qoldiq_qarz/1000000,1) }}m</td>
                <td class="num d30-col">{{ $r->d30 > 0 ? number_format($r->d30/1000,0) : '—' }}</td>
                <td class="num d60-col">{{ $r->d60 > 0 ? number_format($r->d60/1000,0) : '—' }}</td>
                <td class="num d90-col">{{ $r->d90 > 0 ? number_format($r->d90/1000,0) : '—' }}</td>
                <td class="num d120-col">{{ $r->d120 > 0 ? number_format($r->d120/1000,0) : '—' }}</td>
                <td class="num d150-col">{{ $r->d150 > 0 ? number_format($r->d150/1000,0) : '—' }}</td>
                <td class="num d180-col">{{ $r->d180 > 0 ? number_format($r->d180/1000,0) : '—' }}</td>
                <td class="num d180p-col" style="color:#8b0000">{{ $r->d180p > 0 ? number_format($r->d180p/1000,0) : '—' }}</td>
                <td class="num jami-kech text-danger">{{ number_format($r->jami_kechikkan/1000,0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="tl" colspan="{{ Auth::user()->isAdmin() ? 6 : 5 }}">JAMI:</td>
                <td class="num">{{ number_format($jami['d30']/1000,0) }}</td>
                <td class="num">{{ number_format($jami['d60']/1000,0) }}</td>
                <td class="num">{{ number_format($jami['d90']/1000,0) }}</td>
                <td class="num">{{ number_format($jami['d120']/1000,0) }}</td>
                <td class="num">{{ number_format($jami['d150']/1000,0) }}</td>
                <td class="num">{{ number_format($jami['d180']/1000,0) }}</td>
                <td class="num">{{ number_format($jami['d180p']/1000,0) }}</td>
                <td class="num" style="color:#fde68a">{{ number_format($jami['jami']/1000,0) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@if($rows->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">
        {{ $rows->firstItem() }}–{{ $rows->lastItem() }} / {{ $jami['soni'] }} ta kredit
        &nbsp;|&nbsp; Sahifa {{ $rows->currentPage() }} / {{ $rows->lastPage() }}
    </small>
    {{ $rows->links('pagination::bootstrap-5') }}
</div>
@endif
<div class="text-muted mt-1"><small>* Summalar ming so'mda ko'rsatilgan. Excel da to'liq summa.</small></div>
@endsection
