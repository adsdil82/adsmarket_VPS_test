@extends('layouts.app')
@section('title', 'Kredit portfeli')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Kredit portfeli</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-pie-chart me-1 text-success"></i> Kredit portfeli</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('hisobotlar.excel','portfolio') }}?sana={{ $sana }}&filial_id={{ $filialId }}"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel
        </a>
        <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-funnel me-1"></i> Ko'rsatish
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Jami kartalar --}}
@php
$jamiKredit = $portfolio->sum('jami_kredit');
$aktivQoldiq = $portfolio->sum('aktiv_qoldiq');
$jamiTolov   = $portfolio->sum('jami_tolov');
$jamiTa      = $portfolio->sum('jami');
$samar       = $jamiKredit > 0 ? round($jamiTolov/$jamiKredit*100,1) : 0;
@endphp
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-primary">{{ number_format($jamiTa) }}</div>
            <div class="small text-muted">Jami shartnoma</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-5 fw-bold text-success">{{ number_format($jamiKredit/1000000,1) }} mln</div>
            <div class="small text-muted">Jami kredit</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-5 fw-bold text-danger">{{ number_format($aktivQoldiq/1000000,1) }} mln</div>
            <div class="small text-muted">Aktiv qoldiq</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-5 fw-bold" style="color:#7c3aed">{{ $samar }}%</div>
            <div class="small text-muted">Samaradorlik</div>
        </div>
    </div>
</div>

{{-- Filiallar jadval --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2"><h6 class="mb-0 small">Filiallar bo'yicha</h6></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>Filial</th>
                    <th class="text-center">Jami</th>
                    <th class="text-center text-success">Faol</th>
                    <th class="text-center text-danger">Muddati o'tgan</th>
                    <th class="text-center text-secondary">Yopilgan</th>
                    <th class="text-end">Jami kredit</th>
                    <th class="text-end">Aktiv qoldiq</th>
                    <th class="text-end">To'lov qilingan</th>
                    <th class="text-center">Samaradorlik</th>
                </tr>
            </thead>
            <tbody>
                @foreach($portfolio as $r)
                @php $s = $r->jami_kredit > 0 ? round($r->jami_tolov/$r->jami_kredit*100,1) : 0; @endphp
                <tr>
                    <td>
                        <span class="badge bg-secondary me-1">{{ $r->kod }}</span>
                        {{ $r->filial }}
                    </td>
                    <td class="text-center fw-bold">{{ number_format($r->jami) }}</td>
                    <td class="text-center text-success">{{ number_format($r->faol) }}</td>
                    <td class="text-center text-danger">{{ number_format($r->muddati_otgan) }}</td>
                    <td class="text-center text-secondary">{{ number_format($r->yopilgan) }}</td>
                    <td class="text-end">{{ number_format($r->jami_kredit/1000000,1) }} mln</td>
                    <td class="text-end text-danger">{{ number_format($r->aktiv_qoldiq/1000000,1) }} mln</td>
                    <td class="text-end text-success">{{ number_format($r->jami_tolov/1000000,1) }} mln</td>
                    <td class="text-center">
                        <div class="progress" style="height:6px;min-width:60px">
                            <div class="progress-bar bg-success" style="width:{{ $s }}%"></div>
                        </div>
                        <small class="text-muted">{{ $s }}%</small>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td>JAMI</td>
                    <td class="text-center">{{ number_format($jamiTa) }}</td>
                    <td class="text-center text-success">{{ number_format($portfolio->sum('faol')) }}</td>
                    <td class="text-center text-danger">{{ number_format($portfolio->sum('muddati_otgan')) }}</td>
                    <td class="text-center text-secondary">{{ number_format($portfolio->sum('yopilgan')) }}</td>
                    <td class="text-end">{{ number_format($jamiKredit/1000000,1) }} mln</td>
                    <td class="text-end text-danger">{{ number_format($aktivQoldiq/1000000,1) }} mln</td>
                    <td class="text-end text-success">{{ number_format($jamiTolov/1000000,1) }} mln</td>
                    <td class="text-center fw-bold" style="color:#7c3aed">{{ $samar }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Oylik dinamika --}}
@if($oyDinamika->count())
<div class="card border-0 shadow-sm">
    <div class="card-header py-2"><h6 class="mb-0 small">Oylik dinamika (oxirgi 12 oy)</h6></div>
    <div class="card-body">
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
