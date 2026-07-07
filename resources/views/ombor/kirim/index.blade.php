@extends('layouts.app')
@section('title','Tovar kirim')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar kirim</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#14532d,#15803d); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#14532d,#15803d);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #86efac; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }

.bft-stat-wrap { border:1px solid #86efac; border-radius:6px; overflow:hidden; background:#fff; display:flex; flex-wrap:wrap; }
.bft-stat { flex:1 1 150px; text-align:center; padding:10px 6px; border-right:1px solid #dcfce7; }
.bft-stat:last-child { border-right:none; }
.bft-stat .lbl { font-size:.68rem; text-transform:uppercase; letter-spacing:.03em; color:#64748b; font-weight:700; }
.bft-stat .val { font-size:1.15rem; font-weight:800; }

.manba-list { list-style:none; margin:0; padding:0; }
.manba-list li a {
    display:flex; justify-content:space-between; align-items:center; gap:6px;
    padding:9px 12px; text-decoration:none; color:#334155; border-bottom:1px solid #dcfce7;
    font-size:.84rem; font-weight:600;
}
.manba-list li:last-child a { border-bottom:none; }
.manba-list li a:hover { background:#f0fdf4; }
.manba-list li a.active { background:linear-gradient(90deg,#16a34a,#14532d); color:#fff; }
.manba-list li a.active .manba-summa { color:#bbf7d0; }
.manba-summa { font-size:.72rem; color:#64748b; font-family:'Roboto Mono','Courier New',monospace; }
.manba-soni-badge { font-size:.68rem; font-weight:800; min-width:26px; }

.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #86efac; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#16a34a,#14532d); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.02em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:28px; }
.bank-table tbody td { padding:5px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#f0fdf4; }
.bank-table tbody tr:hover td { background:#dcfce7 !important; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #86efac; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

@php
    $qsBase = array_filter(['filial_id'=>request('filial_id'), 'dan_sana'=>$danSana, 'gacha_sana'=>$gachaSana]);
@endphp

{{-- ── Sarlavha ─────────────────────────────────────────────────── --}}
<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-box-arrow-in-down fs-5"></i>
        <span class="fw-bold">Tovar kirim</span>
        <span class="badge bg-light text-dark">{{ $kirimlar->total() }}</span>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <form method="GET" class="d-flex gap-1 align-items-center flex-wrap">
            <input type="hidden" name="taminotchi_id" value="{{ $taminotchiId }}">
            @if(Auth::user()->isAdmin())
            <select name="filial_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width:150px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
            @endif
            <input type="date" name="dan_sana" class="form-control form-control-sm" style="width:140px"
                   value="{{ $danSana }}" onchange="this.form.submit()" title="Sana (dan)">
            <span class="text-white small">—</span>
            <input type="date" name="gacha_sana" class="form-control form-control-sm" style="width:140px"
                   value="{{ $gachaSana }}" onchange="this.form.submit()" title="Sana (gacha)">
            @if($danSana || $gachaSana)
            <a href="{{ route('kirim.index', array_filter(['filial_id'=>request('filial_id'), 'taminotchi_id'=>$taminotchiId])) }}"
               class="btn btn-sm btn-light py-1" title="Sana filtrini tozalash">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
        <a href="{{ route('kirim.create') }}" class="btn btn-sm btn-light py-1 fw-bold">
            <i class="bi bi-plus-lg me-1 text-success"></i>Yangi kirim
        </a>
    </div>
</div>

{{-- ── Statistika kartalari ─────────────────────────────────────── --}}
<div class="bft-stat-wrap mb-3">
    <div class="bft-stat">
        <div class="lbl">Bugungi kirim</div>
        <div class="val text-success">{{ number_format($bugunJami,0,'.',' ') }} so'm</div>
    </div>
    <div class="bft-stat">
        <div class="lbl">Oy davomida</div>
        <div class="val" style="color:#0284c7">{{ number_format($oyJami,0,'.',' ') }} so'm</div>
    </div>
    <div class="bft-stat">
        <div class="lbl">Jami yozuvlar</div>
        <div class="val">{{ number_format($jamiYozuvlar) }} ta</div>
    </div>
</div>

<div class="row g-3">
    {{-- ── Chap panel: ta'minotchilar ──────────────────────────────── --}}
    <div class="col-lg-3">
        <div class="bft-section-title mb-0"><span><i class="bi bi-funnel me-1"></i>Ta'minotchilar</span></div>
        <div class="bft-wrap mb-3">
            <ul class="manba-list">
                <li>
                    <a href="{{ route('kirim.index', $qsBase) }}" class="{{ !$taminotchiId ? 'active' : '' }}">
                        <span><i class="bi bi-list-ul me-1"></i>Barchasi</span>
                        <span class="manba-soni-badge badge bg-secondary">{{ $taminotchiSoni->sum('soni') }}</span>
                    </a>
                </li>
                @foreach($taminotchiSoni as $m)
                <li>
                    <a href="{{ route('kirim.index', array_merge($qsBase, ['taminotchi_id'=>$m->taminotchi_id])) }}"
                       class="{{ (string) $taminotchiId === (string) $m->taminotchi_id ? 'active' : '' }}">
                        <span>
                            {{ $m->nomi }}
                            <span class="manba-summa d-block">{{ number_format($m->summa,0,'.',' ') }} so'm</span>
                        </span>
                        <span class="manba-soni-badge badge bg-secondary">{{ $m->soni }}</span>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ── O'ng panel: tanlangan ta'minotchiga mos kirimlar jadvali ─── --}}
    <div class="col-lg-9">
        <div class="bft-section-title mb-0">
            <span><i class="bi bi-table me-1"></i>
                {{ $taminotchiId ? ($taminotchiSoni->firstWhere('taminotchi_id', $taminotchiId)->nomi ?? 'Ta\'minotchi') : 'Barcha kirimlar' }}
            </span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">{{ $kirimlar->total() }} ta</span>
                <a href="{{ route('kirim.excel', array_merge($qsBase, ['taminotchi_id'=>$taminotchiId])) }}" class="btn btn-sm btn-light py-0 px-2" title="Excelga eksport">
                    <i class="bi bi-file-earmark-excel text-success"></i>
                </a>
            </div>
        </div>
        <div class="bank-wrap mb-2">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th class="tl">#</th>
                        <th class="tl">Sana</th>
                        <th class="tl">Ta'minotchi</th>
                        <th class="tl">Hujjat #</th>
                        <th class="tl">Xodim</th>
                        @if(Auth::user()->isAdmin())<th class="tl">Filial</th>@endif
                        <th>Jami summa</th>
                        <th>To'langan</th>
                        <th>Qoldiq</th>
                        <th class="tl">Holat</th>
                        <th style="width:110px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kirimlar as $k)
                    <tr>
                        <td class="tl text-muted">{{ $k->id }}</td>
                        <td class="tl">{{ $k->kirim_sana->format('d.m.Y') }}</td>
                        <td class="tl">
                            <a href="{{ route('taminotchi.show', $k->taminotchi_id) }}" class="text-decoration-none fw-semibold" style="color:#14532d">{{ $k->taminotchi?->nomi }}</a>
                        </td>
                        <td class="tl text-muted">{{ $k->hujjat_raqam ?: '—' }}</td>
                        <td class="tl text-muted">{{ $k->xodim?->ism_familiya }}</td>
                        @if(Auth::user()->isAdmin())
                        <td class="tl"><span class="badge bg-dark" style="font-size:.68rem">{{ $k->filial?->kod }}</span></td>
                        @endif
                        <td class="num fw-bold text-success">{{ number_format($k->jami_summa,0,'.',' ') }}</td>
                        <td class="num" style="color:#0284c7">{{ number_format($k->tolangan,0,'.',' ') }}</td>
                        <td class="num fw-bold" style="color:{{ $k->qoldiq > 0 ? '#dc2626' : '#16a34a' }}">{{ number_format($k->qoldiq,0,'.',' ') }}</td>
                        <td class="tl">
                            <span class="badge bg-{{ $k->holat_rangi }}" style="font-size:.68rem">{{ $k->holat }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('taminotchi.kirim.edit', [$k->taminotchi_id, $k]) }}" class="btn btn-sm btn-outline-success py-0 px-1" title="Ko'rish/Tahrirlash">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Hujjatlar"
                                    onclick="hujjatModalOch('{{ route('kirim.hujjat.html', [$k, 'kirim_varaqasi']) }}', 'Kirim varaqasi — KIR-{{ $k->id }}', false)">
                                <i class="bi bi-file-earmark-text"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Kirimlar topilmadi
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kirimlar->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted">{{ $kirimlar->firstItem() }}–{{ $kirimlar->lastItem() }} / {{ $kirimlar->total() }} ta</small>
            {{ $kirimlar->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
