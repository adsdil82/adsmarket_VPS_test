@extends('layouts.app')

@section('title', 'Bosh sahifa')

@push('styles')
<style>
/* ── Filial Selector ───────────────────────────────── */
.filial-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; font-size: .82rem;
    font-weight: 600; cursor: pointer; border: 2px solid transparent;
    transition: all .2s ease; text-decoration: none; white-space: nowrap;
}
.filial-pill.active {
    background: var(--accent-color, #4f46e5);
    color: #fff; border-color: var(--accent-color, #4f46e5);
    box-shadow: 0 4px 12px rgba(79,70,229,.35);
}
.filial-pill:not(.active) {
    background: #fff; color: #555;
    border-color: #dde; box-shadow: 0 1px 4px rgba(0,0,0,.08);
}
.filial-pill:not(.active):hover {
    border-color: var(--accent-color, #4f46e5);
    color: var(--accent-color, #4f46e5);
}
.filial-kod-badge {
    font-size: .7rem; background: rgba(0,0,0,.1);
    border-radius: 10px; padding: 1px 6px;
}
.filial-pill.active .filial-kod-badge { background: rgba(255,255,255,.2); }

/* ── Stat Cards ────────────────────────────────────── */
.stat-card {
    border: none; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    transition: transform .18s, box-shadow .18s;
    overflow: hidden; position: relative;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.stat-card .card-body { padding: 18px 20px; }
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.stat-label { font-size: .75rem; font-weight: 600; text-transform: uppercase;
               letter-spacing: .5px; color: #888; margin-bottom: 4px; }
.stat-value { font-size: 1.55rem; font-weight: 700; line-height: 1.2; }
.stat-sub   { font-size: .78rem; color: #999; margin-top: 3px; }
.stat-badge {
    position: absolute; top: 10px; right: 10px;
    font-size: .65rem; padding: 2px 7px; border-radius: 10px;
}
.progress-thin { height: 4px; border-radius: 4px; }

/* ── Charts ─────────────────────────────────────────── */
.chart-card {
    border: none; border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.chart-card .card-header {
    background: transparent; border-bottom: 1px solid #f0f0f4;
    padding: 14px 18px; font-weight: 600;
}

/* ── Tables ──────────────────────────────────────────── */
.dash-table { font-size: .83rem; }
.dash-table th { font-size: .72rem; text-transform: uppercase;
                  letter-spacing: .4px; color: #888; font-weight: 600; }
.dash-table tbody tr:hover { background: rgba(79,70,229,.04); }

/* ── Top Qarzdorlar ──────────────────────────────────── */
.qarz-bar { height: 6px; border-radius: 4px; background: #f1f3f7; overflow: hidden; margin-top: 4px; }
.qarz-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg,#ef4444,#f97316); }

/* ── Loading overlay ────────────────────────────────── */
#dash-loading {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(255,255,255,.6); backdrop-filter: blur(3px);
    display: none; align-items: center; justify-content: center;
}
.spin-ring {
    width: 48px; height: 48px; border-radius: 50%;
    border: 4px solid #e0e0ff;
    border-top-color: var(--accent-color, #4f46e5);
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Filial info bar ─────────────────────────────────── */
.filial-info-bar {
    background: linear-gradient(135deg, var(--accent-color,#4f46e5) 0%, #7c3aed 100%);
    color: #fff; border-radius: 14px; padding: 14px 20px;
    display: flex; align-items: center; gap: 12px;
    box-shadow: 0 4px 16px rgba(79,70,229,.3);
}
.filial-info-bar h5 { margin: 0; font-size: 1rem; font-weight: 700; }
.filial-info-bar small { opacity: .8; font-size: .78rem; }

/* number counter animation */
.count-anim { transition: all .4s ease; }
</style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item active">Bosh sahifa</li>
@endsection

@section('content')

{{-- ═══ Loading overlay ══════════════════════════════════════════ --}}
<div id="dash-loading">
    <div class="spin-ring"></div>
</div>

{{-- ═══ Filial selector (faqat admin) ══════════════════════════════ --}}
@if(Auth::user()->isAdmin())
<div class="mb-4">
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="text-muted fw-medium me-1" style="font-size:.82rem">
            <i class="bi bi-diagram-3 me-1"></i>Filial:
        </span>
        <button class="filial-pill {{ !$filialId ? 'active' : '' }}"
                onclick="filialTanla(null, this)"
                data-filial-id="">
            <i class="bi bi-globe2"></i> Barchasi
        </button>
        @foreach($filiallar as $filial)
        <button class="filial-pill {{ $filialId == $filial->id ? 'active' : '' }}"
                onclick="filialTanla({{ $filial->id }}, this)"
                data-filial-id="{{ $filial->id }}">
            <span class="filial-kod-badge">{{ $filial->kod }}</span>
            {{ $filial->nomi }}
        </button>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ Tanlangan filial info bar ════════════════════════════════ --}}
<div id="filial-info-bar" class="filial-info-bar mb-4 {{ !$filialId ? 'd-none' : '' }}">
    <div class="bg-white bg-opacity-25 rounded-circle p-2">
        <i class="bi bi-building fs-5"></i>
    </div>
    <div>
        <h5 id="filial-info-nomi">
            @if($filialId)
                {{ $filiallar->firstWhere('id',$filialId)->nomi ?? '' }}
            @endif
        </h5>
        <small id="filial-info-sub">Filialga tegishli statistika</small>
    </div>
    <div class="ms-auto text-end">
        <div class="fw-bold fs-5" id="filial-info-faol">{{ number_format($stats['faol_shartnomalar']) }}</div>
        <small>faol shartnoma</small>
    </div>
</div>

{{-- ═══ 8 ta katta statistika kartasi ════════════════════════════ --}}
<div class="row g-3 mb-4" id="stats-row">

    {{-- 1 AKTIV shartnomalar --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">AKTIV shartnomalar</div>
                        <div class="stat-value text-success" id="s-faol">
                            {{ number_format($stats['faol_shartnomalar']) }}
                        </div>
                        <div class="stat-sub">
                            Jami: <strong id="s-jami">{{ number_format($stats['jami_shartnomalar']) }}</strong>
                        </div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bi bi-file-earmark-check text-success"></i>
                    </div>
                </div>
                @php $faolPct = $stats['jami_shartnomalar'] > 0 ? ($stats['faol_shartnomalar'] / $stats['jami_shartnomalar'] * 100) : 0; @endphp
                <div class="progress progress-thin mt-3">
                    <div class="progress-bar bg-success" id="p-faol" style="width:{{ $faolPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2 Muddati o'tgan --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">Muddati o'tgan</div>
                        <div class="stat-value text-danger" id="s-muddati">
                            {{ number_format($stats['muddati_otgan']) }}
                        </div>
                        <div class="stat-sub">
                            PASSIV: <strong id="s-yopilgan">{{ number_format($stats['yopilgan_shartnomalar']) }}</strong>
                        </div>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                    </div>
                </div>
                @php $muddPct = $stats['jami_shartnomalar'] > 0 ? ($stats['muddati_otgan'] / $stats['jami_shartnomalar'] * 100) : 0; @endphp
                <div class="progress progress-thin mt-3">
                    <div class="progress-bar bg-danger" id="p-muddati" style="width:{{ $muddPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3 Bugungi to'lov --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">Bugungi to'lovlar</div>
                        <div class="stat-value text-primary" id="s-bugun-summa" style="font-size:1.15rem">
                            {{ number_format($stats['bugun_tolov_summa'], 0, '.', ' ') }}
                        </div>
                        <div class="stat-sub">
                            <strong id="s-bugun-soni">{{ $stats['bugun_tolov_soni'] }}</strong> ta to'lov
                        </div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bi bi-receipt text-primary"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-success bg-opacity-10 text-success" style="font-size:.7rem">
                        <i class="bi bi-calendar3 me-1"></i>Bugun
                    </span>
                    <span class="ms-1 text-muted" style="font-size:.72rem" id="s-bugun-sana">
                        {{ now()->format('d.m.Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 4 Shu oy to'lov --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">Shu oy to'lovlar</div>
                        <div class="stat-value" id="s-oy-summa" style="font-size:1.15rem">
                            {{ number_format($stats['oy_tolov_summa'], 0, '.', ' ') }}
                        </div>
                        <div class="stat-sub text-muted">so'm</div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bi bi-calendar-month text-info"></i>
                    </div>
                </div>
                <div class="progress progress-thin mt-3">
                    @php
                        $hafPct = $stats['oy_tolov_summa'] > 0
                            ? min(100, $stats['haf_tolov_summa'] / $stats['oy_tolov_summa'] * 100)
                            : 0;
                    @endphp
                    <div class="progress-bar bg-info" id="p-haf" style="width:{{ $hafPct }}%" title="Haftalik ulush"></div>
                </div>
                <div class="stat-sub mt-1">
                    Hafta: <strong id="s-haf-summa">{{ number_format($stats['haf_tolov_summa'], 0, '.', ' ') }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- 5 Jami qoldiq qarz --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">Jami qoldiq qarz</div>
                        <div class="stat-value text-warning" id="s-qoldiq" style="font-size:1.15rem">
                            {{ number_format($stats['jami_qoldiq'], 0, '.', ' ') }}
                        </div>
                        <div class="stat-sub text-muted">so'm</div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bi bi-cash-stack text-warning"></i>
                    </div>
                </div>
                <div class="stat-sub mt-2">
                    Jami kredit:
                    <strong id="s-jami-kredit">{{ number_format($stats['jami_kredit_summa'], 0, '.', ' ') }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- 6 Samaradorlik --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">To'lov samaradorligi</div>
                        <div class="stat-value" id="s-samar" style="font-size:1.55rem">
                            {{ $stats['samaradorlik'] }}<small class="fs-6 fw-normal text-muted">%</small>
                        </div>
                        <div class="stat-sub">
                            To'landi: <strong id="s-jami-tolov">{{ number_format($stats['jami_tolov_qilingan'], 0, '.', ' ') }}</strong>
                        </div>
                    </div>
                    <div class="stat-icon bg-purple bg-opacity-10" style="background:#f3f0ff">
                        <i class="bi bi-graph-up-arrow" style="color:#7c3aed;font-size:1.4rem"></i>
                    </div>
                </div>
                <div class="progress progress-thin mt-3">
                    <div class="progress-bar" id="p-samar"
                         style="width:{{ min(100,$stats['samaradorlik']) }}%;background:#7c3aed"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 7 Faol mijozlar --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">Faol mijozlar</div>
                        <div class="stat-value" id="s-faol-mijoz">
                            {{ number_format($stats['faol_mijozlar']) }}
                        </div>
                        <div class="stat-sub">
                            Jami: <strong id="s-jami-mijoz">{{ number_format($stats['jami_mijozlar']) }}</strong>
                        </div>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10">
                        <i class="bi bi-people text-secondary"></i>
                    </div>
                </div>
                @php $mijPct = $stats['jami_mijozlar'] > 0 ? ($stats['faol_mijozlar'] / $stats['jami_mijozlar'] * 100) : 0; @endphp
                <div class="progress progress-thin mt-3">
                    <div class="progress-bar bg-secondary" id="p-mijoz" style="width:{{ $mijPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 8 Yangi shartnomalar --}}
    <div class="col-6 col-sm-4 col-lg-3">
        <div class="stat-card card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">Yangi shartnomalar</div>
                        <div class="stat-value text-info" id="s-bugun-yangi">
                            {{ $stats['bugun_yangi_shartnoma'] }}
                        </div>
                        <div class="stat-sub">
                            Oy: <strong id="s-oy-yangi">{{ $stats['oy_yangi_shartnoma'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bi bi-file-earmark-plus text-info"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem">
                        <i class="bi bi-plus-circle me-1"></i>Bugun {{ $stats['bugun_yangi_shartnoma'] }} ta
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═══ Grafiklar qatori ══════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Oylik to'lovlar LINE chart --}}
    <div class="col-lg-7">
        <div class="chart-card card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-graph-up me-2 text-primary"></i>Oylik to'lovlar (so'm)</span>
                <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.72rem">Oxirgi 6 oy</span>
            </div>
            <div class="card-body" style="min-height:260px">
                <canvas id="oylikChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Holatlari DONUT chart --}}
    <div class="col-lg-5">
        <div class="chart-card card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pie-chart me-2 text-success"></i>Shartnoma holatlari</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:260px">
                <div style="max-width:280px;width:100%;position:relative">
                    <canvas id="holatChart"></canvas>
                    <div id="holatCenter" style="
                        position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                        text-align:center;pointer-events:none">
                        <div style="font-size:1.5rem;font-weight:700" id="holatTotalNum">
                            {{ $stats['jami_shartnomalar'] }}
                        </div>
                        <div style="font-size:.72rem;color:#888">jami</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═══ Filiallar muqoyasasi (faqat admin va "Barchasi" tanlanganda) ══ --}}
@if(Auth::user()->isAdmin())
<div class="row g-3 mb-4" id="filial-muqoyasa-row" {{ $filialId ? 'style=display:none' : '' }}>
    <div class="col-12">
        <div class="chart-card card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart me-2 text-warning"></i>Filiallar muqoyasasi</span>
                <span class="text-muted" style="font-size:.75rem">Faol va muddati o'tgan shartnomalar</span>
            </div>
            <div class="card-body" style="min-height:220px">
                <canvas id="filialChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══ Jadvallar qatori ══════════════════════════════════════════ --}}
<div class="row g-3">

    {{-- TOP 5 qarzdorlar --}}
    <div class="col-lg-4">
        <div class="chart-card card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-trophy me-1 text-danger"></i>TOP-5 qarzdorlar</span>
                <a href="{{ route('kreditlar.index', ['holat' => 'muddati_otgan']) }}"
                   class="btn btn-sm btn-outline-danger py-0" style="font-size:.75rem">Barchasi</a>
            </div>
            <div class="card-body" id="top-qarz-body">
                @include('dashboard._top_qarz', ['topQarzdorlar' => $topQarzdorlar])
            </div>
        </div>
    </div>

    {{-- Muddati o'tgan shartnomalar --}}
    <div class="col-lg-4">
        <div class="chart-card card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>Muddati o'tgan
                </span>
                <a href="{{ route('kreditlar.index', ['holat' => 'muddati_otgan']) }}"
                   class="btn btn-sm btn-outline-danger py-0" style="font-size:.75rem">Ko'proq</a>
            </div>
            <div class="card-body p-0" id="kechikkan-body">
                @include('dashboard._kechikkanlar', ['kechikkanlar' => $kechikkanlar])
            </div>
        </div>
    </div>

    {{-- Bugungi to'lovlar --}}
    <div class="col-lg-4">
        <div class="chart-card card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="text-success">
                    <i class="bi bi-receipt me-1"></i>Bugungi to'lovlar
                </span>
                <a href="{{ route('hisobotlar.index') }}"
                   class="btn btn-sm btn-outline-success py-0" style="font-size:.75rem">Hisobot</a>
            </div>
            <div class="card-body p-0" id="bugungi-tulov-body">
                @include('dashboard._bugungi_tulovlar', ['bugungiTulovlar' => $bugungiTulovlar])
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
// ─── PHP → JS ma'lumotlar ──────────────────────────────────────
const OYLIK  = @json($oylikChart);
const HOLAT  = @json($holatlariChart);
@if(Auth::user()->isAdmin())
const FILIAL = @json($filialMuqoyasa);
@endif
const AJAX_URL = '{{ route("dashboard.statistika") }}';
const IS_ADMIN = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};

let oylikChartObj  = null;
let holatChartObj  = null;
let filialChartObj = null;

// ─── Helper: son formatlash ────────────────────────────────────
function fmt(n) {
    return new Intl.NumberFormat('uz-UZ').format(Math.round(n||0));
}
function fmtMln(n) {
    if (!n) return '0';
    if (Math.abs(n) >= 1e9) return (n/1e9).toFixed(1) + ' mlrd';
    if (Math.abs(n) >= 1e6) return (n/1e6).toFixed(1) + ' mln';
    return fmt(n);
}

// ─── Oylik LINE chart ──────────────────────────────────────────
function buildOylikChart(data) {
    const ctx = document.getElementById('oylikChart').getContext('2d');
    if (oylikChartObj) oylikChartObj.destroy();

    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(79,70,229,.25)');
    gradient.addColorStop(1, 'rgba(79,70,229,.01)');

    oylikChartObj = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.oylar,
            datasets: [{
                label: "To'lov (so'm)",
                data: data.summalar,
                borderColor: '#4f46e5',
                backgroundColor: gradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#4f46e5',
                pointRadius: 4,
                fill: true,
                tension: .4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + fmtMln(ctx.raw) + ' so\'m'
                    }
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: v => fmtMln(v),
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,.05)' }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
}

// ─── Holat DONUT chart ─────────────────────────────────────────
function buildHolatChart(data) {
    const ctx = document.getElementById('holatChart').getContext('2d');
    if (holatChartObj) holatChartObj.destroy();

    holatChartObj = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ["AKTIV", "PASSIV", "Muddati o'tgan"],
            datasets: [{
                data: [data.faol, data.yopilgan, data.muddati_otgan],
                backgroundColor: ['#22c55e','#64748b','#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            cutout: '68%',
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12,
                              usePointStyle: true, pointStyleWidth: 8 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + fmt(ctx.raw) + ' ta (' +
                            (ctx.raw / Math.max(1, data.faol+data.yopilgan+data.muddati_otgan) * 100).toFixed(1) + '%)'
                    }
                }
            }
        }
    });

    document.getElementById('holatTotalNum').textContent =
        fmt(data.faol + data.yopilgan + data.muddati_otgan);
}

// ─── Filiallar BAR chart (faqat admin) ───────────────────────
function buildFilialChart(data) {
    if (!IS_ADMIN) return;
    const ctx = document.getElementById('filialChart').getContext('2d');
    if (filialChartObj) filialChartObj.destroy();

    filialChartObj = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.nomlar,
            datasets: [
                {
                    label: 'AKTIV',
                    data: data.faollar,
                    backgroundColor: 'rgba(34,197,94,.75)',
                    borderRadius: 5,
                },
                {
                    label: "Muddati o'tgan",
                    data: data.muddatlar,
                    backgroundColor: 'rgba(239,68,68,.65)',
                    borderRadius: 5,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 11 }, padding: 14, usePointStyle: true }
                },
                tooltip: {
                    callbacks: { label: ctx => ' ' + fmt(ctx.raw) + ' ta' }
                }
            },
            scales: {
                y: { ticks: { font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.04)' } },
                x: { grid: { display: false }, ticks: { font: { size: 12, weight: '600' } } }
            }
        }
    });
}

// ─── Statistika kartalarini yangilash ──────────────────────────
function statsYangilash(s) {
    const set = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    };
    const pct = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.style.width = Math.min(100, val) + '%';
    };

    set('s-faol',        fmt(s.faol_shartnomalar));
    set('s-jami',        fmt(s.jami_shartnomalar));
    set('s-muddati',     fmt(s.muddati_otgan));
    set('s-yopilgan',    fmt(s.yopilgan_shartnomalar));
    set('s-bugun-summa', fmt(s.bugun_tolov_summa));
    set('s-bugun-soni',  s.bugun_tolov_soni);
    set('s-oy-summa',    fmt(s.oy_tolov_summa));
    set('s-haf-summa',   fmt(s.haf_tolov_summa));
    set('s-qoldiq',      fmt(s.jami_qoldiq));
    set('s-jami-kredit', fmt(s.jami_kredit_summa));
    set('s-samar',       s.samaradorlik);
    set('s-jami-tolov',  fmt(s.jami_tolov_qilingan));
    set('s-faol-mijoz',  fmt(s.faol_mijozlar));
    set('s-jami-mijoz',  fmt(s.jami_mijozlar));
    set('s-bugun-yangi', s.bugun_yangi_shartnoma);
    set('s-oy-yangi',    s.oy_yangi_shartnoma);

    // Info bar
    set('filial-info-faol', fmt(s.faol_shartnomalar));

    // Progress bar'lar
    pct('p-faol',    s.jami_shartnomalar > 0 ? s.faol_shartnomalar/s.jami_shartnomalar*100 : 0);
    pct('p-muddati', s.jami_shartnomalar > 0 ? s.muddati_otgan/s.jami_shartnomalar*100 : 0);
    pct('p-haf',     s.oy_tolov_summa > 0 ? s.haf_tolov_summa/s.oy_tolov_summa*100 : 0);
    pct('p-samar',   s.samaradorlik);
    pct('p-mijoz',   s.jami_mijozlar > 0 ? s.faol_mijozlar/s.jami_mijozlar*100 : 0);
}

// ─── TOP qarzdorlar HTML yangilash ─────────────────────────────
function topQarzYangilash(list) {
    const body = document.getElementById('top-qarz-body');
    if (!list || list.length === 0) {
        body.innerHTML = '<p class="text-muted text-center py-4 mb-0"><i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>Katta qarzdorlar yoq</p>';
        return;
    }
    const maxQ = list[0].qoldiq_qarz || 1;
    let html = '';
    list.forEach((r, i) => {
        const pct = Math.round(r.qoldiq_qarz / maxQ * 100);
        const badge = r.holat === 'muddati_otgan'
            ? '<span class="badge bg-danger bg-opacity-10 text-danger ms-1" style="font-size:.65rem">!</span>' : '';
        html += `<div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="/kreditlar/${r.id}" class="text-decoration-none fw-medium small">${r.shartnoma_raqam}</a>${badge}
                    <div class="text-muted" style="font-size:.75rem">${r.mijoz_ism}</div>
                </div>
                <div class="text-end ms-2">
                    <div class="fw-bold text-danger small">${fmtMln(r.qoldiq_qarz)}</div>
                    <div style="font-size:.7rem;color:#aaa">so'm</div>
                </div>
            </div>
            <div class="qarz-bar mt-1"><div class="qarz-bar-fill" style="width:${pct}%"></div></div>
        </div>`;
    });
    body.innerHTML = html;
}

// ─── Kechikkanlar jadval yangilash ─────────────────────────────
function kechikkanYangilash(list) {
    const body = document.getElementById('kechikkan-body');
    if (!list || list.length === 0) {
        body.innerHTML = '<p class="text-muted text-center py-4 mb-0"><i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>Muddati o\'tgan to\'lovlar yo\'q</p>';
        return;
    }
    let rows = '';
    list.forEach(k => {
        rows += `<tr>
            <td><a href="/kreditlar/${k.id}" class="text-decoration-none fw-medium">${k.shartnoma_raqam}</a></td>
            <td class="text-truncate" style="max-width:100px">${k.mijoz_ism}</td>
            <td class="text-end text-danger fw-medium">${fmtMln(k.qoldiq_qarz)}</td>
        </tr>`;
    });
    body.innerHTML = `<div class="table-responsive"><table class="table table-hover mb-0 table-sm dash-table">
        <thead class="table-light"><tr><th>Shartnoma</th><th>Mijoz</th><th class="text-end">Qoldiq</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ─── Bugungi to'lovlar yangilash ───────────────────────────────
function bugunTolovYangilash(list) {
    const body = document.getElementById('bugungi-tulov-body');
    if (!list || list.length === 0) {
        body.innerHTML = '<p class="text-muted text-center py-4 mb-0"><i class="bi bi-inbox fs-4 d-block mb-2"></i>Bugun to\'lov qabul qilinmagan</p>';
        return;
    }
    let rows = '';
    list.forEach(t => {
        rows += `<tr>
            <td>
                <a href="/kreditlar/${t.kredit_id}" class="text-decoration-none small fw-medium">${t.shartnoma_raqam}</a>
                <div class="text-muted" style="font-size:.72rem">${t.mijoz_ism}</div>
            </td>
            <td class="text-end fw-medium text-success small">${fmtMln(t.summa)}</td>
        </tr>`;
    });
    body.innerHTML = `<div class="table-responsive"><table class="table table-hover mb-0 table-sm dash-table">
        <thead class="table-light"><tr><th>Shartnoma</th><th class="text-end">Summa</th></tr></thead>
        <tbody>${rows}</tbody></table></div>`;
}

// ─── Filial tanlash (AJAX) ────────────────────────────────────
function filialTanla(filialId, btn) {
    // Tugmalarni yangilash
    document.querySelectorAll('.filial-pill').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Info bar
    const infoBar = document.getElementById('filial-info-bar');
    if (filialId) {
        const nomi = btn.textContent.replace(/[A-Z]{2,}/, '').trim();
        document.getElementById('filial-info-nomi').textContent = nomi;
        infoBar.classList.remove('d-none');
    } else {
        infoBar.classList.add('d-none');
    }

    // Filial muqoyasasi — faqat "Barchasi"da ko'rsat
    const muqoyasaRow = document.getElementById('filial-muqoyasa-row');
    if (muqoyasaRow) {
        muqoyasaRow.style.display = filialId ? 'none' : '';
    }

    // AJAX so'rov
    const loading = document.getElementById('dash-loading');
    loading.style.display = 'flex';

    const params = new URLSearchParams();
    if (filialId) params.set('filial_id', filialId);

    fetch(AJAX_URL + '?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        statsYangilash(data.stats);
        buildOylikChart(data.oylikChart);
        buildHolatChart(data.holatlariChart);
        topQarzYangilash(data.topQarzdorlar);
        kechikkanYangilash(data.kechikkanlar);
        bugunTolovYangilash(data.bugungiTulovlar);
    })
    .catch(err => console.error('Dashboard AJAX xato:', err))
    .finally(() => { loading.style.display = 'none'; });
}

// ─── Sahifa yuklanganda ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    buildOylikChart(OYLIK);
    buildHolatChart(HOLAT);
    @if(Auth::user()->isAdmin())
    buildFilialChart(FILIAL);
    @endif
});
</script>
@endpush
