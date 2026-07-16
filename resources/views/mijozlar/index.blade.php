@extends('layouts.app')

@section('title', 'Mijozlar')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mijozlar</li>

{{-- Mobile FAB: yangi mijoz --}}
@if(Auth::user()->isMenejerYoki())
<a href="{{ route('mijozlar.create') }}"
   class="mobile-fab btn btn-primary"
   title="Yangi mijoz"
   onclick="return litsenziyaTekshir('mijoz', 'Mijoz qo\'shish')">
    <i class="bi bi-person-plus-fill"></i>
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

.bank-table tbody td.sticky-col { position:sticky; left:0; z-index:2; background:inherit; border-right:2px solid #93c5fd; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; font-size:.85rem; }
.num.text-muted { color:#334155 !important; }

.bank-wrap { overflow:auto; height:calc(100vh - 195px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 170px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.b-faol { background:#22c55e; color:#fff; }
.b-nofaol { background:#64748b; color:#fff; }
.b-sudda { background:#ef4444; color:#fff; }
.b-yomon { background:#f59e0b; color:#fff; }

.col-resizer { position:absolute; right:0; top:0; bottom:0; width:5px; cursor:col-resize; background:transparent; z-index:2; }
.col-resizer:hover, .col-resizer.resizing { background:rgba(255,255,255,.4); }

.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:10px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.holat-tabs { display:flex; gap:5px; flex-wrap:nowrap; overflow-x:auto; padding-bottom:2px; }
.holat-tabs::-webkit-scrollbar { height:4px; }
.holat-tab {
    display:inline-flex; align-items:center; gap:5px; white-space:nowrap; text-decoration:none;
    padding:6px 13px; border-radius:7px 7px 0 0; font-size:.74rem; font-weight:700;
    background:#eef4ff; color:#1e3a8a; border:1px solid #93c5fd; border-bottom:none;
}
.holat-tab:hover { background:#dbeafe; color:#1e3a8a; }
.holat-tab.active { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
</style>
@endpush

@php
$holatTablari = [
    ''       => ['label' => 'Barchasi', 'icon' => 'bi-list-ul'],
    'faol'   => ['label' => 'AKTIV',    'icon' => 'bi-check-circle'],
    'nofaol' => ['label' => 'PASSIV',   'icon' => 'bi-archive'],
    'sudda'  => ['label' => 'SUDDA',    'icon' => 'bi-exclamation-triangle'],
    'yomon'  => ['label' => 'YOMON',    'icon' => 'bi-hand-thumbs-down'],
];
@endphp

@section('content')

{{-- Mobile card ro'yxat (faqat telefonda) — o'zgarishsiz --}}
<div class="d-md-none">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0 fw-bold">
            <i class="bi bi-people me-1"></i> Mijozlar
            <span class="badge bg-secondary ms-1">{{ $mijozlar->total() }}</span>
        </h5>
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('mijozlar.create') }}" class="btn btn-primary btn-sm" onclick="return litsenziyaTekshir('mijoz', 'Mijoz qo\'shish')">
            <i class="bi bi-plus-lg me-1"></i> Yangi mijoz
        </a>
        @endif
    </div>

    <div class="holat-tabs mb-2">
        @foreach($holatTablari as $key => $h)
        <a class="holat-tab {{ request('holat', '') === $key ? 'active' : '' }}"
           href="{{ route('mijozlar.index', array_merge(request()->except(['holat', 'page']), $key !== '' ? ['holat' => $key] : [])) }}">
            <i class="bi {{ $h['icon'] }}"></i> {{ $h['label'] }}
        </a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="holat" value="{{ request('holat') }}">
                <div class="col-sm-5">
                    <input type="search" name="qidiruv" class="form-control form-control-sm"
                           placeholder="Ism, familiya, telefon, passport..."
                           value="{{ request('qidiruv') }}" id="qidiruv-input-mobile">
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
                <div class="col-sm-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search me-1"></i> Qidirish
                    </button>
                    <a href="{{ route('mijozlar.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @forelse($mijozlar as $mijoz)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <a href="{{ route('mijozlar.show', $mijoz) }}" class="fw-bold text-decoration-none">
                    {{ $mijoz->familiya }} {{ $mijoz->ism }}
                </a>
                <span class="badge bg-{{ $mijoz->holat_rangi }}">
                    {{ $mijoz->holat_nomi }}
                </span>
            </div>
            @if($mijoz->otasining_ismi)
            <div class="text-muted small mb-1">{{ $mijoz->otasining_ismi }}</div>
            @endif
            <div class="d-flex gap-3 mb-2" style="font-size:.85rem">
                <a href="tel:{{ $mijoz->telefon }}" class="text-decoration-none">
                    <i class="bi bi-telephone me-1 text-muted"></i>{{ $mijoz->telefon }}
                </a>
                @if(Auth::user()->isAdmin() && $mijoz->filial)
                <span class="badge bg-secondary">{{ $mijoz->filial->kod }}</span>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $mijoz->passport_tolik }}</small>
                <a href="{{ route('mijozlar.show', $mijoz) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                    <i class="bi bi-eye me-1"></i>Ko'rish
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-people fs-3 d-block mb-2"></i>
        Mijozlar topilmadi
    </div>
    @endforelse
    @if($mijozlar->hasPages())
    <div class="mt-2">{{ $mijozlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Bank-style jadval (desktop) --}}
<div class="d-none d-md-block">

<div class="holat-tabs">
    @foreach($holatTablari as $key => $h)
    <a class="holat-tab {{ request('holat', '') === $key ? 'active' : '' }}"
       href="{{ route('mijozlar.index', array_merge(request()->except(['holat', 'page']), $key !== '' ? ['holat' => $key] : [])) }}">
        <i class="bi {{ $h['icon'] }}"></i> {{ $h['label'] }}
    </a>
    @endforeach
</div>

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <input type="hidden" name="holat" value="{{ request('holat') }}">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-people" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Mijozlar</span>
            <span class="badge bg-secondary">{{ $mijozlar->total() }}</span>
        </div>
        <div style="width:220px">
            <input type="search" name="qidiruv" class="form-control" placeholder="Ism, familiya, telefon, passport..." value="{{ request('qidiruv') }}" id="qidiruv-input">
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
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
            <a href="{{ route('mijozlar.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
            <div class="dropdown">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2" style="height:32px" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Ustunlarni ko'rsatish/yashirish">
                    <i class="bi bi-layout-three-columns"></i>
                </button>
                <ul class="dropdown-menu p-2" style="font-size:.8rem;max-height:340px;overflow:auto;min-width:190px">
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="telefon" data-default="1"> Telefon</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="passport" data-default="1"> Passport</label></li>
                    @if(Auth::user()->isAdmin())
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="filial" data-default="1"> Filial</label></li>
                    @endif
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="holat" data-default="1"> Holat</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="shartnomalar" data-default="1"> Shartnomalar</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="qoldiq" data-default="1"> Umumiy qarz</label></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="manzil" data-default="0"> Manzil</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="jinsi" data-default="0"> Jinsi</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="tug-sana" data-default="0"> Tug'ilgan sana</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="pinfl" data-default="0"> PINFL</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="ish-joyi" data-default="0"> Ish joyi</label></li>
                    <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle" data-col="royxatdan" data-default="0"> Ro'yxatdan o'tgan</label></li>
                </ul>
            </div>
        </div>
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('mijozlar.create') }}" class="btn btn-warning btn-sm ms-auto fw-bold" onclick="return litsenziyaTekshir('mijoz', 'Mijoz qo\'shish')">
            <i class="bi bi-plus-lg me-1"></i>Yangi mijoz
        </a>
        @endif
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table" id="mijoz-table">
        <thead>
            <tr>
                <th class="tl sticky-col">F.I.O.</th>
                <th class="tl col-telefon">Telefon</th>
                <th class="tl col-passport">Passport</th>
                @if(Auth::user()->isAdmin())<th class="col-filial">Filial</th>@endif
                <th class="col-holat">Holat</th>
                <th class="col-shartnomalar">Shartnomalar</th>
                <th class="col-qoldiq">Umumiy qarz</th>
                <th class="tl col-manzil d-none">Manzil</th>
                <th class="col-jinsi d-none">Jinsi</th>
                <th class="col-tug-sana d-none">Tug'ilgan sana</th>
                <th class="tl col-pinfl d-none">PINFL</th>
                <th class="tl col-ish-joyi d-none">Ish joyi</th>
                <th class="col-royxatdan d-none">Ro'yxatdan o'tgan</th>
                <th style="width:70px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($mijozlar as $mijoz)
            <tr>
                <td class="tl sticky-col">
                    <a href="{{ route('mijozlar.show', $mijoz) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $mijoz->familiya }} {{ $mijoz->ism }}</a>
                    @if($mijoz->otasining_ismi)
                    <span class="text-muted small">{{ $mijoz->otasining_ismi }}</span>
                    @endif
                </td>
                <td class="tl text-muted col-telefon">
                    <a href="tel:{{ $mijoz->telefon }}" class="text-decoration-none text-muted">{{ $mijoz->telefon }}</a>
                </td>
                <td class="tl text-muted col-passport">{{ $mijoz->passport_tolik }}</td>
                @if(Auth::user()->isAdmin())
                <td class="text-center col-filial"><span class="badge-modern" style="background:#1e293b;color:#fff">{{ $mijoz->filial->kod ?? '—' }}</span></td>
                @endif
                <td class="text-center col-holat"><span class="badge-modern b-{{ $mijoz->holat }}">{{ $mijoz->holat_nomi }}</span></td>
                <td class="num text-muted col-shartnomalar">{{ $mijoz->kreditlar_count }} ta</td>
                <td class="num fw-bold col-qoldiq" style="color:{{ $mijoz->jami_qoldiq_qarz > 0 ? '#dc2626' : '#16a34a' }}">
                    {{ $mijoz->jami_qoldiq_qarz > 0 ? number_format($mijoz->jami_qoldiq_qarz, 0, '.', ' ') : '—' }}
                </td>
                <td class="tl text-muted col-manzil d-none">{{ Str::limit($mijoz->manzil, 30) ?: '—' }}</td>
                <td class="text-center col-jinsi d-none">{{ $mijoz->jinsi_nomi }}</td>
                <td class="text-center col-tug-sana d-none">{{ $mijoz->tug_sana ? $mijoz->tug_sana->format('d.m.Y') : '—' }}</td>
                <td class="tl text-muted col-pinfl d-none">{{ $mijoz->pinfl ?: '—' }}</td>
                <td class="tl text-muted col-ish-joyi d-none">{{ Str::limit($mijoz->ish_joyi, 25) ?: '—' }}</td>
                <td class="text-center text-muted col-royxatdan d-none">{{ $mijoz->created_at ? $mijoz->created_at->format('d.m.Y') : '—' }}</td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="{{ route('mijozlar.show', $mijoz) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ko'rish">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(Auth::user()->isMenejerYoki())
                        <a href="{{ route('mijozlar.edit', $mijoz) }}" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Tahrirlash">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center text-muted py-5">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    Mijozlar topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($mijozlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $mijozlar->firstItem() }}–{{ $mijozlar->lastItem() }} / {{ $mijozlar->total() }} ta</small>
    {{ $mijozlar->links('pagination::bootstrap-5') }}
</div>
@endif

</div>

@endsection

@push('scripts')
<script>
// Qidiruv faqat "Enter" yoki tugma bosilganda ishlaydi
$('#qidiruv-input, #qidiruv-input-mobile').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        $(this).closest('form').submit();
    }
});

(function() {
    document.querySelectorAll('#mijoz-table thead th').forEach(th => {
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

    // Ustunlarni ko'rsatish/yashirish — holat localStorage'da saqlanadi
    var UST_KEY = 'mijoz_ustun_korinishi';
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
