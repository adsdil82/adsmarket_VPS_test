@extends('layouts.app')
@section('title', 'Kredit portfeli')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Kredit portfeli</li>
@endsection

@push('styles')
<style>
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:8px 14px; }
.filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:0; font-size:.85rem;
}
.kp-stat { border:1px solid #93c5fd; border-radius:8px; overflow:hidden; background:#fff; }
.kp-stat-head {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; font-weight:700;
    font-size:.66rem; letter-spacing:.03em; text-transform:uppercase; text-align:center; padding:5px;
}
.kp-stat-body { text-align:center; padding:8px 6px; }
.kp-stat-val { font-size:1.25rem; font-weight:800; }

.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.03em; text-transform:uppercase; padding:6px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800;
    padding:6px 8px; border-top:2px solid #60a5fa;
}
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

<div class="filter-bar mb-3">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-pie-chart" style="font-size:1.1rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a">Kredit portfeli</span>
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <select name="filial_id" class="form-select" style="width:200px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="btn btn-sm btn-success px-3" style="height:32px">
            <i class="bi bi-funnel me-1"></i>Ko'rsatish
        </button>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('hisobotlar.excel','portfolio') }}?sana={{ $sana }}&filial_id={{ $filialId }}"
               class="btn btn-sm btn-success px-3" style="height:32px">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-outline-secondary" style="height:32px">
                <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </form>
</div>

{{-- Jami kartalar --}}
@php
$jamiKredit = $portfolio->sum('jami_kredit');
$aktivQoldiq = $portfolio->sum('aktiv_qoldiq');
$jamiTolov   = $portfolio->sum('jami_tolov');
$jamiTa      = $portfolio->sum('jami');
$samar       = $jamiKredit > 0 ? round($jamiTolov/$jamiKredit*100,1) : 0;
@endphp
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="kp-stat">
            <div class="kp-stat-head">Jami shartnoma</div>
            <div class="kp-stat-body"><div class="kp-stat-val text-primary">{{ number_format($jamiTa) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kp-stat">
            <div class="kp-stat-head">Jami kredit</div>
            <div class="kp-stat-body"><div class="kp-stat-val text-success">{{ number_format($jamiKredit/1000000,1) }} mln</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kp-stat">
            <div class="kp-stat-head">Aktiv qoldiq</div>
            <div class="kp-stat-body"><div class="kp-stat-val text-danger">{{ number_format($aktivQoldiq/1000000,1) }} mln</div></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kp-stat">
            <div class="kp-stat-head">Samaradorlik</div>
            <div class="kp-stat-body"><div class="kp-stat-val" style="color:#7c3aed">{{ $samar }}%</div></div>
        </div>
    </div>
</div>

{{-- Filiallar jadval --}}
<div class="bft-section-title">Filiallar bo'yicha</div>
<div class="bank-wrap mb-3">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Filial</th>
                <th>Jami</th>
                <th>Faol</th>
                <th>Muddati o'tgan</th>
                <th>Yopilgan</th>
                <th>Jami kredit</th>
                <th>Aktiv qoldiq</th>
                <th>To'lov qilingan</th>
                <th>Samaradorlik</th>
            </tr>
        </thead>
        <tbody>
            @foreach($portfolio as $r)
            @php $s = $r->jami_kredit > 0 ? round($r->jami_tolov/$r->jami_kredit*100,1) : 0; @endphp
            <tr>
                <td class="tl">
                    <span class="badge bg-secondary me-1">{{ $r->kod }}</span>
                    {{ $r->filial }}
                </td>
                <td class="num fw-bold">{{ number_format($r->jami) }}</td>
                <td class="num text-success">{{ number_format($r->faol) }}</td>
                <td class="num text-danger">{{ number_format($r->muddati_otgan) }}</td>
                <td class="num text-secondary">{{ number_format($r->yopilgan) }}</td>
                <td class="num">{{ number_format($r->jami_kredit/1000000,1) }} mln</td>
                <td class="num text-danger">{{ number_format($r->aktiv_qoldiq/1000000,1) }} mln</td>
                <td class="num text-success">{{ number_format($r->jami_tolov/1000000,1) }} mln</td>
                <td class="text-center" style="min-width:90px">
                    <div class="progress" style="height:6px">
                        <div class="progress-bar bg-success" style="width:{{ $s }}%"></div>
                    </div>
                    <small class="text-muted">{{ $s }}%</small>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="tl">JAMI</td>
                <td class="num">{{ number_format($jamiTa) }}</td>
                <td class="num" style="color:#86efac">{{ number_format($portfolio->sum('faol')) }}</td>
                <td class="num" style="color:#fca5a5">{{ number_format($portfolio->sum('muddati_otgan')) }}</td>
                <td class="num" style="color:#cbd5e1">{{ number_format($portfolio->sum('yopilgan')) }}</td>
                <td class="num">{{ number_format($jamiKredit/1000000,1) }} mln</td>
                <td class="num" style="color:#fca5a5">{{ number_format($aktivQoldiq/1000000,1) }} mln</td>
                <td class="num" style="color:#86efac">{{ number_format($jamiTolov/1000000,1) }} mln</td>
                <td class="text-center" style="color:#fde68a">{{ $samar }}%</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Oylik dinamika --}}
@if($oyDinamika->count())
<div class="bft-section-title">Oylik dinamika (oxirgi 12 oy)</div>
<div class="bank-wrap mb-3" style="border-top:none">
    <div class="p-3">
        <canvas id="portfelChart" height="60"></canvas>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
var oyDinamika = @json($oyDinamika);
var oylar  = oyDinamika.map(r => r.oy);
var summalar = oyDinamika.map(r => Math.round(r.summa/1000000*10)/10);
var sonlar  = oyDinamika.map(r => r.soni);

var ctx = document.getElementById('portfelChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: oylar,
        datasets: [
            { label: 'Kredit summa (mln)', data: summalar, backgroundColor: 'rgba(45,106,79,.7)', borderRadius: 4 },
            { label: 'Soni', data: sonlar, type: 'line', yAxisID: 'y2',
              borderColor: '#6366f1', backgroundColor: 'transparent', borderWidth: 2, pointRadius: 4 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
        scales: {
            y:  { ticks: { callback: v => v + ' mln', font: { size: 11 } } },
            y2: { position: 'right', grid: { drawOnChartArea: false },
                  ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
