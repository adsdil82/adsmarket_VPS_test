@extends(request('embed') ? 'layouts.iframe' : 'layouts.app')

@section('title', $kredit->shartnoma_raqam)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item active">{{ $kredit->shartnoma_raqam }}</li>
@endsection

@section('content')

@push('styles')
<style>
.sh-header {
    background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd;
    border-radius:8px; padding:10px 14px; margin-bottom:10px;
}
.sh-stat { text-align:center; padding:2px 8px; border-right:1px solid #93c5fd; }
.sh-stat:last-child { border-right:none; }
.sh-stat .lbl { font-size:.62rem; text-transform:uppercase; letter-spacing:.03em; color:#3b5fc0; font-weight:700; }
.sh-stat .val { font-size:.92rem; font-weight:800; color:#1e293b; }

.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
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
.bank-table tbody tr.row-muddati-otgan td { background:#fee2e2 !important; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table .num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap-15 { overflow:auto; height:410px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.bank-table tfoot { position:sticky; bottom:0; z-index:5; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800;
    padding:7px 8px; border-top:2px solid #60a5fa; white-space:nowrap;
}

/* Bank-uslub ixcham jadval (Hujjatlar, Versiyalar, Kafil, Boshlang'ich to'lov) */
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-wrap { max-width:640px; border:1px solid #93c5fd; border-radius:6px; overflow:hidden; }
.bft-wrap-lg { max-width:960px; }
.bft-table { width:auto; margin-bottom:0 !important; font-size:.83rem; }
.bft-table td, .bft-table th { padding:7px 10px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:auto; }
.bft-doc-table { width:100%; table-layout:fixed; }
.bft-doc-table thead th {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; font-weight:600;
    padding:7px 10px; border-bottom:none;
}
.bft-doc-table tbody tr:hover { background:#eef3ff !important; }
.bft-doc-table tfoot td { background:#eef3ff; font-weight:700; border-top:2px solid #93c5fd; border-bottom:none; }
</style>
@endpush

{{-- ── Ixcham sarlavha ───────────────────────────────────────────── --}}
<div class="sh-header d-flex flex-wrap align-items-center gap-3">
    <div>
        <div class="fw-bold" style="font-size:1.05rem">
            {{ $kredit->shartnoma_raqam }}
            <span class="badge bg-{{ $kredit->holat_rangi }} ms-1">{{ $kredit->holatNomi }}</span>
        </div>
        <div class="text-muted" style="font-size:.76rem">
            <a href="{{ route('mijozlar.show', $kredit->mijoz) }}" class="text-decoration-none">
                <i class="bi bi-person me-1"></i>{{ $kredit->mijoz->tolik_ism }}
            </a>
            · {{ $kredit->filial->nomi }}
            · {{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }}–{{ $kredit->tugash_sana?->format('d.m.Y') ?? '—' }}
            · {{ $kredit->muddati_oy }} oy
        </div>
    </div>

    <div class="d-flex flex-wrap" style="border-left:1px solid #93c5fd;border-right:1px solid #93c5fd;padding:0 10px">
        <div class="sh-stat"><div class="lbl">Jami</div><div class="val">{{ number_format($kredit->jami_summa,0,'.',' ') }}</div></div>
        <div class="sh-stat"><div class="lbl">Boshlang'ich</div><div class="val">{{ number_format($kredit->boshlangich_tolov,0,'.',' ') }}</div></div>
        <div class="sh-stat"><div class="lbl">Kredit</div><div class="val" style="color:#1d4ed8">{{ number_format($kredit->kredit_summa,0,'.',' ') }}</div></div>
        <div class="sh-stat"><div class="lbl">To'langan</div><div class="val" style="color:#16a34a">{{ number_format($kredit->tolov_qilingan,0,'.',' ') }}</div></div>
        <div class="sh-stat"><div class="lbl">Qoldiq</div><div class="val" style="color:{{ $kredit->qoldiq_qarz>0?'#dc2626':'#16a34a' }}">{{ number_format($kredit->qoldiq_qarz,0,'.',' ') }}</div></div>
        <div class="sh-stat"><div class="lbl">Oylik</div><div class="val">{{ number_format($kredit->oylik_tolov_miqdori,0,'.',' ') }}</div></div>
    </div>

    <div class="mini-progress" style="width:90px;height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden" title="{{ $kredit->tolov_foizi }}% to'langan">
        <div style="height:100%;width:{{ $kredit->tolov_foizi }}%;background:#22c55e"></div>
    </div>

@unless(request('embed'))
    <div class="d-flex gap-1 flex-wrap ms-auto">
        @if($kredit->holat === 'kutilmoqda' && in_array(Auth::user()->rol, ['admin','menejer']))
        <form action="{{ route('kreditlar.activate', $kredit) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Aktivlashtirilsinmi? Bu amaldan keyin ombordan tovar chiqariladi va boshlang\'ich to\'lov rasmiylashtiriladi.');">
            @csrf
            <button type="submit" class="btn btn-info btn-sm"><i class="bi bi-check2-circle me-1"></i>Aktivlashtirish</button>
        </form>
        @endif
        @if(Auth::user()->isKassir() && $kredit->holat !== 'yopilgan' && $kredit->holat !== 'kutilmoqda')
        <a href="{{ route('kreditlar.tulov.create', $kredit) }}" class="btn btn-success btn-sm"><i class="bi bi-cash-coin me-1"></i>To'lov</a>
        @endif
        <a href="{{ route('kreditlar.pdf', $kredit) }}" class="btn btn-outline-secondary btn-sm" target="_blank"><i class="bi bi-file-pdf"></i></a>
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('kreditlar.edit', $kredit) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#xodimTayinModal" title="Xodimga qayta tayinlash"><i class="bi bi-person-gear"></i></button>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#filialKochirModal" title="Filialga ko'chirish"><i class="bi bi-building-gear"></i></button>
        @endif
        @if($hp_yoqilgan && in_array(Auth::user()->rol, ['admin','menejer']))
        @php $kechikkan = $kredit->holat === 'muddati_otgan' || ($kredit->tugash_sana && $kredit->tugash_sana->lt(today())); @endphp
        @if($kechikkan)
        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#pochtaXatModal"><i class="bi bi-envelope-paper"></i></button>
        @endif
        @endif
    </div>
@endunless
</div>

@php
    // Ba'zi eski (legacy) shartnomalarda grafik jadvali haqiqiy muddatdan
    // ko'proq (masalan 12 ta) bo'sh qator bilan saqlangan (sana=NULL,
    // summa=0) — bular ko'rsatilmasligi kerak, faqat haqiqiy oylar.
    $grafikQatorlari = $kredit->grafik->filter(fn($g) => $g->tolov_sana !== null);
@endphp

{{-- ── Tablar ───────────────────────────────────────────────────── --}}
<ul class="nav nav-tabs mb-3" id="kreditTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-grafik">
            <i class="bi bi-calendar3 me-1"></i> To'lov grafigi
            <span class="badge bg-secondary ms-1">{{ $grafikQatorlari->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-tovarlar">
            <i class="bi bi-box-seam me-1"></i> Tovarlar
            <span class="badge bg-secondary ms-1">{{ $kredit->tovarlar->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-tulovlar">
            <i class="bi bi-receipt me-1"></i> To'lovlar
            <span class="badge bg-secondary ms-1">{{ $kredit->tulovlar->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-oldin">
            <i class="bi bi-cash me-1"></i> Boshlang'ich to'lov
            <span class="badge bg-secondary ms-1">{{ $kredit->oldinTulovlar->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-kafil">
            <i class="bi bi-person-check me-1"></i> Kafil
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-hujjatlar">
            <i class="bi bi-file-earmark-text me-1"></i> Hujjatlar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-versiyalar">
            <i class="bi bi-clock-history me-1"></i> Versiyalar
            <span class="badge bg-secondary ms-1">{{ $kredit->versiyalar->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-pochta">
            <i class="bi bi-envelope-paper me-1"></i> Pochta
            @if($pochta_loglar->count() > 0)
            <span class="badge bg-warning text-dark ms-1" style="font-size:10px">{{ $pochta_loglar->count() }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-sms">
            <i class="bi bi-chat-dots me-1"></i> SMS
            @if($sms_loglar->count() > 0)
            <span class="badge bg-warning text-dark ms-1" style="font-size:10px">{{ $sms_loglar->count() }}</span>
            @endif
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- ── To'lov grafigi ─────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="tab-grafik">
        <div class="d-flex justify-content-end align-items-center mb-2">
            <a href="{{ route('kreditlar.hujjat.html', [$kredit, 'plan_fakt']) }}"
               class="btn btn-sm btn-outline-primary"
               onclick="event.preventDefault(); hujjatModalOch('{{ route('kreditlar.hujjat.html', [$kredit, 'plan_fakt']) }}', 'Plan / Fakt vidoma (sverka)', false)">
                <i class="bi bi-printer me-1"></i>Plan/Fakt vidoma
            </a>
        </div>
        <div class="row g-3">
            {{-- ── Reja (plan) — o'zgarmaydi ─────────────────────────── --}}
            <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-1" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)">
                    <h6 class="mb-0 small fw-bold text-muted"><i class="bi bi-calendar3 me-1"></i>Reja (shartnoma bo'yicha)</h6>
                </div>
                <div class="bank-wrap-15">
                    <table class="bank-table">
                        <thead>
                            <tr>
                                <th class="tl" style="width:30px">#</th>
                                <th class="tl">Sana</th>
                                <th>Summa</th>
                                @if($ustamaKorishMumkin)
                                <th class="ustama-col-2 d-none">Ustama</th>
                                @endif
                                <th>Qoldiq</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grafikQatorlari as $g)
                            <tr>
                                <td class="tl text-muted">{{ $g->oylik_tartib }}</td>
                                <td class="tl">{{ $g->tolov_sana?->format('d.m.Y') ?? '—' }}</td>
                                <td class="num">{{ $g->tolov_summa !== null ? number_format($g->tolov_summa, 0, '.', ' ') : '—' }}</td>
                                @if($ustamaKorishMumkin)
                                <td class="num ustama-col-2 d-none" style="color:#b45309">
                                    {{ $g->ustama_summa > 0 ? number_format($g->ustama_summa, 0, '.', ' ') : '—' }}
                                </td>
                                @endif
                                <td class="num text-muted">{{ $g->qoldiq_suma !== null ? number_format($g->qoldiq_suma, 0, '.', ' ') : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="tl" colspan="2">Jami:</td>
                                <td class="num">{{ number_format($grafikQatorlari->sum('tolov_summa'), 0, '.', ' ') }}</td>
                                @if($ustamaKorishMumkin)
                                <td class="num ustama-col-2 d-none">{{ number_format($grafikQatorlari->sum('ustama_summa'), 0, '.', ' ') }}</td>
                                @endif
                                <td class="num">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            </div>

            {{-- ── Fakt — haqiqiy to'lovlardan keyingi holat ─────────── --}}
            <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-1 d-flex justify-content-between align-items-center" style="background:linear-gradient(90deg,#eef3ff,#e8f0fe)">
                    <h6 class="mb-0 small fw-bold"><i class="bi bi-check2-circle me-1 text-success"></i>Fakt (haqiqiy to'langan)</h6>
                    @if($ustamaKorishMumkin)
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="ustama-toggle-btn-2"
                            onclick="ustamaKoUrish2()" title="Ustama ustunini ko'rsatish/yashirish">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                    @endif
                </div>
                <div class="bank-wrap-15">
                    <table class="bank-table">
                        <thead>
                            <tr>
                                <th class="tl" style="width:30px">#</th>
                                <th>To'langan</th>
                                <th class="tl">Sana</th>
                                <th>Qoldiq</th>
                                <th class="tl">Holat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $faktTolangan = 0; $faktQoldiq = $kredit->kredit_summa; @endphp
                            @foreach($grafikQatorlari as $g)
                            @php
                                $faktTolangan += (float) $g->tolangan_summa;
                                $faktQoldiq = max(0, $kredit->kredit_summa - $faktTolangan);
                            @endphp
                            <tr class="{{ $g->holat === 'muddati_otgan' ? 'row-muddati-otgan' : '' }}">
                                <td class="tl text-muted">{{ $g->oylik_tartib }}</td>
                                <td class="num" style="color:#16a34a">
                                    {{ $g->tolangan_summa > 0 ? number_format($g->tolangan_summa, 0, '.', ' ') : '—' }}
                                </td>
                                <td class="tl text-muted">{{ $g->tolangan_sana?->format('d.m.Y') ?? '—' }}</td>
                                <td class="num fw-semibold">{{ number_format($faktQoldiq, 0, '.', ' ') }}</td>
                                <td class="tl">
                                    <span class="badge bg-{{ $g->holat_rangi }} badge-holat">
                                        {{ $g->holat === 'faol' ? 'AKTIV' : ($g->holat === 'yopilgan' ? 'PASSIV' : $g->holat) }}
                                    </span>
                                    @if($g->kechikish_kunlari > 0)
                                        <span class="text-danger small ms-1">{{ $g->kechikish_kunlari }} kun</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="tl" colspan="2">Jami: {{ number_format($faktTolangan, 0, '.', ' ') }}</td>
                                <td class="tl"></td>
                                <td class="num">{{ number_format($faktQoldiq, 0, '.', ' ') }}</td>
                                <td class="tl"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- ── Tovarlar ────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-tovarlar">
        <div class="bft-wrap bft-wrap-lg">
            <table class="bft-table bft-doc-table">
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Tovar nomi</th>
                        <th class="text-center" style="width:70px">Soni</th>
                        <th class="text-end" style="width:130px">Narx</th>
                        <th class="text-end" style="width:130px">Jami</th>
                        <th style="width:120px">Barkod</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kredit->tovarlar as $i => $tovar)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="fw-medium">{{ $tovar->nomi }}</td>
                        <td class="text-center">{{ $tovar->soni }}</td>
                        <td class="text-end">{{ number_format($tovar->narx, 0, '.', ' ') }}</td>
                        <td class="text-end fw-bold">{{ number_format($tovar->jami_narx, 0, '.', ' ') }}</td>
                        <td class="text-muted small">{{ $tovar->barkod ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Jami:</td>
                        <td class="text-end fw-bold text-primary">
                            {{ number_format($kredit->tovarlar->sum('jami_narx'), 0, '.', ' ') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── To'lovlar ───────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-tulovlar">
        @if(Auth::user()->isKassir() && $kredit->holat !== 'yopilgan')
        <div class="mb-2 text-end">
            <a href="{{ route('kreditlar.tulov.create', $kredit) }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i> To'lov qabul qilish
            </a>
        </div>
        @endif
        <div class="bft-wrap bft-wrap-lg">
                <table class="bft-table bft-doc-table">
                    <thead>
                        <tr>
                            <th style="width:55px">ID</th>
                            <th style="width:100px">Sana</th>
                            <th class="text-end" style="width:120px">Summa</th>
                            <th style="width:120px">To'lov turi</th>
                            <th style="width:130px">Kassir</th>
                            <th style="width:110px">Kvitansiya #</th>
                            <th>Izoh</th>
                            <th class="text-center" style="width:100px">Chop</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kredit->tulovlar as $tulov)
                        <tr>
                            <td class="text-muted small">#{{ $tulov->id }}</td>
                            <td>{{ $tulov->tolov_sana?->format('d.m.Y') ?? '—' }}</td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($tulov->summa, 0, '.', ' ') }}
                            </td>
                            <td>{{ $tulov->tulovTuri->nomi }}</td>
                            <td class="text-muted small">{{ $tulov->xodim->ism_familiya }}</td>
                            <td class="text-muted small">{{ $tulov->kvitansiya_raqam ?? '—' }}</td>
                            <td class="text-muted small">{{ $tulov->izoh ?? '—' }}</td>
                            <td class="text-center" style="white-space:nowrap">
                                {{-- Kvitansiya --}}
                                <button type="button"
                                   class="btn btn-sm btn-outline-success py-0 px-1"
                                   data-url="{{ route('kreditlar.tulov.kvitansiya', [$kredit, $tulov]) }}"
                                   title="Kvitansiya chop etish"
                                   onclick="kvitansiyaModalOch(this.getAttribute('data-url'))">
                                    <i class="bi bi-printer-fill"></i>
                                </button>
                                {{-- Tahrirlash (admin + menejer) --}}
                                @if(Auth::user()->isMenejerYoki())
                                <button type="button"
                                   class="btn btn-sm btn-outline-warning py-0 px-1 ms-1"
                                   title="To'lovni tahrirlash"
                                   onclick="tulovTahrirlash(
                                       {{ $tulov->id }},
                                       '{{ $tulov->tolov_sana?->format('Y-m-d') }}',
                                       {{ $tulov->summa }},
                                       {{ $tulov->tulov_turi_id }},
                                       '{{ addslashes($tulov->kvitansiya_raqam ?? '') }}',
                                       '{{ addslashes($tulov->izoh ?? '') }}',
                                       '{{ route('kreditlar.tulov.update', [$kredit, $tulov]) }}'
                                   )">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                @endif
                                {{-- O'chirish (faqat Admin) --}}
                                @if(Auth::user()->isAdmin())
                                <button type="button"
                                   class="btn btn-sm btn-outline-danger py-0 px-1 ms-1"
                                   title="To'lovni o'chirish"
                                   onclick="tulovOchirish(
                                       {{ $tulov->id }},
                                       '{{ number_format($tulov->summa, 0, '.', ' ') }}',
                                       '{{ $tulov->tolov_sana?->format('d.m.Y') }}',
                                       '{{ route('kreditlar.tulov.destroy', [$kredit, $tulov]) }}'
                                   )">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">To'lovlar yo'q</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($kredit->tulovlar->count() > 0)
                    <tfoot>
                        <tr>
                            <td class="fw-bold">Jami:</td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($kredit->tulovlar->sum('summa'), 0, '.', ' ') }}
                            </td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
        </div>
    </div>

    {{-- ── Boshlang'ich to'lov ─────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-oldin">
        <div class="bft-wrap">
            <table class="bft-table bft-doc-table">
                <thead>
                    <tr>
                        <th style="width:110px">Sana</th>
                        <th class="text-end" style="width:140px">Summa</th>
                        <th style="width:150px">To'lov turi</th>
                        <th>Kassir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kredit->oldinTulovlar as $ot)
                    <tr>
                        <td>{{ $ot->tolov_sana?->format('d.m.Y') ?? '—' }}</td>
                        <td class="text-end fw-bold">{{ number_format($ot->summa, 0, '.', ' ') }}</td>
                        <td>{{ $ot->tulovTuri->nomi }}</td>
                        <td class="text-muted small">{{ $ot->xodim->ism_familiya }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">Boshlang'ich to'lov yo'q</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Kafil ───────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-kafil">
        @if($kredit->kafil)
        {{-- Kafil mijozlar jadvalida mavjud — to'liq karta --}}
        @php $kaf = $kredit->kafil; @endphp
        <div class="bft-section-title d-flex justify-content-between align-items-center">
            <span><i class="bi bi-person-check me-1"></i> Kafil — mijoz kartasi</span>
            <a href="{{ route('mijozlar.show', $kaf) }}" class="btn btn-sm btn-outline-primary py-0">
                <i class="bi bi-eye me-1"></i> Kartani ko'rish
            </a>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <div class="bft-wrap">
                <table class="bft-table">
                    <tbody>
                        <tr>
                            <td class="bft-label">F.I.O.</td>
                            <td class="bft-wide fw-medium">{{ $kaf->tolik_ism }}</td>
                        </tr>
                        <tr>
                            <td class="bft-label">Telefon</td>
                            <td class="bft-wide"><a href="tel:{{ $kaf->telefon }}">{{ $kaf->telefon }}</a></td>
                        </tr>
                        <tr>
                            <td class="bft-label">Passport</td>
                            <td class="bft-wide">{{ $kaf->passport_tolik ?? '—' }}</td>
                        </tr>
                        @if($kaf->pinfl)
                        <tr>
                            <td class="bft-label">PINFL</td>
                            <td class="bft-wide"><code>{{ $kaf->pinfl }}</code></td>
                        </tr>
                        @endif
                        @if($kaf->passport_berilgan_joy)
                        <tr>
                            <td class="bft-label">Passport berilgan joy</td>
                            <td class="bft-wide">{{ $kaf->passport_berilgan_joy }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="bft-label">Tug'ilgan</td>
                            <td class="bft-wide">{{ $kaf->tug_sana?->format('d.m.Y') ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="bft-wrap">
                <table class="bft-table">
                    <tbody>
                        <tr>
                            <td class="bft-label">Manzil</td>
                            <td class="bft-wide">{{ $kaf->manzil ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="bft-label">Ish joyi</td>
                            <td class="bft-wide">{{ $kaf->ish_joyi ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="bft-label">Lavozim</td>
                            <td class="bft-wide">{{ $kaf->lavozimi ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="bft-label">Filial</td>
                            <td class="bft-wide"><span class="badge bg-secondary">{{ $kaf->filial->nomi ?? '—' }}</span></td>
                        </tr>
                        <tr>
                            <td class="bft-label">Holat</td>
                            <td class="bft-wide">
                                <span class="badge bg-{{ $kaf->holat === 'faol' ? 'success' : 'secondary' }}">
                                    {{ $kaf->holat === 'faol' ? 'AKTIV' : 'PASSIV' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @elseif($kredit->kafil_ism)
        {{-- Kafil faqat matn sifatida saqlangan (FK yo'q) --}}
        <div class="bft-wrap">
            <table class="bft-table">
                <tbody>
                    <tr><td class="bft-label">F.I.O.</td><td class="bft-wide">{{ $kredit->kafil_ism }}</td></tr>
                    <tr><td class="bft-label">Telefon</td><td class="bft-wide">{{ $kredit->kafil_telefon ?? '—' }}</td></tr>
                    <tr><td class="bft-label">Manzil</td><td class="bft-wide">{{ $kredit->kafil_manzil ?? '—' }}</td></tr>
                </tbody>
            </table>
        </div>
        @else
        <div class="bft-wrap">
            <div class="text-center text-muted py-5">
                <i class="bi bi-person-dash fs-3 d-block mb-2 opacity-25"></i>
                Kafil ma'lumotlari kiritilmagan
            </div>
        </div>
        @endif
    </div>

    {{-- ── Hujjatlar ────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-hujjatlar">
        @php
        $hujjatlar = [
          ['key'=>'shartnoma',   'icon'=>'file-earmark-text',  'rang'=>'primary',  'nom'=>'Nasiya shartnoma'],
          ['key'=>'kafillik',    'icon'=>'people-fill',        'rang'=>'secondary','nom'=>'Kafillik shartnomasi'],
          ['key'=>'grafik',      'icon'=>'table',              'rang'=>'info',     'nom'=>"To'lov grafigi"],
          ['key'=>'yuk_xati',    'icon'=>'truck',              'rang'=>'warning',  'nom'=>'Yuk xati'],
          ['key'=>'schyot',      'icon'=>'receipt',            'rang'=>'success',  'nom'=>'Schyot-faktura'],
          ['key'=>'ariza',       'icon'=>'envelope-text',      'rang'=>'danger',   'nom'=>'Rahbarga ariza'],
          ['key'=>'til_xat',     'icon'=>'pen-fill',           'rang'=>'dark',     'nom'=>"Til xat (majburiyat)"],
          ['key'=>'plan_fakt',   'icon'=>'clipboard2-data',    'rang'=>'success',  'nom'=>"Plan/Fakt vidoma (sverka)"],
        ];
        $kafilBiriktirilgan = $kredit->kafil_mijoz_id || $kredit->kafil_ism;
        @endphp
        <div class="bft-wrap">
            <table class="bft-table bft-doc-table">
                <thead>
                    <tr><th style="width:36px"></th><th>Hujjat nomi</th><th style="width:110px">Chop etish</th><th style="width:80px">Ko'rish</th><th style="width:90px">Tahrirlash</th></tr>
                </thead>
                <tbody>
            @foreach($hujjatlar as $h)
              @continue($h['key'] === 'kafillik' && !$kafilBiriktirilgan)
                <tr>
                    <td class="text-center"><i class="bi bi-{{ $h['icon'] }} text-{{ $h['rang'] }}"></i></td>
                    <td class="fw-semibold">{{ $h['nom'] }}</td>
                    <td class="text-center">
                        <a href="{{ route('kreditlar.hujjat', [$kredit, $h['key']]) }}"
                           target="_blank"
                           class="btn btn-sm btn-outline-{{ $h['rang'] }}"
                           title="Chop etish">
                            <i class="bi bi-printer"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-{{ $h['rang'] }}"
                                title="Ko'rish"
                                onclick="hujjatModalOch('{{ route('kreditlar.hujjat.html', [$kredit, $h['key']]) }}', '{{ $h['nom'] }}', false)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-{{ $h['rang'] }}"
                                title="Tahrirlash"
                                onclick="hujjatModalOch('{{ route('kreditlar.hujjat.html', [$kredit, $h['key']]) }}', '{{ $h['nom'] }}', true)">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
            @if(!$kafilBiriktirilgan)
                <tr class="text-muted">
                    <td class="text-center"><i class="bi bi-people-fill"></i></td>
                    <td colspan="4"><small><i class="bi bi-info-circle me-1"></i>Kafillik shartnomasi — Kafil biriktirilmagan.</small></td>
                </tr>
            @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Versiyalar ──────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-versiyalar">
        <div class="bft-wrap bft-wrap-lg">
            <table class="bft-table bft-doc-table">
                <thead>
                    <tr>
                        <th style="width:70px">Versiya</th>
                        <th style="width:130px">Sana</th>
                        <th style="width:160px">Xodim</th>
                        <th>Sabab</th>
                        <th>O'zgarishlar</th>
                        <th style="width:56px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kredit->versiyalar as $v)
                    <tr>
                        <td><span class="badge bg-primary">v{{ $v->versiya_raqam }}</span></td>
                        <td class="small">{{ $v->created_at->format('d.m.Y H:i') }}</td>
                        <td class="small">{{ $v->xodim->ism_familiya }}</td>
                        <td class="small">{{ $v->sabab }}</td>
                        <td class="small text-muted">
                            @if($v->ozgargan_maydonlar)
                                {{ implode(', ', $v->ozgargan_maydonlar) }}
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('kreditlar.versiyalar.show', [$kredit, $v]) }}"
                               class="btn btn-sm btn-outline-secondary py-0">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Versiyalar yo'q</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>



    {{-- ── Pochta Xatlar ──────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-pochta">
        @include('kredit._pochta_tab')
    </div>

    {{-- ── SMS yuborish ──────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tab-sms">
        @include('kredit._sms_tab')
    </div>
</div>



{{-- ═══ Tulov O'chirish Confirm Modal ════════════════════════════ --}}
<div class="modal fade" id="tulovOchirishModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
    <div class="modal-content border-0 shadow-lg" style="border-radius:12px">
      <div class="modal-header bg-danger text-white">
        <h6 class="modal-title fw-bold mb-0">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>To'lovni o'chirish
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3" style="font-size:3rem">🗑️</div>
        <p class="mb-1">Quyidagi to'lov <strong class="text-danger">butunlay o'chiriladi</strong>:</p>
        <div class="alert alert-danger py-2 mx-3 my-3">
          <div class="fw-bold fs-5" id="od-summa"></div>
          <div class="text-muted small" id="od-sana"></div>
        </div>
        <p class="text-muted small mb-0">
          ⚠️ Bu amalni qaytarib bo'lmaydi!<br>
          Kredit qoldiq qarzi mos ravishda yangilanadi.
        </p>
      </div>
      <div class="modal-footer py-2 justify-content-center gap-3">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
          <i class="bi bi-x me-1"></i>Bekor
        </button>
        <form id="tulov-ochirish-form" method="POST" style="display:inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger px-4">
            <i class="bi bi-trash me-1"></i>Ha, o'chirish
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ═══ Tulov Tahrirlash Modal ══════════════════════════════════ --}}
<div class="modal fade" id="tulovTahrirlashModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg" style="border-radius:12px">
      <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <h6 class="modal-title text-white fw-bold mb-0">
          <i class="bi bi-pencil-fill me-2"></i>To'lovni tahrirlash
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="tulov-tahrirlash-form" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6">
              <label class="form-label small fw-medium">Summa <span class="text-danger">*</span></label>
              <div class="input-group input-group-sm">
                <input type="number" name="summa" id="tt-summa"
                       class="form-control" step="0.01" min="1" required>
                <span class="input-group-text text-muted">so'm</span>
              </div>
            </div>
            <div class="col-6">
              <label class="form-label small fw-medium">Sana <span class="text-danger">*</span></label>
              <input type="date" name="tolov_sana" id="tt-sana"
                     class="form-control form-control-sm" required>
            </div>
            <div class="col-12">
              <label class="form-label small fw-medium">To'lov turi <span class="text-danger">*</span></label>
              <select name="tulov_turi_id" id="tt-tur" class="form-select form-select-sm" required>
                @foreach(\App\Models\TulovTuri::faol()->get() as $tur)
                <option value="{{ $tur->id }}">{{ $tur->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-medium">Kvitansiya raqami</label>
              <input type="text" name="kvitansiya_raqam" id="tt-kv"
                     class="form-control form-control-sm" placeholder="KV-001">
            </div>
            <div class="col-12">
              <label class="form-label small fw-medium">Izoh</label>
              <textarea name="izoh" id="tt-izoh" class="form-control form-control-sm"
                        rows="2" placeholder="Izoh (ixtiyoriy)"></textarea>
            </div>
          </div>
          <div id="tt-xato" class="alert alert-danger mt-2 py-2 small d-none"></div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
          <button type="submit" class="btn btn-sm btn-warning text-white fw-bold" id="tt-saqlash">
            <i class="bi bi-check2 me-1"></i>Saqlash
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ═══ Xodim Qayta Tayinlash Modal ═══ --}}
@if(Auth::user()->isMenejerYoki())
<div class="modal fade" id="xodimTayinModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <h6 class="modal-title fw-bold text-white mb-0">
          <i class="bi bi-person-gear me-2"></i>Xodim qayta tayinlash
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2 small mb-3">
          <i class="bi bi-info-circle me-1"></i>
          Joriy xodim: <strong>{{ ($kredit->joriy_xodim_id ? $kredit->joriyXodim?->ism_familiya : $kredit->xodim?->ism_familiya) ?? 'Belgilanmagan' }}</strong>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Yangi xodim <span class="text-danger">*</span></label>
          <select id="xt-xodim" class="form-select">
            <option value="">— Tanlang —</option>
            @foreach($xodimlar as $x)
            <option value="{{ $x->id }}">{{ $x->ism_familiya }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Sabab <span class="text-danger">*</span></label>
          <input type="text" id="xt-sabab" class="form-control" placeholder="Nima sababdan..." minlength="5">
        </div>
        <div id="xt-xato" class="alert alert-danger py-2 small d-none"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
        <button type="button" class="btn btn-sm btn-warning text-white fw-bold" id="xt-saqlash" onclick="xodimTayin()">
          <i class="bi bi-check2 me-1"></i>Tayinlash
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ═══ Filial Ko'chirish Modal ═══ --}}
<div class="modal fade" id="filialKochirModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h6 class="modal-title fw-bold mb-0">
          <i class="bi bi-building-gear me-2"></i>Filialga ko'chirish
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger py-2 small mb-3">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Joriy filial: <strong>{{ ($kredit->joriy_filial_id ? $kredit->joriyFilial?->nomi : $kredit->filial?->nomi) ?? '—' }}</strong><br>
          <span class="text-muted">Bu amal ehtiyotkorlik talab qiladi. Sabab majburiy.</span>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Yangi filial <span class="text-danger">*</span></label>
          <select id="fk-filial" class="form-select">
            <option value="">— Tanlang —</option>
            @foreach($filiallar as $f)
            @if($f->id !== ($kredit->joriy_filial_id ?? $kredit->filial_id))
            <option value="{{ $f->id }}">{{ $f->nomi }} ({{ $f->kod }})</option>
            @endif
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Sabab <span class="text-danger">*</span></label>
          <input type="text" id="fk-sabab" class="form-control" placeholder="Ko'chirish sababi..." minlength="10">
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="fk-tolovlar" value="1">
          <label class="form-check-label small" for="fk-tolovlar">
            Keyingi to'lovlar yangi filialda ko'rinsin
          </label>
        </div>
        <div id="fk-xato" class="alert alert-danger py-2 small d-none"></div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
        <button type="button" class="btn btn-sm btn-danger fw-bold" id="fk-saqlash" onclick="filialKochir()">
          <i class="bi bi-check2 me-1"></i>Ko'chirish
        </button>
      </div>
    </div>
  </div>
</div>
@endif

@if($hp_yoqilgan)
@include('kredit._pochta_modal')
@endif

@push('scripts')
<script>
// ── "Ustama" ustunini ko'rsatish/yashirish (faqat ruxsat etilgan rol uchun) ──
function ustamaKoUrish2() {
    var ustunlar = document.querySelectorAll('.ustama-col-2');
    var btn = document.getElementById('ustama-toggle-btn-2');
    var korinadi = ustunlar.length && !ustunlar[0].classList.contains('d-none');
    ustunlar.forEach(function (el) { el.classList.toggle('d-none', korinadi); });
    if (btn) btn.innerHTML = '<i class="bi bi-' + (korinadi ? 'eye-slash' : 'eye') + ' me-1"></i>Ustama';
}

// ── Xodim tayinlash ──────────────────────────────────────────────
function xodimTayin() {
    var xodimId = document.getElementById('xt-xodim').value;
    var sabab   = document.getElementById('xt-sabab').value;
    var errEl   = document.getElementById('xt-xato');
    var btn     = document.getElementById('xt-saqlash');

    errEl.classList.add('d-none');
    if (!xodimId) { errEl.textContent = 'Xodim tanlang'; errEl.classList.remove('d-none'); return; }
    if (sabab.length < 5) { errEl.textContent = 'Sabab kamida 5 harf'; errEl.classList.remove('d-none'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>...';

    fetch("{{ route('transfer.shartnoma.ajax.xodim', $kredit) }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ yangi_xodim_id: xodimId, sabab: sabab })
    })
    .then(r => r.json())
    .then(data => {
        if (data.muvaffaqiyat) {
            window.location.reload();
        } else {
            errEl.textContent = data.xato || 'Xatolik yuz berdi';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Tayinlash';
        }
    })
    .catch(() => { window.location.reload(); });
}

// ── Filial ko'chirish ────────────────────────────────────────────
function filialKochir() {
    var filialId  = document.getElementById('fk-filial').value;
    var sabab     = document.getElementById('fk-sabab').value;
    var tolovlar  = document.getElementById('fk-tolovlar').checked ? 1 : 0;
    var errEl     = document.getElementById('fk-xato');
    var btn       = document.getElementById('fk-saqlash');

    errEl.classList.add('d-none');
    if (!filialId) { errEl.textContent = 'Filial tanlang'; errEl.classList.remove('d-none'); return; }
    if (sabab.length < 10) { errEl.textContent = 'Sabab kamida 10 harf'; errEl.classList.remove('d-none'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>...';

    fetch("{{ route('transfer.shartnoma.ajax.filial', $kredit) }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ yangi_filial_id: filialId, sabab: sabab, tolovlar_yangi_filialda: tolovlar })
    })
    .then(r => r.json())
    .then(data => {
        if (data.muvaffaqiyat) {
            window.location.reload();
        } else {
            errEl.textContent = data.xato || 'Xatolik yuz berdi';
            errEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Ko\'chirish';
        }
    })
    .catch(() => { window.location.reload(); });
}
</script>
<script>
var ttModal = null;
var odModal = null;

function tulovOchirish(id, summa, sana, url) {
    document.getElementById('od-summa').textContent = summa + ' so\'m';
    document.getElementById('od-sana').textContent  = sana;
    document.getElementById('tulov-ochirish-form').action = url;

    if (!odModal) odModal = new bootstrap.Modal(document.getElementById('tulovOchirishModal'));
    odModal.show();
}

function tulovTahrirlash(id, sana, summa, turId, kvitansiya, izoh, url) {
    // Form maydonlarini to'ldirish
    document.getElementById('tt-summa').value  = summa;
    document.getElementById('tt-sana').value   = sana;
    document.getElementById('tt-tur').value    = turId;
    document.getElementById('tt-kv').value     = kvitansiya;
    document.getElementById('tt-izoh').value   = izoh;
    document.getElementById('tt-xato').classList.add('d-none');

    // Form action ni o'rnatish
    document.getElementById('tulov-tahrirlash-form').action = url;

    // Modal ochish
    if (!ttModal) ttModal = new bootstrap.Modal(document.getElementById('tulovTahrirlashModal'));
    ttModal.show();
    setTimeout(() => document.getElementById('tt-summa').focus(), 300);
}

// AJAX submit
document.getElementById('tulov-tahrirlash-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('tt-saqlash');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saqlanmoqda...';

    var formData = new FormData(this);

    fetch(this.action, {
        method: 'POST', // Laravel PUT via _method spoofing
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        if (r.redirected || r.status === 200 || r.status === 302) {
            window.location.reload();
        } else {
            return r.json().then(data => {
                var errEl = document.getElementById('tt-xato');
                if (data.errors) {
                    errEl.textContent = Object.values(data.errors).flat().join(' ');
                } else {
                    errEl.textContent = data.message || 'Xatolik yuz berdi';
                }
                errEl.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Saqlash';
            });
        }
    })
    .catch(() => {
        window.location.reload(); // Xato bo'lsa sahifani yangilaymiz
    });
});
</script>
@endpush

@endsection
