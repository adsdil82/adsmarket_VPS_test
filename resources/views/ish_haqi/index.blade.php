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
.filter-vr { width:1px; align-self:stretch; background:#93c5fd; margin:2px 2px; }

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

<ul class="nav nav-tabs mb-0">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'xodimlar' ? 'active fw-semibold' : '' }}" href="{{ route('ish_haqi.index', ['tab' => 'xodimlar']) }}">
            <i class="bi bi-people-fill me-1"></i>Xodimlar
        </a>
    </li>
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

{{-- ─── Tab 0: Xodimlar ─────────────────────────────────────────── --}}
@if($tab === 'xodimlar')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('ish_haqi.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="xodimlar">
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
        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#bonusTurlariModal"><i class="bi bi-gift me-1"></i>Bonus turlari</button>
            <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#shartnomaShablonlariModal"><i class="bi bi-file-text me-1"></i>Shartnoma shablonlari</button>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#xodimQoshishModal"><i class="bi bi-person-plus-fill me-1"></i>Xodim qo'shish</button>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Xodim</th>
                <th class="tl">Manba</th>
                <th class="tl">Lavozim</th>
                <th class="tl">Filial</th>
                <th class="tl">Ishga kirgan</th>
                <th>Oklad</th>
                <th class="tl">Qo'shimcha ish haqi</th>
                <th class="tl">Bonuslar</th>
                <th class="tl">Holat</th>
                <th class="tl">Ta'til</th>
                <th style="width:160px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($xodimlar as $x)
            @php
                $s = $x->ishHaqiSozlama;
                $oxirgiTatil = $x->tatillar->first();
                $shartnomaData = $x->shartnomalar->map(fn($sh) => [
                    'id' => $sh->id, 'raqami' => $sh->shartnoma_raqami,
                    'sana_label' => $sh->sana->format('d.m.Y'), 'sana_iso' => $sh->sana->format('Y-m-d'),
                    'holat' => $sh->holat, 'holat_badge' => $sh->holatBadge(), 'matn' => $sh->matn,
                    'amal_boshlanish' => optional($sh->amal_qilish_boshlanish)->format('Y-m-d'),
                    'amal_tugash' => optional($sh->amal_qilish_tugash)->format('Y-m-d'),
                ]);
            @endphp
            <tr>
                <td class="tl sticky-col fw-semibold">{{ $x->ism_familiya }}</td>
                <td class="tl">
                    @if($x->tizimga_kirish_bormi)
                    <span class="badge bg-primary">Tizim</span>
                    @else
                    <span class="badge bg-info text-dark">Qo'lda</span>
                    @endif
                </td>
                <td class="tl text-muted">{{ $s->lavozim ?? '—' }}</td>
                <td class="tl text-muted">{{ $x->filial?->nomi ?? '—' }}</td>
                <td class="tl text-muted">{{ optional($s->ishga_kirgan_sana)->format('d.m.Y') ?? '—' }}</td>
                <td class="num">{{ number_format($s->oklad ?? 0, 0, '.', ' ') }}</td>
                <td class="tl">
                    @if(($s->qoshimcha_ish_haqi ?? 0) > 0)
                    {{ number_format($s->qoshimcha_ish_haqi, 0, '.', ' ') }}
                    <div class="small text-muted" style="font-weight:normal">
                        {{ optional($s->qoshimcha_boshlanish_sana)->format('d.m.Y') ?? '—' }} – {{ optional($s->qoshimcha_tugash_sana)->format('d.m.Y') ?? 'muddatsiz' }}
                    </div>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="tl">
                    @forelse($x->bonuslar as $b)
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <span class="badge bg-warning text-dark" title="{{ $b->boshlanish_oy }}/{{ $b->boshlanish_yil }} – {{ $b->tugash_yil ? $b->tugash_oy.'/'.$b->tugash_yil : 'muddatsiz' }}">{{ $b->bonusTuri?->nomi }}</span>
                        <form method="POST" action="{{ route('ish_haqi.bonus.bekor', $b->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Bekor qilish" onclick="return confirm('Bu bonus bekor qilinsinmi?')"><i class="bi bi-x-circle"></i></button>
                        </form>
                    </div>
                    @empty
                    <span class="text-muted">—</span>
                    @endforelse
                </td>
                <td class="tl">
                    @if($s->ishdan_boshagan_sana)
                    <span class="badge bg-secondary">Bo'shagan ({{ $s->ishdan_boshagan_sana->format('d.m.Y') }})</span>
                    @else
                    <span class="badge bg-success">Faol</span>
                    @endif
                </td>
                <td class="tl">
                    @if($oxirgiTatil && $oxirgiTatil->holat === 'rejalashtirilgan')
                    {!! $oxirgiTatil->holatBadge() !!}
                    <div class="small text-muted" style="font-weight:normal">{{ $oxirgiTatil->boshlanish_sana->format('d.m.Y') }} – {{ $oxirgiTatil->rejalashtirilgan_qaytish_sana->format('d.m.Y') }}</div>
                    <div class="d-flex gap-1 mt-1">
                        <form method="POST" action="{{ route('ish_haqi.tatil.qaytdi', $oxirgiTatil->id) }}">
                            @csrf
                            <input type="hidden" name="haqiqiy_qaytgan_sana" value="{{ now()->toDateString() }}">
                            <button type="submit" class="btn btn-outline-success btn-sm py-0 px-1" onclick="return confirm('Bugun ({{ now()->format('d.m.Y') }}) qaytdi deb belgilansinmi?')">Qaytdi</button>
                        </form>
                        <form method="POST" action="{{ route('ish_haqi.tatil.bekor', $oxirgiTatil->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1" onclick="return confirm('Ta\'til bekor qilinsinmi?')">Bekor</button>
                        </form>
                    </div>
                    @elseif($oxirgiTatil)
                    {!! $oxirgiTatil->holatBadge() !!}
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 xodim-tahrirlash-btn" title="Tahrirlash"
                        data-xodim-id="{{ $x->id }}" data-xodim-nomi="{{ $x->ism_familiya }}" data-tizim="{{ $x->tizimga_kirish_bormi ? 1 : 0 }}"
                        data-lavozim="{{ $s->lavozim }}" data-telefon="{{ $s->telefon }}" data-manzil="{{ $s->manzil }}"
                        data-passport="{{ $s->passport_malumot }}" data-ishga-kirgan="{{ optional($s->ishga_kirgan_sana)->format('Y-m-d') }}"
                        data-ishdan-boshagan="{{ optional($s->ishdan_boshagan_sana)->format('Y-m-d') }}"
                        data-qoshimcha="{{ $s->qoshimcha_ish_haqi }}" data-qoshimcha-boshlanish="{{ optional($s->qoshimcha_boshlanish_sana)->format('Y-m-d') }}"
                        data-qoshimcha-tugash="{{ optional($s->qoshimcha_tugash_sana)->format('Y-m-d') }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 tatil-ber-btn" title="Ta'til berish"
                        data-xodim-id="{{ $x->id }}" data-xodim="{{ $x->ism_familiya }}">
                        <i class="bi bi-airplane"></i>
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2 bonus-ber-btn" title="Bonus biriktirish"
                        data-xodim-id="{{ $x->id }}" data-xodim="{{ $x->ism_familiya }}">
                        <i class="bi bi-gift"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 shartnoma-btn" title="Shartnoma"
                        data-xodim-id="{{ $x->id }}" data-xodim="{{ $x->ism_familiya }}"
                        data-shartnomalar="{{ base64_encode($shartnomaData->toJson()) }}">
                        <i class="bi bi-file-earmark-text"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="11" class="text-center text-muted py-5"><i class="bi bi-people fs-3 d-block mb-2"></i>Xodimlar topilmadi. "Xodim qo'shish" orqali qo'shing.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Xodim qo'shish modali --}}
