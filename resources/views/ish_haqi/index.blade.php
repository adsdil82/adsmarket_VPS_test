@extends('layouts.app')
@section('title', 'Xodimlar ish haqi')
@section('breadcrumb')
<li class="breadcrumb-item active">Xodimlar ish haqi</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right; position:relative;
}
.bank-table thead th.tl { text-align:left; }
.bank-table thead th.sticky-col { position:sticky; left:0; z-index:7; }
.bank-table thead th.tl.sticky-col { min-width:150px; }
.bank-table tbody td.sticky-col { position:sticky; left:0; z-index:2; background:inherit; border-right:2px solid #93c5fd; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; font-size:.85rem; }

.bank-wrap { overflow:auto; max-height:calc(100vh - 320px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { max-height:calc(100vh - 260px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:0 0 8px 8px; padding:8px 12px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.oy-tab-strip { display:flex; gap:4px; overflow-x:auto; padding:6px 8px 0; background:#fff; border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; }
.oy-tab { display:inline-block; padding:6px 13px; margin-bottom:6px; border-radius:6px; font-size:.76rem; font-weight:800; color:#1e3a8a; background:#eef4ff; border:1px solid #bfdbfe; text-decoration:none; white-space:nowrap; transition:.15s; }
.oy-tab:hover { background:#dbeafe; color:#1e3a8a; }
.oy-tab.active { background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; border-color:#1d4ed8; box-shadow:0 1px 3px rgba(29,78,216,.4); }

.stat-karta { border:1px solid #d7e2f5; border-radius:8px; padding:10px 14px; background:#fff; }
.stat-karta .son { font-family:'Roboto Mono','Courier New',monospace; font-weight:800; font-size:1.3rem; }
.stat-karta .label { font-size:.7rem; color:#64748b; text-transform:uppercase; letter-spacing:.03em; font-weight:700; }

.davomat-select {
    width:34px; height:24px; padding:0; margin:1px; border:1px solid #cbd5e1; border-radius:4px;
    text-align:center; font-weight:800; font-size:.8rem; color:#fff; cursor:pointer; appearance:none;
    -webkit-appearance:none;
}
.davomat-select:disabled { cursor:not-allowed; opacity:.85; }
#davomat-table th { text-align:center; }

.grup-hisoblandi { background:linear-gradient(180deg,#16a34a,#15803d) !important; text-align:center; }
.grup-ushlandi   { background:linear-gradient(180deg,#dc2626,#b91c1c) !important; text-align:center; }
.grup-tolandi    { background:linear-gradient(180deg,#2563eb,#1d4ed8) !important; text-align:center; }
.sub-hisoblandi  { background:linear-gradient(180deg,#4ade80,#22c55e) !important; color:#052e16 !important; }
.sub-ushlandi    { background:linear-gradient(180deg,#f87171,#ef4444) !important; color:#450a0a !important; }
.sub-tolandi     { background:linear-gradient(180deg,#60a5fa,#3b82f6) !important; color:#172554 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 pb-3" style="margin-top:-40px">
{{-- Muvaffaqiyat/xato xabarlari layouts/app.blade.php da global ko'rsatiladi — bu yerda takrorlanmaydi --}}

<div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill text-danger me-2"></i>Xodimlar ish haqi</h5>
</div>

<ul class="nav nav-tabs mb-0">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'davomat' ? 'active fw-semibold' : '' }}" href="{{ route('ish_haqi.index', ['tab' => 'davomat']) }}">
            <i class="bi bi-calendar-check me-1"></i>Davomat (tabel)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'hisoblash' ? 'active fw-semibold' : '' }}" href="{{ route('ish_haqi.index', ['tab' => 'hisoblash']) }}">
            <i class="bi bi-calculator me-1"></i>Hisoblash
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'tarix' ? 'active fw-semibold' : '' }}" href="{{ route('ish_haqi.index', ['tab' => 'tarix']) }}">
            <i class="bi bi-clock-history me-1"></i>Tarix
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'sozlamalar' ? 'active fw-semibold' : '' }}" href="{{ route('ish_haqi.index', ['tab' => 'sozlamalar']) }}">
            <i class="bi bi-gear me-1"></i>Sozlamalar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'dashboard' ? 'active fw-semibold' : '' }}" href="{{ route('ish_haqi.index', ['tab' => 'dashboard']) }}">
            <i class="bi bi-bar-chart-fill me-1"></i>Dashboard
        </a>
    </li>
</ul>

{{-- ─── Tab 1: Davomat (tabel) ─────────────────────────────────── --}}
@if($tab === 'davomat')
<div class="oy-tab-strip">
    @foreach(['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'] as $i => $qisqa)
    <a class="oy-tab {{ $oy === $i + 1 ? 'active' : '' }}"
       href="{{ route('ish_haqi.index', array_merge(request()->except(['oy','page']), ['tab' => 'davomat', 'oy' => $i + 1])) }}">{{ $qisqa }}</a>
    @endforeach
</div>
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('ish_haqi.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="davomat">
        <input type="hidden" name="oy" value="{{ $oy }}">
        <div>
            <label class="form-label small mb-1 text-dark">Yil</label>
            <input type="number" name="yil" class="form-control" style="width:100px" value="{{ $yil }}" min="2020" max="2100">
        </div>
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" {{ (string) $filialId === (string) $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
            @endforeach
        </select>
        @endif
        <input type="search" name="qidiruv" class="form-control" style="width:180px" placeholder="Xodim ismi..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Ko'rish</button>
        <div class="ms-auto d-flex align-items-center gap-2">
            @if($oyYopiqmi)
            <span class="badge bg-secondary" style="font-size:.75rem"><i class="bi bi-lock-fill me-1"></i>Bu oy yopilgan</span>
            @else
            <span class="badge bg-success" style="font-size:.75rem"><i class="bi bi-unlock-fill me-1"></i>Ochiq</span>
            @endif
        </div>
    </form>
</div>

<div class="d-flex align-items-center gap-3 flex-wrap my-2 small">
    @foreach(\App\Models\XodimDavomat::ICON_HOLATLARI as $key => $info)
    <span class="d-inline-flex align-items-center gap-1">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;background:{{ $info['rang'] }};color:#fff;font-weight:800;font-size:.7rem">{{ $info['icon'] }}</span>
        {{ $info['nomi'] }}
    </span>
    @endforeach
</div>

<form method="POST" action="{{ route('ish_haqi.davomat.saqla') }}" id="davomat-form">
@csrf
<input type="hidden" name="yil" value="{{ $yil }}">
<input type="hidden" name="oy" value="{{ $oy }}">
<div class="bank-wrap shadow-sm">
    <table class="bank-table" id="davomat-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Xodim</th>
                @for($kun = 1; $kun <= $kunlarSoni; $kun++)
                @php $kunSana = $oyBoshi->copy()->day($kun); $damKuni = $kunSana->isWeekend(); @endphp
                <th style="{{ $damKuni ? 'background:linear-gradient(180deg,#94a3b8,#64748b)' : '' }};min-width:34px;padding:4px 2px" title="{{ $kunSana->format('d.m.Y (D)') }}">{{ $kun }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($xodimlar as $x)
            <tr>
                <td class="tl sticky-col fw-semibold">{{ $x->ism_familiya }}</td>
                @for($kun = 1; $kun <= $kunlarSoni; $kun++)
                @php
                    $kunSana = $oyBoshi->copy()->day($kun);
                    $mavjudHolat = $davomatlar[$x->id][$kun] ?? ($kunSana->isWeekend() ? 'dam_olish' : 'keldi');
                @endphp
                <td class="p-0 text-center">
                    <select name="holat[{{ $x->id }}][{{ $kun }}]" class="davomat-select" {{ $oyYopiqmi ? 'disabled' : '' }}>
                        @foreach(\App\Models\XodimDavomat::ICON_HOLATLARI as $key => $info)
                        <option value="{{ $key }}" {{ $mavjudHolat === $key ? 'selected' : '' }}>{{ $info['icon'] }}</option>
                        @endforeach
                    </select>
                </td>
                @endfor
            </tr>
            @empty
            <tr><td colspan="{{ $kunlarSoni + 1 }}" class="text-center text-muted py-5"><i class="bi bi-people fs-3 d-block mb-2"></i>Xodimlar topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($xodimlar->count() && !$oyYopiqmi)
<div class="mt-2 d-flex gap-2">
    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i>Tabelni saqlash ({{ $oy }}/{{ $yil }})</button>
</div>
@endif
</form>

@if(!$oyYopiqmi && $xodimlar->count())
<form method="POST" action="{{ route('ish_haqi.davomat.oy_yopish') }}" class="mt-2"
      onsubmit="return confirm('{{ $oy }}/{{ $yil }} oyi YOPILSINMI? Yopilgandan keyin bu oy tabelini o\'zgartirib bo\'lmaydi, keyingi oy avtomatik ochiladi.')">
    @csrf
    <input type="hidden" name="yil" value="{{ $yil }}">
    <input type="hidden" name="oy" value="{{ $oy }}">
    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-lock me-1"></i>{{ $oy }}/{{ $yil }} oyini yopish</button>
</form>
@endif
@endif

{{-- ─── Tab 2: Hisoblash ───────────────────────────────────────── --}}
@if($tab === 'hisoblash')
<div class="oy-tab-strip">
    @foreach(['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'] as $i => $qisqa)
    <a class="oy-tab {{ $oy === $i + 1 ? 'active' : '' }}"
       href="{{ route('ish_haqi.index', array_merge(request()->except(['oy','page']), ['tab' => 'hisoblash', 'oy' => $i + 1])) }}">{{ $qisqa }}</a>
    @endforeach
</div>
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('ish_haqi.index') }}" class="d-flex align-items-end flex-wrap gap-2" id="hisoblash-filtr-form">
        <input type="hidden" name="tab" value="hisoblash">
        <input type="hidden" name="oy" value="{{ $oy }}">
        <div>
            <label class="form-label small mb-1 text-dark">Yil</label>
            <input type="number" name="yil" class="form-control" style="width:100px" value="{{ $yil }}" min="2020" max="2100">
        </div>
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" {{ (string) $filialId === (string) $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
            @endforeach
        </select>
        @endif
        <input type="search" name="qidiruv" class="form-control" style="width:180px" placeholder="Xodim ismi..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Ko'rish</button>
        <button type="button" class="btn btn-warning btn-sm px-3 fw-bold" style="height:32px" onclick="hisoblaBoshla()">
            <i class="bi bi-calculator me-1"></i>Hisoblash (barchasi)
        </button>
    </form>
</div>
<form method="POST" action="{{ route('ish_haqi.hisobla') }}" id="hisoblash-form">
    @csrf
    <input type="hidden" name="yil" value="{{ $yil }}">
    <input type="hidden" name="oy" value="{{ $oy }}">
    <input type="hidden" name="filial_id" value="{{ $filialId }}">
</form>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col" rowspan="2">Xodim</th>
                <th class="tl" rowspan="2">Filial</th>
                <th rowspan="2">Oklad</th>
                <th rowspan="2">Davomat</th>
                <th rowspan="2" style="background:linear-gradient(180deg,#64748b,#475569)">O'tgan oy qoldig'i</th>
                <th colspan="4" class="grup-hisoblandi">HISOBLANDI</th>
                <th colspan="3" class="grup-ushlandi">USHLANDI</th>
                <th rowspan="2">Jami</th>
                <th colspan="2" class="grup-tolandi">TO'LANDI</th>
                <th rowspan="2" style="background:linear-gradient(180deg,#64748b,#475569)">Oy yakuniy qoldig'i</th>
                <th class="tl" rowspan="2">Holat</th>
                <th rowspan="2" style="width:170px"></th>
            </tr>
            <tr>
                <th class="sub-hisoblandi">Oklad qismi</th>
                <th class="sub-hisoblandi">Komissiya</th>
                <th class="sub-hisoblandi">Reja bonus (%)</th>
                <th class="sub-hisoblandi">Qo'shimcha</th>
                <th class="sub-ushlandi">Jarima</th>
                <th class="sub-ushlandi">Soliq</th>
                <th class="sub-ushlandi">Boshqa ushl.</th>
                <th class="sub-tolandi">Avans</th>
                <th class="sub-tolandi">Yakuniy to'lov</th>
            </tr>
        </thead>
        <tbody>
            @forelse($xodimlar as $x)
            @php
                $h = $hisoblar->get($x->id);
                $oldingi = (float) ($oldingiQoldiqlar->get($x->id) ?? 0);
                $yakuniy = $oldingi + ($h && $h->holat === 'hisoblangan' ? $h->qolganTolash() : 0);
            @endphp
            <tr>
                <td class="tl sticky-col fw-semibold">{{ $x->ism_familiya }}</td>
                <td class="tl text-muted">{{ $x->filial?->nomi ?? '—' }}</td>
                <td class="num">{{ number_format($x->ishHaqiSozlama->oklad ?? 0, 0, '.', ' ') }}</td>
                @if($h)
                <td class="num">{{ $h->davomat_foizi }}%</td>
                <td class="num {{ $oldingi > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($oldingi, 0, '.', ' ') }}</td>
                <td class="num">{{ number_format($h->oklad_qismi, 0, '.', ' ') }}</td>
                <td class="num">{{ number_format($h->komissiya_bonus, 0, '.', ' ') }}</td>
                <td class="num">
                    {{ number_format($h->reja_bonus, 0, '.', ' ') }}
                    @if($x->ishHaqiSozlama->oylik_reja_summa > 0)
                    <div class="small text-muted" style="font-weight:normal">{{ $h->reja_bajarilish_foizi }}%</div>
                    @endif
                </td>
                <td class="num">{{ number_format($h->qoshimcha_hisoblash, 0, '.', ' ') }}</td>
                <td class="num text-danger">{{ number_format($h->ushlanma, 0, '.', ' ') }}</td>
                <td class="num text-danger">
                    {{ number_format($h->soliq_summa, 0, '.', ' ') }}
                    <div class="small text-muted" style="font-weight:normal">{{ $h->soliq_foizi }}%</div>
                </td>
                <td class="num text-danger">
                    {{ number_format($h->boshqa_ushlanma_summa, 0, '.', ' ') }}
                    @if($h->boshqa_ushlanma_foizi > 0)
                    <div class="small text-muted" style="font-weight:normal">{{ $h->boshqa_ushlanma_foizi }}%</div>
                    @endif
                </td>
                <td class="num fw-bold">{{ number_format($h->jami_hisoblangan, 0, '.', ' ') }}</td>
                <td class="num text-success">{{ number_format($h->avans_jami, 0, '.', ' ') }}</td>
                <td class="num {{ $h->holat === 'tolandi' ? 'text-success fw-bold' : 'text-muted' }}">{{ number_format($h->qolganTolash(), 0, '.', ' ') }}</td>
                <td class="num {{ $yakuniy > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($yakuniy, 0, '.', ' ') }}</td>
                <td class="tl">{!! $h->holatBadge() !!}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 avans-btn"
                        data-xodim-id="{{ $x->id }}" data-xodim="{{ $x->ism_familiya }}" title="Avans berish">
                        <i class="bi bi-cash"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 tafsilot-btn"
                        data-hisob-id="{{ $h->id }}" data-xodim="{{ $x->ism_familiya }}"
                        data-qoshimcha="{{ $h->qoshimcha_hisoblash }}" data-qoshimcha-izoh="{{ $h->qoshimcha_izoh }}"
                        data-ushlanma="{{ $h->ushlanma }}" data-ushlanma-izoh="{{ $h->ushlanma_izoh }}"
                        data-holat="{{ $h->holat }}" data-jami="{{ number_format($h->qolganTolash(), 0, '.', ' ') }}"
                        title="Tafsilot / Qo'shimcha / To'lash">
                        <i class="bi bi-three-dots"></i>
                    </button>
                </td>
                @else
                <td class="num text-muted">—</td>
                <td class="num {{ $oldingi > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ number_format($oldingi, 0, '.', ' ') }}</td>
                <td class="num text-muted" colspan="10">— hali hisoblanmagan —</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 avans-btn"
                        data-xodim-id="{{ $x->id }}" data-xodim="{{ $x->ism_familiya }}" title="Avans berish">
                        <i class="bi bi-cash"></i>
                    </button>
                </td>
                @endif
            </tr>
            @empty
            <tr><td colspan="18" class="text-center text-muted py-5"><i class="bi bi-people fs-3 d-block mb-2"></i>Ish haqi sozlamasi bor xodim topilmadi (avval "Sozlamalar" tabida oklad belgilang)</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Avans berish modali --}}
<div class="modal fade" id="avansModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-cash me-1"></i>Avans berish — <span id="avansXodimNomi"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="avansForm">
        @csrf
        <input type="hidden" name="yil" value="{{ $yil }}">
        <input type="hidden" name="oy" value="{{ $oy }}">
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small fw-medium">Summa (so'm)</label>
                    <input type="number" step="0.01" min="1" name="summa" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Kassa</label>
                    <select name="kassa_turi" class="form-select form-select-sm" required>
                        <option value="naqd">Naqd</option>
                        <option value="terminal">Terminal</option>
                        <option value="bank">Bank</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control form-control-sm">
                </div>
            </div>
        </div>
        <div class="modal-footer py-2">
            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Avans berilsinmi? Bu darhol Harajatlar moduliga yoziladi (kassadan chiqim).')">
                <i class="bi bi-cash-coin me-1"></i>Avans berish
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Tafsilot / qo'shimcha-ushlanma / to'lash modali --}}
<div class="modal fade" id="tafsilotModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="tafsilotXodimNomi"></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="qoshimchaForm">
        @csrf
        <div class="modal-body">
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label small fw-medium">Qo'shimcha hisoblash</label>
                    <input type="number" step="0.01" min="0" name="qoshimcha_hisoblash" id="modal-qoshimcha" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Jarima</label>
                    <input type="number" step="0.01" min="0" name="ushlanma" id="modal-ushlanma" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Qo'shimcha izohi</label>
                    <input type="text" name="qoshimcha_izoh" id="modal-qoshimcha-izoh" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Jarima izohi</label>
                    <input type="text" name="ushlanma_izoh" id="modal-ushlanma-izoh" class="form-control form-control-sm">
                </div>
            </div>
            <div class="form-text mb-2">Soliq va boshqa ushlanma avtomatik (foiz asosida) hisoblanadi — bu yerda faqat qo'shimcha hisoblash va jarima qo'lda kiritiladi.</div>
            <button type="submit" class="btn btn-primary btn-sm w-100" id="modal-qoshimcha-btn">
                <i class="bi bi-save me-1"></i>Saqlash
            </button>
        </div>
      </form>
      <div class="modal-footer py-2 d-flex flex-column align-items-stretch gap-2" id="modal-tolash-blok">
        <div class="small text-muted">Avansdan keyin to'lanadigan (qolgan): <strong id="modal-jami"></strong> so'm</div>
        <form method="POST" id="tolashForm" class="d-flex gap-2">
            @csrf
            <select name="kassa_turi" class="form-select form-select-sm" required>
                <option value="naqd">Naqd</option>
                <option value="terminal">Terminal</option>
                <option value="bank">Bank</option>
            </select>
            <button type="submit" class="btn btn-success btn-sm text-nowrap" onclick="return confirm('Ish haqi to\'landi deb belgilansin va Harajatlar moduliga yozilsinmi?')">
                <i class="bi bi-cash-coin me-1"></i>To'lash
            </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

{{-- ─── Tab 3: Tarix ───────────────────────────────────────────── --}}
@if($tab === 'tarix')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('ish_haqi.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="tarix">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $tarixHisoblar->total() }}</span>
        </div>
        <select name="holat" class="form-select" style="width:160px" onchange="this.form.submit()">
            <option value="">Barcha holatlar</option>
            <option value="tolandi"    {{ $holat === 'tolandi'    ? 'selected' : '' }}>To'landi</option>
            <option value="hisoblangan" {{ $holat === 'hisoblangan' ? 'selected' : '' }}>Kutilmoqda</option>
        </select>
        <input type="search" name="qidiruv" class="form-control" style="width:200px" placeholder="Xodim ismi..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv || $holat)
        <a href="{{ route('ish_haqi.index', ['tab' => 'tarix']) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="ms-auto d-flex align-items-center gap-3 small">
            <span>Jami: <strong>{{ $statistika['jami'] }}</strong></span>
            <span class="text-success">To'landi: <strong>{{ $statistika['tolandi'] }}</strong></span>
            <span class="text-warning">Kutilmoqda: <strong>{{ $statistika['kutilmoqda'] }}</strong></span>
            <span class="text-info">Bu oy: <strong>{{ number_format($statistika['bu_oy'], 0, '.', ' ') }}</strong></span>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Oy</th><th class="tl">Xodim</th><th class="tl">Filial</th>
                <th>Jami</th><th class="tl">Holat</th><th class="tl">To'langan vaqt</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tarixHisoblar as $h)
            <tr>
                <td class="tl sticky-col">{{ $h->oyNomi() }}</td>
                <td class="tl">{{ $h->xodim?->ism_familiya }}</td>
                <td class="tl text-muted">{{ $h->xodim?->filial?->nomi ?? '—' }}</td>
                <td class="num fw-bold">{{ number_format($h->jami_hisoblangan, 0, '.', ' ') }}</td>
                <td class="tl">{!! $h->holatBadge() !!}</td>
                <td class="tl text-muted">{{ $h->tolangan_vaqt?->format('d.m.Y H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Yozuvlar topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($tarixHisoblar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $tarixHisoblar->firstItem() }}–{{ $tarixHisoblar->lastItem() }} / {{ $tarixHisoblar->total() }} ta</small>
    {{ $tarixHisoblar->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

{{-- ─── Tab 4: Sozlamalar ──────────────────────────────────────── --}}
@if($tab === 'sozlamalar')

{{-- Global sozlamalar — barcha xodimlarga standart bo'lib qo'llanadi --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2 fw-bold bg-dark text-white">
        <i class="bi bi-globe me-2"></i>Global sozlamalar (jami xodimlar uchun standart)
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('ish_haqi.sozlama.global_saqla') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm-3">
                <label class="form-label small fw-medium">Soliq stavkasi (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="soliq_foizi" class="form-control form-control-sm" value="{{ $globalSozlama->soliq_foizi }}" required>
            </div>
            <div class="col-sm-3">
                <label class="form-label small fw-medium">Boshqa ushlanma (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="boshqa_ushlanma_foizi" class="form-control form-control-sm" value="{{ $globalSozlama->boshqa_ushlanma_foizi }}" required>
            </div>
            <div class="col-sm-4">
                <div class="form-text mb-0">Xodim profilida boshqacha stavka belgilanmasa, shu standart qiymatlar ishlatiladi. Har oy hisoblashda joriy qiymat "suratga olinadi" — keyin o'zgartirilsa, eski oylar o'zgarmaydi.</div>
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-dark btn-sm w-100"><i class="bi bi-save me-1"></i>Saqlash</button>
            </div>
        </form>
    </div>
</div>

<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('ish_haqi.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="sozlamalar">
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" {{ (string) $filialId === (string) $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
            @endforeach
        </select>
        @endif
        <input type="search" name="qidiruv" class="form-control" style="width:200px" placeholder="Xodim ismi..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col" rowspan="2">Xodim</th>
                <th class="tl" rowspan="2">Filial</th>
                <th class="tl" rowspan="2">Rol</th>
                <th rowspan="2">Oklad</th>
                <th rowspan="2">Bonus %</th>
                <th rowspan="2">Oylik reja</th>
                <th rowspan="2">Reja min-max %</th>
                <th rowspan="2">Reja bonusi</th>
                <th colspan="2" class="grup-ushlandi">SHAXSIY STAVKA (bo'sh — global)</th>
                <th rowspan="2" style="background:linear-gradient(180deg,#64748b,#475569)">Dastlabki qoldiq</th>
                <th rowspan="2" style="width:90px"></th>
            </tr>
            <tr>
                <th class="sub-ushlandi">Soliq %</th>
                <th class="sub-ushlandi">Boshqa ushl. %</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sozlamaXodimlar as $x)
            @php $s = $x->ishHaqiSozlama; @endphp
            <tr>
                <td class="tl sticky-col fw-semibold">{{ $x->ism_familiya }}</td>
                <td class="tl text-muted">{{ $x->filial?->nomi ?? '—' }}</td>
                <td class="tl text-muted">{{ $x->rol }}</td>
                <td class="num">{{ number_format($s->oklad ?? 0, 0, '.', ' ') }}</td>
                <td class="num">{{ $s->bonus_foizi ?? 0 }}%</td>
                <td class="num">{{ number_format($s->oylik_reja_summa ?? 0, 0, '.', ' ') }}</td>
                <td class="num">{{ $s->reja_min_foizi ?? 80 }}% – {{ $s->reja_max_foizi ?? 100 }}%</td>
                <td class="num">{{ number_format($s->reja_bonus_summa ?? 0, 0, '.', ' ') }}</td>
                <td class="num">{{ $s->soliq_foizi ?? '—' }}</td>
                <td class="num">{{ $s->boshqa_ushlanma_foizi ?? '—' }}</td>
                <td class="num {{ ($s->dastlabki_qoldiq ?? 0) > 0 ? 'text-danger fw-bold' : '' }}">{{ number_format($s->dastlabki_qoldiq ?? 0, 0, '.', ' ') }}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 sozlama-tahrirlash-btn"
                        data-xodim-id="{{ $x->id }}" data-xodim-nomi="{{ $x->ism_familiya }}"
                        data-oklad="{{ $s->oklad ?? 0 }}" data-bonus-foizi="{{ $s->bonus_foizi ?? 5 }}"
                        data-oylik-reja="{{ $s->oylik_reja_summa ?? 0 }}"
                        data-reja-min-foizi="{{ $s->reja_min_foizi ?? 80 }}" data-reja-max-foizi="{{ $s->reja_max_foizi ?? 100 }}"
                        data-reja-bonus="{{ $s->reja_bonus_summa ?? 0 }}"
                        data-soliq-foizi="{{ $s->soliq_foizi ?? '' }}" data-boshqa-ushlanma-foizi="{{ $s->boshqa_ushlanma_foizi ?? '' }}"
                        data-dastlabki-qoldiq="{{ $s->dastlabki_qoldiq ?? 0 }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center text-muted py-5"><i class="bi bi-people fs-3 d-block mb-2"></i>Xodimlar topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal fade" id="sozlamaModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="sozlamaXodimNomi"></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="sozlamaForm">
        @csrf
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small fw-medium">Oylik oklad (so'm)</label>
                    <input type="number" step="0.01" min="0" name="oklad" id="sozlama-oklad" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Bonus foizi (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="bonus_foizi" id="sozlama-bonus-foizi" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Oylik savdo rejasi (so'm)</label>
                    <input type="number" step="0.01" min="0" name="oylik_reja_summa" id="sozlama-oylik-reja" class="form-control form-control-sm">
                    <div class="form-text">0 — reja belgilanmagan</div>
                </div>
                <div class="col-3">
                    <label class="form-label small fw-medium">Min % (bonus boshlanadi)</label>
                    <input type="number" step="0.01" min="0" name="reja_min_foizi" id="sozlama-reja-min-foizi" class="form-control form-control-sm" value="80">
                </div>
                <div class="col-3">
                    <label class="form-label small fw-medium">Max % (to'liq bonus)</label>
                    <input type="number" step="0.01" min="0" name="reja_max_foizi" id="sozlama-reja-max-foizi" class="form-control form-control-sm" value="100">
                </div>
                <div class="col-12">
                    <div class="form-text">Reja bajarilishi Min%dan past bo'lsa — bonus 0. Min% va Max% oralig'ida — proporsional. Max%dan yuqori (yoki teng) bo'lsa — bonus to'liq.</div>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Reja bonusi (so'm)</label>
                    <input type="number" step="0.01" min="0" name="reja_bonus_summa" id="sozlama-reja-bonus" class="form-control form-control-sm">
                    <div class="form-text">Max% bajarilganda beriladigan to'liq (maksimal) bonus summasi</div>
                </div>
                <div class="col-12"><hr class="my-1"></div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Soliq foizi (bo'sh — global: {{ $globalSozlama->soliq_foizi }}%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="soliq_foizi" id="sozlama-soliq-foizi" class="form-control form-control-sm" placeholder="Global stavkani ishlatish uchun bo'sh qoldiring">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Boshqa ushlanma % (bo'sh — global: {{ $globalSozlama->boshqa_ushlanma_foizi }}%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="boshqa_ushlanma_foizi" id="sozlama-boshqa-ushlanma-foizi" class="form-control form-control-sm" placeholder="Global stavkani ishlatish uchun bo'sh qoldiring">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Dastlabki qoldiq (so'm)</label>
                    <input type="number" step="0.01" name="dastlabki_qoldiq" id="sozlama-dastlabki-qoldiq" class="form-control form-control-sm">
                    <div class="form-text">Tizimga qo'shilishidan oldingi eski qoldiq — bir martalik kiritiladi, har oy qoldig'iga doimiy qo'shilib turadi</div>
                </div>
            </div>
        </div>
        <div class="modal-footer py-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

{{-- ─── Tab 5: Dashboard ───────────────────────────────────────── --}}
@if($tab === 'dashboard')
<div class="row g-3 mb-3 mt-1">
    <div class="col-6 col-md-3">
        <div class="stat-karta">
            <div class="son">{{ $statistika['jami_xodim'] }}</div>
            <div class="label">Jami xodim</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-karta">
            <div class="son">{{ number_format($statistika['bu_oy_jami'], 0, '.', ' ') }}</div>
            <div class="label">Bu oy jami hisoblangan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-karta">
            <div class="son text-success">{{ number_format($statistika['bu_oy_tolandi'], 0, '.', ' ') }}</div>
            <div class="label">Bu oy to'langan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-karta">
            <div class="son text-warning">{{ number_format($statistika['bu_oy_kutilmoqda'], 0, '.', ' ') }}</div>
            <div class="label">Bu oy kutilmoqda</div>
        </div>
    </div>
</div>

<h6 class="fw-bold mb-2"><i class="bi bi-trophy me-1 text-warning"></i>Bu oy reytingi (jami hisoblangan bo'yicha)</h6>
<div class="bank-wrap shadow-sm" style="max-height:calc(100vh - 420px)">
    <table class="bank-table">
        <thead>
            <tr><th class="tl sticky-col">O'rin</th><th class="tl">Xodim</th><th class="tl">Filial</th><th class="tl">Rol</th><th>Jami</th><th class="tl">Holat</th></tr>
        </thead>
        <tbody>
            @forelse($reyting as $i => $h)
            <tr>
                <td class="tl sticky-col fw-bold">{{ $i + 1 }}</td>
                <td class="tl">{{ $h->xodim?->ism_familiya }}</td>
                <td class="tl text-muted">{{ $h->xodim?->filial?->nomi ?? '—' }}</td>
                <td class="tl text-muted">{{ $h->xodim?->rol }}</td>
                <td class="num fw-bold">{{ number_format($h->jami_hisoblangan, 0, '.', ' ') }}</td>
                <td class="tl">{!! $h->holatBadge() !!}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-bar-chart fs-3 d-block mb-2"></i>Bu oy uchun hali hisob-kitob yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

</div>
@endsection

@push('scripts')
@if($tab === 'davomat')
<script>
(function () {
    const rangMap = {
        @foreach(\App\Models\XodimDavomat::ICON_HOLATLARI as $key => $info)
        '{{ $key }}': '{{ $info['rang'] }}',
        @endforeach
    };

    function rangla(sel) {
        sel.style.background = rangMap[sel.value] || '#94a3b8';
    }

    document.querySelectorAll('.davomat-select').forEach(function (sel) {
        rangla(sel);
        sel.addEventListener('change', function () { rangla(sel); });
    });
})();
</script>
@endif

@if($tab === 'hisoblash')
<script>
function hisoblaBoshla() {
    if (!confirm("Tanlangan oy uchun barcha xodimlarning ish haqi hisoblansinmi (mavjud hisoblanmagan yozuvlar qayta hisoblanadi)?")) return;
    document.getElementById('hisoblash-form').submit();
}

(function () {
    const modalEl = document.getElementById('tafsilotModal');
    const modal = new bootstrap.Modal(modalEl);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.tafsilot-btn');
        if (!btn) return;

        const id = btn.dataset.hisobId;
        document.getElementById('tafsilotXodimNomi').textContent = btn.dataset.xodim;
        document.getElementById('modal-qoshimcha').value = btn.dataset.qoshimcha || 0;
        document.getElementById('modal-qoshimcha-izoh').value = btn.dataset.qoshimchaIzoh || '';
        document.getElementById('modal-ushlanma').value = btn.dataset.ushlanma || 0;
        document.getElementById('modal-ushlanma-izoh').value = btn.dataset.ushlanmaIzoh || '';
        document.getElementById('modal-jami').textContent = btn.dataset.jami;

        const qoshimchaForm = document.getElementById('qoshimchaForm');
        const tolashForm = document.getElementById('tolashForm');
        qoshimchaForm.action = `{{ url('ish-haqi') }}/${id}/qoshimcha`;
        tolashForm.action = `{{ url('ish-haqi') }}/${id}/tola`;

        const tolangan = btn.dataset.holat === 'tolandi';
        document.getElementById('modal-tolash-blok').style.display = tolangan ? 'none' : '';
        qoshimchaForm.querySelectorAll('input, button').forEach(el => el.disabled = tolangan);

        modal.show();
    });
})();

(function () {
    const modalEl = document.getElementById('avansModal');
    const modal = new bootstrap.Modal(modalEl);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.avans-btn');
        if (!btn) return;

        document.getElementById('avansXodimNomi').textContent = btn.dataset.xodim;
        document.getElementById('avansForm').action = `{{ url('ish-haqi/avans') }}/${btn.dataset.xodimId}`;
        modal.show();
    });
})();
</script>
@endif

@if($tab === 'sozlamalar')
<script>
(function () {
    const modalEl = document.getElementById('sozlamaModal');
    const modal = new bootstrap.Modal(modalEl);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.sozlama-tahrirlash-btn');
        if (!btn) return;

        document.getElementById('sozlamaXodimNomi').textContent = btn.dataset.xodimNomi;
        document.getElementById('sozlama-oklad').value = btn.dataset.oklad;
        document.getElementById('sozlama-bonus-foizi').value = btn.dataset.bonusFoizi;
        document.getElementById('sozlama-oylik-reja').value = btn.dataset.oylikReja;
        document.getElementById('sozlama-reja-min-foizi').value = btn.dataset.rejaMinFoizi;
        document.getElementById('sozlama-reja-max-foizi').value = btn.dataset.rejaMaxFoizi;
        document.getElementById('sozlama-reja-bonus').value = btn.dataset.rejaBonus;
        document.getElementById('sozlama-soliq-foizi').value = btn.dataset.soliqFoizi || '';
        document.getElementById('sozlama-boshqa-ushlanma-foizi').value = btn.dataset.boshqaUshlanmaFoizi || '';
        document.getElementById('sozlama-dastlabki-qoldiq').value = btn.dataset.dastlabkiQoldiq;

        document.getElementById('sozlamaForm').action = `{{ url('ish-haqi/sozlama') }}/${btn.dataset.xodimId}`;

        modal.show();
    });
})();
</script>
@endif
@endpush
