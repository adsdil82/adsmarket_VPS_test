@extends('layouts.app')
@section('title','SMS')
@section('breadcrumb')
<li class="breadcrumb-item active">SMS</li>
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

.jami-row th { background:linear-gradient(180deg,#fde68a,#fbbf24) !important; color:#7c2d12; position:sticky; top:26px; z-index:6; }
.jami-row th.sticky-col { background:linear-gradient(180deg,#fde68a,#fbbf24) !important; z-index:8; }
.jami-row th.num { font-family:'Roboto Mono','Courier New',monospace; }

.bank-wrap { overflow:auto; max-height:calc(100vh - 320px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { max-height:calc(100vh - 260px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 12px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.check-col { width:44px; text-align:center !important; }
.check-col .form-check-input { width:20px; height:20px; margin:0; cursor:pointer; border:2px solid #ef4444; box-shadow:none; }
.check-col .form-check-input:checked { background-color:#ef4444; border-color:#ef4444; }
.check-col .form-check-input:focus { box-shadow:0 0 0 .2rem rgba(239,68,68,.25); }
th.check-col { vertical-align:middle; }

.yuborish-tur-karta { cursor:pointer;border:2px solid transparent;border-radius:10px;padding:14px 12px;transition:all .2s; }
.yuborish-tur-karta:hover { border-color:var(--bs-primary); }
.yuborish-tur-karta.tanlangan { border-color:var(--bs-primary);background:rgba(13,110,253,.07); }
.yuborish-tur-karta .karta-icon { font-size:1.6rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 pb-3" style="margin-top:-40px">
{{-- Muvaffaqiyat/xato xabarlari layouts/app.blade.php da global ko'rsatiladi — bu yerda takrorlanmaydi --}}

<div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold"><i class="bi bi-chat-dots-fill text-warning me-2"></i>SMS</h5>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('xabarnoma.shablonlar.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-text me-1"></i>Shablonlar
        </a>
        <a href="{{ Auth::user()->isAdmin() ? route('admin.sozlamalar').'#collapseSms' : route('xabarnoma.sms.sozlamalar') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear me-1"></i>Sozlamalar
        </a>
    </div>
</div>

<ul class="nav nav-tabs mb-0">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'kutilayotgan' ? 'active fw-semibold' : '' }}" href="{{ route('xabarnoma.sms.kutilayotgan') }}">
            <i class="bi bi-hourglass-split me-1"></i>Kutilayotgan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'guruhli' ? 'active fw-semibold' : '' }}" href="{{ route('xabarnoma.sms.guruhli') }}">
            <i class="bi bi-people me-1"></i>Guruhli yuborish
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'yakka' ? 'active fw-semibold' : '' }}" href="{{ route('xabarnoma.sms.yakka') }}">
            <i class="bi bi-person me-1"></i>Yakka yuborish
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'tarix' ? 'active fw-semibold' : '' }}" href="{{ route('xabarnoma.sms.tarix') }}">
            <i class="bi bi-clock-history me-1"></i>Tarix
        </a>
    </li>
</ul>

{{-- ─── Tab 0: Kutilayotgan (checkbox + filtr + shablon) ───────── --}}
@if($tab === 'kutilayotgan')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('xabarnoma.sms.kutilayotgan') }}" class="d-flex align-items-end flex-wrap gap-2" id="kutilayotgan-filtr-form">
        <input type="hidden" name="filtr" value="{{ $filtr }}" id="filtr-input">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $kreditlar->total() }}</span>
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-danger {{ $filtr === 'kechikkan' ? 'active' : '' }}" onclick="filtrTanla('kechikkan')">
                <i class="bi bi-exclamation-triangle me-1"></i>Kechikkanlar
            </button>
            <button type="button" class="btn btn-outline-warning {{ $filtr === 'ertaga' ? 'active' : '' }}" onclick="filtrTanla('ertaga')">
                <i class="bi bi-calendar-event me-1"></i>Ertaga to'laydiganlar
            </button>
            <button type="button" class="btn btn-outline-secondary {{ $filtr === 'hammasi' ? 'active' : '' }}" onclick="filtrTanla('hammasi')">
                <i class="bi bi-list-ul me-1"></i>Hammasi
            </button>
        </div>
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px" onchange="this.form.submit()">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" {{ (string) $filialId === (string) $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
            @endforeach
        </select>
        @endif
        <input type="search" name="qidiruv" class="form-control" style="width:200px" placeholder="Mijoz yoki shartnoma..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv)
        <a href="{{ route('xabarnoma.sms.kutilayotgan', ['filtr' => $filtr, 'filial_id' => $filialId]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
    </form>
</div>

<form method="POST" action="{{ route('xabarnoma.sms.kutilayotgan.yubor') }}" id="kutilayotganBulkForm">
@csrf
<div class="filter-bar mb-0" style="border-top:none;border-radius:0;background:linear-gradient(90deg,#fef3c7,#fde68a)">
    <div class="d-flex align-items-end flex-wrap gap-2">
        <div style="min-width:260px">
            <label class="form-label small fw-medium mb-1">Shablon tanlang</label>
            <select name="template_id" id="kutilayotgan-shablon" class="form-select form-select-sm" required>
                <option value="">— Shablon tanlang —</option>
                @foreach($shablonlar as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="fw-bold text-danger" id="tanlanganSoniKutilayotgan" style="font-size:1rem">0</span>
            <span class="small text-muted">ta tanlandi</span>
            <button type="submit" class="btn btn-warning fw-bold btn-sm" id="kutilayotganYuborBtn" disabled
                    onclick="return confirm('Tanlangan mijozlarga SMS yuborilsinmi?')">
                <i class="bi bi-send-fill me-1"></i>Tanlanganlarni yuborish
            </button>
        </div>
    </div>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="check-col sticky-col" style="width:36px"><input type="checkbox" class="form-check-input" id="hammaBelgilaKutilayotgan"></th>
                <th class="tl">Shartnoma</th>
                <th class="tl">Mijoz</th>
                <th class="tl">Filial</th>
                <th class="tl">Telefon</th>
                <th>Qoldiq qarz</th>
                <th>Kechikkan summa</th>
            </tr>
            <tr class="jami-row">
                <th class="tl sticky-col" colspan="5">JAMI ({{ $kreditlar->total() }} ta)</th>
                <th></th>
                <th class="num">{{ number_format($kechikkanJami ?? 0, 0, '.', ' ') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kreditlar as $kredit)
            <tr>
                <td class="check-col sticky-col">
                    <input type="checkbox" class="form-check-input kredit-check-sms" name="kredit_ids[]" value="{{ $kredit->id }}" {{ $kredit->mijoz?->telefon ? '' : 'disabled' }}>
                </td>
                <td class="tl"><a href="{{ route('kreditlar.show', $kredit) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $kredit->shartnoma_raqam }}</a></td>
                <td class="tl">{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</td>
                <td class="tl text-muted">{{ $kredit->filial?->nomi }}</td>
                <td class="tl text-muted">
                    {{ $kredit->mijoz?->telefon ?: '—' }}
                    @unless($kredit->mijoz?->telefon)
                    <span class="badge-modern" style="background:#f59e0b;color:#000">Tel yo'q</span>
                    @endunless
                </td>
                <td class="num">{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</td>
                <td class="num" style="color:#dc2626">{{ number_format($kredit->kechikkan_summa ?? 0, 0, '.', ' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Mijozlar topilmadi</td></tr>
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

{{-- ─── Tab 1: Guruhli yuborish ────────────────────────────────── --}}
@if($tab === 'guruhli')
<div class="border border-top-0 rounded-bottom p-3 bg-white shadow-sm">
<form method="POST" action="{{ route('xabarnoma.sms.guruhli.send') }}" id="guruhli-form">
@csrf

<div class="row g-3">

  {{-- CHAP: Filtr va yuborish turi --}}
  <div class="col-lg-7">

    {{-- 1. Yuborish turini tanlash --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header py-2 fw-bold"><i class="bi bi-funnel me-2"></i>1. Yuborish turini tanlang</div>
      <div class="card-body">
        <input type="hidden" name="type" id="tur-input" value="">
        <div class="row g-2" id="tur-kartalar">
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="overdue" onclick="turTanla('overdue',this)">
              <div class="karta-icon">⚠️</div>
              <div class="small fw-bold mt-1">Kechikkan kreditlar</div>
              <div class="text-muted" style="font-size:.7rem">Muddati o'tgan</div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="upcoming" onclick="turTanla('upcoming',this)">
              <div class="karta-icon">📅</div>
              <div class="small fw-bold mt-1">Oldindan ogohlantirish</div>
              <div class="text-muted" style="font-size:.7rem">Yaqinlashgan to'lov</div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="branch" onclick="turTanla('branch',this)">
              <div class="karta-icon">🏢</div>
              <div class="small fw-bold mt-1">Filial bo'yicha</div>
              <div class="text-muted" style="font-size:.7rem">Filial mijozlari</div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="custom" onclick="turTanla('custom',this)">
              <div class="karta-icon">🔧</div>
              <div class="small fw-bold mt-1">Custom filter</div>
              <div class="text-muted" style="font-size:.7rem">O'zingiz tanlang</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 2. Filtrlar --}}
    <div class="card border-0 shadow-sm mb-3" id="filtr-blok" style="display:none">
      <div class="card-header py-2 fw-bold"><i class="bi bi-sliders me-2"></i>2. Filtrlar</div>
      <div class="card-body">

        {{-- Kechikkan kunlar (overdue) --}}
        <div id="filtr-overdue" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Kechikkan kun (dan)</label>
              <select name="min_days" class="form-select form-select-sm">
                <option value="1">1 kundan</option><option value="4">4 kundan</option>
                <option value="8">8 kundan</option><option value="16">16 kundan</option>
                <option value="31">31 kundan</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Kechikkan kun (gacha)</label>
              <select name="max_days" class="form-select form-select-sm">
                <option value="3">3 kungacha</option><option value="7">7 kungacha</option>
                <option value="15">15 kungacha</option><option value="30">30 kungacha</option>
                <option value="9999" selected>Cheksiz</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Minimal qarz (so'm)</label>
              <input type="number" name="min_amount" class="form-control form-control-sm" value="0" min="0" step="10000">
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial</label>
              <select name="filial_id" class="form-select form-select-sm">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Oldindan ogohlantirish (upcoming) --}}
        <div id="filtr-upcoming" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Necha kun oldin ogohlantirish</label>
              <select name="days" class="form-select form-select-sm">
                <option value="1">1 kun oldin</option><option value="3" selected>3 kun oldin</option>
                <option value="5">5 kun oldin</option><option value="7">7 kun oldin</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial</label>
              <select name="filial_id_upcoming" class="form-select form-select-sm">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Filial bo'yicha (branch) --}}
        <div id="filtr-branch" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial <span class="text-danger">*</span></label>
              <select name="filial_id_branch" class="form-select form-select-sm" required>
                <option value="">— Filial tanlang —</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Faqat qarzdorlar</label>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="only_debtors" value="1" id="only-debt" checked>
                <label class="form-check-label small" for="only-debt">Faqat qoldig'i bor mijozlar</label>
              </div>
            </div>
          </div>
        </div>

        {{-- Custom --}}
        <div id="filtr-custom" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial</label>
              <select name="filial_id_custom" class="form-select form-select-sm">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Minimal qarz</label>
              <input type="number" name="min_debt" class="form-control form-control-sm" value="0" min="0">
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Holat</label>
              <select name="statuses[]" class="form-select form-select-sm" multiple>
                <option value="faol" selected>Faol</option>
                <option value="muddati_otgan" selected>Muddati o'tgan</option>
                <option value="muzlatilgan">Muzlatilgan</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Limit</label>
              <input type="number" name="limit" class="form-control form-control-sm" value="200" min="1" max="500">
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button type="button" class="btn btn-sm btn-outline-info" onclick="previewOl()">
            <i class="bi bi-eye me-1"></i>Natijani ko'rish
          </button>
        </div>
        {{-- Hidden: filial_id normalize --}}
        <input type="hidden" id="filial_id_resolved" name="filial_id" value="">
      </div>
    </div>

  </div>{{-- /col-lg-7 --}}

  {{-- O'NG: Shablon + natija --}}
  <div class="col-lg-5">

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header py-2 fw-bold"><i class="bi bi-file-text me-2"></i>3. Shablon tanlang</div>
      <div class="card-body">
        <select name="template_id" id="template-select" class="form-select" required onchange="shablon_preview(this)">
          <option value="">— Shablon tanlang —</option>
          @foreach($shablonlar as $s)
          <option value="{{ $s->id }}" data-body="{{ $s->body }}">{{ $s->name }}</option>
          @endforeach
        </select>
        <div id="shablon-preview" class="mt-2 p-2 bg-light rounded small text-muted" style="display:none;white-space:pre-wrap"></div>
      </div>
    </div>

    {{-- Natija --}}
    <div class="card border-0 shadow-sm mb-3" id="natija-karta" style="display:none">
      <div class="card-header py-2 fw-bold"><i class="bi bi-bar-chart me-2"></i>Natija</div>
      <div class="card-body">
        <div class="row g-2 text-center mb-3">
          <div class="col-4">
            <div class="badge bg-primary fs-5 d-block mb-1" id="n-total">0</div>
            <div class="text-muted small">Jami</div>
          </div>
          <div class="col-4">
            <div class="badge bg-warning text-dark fs-5 d-block mb-1" id="n-nophone">0</div>
            <div class="text-muted small">Tel yo'q</div>
          </div>
          <div class="col-4">
            <div class="badge bg-danger fs-5 d-block mb-1" id="n-badphone">0</div>
            <div class="text-muted small">Noto'g'ri tel</div>
          </div>
        </div>
        <div id="preview-table" class="table-responsive" style="max-height:200px;overflow-y:auto"></div>
      </div>
    </div>

    {{-- Yuborish tugmalar --}}
    <div class="card border-0 shadow-sm" id="yuborish-karta" style="display:none">
      <div class="card-body">
        <div class="alert alert-warning py-2 small mb-3">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Guruhli SMS yuborishdan oldin test rejimda sinab ko'ring.
        </div>
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-outline-info" onclick="testYuborish()">
            <i class="bi bi-send me-1"></i>Test SMS yuborish
          </button>
          <button type="submit" class="btn btn-warning fw-bold" id="guruhli-submit"
                  onclick="return confirm('Haqiqatan ham guruhga SMS yuborilsinmi?')">
            <i class="bi bi-send-fill me-1"></i>Guruhga yuborish
          </button>
        </div>
      </div>
    </div>

  </div>{{-- /col-lg-5 --}}
</div>{{-- /row --}}
</form>
</div>
@endif

{{-- ─── Tab 2: Yakka yuborish ──────────────────────────────────── --}}
@if($tab === 'yakka')
<div class="border border-top-0 rounded-bottom p-3 bg-white shadow-sm">
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold"><i class="bi bi-person-check me-2"></i>Yakka SMS yuborish</div>
    <div class="card-body">
        <form method="POST" action="{{ route('xabarnoma.sms.yakka.send') }}">
        @csrf
            {{-- Mijoz qidirish --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Mijoz</label>
                <input type="hidden" id="customer_id" name="customer_id">
                <input type="hidden" id="contract_id" name="contract_id">
                <div class="input-group">
                    <input type="text" id="mijoz-qidiruv" class="form-control"
                           placeholder="Ism yoki telefon yozing..." autocomplete="off">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
                <div id="mijoz-dropdown" class="list-group shadow-sm mt-1" style="position:absolute;z-index:100;width:100%;max-height:200px;overflow-y:auto;display:none"></div>
                <div id="mijoz-info" class="mt-1 small text-muted"></div>
            </div>

            {{-- Telefon --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Telefon <span class="text-danger">*</span></label>
                <input type="text" name="phone" id="phone" class="form-control"
                       placeholder="+998 90 000 00 00" required>
            </div>

            {{-- Shablon --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Shablon (ixtiyoriy)</label>
                <select name="template_id" id="shablon-select" class="form-select">
                    <option value="">— Qo'lda yozish —</option>
                    @foreach($shablonlar as $s)
                    <option value="{{ $s->id }}" data-body="{{ $s->body }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Xabar matni --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Xabar matni <span class="text-danger">*</span></label>
                <textarea name="message" id="message-text" class="form-control" rows="4"
                          placeholder="Xabar matni..." required minlength="5" maxlength="800"
                          oninput="charHisob()"></textarea>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted" id="char-count">0 / 160 belgi (1 segment)</small>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-bold">
                    <i class="bi bi-send me-1"></i>Yuborish
                </button>
                <a href="{{ route('xabarnoma.sms.guruhli') }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
</div>
@endif

{{-- ─── Tab 3: Tarix ───────────────────────────────────────────── --}}
@if($tab === 'tarix')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('xabarnoma.sms.tarix') }}" class="d-flex align-items-end flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $loglar->total() }}</span>
        </div>
        <select name="status" class="form-select" style="width:160px" onchange="this.form.submit()">
            <option value="">Barcha holatlar</option>
            <option value="sent"    {{ $holat === 'sent'    ? 'selected' : '' }}>Yuborildi</option>
            <option value="test"    {{ $holat === 'test'    ? 'selected' : '' }}>Test</option>
            <option value="failed"  {{ $holat === 'failed'  ? 'selected' : '' }}>Xato</option>
            <option value="skipped" {{ $holat === 'skipped' ? 'selected' : '' }}>O'tkazildi</option>
        </select>
        <input type="date" name="dan_sana" class="form-control" style="width:150px" value="{{ request('dan_sana') }}">
        <input type="date" name="gacha_sana" class="form-control" style="width:150px" value="{{ request('gacha_sana') }}">
        <input type="search" name="qidiruv" class="form-control" style="width:220px" placeholder="Telefon yoki mijoz..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv || $holat || request('dan_sana') || request('gacha_sana'))
        <a href="{{ route('xabarnoma.sms.tarix') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="ms-auto d-flex align-items-center gap-3 small">
            <span>Jami: <strong>{{ $statistika['jami'] }}</strong></span>
            <span class="text-success">Yuborildi: <strong>{{ $statistika['yuborildi'] }}</strong></span>
            <span class="text-danger">Xato: <strong>{{ $statistika['xato'] }}</strong></span>
            <span class="text-info">Bugun: <strong>{{ $statistika['bugun'] }}</strong></span>
        </div>
    </form>
</div>

@if($batchlar->count())
<div class="border-start border-end small">
    <table class="bank-table" style="margin-bottom:0">
        <thead>
            <tr><th class="tl">Sana</th><th class="tl">Tur</th><th>Jami</th><th>Yuborildi</th><th>Xato</th><th class="tl">Holat</th></tr>
        </thead>
        <tbody>
            @foreach($batchlar as $b)
            <tr>
                <td class="tl">{{ $b->created_at->format('d.m.Y H:i') }}</td>
                <td class="tl text-muted">{{ $b->title }}</td>
                <td class="num">{{ $b->total_recipients }}</td>
                <td class="num text-success">{{ $b->total_sent }}</td>
                <td class="num text-danger">{{ $b->total_failed }}</td>
                <td class="tl"><span class="badge bg-{{ $b->status_rangi }}" style="font-size:.65rem">{{ $b->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Sana</th><th class="tl">Telefon</th><th class="tl">Mijoz</th>
                <th class="tl">Shablon</th><th class="tl">Holat</th><th class="tl">Xabar / Sabab</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loglar as $log)
            <tr>
                <td class="tl sticky-col text-muted">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                <td class="tl">{{ $log->phone }}</td>
                <td class="tl">{{ $log->customer?->familiya }} {{ $log->customer?->ism }}</td>
                <td class="tl text-muted">{{ $log->template?->name ?? '—' }}</td>
                <td class="tl">
                    <span class="badge bg-{{ $log->status_rangi }}" style="font-size:.65rem">{{ $log->status }}</span>
                </td>
                <td class="tl text-truncate" style="max-width:280px" title="{{ $log->error_message ?: $log->message }}">
                    @if($log->error_message)
                        <span class="text-danger">{{ Str::limit($log->error_message, 60) }}</span>
                    @else
                        <span class="text-muted">{{ Str::limit($log->message, 60) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Yozuvlar yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($loglar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $loglar->firstItem() }}–{{ $loglar->lastItem() }} / {{ $loglar->total() }} ta</small>
    {{ $loglar->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

</div>
@endsection

@push('scripts')
@if($tab === 'kutilayotgan')
<script>
function filtrTanla(f) {
    document.getElementById('filtr-input').value = f;
    document.getElementById('kutilayotgan-filtr-form').submit();
}

(function () {
    const hammaBelgila = document.getElementById('hammaBelgilaKutilayotgan');
    const yuborBtn = document.getElementById('kutilayotganYuborBtn');
    const soni = document.getElementById('tanlanganSoniKutilayotgan');
    const shablon = document.getElementById('kutilayotgan-shablon');

    function yangila() {
        const belgilangan = document.querySelectorAll('.kredit-check-sms:checked').length;
        soni.textContent = belgilangan;
        yuborBtn.disabled = belgilangan === 0 || !shablon.value;
    }

    if (hammaBelgila) {
        hammaBelgila.addEventListener('change', function () {
            document.querySelectorAll('.kredit-check-sms:not(:disabled)').forEach(cb => cb.checked = hammaBelgila.checked);
            yangila();
        });
    }
    document.querySelectorAll('.kredit-check-sms').forEach(cb => cb.addEventListener('change', yangila));
    if (shablon) shablon.addEventListener('change', yangila);
})();
</script>
@endif

@if($tab === 'guruhli')
<script>
var aktivTur = '';

function shablon_preview(sel) {
    var body = sel.options[sel.selectedIndex]?.dataset?.body || '';
    var el = document.getElementById('shablon-preview');
    if (body) { el.textContent = body; el.style.display = ''; }
    else { el.style.display = 'none'; }
}

function turTanla(tur, el) {
    aktivTur = tur;
    document.getElementById('tur-input').value = tur;
    document.querySelectorAll('.yuborish-tur-karta').forEach(k => k.classList.remove('tanlangan'));
    el.classList.add('tanlangan');

    document.getElementById('filtr-blok').style.display = '';
    document.querySelectorAll('.filtr-group').forEach(g => g.style.display = 'none');
    var filtrEl = document.getElementById('filtr-' + tur);
    if (filtrEl) filtrEl.style.display = '';
}

function getFilialId() {
    var turSelectors = {
        'overdue':  '[name=filial_id]',
        'upcoming': '[name=filial_id_upcoming]',
        'branch':   '[name=filial_id_branch]',
        'custom':   '[name=filial_id_custom]',
    };
    var sel = turSelectors[aktivTur] || '[name=filial_id]';
    var el = document.querySelector(sel);
    return el ? el.value : '';
}

function previewOl() {
    if (!aktivTur) { alert("Yuborish turini tanlang!"); return; }
    var tmplId = document.getElementById('template-select').value;
    if (!tmplId) { alert("Shablon tanlang!"); return; }

    var filialId = getFilialId();
    document.getElementById('filial_id_resolved').value = filialId;

    var fd = new FormData(document.getElementById('guruhli-form'));
    fd.set('type', aktivTur);
    fd.set('template_id', tmplId);
    fd.set('filial_id', filialId);

    fetch('{{ route("xabarnoma.sms.preview") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: fd
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('n-total').textContent    = d.total    || 0;
        document.getElementById('n-nophone').textContent  = d.no_phone || 0;
        document.getElementById('n-badphone').textContent = d.bad_phone|| 0;
        document.getElementById('natija-karta').style.display = '';
        document.getElementById('yuborish-karta').style.display = d.total > 0 ? '' : 'none';

        var rows = (d.preview || []).map(p =>
            '<tr><td class="small">' + (p.name||'') + '</td>' +
            '<td class="small">' + (p.phone||'') + '</td>' +
            '<td class="small text-muted" title="' + (p.message||'').replace(/"/g, "'") + '">' +
            (p.message||'').substring(0, 50) + (p.message?.length > 50 ? '...' : '') +
            '</td></tr>'
        ).join('');

        document.getElementById('preview-table').innerHTML = rows
            ? '<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Mijoz</th><th>Telefon</th><th>Xabar</th></tr></thead><tbody>' + rows + '</tbody></table>'
            : '<p class="text-muted small py-2 text-center">Namuna ko\'rsatilmadi</p>';
    })
    .catch(e => { console.error(e); alert('Server xatosi: ' + e.message); });
}

function testYuborish() {
    fetch('{{ route("xabarnoma.sms.test") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
    })
    .then(r => r.json())
    .then(d => {
        var ok = d.status === 'test' || d.status === 'sent';
        alert(ok ? 'Test SMS yuborildi! Provider: ' + (d.provider || '—') : 'Xato: ' + (d.error || d.message || 'Unknown'));
    })
    .catch(e => alert('Xato: ' + e.message));
}
</script>
@endif

@if($tab === 'yakka')
<script>
var mijozTimer;
document.getElementById('mijoz-qidiruv').addEventListener('input', function() {
    clearTimeout(mijozTimer);
    var q = this.value.trim();
    if (q.length < 2) { document.getElementById('mijoz-dropdown').style.display='none'; return; }
    mijozTimer = setTimeout(function() {
        $.getJSON('{{ route("mijozlar.ajax.qidiruv") }}', {q: q})
            .done(function(data) {
                var dd = document.getElementById('mijoz-dropdown');
                dd.innerHTML = '';
                if (!data.length) { dd.style.display='none'; return; }
                data.forEach(function(m) {
                    var el = document.createElement('a');
                    el.className = 'list-group-item list-group-item-action small';
                    el.href = '#';
                    el.textContent = m.fio + ' — ' + m.telefon;
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('customer_id').value = m.id;
                        document.getElementById('phone').value = m.telefon;
                        document.getElementById('mijoz-qidiruv').value = m.fio;
                        document.getElementById('mijoz-info').innerHTML = '<i class="bi bi-check-circle text-success me-1"></i>' + m.fio + ' · ' + m.telefon + ' · ' + m.passport;
                        dd.style.display = 'none';
                    });
                    dd.appendChild(el);
                });
                dd.style.display = '';
            });
    }, 300);
});

document.getElementById('shablon-select').addEventListener('change', function() {
    var body = this.options[this.selectedIndex].dataset.body || '';
    if (body) document.getElementById('message-text').value = body;
    charHisob();
});

function charHisob() {
    var len = document.getElementById('message-text').value.length;
    var segs = len <= 160 ? 1 : Math.ceil(len/153);
    document.getElementById('char-count').textContent = len + ' / 160 belgi (' + segs + ' segment)';
}
</script>
@endif
@endpush