<div class="modal fade" id="xodimQoshishModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-person-plus-fill me-1"></i>Xodim qo'shish</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('ish_haqi.xodim.qoshish') }}">
        @csrf
        <div class="modal-body">
            <div class="btn-group w-100 mb-3" role="group">
                <input type="radio" class="btn-check" name="manba" id="manba-tizim" value="tizim" checked>
                <label class="btn btn-outline-primary btn-sm" for="manba-tizim">Tizimdagi foydalanuvchidan</label>
                <input type="radio" class="btn-check" name="manba" id="manba-qolda" value="qolda">
                <label class="btn btn-outline-primary btn-sm" for="manba-qolda">Qo'lda kiritish</label>
            </div>

            <div id="manba-tizim-blok" class="row g-2 mb-2">
                <div class="col-12">
                    <label class="form-label small fw-medium">Foydalanuvchi</label>
                    <select name="xodim_id" class="form-select form-select-sm">
                        <option value="">— tanlang —</option>
                        @foreach($mavjudFoydalanuvchilar as $f)
                        <option value="{{ $f->id }}">{{ $f->ism_familiya }} ({{ $f->email }})</option>
                        @endforeach
                    </select>
                    @if($mavjudFoydalanuvchilar->isEmpty())
                    <div class="form-text text-warning">Barcha tizim foydalanuvchilari allaqachon xodim sifatida qo'shilgan.</div>
                    @endif
                </div>
            </div>

            <div id="manba-qolda-blok" class="row g-2 mb-2" style="display:none">
                <div class="col-6">
                    <label class="form-label small fw-medium">Ism familiya</label>
                    <input type="text" name="ism_familiya" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Filial</label>
                    <select name="filial_id" class="form-select form-select-sm">
                        <option value="">—</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small fw-medium">Lavozim</label>
                    <input type="text" name="lavozim" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Telefon</label>
                    <input type="text" name="telefon" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Manzil</label>
                    <input type="text" name="manzil" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Passport ma'lumoti</label>
                    <input type="text" name="passport_malumot" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Ishga kirgan sana</label>
                    <input type="date" name="ishga_kirgan_sana" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Oylik oklad (so'm)</label>
                    <input type="number" step="0.01" min="0" name="oklad" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="form-text mt-2">Bonus foizi, oylik reja va boshqa sozlamalar "Sozlamalar" tabida qo'shimcha belgilanadi.</div>
        </div>
        <div class="modal-footer py-2">
            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i>Qo'shish</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Xodim tahrirlash modali --}}
<div class="modal fade" id="xodimTahrirlashModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="xodimTahrirlashNomi"></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="xodimTahrirlashForm">
        @csrf
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-12" id="tahrirlash-ism-blok" style="display:none">
                    <label class="form-label small fw-medium">Ism familiya</label>
                    <input type="text" name="ism_familiya" id="tahrirlash-ism" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Lavozim</label>
                    <input type="text" name="lavozim" id="tahrirlash-lavozim" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Telefon</label>
                    <input type="text" name="telefon" id="tahrirlash-telefon" class="form-control form-control-sm">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Manzil</label>
                    <input type="text" name="manzil" id="tahrirlash-manzil" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Passport ma'lumoti</label>
                    <input type="text" name="passport_malumot" id="tahrirlash-passport" class="form-control form-control-sm">
                </div>
                <div class="col-6"></div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Ishga kirgan sana</label>
                    <input type="date" name="ishga_kirgan_sana" id="tahrirlash-ishga-kirgan" class="form-control form-control-sm">
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Ishdan bo'shagan sana</label>
                    <input type="date" name="ishdan_boshagan_sana" id="tahrirlash-ishdan-boshagan" class="form-control form-control-sm">
                </div>
                <div class="col-12"><hr class="my-1"></div>
                <div class="col-4">
                    <label class="form-label small fw-medium">Qo'shimcha ish haqi (so'm)</label>
                    <input type="number" step="0.01" min="0" name="qoshimcha_ish_haqi" id="tahrirlash-qoshimcha" class="form-control form-control-sm">
                </div>
                <div class="col-4">
                    <label class="form-label small fw-medium">Boshlanish sanasi</label>
                    <input type="date" name="qoshimcha_boshlanish_sana" id="tahrirlash-qoshimcha-boshlanish" class="form-control form-control-sm">
                </div>
                <div class="col-4">
                    <label class="form-label small fw-medium">Tugash sanasi</label>
                    <input type="date" name="qoshimcha_tugash_sana" id="tahrirlash-qoshimcha-tugash" class="form-control form-control-sm">
                    <div class="form-text">Bo'sh — muddatsiz</div>
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

