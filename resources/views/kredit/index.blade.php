@extends('layouts.app')

@section('title', 'Shartnomalar')

@section('breadcrumb')
    <li class="breadcrumb-item active">Shartnomalar</li>

{{-- Mobile FAB: yangi shartnoma --}}
@if(Auth::user()->isMenejerYoki())
<a href="{{ route('kreditlar.create') }}"
   class="mobile-fab btn btn-primary"
   title="Yangi shartnoma"
   onclick="return litsenziyaTekshir('shartnoma', 'Shartnoma qo\'shish')">
    <i class="bi bi-plus-lg"></i>
</a>
@endif
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
.bank-table thead th.tl.sticky-col { position:sticky; left:0; z-index:7; min-width:150px; }

.jami-row th {
    background:linear-gradient(180deg,#fef9c3,#fde68a) !important; color:#7c2d12 !important;
    font-weight:800; font-size:.78rem; text-transform:none; letter-spacing:0; padding:6px 8px;
}
.jami-row th.sticky-col { background:linear-gradient(180deg,#fde68a,#fbbf24) !important; z-index:8; }
.jami-row th.num { font-family:'Roboto Mono','Courier New',monospace; }

.bank-table tbody td.sticky-col { position:sticky; left:0; z-index:2; background:inherit; border-right:2px solid #93c5fd; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody tr.row-muddati-otgan td { background:#fee2e2 !important; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; font-size:.85rem; }
.num.text-muted { color:#334155 !important; }

.bank-wrap { overflow:auto; height:calc(100vh - 195px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 170px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.b-faol { background:#22c55e; color:#fff; }
.b-yopilgan { background:#64748b; color:#fff; }
.b-muddati_otgan { background:#ef4444; color:#fff; }
.b-muzlatilgan { background:#06b6d4; color:#fff; }
.b-kutilmoqda { background:#f59e0b; color:#fff; }

.mini-progress { width:70px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; display:inline-block; vertical-align:middle; }
.mini-progress-bar { height:100%; }

.col-resizer { position:absolute; right:0; top:0; bottom:0; width:5px; cursor:col-resize; background:transparent; z-index:2; }
.col-resizer:hover, .col-resizer.resizing { background:rgba(255,255,255,.4); }

.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:10px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
</style>
@endpush

@section('content')

{{-- Mobile card ro'yxat (faqat telefonda) — o'zgarishsiz --}}
<div class="d-md-none">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-file-earmark-text me-1"></i> Shartnomalar
            <span class="badge bg-secondary ms-1">{{ $kreditlar->total() }}</span>
        </h5>
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('kreditlar.create') }}" class="btn btn-primary btn-sm" onclick="return litsenziyaTekshir('shartnoma', 'Shartnoma qo\'shish')">
            <i class="bi bi-plus-lg me-1"></i> Yangi shartnoma
        </a>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <input type="search" name="qidiruv" class="form-control form-control-sm"
                           placeholder="Shartnoma raqam, mijoz ismi, telefon..."
                           value="{{ request('qidiruv') }}">
                </div>
                @if(Auth::user()->isAdmin())
                <div class="col-sm-3">
                    <select name="filial_id" class="form-select form-select-sm">
                        <option value="">Barcha filiallar</option>
                        @foreach($filiallar as $f)
                            <option value="{{ $f->id }}" {{ request('filial_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->nomi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-sm-2">
                    <select name="holat" class="form-select form-select-sm">
                        <option value="">Barcha holat</option>
                        <option value="faol"          {{ request('holat') === 'faol'          ? 'selected' : '' }}>AKTIV</option>
                        <option value="yopilgan"      {{ request('holat') === 'yopilgan'      ? 'selected' : '' }}>PASSIV</option>
                        <option value="muddati_otgan" {{ request('holat') === 'muddati_otgan' ? 'selected' : '' }}>Muddati o'tgan</option>
                        <option value="muzlatilgan"   {{ request('holat') === 'muzlatilgan'   ? 'selected' : '' }}>Muzlatilgan</option>
                        <option value="muddati_kelgan"     {{ request('holat') === 'muddati_kelgan'     ? 'selected' : '' }}>Muddati kelgan</option>
                        <option value="muddatidan_oldinda" {{ request('holat') === 'muddatidan_oldinda' ? 'selected' : '' }}>Muddatidan oldinda</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <select name="xodim_id" class="form-select form-select-sm">
                        <option value="">Barcha xodimlar</option>
                        @foreach($xodimlar as $x)
                            <option value="{{ $x->id }}" {{ request('xodim_id') == $x->id ? 'selected' : '' }}>{{ $x->ism_familiya }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <select name="kechikish_oraligi" class="form-select form-select-sm">
                        <option value="">Kechikish (barchasi)</option>
                        <option value="1-30"    {{ request('kechikish_oraligi') === '1-30'    ? 'selected' : '' }}>1–30 kun</option>
                        <option value="31-60"   {{ request('kechikish_oraligi') === '31-60'   ? 'selected' : '' }}>31–60 kun</option>
                        <option value="61-90"   {{ request('kechikish_oraligi') === '61-90'   ? 'selected' : '' }}>61–90 kun</option>
                        <option value="91-120"  {{ request('kechikish_oraligi') === '91-120'  ? 'selected' : '' }}>91–120 kun</option>
                        <option value="121-150" {{ request('kechikish_oraligi') === '121-150' ? 'selected' : '' }}>121–150 kun</option>
                        <option value="151-180" {{ request('kechikish_oraligi') === '151-180' ? 'selected' : '' }}>151–180 kun</option>
                        <option value="180+"    {{ request('kechikish_oraligi') === '180+'    ? 'selected' : '' }}>180+ kun</option>
                    </select>
                </div>
                <div class="col-sm-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search me-1"></i> Qidirish
                    </button>
                    <a href="{{ route('kreditlar.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                        <i class="bi bi-x"></i>
                    </a>
                    <a href="{{ route('kreditlar.excel', request()->query()) }}" class="btn btn-sm btn-success ms-1">
                        <i class="bi bi-file-earmark-excel"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @forelse($kreditlar as $k)
    <div class="card border-0 shadow-sm mb-2 {{ $k->holat === 'muddati_otgan' ? 'border-danger border-opacity-25' : '' }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <a href="{{ route('kreditlar.show', $k) }}" class="fw-bold text-decoration-none fs-6">
                    {{ $k->shartnoma_raqam }}
                </a>
                <span class="badge bg-{{ $k->holat_rangi }}">{{ $k->holatNomi }}</span>
            </div>
            <div class="fw-medium mb-1">{{ $k->mijoz->familiya }} {{ $k->mijoz->ism }}</div>
            <div class="text-muted small mb-2">{{ $k->mijoz->telefon }}</div>
            <div class="row g-1 mb-2" style="font-size:.8rem">
                <div class="col-6">
                    <span class="text-muted">Tovar summasi:</span>
                    <strong>{{ number_format($k->jami_summa, 0, '.', ' ') }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Qoldiq:</span>
                    <strong class="{{ $k->qoldiq_qarz > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($k->qoldiq_qarz, 0, '.', ' ') }}
                    </strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Muddat:</span>
                    {{ $k->tugash_sana ? $k->tugash_sana->format('d.m.Y') : '—' }}
                </div>
                @if(Auth::user()->isAdmin())
                <div class="col-6">
                    <span class="text-muted">Filial:</span>
                    <span class="badge bg-secondary">{{ $k->filial->kod }}</span>
                </div>
                @endif
            </div>
            @php $foiz = $k->tolov_foizi; @endphp
            <div class="progress" style="height:5px;border-radius:3px;margin-bottom:6px">
                <div class="progress-bar bg-{{ $k->holat_rangi }}" style="width:{{ $foiz }}%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $k->xodim->ism_familiya }}</small>
                <a href="{{ route('kreditlar.show', $k) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                    <i class="bi bi-eye me-1"></i>Ko'rish
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-search fs-3 d-block mb-2"></i>
        Shartnomalar topilmadi
    </div>
    @endforelse
    @if($kreditlar->hasPages())
    <div class="mt-2">
        {{ $kreditlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Bank-style jadval (desktop) --}}
<div class="d-none d-md-block">

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-file-earmark-text" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Shartnomalar</span>
            <span class="badge bg-secondary">{{ $kreditlar->total() }}</span>
        </div>
        <div style="width:220px">
            <input type="search" name="qidiruv" class="form-control" placeholder="Shartnoma, mijoz, telefon..." value="{{ request('qidiruv') }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <select name="filial_id" class="form-select" style="width:150px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ request('filial_id') == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <select name="holat" class="form-select" style="width:170px">
                <option value="">Barcha holat</option>
                <option value="faol"          {{ request('holat') === 'faol'          ? 'selected' : '' }}>AKTIV</option>
                <option value="yopilgan"      {{ request('holat') === 'yopilgan'      ? 'selected' : '' }}>PASSIV</option>
                <option value="muddati_otgan" {{ request('holat') === 'muddati_otgan' ? 'selected' : '' }}>Muddati o'tgan</option>
                <option value="muzlatilgan"   {{ request('holat') === 'muzlatilgan'   ? 'selected' : '' }}>Muzlatilgan</option>
                <option value="muddati_kelgan"     {{ request('holat') === 'muddati_kelgan'     ? 'selected' : '' }}>Muddati kelgan</option>
                <option value="muddatidan_oldinda" {{ request('holat') === 'muddatidan_oldinda' ? 'selected' : '' }}>Muddatidan oldinda</option>
            </select>
        </div>
        <div>
            <select name="xodim_id" class="form-select" style="width:170px">
                <option value="">Barcha xodimlar</option>
                @foreach($xodimlar as $x)
                    <option value="{{ $x->id }}" {{ request('xodim_id') == $x->id ? 'selected' : '' }}>{{ $x->ism_familiya }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="kechikish_oraligi" class="form-select" style="width:170px">
                <option value="">Kechikish (barchasi)</option>
                <option value="1-30"    {{ request('kechikish_oraligi') === '1-30'    ? 'selected' : '' }}>1–30 kun</option>
                <option value="31-60"   {{ request('kechikish_oraligi') === '31-60'   ? 'selected' : '' }}>31–60 kun</option>
                <option value="61-90"   {{ request('kechikish_oraligi') === '61-90'   ? 'selected' : '' }}>61–90 kun</option>
                <option value="91-120"  {{ request('kechikish_oraligi') === '91-120'  ? 'selected' : '' }}>91–120 kun</option>
                <option value="121-150" {{ request('kechikish_oraligi') === '121-150' ? 'selected' : '' }}>121–150 kun</option>
                <option value="151-180" {{ request('kechikish_oraligi') === '151-180' ? 'selected' : '' }}>151–180 kun</option>
                <option value="180+"    {{ request('kechikish_oraligi') === '180+'    ? 'selected' : '' }}>180+ kun</option>
            </select>
        </div>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
            <a href="{{ route('kreditlar.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
            <a href="{{ route('kreditlar.excel', request()->query()) }}" class="btn btn-success btn-sm px-2" style="height:32px" title="Excelga eksport">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2" style="height:32px" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Ustunlarni ko'rsatish/yashirish">
                    <i class="bi bi-layout-three-columns"></i>
                </button>
                <ul class="dropdown-menu p-2" style="font-size:.8rem;max-height:340px;overflow:auto;min-width:190px">
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="xodim" data-default="1"> Xodim</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="mijoz" data-default="1"> Mijoz</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="telefon" data-default="1"> Telefon</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="manzil" data-default="1"> Manzil</label></li>
                    @if(Auth::user()->isAdmin())
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="filial" data-default="1"> Filial</label></li>
                    @endif
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="boshlanish" data-default="1"> Boshlanish</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="tugash" data-default="1"> Tugash</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="muddat" data-default="1"> Muddat</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="jami" data-default="1"> Tovar summasi</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="oldindan" data-default="1"> Oldindan</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="kredit" data-default="1"> Kredit</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="jami-tolangan" data-default="1"> Jami to'langan</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="qoldiq" data-default="1"> Qoldiq</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="kechikkan" data-default="1"> Kechikkan summa</label></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="holat" data-default="0"> Holat</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="progress" data-default="0"> Progress</label></li>
                </ul>
            </div>
        </div>
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('kreditlar.create') }}" class="btn btn-warning btn-sm ms-auto fw-bold" onclick="return litsenziyaTekshir('shartnoma', 'Shartnoma qo\'shish')">
            <i class="bi bi-plus-lg me-1"></i>Yangi shartnoma
        </a>
        @endif
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table" id="shartnoma-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Shartnoma</th>
                <th class="tl col-xodim">Xodim</th>
                <th class="tl col-mijoz">Mijoz</th>
                <th class="tl col-telefon">Telefon</th>
                <th class="tl col-manzil">Manzil</th>
                @if(Auth::user()->isAdmin())<th class="col-filial">Filial</th>@endif
                <th class="col-boshlanish">Boshlanish</th>
                <th class="col-tugash">Tugash</th>
                <th class="col-muddat">Muddat</th>
                <th class="col-jami">Tovar summasi</th>
                <th class="col-oldindan">Oldindan</th>
                <th class="col-kredit">Kredit</th>
                <th class="col-jami-tolangan">Jami to'langan</th>
                <th class="col-qoldiq">Qoldiq</th>
                <th class="col-kechikkan">Kechikkan summa</th>
                <th class="col-holat d-none">Holat</th>
                <th class="col-progress d-none">Progress</th>
                <th style="width:50px"></th>
            </tr>
            <tr class="jami-row">
                <th class="tl sticky-col">JAMI ({{ number_format($jamiSummalar->soni) }} ta)</th>
                <th class="col-xodim"></th>
                <th class="col-mijoz"></th>
                <th class="col-telefon"></th>
                <th class="col-manzil"></th>
                @if(Auth::user()->isAdmin())<th class="col-filial"></th>@endif
                <th class="col-boshlanish"></th>
                <th class="col-tugash"></th>
                <th class="col-muddat"></th>
                <th class="num col-jami">{{ number_format($jamiSummalar->jami_summa, 0, '.', ' ') }}</th>
                <th class="num col-oldindan">{{ number_format($jamiSummalar->boshlangich_tolov, 0, '.', ' ') }}</th>
                <th class="num col-kredit">{{ number_format($jamiSummalar->kredit_summa, 0, '.', ' ') }}</th>
                <th class="num col-jami-tolangan">{{ number_format($jamiSummalar->jami_tolangan, 0, '.', ' ') }}</th>
                <th class="num col-qoldiq">{{ number_format($jamiSummalar->qoldiq_qarz, 0, '.', ' ') }}</th>
                <th class="num col-kechikkan">{{ number_format($jamiSummalar->kechikkan_summa, 0, '.', ' ') }}</th>
                <th class="col-holat d-none"></th>
                <th class="col-progress d-none"></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($kreditlar as $k)
            @php
                $foiz = $k->tolov_foizi;
                $barRang = $foiz >= 100 ? '#22c55e' : ($foiz >= 70 ? '#06b6d4' : ($foiz >= 40 ? '#f59e0b' : '#ef4444'));
                $oyQoldi = max(0, $k->muddati_oy - $k->tolangan_oy_soni);
            @endphp
            <tr class="{{ $k->holat === 'muddati_otgan' ? 'row-muddati-otgan' : '' }}">
                <td class="tl sticky-col">
                    <a href="{{ route('kreditlar.show', $k) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $k->shartnoma_raqam }}</a>
                </td>
                <td class="tl text-muted col-xodim">{{ $k->xodim->ism_familiya }}</td>
                <td class="tl col-mijoz">
                    <a href="{{ route('mijozlar.show', $k->mijoz) }}" class="text-decoration-none" style="color:#1e293b">{{ $k->mijoz->familiya }} {{ $k->mijoz->ism }}</a>
                </td>
                <td class="tl text-muted col-telefon">{{ $k->mijoz->telefon }}</td>
                <td class="tl text-muted col-manzil">{{ Str::limit($k->mijoz->manzil, 30) ?: '—' }}</td>
                @if(Auth::user()->isAdmin())
                <td class="text-center col-filial"><span class="badge-modern" style="background:#1e293b;color:#fff">{{ $k->filial->kod }}</span></td>
                @endif
                <td class="tl text-muted col-boshlanish">{{ $k->boshlanish_sana ? $k->boshlanish_sana->format('d.m.Y') : '—' }}</td>
                <td class="tl text-muted col-tugash">{{ $k->tugash_sana ? $k->tugash_sana->format('d.m.Y') : '—' }}</td>
                <td class="text-center col-muddat"><span class="badge-modern" style="background:#e0e7ff;color:#3730a3">{{ $k->muddati_oy }} oy</span></td>
                <td class="num text-muted col-jami">{{ number_format($k->jami_summa, 0, '.', ' ') }}</td>
                <td class="num col-oldindan" style="color:#0891b2">
                    {{ $k->boshlangich_tolov > 0 ? number_format($k->boshlangich_tolov, 0, '.', ' ') : '—' }}
                </td>
                <td class="num col-kredit" style="color:#1e293b">{{ number_format($k->kredit_summa, 0, '.', ' ') }}</td>
                <td class="num col-jami-tolangan" style="color:#16a34a">{{ number_format($k->boshlangich_tolov + $k->tolov_qilingan, 0, '.', ' ') }}</td>
                <td class="num fw-bold col-qoldiq" style="color:{{ $k->qoldiq_qarz > 0 ? '#dc2626' : '#16a34a' }}">
                    {{ number_format($k->qoldiq_qarz, 0, '.', ' ') }}
                </td>
                <td class="num fw-bold col-kechikkan" style="color:{{ $k->kechikkan_summa > 0 ? '#dc2626' : '#94a3b8' }}">
                    {{ $k->kechikkan_summa > 0 ? number_format($k->kechikkan_summa, 0, '.', ' ') : '—' }}
                </td>
                <td class="text-center col-holat d-none"><span class="badge-modern b-{{ $k->holat }}">{{ $k->holatNomi }}</span></td>
                <td class="col-progress d-none">
                    <div class="d-flex align-items-center gap-1"
                         title="Tovar summasi: {{ number_format($k->jami_summa,0,'.',' ') }} | To'landi: {{ number_format($k->boshlangich_tolov+$k->tolov_qilingan,0,'.',' ') }} ({{ $foiz }}%) | Oy: {{ $k->tolangan_oy_soni }}/{{ $k->muddati_oy }}">
                        <div class="mini-progress"><div class="mini-progress-bar" style="width:{{ $foiz }}%;background:{{ $barRang }}"></div></div>
                        <small class="text-muted" style="font-size:.66rem">{{ $oyQoldi > 0 ? $oyQoldi.'oy' : 'Yopiq' }}</small>
                    </div>
                </td>
                <td class="text-center">
                    <a href="{{ route('kreditlar.show', $k) }}" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="18" class="text-center text-muted py-5">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    Shartnomalar topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($kreditlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $kreditlar->firstItem() }}–{{ $kreditlar->lastItem() }} / {{ $kreditlar->total() }} ta</small>
    {{ $kreditlar->links('pagination::bootstrap-5') }}
</div>
@endif

</div>

@endsection

@push('scripts')
<script>
(function() {
    document.querySelectorAll('#shartnoma-table thead th').forEach(th => {
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

    // Ustunlarni ko'rsatish/yashirish (Holat, Progress) — holat localStorage'da saqlanadi
    var UST_KEY = 'shartnoma_ustun_korinishi';
    var saqlangan = {};
    try { saqlangan = JSON.parse(localStorage.getItem(UST_KEY)) || {}; } catch (e) {}

    function ustunniQoy(col, korinsin) {
        document.querySelectorAll('.col-' + col).forEach(el => el.classList.toggle('d-none', !korinsin));
    }

    document.querySelectorAll('.ustun-toggle').forEach(cb => {
        var col = cb.dataset.col;
        var def = cb.dataset.default === '1';
        var korinsin = saqlangan.hasOwnProperty(col) ? !!saqlangan[col] : def;
        cb.checked = korinsin;
        ustunniQoy(col, korinsin);
        cb.addEventListener('change', function() {
            saqlangan[col] = cb.checked;
            localStorage.setItem(UST_KEY, JSON.stringify(saqlangan));
            ustunniQoy(col, cb.checked);
        });
    });
})();
</script>
@endpush
