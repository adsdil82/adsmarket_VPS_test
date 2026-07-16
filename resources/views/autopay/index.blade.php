@extends('layouts.app')
@section('title', 'AutoPay')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Kreditlar</a></li>
<li class="breadcrumb-item active">AutoPay</li>
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
.bank-table tbody tr.row-bogsiz td { background:#fef3c7 !important; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; font-size:.85rem; }
.jami-row th {
    background:linear-gradient(180deg,#fef9c3,#fde68a) !important; color:#7c2d12 !important;
    font-weight:800; font-size:.78rem; text-transform:none; letter-spacing:0; padding:6px 8px;
}
.jami-row th.sticky-col { background:linear-gradient(180deg,#fde68a,#fbbf24) !important; z-index:8; }
.jami-row th.num { font-family:'Roboto Mono','Courier New',monospace; }

.bank-wrap { overflow:auto; height:calc(100vh - 175px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 150px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }

.col-resizer { position:absolute; right:0; top:0; bottom:0; width:5px; cursor:col-resize; background:transparent; z-index:2; }
.col-resizer:hover, .col-resizer.resizing { background:rgba(255,255,255,.4); }

.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 12px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.check-col { width:44px; text-align:center !important; }
.check-col .form-check-input {
    width:20px; height:20px; margin:0; cursor:pointer;
    border:2px solid #ef4444; box-shadow:none;
}
.check-col .form-check-input:checked { background-color:#ef4444; border-color:#ef4444; }
.check-col .form-check-input:focus { box-shadow:0 0 0 .2rem rgba(239,68,68,.25); }
th.check-col { vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 pb-3" style="margin-top:-40px">

<div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-fill text-primary me-2"></i>AutoPay</h5>
    <div class="d-flex align-items-center gap-2">
        <span class="badge {{ $yoqilgan ? 'bg-success' : 'bg-secondary' }}">{{ $yoqilgan ? 'Yoqilgan' : "O'chirilgan" }}</span>
        @if(auth()->user()->isAdmin())
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-gear me-1"></i>Ulanishlar
            </button>
            <div class="dropdown-menu dropdown-menu-end p-2" style="min-width:260px">
                <form method="POST" action="{{ route('autopay.webhook_ulash') }}" class="mb-2"
                      onsubmit="return confirm('Post-payment webhook manzilini AutoPay\'da ro\'yxatdan o\'tkazasizmi?')">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm w-100 text-start" {{ $yoqilgan ? '' : 'disabled' }}>
                        <i class="bi bi-arrow-repeat me-1"></i>Webhook ulash
                    </button>
                </form>
                <form method="POST" action="{{ route('autopay.verification_ulash') }}"
                      onsubmit="return confirm('Prepayment verification manzilini AutoPay\'da ro\'yxatdan o\'tkazasizmi?')">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm w-100 text-start" {{ $yoqilgan ? '' : 'disabled' }}>
                        <i class="bi bi-arrow-repeat me-1"></i>Verification ulash
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@php
    $tabParams = ['filial_id' => $filialId, 'qidiruv' => $qidiruv];
@endphp

<ul class="nav nav-tabs mb-0">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'kutilayotgan' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', $tabParams + ['tab' => 'kutilayotgan']) }}">
            <i class="bi bi-hourglass-split me-1"></i>Kutilayotgan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'yuborilgan' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', $tabParams + ['tab' => 'yuborilgan']) }}">
            <i class="bi bi-send-check me-1"></i>Yuborilgan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'tarix' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', $tabParams + ['tab' => 'tarix']) }}">
            <i class="bi bi-clock-history me-1"></i>Tranzaksiyalar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'kartalar' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', ['tab' => 'kartalar']) }}">
            <i class="bi bi-credit-card-2-front me-1"></i>Kartalar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'processing' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', ['tab' => 'processing']) }}">
            <i class="bi bi-cpu me-1"></i>Processing
            <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">pullik</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'monitoring' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', ['tab' => 'monitoring']) }}">
            <i class="bi bi-activity me-1"></i>Monitoring
            <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">pullik</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'scoring' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', ['tab' => 'scoring']) }}">
            <i class="bi bi-graph-up-arrow me-1"></i>Scoring
            <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">pullik</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'egov' ? 'active fw-semibold' : '' }}"
           href="{{ route('autopay.index', ['tab' => 'egov']) }}">
            <i class="bi bi-bank2 me-1"></i>E-GOV
            <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem">pullik</span>
        </a>
    </li>
</ul>

{{-- ─── Tab 1: Kutilayotgan ──────────────────────────────────── --}}
@if($tab === 'kutilayotgan')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('autopay.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="kutilayotgan">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $kreditlar->total() }}</span>
        </div>
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $filial)
            <option value="{{ $filial->id }}" {{ (string) $filialId === (string) $filial->id ? 'selected' : '' }}>{{ $filial->nomi }}</option>
            @endforeach
        </select>
        @endif
        <input type="search" name="qidiruv" class="form-control" style="width:220px" placeholder="Mijoz yoki shartnoma..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv)
        <a href="{{ route('autopay.index', ['tab' => 'kutilayotgan', 'filial_id' => $filialId]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm px-2" style="height:32px" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Ustunlarni ko'rsatish/yashirish">
                <i class="bi bi-layout-three-columns"></i>
            </button>
            <ul class="dropdown-menu p-2" style="font-size:.8rem;min-width:190px;max-height:340px;overflow:auto">
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="xodim" data-default="0"> Xodim</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="mijoz" data-default="1"> Mijoz</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="filial" data-default="1"> Filial</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="telefon" data-default="0"> Telefon</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="manzil" data-default="0"> Manzil</label></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="boshlanish" data-default="0"> Boshlanish</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="tugash" data-default="0"> Tugash</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="muddat" data-default="0"> Muddat</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="jami" data-default="0"> Tovar summasi</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="oldindan" data-default="0"> Oldindan</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="kredit" data-default="0"> Kredit summa</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-1" data-col="tolangan" data-default="0"> Jami to'langan</label></li>
            </ul>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="fw-bold text-danger" id="tanlanganSoniHeader" style="font-size:1rem">0</span>
            <button type="submit" form="bulkForm" class="btn btn-success btn-sm" id="bulkYuborBtn" disabled
                    onclick="return confirm('Tanlangan shartnomalarni AutoPay\'ga yuborasizmi?')">
                <i class="bi bi-send me-1"></i>Tanlanganlarni yuborish
            </button>
        </div>
    </form>
</div>

<form method="POST" action="{{ route('autopay.yuborish_bulk') }}" id="bulkForm">
@csrf
<div class="bank-wrap shadow-sm" id="wrap-1">
    <table class="bank-table" id="table-1">
        <thead>
            <tr>
                <th class="check-col sticky-col"><input type="checkbox" class="form-check-input" id="hammaBelgila"></th>
                <th class="tl">Shartnoma</th>
                <th class="tl col-xodim d-none">Xodim</th>
                <th class="tl col-mijoz">Mijoz</th>
                <th class="col-filial">Filial</th>
                <th class="tl col-telefon d-none">Telefon</th>
                <th class="tl col-manzil d-none">Manzil</th>
                <th class="tl col-boshlanish d-none">Boshlanish</th>
                <th class="tl col-tugash d-none">Tugash</th>
                <th class="col-muddat d-none">Muddat</th>
                <th class="col-jami d-none">Tovar summasi</th>
                <th class="col-oldindan d-none">Oldindan</th>
                <th class="col-kredit d-none">Kredit summa</th>
                <th class="col-tolangan d-none">Jami to'langan</th>
                <th>Qoldiq qarz</th>
                <th>Kechikkan summa</th>
                <th class="tl">AutoPay holati</th>
                <th style="width:70px"></th>
            </tr>
            <tr class="jami-row">
                <th class="tl sticky-col" colspan="2">JAMI ({{ $kreditlar->total() }} ta)</th>
                <th class="col-xodim d-none"></th>
                <th class="col-mijoz"></th>
                <th class="col-filial"></th>
                <th class="tl col-telefon d-none"></th>
                <th class="tl col-manzil d-none"></th>
                <th class="tl col-boshlanish d-none"></th>
                <th class="tl col-tugash d-none"></th>
                <th class="col-muddat d-none"></th>
                <th class="num col-jami d-none">{{ number_format($jamiSummalar->jami_summa ?? 0, 0, '.', ' ') }}</th>
                <th class="num col-oldindan d-none">{{ number_format($jamiSummalar->boshlangich_tolov ?? 0, 0, '.', ' ') }}</th>
                <th class="num col-kredit d-none">{{ number_format($jamiSummalar->kredit_summa ?? 0, 0, '.', ' ') }}</th>
                <th class="num col-tolangan d-none">{{ number_format($jamiSummalar->jami_tolangan ?? 0, 0, '.', ' ') }}</th>
                <th class="num">{{ number_format($qoldiqJami ?? 0, 0, '.', ' ') }}</th>
                <th class="num">{{ number_format($kechikkanJami ?? 0, 0, '.', ' ') }}</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($kreditlar as $kredit)
            @php $sh = $kredit->autopayShartnoma; @endphp
            <tr>
                <td class="check-col sticky-col">
                    @if(!$sh || $sh->holat !== 'faol')
                    <input type="checkbox" class="form-check-input kredit-check" name="kredit_ids[]" value="{{ $kredit->id }}" {{ $kredit->mijoz?->pinfl ? '' : 'disabled' }}>
                    @endif
                </td>
                <td class="tl"><a href="{{ route('kreditlar.show', $kredit) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $kredit->shartnoma_raqam }}</a></td>
                <td class="tl text-muted col-xodim d-none">{{ $kredit->xodim?->ism_familiya ?? '—' }}</td>
                <td class="tl col-mijoz">
                    {{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}
                    @unless($kredit->mijoz?->pinfl)
                    <span class="badge-modern" style="background:#f59e0b;color:#000">PINFL yo'q</span>
                    @endunless
                </td>
                <td class="tl text-muted col-filial">{{ $kredit->filial?->nomi }}</td>
                <td class="tl text-muted col-telefon d-none">{{ $kredit->mijoz?->telefon }}</td>
                <td class="tl text-muted col-manzil d-none">{{ Str::limit($kredit->mijoz?->manzil, 30) ?: '—' }}</td>
                <td class="tl text-muted col-boshlanish d-none">{{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="tl text-muted col-tugash d-none">{{ $kredit->tugash_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-center col-muddat d-none"><span class="badge-modern" style="background:#e0e7ff;color:#3730a3">{{ $kredit->muddati_oy }} oy</span></td>
                <td class="num col-jami d-none">{{ number_format($kredit->jami_summa, 0, '.', ' ') }}</td>
                <td class="num col-oldindan d-none">{{ number_format($kredit->boshlangich_tolov, 0, '.', ' ') }}</td>
                <td class="num col-kredit d-none">{{ number_format($kredit->kredit_summa, 0, '.', ' ') }}</td>
                <td class="num col-tolangan d-none" style="color:#16a34a">{{ number_format($kredit->boshlangich_tolov + $kredit->tolov_qilingan, 0, '.', ' ') }}</td>
                <td class="num" style="color:#dc2626">{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</td>
                <td class="num" style="color:#dc2626">{{ number_format($kredit->kechikkan_summa ?? 0, 0, '.', ' ') }}</td>
                <td class="tl">
                    @if(!$sh)
                    <span class="badge-modern" style="background:#64748b;color:#fff">Yuborilmagan</span>
                    @elseif($sh->holat === 'faol')
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Faol (auto)</span>
                    @elseif($sh->holat === 'toxtatilgan')
                    <span class="badge-modern" style="background:#f59e0b;color:#000">To'xtatilgan</span>
                    @elseif($sh->holat === 'ochirilgan')
                    <span class="badge-modern" style="background:#1e293b;color:#fff">O'chirilgan</span>
                    @else
                    <span class="badge-modern" style="background:#ef4444;color:#fff" title="{{ $sh->xato_matni }}">Xato</span>
                    @endif
                </td>
                <td class="text-center">
                    @if(!$sh || in_array($sh->holat, ['ochirilgan', 'xato']))
                    <button type="submit" formaction="{{ route('autopay.yuborish', $kredit) }}" class="btn btn-outline-success btn-sm py-0 px-1"
                            {{ $yoqilgan && $kredit->mijoz?->pinfl ? '' : 'disabled' }}
                            onclick="return confirm('Bu shartnomani AutoPay\'ga yuborib, avtomatik yechishni yoqasizmi?')" title="Yuborish">
                        <i class="bi bi-send"></i>
                    </button>
                    @elseif($sh->holat === 'toxtatilgan')
                    <button type="submit" formaction="{{ route('autopay.yoqish', $sh) }}" class="btn btn-outline-success btn-sm py-0 px-1"
                            {{ $yoqilgan ? '' : 'disabled' }}
                            onclick="return confirm('Avtomatik yechishni qayta yoqasizmi? Shartnoma AutoPay\'da allaqachon mavjud.')" title="Qayta yoqish">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    @else
                    <button type="submit" formaction="{{ route('autopay.toxtatish', $sh) }}" class="btn btn-outline-warning btn-sm py-0 px-1"
                            onclick="return confirm('Avtomatik yechishni to\'xtatasizmi?')" title="To'xtatish">
                        <i class="bi bi-pause-fill"></i>
                    </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="18" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Muddati o'tgan shartnomalar yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</form>
@if($kreditlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $kreditlar->firstItem() }}–{{ $kreditlar->lastItem() }} / {{ $kreditlar->total() }} ta</small>
    {{ $kreditlar->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

{{-- ─── Tab 2: Yuborilgan ────────────────────────────────────── --}}
@if($tab === 'yuborilgan')
<div class="filter-bar mb-0 d-flex align-items-end flex-wrap gap-2">
    <form method="GET" action="{{ route('autopay.index') }}" class="d-flex align-items-end flex-wrap gap-2 flex-grow-1">
        <input type="hidden" name="tab" value="yuborilgan">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $shartnomalar->total() }}</span>
        </div>
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $filial)
            <option value="{{ $filial->id }}" {{ (string) $filialId === (string) $filial->id ? 'selected' : '' }}>{{ $filial->nomi }}</option>
            @endforeach
        </select>
        @endif
        <select name="holat" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="">Barcha holatlar</option>
            <option value="faol" {{ $holat === 'faol' ? 'selected' : '' }}>Faol (auto)</option>
            <option value="toxtatilgan" {{ $holat === 'toxtatilgan' ? 'selected' : '' }}>To'xtatilgan</option>
            <option value="xato" {{ $holat === 'xato' ? 'selected' : '' }}>Xato</option>
            <option value="ochirilgan" {{ $holat === 'ochirilgan' ? 'selected' : '' }}>O'chirilgan</option>
        </select>
        <select name="manba" class="form-select" style="width:140px" onchange="this.form.submit()">
            <option value="">Barcha manbalar</option>
            <option value="api" {{ $manba === 'api' ? 'selected' : '' }}>API</option>
            <option value="qolda" {{ $manba === 'qolda' ? 'selected' : '' }}>Qo'lda</option>
        </select>
        <input type="search" name="qidiruv" class="form-control" style="width:200px" placeholder="Mijoz yoki loan_id..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv || $holat || $manba)
        <a href="{{ route('autopay.index', ['tab' => 'yuborilgan', 'filial_id' => $filialId]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm px-2" style="height:32px" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Ustunlarni ko'rsatish/yashirish">
                <i class="bi bi-layout-three-columns"></i>
            </button>
            <ul class="dropdown-menu p-2" style="font-size:.8rem;min-width:190px;max-height:340px;overflow:auto">
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="mijoz" data-default="1"> Mijoz</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="filial" data-default="1"> Filial</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="sana" data-default="1"> Yuborilgan sana</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="telefon" data-default="0"> Telefon</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="manzil" data-default="0"> Manzil</label></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="boshlanish" data-default="0"> Boshlanish</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="tugash" data-default="0"> Tugash</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="muddat" data-default="0"> Muddat</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="jami" data-default="0"> Tovar summasi</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="kredit" data-default="0"> Kredit summa</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-2" data-col="tolangan" data-default="0"> Jami to'langan</label></li>
            </ul>
        </div>
    </form>
    <form method="POST" action="{{ route('autopay.tozalash') }}"
          onsubmit="return confirm('Hech qachon biriktirilmagan (qo\'lda, bo\'sh) shartnomalar va ularning tranzaksiyalarini bazadan butunlay o\'chirasizmi? AutoPay\'ning o\'ziga tegmaydi, keyingi sinxronlashda qayta paydo bo\'lishi mumkin.')">
        @csrf
        <button class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash3 me-1"></i>Tozalash
        </button>
    </form>
    <form method="POST" action="{{ route('autopay.qarz_sinxron') }}"
          onsubmit="return confirm('Barcha faol shartnomalarning qarzini bizning bazadagi joriy qoldiq bilan AutoPay tomonida moslashtirasizmi?')">
        @csrf
        <button class="btn btn-outline-secondary btn-sm" {{ $yoqilgan ? '' : 'disabled' }}>
            <i class="bi bi-cash-coin me-1"></i>Qarzlarni sinxronlash
        </button>
    </form>
    <form method="POST" action="{{ route('autopay.sinxronlash') }}"
          onsubmit="return confirm('AutoPay hisobidagi barcha shartnomalarni tekshirib, bizning tizim yaratgan (NP- bilan boshlanadigan) shartnomalarni haqiqiy holatga moslashtiramiz. Davom etasizmi?')">
        @csrf
        <button class="btn btn-outline-primary btn-sm" {{ $yoqilgan ? '' : 'disabled' }}>
            <i class="bi bi-arrow-repeat me-1"></i>Sinxronlash
        </button>
    </form>
    <form method="POST" action="{{ route('autopay.ochirish_bulk') }}" id="ochirishBulkForm"
          onsubmit="return confirm('Tanlangan shartnomalarni AutoPay\'dan butunlay o\'chirasizmi? Amal qaytarilmaydi.')">
        @csrf
        <button type="submit" class="btn btn-outline-danger btn-sm" id="bulkOchirishBtn" disabled>
            <i class="bi bi-x-circle me-1"></i>Tanlanganlarni o'chirish (<span id="tanlanganSoniYuborilgan">0</span>)
        </button>
    </form>
</div>

<div class="bank-wrap shadow-sm" id="wrap-2">
    <table class="bank-table" id="table-2">
        <thead>
            <tr>
                <th class="check-col sticky-col"><input type="checkbox" class="form-check-input" id="hammaBelgilaYuborilgan"></th>
                <th class="tl">Loan ID</th>
                <th class="tl col-mijoz">Mijoz</th>
                <th class="tl">PINFL</th>
                <th class="col-filial">Filial</th>
                <th class="tl col-telefon d-none">Telefon</th>
                <th class="tl col-manzil d-none">Manzil</th>
                <th class="tl col-boshlanish d-none">Boshlanish</th>
                <th class="tl col-tugash d-none">Tugash</th>
                <th class="col-muddat d-none">Muddat</th>
                <th class="col-jami d-none">Tovar summasi</th>
                <th class="col-kredit d-none">Kredit summa</th>
                <th class="col-tolangan d-none">Jami to'langan</th>
                <th>So'nggi qarz</th>
                <th class="tl">Manba</th>
                <th class="tl">Holat</th>
                <th class="tl col-sana">Yuborilgan</th>
                <th style="width:210px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($shartnomalar as $sh)
            <tr class="{{ $sh->biriktirilmaganmi() ? 'row-bogsiz' : '' }}">
                <td class="check-col sticky-col">
                    @if($sh->loan_id)
                    <input type="checkbox" class="form-check-input shartnoma-check" name="shartnoma_ids[]" value="{{ $sh->id }}" form="ochirishBulkForm">
                    @endif
                </td>
                <td class="tl"><code class="small">{{ $sh->loan_id }}</code></td>
                <td class="tl col-mijoz">
                    @if($sh->mijoz)
                        {{ $sh->mijoz->familiya }} {{ $sh->mijoz->ism }}
                    @elseif($sh->kredit?->mijoz)
                        {{ $sh->kredit->mijoz->familiya }} {{ $sh->kredit->mijoz->ism }}
                    @else
                        <span class="text-muted" title="Bu yozuv lokal mijoz bazasiga bog'lanmagan">—</span>
                    @endif
                </td>
                <td class="tl text-muted">{{ $sh->pinfl ?: '—' }}</td>
                <td class="tl text-muted col-filial">{{ $sh->kredit?->filial?->nomi }}</td>
                <td class="tl text-muted col-telefon d-none">{{ $sh->mijoz?->telefon }}</td>
                <td class="tl text-muted col-manzil d-none">{{ Str::limit($sh->mijoz?->manzil, 30) ?: '—' }}</td>
                <td class="tl text-muted col-boshlanish d-none">{{ $sh->kredit?->boshlanish_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="tl text-muted col-tugash d-none">{{ $sh->kredit?->tugash_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-center col-muddat d-none">{{ $sh->kredit ? $sh->kredit->muddati_oy.' oy' : '—' }}</td>
                <td class="num col-jami d-none">{{ $sh->kredit ? number_format($sh->kredit->jami_summa, 0, '.', ' ') : '—' }}</td>
                <td class="num col-kredit d-none">{{ $sh->kredit ? number_format($sh->kredit->kredit_summa, 0, '.', ' ') : '—' }}</td>
                <td class="num col-tolangan d-none" style="color:#16a34a">{{ $sh->kredit ? number_format($sh->kredit->boshlangich_tolov + $sh->kredit->tolov_qilingan, 0, '.', ' ') : '—' }}</td>
                <td class="num">{{ number_format($sh->oxirgi_debt, 0, '.', ' ') }}</td>
                <td class="tl">
                    @if($sh->manba === 'qolda')
                        @if($sh->reg_kredit_id)
                        <span class="badge-modern" style="background:#0891b2;color:#fff" title="Qo'lda yaratilgan, biriktirilgan">Qo'lda</span>
                        @else
                        <span class="badge-modern" style="background:#94a3b8;color:#fff" title="Hali bizning tizimga biriktirilmagan">Qo'lda — bo'sh</span>
                        @endif
                    @else
                    <span class="badge-modern" style="background:#3b82f6;color:#fff">API</span>
                    @endif
                </td>
                <td class="tl">
                    @if($sh->holat === 'faol')
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Faol (auto)</span>
                    @elseif($sh->holat === 'toxtatilgan')
                    <span class="badge-modern" style="background:#f59e0b;color:#000">To'xtatilgan</span>
                    @elseif($sh->holat === 'ochirilgan')
                    <span class="badge-modern" style="background:#1e293b;color:#fff">O'chirilgan</span>
                    @else
                    <span class="badge-modern" style="background:#ef4444;color:#fff" title="{{ $sh->xato_matni }}">Xato</span>
                    @endif
                </td>
                <td class="tl text-muted col-sana">{{ $sh->yuborilgan_vaqt?->format('d.m.Y H:i') }}</td>
                <td class="text-center">
                    @if($sh->biriktirilmaganmi())
                    <button type="button" class="btn btn-primary btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#biriktirModal{{ $sh->id }}">
                        <i class="bi bi-link-45deg me-1"></i>Biriktirish
                    </button>

                    <div class="modal fade" id="biriktirModal{{ $sh->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('autopay.biriktirish', $sh) }}" class="modal-content biriktir-form">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">Shartnomaga biriktirish — {{ $sh->loan_id }} ({{ number_format($sh->oxirgi_debt, 0, '.', ' ') }} so'm)</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label small">Shartnoma raqami yoki mijoz ismi bo'yicha qidiring</label>
                                    <input type="text" class="form-control biriktir-qidiruv mb-2" placeholder="Kamida 2 ta belgi..." autocomplete="off">
                                    <input type="hidden" name="kredit_id" class="biriktir-kredit-id" required>
                                    <div class="biriktir-natija list-group" style="max-height:220px;overflow:auto"></div>
                                    <div class="biriktir-tanlangan small text-success mt-2"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                                    <button type="submit" class="btn btn-primary btn-sm biriktir-submit" disabled>Biriktirish</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    @if($sh->kredit)
                    <a href="{{ route('kreditlar.show', $sh->kredit) }}" class="btn btn-outline-secondary btn-sm py-0 px-1" title="Shartnoma"><i class="bi bi-eye"></i></a>
                    @endif
                    @if($sh->holat === 'faol')
                    <form method="POST" action="{{ route('autopay.toxtatish', $sh) }}" class="d-inline"
                          onsubmit="return confirm('Avtomatik yechishni to\'xtatasizmi?')">
                        @csrf
                        <button class="btn btn-outline-warning btn-sm py-0 px-1" title="To'xtatish"><i class="bi bi-pause-fill"></i></button>
                    </form>
                    @elseif($sh->holat === 'toxtatilgan')
                    <form method="POST" action="{{ route('autopay.yoqish', $sh) }}" class="d-inline"
                          onsubmit="return confirm('Avtomatik yechishni qayta yoqasizmi?')">
                        @csrf
                        <button class="btn btn-outline-success btn-sm py-0 px-1" {{ $yoqilgan ? '' : 'disabled' }} title="Qayta yoqish"><i class="bi bi-play-fill"></i></button>
                    </form>
                    @endif
                    @if(!in_array($sh->holat, ['ochirilgan', 'xato']))
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" data-bs-toggle="modal" data-bs-target="#tahrirModal{{ $sh->id }}" title="Tahrirlash">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="{{ route('autopay.ochirish', $sh) }}" class="d-inline"
                          onsubmit="return confirm('Shartnomani AutoPay\'dan butunlay o\'chirasizmi? Bu qaytarib bo\'lmaydi.')">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm py-0 px-1" title="O'chirish"><i class="bi bi-trash"></i></button>
                    </form>

                    <div class="modal fade" id="tahrirModal{{ $sh->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('autopay.tahrirlash', $sh) }}" class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">Shartnomani tahrirlash — {{ $sh->loan_id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label small">Qarz summasi (so'm)</label>
                                        <input type="number" step="0.01" name="debt" class="form-control" value="{{ $sh->oxirgi_debt }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Izoh (info)</label>
                                        <input type="text" name="info" class="form-control" maxlength="255"
                                               value="{{ 'NasiyaPro shartnoma ' . $sh->kredit?->shartnoma_raqam }}">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Saqlash</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="18" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Hali hech qanday shartnoma yuborilmagan</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($shartnomalar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $shartnomalar->firstItem() }}–{{ $shartnomalar->lastItem() }} / {{ $shartnomalar->total() }} ta</small>
    {{ $shartnomalar->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

{{-- ─── Tab 3: To'lovlar tarixi ──────────────────────────────── --}}
@if($tab === 'tarix')
<div class="filter-bar mb-0 d-flex align-items-end flex-wrap gap-2">
    <form method="GET" action="{{ route('autopay.index') }}" class="d-flex align-items-end flex-wrap gap-2 flex-grow-1">
        <input type="hidden" name="tab" value="tarix">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $tranzaksiyalar->total() }}</span>
        </div>
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $filial)
            <option value="{{ $filial->id }}" {{ (string) $filialId === (string) $filial->id ? 'selected' : '' }}>{{ $filial->nomi }}</option>
            @endforeach
        </select>
        @endif
        <select name="davr" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="bugun" {{ $davr === 'bugun' ? 'selected' : '' }}>Bugun</option>
            <option value="shu_oy" {{ $davr === 'shu_oy' ? 'selected' : '' }}>Shu oy</option>
            <option value="otgan_oy" {{ $davr === 'otgan_oy' ? 'selected' : '' }}>O'tgan oy</option>
            <option value="barchasi" {{ $davr === 'barchasi' ? 'selected' : '' }}>Barchasi</option>
        </select>
        <select name="manba" class="form-select" style="width:140px" onchange="this.form.submit()">
            <option value="">Barcha manbalar</option>
            <option value="api" {{ $manba === 'api' ? 'selected' : '' }}>API</option>
            <option value="qolda" {{ $manba === 'qolda' ? 'selected' : '' }}>Qo'lda</option>
        </select>
        <input type="search" name="qidiruv" class="form-control" style="width:200px" placeholder="Loan ID..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv || $davr !== 'bugun' || $manba)
        <a href="{{ route('autopay.index', ['tab' => 'tarix', 'filial_id' => $filialId]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm px-2" style="height:32px" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Ustunlarni ko'rsatish/yashirish">
                <i class="bi bi-layout-three-columns"></i>
            </button>
            <ul class="dropdown-menu p-2" style="font-size:.8rem;min-width:170px">
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-3" data-col="mijoz" data-default="1"> Mijoz</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-3" data-col="ext" data-default="1"> Ext ID</label></li>
            </ul>
        </div>
    </form>
    <form method="POST" action="{{ route('autopay.sinxronlash_tranzaksiya') }}"
          onsubmit="return confirm('AutoPay\'dan tanlangan davr ({{ $davr }}) bo\'yicha tranzaksiyalarni sinxronlaysizmi?')">
        @csrf
        <input type="hidden" name="davr" value="{{ $davr }}">
        <button class="btn btn-outline-primary btn-sm" {{ $yoqilgan ? '' : 'disabled' }}>
            <i class="bi bi-arrow-repeat me-1"></i>Sinxronlash
        </button>
    </form>
</div>

<div class="bank-wrap shadow-sm" id="wrap-3">
    <table class="bank-table" id="table-3">
        <thead>
            <tr>
                <th class="tl">Sana</th>
                <th class="tl sticky-col">Loan ID</th>
                <th class="tl col-mijoz">Mijoz</th>
                <th class="tl">PINFL</th>
                <th>Summa</th>
                <th class="tl">Holat</th>
                <th class="tl">Manba</th>
                <th class="tl">Karta</th>
                <th class="tl col-ext">Ext ID</th>
                <th style="width:170px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tranzaksiyalar as $tr)
            @php $bogsiz = $tr->shartnoma && $tr->shartnoma->manba === 'qolda' && !$tr->shartnoma->reg_kredit_id; @endphp
            <tr class="{{ $bogsiz ? 'row-bogsiz' : '' }}">
                <td class="tl text-muted">{{ $tr->sana?->format('d.m.Y H:i') }}</td>
                <td class="tl sticky-col"><code class="small">{{ $tr->shartnoma?->loan_id }}</code></td>
                <td class="tl col-mijoz">
                    @if($tr->shartnoma?->mijoz)
                        {{ $tr->shartnoma->mijoz->familiya }} {{ $tr->shartnoma->mijoz->ism }}
                    @elseif($tr->shartnoma?->kredit?->mijoz)
                        {{ $tr->shartnoma->kredit->mijoz->familiya }} {{ $tr->shartnoma->kredit->mijoz->ism }}
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="tl text-muted">{{ $tr->shartnoma?->pinfl ?: '—' }}</td>
                <td class="num">{{ number_format($tr->summa, 0, '.', ' ') }}</td>
                <td class="tl">
                    @if($tr->holat === 'success')
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Muvaffaqiyatli</span>
                    @else
                    <span class="badge-modern" style="background:#64748b;color:#fff">Bekor qilingan</span>
                    @endif
                </td>
                <td class="tl">
                    @if(!$tr->shartnoma)
                    <span class="badge-modern" style="background:#94a3b8;color:#fff">—</span>
                    @elseif($tr->shartnoma->manba === 'qolda')
                        @if($tr->shartnoma->reg_kredit_id)
                        <span class="badge-modern" style="background:#0891b2;color:#fff" title="Qo'lda yaratilgan, biriktirilgan">Qo'lda</span>
                        @else
                        <span class="badge-modern" style="background:#f59e0b;color:#000" title="Hali biriktirilmagan">Qo'lda — bo'sh</span>
                        @endif
                    @else
                    <span class="badge-modern" style="background:#3b82f6;color:#fff">API</span>
                    @endif
                </td>
                <td class="tl text-muted"><code class="small">{{ $tr->karta_pan ?: '—' }}</code></td>
                <td class="tl text-muted col-ext text-truncate" style="max-width:220px" title="{{ $tr->ext_id }}">{{ $tr->ext_id }}</td>
                <td class="text-center">
                    @if($tr->tulov_id && $tr->shartnoma?->kredit)
                    <a href="{{ route('kreditlar.tulov.kvitansiya', [$tr->shartnoma->kredit, $tr->tulov_id]) }}" class="btn btn-outline-secondary btn-sm py-0 px-1" title="Kvitansiya"><i class="bi bi-receipt"></i></a>
                    <form method="POST" action="{{ route('autopay.tranzaksiya.bekor_qilish', $tr) }}" class="d-inline"
                          onsubmit="return confirm('To\'lovni bekor qilasizmi? AutoPay\'dan ham, bizning tizimdan ham qaytariladi (3 oydan eski tranzaksiyalar bekor qilinmaydi).')">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm py-0 px-1" title="Bekor qilish"><i class="bi bi-arrow-counterclockwise"></i></button>
                    </form>
                    @elseif($bogsiz)
                    <button type="button" class="btn btn-primary btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#biriktirModalTr{{ $tr->id }}">
                        <i class="bi bi-link-45deg me-1"></i>Biriktirish
                    </button>
                    <div class="modal fade" id="biriktirModalTr{{ $tr->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="{{ route('autopay.biriktirish', $tr->shartnoma) }}" class="modal-content biriktir-form">
                                @csrf
                                <div class="modal-header">
                                    <h6 class="modal-title">Shartnomaga biriktirish — {{ $tr->shartnoma->loan_id }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label small">Shartnoma raqami yoki mijoz ismi bo'yicha qidiring</label>
                                    <input type="text" class="form-control biriktir-qidiruv mb-2" placeholder="Kamida 2 ta belgi..." autocomplete="off">
                                    <input type="hidden" name="kredit_id" class="biriktir-kredit-id" required>
                                    <div class="biriktir-natija list-group" style="max-height:220px;overflow:auto"></div>
                                    <div class="biriktir-tanlangan small text-success mt-2"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                                    <button type="submit" class="btn btn-primary btn-sm biriktir-submit" disabled>Biriktirish</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <span class="text-muted small">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Tranzaksiyalar topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($tranzaksiyalar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $tranzaksiyalar->firstItem() }}–{{ $tranzaksiyalar->lastItem() }} / {{ $tranzaksiyalar->total() }} ta</small>
    {{ $tranzaksiyalar->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

{{-- ─── Tab 4: Kartalar ──────────────────────────────────────── --}}
@if($tab === 'kartalar')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('autopay.index') }}" id="kartaForm" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="kartalar">
        <input type="hidden" name="mijoz_id" id="kartaMijozId" value="{{ $tanlanganMijoz?->id }}">
        <div style="width:320px;position:relative">
            <label class="form-label small mb-1" style="color:#1e3a8a">Mijoz (ism, familiya yoki telefon)</label>
            <input type="text" class="form-control karta-mijoz-qidiruv" autocomplete="off"
                   placeholder="Qidirish uchun yozing..."
                   value="{{ $tanlanganMijoz ? $tanlanganMijoz->familiya.' '.$tanlanganMijoz->ism : '' }}">
            <div class="karta-mijoz-natija list-group" style="position:absolute;z-index:20;width:100%;max-height:220px;overflow:auto"></div>
        </div>
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($tanlanganMijoz)
        <a href="{{ route('autopay.index', ['tab' => 'kartalar']) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>
    @if($tanlanganMijoz?->pinfl)
    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kartaRoyxatModal">
        <i class="bi bi-plus-circle me-1"></i>Yangi karta qo'shish
    </button>
    @endif
</div>

@if($tanlanganMijoz?->pinfl)
<div class="modal fade" id="kartaRoyxatModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('autopay.karta_royxat') }}" class="modal-content">
            @csrf
            <input type="hidden" name="mijoz_id" value="{{ $tanlanganMijoz->id }}">
            <div class="modal-header">
                <h6 class="modal-title">Yangi karta qo'shish — {{ $tanlanganMijoz->familiya }} {{ $tanlanganMijoz->ism }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <div>
                    <label class="form-label small mb-1">Karta turi</label>
                    <select name="type" class="form-select karta-turi-select" required>
                        <option value="uzcard">Uzcard</option>
                        <option value="humo">Humo</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Karta raqami (16 xonali)</label>
                    <input type="text" name="card_number" class="form-control" maxlength="16" pattern="\d{16}" placeholder="8600123412341234" required>
                </div>
                <div>
                    <label class="form-label small mb-1">Amal muddati (kartada yozilganidek)</label>
                    <input type="text" name="expire" class="form-control" maxlength="5" pattern="\d{2}/\d{2}" placeholder="MM/YY" required>
                </div>
                <div class="karta-humo-telefon d-none">
                    <label class="form-label small mb-1">Telefon (Humo uchun, OTP shu raqamga boradi)</label>
                    <input type="text" name="phone" class="form-control" placeholder="+998901234567">
                </div>
                <div class="alert alert-info small mb-0 py-2"><i class="bi bi-info-circle me-1"></i>Karta ma'lumotlari yuborilgach, mijozga OTP kod keladi — keyingi oynada shuni kiritasiz.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send me-1"></i>OTP yuborish</button>
            </div>
        </form>
    </div>
</div>
@if(session('karta_ext'))
<div class="modal fade" id="kartaOtpModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('autopay.karta_tasdiq') }}" class="modal-content">
            @csrf
            <input type="hidden" name="mijoz_id" value="{{ $tanlanganMijoz->id }}">
            <input type="hidden" name="ext" value="{{ session('karta_ext') }}">
            <input type="hidden" name="type" value="{{ session('karta_type') }}">
            <div class="modal-header">
                <h6 class="modal-title">OTP kodni tasdiqlash</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <p class="small text-muted mb-0">
                    @if(session('karta_telefon_mask'))
                    {{ session('karta_telefon_mask') }} raqamiga yuborilgan kodni kiriting.
                    @else
                    Mijozga yuborilgan OTP kodni kiriting.
                    @endif
                </p>
                <input type="text" name="otp_code" class="form-control" maxlength="6" pattern="\d{4,6}" placeholder="123456" required autofocus>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Tasdiqlash</button>
            </div>
        </form>
    </div>
</div>
@endif
@endif

<div class="bank-wrap shadow-sm" id="wrap-4" style="height:calc(100vh - 260px)">
    @if(!$tanlanganMijoz)
    <div class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Kartalarni ko'rish uchun mijozni qidiring va tanlang</div>
    @elseif(!$tanlanganMijoz->pinfl)
    <div class="text-center text-muted py-5"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>{{ $tanlanganMijoz->familiya }} {{ $tanlanganMijoz->ism }} uchun PINFL kiritilmagan</div>
    @elseif(!$kartaNatija['success'])
    <div class="text-center text-danger py-5"><i class="bi bi-x-circle fs-3 d-block mb-2"></i>AutoPay xatosi: {{ $kartaNatija['error'] }}</div>
    @else
    <table class="bank-table" id="table-4">
        <thead>
            <tr>
                <th class="tl">Turi</th>
                <th class="tl">Karta raqami</th>
                <th class="tl">Egasi</th>
                <th class="tl">Telefon</th>
                <th>Auto</th>
                <th>Tekshirilgan</th>
                <th>Bloklangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $uzcard = $kartaNatija['result']['uzcard']['data'] ?? [];
                $humo = $kartaNatija['result']['humo']['data'] ?? [];
                $barchaKartalar = collect($uzcard)->map(fn($k) => $k + ['turi' => 'Uzcard'])
                    ->merge(collect($humo)->map(fn($k) => $k + ['turi' => 'Humo']));
            @endphp
            @forelse($barchaKartalar as $k)
            <tr>
                <td class="tl"><span class="badge-modern" style="background:{{ $k['turi'] === 'Uzcard' ? '#0891b2' : '#7c3aed' }};color:#fff">{{ $k['turi'] }}</span></td>
                <td class="tl"><code class="small">{{ $k['pan'] ?? '—' }}</code></td>
                <td class="tl">{{ $k['owner'] ?? $k['card_owner'] ?? '—' }}</td>
                <td class="tl text-muted">{{ $k['phone'] ?? $k['card_phone'] ?? '—' }}</td>
                <td class="text-center">
                    @if($k['auto'] ?? false)
                    <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                    <i class="bi bi-x-circle text-muted"></i>
                    @endif
                </td>
                <td class="text-center">
                    @if($k['is_verified'] ?? false)
                    <i class="bi bi-check-circle-fill text-success"></i>
                    @else
                    <i class="bi bi-x-circle text-muted"></i>
                    @endif
                </td>
                <td class="text-center">
                    @if($k['is_blocked'] ?? false)
                    <span class="badge-modern" style="background:#ef4444;color:#fff" title="{{ $k['block_reason'] ?? '' }}">Bloklangan</span>
                    @else
                    <span class="text-muted small">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Bu mijozda karta topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif
</div>
@endif

{{-- ─── Tab: Processing (pullik) ─────────────────────────────── --}}
@if($tab === 'processing')
@if(!$processingYoqilgan)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Processing xizmati hali admin sozlamalarida yoqilmagan. <a href="{{ route('admin.sozlamalar') }}">Sozlamalar</a> sahifasidan yoqing
    (bu xizmat AutoPay'ning oylik hisobiga qo'shimcha xarajat qiladi).
</div>
@else
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('autopay.index') }}" id="processingForm" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="processing">
        <input type="hidden" name="mijoz_id" id="processingMijozId" value="{{ $processingMijoz?->id }}">
        <div style="width:320px;position:relative">
            <label class="form-label small mb-1" style="color:#1e3a8a">Mijoz (ism, familiya yoki telefon)</label>
            <input type="text" class="form-control processing-mijoz-qidiruv" autocomplete="off"
                   placeholder="Qidirish uchun yozing..."
                   value="{{ $processingMijoz ? $processingMijoz->familiya.' '.$processingMijoz->ism : '' }}">
            <div class="processing-mijoz-natija list-group" style="position:absolute;z-index:20;width:100%;max-height:220px;overflow:auto"></div>
        </div>
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($processingMijoz)
        <a href="{{ route('autopay.index', ['tab' => 'processing']) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>
</div>

<div class="bank-wrap shadow-sm mb-3">
    @if(!$processingMijoz)
    <div class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Kartalarni qidirish uchun mijozni tanlang</div>
    @elseif(!$processingMijoz->pinfl)
    <div class="text-center text-muted py-5"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>{{ $processingMijoz->familiya }} {{ $processingMijoz->ism }} uchun PINFL kiritilmagan</div>
    @elseif(!$processingNatija['success'])
    <div class="text-center text-danger py-5"><i class="bi bi-x-circle fs-3 d-block mb-2"></i>AutoPay xatosi: {{ $processingNatija['error'] }}</div>
    @else
    @if($processingNatija['fails_key'])
    <div class="alert alert-warning m-3 mb-0 d-flex align-items-center justify-content-between gap-2">
        <div><i class="bi bi-exclamation-triangle me-1"></i>Ba'zi so'rovlar hozircha muvaffaqiyatsiz — AutoPay ularni fonda qayta urinadi. Bir necha daqiqadan so'ng qayta tekshiring.</div>
        <form method="GET" action="{{ route('autopay.index') }}" class="d-flex gap-1">
            <input type="hidden" name="tab" value="processing">
            <input type="hidden" name="mijoz_id" value="{{ $processingMijoz->id }}">
            <input type="hidden" name="fails_key" value="{{ $processingNatija['fails_key'] }}">
            <button class="btn btn-outline-warning btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Qayta tekshirish</button>
        </form>
    </div>
    @endif
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Turi</th>
                <th class="tl">Karta raqami</th>
                <th class="tl">Egasi</th>
                <th class="tl">Telefon</th>
            </tr>
        </thead>
        <tbody>
            @forelse($processingNatija['cards'] as $k)
            <tr>
                <td class="tl"><span class="badge-modern" style="background:{{ $k['turi'] === 'uzcard' ? '#0891b2' : '#7c3aed' }};color:#fff">{{ ucfirst($k['turi']) }}</span></td>
                <td class="tl"><code class="small">{{ $k['pan'] ?? '—' }}</code></td>
                <td class="tl">{{ $k['owner'] ?? '—' }}</td>
                <td class="tl text-muted">{{ $k['phone'] ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center text-muted py-5">Bu mijozda processing orqali karta topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif
</div>

@if($processingRecheckNatija)
<div class="alert {{ $processingRecheckNatija['success'] ? 'alert-success' : 'alert-danger' }} mb-3">
    @if($processingRecheckNatija['success'])
    <i class="bi bi-check-circle me-1"></i>Qayta tekshirildi: {{ json_encode($processingRecheckNatija['result'], JSON_UNESCAPED_UNICODE) }}
    @else
    <i class="bi bi-x-circle me-1"></i>AutoPay xatosi: {{ $processingRecheckNatija['error'] }}
    @endif
</div>
@endif

<h6 class="fw-bold mb-2"><i class="bi bi-clock-history me-1"></i>Qidiruvlar tarixi</h6>
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('autopay.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="processing">
        @if($processingMijoz)<input type="hidden" name="mijoz_id" value="{{ $processingMijoz->id }}">@endif
        <div>
            <label class="form-label small mb-1">Sanadan</label>
            <input type="date" name="tarix_dan" class="form-control" style="width:160px" value="{{ request('tarix_dan') }}">
        </div>
        <div>
            <label class="form-label small mb-1">Sanagacha</label>
            <input type="date" name="tarix_gacha" class="form-control" style="width:160px" value="{{ request('tarix_gacha') }}">
        </div>
        <select name="tarix_status" class="form-select" style="width:160px" onchange="this.form.submit()">
            <option value="">Barcha holatlar</option>
            <option value="success" {{ request('tarix_status') === 'success' ? 'selected' : '' }}>Muvaffaqiyatli</option>
            <option value="processing" {{ request('tarix_status') === 'processing' ? 'selected' : '' }}>Navbatda</option>
            <option value="failed" {{ request('tarix_status') === 'failed' ? 'selected' : '' }}>Xato</option>
        </select>
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Filtrlash</button>
    </form>
</div>
<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Sana</th>
                <th class="tl">PINFL</th>
                <th class="tl">Turi</th>
                <th class="tl">Egasi</th>
                <th class="tl">Holat</th>
                <th>Kartalar soni</th>
                <th class="tl">Kim so'ragan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($processingTarix as $t)
            <tr>
                <td class="tl text-muted">{{ $t['processed_at'] ?? $t['created_at'] ?? '—' }}</td>
                <td class="tl"><code class="small">{{ $t['pinfl'] ?? '—' }}</code></td>
                <td class="tl">{{ ucfirst($t['type'] ?? '—') }}</td>
                <td class="tl">{{ $t['owner'] ?? '—' }}</td>
                <td class="tl">
                    @if(($t['status'] ?? '') === 'success')
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Muvaffaqiyatli</span>
                    @elseif(($t['status'] ?? '') === 'failed')
                    <span class="badge-modern" style="background:#ef4444;color:#fff">Xato</span>
                    @else
                    <span class="badge-modern" style="background:#64748b;color:#fff">{{ ucfirst($t['status'] ?? '—') }}</span>
                    @endif
                </td>
                <td class="num">{{ $t['cards_count'] ?? 0 }}</td>
                <td class="tl text-muted">{{ $t['created_by'] ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5">Qidiruvlar tarixi bo'sh</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif
@endif

{{-- ─── Tab: Monitoring (pullik) ─────────────────────────────── --}}
@if($tab === 'monitoring')
@if(!$monitoringYoqilgan)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Monitoring xizmati hali admin sozlamalarida yoqilmagan. <a href="{{ route('admin.sozlamalar') }}">Sozlamalar</a> sahifasidan yoqing
    (bu xizmat AutoPay'ning oylik hisobiga qo'shimcha xarajat qiladi).
</div>
@else
<div class="filter-bar mb-0 d-flex align-items-end justify-content-between flex-wrap gap-2">
    <form method="GET" action="{{ route('autopay.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="monitoring">
        <div>
            <label class="form-label small mb-1">Karta raqami (16 xonali)</label>
            <input type="text" name="card_number" class="form-control" style="width:190px" maxlength="16" pattern="\d{16}" placeholder="9860123412341234" value="{{ request('card_number') }}" required>
        </div>
        <div>
            <label class="form-label small mb-1">Turi</label>
            <select name="turi" class="form-select" style="width:120px">
                <option value="uzcard" {{ request('turi', 'uzcard') === 'uzcard' ? 'selected' : '' }}>Uzcard</option>
                <option value="humo" {{ request('turi') === 'humo' ? 'selected' : '' }}>Humo</option>
            </select>
        </div>
        <div>
            <label class="form-label small mb-1">Sanadan</label>
            <input type="date" name="sana_dan" class="form-control" style="width:160px" value="{{ request('sana_dan') }}" required>
        </div>
        <div>
            <label class="form-label small mb-1">Sanagacha</label>
            <input type="date" name="sana_gacha" class="form-control" style="width:160px" value="{{ request('sana_gacha') }}">
        </div>
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Tranzaksiyalarni ko'rish</button>
    </form>
    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#monitorRoyxatModal">
        <i class="bi bi-plus-circle me-1"></i>Kartani ro'yxatga olish
    </button>
</div>

<div class="modal fade" id="monitorRoyxatModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('autopay.monitoring_karta_royxat') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">Monitoring uchun kartani ro'yxatga olish</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <div>
                    <label class="form-label small mb-1">Karta turi</label>
                    <select name="type" class="form-select monitor-turi-select" required>
                        <option value="uzcard">Uzcard</option>
                        <option value="humo">Humo</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Karta raqami (16 xonali)</label>
                    <input type="text" name="card_number" class="form-control" maxlength="16" pattern="\d{16}" placeholder="8600123412341234" required>
                </div>
                <div>
                    <label class="form-label small mb-1">Amal muddati (kartada yozilganidek)</label>
                    <input type="text" name="expire" class="form-control" maxlength="5" pattern="\d{2}/\d{2}" placeholder="MM/YY" required>
                </div>
                <div class="monitor-humo-telefon d-none">
                    <label class="form-label small mb-1">Telefon (Humo uchun, OTP shu raqamga boradi)</label>
                    <input type="text" name="phone" class="form-control" placeholder="+998901234567">
                </div>
                <div class="alert alert-info small mb-0 py-2"><i class="bi bi-info-circle me-1"></i>Karta egasiga OTP kod boradi — 3 marta noto'g'ri urinish kartani Uzcard'da 4 soatga, Humo'da 24 soatga bloklaydi.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-send me-1"></i>OTP yuborish</button>
            </div>
        </form>
    </div>
</div>
@if(session('monitor_ext'))
<div class="modal fade" id="monitorOtpModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('autopay.monitoring_karta_tasdiq') }}" class="modal-content">
            @csrf
            <input type="hidden" name="ext" value="{{ session('monitor_ext') }}">
            <input type="hidden" name="type" value="{{ session('monitor_type') }}">
            <div class="modal-header">
                <h6 class="modal-title">OTP kodni tasdiqlash</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <p class="small text-muted mb-0">
                    @if(session('monitor_telefon_mask'))
                    {{ session('monitor_telefon_mask') }} raqamiga yuborilgan kodni kiriting.
                    @else
                    Karta egasiga yuborilgan OTP kodni kiriting.
                    @endif
                </p>
                <input type="text" name="otp_code" class="form-control" maxlength="6" pattern="\d{4,6}" placeholder="123456" required autofocus>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg me-1"></i>Tasdiqlash</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="bank-wrap shadow-sm">
    @if(!$monitoringNatija)
    <div class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Tranzaksiyalarni ko'rish uchun karta raqami va sanadan maydonlarini to'ldiring</div>
    @elseif(!$monitoringNatija['success'])
    <div class="text-center text-danger py-5"><i class="bi bi-x-circle fs-3 d-block mb-2"></i>AutoPay xatosi: {{ $monitoringNatija['error'] }}</div>
    @else
    @php
        $turi = request('turi', 'uzcard');
        $qatorlar = $turi === 'humo' ? ($monitoringNatija['result']['rows'] ?? []) : ($monitoringNatija['result']['content'] ?? []);
    @endphp
    <table class="bank-table">
        <thead>
            @if($turi === 'humo')
            <tr>
                <th class="tl">Sana</th>
                <th class="tl">Karta</th>
                <th>Summa</th>
                <th class="tl">Do'kon</th>
                <th class="tl">Shahar</th>
            </tr>
            @else
            <tr>
                <th class="tl">Sana</th>
                <th class="tl">Karta</th>
                <th>Summa</th>
                <th class="tl">Turi</th>
                <th class="tl">Do'kon</th>
                <th class="tl">Kredit/Debet</th>
            </tr>
            @endif
        </thead>
        <tbody>
            @forelse($qatorlar as $q)
            @if($turi === 'humo')
            <tr>
                <td class="tl text-muted">{{ $q['TRAN_DATE_TIME'] ?? '—' }}</td>
                <td class="tl"><code class="small">{{ $q['CARD'] ?? '—' }}</code></td>
                <td class="num">{{ number_format((float) ($q['TRAN_AMT'] ?? 0), 0, '.', ' ') }}</td>
                <td class="tl">{{ $q['ABVR_NAME'] ?? '—' }}</td>
                <td class="tl text-muted">{{ $q['CITY'] ?? '—' }}</td>
            </tr>
            @else
            <tr>
                <td class="tl text-muted">{{ $q['udate'] ?? '—' }} {{ $q['utime'] ?? '' }}</td>
                <td class="tl"><code class="small">{{ $q['hpan'] ?? '—' }}</code></td>
                <td class="num">{{ number_format((float) ($q['reqamt'] ?? 0), 0, '.', ' ') }}</td>
                <td class="tl text-muted">{{ $q['transType'] ?? '—' }}</td>
                <td class="tl">{{ $q['merchantName'] ?? '—' }}</td>
                <td class="tl">
                    @if($q['credit'] ?? false)
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Kredit</span>
                    @else
                    <span class="badge-modern" style="background:#64748b;color:#fff">Debet</span>
                    @endif
                </td>
            </tr>
            @endif
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Bu davrda tranzaksiya topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
    @endif
</div>
@endif
@endif

{{-- ─── Tab 5: Scoring (pullik) ──────────────────────────────── --}}
@if($tab === 'scoring')
@if(!$scoringYoqilgan)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Scoring xizmati hali admin sozlamalarida yoqilmagan. <a href="{{ route('admin.sozlamalar') }}">Sozlamalar</a> sahifasidan yoqing
    (bu xizmat AutoPay'ning oylik hisobiga qo'shimcha xarajat qiladi).
</div>
@else
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('autopay.index') }}" id="scoringForm" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="scoring">
        <input type="hidden" name="mijoz_id" id="scoringMijozId" value="{{ $scoringMijoz?->id }}">
        <div style="width:320px;position:relative">
            <label class="form-label small mb-1" style="color:#1e3a8a">Mijoz (ism, familiya yoki telefon)</label>
            <input type="text" class="form-control scoring-mijoz-qidiruv" autocomplete="off"
                   placeholder="Qidirish uchun yozing..."
                   value="{{ $scoringMijoz ? $scoringMijoz->familiya.' '.$scoringMijoz->ism : '' }}">
            <div class="scoring-mijoz-natija list-group" style="position:absolute;z-index:20;width:100%;max-height:220px;overflow:auto"></div>
        </div>
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($scoringMijoz)
        <a href="{{ route('autopay.index', ['tab' => 'scoring']) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>
</div>

<div class="bank-wrap shadow-sm" style="height:calc(100vh - 260px)">
    @if(!$scoringMijoz)
    <div class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Scoring ma'lumotini ko'rish uchun mijozni qidiring va tanlang</div>
    @elseif(!$scoringMijoz->pinfl)
    <div class="text-center text-muted py-5"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>{{ $scoringMijoz->familiya }} {{ $scoringMijoz->ism }} uchun PINFL kiritilmagan</div>
    @elseif(!$scoringNatija['success'])
    <div class="text-center text-danger py-5"><i class="bi bi-x-circle fs-3 d-block mb-2"></i>AutoPay xatosi: {{ $scoringNatija['error'] }}</div>
    @else
    @php $s = $scoringNatija['result']; @endphp
    <div class="p-3">
        <h6 class="fw-bold mb-2"><i class="bi bi-person-badge me-1"></i>Shaxsiy ma'lumot</h6>
        <table class="bank-table mb-3">
            <thead><tr><th class="tl">F.I.Sh</th><th class="tl">Ro'yxatdan o'tgan sana</th></tr></thead>
            <tbody>
                @forelse($s['personal_info'] ?? [] as $p)
                <tr><td class="tl">{{ $p['fio'] ?? '—' }}</td><td class="tl text-muted">{{ $p['created_at'] ?? '—' }}</td></tr>
                @empty
                <tr><td colspan="2" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                @endforelse
            </tbody>
        </table>

        <h6 class="fw-bold mb-2"><i class="bi bi-credit-card me-1"></i>Kartalar soni</h6>
        <table class="bank-table mb-3">
            <thead><tr><th class="tl">Turi</th><th>Soni</th></tr></thead>
            <tbody>
                <tr><td class="tl">Uzcard</td><td class="num">{{ $s['cards']['uzcard'] ?? 0 }}</td></tr>
                <tr><td class="tl">Humo</td><td class="num">{{ $s['cards']['humo'] ?? 0 }}</td></tr>
            </tbody>
        </table>

        <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-text me-1"></i>Shartnomalar (barcha tashkilotlar)</h6>
        <table class="bank-table mb-3">
            <thead><tr><th class="tl">Loan ID</th><th>Auto</th><th>Qarz</th><th>Jami qarz</th><th class="tl">Yaratilgan</th></tr></thead>
            <tbody>
                @forelse($s['contracts'] ?? [] as $c)
                <tr>
                    <td class="tl"><code class="small">{{ $c['loan_id'] ?? '—' }}</code></td>
                    <td class="text-center">{{ ($c['auto'] ?? false) ? '✔' : '—' }}</td>
                    <td class="num">{{ number_format(($c['debt'] ?? 0) / 100, 0, '.', ' ') }}</td>
                    <td class="num">{{ number_format(($c['total_debt'] ?? 0) / 100, 0, '.', ' ') }}</td>
                    <td class="tl text-muted">{{ $c['created_at'] ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Shartnoma topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>

        <h6 class="fw-bold mb-2"><i class="bi bi-arrow-left-right me-1"></i>So'nggi tranzaksiyalar</h6>
        <table class="bank-table">
            <thead><tr><th class="tl">Sana</th><th class="tl">Loan ID</th><th>Summa</th><th class="tl">Holat</th><th class="tl">Karta</th></tr></thead>
            <tbody>
                @forelse($s['transactions'] ?? [] as $t)
                <tr>
                    <td class="tl text-muted">{{ $t['date'] ?? '—' }}</td>
                    <td class="tl"><code class="small">{{ $t['loan_id'] ?? '—' }}</code></td>
                    <td class="num">{{ number_format(($t['amount'] ?? 0) / 100, 0, '.', ' ') }}</td>
                    <td class="tl">
                        @if(($t['status'] ?? '') === 'success')
                        <span class="badge-modern" style="background:#22c55e;color:#fff">Muvaffaqiyatli</span>
                        @else
                        <span class="badge-modern" style="background:#64748b;color:#fff">Bekor qilingan</span>
                        @endif
                    </td>
                    <td class="tl text-muted">{{ $t['card_mask'] ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Tranzaksiya topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
@endif
@endif

{{-- ─── Tab 6: E-GOV (pullik) ────────────────────────────────── --}}
@if($tab === 'egov')
@if(!$egovYoqilgan)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-1"></i>
    E-GOV xizmati hali admin sozlamalarida yoqilmagan. <a href="{{ route('admin.sozlamalar') }}">Sozlamalar</a> sahifasidan yoqing
    (mijozni ro'yxatdan o'tkazish/yangilash AutoPay'ning oylik hisobiga qo'shimcha xarajat qiladi; saqlangan ma'lumotni o'qish bepul).
</div>
@else
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('autopay.index') }}" id="egovForm" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="egov">
        <input type="hidden" name="mijoz_id" id="egovMijozId" value="{{ $egovMijoz?->id }}">
        <div style="width:320px;position:relative">
            <label class="form-label small mb-1" style="color:#1e3a8a">Mijoz (ism, familiya yoki telefon)</label>
            <input type="text" class="form-control egov-mijoz-qidiruv" autocomplete="off"
                   placeholder="Qidirish uchun yozing..."
                   value="{{ $egovMijoz ? $egovMijoz->familiya.' '.$egovMijoz->ism : '' }}">
            <div class="egov-mijoz-natija list-group" style="position:absolute;z-index:20;width:100%;max-height:220px;overflow:auto"></div>
        </div>
        @if($egovMijoz)
        <div style="width:200px">
            <label class="form-label small mb-1" style="color:#1e3a8a">E-GOV xizmati</label>
            <select name="service_id" class="form-select" onchange="this.form.submit()">
                <option value="">— tanlang —</option>
                @foreach($egovXizmatlar as $xiz)
                <option value="{{ $xiz['service_id'] }}" {{ (string) request('service_id') === (string) $xiz['service_id'] ? 'selected' : '' }}>{{ $xiz['name_uz'] }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($egovMijoz)
        <a href="{{ route('autopay.index', ['tab' => 'egov']) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>
    @if($egovMijoz)
    <div class="d-flex gap-2 mt-2">
        <form method="POST" action="{{ route('autopay.egov_saqlash') }}"
              onsubmit="return confirm('{{ $egovMijoz->familiya }} {{ $egovMijoz->ism }} ni E-GOV xizmatlariga ro\'yxatdan o\'tkazasizmi? Bu PULLIK amal — barcha xizmatlarga so\'rov yuboriladi.')">
            @csrf
            <input type="hidden" name="mijoz_id" value="{{ $egovMijoz->id }}">
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-cloud-arrow-up me-1"></i>Ro'yxatdan o'tkazish (saqlash)</button>
        </form>
        <form method="POST" action="{{ route('autopay.egov_yangilash') }}"
              onsubmit="return confirm('{{ $egovMijoz->familiya }} {{ $egovMijoz->ism }} uchun E-GOV ma\'lumotlarini yangilaysizmi? Bu PULLIK amal.')">
            @csrf
            <input type="hidden" name="mijoz_id" value="{{ $egovMijoz->id }}">
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Yangilash</button>
        </form>
    </div>
    @endif
</div>

<div class="bank-wrap shadow-sm" style="height:calc(100vh - 300px)">
    @if(!$egovMijoz)
    <div class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Ma'lumotni ko'rish uchun mijozni qidiring va tanlang</div>
    @elseif(!$egovMijoz->pinfl)
    <div class="text-center text-muted py-5"><i class="bi bi-exclamation-triangle fs-3 d-block mb-2"></i>{{ $egovMijoz->familiya }} {{ $egovMijoz->ism }} uchun PINFL kiritilmagan</div>
    @elseif(!request('service_id'))
    <div class="text-center text-muted py-5"><i class="bi bi-arrow-up fs-3 d-block mb-2"></i>Yuqoridan E-GOV xizmatini tanlang</div>
    @elseif(!$egovNatija['success'])
    <div class="text-center text-danger py-5"><i class="bi bi-x-circle fs-3 d-block mb-2"></i>AutoPay xatosi: {{ $egovNatija['error'] }}</div>
    @else
    @php $malumot = $egovNatija['result']['data'] ?? null; @endphp
    <div class="p-3">
        @if(empty($malumot))
        <div class="text-center text-muted py-5">Bu xizmat bo'yicha ma'lumot saqlanmagan — avval "Ro'yxatdan o'tkazish" tugmasini bosing</div>
        @else
        <table class="bank-table">
            <thead><tr><th class="tl">Maydon</th><th class="tl">Qiymat</th></tr></thead>
            <tbody>
                @foreach($malumot as $kalit => $qiymat)
                <tr>
                    <td class="tl fw-semibold">{{ $kalit }}</td>
                    <td class="tl">
                        @if(is_array($qiymat))
                        <pre class="small mb-0" style="white-space:pre-wrap">{{ json_encode($qiymat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                        {{ $qiymat }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif
</div>
@endif
@endif

</div>

<script>
(function () {
    // Bulk-yuborish sanog'i (faqat Kutilayotgan tabda mavjud)
    const hammaBelgila = document.getElementById('hammaBelgila');
    if (hammaBelgila) {
        const bulkBtn = document.getElementById('bulkYuborBtn');
        const soni = document.getElementById('tanlanganSoniHeader');

        function yangila() {
            const belgilangan = document.querySelectorAll('.kredit-check:checked').length;
            soni.textContent = belgilangan;
            bulkBtn.disabled = belgilangan === 0;
        }

        hammaBelgila.addEventListener('change', function () {
            document.querySelectorAll('.kredit-check:not(:disabled)').forEach(cb => cb.checked = hammaBelgila.checked);
            yangila();
        });
        document.querySelectorAll('.kredit-check').forEach(cb => cb.addEventListener('change', yangila));
    }

    // Bulk-o'chirish sanog'i (Yuborilgan tabda)
    const hammaBelgilaYuborilgan = document.getElementById('hammaBelgilaYuborilgan');
    if (hammaBelgilaYuborilgan) {
        const ochirishBtn = document.getElementById('bulkOchirishBtn');
        const soniYuborilgan = document.getElementById('tanlanganSoniYuborilgan');

        function yangilaYuborilgan() {
            const belgilangan = document.querySelectorAll('.shartnoma-check:checked').length;
            soniYuborilgan.textContent = belgilangan;
            ochirishBtn.disabled = belgilangan === 0;
        }

        hammaBelgilaYuborilgan.addEventListener('change', function () {
            document.querySelectorAll('.shartnoma-check:not(:disabled)').forEach(cb => cb.checked = hammaBelgilaYuborilgan.checked);
            yangilaYuborilgan();
        });
        document.querySelectorAll('.shartnoma-check').forEach(cb => cb.addEventListener('change', yangilaYuborilgan));
    }

    // Kartalar tabi: karta turi Humo bo'lsa telefon maydonini ko'rsatish
    const kartaTuriSelect = document.querySelector('.karta-turi-select');
    if (kartaTuriSelect) {
        const telefonBlok = document.querySelector('.karta-humo-telefon');
        function kartaTuriniYangila() {
            telefonBlok.classList.toggle('d-none', kartaTuriSelect.value !== 'humo');
        }
        kartaTuriSelect.addEventListener('change', kartaTuriniYangila);
        kartaTuriniYangila();
    }

    // Kartalar tabi: karta ro'yxatga olingandan so'ng OTP oynasini avtomatik ochish.
    // window.load kutiladi, chunki bootstrap.bundle.min.js sahifa pastida (bu skriptdan keyin) ulanadi.
    const kartaOtpModalEl = document.getElementById('kartaOtpModal');
    if (kartaOtpModalEl) {
        window.addEventListener('load', function () {
            new bootstrap.Modal(kartaOtpModalEl).show();
        });
    }

    // Monitoring tabi: karta turi Humo bo'lsa telefon maydonini ko'rsatish
    const monitorTuriSelect = document.querySelector('.monitor-turi-select');
    if (monitorTuriSelect) {
        const monitorTelefonBlok = document.querySelector('.monitor-humo-telefon');
        function monitorTuriniYangila() {
            monitorTelefonBlok.classList.toggle('d-none', monitorTuriSelect.value !== 'humo');
        }
        monitorTuriSelect.addEventListener('change', monitorTuriniYangila);
        monitorTuriniYangila();
    }

    // Monitoring tabi: karta ro'yxatga olingandan so'ng OTP oynasini avtomatik ochish
    const monitorOtpModalEl = document.getElementById('monitorOtpModal');
    if (monitorOtpModalEl) {
        window.addEventListener('load', function () {
            new bootstrap.Modal(monitorOtpModalEl).show();
        });
    }

    // Ustunlarni ko'rsatish/yashirish + o'lchamini o'zgartirish — har bir jadval uchun alohida
    [1, 2, 3].forEach(function (n) {
        const table = document.getElementById('table-' + n);
        if (!table) return;

        table.querySelectorAll('thead th').forEach(th => {
            const r = document.createElement('div');
            r.className = 'col-resizer';
            th.appendChild(r);
            let sx, sw;
            r.addEventListener('mousedown', e => {
                e.preventDefault(); sx = e.clientX; sw = th.offsetWidth;
                r.classList.add('resizing');
                const mm = ev => { th.style.width = th.style.minWidth = Math.max(40, sw + ev.clientX - sx) + 'px'; };
                const mu = () => { r.classList.remove('resizing'); document.removeEventListener('mousemove', mm); document.removeEventListener('mouseup', mu); };
                document.addEventListener('mousemove', mm);
                document.addEventListener('mouseup', mu);
            });
        });

        const UST_KEY = 'autopay_ustun_korinishi_' + n;
        let saqlangan = {};
        try { saqlangan = JSON.parse(localStorage.getItem(UST_KEY)) || {}; } catch (e) {}

        function ustunniQoy(col, korinsin) {
            table.querySelectorAll('.col-' + col).forEach(el => el.classList.toggle('d-none', !korinsin));
        }

        document.querySelectorAll('.ustun-toggle-' + n).forEach(cb => {
            const col = cb.dataset.col;
            const def = cb.dataset.default === '1';
            const korinsin = saqlangan.hasOwnProperty(col) ? !!saqlangan[col] : def;
            cb.checked = korinsin;
            ustunniQoy(col, korinsin);
            cb.addEventListener('change', function () {
                saqlangan[col] = cb.checked;
                localStorage.setItem(UST_KEY, JSON.stringify(saqlangan));
                ustunniQoy(col, cb.checked);
            });
        });
    });

    // Shartnomaga biriktirish — mijoz/shartnoma qidiruvi (AJAX)
    document.querySelectorAll('.biriktir-form').forEach(function (form) {
        const input     = form.querySelector('.biriktir-qidiruv');
        const natijaEl  = form.querySelector('.biriktir-natija');
        const hiddenId  = form.querySelector('.biriktir-kredit-id');
        const tanlangan = form.querySelector('.biriktir-tanlangan');
        const submitBtn = form.querySelector('.biriktir-submit');
        let vaqt;

        input.addEventListener('input', function () {
            clearTimeout(vaqt);
            const q = input.value.trim();
            natijaEl.innerHTML = '';
            if (q.length < 2) return;
            vaqt = setTimeout(function () {
                fetch("{{ route('autopay.kredit_qidir') }}?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(function (natijalar) {
                        natijaEl.innerHTML = '';
                        if (!natijalar.length) {
                            natijaEl.innerHTML = '<div class="list-group-item small text-muted">Topilmadi</div>';
                            return;
                        }
                        natijalar.forEach(function (k) {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action small';
                            item.textContent = k.label;
                            item.addEventListener('click', function () {
                                hiddenId.value = k.id;
                                tanlangan.textContent = 'Tanlandi: ' + k.label;
                                submitBtn.disabled = false;
                                natijaEl.innerHTML = '';
                                input.value = '';
                            });
                            natijaEl.appendChild(item);
                        });
                    });
            }, 300);
        });
    });

    // Mijoz qidiruvi — Kartalar va Scoring tablari uchun umumiy funksiya
    function mijozQidiruvniUlash(inputSel, natijaSel, hiddenIdSel, formId) {
        const input = document.querySelector(inputSel);
        if (!input) return;
        const natijaEl = document.querySelector(natijaSel);
        const hiddenId = document.querySelector(hiddenIdSel);
        let vaqt;

        input.addEventListener('input', function () {
            clearTimeout(vaqt);
            const q = input.value.trim();
            natijaEl.innerHTML = '';
            if (q.length < 2) return;
            vaqt = setTimeout(function () {
                fetch("{{ route('autopay.mijoz_qidir') }}?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(function (natijalar) {
                        natijaEl.innerHTML = '';
                        if (!natijalar.length) {
                            natijaEl.innerHTML = '<div class="list-group-item small text-muted">Topilmadi</div>';
                            return;
                        }
                        natijalar.forEach(function (m) {
                            const item = document.createElement('button');
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action small';
                            item.textContent = m.label;
                            item.addEventListener('click', function () {
                                hiddenId.value = m.id;
                                input.value = m.label;
                                natijaEl.innerHTML = '';
                                document.getElementById(formId).submit();
                            });
                            natijaEl.appendChild(item);
                        });
                    });
            }, 300);
        });
    }

    mijozQidiruvniUlash('.karta-mijoz-qidiruv', '.karta-mijoz-natija', '#kartaMijozId', 'kartaForm');
    mijozQidiruvniUlash('.processing-mijoz-qidiruv', '.processing-mijoz-natija', '#processingMijozId', 'processingForm');
    mijozQidiruvniUlash('.scoring-mijoz-qidiruv', '.scoring-mijoz-natija', '#scoringMijozId', 'scoringForm');
    mijozQidiruvniUlash('.egov-mijoz-qidiruv', '.egov-mijoz-natija', '#egovMijozId', 'egovForm');
})();
</script>
@endsection
