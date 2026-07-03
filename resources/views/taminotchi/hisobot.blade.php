@extends('layouts.app')
@section('title', "Ta'minotchilar hisoboti")
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
<li class="breadcrumb-item active">Hisobot</li>
@endsection

@push('styles')
<style>
.stat-card { background:#fff; border:1px solid #e2e8f4; border-radius:8px; padding:12px 16px; position:relative; overflow:hidden; }
.stat-card::before { content:''; position:absolute; left:0; top:0; bottom:0; width:4px; background:var(--c,#2563eb); border-radius:4px 0 0 4px; }
.stat-card .lbl { font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; font-weight:600; }
.stat-card .val { font-size:1.15rem; font-weight:800; color:var(--c,#1e293b); margin:2px 0; line-height:1.2; }
.stat-card .sub { font-size:.7rem; color:#64748b; }
.stat-card .ico { position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:1.8rem; opacity:.1; }

.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table thead { position:sticky; top:0; z-index:20; }
.bank-table thead tr.head-top th { background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; font-weight:700; font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; padding:8px 10px; border-right:1px solid rgba(255,255,255,.15); border-bottom:1px solid rgba(255,255,255,.1); white-space:nowrap; user-select:none; text-align:center; }
.bank-table thead tr.head-top th.tl { text-align:left; }
.bank-table thead tr.head-group th { background:#1e40af; color:#bfdbfe; font-size:.66rem; font-weight:600; letter-spacing:.04em; text-transform:uppercase; padding:5px 10px; border-right:1px solid rgba(255,255,255,.1); border-bottom:3px solid #1e3a8a; white-space:nowrap; text-align:right; }
.bank-table thead th a { color:inherit; text-decoration:none; display:inline-flex; align-items:center; gap:3px; }
.sort-icon { font-size:.58rem; opacity:.45; }
.sort-icon.on { opacity:1; color:#fde68a; }
.bank-table tbody tr { border-bottom:1px solid #e2e8f4; }
.bank-table tbody tr:hover { background:#eff6ff !important; }
.bank-table tbody tr:nth-child(even) { background:#f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background:#fff; }
.bank-table tbody td { padding:5px 10px; vertical-align:middle; border-right:1px solid #eef0f6; }
.bank-table tfoot { position:sticky; bottom:0; z-index:20; }
.bank-table tfoot tr.tot-uzs td { background:#fffbeb; color:#92400e; font-weight:600; font-size:.78rem; padding:5px 10px; border-top:2px solid #fde68a; }
.bank-table tfoot tr.tot-usd td { background:#f0fdf4; color:#14532d; font-weight:600; font-size:.78rem; padding:5px 10px; border-top:1px solid #86efac; }
.bank-table tfoot tr.tot-grand td { background:linear-gradient(90deg,#1e3a8a,#1e40af); color:#fff; font-weight:700; font-size:.79rem; padding:6px 10px; border-top:2px solid #60a5fa; }
.sep { border-left:2px solid #c7d7f8 !important; }
.bank-table tfoot td.sep { border-left:2px solid rgba(255,255,255,.2) !important; }
.bank-table tfoot tr.tot-uzs td.sep,.bank-table tfoot tr.tot-usd td.sep { border-left:2px solid rgba(0,0,0,.1) !important; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.nd { color:#dc2626; font-weight:700; }
.nc { color:#15803d; font-weight:700; }
.nz { color:#94a3b8; }
.t-name { font-weight:600; color:#1d4ed8; text-decoration:none; }
.t-name:hover { color:#1e40af; text-decoration:underline; }
.badge-uzs { background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 5px; font-size:.64rem; font-weight:700; }
.badge-usd { background:#fef9c3; color:#a16207; border:1px solid #fde047; border-radius:3px; padding:1px 5px; font-size:.64rem; font-weight:700; }
.status-faol   { background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 5px; font-size:.67rem; font-weight:700; }
.status-nofaol { background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; border-radius:3px; padding:1px 5px; font-size:.67rem; font-weight:700; }
.bank-wrap { overflow:auto; max-height:calc(100vh - 330px); border:1px solid #c7d7f8; border-radius:0 0 6px 6px; }
.filter-bar { background:linear-gradient(90deg,#eef3ff,#e8f0fe); border:1px solid #c7d7f8; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 12px; }
.filter-bar .form-control,.filter-bar .form-select { background:#fff; border:1px solid #93c5fd; color:#1e3a8a; font-size:.79rem; height:30px; padding:3px 8px; }
.filter-bar label { color:#3b5fc0; font-size:.72rem; font-weight:600; margin-bottom:2px; }
.col-resizer { position:absolute; right:0; top:0; bottom:0; width:5px; cursor:col-resize; background:transparent; z-index:2; }
.col-resizer:hover { background:rgba(255,255,255,.3); }
.bank-table thead th { position:relative; }
</style>
@endpush

@section('content')
@php
    $n   = fn($v) => number_format((float)$v, 0, '.', ' ');
    $nu  = fn($v) => number_format((float)$v, 2, '.', ' ');
    $toUsd = fn($uzs) => $usdKurs > 0 ? (float)$uzs / (float)$usdKurs : 0;
    $uzsItems = collect($statistika)->where('asosiy_valyuta','UZS');
    $usdItems = collect($statistika)->where('asosiy_valyuta','USD');

    function hUrl2($col) {
        $d = (request('sort')===$col && request('dir')==='asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery(['sort'=>$col,'dir'=>$d]);
    }
    function hIco2($col) {
        if (request('sort')!==$col) return '<span class="sort-icon">⇅</span>';
        return request('dir')==='asc' ? '<span class="sort-icon on">▲</span>' : '<span class="sort-icon on">▼</span>';
    }
@endphp

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-bar-chart-line text-warning" style="font-size:1.1rem"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:.95rem">Ta'minotchilar hisoboti</span>
        </div>
        <div><label>Dan</label><input type="date" name="dan_sana" class="form-control" value="{{ $danSana }}"></div>
        <div><label>Gacha</label><input type="date" name="gacha_sana" class="form-control" value="{{ $gachaSana }}"></div>
        @if(Auth::user()->isAdmin() && $filiallar->count())
        <div><label>Filial</label>
            <select name="filial_id" class="form-select" style="width:160px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                @endforeach
            </select></div>
        @endif
        <div><label>Holat</label>
            <select name="holat" class="form-select" style="width:110px">
                <option value="">Barchasi</option>
                <option value="faol" {{ request('holat')==='faol'?'selected':'' }}>Faol</option>
                <option value="nofaol" {{ request('holat')==='nofaol'?'selected':'' }}>Nofaol</option>
            </select></div>
        <div class="d-flex gap-1 align-items-end">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:30px"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('taminotchi.hisobot') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:30px"><i class="bi bi-x-lg"></i></a>
        </div>
        <div class="ms-auto d-flex align-items-end gap-2">
            <small style="color:#3b5fc0;font-size:.75rem;padding-bottom:4px">
                <i class="bi bi-currency-dollar text-warning me-1"></i>1 USD = <strong>{{ $n($usdKurs) }}</strong> so'm
            </small>
            <a href="{{ request()->fullUrlWithQuery(['format'=>'excel']) }}" class="btn btn-success btn-sm" style="height:30px">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
        </div>
    </form>
</div>

{{-- Tahlil kartalari --}}
@php $oG=$jami['oxiri']; $oColor=$oG>0?'#dc2626':($oG<0?'#16a34a':'#6b7280'); @endphp
<div class="row g-2 my-2">
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#2563eb">
            <div class="lbl">Davr boshi</div>
            <div class="val" style="font-size:.95rem">{{ $n(abs($jami['boshi'])) }}</div>
            <div class="sub">so'm qoldiq</div>
            <i class="bi bi-clock-history ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#0891b2">
            <div class="lbl">Davr kirim</div>
            <div class="val" style="color:#0891b2;font-size:.95rem">{{ $n($jami['kirim']) }}</div>
            <div class="sub">so'm</div>
            <i class="bi bi-box-arrow-in-down ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#16a34a">
            <div class="lbl">Davr to'lov</div>
            <div class="val" style="color:#16a34a;font-size:.95rem">{{ $n($jami['tolov']) }}</div>
            <div class="sub">so'm</div>
            <i class="bi bi-cash-coin ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:{{ $oColor }}">
            <div class="lbl">Davr oxiri</div>
            <div class="val" style="color:{{ $oColor }};font-size:.95rem">{{ $n(abs($oG)) }}</div>
            <div class="sub">{{ $oG>0?'biz qarazdormiz':($oG<0?'ular qarazdor':'Teng') }}</div>
            <i class="bi bi-{{ $oG>0?'exclamation-triangle':'check-circle' }} ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#dc2626">
            <div class="lbl">Biz qarazdor</div>
            <div class="val nd">{{ $jami['qarazdor'] }} ta</div>
            <div class="sub">ta'minotchiga</div>
            <i class="bi bi-person-exclamation ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#16a34a">
            <div class="lbl">Ular qarazdor</div>
            <div class="val nc">{{ $jami['hakdor'] }} ta</div>
            <div class="sub">bizga</div>
            <i class="bi bi-person-check ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#f59e0b">
            <div class="lbl">Ochiq kirimlar</div>
            <div class="val" style="color:#d97706">{{ $jami['ochiq'] }} ta</div>
            <div class="sub">to'lanmagan</div>
            <i class="bi bi-hourglass-split ico"></i>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3 col-xl">
        <div class="stat-card" style="--c:#6366f1">
            <div class="lbl">Jami</div>
            <div class="val" style="color:#6366f1">{{ $jami['jami_soni'] }} ta</div>
            <div class="sub">{{ $jami['teng'] }} tasi teng</div>
            <i class="bi bi-people ico"></i>
        </div>
    </div>
</div>

<div class="bank-wrap shadow-sm">
<table class="bank-table" id="hisobot-tbl">
    <thead>
        <tr class="head-top">
            <th class="tl" rowspan="2" style="width:28px">#</th>
            <th class="tl" rowspan="2" style="min-width:160px"><a href="{{ hUrl2('nomi') }}">{!! hIco2('nomi') !!} Ta'minotchi</a></th>
            <th class="tl" rowspan="2" style="width:95px">Rahbar</th>
            <th class="tl" rowspan="2" style="width:95px">Telefon</th>
            <th rowspan="2" style="width:48px">Val.</th>
            <th rowspan="2" style="width:52px"><a href="{{ hUrl2('holat') }}">{!! hIco2('holat') !!} Holat</a></th>
            <th class="sep" style="width:115px"><a href="{{ hUrl2('boshi_qoldiq') }}">{!! hIco2('boshi_qoldiq') !!} Davr boshi</a></th>
            <th class="sep" colspan="3">Davr ichida</th>
            <th class="sep" style="width:125px"><a href="{{ hUrl2('oxiri_qoldiq') }}">{!! hIco2('oxiri_qoldiq') !!} Davr oxiri</a></th>
        </tr>
        <tr class="head-group">
            <th class="sep num">Qoldiq</th>
            <th class="sep num" style="width:115px"><a href="{{ hUrl2('davr_kirim') }}">{!! hIco2('davr_kirim') !!} Kirim</a></th>
            <th class="num" style="width:115px"><a href="{{ hUrl2('davr_tolov') }}">{!! hIco2('davr_tolov') !!} To'lov</a></th>
            <th class="num" style="width:55px">Soni</th>
            <th class="sep num">Qoldiq</th>
        </tr>
    </thead>
    <tbody>
        @forelse($statistika as $i => $r)
        @php
            $isUsd=$r->asosiy_valyuta==='USD';
            $b=(float)$r->boshi_qoldiq; $k=(float)$r->davr_kirim;
            $t=(float)$r->davr_tolov;   $o=(float)$r->oxiri_qoldiq;
            $bC=$b>0?'nd':($b<0?'nc':'nz');
            $oC=$o>0?'nd':($o<0?'nc':'nz');
        @endphp
        <tr>
            <td class="text-muted text-center" style="font-size:.73rem">{{ $i+1 }}</td>
            <td><a href="{{ route('taminotchi.show', $r->id) }}" class="t-name">{{ $r->nomi }}</a></td>
            <td style="font-size:.73rem;color:#64748b;white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis" title="{{ $r->kontakt_shaxs }}">{{ $r->kontakt_shaxs ?? '—' }}</td>
            <td style="font-size:.73rem;color:#64748b;white-space:nowrap">{{ $r->telefon ?? '—' }}</td>
            <td class="text-center"><span class="{{ $isUsd?'badge-usd':'badge-uzs' }}">{{ $r->asosiy_valyuta }}</span></td>
            <td class="text-center"><span class="{{ $r->holat==='faol'?'status-faol':'status-nofaol' }}">{{ $r->holat==='faol'?'FAOL':'NOF' }}</span></td>
            @if($isUsd)
            <td class="num sep {{ $bC }}" title="{{ $n(abs($b)) }} so'm">${{ $nu($toUsd(abs($b))) }}</td>
            <td class="num sep" style="color:#1d4ed8" title="{{ $n($k) }} so'm">{{ $k>0?'$'.$nu($toUsd($k)):'—' }}</td>
            <td class="num" style="color:#15803d" title="{{ $n($t) }} so'm">{{ $t>0?'$'.$nu($toUsd($t)):'—' }}</td>
            <td class="num text-center" style="color:#94a3b8">{{ $r->kirim_soni?$r->kirim_soni:'—' }}</td>
            <td class="num sep {{ $oC }}" title="{{ $n(abs($o)) }} so'm">${{ $nu($toUsd(abs($o))) }}</td>
            @else
            <td class="num sep {{ $bC }}">{{ $b!=0?$n(abs($b)):'0' }}</td>
            <td class="num sep" style="color:#1d4ed8">{{ $k>0?$n($k):'—' }}</td>
            <td class="num" style="color:#15803d">{{ $t>0?$n($t):'—' }}</td>
            <td class="num text-center" style="color:#94a3b8">{{ $r->kirim_soni?$r->kirim_soni:'—' }}</td>
            <td class="num sep {{ $oC }}">{{ $o!=0?$n(abs($o)):'0' }}</td>
            @endif
        </tr>
        @empty
        <tr><td colspan="12" class="text-center py-5 text-muted">
            <i class="bi bi-truck fs-2 d-block mb-2 opacity-25"></i>Ma'lumot topilmadi
        </td></tr>
        @endforelse
    </tbody>
    @if(count($statistika))
    <tfoot>
        @if($uzsItems->count())
        @php $uzs=['b'=>$uzsItems->sum('boshi_qoldiq'),'k'=>$uzsItems->sum('davr_kirim'),'t'=>$uzsItems->sum('davr_tolov'),'o'=>$uzsItems->sum('oxiri_qoldiq')]; @endphp
        <tr class="tot-uzs">
            <td colspan="6"><span style="font-size:.66rem;text-transform:uppercase;font-weight:700;opacity:.7">UZS</span> {{ $uzsItems->count() }} ta</td>
            <td class="num sep {{ $uzs['b']>0?'nd':($uzs['b']<0?'nc':'nz') }}">{{ $n(abs($uzs['b'])) }}</td>
            <td class="num sep" style="color:#1d4ed8">{{ $uzs['k']>0?$n($uzs['k']):'—' }}</td>
            <td class="num" style="color:#15803d">{{ $uzs['t']>0?$n($uzs['t']):'—' }}</td>
            <td class="num nz">—</td>
            <td class="num sep {{ $uzs['o']>0?'nd':($uzs['o']<0?'nc':'nz') }}">{{ $n(abs($uzs['o'])) }}</td>
        </tr>
        @endif
        @if($usdItems->count())
        @php $usd=['b'=>$usdItems->sum('boshi_qoldiq'),'k'=>$usdItems->sum('davr_kirim'),'t'=>$usdItems->sum('davr_tolov'),'o'=>$usdItems->sum('oxiri_qoldiq')]; @endphp
        <tr class="tot-usd">
            <td colspan="6"><span style="font-size:.66rem;text-transform:uppercase;font-weight:700;opacity:.7">USD</span> {{ $usdItems->count() }} ta · {{ $n($usdKurs) }} so'm</td>
            <td class="num sep {{ $usd['b']>0?'nd':($usd['b']<0?'nc':'nz') }}">${{ $nu($toUsd(abs($usd['b']))) }}</td>
            <td class="num sep" style="color:#1d4ed8">{{ $usd['k']>0?'$'.$nu($toUsd($usd['k'])):'—' }}</td>
            <td class="num" style="color:#15803d">{{ $usd['t']>0?'$'.$nu($toUsd($usd['t'])):'—' }}</td>
            <td class="num nz">—</td>
            <td class="num sep {{ $usd['o']>0?'nd':($usd['o']<0?'nc':'nz') }}">${{ $nu($toUsd(abs($usd['o']))) }}</td>
        </tr>
        @endif
        <tr class="tot-grand">
            <td colspan="6"><i class="bi bi-sigma me-1"></i>Jami {{ count($statistika) }} ta</td>
            <td class="num sep" style="color:{{ $jami['boshi']>0?'#fca5a5':($jami['boshi']<0?'#86efac':'#94a3b8') }}">{{ $n(abs($jami['boshi'])) }}</td>
            <td class="num sep" style="color:#93c5fd">{{ $jami['kirim']>0?$n($jami['kirim']):'—' }}</td>
            <td class="num" style="color:#6ee7b7">{{ $jami['tolov']>0?$n($jami['tolov']):'—' }}</td>
            <td class="num" style="color:#94a3b8">—</td>
            <td class="num sep" style="color:{{ $jami['oxiri']>0?'#fca5a5':($jami['oxiri']<0?'#86efac':'#94a3b8') }};font-size:.84rem">{{ $n(abs($jami['oxiri'])) }}</td>
        </tr>
    </tfoot>
    @endif
</table>
</div>
@endsection

@push('scripts')
<script>
(function(){
    document.querySelectorAll('#hisobot-tbl thead th').forEach(th => {
        const r=document.createElement('div'); r.className='col-resizer'; th.appendChild(r);
        let sx,sw;
        r.addEventListener('mousedown', e => {
            e.preventDefault(); sx=e.clientX; sw=th.offsetWidth;
            const mm=ev=>{th.style.width=th.style.minWidth=Math.max(40,sw+ev.clientX-sx)+'px';};
            const mu=()=>{document.removeEventListener('mousemove',mm);document.removeEventListener('mouseup',mu);};
            document.addEventListener('mousemove',mm); document.addEventListener('mouseup',mu);
        });
    });
})();
</script>
@endpush
