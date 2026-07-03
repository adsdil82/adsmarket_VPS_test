@extends('layouts.app')

@section('title', "To'lov qabul qilish")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.show', $kredit) }}">{{ $kredit->shartnoma_raqam }}</a></li>
    <li class="breadcrumb-item active">To'lov</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px 8px 0 0;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bft-stat-wrap { border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }
.bft-stat { text-align:center; padding:8px 4px; border-right:1px solid #e5edfb; }
.bft-stat:last-child { border-right:none; }
.bft-stat .lbl { font-size:.68rem; text-transform:uppercase; letter-spacing:.03em; color:#64748b; font-weight:700; }
.bft-stat .val { font-size:1rem; font-weight:800; }

.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#1e3a8a,#1d4ed8);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }
.bft-wrap-compact { max-width:620px; }
.bft-table { width:100%; margin-bottom:0 !important; font-size:.86rem; }
.bft-table td { padding:9px 12px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:100%; }

.bank-table { border-collapse:collapse; font-size:.78rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.64rem; letter-spacing:.02em; text-transform:uppercase; padding:6px 6px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody tr.row-tolangan td { background:#dcfce7 !important; }
.bank-table tbody tr.row-muddati-otgan td { background:#fee2e2 !important; }
.bank-table tbody tr.row-qisman td { background:#fef9c3 !important; }
.bank-table tbody td { padding:4px 6px; vertical-align:middle; white-space:nowrap; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800;
    padding:6px 6px; border-top:2px solid #60a5fa;
}
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap-sm { overflow:auto; height:420px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }

/* ── To'lov yo'li (asfalt yo'l progress) ── */
.yol-progress-wrap { display:flex; align-items:flex-start; }
.yol-road-wrap { flex:1; }
.yol-road {
    position:relative; height:32px; border-radius:6px; margin-top:38px;
    background-color:#3f3f46;
    background-image: repeating-linear-gradient(135deg, rgba(255,255,255,.04) 0 8px, transparent 8px 16px);
    box-shadow: inset 0 2px 5px rgba(0,0,0,.5), 0 1px 2px rgba(0,0,0,.15);
    border:2px solid #27272a;
}
.yol-start-belgi, .yol-finish-belgi {
    position:absolute; top:50%; transform:translateY(-50%);
    font-size:1rem; z-index:3; text-shadow:0 1px 2px rgba(0,0,0,.4);
}
.yol-start-belgi { left:6px; color:#22c55e; }
.yol-finish-belgi { right:6px; color:#fde68a; }
.yol-markazchiziq {
    position:absolute; top:50%; left:4px; right:4px; height:3px; transform:translateY(-50%);
    background-image: repeating-linear-gradient(90deg, #fde68a 0 14px, transparent 14px 26px);
    opacity:.9; border-radius:2px;
}
.yol-oy-nuqta {
    position:absolute; top:50%; width:10px; height:10px; border-radius:50%;
    background:#94a3b8; border:2px solid #fff; transform:translate(-50%,-50%); z-index:2;
    box-shadow:0 1px 2px rgba(0,0,0,.4);
}
.yol-oy-nuqta.yol-otgan { background:#22c55e; }
.yol-odam {
    position:absolute; top:-38px; transform:translateX(-50%);
    font-size:2.4rem; z-index:4; transition:left .3s;
    text-shadow:0 1px 3px rgba(0,0,0,.3);
}
.yol-odam-bayroq {
    position:absolute; top:-2px; right:-8px; font-size:.85rem; color:#fde68a;
    text-shadow:0 1px 2px rgba(0,0,0,.4);
}
/* Yurish animatsiyasi — oyoq-qo'l tebranishini his qildiruvchi yengil CSS burilish, JS ishlatilmaydi */
.yol-odam-yurish { animation: yol-yurish-anim .6s ease-in-out infinite; transform-origin:bottom center; }
@keyframes yol-yurish-anim {
    0%, 100% { transform: translateX(-50%) rotate(-7deg); }
    50%      { transform: translateX(-50%) rotate(7deg); }
}
/* Finishgacha 2 oy qolganda — "emaklab" (charchagan, egilib) holat animatsiyasi */
.yol-odam-emaklab { top:-26px; font-size:1.9rem; animation: yol-emaklab-anim .9s ease-in-out infinite; transform-origin:bottom center; }
@keyframes yol-emaklab-anim {
    0%, 100% { transform: translateX(-50%) rotate(-72deg) translateY(2px); }
    50%      { transform: translateX(-50%) rotate(-82deg) translateY(-3px); }
}
.yol-keyingi-bayroq {
    position:absolute; top:-18px; transform:translateX(-50%);
    font-size:1rem; color:#f59e0b; z-index:3;
    text-shadow:0 1px 2px rgba(0,0,0,.2);
}
/* Chizg'ich (ruler) — oylarni proporsional ko'rsatuvchi shkala */
.yol-chizgich { position:relative; height:18px; margin-top:0; }
.yol-chizgich-belgi { position:absolute; top:0; transform:translateX(-50%); display:flex; flex-direction:column; align-items:center; }
.yol-chizgich-chiziq { width:1px; height:8px; background:#94a3b8; }
.yol-chizgich-son { font-size:.6rem; color:#475569; font-weight:700; margin-top:2px; white-space:nowrap; }
</style>
@endpush

@section('content')

{{-- ── Shartnoma qisqa ma'lumoti ──────────────────────────────── --}}
<div class="bft-header-card mb-1" style="border-radius:8px">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="fw-bold">{{ $kredit->shartnoma_raqam }}</span>
        <span class="opacity-75">{{ $kredit->mijoz->tolik_ism }}</span>
        <button type="button" class="btn btn-sm btn-light py-0"
                onclick="shartnomaModalOch('{{ route('kreditlar.show', $kredit) }}')">
            <i class="bi bi-eye me-1"></i>Ko'rish
        </button>
        <span class="badge bg-{{ $kredit->holat_rangi }}">{{ $kredit->holatNomi }}</span>
    </div>
    <div class="d-flex gap-3 flex-wrap small">
        <span>Kredit: <strong>{{ number_format($kredit->kredit_summa, 0, '.', ' ') }}</strong></span>
        <span>To'landi: <strong style="color:#86efac">{{ number_format($kredit->tolov_qilingan, 0, '.', ' ') }}</strong></span>
        <span>Qoldiq: <strong style="color:#fca5a5">{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</strong></span>
        <span>Oylik: <strong>{{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }}</strong></span>
        <strong>{{ $kredit->tolov_foizi }}%</strong>
    </div>
</div>
<div class="progress mb-3" style="height:4px;border-radius:0 0 6px 6px">
    <div class="progress-bar bg-success" style="width: {{ $kredit->tolov_foizi }}%"></div>
</div>

{{-- ── Kechikkan qarz ogohlantirishi ──────────────────────────── --}}
@if($kechikkanSoni > 0)
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>{{ $kechikkanSoni }} oy</strong> bo'yicha to'lov kechikkan —
        kechikkan summa: <strong id="kechikkan-summa-matn">{{ number_format($kechikkanSumma, 0, '.', ' ') }} so'm</strong>
    </div>
</div>
@endif

{{-- ── To'lov yo'li (asfalt yo'l ko'rinishidagi progress) ─────── --}}
@php
    $yolGrafik  = $kredit->grafik->filter(fn($g) => $g->tolov_sana !== null)->sortBy('oylik_tartib')->values();
    $yolJamiOy  = max(1, $yolGrafik->count() - 1);

    $bugun = today();
    $haqiqiyTolanganOylar = $yolGrafik->where('holat', 'tolangan')->count();

    // Odamcha — nechta oy to'langan bo'lsa, o'sha oyning belgisida turadi (summaga emas, oy soniga qarab)
    $yolOdamFoiz = $haqiqiyTolanganOylar > 0
        ? (($haqiqiyTolanganOylar - 1) / $yolJamiOy) * 100
        : 0;

    // Sariq bayroqcha — navbatdagi (keyingi) to'lanishi kerak bo'lgan oyning boshini ko'rsatadi
    $yolKeyingi = $yolGrafik->first(fn($g) => $g->holat !== 'tolangan');
    $yolBugungiMaqsadIndex = $yolKeyingi ? $yolGrafik->search(fn($g) => $g->id === $yolKeyingi->id) : null;
    $yolBugungiMaqsad = $yolKeyingi;

    // Odamcha rangi: muddati kelgan oylar soni bilan haqiqatda to'langan oylar sonini solishtirib aniqlanadi
    $muddatiKelganOylar = $yolGrafik->filter(fn($g) => $g->tolov_sana && $g->tolov_sana->lte($bugun))->count();
    $yolOdamHolati = $haqiqiyTolanganOylar < $muddatiKelganOylar ? 'kechikkan'
        : ($haqiqiyTolanganOylar > $muddatiKelganOylar ? 'otib_ketgan' : 'yetib_olgan');
    $yolOdamRang = match($yolOdamHolati) {
        'kechikkan'   => '#dc2626',
        'yetib_olgan' => '#f59e0b',
        'otib_ketgan' => '#16a34a',
    };
    $yolOdamMatni = match($yolOdamHolati) {
        'kechikkan'   => 'Kechikib yuribdi',
        'yetib_olgan' => "Jadvalga yetib oldi",
        'otib_ketgan' => "Jadvaldan o'tib ketdi",
    };

    // Jadvalga yetib olgan yoki kechikkan bo'lsa — odamcha aynan navbatdagi bayroqcha
    // turgan oyda turadi (yetib olgan bo'lsa bayroqni qo'lida ushlab, kechikkan bo'lsa
    // hali yeta olmay o'sha oyda kutib turgan holatda). Faqat oldinlab ketgan holatda
    // odamcha o'zining haqiqiy (bayroqdan oldinroq) o'rnida qoladi.
    if ($yolBugungiMaqsadIndex !== null && $yolBugungiMaqsadIndex !== false && in_array($yolOdamHolati, ['yetib_olgan', 'kechikkan'])) {
        $yolOdamFoiz = $yolJamiOy > 0 ? ($yolBugungiMaqsadIndex / $yolJamiOy) * 100 : 0;
    }

    // Finishgacha necha oy qolganini hisoblab, oxirgi 2 oyda odamchani "emaklab" (charchagan) holatga o'tkazamiz
    $yolQolganOy = max(0, $yolGrafik->count() - $haqiqiyTolanganOylar);
    $yolEmaklaydimi = $yolQolganOy > 0 && $yolQolganOy <= 2;
@endphp
@if($yolGrafik->count() > 1)
<div class="bft-section-title mb-0">
    <span><i class="bi bi-signpost-split me-1"></i>To'lov yo'li</span>
    <span class="badge" style="background:{{ $yolOdamRang }}">{{ $yolOdamMatni }}</span>
</div>
<div class="bft-wrap mb-3">
    <div class="px-3 pt-3 pb-1">
        <div class="yol-progress-wrap">
            <div class="yol-road-wrap">
                <div class="yol-road">
                    <div class="yol-markazchiziq"></div>

                    {{-- START va FINISH belgilari — yo'l ICHIDA, uning ikki chekkasida --}}
                    <div class="yol-start-belgi" title="Boshlanish"><i class="bi bi-flag-fill"></i></div>
                    <div class="yol-finish-belgi" title="Yakun"><i class="bi bi-flag-checkered"></i></div>

                    @foreach($yolGrafik as $i => $g)
                    @php $chapFoiz = $yolJamiOy > 0 ? ($i / $yolJamiOy) * 100 : 0; @endphp
                    <div class="yol-oy-nuqta {{ $g->holat === 'tolangan' ? 'yol-otgan' : '' }}" style="left:{{ $chapFoiz }}%" title="{{ $g->oylik_tartib }}-oy — {{ $g->tolov_sana?->format('d.m.Y') }}"></div>
                    @endforeach

                    @if($yolBugungiMaqsadIndex !== null && $yolBugungiMaqsadIndex !== false && $yolOdamHolati !== 'yetib_olgan')
                    @php $maqsadFoiz = $yolJamiOy > 0 ? ($yolBugungiMaqsadIndex / $yolJamiOy) * 100 : 0; @endphp
                    <div class="yol-keyingi-bayroq" style="left:{{ $maqsadFoiz }}%" title="Navbatdagi to'lanishi kerak bo'lgan oy — {{ $yolBugungiMaqsad->oylik_tartib }}-oy">
                        <i class="bi bi-flag-fill"></i>
                    </div>
                    @endif

                    <div class="yol-odam {{ $yolEmaklaydimi ? 'yol-odam-emaklab' : 'yol-odam-yurish' }}" style="left:{{ $yolOdamFoiz }}%;color:{{ $yolOdamRang }}" title="{{ $yolOdamMatni }} — {{ $haqiqiyTolanganOylar }} oy to'langan{{ $yolEmaklaydimi ? ' (finishga oz qoldi)' : '' }}">
                        <i class="bi {{ $yolEmaklaydimi ? 'bi-person' : 'bi-person-walking' }}"></i>
                        @if($yolOdamHolati === 'yetib_olgan')
                        <i class="bi bi-flag-fill yol-odam-bayroq" title="Bayroqni ushlab turibdi — jadvalga yetib oldi"></i>
                        @endif
                    </div>
                </div>

                {{-- Chizg'ich — oylarni proporsional ko'rsatadigan chiziqchalar --}}
                <div class="yol-chizgich">
                    @foreach($yolGrafik as $i => $g)
                    @php $chapFoiz = $yolJamiOy > 0 ? ($i / $yolJamiOy) * 100 : 0; @endphp
                    <div class="yol-chizgich-belgi" style="left:{{ $chapFoiz }}%">
                        <div class="yol-chizgich-chiziq"></div>
                        <span class="yol-chizgich-son">{{ $g->oylik_tartib }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
{{-- ── To'lov formasi ─────────────────────────────────────────── --}}
<div class="col-lg-5">
<div class="bft-section-title mb-0 bft-wrap-compact"><span><i class="bi bi-cash-coin me-1"></i>To'lov qabul qilish</span></div>
<div class="bft-wrap bft-wrap-compact mb-3">
    <div class="p-3">
        <form method="POST" action="{{ route('kreditlar.tulov.store', $kredit) }}" id="tulov-form">
            @csrf

            <table class="bft-table mb-2">
                <tbody>
                    <tr>
                        <td class="bft-label">Kvitansiya / Sana</td>
                        <td class="bft-wide">
                            <div class="d-flex gap-2">
                                <input type="text" id="kvitansiya_korinish" class="form-control form-control-sm bg-body-secondary fw-bold text-primary" readonly
                                       style="max-width:200px"
                                       value="{{ $kvitansiyaPreview->first() }}">
                                <input type="text" class="form-control form-control-sm bg-body-secondary fw-bold" readonly
                                       style="max-width:190px"
                                       value="{{ now()->format('d.m.Y') }} (bugun)">
                            </div>
                            <div class="form-text small mb-0"><i class="bi bi-shield-lock me-1"></i>Avtomatik beriladi, orqaga sana qo'yib bo'lmaydi</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">To'lov turi <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <select name="tulov_turi_id" id="tulov-turi-select" class="form-select form-select-sm" style="max-width:380px" onchange="kvitansiyaYangila(this)">
                                @foreach($tulovTurlari as $tur)
                                    <option value="{{ $tur->id }}"
                                            data-kvitansiya="{{ $kvitansiyaPreview[$tur->id] }}"
                                            {{ old('tulov_turi_id') == $tur->id ? 'selected' : ($loop->first && !old('tulov_turi_id') ? 'selected' : '') }}>
                                        {{ $tur->nomi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tulov_turi_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Summa <span class="text-danger">*</span></td>
                        <td class="bft-wide">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="max-width:300px">
                                    <input type="text" inputmode="numeric" id="summa_korinish"
                                           class="form-control fw-bold @error('summa') is-invalid @enderror"
                                           value="{{ number_format((float) old('summa', $birinchiOy->tolov_summa ?? $kredit->oylik_tolov_miqdori), 0, '.', ' ') }}"
                                           oninput="summaFormatla(this)" autofocus>
                                    <span class="input-group-text">so'm</span>
                                </div>
                                @if($maxKechikishKuni > 0)
                                <span class="badge bg-danger" title="Eng eski kechikkan oy">
                                    <i class="bi bi-clock-history me-1"></i>{{ $maxKechikishKuni }} kun kechikkan
                                </span>
                                @endif
                            </div>
                            <input type="hidden" name="summa" id="summa"
                                   value="{{ old('summa', $birinchiOy->tolov_summa ?? $kredit->oylik_tolov_miqdori) }}">
                            <div class="form-text small mb-0">Maksimal: {{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }} so'm</div>
                            @error('summa')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                            <div class="d-flex flex-wrap gap-1 mt-2">
                                @if($birinchiOy)
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="summaQoy({{ $birinchiOy->tolov_summa }})">
                                    <i class="bi bi-calendar-check me-1"></i>1 oylik — {{ number_format($birinchiOy->tolov_summa, 0, '.', ' ') }}
                                </button>
                                @endif
                                @if($kechikkanSoni > 0)
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="summaQoy({{ $kechikkanSumma }})">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Kechikkan — {{ number_format($kechikkanSumma, 0, '.', ' ') }}
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="summaQoy({{ $kredit->qoldiq_qarz }})">
                                    <i class="bi bi-flag-fill me-1"></i>To'liq — {{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Izoh</td>
                        <td class="bft-wide">
                            <input type="text" name="izoh" class="form-control form-control-sm" style="max-width:380px" value="{{ old('izoh') }}">
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-sm px-4">
                    <i class="bi bi-check-lg me-1"></i> To'lovni qabul qilish
                </button>
                <a href="{{ route('kreditlar.show', $kredit) }}" class="btn btn-outline-secondary btn-sm">
                    Bekor qilish
                </a>
            </div>
        </form>
    </div>
</div>
</div>

{{-- ── Grafikka nisbatan to'lov nisbati (donut diagramma) ─────── --}}
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center">
    <div class="position-relative" style="width:180px;height:180px">
        <canvas id="tolov-nisbat-chart" width="180" height="180"></canvas>
        <div class="position-absolute top-50 start-50 translate-middle text-center">
            <div class="fw-bold" style="font-size:1.3rem;color:#2563eb">{{ $diagrammaFoiz['vaqtida'] + $diagrammaFoiz['kechikib'] }}%</div>
            <div class="text-muted" style="font-size:.66rem">to'langan</div>
        </div>
    </div>
    <div class="mt-3 small" style="width:100%">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span><span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:#2563eb"></span>Vaqtida to'landi</span>
            <strong>{{ $diagrammaFoiz['vaqtida'] }}%</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span><span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:#f59e0b"></span>Kechikib to'landi</span>
            <strong>{{ $diagrammaFoiz['kechikib'] }}%</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span><span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:#dc2626"></span>Qoldi</span>
            <strong>{{ $diagrammaFoiz['qoldi'] }}%</strong>
        </div>
    </div>
</div>

{{-- ── To'lov grafigi (TO'LIQ) ───────────────────────────────── --}}
<div class="col-lg-5">
<div class="bft-section-title mb-0">
    <span><i class="bi bi-calendar3 me-1"></i>To'lov grafigi ({{ $kredit->grafik->count() }})</span>
    @if($ustamaKorishMumkin)
    <button type="button" class="btn btn-sm btn-light py-0 px-2" id="ustama-toggle-btn"
            onclick="ustamaKoUrish()" title="Ustama ustunini ko'rsatish/yashirish">
        <i class="bi bi-eye-slash"></i>
    </button>
    @endif
</div>
<div class="bank-wrap-sm mb-3">
    @if($kredit->grafik->isEmpty())
    <div class="text-center text-muted py-5 small">
        <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
        Grafik mavjud emas
    </div>
    @else
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Oy</th>
                <th class="tl">Sana</th>
                <th>{{ $ustamaKorishMumkin ? 'Tan summa' : 'Summa' }}</th>
                @if($ustamaKorishMumkin)
                <th class="ustama-col d-none">Ustama</th>
                @endif
                <th>Qoldiq</th>
                <th class="tl">Holat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kredit->grafik as $g)
            @php
                $tanSumma = $ustamaKorishMumkin
                    ? ($g->tolov_summa ?? 0) - ($g->ustama_summa ?? 0)
                    : ($g->tolov_summa ?? 0);
                $rowKlass = match($g->holat) {
                    'tolangan' => 'row-tolangan',
                    'muddati_otgan' => 'row-muddati-otgan',
                    'qisman' => 'row-qisman',
                    default => '',
                };
            @endphp
            <tr class="{{ $rowKlass }}">
                <td class="tl fw-medium">
                    {{ $g->oylik_tartib }}-oy
                    @if($g->holat === 'muddati_otgan')
                        <i class="bi bi-exclamation-circle-fill text-danger ms-1" title="{{ $g->kechikish_kunlari }} kun kechikkan"></i>
                    @endif
                </td>
                <td class="tl text-muted">{{ $g->tolov_sana?->format('d.m.Y') }}</td>
                <td class="num">{{ number_format($tanSumma, 0, '.', ' ') }}</td>
                @if($ustamaKorishMumkin)
                <td class="num text-muted ustama-col d-none">{{ number_format($g->ustama_summa ?? 0, 0, '.', ' ') }}</td>
                @endif
                <td class="num fw-medium">{{ number_format($g->qoldiq_suma ?? 0, 0, '.', ' ') }}</td>
                <td class="tl">
                    @if($g->holat === 'tolangan')
                        <span class="badge bg-success">To'langan</span>
                    @elseif($g->holat === 'muddati_otgan')
                        <span class="badge bg-danger">Muddati o'tgan</span>
                    @elseif($g->holat === 'qisman')
                        <span class="badge bg-warning text-dark">Qisman</span>
                    @else
                        <span class="badge bg-secondary">Kutilmoqda</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
</div>
</div>

{{-- ── To'lov tarixi (kvitansiyalar) ─────────────────────────── --}}
<div class="bft-section-title mb-0">
    <span><i class="bi bi-receipt me-1"></i>To'lov tarixi (kvitansiyalar)</span>
    <span class="badge bg-light text-primary">{{ $kredit->tulovlar->count() }} ta</span>
</div>
<div class="bank-wrap mb-3" style="max-height:360px">
    @if($kredit->tulovlar->isEmpty())
    <div class="text-center text-muted py-4 small">
        <i class="bi bi-receipt fs-3 d-block mb-2 opacity-25"></i>
        Hali to'lov qilinmagan
    </div>
    @else
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Sana</th>
                <th class="tl">Turi</th>
                <th>Summa</th>
                <th class="tl">Kvitansiya #</th>
                <th class="tl">Kassir</th>
                <th class="tl">Izoh</th>
                <th style="width:60px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($kredit->tulovlar as $t)
            <tr>
                <td class="tl text-muted">{{ $t->tolov_sana?->format('d.m.Y') ?? '—' }}</td>
                <td class="tl">{{ $t->tulovTuri->nomi ?? '—' }}</td>
                <td class="num fw-bold text-success">{{ number_format($t->summa, 0, '.', ' ') }}</td>
                <td class="tl text-muted">{{ $t->kvitansiya_raqam ?? '—' }}</td>
                <td class="tl text-muted">{{ $t->xodim->ism_familiya ?? '—' }}</td>
                <td class="tl text-muted">{{ $t->izoh ?? '—' }}</td>
                <td class="text-center">
                    <a href="{{ route('kreditlar.tulov.kvitansiya', [$kredit, $t]) }}" target="_blank"
                       class="btn btn-sm btn-outline-primary py-0 px-1" title="Kvitansiyani chop etish">
                        <i class="bi bi-printer"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="tl" colspan="2">Jami:</td>
                <td class="num">{{ number_format($kredit->tulovlar->sum('summa'), 0, '.', ' ') }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>

{{-- Modal: Shartnomani ko'rish (iframe) --}}
<div class="modal fade" id="shartnoma-modal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height:85vh">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-file-earmark-text me-1"></i>Shartnoma — {{ $kredit->shartnoma_raqam }}</h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('kreditlar.show', $kredit) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Yangi oynada
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <iframe id="shartnoma-iframe" src="" style="width:100%;height:100%;border:none"></iframe>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Yopish
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
// ── Grafikka nisbatan to'lov nisbati — donut diagramma ──
new Chart(document.getElementById('tolov-nisbat-chart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ["Vaqtida to'landi", "Kechikib to'landi", 'Qoldi'],
        datasets: [{
            data: [{{ $diagrammaFoiz['vaqtida'] }}, {{ $diagrammaFoiz['kechikib'] }}, {{ $diagrammaFoiz['qoldi'] }}],
            backgroundColor: ['#2563eb', '#f59e0b', '#dc2626'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.parsed + '%' } }
        }
    }
});

// ── Summa maydoni — minglik ajratuvchi bilan ko'rsatish, xom qiymatni hidden'ga yozish ──
function summaFormatla(el) {
    var raqam = el.value.replace(/[^\d]/g, '');
    document.getElementById('summa').value = raqam ? parseInt(raqam, 10) : '';
    var pos = el.selectionStart;
    var oldLen = el.value.length;
    el.value = raqam ? Number(raqam).toLocaleString('ru-RU').replace(/,/g, ' ') : '';
    var newLen = el.value.length;
    el.selectionStart = el.selectionEnd = Math.max(0, pos + (newLen - oldLen));
}

function summaQoy(qiymat) {
    var xom = Math.round(qiymat * 100) / 100;
    document.getElementById('summa').value = xom;
    document.getElementById('summa_korinish').value = Number(xom).toLocaleString('ru-RU').replace(/,/g, ' ');
}

// ── To'lov turi tanlanganda — shu turga mos "keyingi kvitansiya raqami"ni ko'rsatish ──
function kvitansiyaYangila(select) {
    var opt = select.options[select.selectedIndex];
    document.getElementById('kvitansiya_korinish').value = opt.dataset.kvitansiya;
}

// ── "Ustama" ustunini ko'rsatish/yashirish (faqat ruxsat etilgan rol uchun) ──
function ustamaKoUrish() {
    var ustunlar = document.querySelectorAll('.ustama-col');
    var btn = document.getElementById('ustama-toggle-btn');
    var korinadi = ustunlar.length && !ustunlar[0].classList.contains('d-none');
    ustunlar.forEach(function (el) { el.classList.toggle('d-none', korinadi); });
    if (btn) btn.innerHTML = '<i class="bi bi-' + (korinadi ? 'eye-slash' : 'eye') + '"></i>';
}

// ── Shartnomani modal ichida (iframe) ko'rish ──
function shartnomaModalOch(url) {
    var sep = url.includes('?') ? '&' : '?';
    document.getElementById('shartnoma-iframe').src = url + sep + 'embed=1';
    new bootstrap.Modal(document.getElementById('shartnoma-modal')).show();
}
</script>
@endpush
@endsection