{{-- Ta'til berish modali --}}
<div class="modal fade" id="tatilBerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-airplane me-1"></i>Ta'til berish — <span id="tatilXodimNomi"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="tatilBerForm">
        @csrf
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label small fw-medium">Turi</label>
                    <select name="turi" class="form-select form-select-sm" required>
                        <option value="yillik">Yillik ta'til</option>
                        <option value="haq_tolanmaydigan">Haq to'lanmaydigan</option>
                        <option value="kasallik">Kasallik varaqasi</option>
                        <option value="boshqa">Boshqa</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Boshlanish sanasi</label>
                    <input type="date" name="boshlanish_sana" class="form-control form-control-sm" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-medium">Rejalashtirilgan qaytish</label>
                    <input type="date" name="rejalashtirilgan_qaytish_sana" class="form-control form-control-sm" required>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control form-control-sm">
                </div>
            </div>
            <div class="form-text mt-2">Belgilangan kunlar (yopilmagan oylarda) Davomat tabiga avtomatik yoziladi.</div>
        </div>
        <div class="modal-footer py-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Ta'til berish</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Bonus biriktirish modali --}}
<div class="modal fade" id="bonusBerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-gift me-1"></i>Bonus biriktirish — <span id="bonusXodimNomi"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="bonusBerForm">
        @csrf
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label small fw-medium">Bonus turi</label>
                    <select name="bonus_turi_id" class="form-select form-select-sm" required>
                        @forelse($bonusTurlari->where('holat', 'faol') as $bt)
                        <option value="{{ $bt->id }}">{{ $bt->nomi }} ({{ $bt->hisoblash_turi === 'foiz_okladdan' ? $bt->standart_qiymat.'% oklad' : number_format($bt->standart_qiymat, 0, '.', ' ').' so\'m' }})</option>
                        @empty
                        <option value="" disabled>Bonus turlari yo'q — avval "Bonus turlari" orqali qo'shing</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Qiymat (bo'sh — standart qiymat)</label>
                    <input type="number" step="0.01" min="0" name="qiymat" class="form-control form-control-sm">
                </div>
                <div class="col-3">
                    <label class="form-label small fw-medium">Boshlanish oy</label>
                    <input type="number" min="1" max="12" name="boshlanish_oy" class="form-control form-control-sm" value="{{ now()->month }}" required>
                </div>
                <div class="col-3">
                    <label class="form-label small fw-medium">Boshlanish yil</label>
                    <input type="number" min="2020" max="2100" name="boshlanish_yil" class="form-control form-control-sm" value="{{ now()->year }}" required>
                </div>
                <div class="col-3">
                    <label class="form-label small fw-medium">Tugash oy</label>
                    <input type="number" min="1" max="12" name="tugash_oy" class="form-control form-control-sm">
                </div>
                <div class="col-3">
                    <label class="form-label small fw-medium">Tugash yil</label>
                    <input type="number" min="2020" max="2100" name="tugash_yil" class="form-control form-control-sm">
                    <div class="form-text">Bo'sh — muddatsiz</div>
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control form-control-sm">
                </div>
            </div>
        </div>
        <div class="modal-footer py-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Biriktirish</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Mehnat shartnomasi modali --}}
<div class="modal fade" id="shartnomaModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-file-earmark-text me-1"></i>Mehnat shartnomasi — <span id="shartnomaXodimNomi"></span></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
            <label class="form-label small fw-medium">Shartnoma</label>
            <select id="shartnoma-tanlov" class="form-select form-select-sm">
                <option value="yangi">+ Yangi shartnoma yaratish</option>
            </select>
        </div>

        <div id="shartnoma-yangi-blok">
            <form method="POST" id="shartnomaYaratForm">
                @csrf
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label small fw-medium">Shablon</label>
                        <select name="shablon_id" class="form-select form-select-sm" required>
                            @forelse($shartnomaShablonlari->where('holat', 'faol') as $sh)
                            <option value="{{ $sh->id }}">{{ $sh->nomi }}</option>
                            @empty
                            <option value="" disabled>Shablonlar yo'q — avval "Shartnoma shablonlari" orqali qo'shing</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-medium">Shartnoma raqami</label>
                        <input type="text" name="shartnoma_raqami" class="form-control form-control-sm">
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-medium">Sana</label>
                        <input type="date" name="sana" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="col-4"></div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Amal qilish boshlanishi</label>
                        <input type="date" name="amal_qilish_boshlanish" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Amal qilish tugashi</label>
                        <input type="date" name="amal_qilish_tugash" class="form-control form-control-sm">
                        <div class="form-text">Bo'sh — muddatsiz</div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100 mt-2"><i class="bi bi-magic me-1"></i>Shablondan yaratish</button>
            </form>
        </div>

        <div id="shartnoma-tahrirlash-blok" style="display:none">
            <div class="mb-2 d-flex align-items-center gap-2">
                <span id="shartnoma-holat-badge"></span>
                <a href="#" target="_blank" id="shartnoma-pdf-link" class="btn btn-outline-primary btn-sm ms-auto">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF ko'rish / chop etish
                </a>
            </div>
            <form method="POST" id="shartnomaSaqlaForm">
                @csrf
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label small fw-medium">Shartnoma raqami</label>
                        <input type="text" name="shartnoma_raqami" id="st-raqami" class="form-control form-control-sm">
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-medium">Sana</label>
                        <input type="date" name="sana" id="st-sana" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-4"></div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Amal boshlanishi</label>
                        <input type="date" name="amal_qilish_boshlanish" id="st-amal-boshlanish" class="form-control form-control-sm">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-medium">Amal tugashi</label>
                        <input type="date" name="amal_qilish_tugash" id="st-amal-tugash" class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-medium">Matn</label>
                        <textarea name="matn" id="st-matn" class="form-control form-control-sm" rows="12" style="font-family:monospace;font-size:.78rem"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100 mt-2"><i class="bi bi-save me-1"></i>Saqlash</button>
            </form>
            <div class="d-flex gap-2 mt-2">
                <form method="POST" id="shartnomaImzoForm" class="flex-fill">
                    @csrf
                    <input type="hidden" name="holat" value="imzolangan">
                    <button type="submit" class="btn btn-outline-success btn-sm w-100" onclick="return confirm('Shartnoma imzolangan deb belgilansinmi?')"><i class="bi bi-check2-circle me-1"></i>Imzolandi</button>
                </form>
                <form method="POST" id="shartnomaBekorForm" class="flex-fill">
                    @csrf
                    <input type="hidden" name="holat" value="bekor_qilingan">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Shartnoma bekor qilinsinmi?')"><i class="bi bi-x-circle me-1"></i>Bekor qilish</button>
                </form>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Bonus turlari boshqarish modali --}}
<div class="modal fade" id="bonusTurlariModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-gift me-1"></i>Bonus turlari</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-bordered mb-3">
            <thead><tr><th>Nomi</th><th>Turi</th><th>Standart qiymat</th><th>Holat</th><th></th></tr></thead>
            <tbody>
                @forelse($bonusTurlari as $bt)
                <tr>
                    <td>{{ $bt->nomi }}</td>
                    <td>{{ $bt->hisoblash_turi === 'foiz_okladdan' ? 'Okladdan %' : 'Belgilangan summa' }}</td>
                    <td>{{ $bt->hisoblash_turi === 'foiz_okladdan' ? $bt->standart_qiymat.'%' : number_format($bt->standart_qiymat, 0, '.', ' ') }}</td>
                    <td>{{ $bt->holat === 'faol' ? 'Faol' : 'Nofaol' }}</td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 bonus-turi-tahrirlash-btn"
                            data-id="{{ $bt->id }}" data-nomi="{{ $bt->nomi }}" data-tavsif="{{ $bt->tavsif }}"
                            data-hisoblash-turi="{{ $bt->hisoblash_turi }}" data-standart-qiymat="{{ $bt->standart_qiymat }}"
                            data-holat="{{ $bt->holat }}" data-sort-order="{{ $bt->sort_order }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Bonus turlari yo'q</td></tr>
                @endforelse
            </tbody>
        </table>

        <h6 class="fw-bold small" id="bonusTuriFormSarlavha">Yangi bonus turi</h6>
        <form method="POST" action="{{ route('ish_haqi.bonus_turi.saqla') }}" id="bonusTuriForm">
            @csrf
            <input type="hidden" name="id" id="bt-id">
            <div class="row g-2">
                <div class="col-4">
                    <label class="form-label small fw-medium">Nomi</label>
                    <input type="text" name="nomi" id="bt-nomi" class="form-control form-control-sm" required>
                </div>
                <div class="col-4">
                    <label class="form-label small fw-medium">Hisoblash turi</label>
                    <select name="hisoblash_turi" id="bt-hisoblash-turi" class="form-select form-select-sm">
                        <option value="summa">Belgilangan summa</option>
                        <option value="foiz_okladdan">Okladdan foiz</option>
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label small fw-medium">Standart qiymat</label>
                    <input type="number" step="0.01" min="0" name="standart_qiymat" id="bt-standart-qiymat" class="form-control form-control-sm">
                </div>
                <div class="col-8">
                    <label class="form-label small fw-medium">Tavsif</label>
                    <input type="text" name="tavsif" id="bt-tavsif" class="form-control form-control-sm">
                </div>
                <div class="col-2">
                    <label class="form-label small fw-medium">Holat</label>
                    <select name="holat" id="bt-holat" class="form-select form-select-sm">
                        <option value="faol">Faol</option>
                        <option value="nofaol">Nofaol</option>
                    </select>
                </div>
                <div class="col-2">
                    <label class="form-label small fw-medium">Tartib</label>
                    <input type="number" name="sort_order" id="bt-sort-order" class="form-control form-control-sm" value="0">
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="bt-yangi-btn">+ Yangi</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Shartnoma shablonlari boshqarish modali --}}
<div class="modal fade" id="shartnomaShablonlariModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-file-text me-1"></i>Mehnat shartnomasi shablonlari</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-bordered mb-3">
            <thead><tr><th>Nomi</th><th>Holat</th><th></th></tr></thead>
            <tbody>
                @forelse($shartnomaShablonlari as $sh)
                <tr>
                    <td>{{ $sh->nomi }}</td>
                    <td>{{ $sh->holat === 'faol' ? 'Faol' : 'Nofaol' }}</td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 shablon-tahrirlash-btn"
                            data-id="{{ $sh->id }}" data-nomi="{{ $sh->nomi }}" data-matn="{{ base64_encode($sh->matn) }}"
                            data-holat="{{ $sh->holat }}" data-sort-order="{{ $sh->sort_order }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted">Shablonlar yo'q</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="form-text mb-2">
            O'zgaruvchilar:
            @foreach(\App\Models\MehnatShartnomaShabloni::ozgaruvchilar() as $key => $tavsif)
            <code>{!! '{{'.$key.'}}' !!}</code>
            @endforeach
        </div>

        <h6 class="fw-bold small" id="shablonFormSarlavha">Yangi shablon</h6>
        <form method="POST" action="{{ route('ish_haqi.shartnoma_shabloni.saqla') }}" id="shablonForm">
            @csrf
            <input type="hidden" name="id" id="sh-id">
            <div class="row g-2">
                <div class="col-8">
                    <label class="form-label small fw-medium">Nomi</label>
                    <input type="text" name="nomi" id="sh-nomi" class="form-control form-control-sm" required>
                </div>
                <div class="col-2">
                    <label class="form-label small fw-medium">Holat</label>
                    <select name="holat" id="sh-holat" class="form-select form-select-sm">
                        <option value="faol">Faol</option>
                        <option value="nofaol">Nofaol</option>
                    </select>
                </div>
                <div class="col-2">
                    <label class="form-label small fw-medium">Tartib</label>
                    <input type="number" name="sort_order" id="sh-sort-order" class="form-control form-control-sm" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-medium">Matn</label>
                    <textarea name="matn" id="sh-matn" class="form-control form-control-sm" rows="14" style="font-family:monospace;font-size:.78rem" required></textarea>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="sh-yangi-btn">+ Yangi</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

{{-- ─── Tab 1: Davomat (tabel) ─────────────────────────────────── --}}
@if($tab === 'davomat')
<div class="oy-tab-strip">
    @foreach(['Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'] as $i => $qisqa)
    <a class="oy-tab {{ $oy === $i + 1 ? 'active' : '' }}"
       href="{{ route('ish_haqi.index', array_merge(request()->except(['oy','page']), ['tab' => 'davomat', 'oy' => $i + 1])) }}">{{ $qisqa }}</a>
    @endforeach
</div>
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('ish_haqi.index') }}" class="d-flex align-items-center flex-wrap gap-2">
        <input type="hidden" name="tab" value="davomat">
        <input type="hidden" name="oy" value="{{ $oy }}">
        <input type="number" name="yil" class="form-control" style="width:85px" value="{{ $yil }}" min="2020" max="2100" title="Yil">
        @if($filiallar->count())
        <select name="filial_id" class="form-select" style="width:150px">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" {{ (string) $filialId === (string) $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
            @endforeach
        </select>
        @endif
        <input type="search" name="qidiruv" class="form-control" style="width:160px" placeholder="Xodim ismi..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Ko'rish</button>

        <div class="filter-vr"></div>

        <div class="btn-group btn-group-sm" role="group">
            <a class="btn {{ !$manba ? 'btn-dark' : 'btn-outline-dark' }}"
               href="{{ route('ish_haqi.index', array_merge(request()->except(['manba','page']), ['tab' => 'davomat'])) }}">Barchasi</a>
            <a class="btn {{ $manba === 'tizim' ? 'btn-dark' : 'btn-outline-dark' }}"
               href="{{ route('ish_haqi.index', array_merge(request()->except(['manba','page']), ['tab' => 'davomat', 'manba' => 'tizim'])) }}">Tizim</a>
            <a class="btn {{ $manba === 'qolda' ? 'btn-dark' : 'btn-outline-dark' }}"
               href="{{ route('ish_haqi.index', array_merge(request()->except(['manba','page']), ['tab' => 'davomat', 'manba' => 'qolda'])) }}">Qo'lda</a>
        </div>

        @if(!$oyYopiqmi && $xodimlar->count())
        <div class="filter-vr"></div>

        <select id="avto-belgilash-holat" class="form-select form-select-sm" style="width:150px" title="Avto belgilash">
            @foreach(\App\Models\XodimDavomat::ICON_HOLATLARI as $key => $info)
            <option value="{{ $key }}">{{ $info['icon'] }} {{ $info['nomi'] }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-warning btn-sm fw-bold" id="avto-belgilash-btn" style="height:32px">
            <i class="bi bi-lightning-fill me-1"></i>Avto belgilash
        </button>
        @endif

        @if($xodimlar->count() && !$oyYopiqmi)
        <div class="filter-vr"></div>

        <button type="submit" form="davomat-form" class="btn btn-success btn-sm" style="height:32px">
            <i class="bi bi-save me-1"></i>Saqlash ({{ $oy }}/{{ $yil }})
        </button>
        <button type="submit" form="oy-yopish-form" class="btn btn-outline-danger btn-sm" style="height:32px">
            <i class="bi bi-lock me-1"></i>Oyni yopish
        </button>
        @endif

        @if($oyYopiqmi && $xodimlar->count())
        <div class="filter-vr"></div>

        <button type="submit" form="oy-ochish-form" class="btn btn-outline-secondary btn-sm" style="height:32px">
            <i class="bi bi-unlock me-1"></i>Qayta ochish
        </button>
        @endif

        <div class="ms-auto d-flex align-items-center gap-2">
            @if($oyYopiqmi)
            <span class="badge bg-secondary" style="font-size:.75rem"><i class="bi bi-lock-fill me-1"></i>Bu oy yopilgan</span>
            @else
            <span class="badge bg-success" style="font-size:.75rem"><i class="bi bi-unlock-fill me-1"></i>Ochiq</span>
            @endif
        </div>
    </form>
</div>

<form method="POST" action="{{ route('ish_haqi.davomat.saqla') }}" id="davomat-form">
@csrf
<input type="hidden" name="yil" value="{{ $yil }}">
<input type="hidden" name="oy" value="{{ $oy }}">
<div class="bank-wrap shadow-sm">
    <table class="bank-table" id="davomat-table">
        <thead>
            <tr>
                <th class="tl sticky-col" style="min-width:170px">
                    <input type="checkbox" id="xodim-hammasi-check" class="form-check-input me-1" style="vertical-align:middle">
                    Xodim
                </th>
                @for($kun = 1; $kun <= $kunlarSoni; $kun++)
                @php
                    $kunSana = $oyBoshi->copy()->day($kun);
                    $kalendarBelgi = $damOlishKalendar->get($kunSana->toDateString());
                @endphp
                <th style="{{ $kalendarBelgi ? ($kalendarBelgi->turi === 'bayram' ? 'background:linear-gradient(180deg,#f87171,#dc2626)' : 'background:linear-gradient(180deg,#94a3b8,#64748b)') : '' }};min-width:34px;padding:4px 2px" title="{{ $kunSana->format('d.m.Y (D)') }}{{ $kalendarBelgi ? ' — ' . ($kalendarBelgi->turi === 'bayram' ? "Bayram" : "Dam olish") : '' }}">{{ $kun }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @forelse($xodimlar as $x)
            <tr>
                <td class="tl sticky-col fw-semibold">
                    <input type="checkbox" class="form-check-input xodim-check me-1" data-xodim-id="{{ $x->id }}" style="vertical-align:middle">
                    {{ $x->ism_familiya }}
                </td>
                @for($kun = 1; $kun <= $kunlarSoni; $kun++)
                @php
                    $kunSana = $oyBoshi->copy()->day($kun);
                    // Belgilanmagan kun bo'sh qoladi (avtomatik "keldi" deb taxmin qilinmaydi) —
                    // faqat global kalendarda dam olish/bayram deb belgilangan kunlar oldindan belgilanadi
                    // (Global sozlamalar → dam olish kalendari, "Sozlamalar" tabida boshqariladi).
                    $globalDamOlishmi = $damOlishKalendar->has($kunSana->toDateString());
                    $mavjudHolat = $davomatlar[$x->id][$kun] ?? ($globalDamOlishmi ? 'dam_olish' : '');
                @endphp
                <td class="p-0 text-center">
                    <select name="holat[{{ $x->id }}][{{ $kun }}]" class="davomat-select" data-global-dam-olish="{{ $globalDamOlishmi ? '1' : '0' }}" {{ $oyYopiqmi ? 'disabled' : '' }}>
                        <option value="" {{ $mavjudHolat === '' ? 'selected' : '' }}>·</option>
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
<div class="mt-2 d-flex align-items-center gap-3 flex-wrap">
    <div class="d-flex align-items-center gap-3 flex-wrap small">
        <span class="d-inline-flex align-items-center gap-1">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;background:#94a3b8;color:#fff;font-weight:800;font-size:.7rem">·</span>
            Belgilanmagan
        </span>
        @foreach(\App\Models\XodimDavomat::ICON_HOLATLARI as $key => $info)
        <span class="d-inline-flex align-items-center gap-1">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;background:{{ $info['rang'] }};color:#fff;font-weight:800;font-size:.7rem">{{ $info['icon'] }}</span>
            {{ $info['nomi'] }}
        </span>
        @endforeach
    </div>
</div>
@endif
</form>

@if(!$oyYopiqmi && $xodimlar->count())
<form method="POST" action="{{ route('ish_haqi.davomat.oy_yopish') }}" id="oy-yopish-form"
      onsubmit="return confirm('{{ $oy }}/{{ $yil }} oyi YOPILSINMI? Yopilgandan keyin bu oy tabelini o\'zgartirib bo\'lmaydi, keyingi oy avtomatik ochiladi.')">
    @csrf
    <input type="hidden" name="yil" value="{{ $yil }}">
    <input type="hidden" name="oy" value="{{ $oy }}">
</form>
@endif

@if($oyYopiqmi && $xodimlar->count())
<form method="POST" action="{{ route('ish_haqi.davomat.oy_ochish') }}" id="oy-ochish-form"
      onsubmit="return confirm('{{ $oy }}/{{ $yil }} oyi qayta OCHILSINMI? Bu oyning tabelini yana tahrirlash mumkin bo\'ladi.')">
    @csrf
    <input type="hidden" name="yil" value="{{ $yil }}">
    <input type="hidden" name="oy" value="{{ $oy }}">
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
                <th colspan="6" class="grup-hisoblandi">HISOBLANDI</th>
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
                <th class="sub-hisoblandi">Muddatli qo'shimcha</th>
                <th class="sub-hisoblandi">Bonus (biriktirilgan)</th>
                <th class="sub-hisoblandi">Qo'shimcha (qo'lda)</th>
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
                <td class="num">{{ number_format($h->qoshimcha_ish_haqi_summa, 0, '.', ' ') }}</td>
                <td class="num">{{ number_format($h->biriktirilgan_bonus_summa, 0, '.', ' ') }}</td>
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
                <td class="num text-muted" colspan="12">— hali hisoblanmagan —</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 avans-btn"
                        data-xodim-id="{{ $x->id }}" data-xodim="{{ $x->ism_familiya }}" title="Avans berish">
                        <i class="bi bi-cash"></i>
                    </button>
                </td>
                @endif
            </tr>
            @empty
            <tr><td colspan="20" class="text-center text-muted py-5"><i class="bi bi-people fs-3 d-block mb-2"></i>Ish haqi sozlamasi bor xodim topilmadi (avval "Sozlamalar" tabida oklad belgilang)</td></tr>
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

{{-- Global sozlamalar — alohida modalda (soliq/ushlanma + dam olish kalendari) --}}
<div class="d-flex justify-content-end mb-2">
    <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#globalSozlamaModal">
        <i class="bi bi-globe me-1"></i>Global sozlamalar (soliq, ushlanma, dam olish kalendari)
    </button>
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

{{-- Global sozlamalar modali — soliq/ushlanma + yillik dam olish/bayram kalendari --}}
<div class="modal fade" id="globalSozlamaModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title"><i class="bi bi-globe me-1"></i>Global sozlamalar (jami xodimlar uchun standart)</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="{{ route('ish_haqi.sozlama.global_saqla') }}" class="row g-2 align-items-end mb-3">
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

        <hr>

        <h6 class="fw-bold mb-2"><i class="bi bi-calendar3 me-1"></i>Dam olish / bayram kunlari kalendari</h6>
        <div class="form-text mb-2">
            Davomat tabidagi kunlar endi avtomatik shanba/yakshanba deb taxmin qilinmaydi — shu yerda belgilangan
            kunlargina "dam olish kuni" sifatida oldindan belgilanadi (mijozlar orasida ish kunlari har xil bo'lishi
            mumkin — ba'zilarida shanba ham ish kuni, ba'zilarida juma dam olish kuni).
        </div>

        <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
            <label class="form-label small fw-medium mb-0">Yil:</label>
            <div class="btn-group btn-group-sm">
                @foreach(range(now()->year - 1, now()->year + 2) as $yy)
                <a class="btn {{ $kalendarYil === $yy ? 'btn-dark' : 'btn-outline-dark' }}"
                   href="{{ route('ish_haqi.index', ['tab' => 'sozlamalar', 'kalendar_yil' => $yy, 'ochiq_modal' => 'globalSozlamaModal']) }}">{{ $yy }}</a>
                @endforeach
            </div>

            <div class="btn-group btn-group-sm ms-2" role="group">
                <input type="radio" class="btn-check" name="belgilash_turi" id="turi-dam-olish" value="dam_olish" checked>
                <label class="btn btn-outline-secondary" for="turi-dam-olish">Dam olish kuni</label>
                <input type="radio" class="btn-check" name="belgilash_turi" id="turi-bayram" value="bayram">
                <label class="btn btn-outline-danger" for="turi-bayram">Bayram kuni</label>
            </div>

            <div class="ms-auto d-flex gap-1 flex-wrap">
                @foreach(['Dush','Sesh','Chor','Pay','Jum','Shan','Yak'] as $i => $haftaNomi)
                <button type="button" class="btn btn-outline-secondary btn-sm hafta-belgilash-btn" data-kun="{{ $i + 1 }}">{{ $haftaNomi }}</button>
                @endforeach
                <button type="button" class="btn btn-outline-danger btn-sm" id="kalendar-tozalash-btn"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>

        <form method="POST" action="{{ route('ish_haqi.dam_olish.saqla') }}" id="kalendarForm">
            @csrf
            <input type="hidden" name="yil" value="{{ $kalendarYil }}">
            <div class="bank-wrap shadow-sm" style="max-height:380px">
                <table class="bank-table" id="kalendar-table">
                    <thead>
                        <tr>
                            <th class="tl sticky-col">Oy</th>
                            @for($kun = 1; $kun <= 31; $kun++)
                            <th style="min-width:28px;padding:4px 1px">{{ $kun }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avgust','Sentabr','Oktabr','Noyabr','Dekabr'] as $oyIdx => $oyNomi)
                        @php
                            $oyRaqami = $oyIdx + 1;
                            $kunlarSoniOy = \Carbon\Carbon::create($kalendarYil, $oyRaqami, 1)->daysInMonth;
                        @endphp
                        <tr>
                            <td class="tl sticky-col fw-semibold">{{ $oyNomi }}</td>
                            @for($kun = 1; $kun <= 31; $kun++)
                            @if($kun > $kunlarSoniOy)
                            <td class="p-0" style="background:#f1f5f9"></td>
                            @else
                            @php
                                $sanaObj = \Carbon\Carbon::create($kalendarYil, $oyRaqami, $kun);
                                $sanaStr = $sanaObj->toDateString();
                                $mavjud = $damOlishKalendar->get($sanaStr);
                                $mavjudTuri = $mavjud->turi ?? '';
                                $haftaQisqa = ['', 'Du', 'Se', 'Ch', 'Pa', 'Ju', 'Sh', 'Ya'][$sanaObj->dayOfWeekIso];
                                $kunFoni = $mavjudTuri === 'bayram' ? 'background:#dc2626;color:#fff' : ($mavjudTuri === 'dam_olish' ? 'background:#94a3b8;color:#fff' : '');
                            @endphp
                            <td class="p-0 text-center kalendar-kun" data-sana="{{ $sanaStr }}" data-turi="{{ $mavjudTuri }}"
                                style="cursor:pointer;font-size:.62rem;font-weight:700;padding:3px 0;{{ $kunFoni }}"
                                title="{{ $sanaObj->format('d.m.Y') }}">
                                {{ $haftaQisqa }}
                                <input type="hidden" name="kunlar[{{ $sanaStr }}]" value="{{ $mavjudTuri }}">
                            </td>
                            @endif
                            @endfor
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-success btn-sm mt-2"><i class="bi bi-save me-1"></i>{{ $kalendarYil }}-yil kalendarini saqlash</button>
        </form>
      </div>
    </div>
  </div>
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
@if($tab === 'xodimlar')
<script>
function b64DecodeUnicode(str) {
    if (!str) return '';
    const binary = atob(str);
    const bytes = Uint8Array.from(binary, function (c) { return c.charCodeAt(0); });
    return new TextDecoder('utf-8').decode(bytes);
}

(function () {
    const manbaRadios = document.querySelectorAll('input[name="manba"]');
    const tizimBlok = document.getElementById('manba-tizim-blok');
    const qoldaBlok = document.getElementById('manba-qolda-blok');
    function manbaToggle() {
        const checked = document.querySelector('input[name="manba"]:checked');
        const val = checked ? checked.value : 'tizim';
        tizimBlok.style.display = val === 'tizim' ? '' : 'none';
        qoldaBlok.style.display = val === 'qolda' ? '' : 'none';
    }
    manbaRadios.forEach(function (r) { r.addEventListener('change', manbaToggle); });
    manbaToggle();
})();

(function () {
    const modalEl = document.getElementById('xodimTahrirlashModal');
    const modal = new bootstrap.Modal(modalEl);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.xodim-tahrirlash-btn');
        if (!btn) return;

        document.getElementById('xodimTahrirlashNomi').textContent = "Tahrirlash — " + btn.dataset.xodimNomi;
        document.getElementById('tahrirlash-ism-blok').style.display = btn.dataset.tizim === '0' ? '' : 'none';
        document.getElementById('tahrirlash-ism').value = btn.dataset.xodimNomi || '';
        document.getElementById('tahrirlash-lavozim').value = btn.dataset.lavozim || '';
        document.getElementById('tahrirlash-telefon').value = btn.dataset.telefon || '';
        document.getElementById('tahrirlash-manzil').value = btn.dataset.manzil || '';
        document.getElementById('tahrirlash-passport').value = btn.dataset.passport || '';
        document.getElementById('tahrirlash-ishga-kirgan').value = btn.dataset.ishgaKirgan || '';
        document.getElementById('tahrirlash-ishdan-boshagan').value = btn.dataset.ishdanBoshagan || '';
        document.getElementById('tahrirlash-qoshimcha').value = btn.dataset.qoshimcha || 0;
        document.getElementById('tahrirlash-qoshimcha-boshlanish').value = btn.dataset.qoshimchaBoshlanish || '';
        document.getElementById('tahrirlash-qoshimcha-tugash').value = btn.dataset.qoshimchaTugash || '';
        document.getElementById('xodimTahrirlashForm').action = `{{ url('ish-haqi/xodim') }}/${btn.dataset.xodimId}`;

        modal.show();
    });
})();

(function () {
    const modalEl = document.getElementById('tatilBerModal');
    const modal = new bootstrap.Modal(modalEl);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.tatil-ber-btn');
        if (!btn) return;

        document.getElementById('tatilXodimNomi').textContent = btn.dataset.xodim;
        document.getElementById('tatilBerForm').action = `{{ url('ish-haqi/xodim') }}/${btn.dataset.xodimId}/tatil`;

        modal.show();
    });
})();

(function () {
    const modalEl = document.getElementById('bonusBerModal');
    const modal = new bootstrap.Modal(modalEl);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.bonus-ber-btn');
        if (!btn) return;

        document.getElementById('bonusXodimNomi').textContent = btn.dataset.xodim;
        document.getElementById('bonusBerForm').action = `{{ url('ish-haqi/xodim') }}/${btn.dataset.xodimId}/bonus`;

        modal.show();
    });
})();

(function () {
    const modalEl = document.getElementById('shartnomaModal');
    const modal = new bootstrap.Modal(modalEl);
    const select = document.getElementById('shartnoma-tanlov');

    function toggleBlok() {
        const yangiBlok = document.getElementById('shartnoma-yangi-blok');
        const tahrirlashBlok = document.getElementById('shartnoma-tahrirlash-blok');

        if (select.value === 'yangi') {
            yangiBlok.style.display = '';
            tahrirlashBlok.style.display = 'none';
            return;
        }

        yangiBlok.style.display = 'none';
        tahrirlashBlok.style.display = '';

        const info = JSON.parse(select.options[select.selectedIndex].dataset.info);
        document.getElementById('st-raqami').value = info.raqami || '';
        document.getElementById('st-sana').value = info.sana_iso || '';
        document.getElementById('st-amal-boshlanish').value = info.amal_boshlanish || '';
        document.getElementById('st-amal-tugash').value = info.amal_tugash || '';
        document.getElementById('st-matn').value = info.matn;
        document.getElementById('shartnoma-holat-badge').innerHTML = info.holat_badge;
        document.getElementById('shartnomaSaqlaForm').action = `{{ url('ish-haqi/shartnoma') }}/${info.id}`;
        document.getElementById('shartnomaImzoForm').action = `{{ url('ish-haqi/shartnoma') }}/${info.id}/holat`;
        document.getElementById('shartnomaBekorForm').action = `{{ url('ish-haqi/shartnoma') }}/${info.id}/holat`;
        document.getElementById('shartnoma-pdf-link').href = `{{ url('ish-haqi/shartnoma') }}/${info.id}/pdf`;
    }
    select.addEventListener('change', toggleBlok);

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.shartnoma-btn');
        if (!btn) return;

        document.getElementById('shartnomaXodimNomi').textContent = btn.dataset.xodim;
        document.getElementById('shartnomaYaratForm').action = `{{ url('ish-haqi/xodim') }}/${btn.dataset.xodimId}/shartnoma`;

        let shartnomalar = [];
        try { shartnomalar = JSON.parse(b64DecodeUnicode(btn.dataset.shartnomalar)); } catch (err) { shartnomalar = []; }

        select.innerHTML = '<option value="yangi">+ Yangi shartnoma yaratish</option>';
        shartnomalar.forEach(function (s) {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = (s.raqami || '(raqamsiz)') + ' — ' + s.sana_label + ' — ' + s.holat;
            opt.dataset.info = JSON.stringify(s);
            select.appendChild(opt);
        });
        select.value = 'yangi';
        toggleBlok();

        modal.show();
    });
})();

