@extends('layouts.app')
@section('title', "Ta'minotchilar")
@section('breadcrumb')
    <li class="breadcrumb-item active">Ta'minotchilar</li>
@endsection

@push('styles')
<style>
/* ── Wrapper: scroll faqat jadval ichida, thead/tfoot yopishib turadi ── */
.bank-wrap {
    border: 1px solid #c7d7f8;
    border-radius: 0 0 6px 6px;
    overflow: auto;
    /* Viewport balandligidan header(~120px) va filter(~60px) va footer(~44px) chiqariladi */
    max-height: calc(100vh - 240px);
}

.bank-table { border-collapse: collapse; font-size: .82rem; width:100%; }

/* Sticky thead */
.bank-table thead { position: sticky; top: 0; z-index: 20; }

.bank-table thead tr.head-top th {
    background: linear-gradient(180deg,#2563eb 0%,#1d4ed8 100%);
    color: #fff;
    font-weight: 700;
    font-size: .7rem;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 8px 10px;
    border-right: 1px solid rgba(255,255,255,.15);
    border-bottom: 1px solid rgba(255,255,255,.1);
    white-space: nowrap;
    user-select: none;
    text-align: center;
}
.bank-table thead tr.head-top th.th-left { text-align: left; }
.bank-table thead tr.head-group th {
    background: #1e40af;
    color: #bfdbfe;
    font-size: .66rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 5px 10px;
    border-right: 1px solid rgba(255,255,255,.1);
    border-bottom: 3px solid #1e3a8a;
    white-space: nowrap;
    text-align: right;
}
.bank-table thead tr.head-group th.th-left { text-align: left; }
.bank-table thead th a { color: inherit; text-decoration: none; display:inline-flex; align-items:center; gap:3px; }
.bank-table thead th a:hover { opacity: .8; }
.sort-icon { font-size: .58rem; opacity: .45; }
.sort-icon.on { opacity: 1; color: #fde68a; }

.bank-table tbody tr { border-bottom: 1px solid #e2e8f4; }
.bank-table tbody tr:hover { background: #eff6ff !important; }
.bank-table tbody tr:nth-child(even) { background: #f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background: #ffffff; }
.bank-table tbody td { padding: 5px 10px; vertical-align: middle; border-right: 1px solid #eef0f6; }
.bank-table tbody td:last-child { border-right: none; }

/* Sticky tfoot */
.bank-table tfoot { position: sticky; bottom: 0; z-index: 20; }
.bank-table tfoot tr.tot-uzs td {
    background: #fffbeb;
    color: #92400e;
    font-weight: 600;
    font-size: .78rem;
    padding: 5px 10px;
    border-top: 2px solid #fde68a;
}
.bank-table tfoot tr.tot-usd td {
    background: #f0fdf4;
    color: #14532d;
    font-weight: 600;
    font-size: .78rem;
    padding: 5px 10px;
    border-top: 1px solid #86efac;
}
.bank-table tfoot tr.tot-grand td {
    background: linear-gradient(90deg,#1e3a8a 0%,#1e40af 100%);
    color: #fff;
    font-weight: 700;
    font-size: .79rem;
    padding: 6px 10px;
    border-top: 2px solid #60a5fa;
}
.tot-label { font-size: .68rem; letter-spacing: .04em; text-transform: uppercase; opacity: .75; font-weight: 600; margin-right: 4px; }
.tot-debt  { color: #dc2626; font-weight: 800; }
.tot-cred  { color: #16a34a; font-weight: 800; }
.tot-zero  { color: #6b7280; }
.bank-table tfoot td.sep { border-left: 2px solid rgba(0,0,0,.08) !important; }
.bank-table tfoot tr.tot-grand td.sep { border-left: 2px solid rgba(255,255,255,.2) !important; }

/* Ustun guruhlari ajratuvchisi */
.bank-table td.sep, .bank-table th.sep { border-left: 2px solid #c7d7f8 !important; }
.bank-table tfoot td.sep { border-left: 2px solid rgba(255,255,255,.2) !important; }

.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.num-red  { color:#dc2626; font-weight:700; }
.num-grn  { color:#15803d; font-weight:700; }
.num-grey { color:#6b7280; }
.num-dark { color:#1e293b; }
.num-blue { color:#1d4ed8; }

.t-name { font-weight:600; color:#1d4ed8; text-decoration:none; }
.t-name:hover { color:#1e40af; text-decoration:underline; }

.badge-uzs { background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 5px; font-size:.64rem; font-weight:700; }
.badge-usd { background:#fef9c3; color:#a16207; border:1px solid #fde047; border-radius:3px; padding:1px 5px; font-size:.64rem; font-weight:700; }
.status-faol   { background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }
.status-nofaol { background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }

.filter-bar {
    background: linear-gradient(90deg,#eef3ff 0%,#e8f0fe 100%);
    border: 1px solid #c7d7f8;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
    padding: 7px 12px;
}
.filter-bar .form-control, .filter-bar .form-select {
    background:#fff; border:1px solid #93c5fd; color:#1e3a8a; font-size:.79rem; height:30px; padding:3px 8px;
}
.filter-bar .form-control::placeholder { color:#93c5fd; }
.filter-bar .form-control:focus, .filter-bar .form-select:focus { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.18); }
.filter-bar label { color:#3b5fc0; font-size:.72rem; font-weight:600; margin-bottom:2px; }

.action-btn { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:3px; border:1px solid; font-size:.72rem; transition:all .12s; }
.action-btn:hover { opacity:.75; transform:scale(1.1); }

/* Ustun kengaytirish qo'lchasi */
.col-resizer {
    position: absolute; right: 0; top: 0; bottom: 0;
    width: 5px; cursor: col-resize;
    background: transparent;
    z-index: 2;
}
.col-resizer:hover, .col-resizer.resizing { background: rgba(255,255,255,.35); }
.bank-table thead th { position: relative; }

/* Jadval to'liq kenglik, minimal padding konteyner */
.bank-wrap { overflow: auto; max-height: calc(100vh - 160px); border: 1px solid #c7d7f8; border-radius: 0 0 6px 6px; }
</style>

@push('scripts')
<script>
(function(){
    const table = document.querySelector('.bank-table');
    if (!table) return;

    // Har bir th ga resize qo'lchasini qo'shamiz
    table.querySelectorAll('thead th').forEach(th => {
        // rowspan=2 bo'lgan birinchi qatordagi thlar
        const r = document.createElement('div');
        r.className = 'col-resizer';
        th.appendChild(r);

        let startX, startW, col;

        r.addEventListener('mousedown', e => {
            e.preventDefault();
            startX = e.clientX;
            // Agar th ustida colIndex aniqlash uchun th ni ishlatamiz
            col = th;
            startW = col.offsetWidth;
            r.classList.add('resizing');

            const onMove = ev => {
                const newW = Math.max(40, startW + ev.clientX - startX);
                col.style.width = newW + 'px';
                col.style.minWidth = newW + 'px';
            };
            const onUp = () => {
                r.classList.remove('resizing');
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    });
})();
</script>
@endpush
@endpush

@section('content')
@php
    $sort    = request('sort','nomi');
    $dir     = request('dir','asc');
    function sUrl($col) {
        $d = (request('sort') === $col && request('dir') === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort'=>$col,'dir'=>$d,'page'=>1]);
    }
    function sIco($col) {
        if (request('sort') !== $col) return '<span class="sort-icon">⇅</span>';
        return request('dir') === 'asc'
            ? '<span class="sort-icon on">▲</span>'
            : '<span class="sort-icon on">▼</span>';
    }
    $n = fn($v) => number_format((float)$v, 0, '.', ' ');
    $nu = fn($v) => number_format((float)$v, 2, '.', ' ');

    // USD konversiya: UZS summani USD'ga aylantirish
    $toUsd = fn($uzs) => $usdKurs > 0 ? (float)$uzs / (float)$usdKurs : 0;

    // UZS ta'minotchilar jami (UZS da)
    $uzsItems   = $taminotchilar->where('asosiy_valyuta','UZS');
    $uzsBoshi   = $uzsItems->sum('boshi_qoldiq');
    $uzsKirim   = $uzsItems->sum('davr_kirim');
    $uzsTolov   = $uzsItems->sum('davr_tolov');
    $uzsOxiri   = $uzsItems->sum('oxiri_qoldiq');

    // USD ta'minotchilar jami (UZS da saqlanadi, USD'ga aylantiramiz)
    $usdItems   = $taminotchilar->where('asosiy_valyuta','USD');
    $usdBoshiUzs= $usdItems->sum('boshi_qoldiq');
    $usdKirimUzs= $usdItems->sum('davr_kirim');
    $usdTolovUzs= $usdItems->sum('davr_tolov');
    $usdOxiriUzs= $usdItems->sum('oxiri_qoldiq');

    // Grand total (UZS)
    $totBoshi  = $taminotchilar->sum('boshi_qoldiq');
    $totKirim  = $taminotchilar->sum('davr_kirim');
    $totTolov  = $taminotchilar->sum('davr_tolov');
    $totOxiri  = $taminotchilar->sum('oxiri_qoldiq');
@endphp

<div class="filter-bar">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        {{-- Chap: sarlavha --}}
        <div class="d-flex align-items-center gap-2 me-2" style="white-space:nowrap">
            <i class="bi bi-truck text-warning" style="font-size:1.1rem"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:.95rem">Ta'minotchilar</span>
            <span class="badge bg-secondary">{{ $taminotchilar->total() }}</span>
        </div>

        <div>
            <label>Qidiruv</label>
            <input type="search" name="qidiruv" class="form-control" style="width:160px"
                   placeholder="Nomi yoki telefon..." value="{{ request('qidiruv') }}">
        </div>
        <div>
            <label>Davr boshi</label>
            <input type="date" name="dan_sana" class="form-control" value="{{ $danSana }}">
        </div>
        <div>
            <label>Davr oxiri</label>
            <input type="date" name="gacha_sana" class="form-control" value="{{ $gachaSana }}">
        </div>
        <div>
            <label>Holat</label>
            <select name="holat" class="form-select" style="width:110px">
                <option value="">Barchasi</option>
                <option value="faol"   {{ request('holat')==='faol'   ?'selected':'' }}>Faol</option>
                <option value="nofaol" {{ request('holat')==='nofaol' ?'selected':'' }}>Nofaol</option>
            </select>
        </div>
        <div class="col-auto">
            <label>Ko'rsatish</label>
            <select name="per_page" class="form-select" style="width:80px" onchange="this.form.submit()">
                @foreach([20,30,40,50] as $pp)
                <option value="{{ $pp }}" {{ request('per_page', 30) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-1 align-items-end">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:30px">
                <i class="bi bi-search me-1"></i>Filter
            </button>
            <a href="{{ route('taminotchi.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:30px" title="Tozalash">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>

        {{-- O'ng: kurs va Yangi ta'minotchi --}}
        <div class="ms-auto d-flex align-items-end gap-2" style="white-space:nowrap">
            <small style="color:#3b5fc0;font-size:.75rem;padding-bottom:4px">
                <i class="bi bi-currency-dollar text-warning me-1"></i>
                1 USD = <strong>{{ number_format($usdKurs, 0, '.', ' ') }}</strong> so'm
            </small>
            @if(Auth::user()->isMenejerYoki())
            <a href="{{ route('taminotchi.create') }}" class="btn btn-warning btn-sm" style="height:30px;white-space:nowrap">
                <i class="bi bi-plus-lg me-1"></i>Yangi
            </a>
            @endif
        </div>

        @foreach(['sort','dir'] as $p) @if(request($p))<input type="hidden" name="{{ $p }}" value="{{ request($p) }}">@endif @endforeach
    </form>
</div>

<div class="bank-wrap shadow-sm">
<table class="bank-table">
    <thead>
        <tr class="head-top">
            <th class="th-left" rowspan="2" style="min-width:180px">
                <a href="{{ sUrl('nomi') }}">{!! sIco('nomi') !!} Ta'minotchi</a>
            </th>
            <th class="th-left" rowspan="2" style="width:110px">Rahbar</th>
            <th class="th-left" rowspan="2" style="width:110px">Telefon</th>
            <th rowspan="2" style="width:55px">Val.</th>
            <th rowspan="2" style="width:60px">
                <a href="{{ sUrl('holat') }}">{!! sIco('holat') !!} Holat</a>
            </th>
            <th class="sep" colspan="1" style="width:130px">
                <a href="{{ sUrl('boshi_qoldiq') }}">{!! sIco('boshi_qoldiq') !!} Davr boshi</a>
            </th>
            <th class="sep" colspan="2">Davr ichida</th>
            <th class="sep" colspan="1" style="width:140px">
                <a href="{{ sUrl('oxiri_qoldiq') }}">{!! sIco('oxiri_qoldiq') !!} Davr oxiri</a>
            </th>
            <th rowspan="2" style="width:75px"></th>
        </tr>
        <tr class="head-group">
            <th class="sep num">Qoldiq</th>
            <th class="sep num" style="width:130px">
                <a href="{{ sUrl('davr_kirim') }}">{!! sIco('davr_kirim') !!} Kirim</a>
            </th>
            <th class="num" style="width:130px">
                <a href="{{ sUrl('davr_tolov') }}">{!! sIco('davr_tolov') !!} To'lov</a>
            </th>
            <th class="sep num">Qoldiq</th>
        </tr>
    </thead>
    <tbody>
        @forelse($taminotchilar as $t)
        @php
            $isUsd = $t->asosiy_valyuta === 'USD';
            $boshi = (float)$t->boshi_qoldiq;
            $kirim = (float)$t->davr_kirim;
            $tolov = (float)$t->davr_tolov;
            $oxiri = (float)$t->oxiri_qoldiq;
            $bClass = $boshi > 0 ? 'num-red' : ($boshi < 0 ? 'num-grn' : 'num-grey');
            $oClass = $oxiri > 0 ? 'num-red' : ($oxiri < 0 ? 'num-grn' : 'num-grey');
        @endphp
        <tr>
            <td>
                <a href="{{ route('taminotchi.show', $t) }}" class="t-name">{{ $t->nomi }}</a>
            </td>
            <td style="font-size:.75rem;color:#64748b;white-space:nowrap;max-width:120px;overflow:hidden;text-overflow:ellipsis" title="{{ $t->kontakt_shaxs }}">{{ $t->kontakt_shaxs ?? '—' }}</td>
            <td style="font-size:.75rem; color:#64748b; white-space:nowrap">{{ $t->telefon ?? '—' }}</td>
            <td class="text-center">
                <span class="{{ $isUsd ? 'badge-usd' : 'badge-uzs' }}">{{ $t->asosiy_valyuta }}</span>
            </td>
            <td class="text-center">
                <span class="{{ $t->holat==='faol' ? 'status-faol' : 'status-nofaol' }}">
                    {{ $t->holat==='faol' ? 'FAOL' : 'NOFAOL' }}
                </span>
            </td>
            @if($isUsd)
            <td class="num sep {{ $bClass }}" title="{{ $n(abs($boshi)) }} so'm">
                {{ $boshi != 0 ? '$'.$nu($toUsd(abs($boshi))) : '0' }}
            </td>
            <td class="num sep num-blue" title="{{ $n($kirim) }} so'm">
                {{ $kirim > 0 ? '$'.$nu($toUsd($kirim)) : '—' }}
            </td>
            <td class="num num-dark" title="{{ $n($tolov) }} so'm">
                {{ $tolov > 0 ? '$'.$nu($toUsd($tolov)) : '—' }}
            </td>
            <td class="num sep {{ $oClass }}" title="{{ $n(abs($oxiri)) }} so'm">
                {{ $oxiri != 0 ? '$'.$nu($toUsd(abs($oxiri))) : '0' }}
            </td>
            @else
            <td class="num sep {{ $bClass }}">{{ $boshi != 0 ? $n(abs($boshi)) : '0' }}</td>
            <td class="num sep num-blue">{{ $kirim > 0 ? $n($kirim) : '—' }}</td>
            <td class="num num-dark">{{ $tolov > 0 ? $n($tolov) : '—' }}</td>
            <td class="num sep {{ $oClass }}">{{ $oxiri != 0 ? $n(abs($oxiri)) : '0' }}</td>
            @endif
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('taminotchi.show', $t) }}"
                       class="action-btn text-primary border-primary" title="Ko'rish"><i class="bi bi-eye"></i></a>
                    @if(Auth::user()->isMenejerYoki())
                    <a href="{{ route('taminotchi.edit', $t) }}"
                       class="action-btn text-warning border-warning" title="Tahrirlash"><i class="bi bi-pencil"></i></a>
                    @endif
                    <a href="{{ route('taminotchi.akt_sverka', $t) }}"
                       class="action-btn text-info border-info" title="Akt sverka"><i class="bi bi-file-earmark-text"></i></a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center py-5 text-muted">
                <i class="bi bi-truck fs-2 d-block mb-2 opacity-25"></i>Ta'minotchilar topilmadi
            </td>
        </tr>
        @endforelse
    </tbody>
    @if($taminotchilar->count())
    <tfoot>
        @if($uzsItems->count())
        @php
            $bC = $uzsBoshi > 0 ? 'tot-debt' : ($uzsBoshi < 0 ? 'tot-cred' : 'tot-zero');
            $oC = $uzsOxiri > 0 ? 'tot-debt' : ($uzsOxiri < 0 ? 'tot-cred' : 'tot-zero');
        @endphp
        <tr class="tot-uzs">
            <td colspan="5">
                <span class="tot-label">UZS</span>
                {{ $uzsItems->count() }} ta ta'minotchi
            </td>
            <td class="num sep {{ $bC }}">{{ $n(abs($uzsBoshi)) }}</td>
            <td class="num sep" style="color:#1d4ed8">{{ $uzsKirim > 0 ? $n($uzsKirim) : '—' }}</td>
            <td class="num" style="color:#15803d">{{ $uzsTolov > 0 ? $n($uzsTolov) : '—' }}</td>
            <td class="num sep {{ $oC }}">{{ $n(abs($uzsOxiri)) }}</td>
            <td></td>
        </tr>
        @endif

        @if($usdItems->count())
        @php
            $bCu = $usdBoshiUzs > 0 ? 'tot-debt' : ($usdBoshiUzs < 0 ? 'tot-cred' : 'tot-zero');
            $oCu = $usdOxiriUzs > 0 ? 'tot-debt' : ($usdOxiriUzs < 0 ? 'tot-cred' : 'tot-zero');
        @endphp
        <tr class="tot-usd">
            <td colspan="5">
                <span class="tot-label">USD</span>
                {{ $usdItems->count() }} ta ta'minotchi
                <small style="opacity:.6;font-size:.66rem"> · kurs {{ $n($usdKurs) }} so'm</small>
            </td>
            <td class="num sep {{ $bCu }}">
                ${{ $nu($toUsd(abs($usdBoshiUzs))) }}
            </td>
            <td class="num sep" style="color:#1d4ed8">
                {{ $usdKirimUzs > 0 ? '$'.$nu($toUsd($usdKirimUzs)) : '—' }}
            </td>
            <td class="num" style="color:#15803d">
                {{ $usdTolovUzs > 0 ? '$'.$nu($toUsd($usdTolovUzs)) : '—' }}
            </td>
            <td class="num sep {{ $oCu }}">${{ $nu($toUsd(abs($usdOxiriUzs))) }}</td>
            <td></td>
        </tr>
        @endif

        <tr class="tot-grand">
            <td colspan="5">
                <i class="bi bi-sigma me-1"></i>
                Jami {{ $taminotchilar->total() }} ta
                @if($taminotchilar->hasPages())<small style="opacity:.7">(sahifada {{ $taminotchilar->count() }} ta)</small>@endif
            </td>
            @php
                $bgB = $totBoshi > 0 ? '#fca5a5' : ($totBoshi < 0 ? '#86efac' : '#94a3b8');
                $bgO = $totOxiri > 0 ? '#fca5a5' : ($totOxiri < 0 ? '#86efac' : '#94a3b8');
            @endphp
            <td class="num sep" style="color:{{ $bgB }}; font-size:.82rem">{{ $n(abs($totBoshi)) }}</td>
            <td class="num sep" style="color:#93c5fd; font-size:.82rem">{{ $totKirim > 0 ? $n($totKirim) : '—' }}</td>
            <td class="num" style="color:#6ee7b7; font-size:.82rem">{{ $totTolov > 0 ? $n($totTolov) : '—' }}</td>
            <td class="num sep" style="color:{{ $bgO }}; font-size:.82rem">{{ $n(abs($totOxiri)) }}</td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>
</div>

@if($taminotchilar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $taminotchilar->firstItem() }}–{{ $taminotchilar->lastItem() }} / {{ $taminotchilar->total() }} ta</small>
    {{ $taminotchilar->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection
