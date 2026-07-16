@extends('layouts.app')
@section('title', 'HibritPochta')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Kreditlar</a></li>
<li class="breadcrumb-item active">HibritPochta</li>
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
.jami-row th {
    background:linear-gradient(180deg,#fef9c3,#fde68a) !important; color:#7c2d12 !important;
    font-weight:800; font-size:.78rem; text-transform:none; letter-spacing:0; padding:6px 8px;
}
.jami-row th.sticky-col { background:linear-gradient(180deg,#fde68a,#fbbf24) !important; z-index:8; }
.jami-row th.num { font-family:'Roboto Mono','Courier New',monospace; }

.bank-wrap { overflow:auto; height:calc(100vh - 260px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 220px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 12px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 pb-3" style="margin-top:-40px">
{{-- Muvaffaqiyat/xato xabarlari layouts/app.blade.php da global ko'rsatiladi — bu yerda takrorlanmaydi --}}

<div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>HibritPochta</h5>
    <div class="d-flex align-items-center gap-2">
        <span class="badge {{ $yoqilgan ? 'bg-success' : 'bg-secondary' }}">{{ $yoqilgan ? 'Yoqilgan' : "O'chirilgan" }}</span>
        <a href="{{ route('admin.sozlamalar') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear me-1"></i>Sozlamalar
        </a>
    </div>
</div>

@php $tabParams = ['filial_id' => $filialId, 'qidiruv' => $qidiruv]; @endphp

<ul class="nav nav-tabs mb-0">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'kutilayotgan' ? 'active fw-semibold' : '' }}"
           href="{{ route('hibrit_pochta.index', ['tab' => 'kutilayotgan']) }}">
            <i class="bi bi-hourglass-split me-1"></i>Kutilayotgan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'loglar' ? 'active fw-semibold' : '' }}"
           href="{{ route('hibrit_pochta.index', ['tab' => 'loglar']) }}">
            <i class="bi bi-journal-text me-1"></i>Loglar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'shablonlar' ? 'active fw-semibold' : '' }}"
           href="{{ route('hibrit_pochta.index', ['tab' => 'shablonlar']) }}">
            <i class="bi bi-braces me-1"></i>Shablonlar
        </a>
    </li>
</ul>

{{-- ─── Tab 1: Kutilayotgan ──────────────────────────────────── --}}
@if($tab === 'kutilayotgan')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('hibrit_pochta.index') }}" class="d-flex align-items-end flex-wrap gap-2">
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
        <a href="{{ route('hibrit_pochta.index', ['tab' => 'kutilayotgan', 'filial_id' => $filialId]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary btn-sm px-2" style="height:32px" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Ustunlarni ko'rsatish/yashirish">
                <i class="bi bi-layout-three-columns"></i>
            </button>
            <ul class="dropdown-menu p-2" style="font-size:.8rem;min-width:190px;max-height:340px;overflow:auto">
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="xodim" data-default="0"> Xodim</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="boshlanish" data-default="0"> Boshlanish</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="tugash" data-default="0"> Tugash</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="muddat" data-default="0"> Muddat</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="jami" data-default="0"> Tovar summasi</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="oldindan" data-default="0"> Oldindan</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="kredit" data-default="0"> Kredit summa</label></li>
                <li><label class="dropdown-item form-check py-1"><input type="checkbox" class="form-check-input me-1 ustun-toggle-hp" data-col="tolangan" data-default="0"> Jami to'langan</label></li>
            </ul>
        </div>
        @if(!$yoqilgan)
        <span class="badge bg-warning text-dark ms-auto">Hibrit Pochta o'chirilgan — xat yubora olmaysiz</span>
        @endif
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Shartnoma</th>
                <th class="tl">Eski raqam</th>
                <th class="tl col-xodim d-none">Xodim</th>
                <th class="tl">Mijoz</th>
                <th class="tl">Filial</th>
                <th class="tl">Telefon</th>
                <th class="tl">Manzil</th>
                <th class="tl col-boshlanish d-none">Boshlanish</th>
                <th class="tl col-tugash d-none">Tugash</th>
                <th class="col-muddat d-none">Muddat</th>
                <th class="col-jami d-none">Tovar summasi</th>
                <th class="col-oldindan d-none">Oldindan</th>
                <th class="col-kredit d-none">Kredit summa</th>
                <th class="col-tolangan d-none">Jami to'langan</th>
                <th>Qoldiq qarz</th>
                <th>Kechikkan summa</th>
                <th>Kechikish</th>
                <th class="tl">Xat holati</th>
                <th class="tl">Oxirgi yuborilgan</th>
                <th style="width:130px"></th>
            </tr>
            <tr class="jami-row">
                <th class="tl sticky-col" colspan="2">JAMI ({{ $kreditlar->total() }} ta)</th>
                <th class="col-xodim d-none"></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="col-boshlanish d-none"></th>
                <th class="col-tugash d-none"></th>
                <th class="col-muddat d-none"></th>
                <th class="num col-jami d-none">{{ number_format($jamiSummalar->jami_summa ?? 0, 0, '.', ' ') }}</th>
                <th class="num col-oldindan d-none">{{ number_format($jamiSummalar->boshlangich_tolov ?? 0, 0, '.', ' ') }}</th>
                <th class="num col-kredit d-none">{{ number_format($jamiSummalar->kredit_summa ?? 0, 0, '.', ' ') }}</th>
                <th class="num col-tolangan d-none">{{ number_format($jamiSummalar->jami_tolangan ?? 0, 0, '.', ' ') }}</th>
                <th class="num">{{ number_format($qoldiqJami ?? 0, 0, '.', ' ') }}</th>
                <th class="num">{{ number_format($kechikkanJami ?? 0, 0, '.', ' ') }}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($kreditlar as $kredit)
            @php $oxirgiXat = $kredit->oxirgiYuborilganPochta; @endphp
            <tr>
                <td class="tl sticky-col"><a href="{{ route('kreditlar.show', $kredit) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $kredit->shartnoma_raqam }}</a></td>
                <td class="tl">
                    @if($kredit->eskiRaqamKorinishi())
                    <span class="badge-modern" style="background:#7c3aed;color:#fff" title="Eski (qog'ozdagi) shartnoma raqami">{{ $kredit->eskiRaqamKorinishi() }}</span>
                    @else
                    <span class="text-muted small">—</span>
                    @endif
                </td>
                <td class="tl text-muted col-xodim d-none">{{ $kredit->xodim?->ism_familiya ?? '—' }}</td>
                <td class="tl">{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</td>
                <td class="tl text-muted">{{ $kredit->filial?->nomi }}</td>
                <td class="tl text-muted">{{ $kredit->mijoz?->telefon }}</td>
                <td class="tl text-muted text-truncate" style="max-width:220px" title="{{ $kredit->mijoz?->manzil }}">{{ $kredit->mijoz?->manzil ?: '—' }}</td>
                <td class="tl text-muted col-boshlanish d-none">{{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="tl text-muted col-tugash d-none">{{ $kredit->tugash_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-center col-muddat d-none"><span class="badge-modern" style="background:#e0e7ff;color:#3730a3">{{ $kredit->muddati_oy }} oy</span></td>
                <td class="num col-jami d-none">{{ number_format($kredit->jami_summa, 0, '.', ' ') }}</td>
                <td class="num col-oldindan d-none">{{ number_format($kredit->boshlangich_tolov, 0, '.', ' ') }}</td>
                <td class="num col-kredit d-none">{{ number_format($kredit->kredit_summa, 0, '.', ' ') }}</td>
                <td class="num col-tolangan d-none">{{ number_format($kredit->boshlangich_tolov + $kredit->tolov_qilingan, 0, '.', ' ') }}</td>
                <td class="num" style="color:#dc2626">{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</td>
                <td class="num" style="color:#dc2626">{{ number_format($kredit->kechikkan_summa ?? 0, 0, '.', ' ') }}</td>
                <td class="text-center"><span class="badge-modern" style="background:#fee2e2;color:#991b1b">{{ $kredit->tugash_sana ? (int) abs(now()->diffInDays($kredit->tugash_sana, true)) : 0 }} kun</span></td>
                <td class="tl">
                    @if($oxirgiXat)
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Yuborilgan</span>
                    @else
                    <span class="badge-modern" style="background:#94a3b8;color:#fff">Yuborilmagan</span>
                    @endif
                </td>
                <td class="tl text-muted">{{ $oxirgiXat?->yuborildi_vaqt?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-center">
                    @if(auth()->user()->ruxsat('hibrit_pochta', 'qoshish'))
                    <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2 xat-yuborish-btn" data-kredit-id="{{ $kredit->id }}" title="Pochta xat yuborish" {{ $yoqilgan ? '' : 'disabled' }}>
                        <i class="bi bi-envelope-paper me-1"></i>Xat
                    </button>
                    @else
                    <span class="text-muted small">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="20" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Muddati o'tgan shartnomalar yo'q</td></tr>
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

{{-- ═══ Hybrid Pochta — Xat Yuborish Modali (barcha qatorlar uchun umumiy) ═══ --}}
<div class="modal fade" id="pochtaXatModal" tabindex="-1" aria-labelledby="pochtaXatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header py-2" style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff">
        <h6 class="modal-title mb-0" id="pochtaXatModalLabel">
          <i class="bi bi-envelope-paper me-2"></i>Pochta Xat Yuborish
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">

        <div id="hp-loading" class="text-center py-5 text-muted small">
          <div class="spinner-border spinner-border-sm me-2"></div>Ma'lumotlar yuklanmoqda...
        </div>

        {{-- ─── QADAM 1: Ma'lumotlar ─────────────────────────────────── --}}
        <div id="hp-step1" class="d-none">
          <div class="p-3 border-bottom bg-light-subtle">
            <div class="d-flex align-items-center gap-2">
              <span class="badge rounded-pill bg-primary">1</span>
              <strong class="small">Xat ma'lumotlari</strong>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-secondary">2</span>
              <span class="small text-muted">Yaratildi (kabinetda tasdiqlanadi)</span>
            </div>
          </div>

          <div class="p-3">
            <div class="mb-3">
              <label class="form-label small fw-medium mb-1">Xat shabloni <span class="text-danger">*</span></label>
              <select id="hp-shablon" class="form-select form-select-sm">
                <option value="">— Shablon tanlang —</option>
              </select>
            </div>

            <div id="hp-limit-info" class="alert alert-warning py-2 small d-none"></div>

            <div class="mb-3">
              <label class="form-label small fw-medium mb-1">Qabul qiluvchi FIO <span class="text-danger">*</span></label>
              <input type="text" id="hp-receiver" class="form-control form-control-sm">
            </div>

            <div class="mb-3">
              <label class="form-label small fw-medium mb-1">
                To'liq pochta manzili <span class="text-danger">*</span>
                <span class="text-muted fw-normal">(ko'cha, uy raqami, shahar/tuman)</span>
              </label>
              <textarea id="hp-address" class="form-control form-control-sm" rows="2"></textarea>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-medium mb-1">Viloyat <span class="text-danger">*</span></label>
                <select id="hp-region" class="form-select form-select-sm">
                  <option value="">— Yuklanmoqda... —</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-medium mb-1">Tuman/Shahar <span class="text-danger">*</span></label>
                <select id="hp-area" class="form-select form-select-sm" disabled>
                  <option value="">— Avval viloyat tanlang —</option>
                </select>
              </div>
            </div>

            <div id="hp-preview-box" class="d-none">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="form-label small fw-medium mb-0">Xat matni ko'rinishi:</label>
                <a id="hp-preview-link" href="#" target="_blank" class="btn btn-xs btn-outline-secondary">
                  <i class="bi bi-file-pdf me-1"></i>PDF ko'rish
                </a>
              </div>
              <div id="hp-preview-text"
                class="border rounded p-2 small bg-light" style="white-space:pre-wrap;max-height:150px;overflow:auto;font-size:11px"></div>
            </div>
          </div>

          <div class="modal-footer py-2 bg-light-subtle border-top">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Bekor</button>
            <button id="hp-step1-next" type="button" class="btn btn-sm btn-primary" disabled>
              Yuborish <i class="bi bi-arrow-right ms-1"></i>
            </button>
          </div>
        </div>

        {{-- ─── QADAM 2: E-IMZO imzolash ───────────────────────────── --}}
        <div id="hp-step2" class="d-none">
          <div class="p-3 border-bottom bg-light-subtle">
            <div class="d-flex align-items-center gap-2">
              <span class="badge rounded-pill bg-success">1</span>
              <span class="small text-muted">Ma'lumotlar</span>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-primary">2</span>
              <strong class="small">E-IMZO imzo</strong>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-secondary">3</span>
              <span class="small text-muted">Natija</span>
            </div>
          </div>

          <div class="p-3">
            <div id="hp-eimzo-loading" class="text-center py-3 text-muted small">
              <div class="spinner-border spinner-border-sm me-2" role="status"></div>
              Xat yaratilmoqda va E-IMZO sertifikatlari yuklanmoqda...
            </div>

            <div id="hp-eimzo-error" class="alert alert-warning small d-none">
              <strong><i class="bi bi-exclamation-triangle me-1"></i>E-IMZO topilmadi yoki brauzer bloklamoqda.</strong><br>
              1. E-IMZO dasturi kompyuteringizda ishga tushirilganini tekshiring.<br>
              2. Bu sahifa HTTPS orqali ochilgani uchun brauzeringiz avval
                 <a href="https://127.0.0.1:64443" target="_blank">https://127.0.0.1:64443</a> manziliga
                 bir marta o'zingiz kirib, xavfsizlik sertifikati ogohlantirishini
                 (<em>"Дополнительно" → "Перейти"</em>) qabul qilishingiz kerak — aks holda brauzer
                 E-IMZO bilan ulanishni "xavfsiz emas" deb bloklaydi.<br>
              3. Shundan keyin shu oynani yopib, qaytadan "Xat" tugmasini bosing.<br>
              <a href="https://e-imzo.uz" target="_blank">e-imzo.uz</a> dan E-IMZO o'rnatish mumkin (agar hali o'rnatilmagan bo'lsa).
            </div>

            <div id="hp-eimzo-keys-box" class="d-none">
              <div class="alert alert-info small py-2 mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Sertifikat tanlang va "Imzolash va Yuborish" tugmasini bosing.
              </div>
              <label class="form-label small fw-medium mb-1">E-IMZO Sertifikat:</label>
              <select id="hp-eimzo-key" class="form-select form-select-sm mb-2">
                <option value="">— Sertifikat tanlang —</option>
              </select>
              <div id="hp-cert-info" class="text-muted small mt-1"></div>
            </div>
          </div>

          <div class="modal-footer py-2 bg-light-subtle border-top">
            <button id="hp-step2-back" type="button" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Orqaga
            </button>
            <button id="hp-sign-btn" type="button" class="btn btn-sm btn-primary d-none">
              <i class="bi bi-pen me-1"></i>Imzolash va Yuborish
            </button>
          </div>
        </div>

        {{-- ─── QADAM 3: Natija ──────────────────────────────────────── --}}
        <div id="hp-step3" class="d-none">
          <div class="p-4 text-center">
            <div id="hp-result-ok" class="d-none">
              <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
              <h5 class="mt-3 text-success">Xat HibritPochta tizimida yaratildi!</h5>
              <p class="text-muted small" id="hp-result-msg"></p>
              <p class="small mb-2">Yuborish uchun operator <strong>hybrid.pochta.uz</strong> kabinetiga kirib, E-IMZO bilan tasdiqlashi kerak.</p>
              <a href="https://hybrid.pochta.uz" target="_blank" class="btn btn-sm btn-success mt-1">
                <i class="bi bi-box-arrow-up-right me-1"></i>Kabinetga o'tish
              </a>
            </div>
            <div id="hp-result-err" class="d-none">
              <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem"></i>
              <h5 class="mt-3 text-danger">Xatolik yuz berdi</h5>
              <p class="text-muted small" id="hp-result-err-msg"></p>
            </div>
          </div>
          <div class="modal-footer py-2 bg-light-subtle border-top">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"
              onclick="window.location.reload()">Yopish</button>
          </div>
        </div>

      </div>{{-- /modal-body --}}
    </div>
  </div>
</div>
@endif

{{-- ─── Tab 2: Loglar ─────────────────────────────────────────── --}}
@if($tab === 'loglar')
<div class="filter-bar mb-0">
    <form method="GET" action="{{ route('hibrit_pochta.index') }}" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="loglar">
        <div class="d-flex align-items-center gap-2 me-2">
            <span class="badge bg-secondary">{{ $loglar->total() }}</span>
        </div>
        <select name="holat" class="form-select" style="width:160px" onchange="this.form.submit()">
            <option value="">Barcha holatlar</option>
            <option value="yuborildi"  {{ $holat === 'yuborildi'  ? 'selected' : '' }}>Yuborildi</option>
            <option value="yaratildi"  {{ $holat === 'yaratildi'  ? 'selected' : '' }}>Yaratildi</option>
            <option value="kutilmoqda" {{ $holat === 'kutilmoqda' ? 'selected' : '' }}>Kutilmoqda</option>
            <option value="xato"       {{ $holat === 'xato'       ? 'selected' : '' }}>Xato</option>
            <option value="ochirilgan" {{ $holat === 'ochirilgan' ? 'selected' : '' }}>Kabinetda o'chirilgan</option>
        </select>
        <input type="search" name="qidiruv" class="form-control" style="width:220px" placeholder="Mijoz yoki shartnoma..." value="{{ $qidiruv }}">
        <button class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
        @if($qidiruv || $holat)
        <a href="{{ route('hibrit_pochta.index', ['tab' => 'loglar']) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        @endif
        <div class="ms-auto d-flex align-items-center gap-3 small">
            <span>Jami: <strong>{{ $statistika['jami'] }}</strong></span>
            <span class="text-success">Yuborildi: <strong>{{ $statistika['yuborildi'] }}</strong></span>
            <span class="text-danger">Xato: <strong>{{ $statistika['xato'] }}</strong></span>
            <span class="text-info">Bugun: <strong>{{ $statistika['bugun'] }}</strong></span>
        </div>
        <button type="button" id="hp-holat-sinxron-btn" class="btn btn-outline-success btn-sm">
            <i class="bi bi-arrow-repeat me-1"></i>Holatlarni sinxronlash
        </button>
        <button type="button" id="hp-loglar-tozala-btn" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash me-1"></i>Xato va o'chirilganlarni tozalash
        </button>
    </form>
</div>

<script>
document.getElementById('hp-holat-sinxron-btn')?.addEventListener('click', async function() {
    const btn = this;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Tekshirilmoqda...';

    try {
        const resp = await fetch('{{ route("hibrit_pochta.holat_sinxron") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
        const data = await resp.json();

        if (resp.ok && data.ok) {
            alert(`Tekshirildi: ${data.tekshirildi} ta\nYangi yuborilgan: ${data.yuborildi} ta\nKabinetda o'chirilgan: ${data.ochirilgan} ta` + (data.xatolar ? `\nAPI xatosi: ${data.xatolar} ta` : ''));
            window.location.reload();
        } else {
            alert('Xato: ' + (data.xato || "Noma'lum xato"));
            btn.disabled = false;
            btn.innerHTML = original;
        }
    } catch (e) {
        alert('Tarmoq xatosi: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = original;
    }
});

document.getElementById('hp-loglar-tozala-btn')?.addEventListener('click', async function() {
    if (!confirm("\"Xato\" va \"Kabinetda o'chirilgan\" holatidagi BARCHA log yozuvlari butunlay o'chirilsinmi?\n\n\"Yuborildi\" holatidagilarga tegilmaydi.")) return;

    const btn = this;
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Tozalanmoqda...';

    try {
        const resp = await fetch('{{ route("hibrit_pochta.loglar_tozala") }}', {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
        const data = await resp.json();

        if (resp.ok && data.ok) {
            alert(`${data.ochirildi} ta log yozuvi o'chirildi.`);
            window.location.reload();
        } else {
            alert('Xato: ' + (data.xato || "Noma'lum xato"));
            btn.disabled = false;
            btn.innerHTML = original;
        }
    } catch (e) {
        alert('Tarmoq xatosi: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = original;
    }
});
</script>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Sana</th>
                <th class="tl">Shartnoma</th>
                <th class="tl">Mijoz</th>
                <th class="tl">Shablon</th>
                <th class="tl">Manzil</th>
                <th class="tl">Holat</th>
                <th style="width:120px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($loglar as $log)
            <tr>
                <td class="tl text-muted sticky-col">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                <td class="tl">
                    @if($log->kredit)
                    <a href="{{ route('kreditlar.show', $log->reg_kredit_id) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $log->kredit->shartnoma_raqam ?? 'K-'.$log->reg_kredit_id }}</a>
                    @else
                    <span class="text-muted">{{ $log->reg_kredit_id }}</span>
                    @endif
                </td>
                <td class="tl">{{ $log->receiver }}</td>
                <td class="tl text-muted">{{ $log->shablon?->nomi ?? '—' }}</td>
                <td class="tl text-muted text-truncate" style="max-width:220px" title="{{ $log->address }}">{{ $log->address }}</td>
                <td class="tl">{!! $log->holatBadge() !!}</td>
                <td class="text-center">
                    @if($log->shablon_id)
                    <a href="{{ route('kreditlar.pochta.preview', ['kredit' => $log->reg_kredit_id, 'shablon_id' => $log->shablon_id]) }}" target="_blank" class="btn btn-outline-primary btn-sm py-0 px-1" title="Xatni ko'rish"><i class="bi bi-eye"></i></a>
                    @endif
                    @if($log->holat === 'yuborildi' && $log->api_letter_id)
                    <a href="{{ route('admin.gibrid-pochta.kvitansiya', $log) }}" class="btn btn-outline-success btn-sm py-0 px-1" title="Kvitansiya PDF"><i class="bi bi-file-pdf"></i></a>
                    @endif
                    @if($log->xato_xabar)
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" data-bs-toggle="tooltip" title="{{ $log->xato_xabar }}"><i class="bi bi-info-circle"></i></button>
                    @endif
                    @if(in_array($log->holat, ['ochirilgan', 'xato', 'kutilmoqda']))
                    <button class="btn btn-outline-danger btn-sm py-0 px-1 log-ochirish-btn" data-log-id="{{ $log->id }}" title="Ro'yxatdan o'chirish"><i class="bi bi-trash"></i></button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-search fs-3 d-block mb-2"></i>Log yozuvlari topilmadi</td></tr>
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

{{-- ─── Tab 3: Shablonlar ─────────────────────────────────────── --}}
@if($tab === 'shablonlar')
<div class="filter-bar mb-0 d-flex align-items-center justify-content-between">
    <span class="small text-muted">Shablonlarda ishlatsa bo'ladigan o'zgaruvchilar quyida ko'rsatilgan.</span>
    @if(auth()->user()->isAdmin())
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addShablonModal">
        <i class="bi bi-plus-lg me-1"></i>Yangi shablon
    </button>
    @endif
</div>
<div class="alert alert-light border py-2 small mb-0" style="border-radius:0">
    <strong><i class="bi bi-braces me-1"></i>O'zgaruvchilar:</strong>
    @foreach($ozgaruvchilar as $kalit => $tavsif)
    <code class="me-2">&#123;&#123;{{ $kalit }}&#125;&#125;</code> — {{ $tavsif }}@if(!$loop->last), @endif
    @endforeach
</div>

<div class="bank-wrap shadow-sm" style="height:calc(100vh - 320px)">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Nomi</th>
                <th class="tl">Matn (qisqartirilgan)</th>
                <th>Qayta yuborish</th>
                <th>Holat</th>
                <th>Tartib</th>
                <th style="width:90px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($shablonlar as $sh)
            <tr>
                <td class="tl sticky-col fw-semibold">{{ $sh->nomi }}</td>
                <td class="tl text-muted">{{ Str::limit($sh->matn, 90) }}</td>
                <td class="text-center">
                    @if($sh->qayta_yuborish_kun > 0)
                    <span class="badge-modern" style="background:#e0e7ff;color:#3730a3">{{ $sh->qayta_yuborish_kun }} kun</span>
                    @else
                    <span class="text-muted small">Cheklovsiz</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($sh->holat === 'faol')
                    <span class="badge-modern" style="background:#22c55e;color:#fff">Faol</span>
                    @else
                    <span class="badge-modern" style="background:#64748b;color:#fff">Nofaol</span>
                    @endif
                </td>
                <td class="text-center text-muted">{{ $sh->sort_order }}</td>
                <td class="text-center">
                    <button class="btn btn-outline-primary btn-sm py-0 px-1" data-bs-toggle="modal" data-bs-target="#demoModal{{ $sh->id }}" title="Demo ko'rish">
                        <i class="bi bi-eye"></i>
                    </button>
                    @if(auth()->user()->isAdmin())
                    <button class="btn btn-outline-secondary btn-sm py-0 px-1"
                        onclick='editShablon({{ $sh->id }}, @json($sh->nomi), @json($sh->matn), {{ $sh->qayta_yuborish_kun }}, "{{ $sh->holat }}", {{ $sh->sort_order }})'>
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="{{ route('malumotnamalar.pochta-shablonlar.destroy', $sh) }}" class="d-inline"
                          onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>

            {{-- Demo ko'rish modali --}}
            <div class="modal fade" id="demoModal{{ $sh->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h6 class="modal-title"><i class="bi bi-eye me-1"></i>Demo ko'rish — {{ $sh->nomi }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="bi bi-info-circle me-1"></i>Bu — sinov (demo) ma'lumotlar bilan to'ldirilgan namuna. Haqiqiy xat mijozning o'z ma'lumotlari bilan yuboriladi.
                            </div>
                            <div class="p-3 border rounded bg-light" style="white-space:pre-wrap; font-family:Georgia,serif; line-height:1.6;">{{ $sh->demoMatn }}</div>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Yopish</button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Hozircha shablon yo'q. Yangi shablon qo'shing.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(auth()->user()->isAdmin())
{{-- ADD Modal --}}
<div class="modal fade" id="addShablonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('malumotnamalar.pochta-shablonlar.store') }}">
            @csrf
            <input type="hidden" name="tab" value="shablonlar">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Yangi pochta shabloni</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('malumotnamalar.pochta-shablonlar._form', ['sh' => null, 'prefix' => ''])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-sm btn-primary">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT Modal --}}
<div class="modal fade" id="editShablonModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editShablonForm">
            @csrf @method('PUT')
            <input type="hidden" name="tab" value="shablonlar">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Shablonni tahrirlash</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('malumotnamalar.pochta-shablonlar._form', ['sh' => null, 'prefix' => 'hp_edit_'])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-sm btn-primary">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endif

</div>

@push('scripts')
<script>
function editShablon(id, nomi, matn, kun, holat, sort) {
    document.getElementById('editShablonForm').action = '/malumotnamalar/pochta-shablonlar/' + id;
    document.getElementById('hp_edit_nomi').value               = nomi;
    document.getElementById('hp_edit_matn').value               = matn;
    document.getElementById('hp_edit_qayta_yuborish_kun').value = kun;
    document.getElementById('hp_edit_holat').value              = holat;
    document.getElementById('hp_edit_sort_order').value          = sort;
    new bootstrap.Modal(document.getElementById('editShablonModal')).show();
}

(function () {
    const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.log-ochirish-btn');
        if (!btn) return;

        if (!confirm("Bu log yozuvi ro'yxatdan butunlay o'chirilsinmi?")) return;

        const logId = btn.dataset.logId;
        btn.disabled = true;
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch(`{{ url('hibrit-pochta/log') }}/${logId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.ok) {
                btn.closest('tr').remove();
            } else {
                alert('Xato: ' + (d.xato || "Noma'lum xato"));
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        })
        .catch(err => {
            alert('Tarmoq xatosi: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = origHtml;
        });
    });

})();
</script>

{{-- Pochta xat yuborish oynasi (Kutilayotgan tabidagi barcha "Xat" tugmalari uchun umumiy) --}}
<script>
(function() {
"use strict";
if (!document.getElementById('pochtaXatModal')) return;

const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
let state = { kreditId: null, letterId: null, hash: null, logId: null, eimzoKeys: [], shablonlar: {}, kreditVars: {}, pochtaLoglar: [] };

const modalEl    = document.getElementById('pochtaXatModal');
const loadingBox = document.getElementById('hp-loading');
const step1      = () => document.getElementById('hp-step1');
const step2      = () => document.getElementById('hp-step2');
const step3      = () => document.getElementById('hp-step3');
const btnNext    = document.getElementById('hp-step1-next');
const btnBack    = document.getElementById('hp-step2-back');
const btnSign    = document.getElementById('hp-sign-btn');
const selShablon = document.getElementById('hp-shablon');
const selRegion  = document.getElementById('hp-region');
const selArea    = document.getElementById('hp-area');
const selKey     = document.getElementById('hp-eimzo-key');

async function loadRegions() {
    try {
        const r = await fetch('{{ route("hibrit_pochta.regions") }}', { headers: { 'Accept': 'application/json' } });
        const arr = await r.json();
        arr.forEach(reg => selRegion.appendChild(new Option(reg.Name, reg.Id)));
    } catch (e) {
        selRegion.innerHTML = '<option value="">Viloyatlar yuklanmadi</option>';
    }
}

async function loadAreas(regionId, selectedId = '') {
    selArea.innerHTML = '<option value="">Yuklanmoqda...</option>';
    selArea.disabled = true;
    try {
        const r = await fetch('{{ route("hibrit_pochta.areas") }}?region_id=' + regionId, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        });
        const filtered = await r.json();
        selArea.innerHTML = '<option value="">— Tuman tanlang —</option>';
        filtered.forEach(area => selArea.appendChild(new Option(area.Name, area.Id, false, String(area.Id) === String(selectedId))));
        selArea.disabled = false;
    } catch (e) {
        selArea.innerHTML = '<option value="">Tumanlar yuklanmadi</option>';
        selArea.disabled = false;
    }
}

selRegion.addEventListener('change', () => {
    if (selRegion.value) loadAreas(selRegion.value);
    else { selArea.innerHTML = '<option value="">— Avval viloyat tanlang —</option>'; selArea.disabled = true; }
    validateForm();
});
selArea.addEventListener('change', validateForm);

selShablon.addEventListener('change', function() {
    const id = this.value;
    const previewBox = document.getElementById('hp-preview-box');
    if (id && state.shablonlar[id]) {
        let matn = state.shablonlar[id];
        Object.entries(state.kreditVars).forEach(([k, v]) => { matn = matn.replaceAll('{{' + k + '}}', v); });
        document.getElementById('hp-preview-text').textContent = matn;
        document.getElementById('hp-preview-link').href = `/kreditlar/${state.kreditId}/pochta/preview?shablon_id=${id}`;
        previewBox.classList.remove('d-none');
    } else {
        previewBox.classList.add('d-none');
    }
    checkLimit();
    validateForm();
});

const limitInfo = document.getElementById('hp-limit-info');
function checkLimit() {
    const id = selShablon.value;
    const kun = selShablon.selectedOptions[0]?.dataset.kun;
    limitInfo.classList.add('d-none');
    if (!id || !kun || kun === '0') return;

    const lastLog = state.pochtaLoglar.find(l => String(l.shablon_id) === id && l.holat === 'yuborildi');
    if (!lastLog) return;

    const daysSince = Math.floor((Date.now() - new Date(lastLog.yuborildi_vaqt)) / 86400000);
    if (daysSince < parseInt(kun)) {
        limitInfo.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>Oxirgi xat: <strong>${daysSince} kun</strong> oldin yuborilgan. Minimum oraliq: ${kun} kun. <strong>${kun - daysSince} kun qolgan.</strong>`;
        limitInfo.classList.remove('d-none');
    }
}

function validateForm() {
    const ok = selShablon.value && document.getElementById('hp-receiver').value.trim()
        && document.getElementById('hp-address').value.trim()
        && selRegion.value && selArea.value;
    btnNext.disabled = !ok;
}
['hp-receiver', 'hp-address'].forEach(id => document.getElementById(id).addEventListener('input', validateForm));

function loadEIMZO() {
    if (typeof EIMZOClient !== 'undefined') { initEIMZO(); return; }

    // Sahifa HTTPS orqali yuklanadi — E-IMZO skriptini http:// dan yuklashga
    // urinish "mixed content" sifatida brauzer tomonidan bloklanadi (E-IMZO
    // haqiqatan ishlab turgan bo'lsa ham). Shuning uchun avval sahifa
    // protokoliga mos https:// bilan, keyin (eski E-IMZO versiyalari uchun)
    // http:// bilan urinamiz.
    const urls = location.protocol === 'https:'
        ? ['https://127.0.0.1:64443/eimzo/eimzo.js', 'http://127.0.0.1:64443/eimzo/eimzo.js']
        : ['http://127.0.0.1:64443/eimzo/eimzo.js', 'https://127.0.0.1:64443/eimzo/eimzo.js'];

    function tryLoad(i) {
        if (i >= urls.length) {
            document.getElementById('hp-eimzo-loading').classList.add('d-none');
            document.getElementById('hp-eimzo-error').classList.remove('d-none');
            return;
        }
        const s = document.createElement('script');
        s.src = urls[i];
        s.onload = initEIMZO;
        s.onerror = () => tryLoad(i + 1);
        document.head.appendChild(s);
    }
    tryLoad(0);
}

function initEIMZO() {
    try {
        EIMZOClient.API_KEYS = [['localhost', '96D0C1491615C82B9A54D9989779DF825B690748A7C9E9B0B5DA85F2FF7A7E29']];
        EIMZOClient.loadKeys(
            function(keys) {
                state.eimzoKeys = keys || [];
                document.getElementById('hp-eimzo-loading').classList.add('d-none');
                if (keys && keys.length > 0) {
                    const sel = document.getElementById('hp-eimzo-key');
                    sel.innerHTML = '<option value="">— Sertifikat tanlang —</option>';
                    keys.forEach((k, i) => {
                        const exp = k.validTo ? ` (${k.validTo.substring(0, 10)})` : '';
                        sel.appendChild(new Option(`${k.CN || k.alias}${exp}`, i));
                    });
                    document.getElementById('hp-eimzo-keys-box').classList.remove('d-none');
                    sel.addEventListener('change', function() {
                        btnSign.classList.toggle('d-none', !this.value && this.value !== '0');
                        if (this.value !== '') {
                            const k = state.eimzoKeys[parseInt(this.value)];
                            document.getElementById('hp-cert-info').textContent =
                                k ? `PINFL: ${k.TIN || '—'} | Tashkilot: ${k.O || '—'} | Muddati: ${k.validTo || '—'}` : '';
                        }
                    });
                } else {
                    document.getElementById('hp-eimzo-error').innerHTML =
                        '<i class="bi bi-exclamation-triangle me-1"></i>E-IMZO da sertifikat topilmadi. Sertifikat o\'rnatilganini tekshiring.';
                    document.getElementById('hp-eimzo-error').classList.remove('d-none');
                }
            },
            function(e, r) {
                document.getElementById('hp-eimzo-loading').classList.add('d-none');
                document.getElementById('hp-eimzo-error').classList.remove('d-none');
                document.getElementById('hp-eimzo-error').textContent = 'E-IMZO xato: ' + (e || r || "Noma'lum xato");
            }
        );
    } catch (ex) {
        document.getElementById('hp-eimzo-loading').classList.add('d-none');
        document.getElementById('hp-eimzo-error').classList.remove('d-none');
    }
}

btnNext.addEventListener('click', async function() {
    btnNext.disabled = true;
    btnNext.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Xat yaratilmoqda...';

    const body = {
        shablon_id: selShablon.value,
        receiver: document.getElementById('hp-receiver').value.trim(),
        address: document.getElementById('hp-address').value.trim(),
        region_id: selRegion.value,
        area_id: selArea.value,
    };

    try {
        const resp = await fetch(`/kreditlar/${state.kreditId}/pochta/create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await resp.json();

        if (!resp.ok || !data.ok) {
            alert('Xato: ' + (data.xato || "Noma'lum xato"));
            btnNext.disabled = false;
            btnNext.innerHTML = 'Yuborish <i class="bi bi-arrow-right ms-1"></i>';
            return;
        }

        state.letterId = data.letter_id;
        state.hash = data.hash;
        state.logId = data.log_id;

        // E-IMZO bilan tasdiqlash/yuborish endi hybrid.pochta.uz kabinetida operator
        // tomonidan bajariladi — biz faqat xatni yaratib, kabinetga havola beramiz.
        step1().classList.add('d-none');
        step3().classList.remove('d-none');
        document.getElementById('hp-result-ok').classList.remove('d-none');
        document.getElementById('hp-result-msg').textContent = `Xat ID: #${data.letter_id} · Log: #${data.log_id}`;

        btnNext.disabled = false;
        btnNext.innerHTML = 'Yuborish <i class="bi bi-arrow-right ms-1"></i>';
    } catch (e) {
        alert('Tarmoq xatosi: ' + e.message);
        btnNext.disabled = false;
        btnNext.innerHTML = 'Yuborish <i class="bi bi-arrow-right ms-1"></i>';
    }
});

btnBack.addEventListener('click', () => {
    step2().classList.add('d-none');
    step1().classList.remove('d-none');
    btnNext.disabled = false;
    btnNext.innerHTML = 'E-IMZO imzolash <i class="bi bi-arrow-right ms-1"></i>';
});

btnSign.addEventListener('click', function() {
    const keyIdx = parseInt(selKey.value);
    if (isNaN(keyIdx)) { alert('Sertifikat tanlang'); return; }
    const key = state.eimzoKeys[keyIdx];
    if (!key) { alert('Sertifikat topilmadi'); return; }

    btnSign.disabled = true;
    btnSign.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Imzolash...';

    function hexToBase64(hex) {
        const bytes = new Uint8Array(hex.match(/../g).map(h => parseInt(h, 16)));
        let bin = '';
        bytes.forEach(b => bin += String.fromCharCode(b));
        return btoa(bin);
    }

    const dataB64 = hexToBase64(state.hash);

    EIMZOClient.createPkcs7(
        key.id ?? key.serialNumber ?? keyIdx,
        dataB64,
        null,
        async function(pkcs7b64) {
            btnSign.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Yuborilmoqda...';
            try {
                const resp = await fetch(`/kreditlar/${state.kreditId}/pochta/send`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ letter_id: state.letterId, signature: pkcs7b64, log_id: state.logId }),
                });
                const data = await resp.json();

                step2().classList.add('d-none');
                step3().classList.remove('d-none');

                if (resp.ok && data.ok) {
                    document.getElementById('hp-result-ok').classList.remove('d-none');
                    document.getElementById('hp-result-msg').textContent = `Xat ID: #${data.letter_id} · Log: #${data.log_id}`;
                } else {
                    document.getElementById('hp-result-err').classList.remove('d-none');
                    document.getElementById('hp-result-err-msg').textContent = data.xato || "Noma'lum xato";
                }
            } catch (e) {
                step2().classList.add('d-none');
                step3().classList.remove('d-none');
                document.getElementById('hp-result-err').classList.remove('d-none');
                document.getElementById('hp-result-err-msg').textContent = 'Tarmoq xatosi: ' + e.message;
            }
        },
        function(e, r) {
            btnSign.disabled = false;
            btnSign.innerHTML = '<i class="bi bi-pen me-1"></i>Imzolash va Yuborish';
            alert('E-IMZO imzolash xatosi: ' + (e || r || "Noma'lum"));
        }
    );
});

// "Xat" tugmasi bosilganda: avval kredit ma'lumotlarini yuklaymiz, keyin oynani ochamiz.
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.xat-yuborish-btn');
    if (!btn) return;

    state = { kreditId: btn.dataset.kreditId, letterId: null, hash: null, logId: null, eimzoKeys: [], shablonlar: {}, kreditVars: {}, pochtaLoglar: [] };

    step1().classList.add('d-none');
    step2().classList.add('d-none');
    step3().classList.add('d-none');
    loadingBox.classList.remove('d-none');
    document.getElementById('hp-result-ok').classList.add('d-none');
    document.getElementById('hp-result-err').classList.add('d-none');

    new bootstrap.Modal(modalEl).show();

    fetch(`{{ url('hibrit-pochta') }}/${state.kreditId}/malumot`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => {
            loadingBox.classList.add('d-none');
            if (!d.ok) {
                alert('Xato: ' + (d.xato || "Ma'lumot yuklanmadi"));
                bootstrap.Modal.getInstance(modalEl)?.hide();
                return;
            }

            state.shablonlar = {};
            (d.shablonlar || []).forEach(sh => { state.shablonlar[sh.id] = sh.matn; });
            state.kreditVars = d.vars || {};
            state.pochtaLoglar = d.loglar || [];

            selShablon.innerHTML = '<option value="">— Shablon tanlang —</option>';
            (d.shablonlar || []).forEach(sh => {
                const label = sh.nomi + (sh.qayta_yuborish_kun > 0 ? ` (${sh.qayta_yuborish_kun} kun)` : '');
                const opt = new Option(label, sh.id);
                opt.dataset.kun = sh.qayta_yuborish_kun;
                selShablon.appendChild(opt);
            });

            document.getElementById('hp-receiver').value = d.mijoz?.tolik_ism ?? '';
            document.getElementById('hp-address').value = d.mijoz?.manzil ?? '';
            document.getElementById('hp-preview-box').classList.add('d-none');
            limitInfo.classList.add('d-none');

            step1().classList.remove('d-none');
            btnNext.disabled = true;
            btnNext.innerHTML = 'Yuborish <i class="bi bi-arrow-right ms-1"></i>';

            if (selRegion.options.length <= 1) loadRegions();
            validateForm();
        })
        .catch(err => {
            loadingBox.classList.add('d-none');
            alert("Tarmoq xatosi: " + err.message);
            bootstrap.Modal.getInstance(modalEl)?.hide();
        });
});
})();

(function () {
    var table = document.querySelector('.bank-table');
    if (!table) return;
    var UST_KEY = 'hp_ustun_korinishi';
    var saqlangan = {};
    try { saqlangan = JSON.parse(localStorage.getItem(UST_KEY)) || {}; } catch (e) {}

    function ustunniQoy(col, korinsin) {
        table.querySelectorAll('.col-' + col).forEach(function (el) { el.classList.toggle('d-none', !korinsin); });
    }

    document.querySelectorAll('.ustun-toggle-hp').forEach(function (cb) {
        var col = cb.dataset.col;
        var def = cb.dataset.default === '1';
        var korinsin = saqlangan.hasOwnProperty(col) ? !!saqlangan[col] : def;
        cb.checked = korinsin;
        ustunniQoy(col, korinsin);
        cb.addEventListener('change', function () {
            saqlangan[col] = cb.checked;
            localStorage.setItem(UST_KEY, JSON.stringify(saqlangan));
            ustunniQoy(col, cb.checked);
        });
    });
})();
</script>
@endpush
@endsection