(function () {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.bonus-turi-tahrirlash-btn');
        if (!btn) return;

        document.getElementById('bonusTuriFormSarlavha').textContent = "Tahrirlash — " + btn.dataset.nomi;
        document.getElementById('bt-id').value = btn.dataset.id;
        document.getElementById('bt-nomi').value = btn.dataset.nomi;
        document.getElementById('bt-tavsif').value = btn.dataset.tavsif || '';
        document.getElementById('bt-hisoblash-turi').value = btn.dataset.hisoblashTuri;
        document.getElementById('bt-standart-qiymat').value = btn.dataset.standartQiymat;
        document.getElementById('bt-holat').value = btn.dataset.holat;
        document.getElementById('bt-sort-order').value = btn.dataset.sortOrder;
    });

    const yangiBtn = document.getElementById('bt-yangi-btn');
    if (yangiBtn) {
        yangiBtn.addEventListener('click', function () {
            document.getElementById('bonusTuriFormSarlavha').textContent = 'Yangi bonus turi';
            document.getElementById('bonusTuriForm').reset();
            document.getElementById('bt-id').value = '';
        });
    }
})();

(function () {
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.shablon-tahrirlash-btn');
        if (!btn) return;

        document.getElementById('shablonFormSarlavha').textContent = "Tahrirlash — " + btn.dataset.nomi;
        document.getElementById('sh-id').value = btn.dataset.id;
        document.getElementById('sh-nomi').value = btn.dataset.nomi;
        document.getElementById('sh-matn').value = b64DecodeUnicode(btn.dataset.matn);
        document.getElementById('sh-holat').value = btn.dataset.holat;
        document.getElementById('sh-sort-order').value = btn.dataset.sortOrder;
    });

    const yangiBtn = document.getElementById('sh-yangi-btn');
    if (yangiBtn) {
        yangiBtn.addEventListener('click', function () {
            document.getElementById('shablonFormSarlavha').textContent = 'Yangi shablon';
            document.getElementById('shablonForm').reset();
            document.getElementById('sh-id').value = '';
        });
    }
})();
</script>
@endif

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

    const hammasiCheck = document.getElementById('xodim-hammasi-check');
    if (hammasiCheck) {
        hammasiCheck.addEventListener('change', function () {
            document.querySelectorAll('.xodim-check').forEach(function (c) { c.checked = hammasiCheck.checked; });
        });
    }

    const avtoBtn = document.getElementById('avto-belgilash-btn');
    if (avtoBtn) {
        avtoBtn.addEventListener('click', function () {
            const tanlangan = Array.from(document.querySelectorAll('.xodim-check:checked')).map(function (c) { return c.dataset.xodimId; });
            if (!tanlangan.length) {
                alert("Avval kamida bitta xodimni belgilang.");
                return;
            }
            const holat = document.getElementById('avto-belgilash-holat').value;
            if (!confirm(`Tanlangan ${tanlangan.length} ta xodim uchun oy oxirigacha "${holat}" belgilansinmi? (Global dam olish/bayram kunlari o'zgarmaydi)`)) return;

            tanlangan.forEach(function (xodimId) {
                document.querySelectorAll(`select[name^="holat[${xodimId}]"]`).forEach(function (sel) {
                    if (sel.disabled || sel.dataset.globalDamOlish === '1') return;
                    sel.value = holat;
                    rangla(sel);
                });
            });
        });
    }
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

(function () {
    function joriyTuri() {
        const checked = document.querySelector('input[name="belgilash_turi"]:checked');
        return checked ? checked.value : 'dam_olish';
    }

    function kunRangla(td) {
        const turi = td.dataset.turi;
        if (turi === 'dam_olish') { td.style.background = '#94a3b8'; td.style.color = '#fff'; }
        else if (turi === 'bayram') { td.style.background = '#dc2626'; td.style.color = '#fff'; }
        else { td.style.background = ''; td.style.color = ''; }
    }

    document.querySelectorAll('.kalendar-kun').forEach(function (td) {
        td.addEventListener('click', function () {
            const hidden = td.querySelector('input[type=hidden]');
            td.dataset.turi = (td.dataset.turi === joriyTuri()) ? '' : joriyTuri();
            hidden.value = td.dataset.turi;
            kunRangla(td);
        });
    });

    document.querySelectorAll('.hafta-belgilash-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const kun = parseInt(btn.dataset.kun, 10);
            document.querySelectorAll('.kalendar-kun').forEach(function (td) {
                if (!td.dataset.sana) return;
                const d = new Date(td.dataset.sana + 'T00:00:00');
                const iso = d.getDay() === 0 ? 7 : d.getDay();
                if (iso === kun) {
                    td.dataset.turi = joriyTuri();
                    td.querySelector('input[type=hidden]').value = td.dataset.turi;
                    kunRangla(td);
                }
            });
        });
    });

    const tozalashBtn = document.getElementById('kalendar-tozalash-btn');
    if (tozalashBtn) {
        tozalashBtn.addEventListener('click', function () {
            if (!confirm("Ushbu yil uchun barcha belgilar tozalansinmi?")) return;
            document.querySelectorAll('.kalendar-kun').forEach(function (td) {
                td.dataset.turi = '';
                td.querySelector('input[type=hidden]').value = '';
                kunRangla(td);
            });
        });
    }

    @if(request()->get('ochiq_modal') === 'globalSozlamaModal')
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('globalSozlamaModal')).show();
    });
    @endif
})();
</script>
@endif
@endpush
