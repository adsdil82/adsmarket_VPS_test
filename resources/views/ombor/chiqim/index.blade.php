@extends('layouts.app')
@section('title','Tovar chiqim')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar chiqim</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#7f1d1d,#b91c1c); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#7f1d1d,#b91c1c);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #fca5a5; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }

.sabab-list { list-style:none; margin:0; padding:0; }
.sabab-list li a {
    display:flex; justify-content:space-between; align-items:center; gap:6px;
    padding:9px 12px; text-decoration:none; color:#334155; border-bottom:1px solid #fee2e2;
    font-size:.84rem; font-weight:600;
}
.sabab-list li.sabab-sub a { padding-left:26px; font-size:.78rem; background:#fffbeb; }
.sabab-list li:last-child a { border-bottom:none; }
.sabab-list li a:hover { background:#fef2f2; }
.sabab-list li a.active { background:linear-gradient(90deg,#dc2626,#7f1d1d); color:#fff; }
.sabab-list li a.active .sabab-summa { color:#fecaca; }
.sabab-summa { font-size:.72rem; color:#64748b; font-family:'Roboto Mono','Courier New',monospace; }
.sabab-soni-badge { font-size:.68rem; font-weight:800; min-width:26px; }

.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #fca5a5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#dc2626,#7f1d1d); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.02em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:28px; }
.bank-table tbody td { padding:5px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#fef8f8; }
.bank-table tbody tr:hover td { background:#fde8e8 !important; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #fca5a5; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

@php
    $joriySabab = request('sabab');
    $qsBase = array_filter(['filial_id'=>request('filial_id'), 'dan_sana'=>$danSana, 'gacha_sana'=>$gachaSana]);
@endphp

{{-- ── Sarlavha ─────────────────────────────────────────────────── --}}
<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-box-arrow-up fs-5"></i>
        <span class="fw-bold">Tovar chiqim</span>
        <span class="badge bg-light text-dark">{{ $isNasiyaBonus ? $bonusRows->total() : $chiqimlar->total() }}</span>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <form method="GET" class="d-flex gap-1 align-items-center flex-wrap">
            <input type="hidden" name="sabab" value="{{ $joriySabab }}">
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
            <a href="{{ route('chiqim.index', array_filter(['filial_id'=>request('filial_id'), 'sabab'=>$joriySabab])) }}"
               class="btn btn-sm btn-light py-1" title="Sana filtrini tozalash">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
        <a href="{{ route('chiqim.create') }}" class="btn btn-sm btn-light py-1 fw-bold">
            <i class="bi bi-plus-lg me-1 text-danger"></i>Yangi chiqim
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- ── Chap panel: chiqim turlari ──────────────────────────────── --}}
    <div class="col-lg-3">
        <div class="bft-section-title mb-0"><span><i class="bi bi-funnel me-1"></i>Chiqim turlari</span></div>
        <div class="bft-wrap mb-3">
            <ul class="sabab-list">
                <li>
                    <a href="{{ route('chiqim.index', $qsBase) }}"
                       class="{{ !$joriySabab ? 'active' : '' }}">
                        <span><i class="bi bi-list-ul me-1"></i>Barchasi</span>
                        <span class="sabab-soni-badge badge bg-secondary">{{ $sababSoni->sum('soni') }}</span>
                    </a>
                </li>
                @foreach($sabablar as $key => $nom)
                <li>
                    <a href="{{ route('chiqim.index', array_merge($qsBase, ['sabab'=>$key])) }}"
                       class="{{ $joriySabab === $key ? 'active' : '' }}">
                        <span>
                            {{ $nom }}
                            <span class="sabab-summa d-block">{{ number_format($sababSoni[$key]->summa ?? 0,0,'.',' ') }} so'm</span>
                        </span>
                        <span class="sabab-soni-badge badge bg-secondary">{{ $sababSoni[$key]->soni ?? 0 }}</span>
                    </a>
                </li>
                @if($key === 'nasiya_sotish')
                <li class="sabab-sub">
                    <a href="{{ route('chiqim.index', array_merge($qsBase, ['sabab'=>'nasiya_bonus'])) }}"
                       class="{{ $joriySabab === 'nasiya_bonus' ? 'active' : '' }}"
                       title="Nasiya shartnomalariga biriktirilgan bonus tovarlar (avtomatik)">
                        <span>
                            <i class="bi bi-gift me-1"></i>Nasiya shartnomalariga biriktirilgan bonus
                            <span class="sabab-summa d-block">{{ number_format($nasiyaBonus->summa ?? 0,0,'.',' ') }} so'm</span>
                        </span>
                        <span class="sabab-soni-badge badge bg-warning text-dark">{{ $nasiyaBonus->soni ?? 0 }}</span>
                    </a>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ── O'ng panel: tanlangan turga mos jadval ──────────────────── --}}
    <div class="col-lg-9">
        <div class="bft-section-title mb-0">
            <span><i class="bi bi-table me-1"></i>
                @if($isNasiyaBonus)
                    Nasiya shartnomalariga biriktirilgan bonus
                @else
                    {{ $joriySabab ? ($sabablar[$joriySabab] ?? $joriySabab) : 'Barcha chiqimlar' }}
                @endif
            </span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark">{{ $isNasiyaBonus ? $bonusRows->total() : $chiqimlar->total() }} ta</span>
                <a href="{{ route('chiqim.excel', array_merge($qsBase, ['sabab'=>$joriySabab])) }}" class="btn btn-sm btn-light py-0 px-2" title="Excelga eksport">
                    <i class="bi bi-file-earmark-excel text-success"></i>
                </a>
            </div>
        </div>

        @if($isNasiyaBonus)
        {{-- ── Nasiya-bonus tovarlar jadvali ────────────────────────── --}}
        <div class="bank-wrap mb-2">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th class="tl">#</th>
                        <th class="tl">Shartnoma</th>
                        <th class="tl">Sana</th>
                        <th class="tl">Tovar nomi</th>
                        <th>Miqdor</th>
                        <th>Narx</th>
                        <th>Jami</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bonusRows as $i => $r)
                    <tr>
                        <td class="tl text-muted">{{ $bonusRows->firstItem() + $i }}</td>
                        <td class="tl">
                            <a href="{{ route('kreditlar.show', $r->reg_kredit_id) }}" class="text-decoration-none fw-semibold" style="color:#7f1d1d">{{ $r->shartnoma_raqam }}</a>
                        </td>
                        <td class="tl text-muted">{{ \Carbon\Carbon::parse($r->boshlanish_sana)->format('d.m.Y') }}</td>
                        <td class="tl">{{ $r->nomi }}</td>
                        <td class="num">{{ $r->soni }}</td>
                        <td class="num">{{ number_format($r->narx,0,'.',' ') }}</td>
                        <td class="num fw-bold" style="color:#b45309">{{ number_format($r->jami_narx,0,'.',' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Bonus tovarlar topilmadi
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bonusRows->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted">{{ $bonusRows->firstItem() }}–{{ $bonusRows->lastItem() }} / {{ $bonusRows->total() }} ta</small>
            {{ $bonusRows->links('pagination::bootstrap-5') }}
        </div>
        @endif

        @else
        {{-- ── Odatdagi chiqimlar jadvali ───────────────────────────── --}}
        <div class="bank-wrap mb-2">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th class="tl">#</th>
                        <th class="tl">Sana</th>
                        <th class="tl">Sabab</th>
                        <th class="tl">Xodim</th>
                        @if(Auth::user()->isAdmin())<th class="tl">Filial</th>@endif
                        <th>Summa</th>
                        <th class="tl">Holat</th>
                        <th style="width:110px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chiqimlar as $c)
                    <tr>
                        <td class="tl text-muted">{{ $c->id }}</td>
                        <td class="tl">{{ $c->sana->format('d.m.Y') }}</td>
                        <td class="tl">
                            @php $sababRanglar = ['nasiya_sotish'=>'primary','naqd_sotish'=>'success','bonus'=>'warning','yoqolgan'=>'danger','brak'=>'dark','boshqa'=>'secondary']; @endphp
                            <span class="badge bg-{{ $sababRanglar[$c->sabab]??'secondary' }}" style="font-size:.68rem">
                                {{ $sabablar[$c->sabab] ?? $c->sabab }}
                            </span>
                        </td>
                        <td class="tl text-muted">{{ $c->xodim?->ism_familiya }}</td>
                        @if(Auth::user()->isAdmin())
                        <td class="tl"><span class="badge bg-dark" style="font-size:.68rem">{{ $c->filial?->kod }}</span></td>
                        @endif
                        <td class="num fw-bold text-danger">{{ number_format($c->umumiy_summa,0,'.',' ') }}</td>
                        <td class="tl">
                            <span class="badge bg-{{ $c->holat==='tasdiqlangan'?'success':'danger' }}" style="font-size:.68rem">{{ $c->holat }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('chiqim.show',$c) }}" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ko'rish">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1" title="Hujjatlar"
                                    onclick="hujjatModalOch('{{ route('chiqim.hujjat.html', [$c, 'yuk_xati']) }}', 'Yuk xati — CHQ-{{ $c->id }}', false)">
                                <i class="bi bi-file-earmark-text"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Chiqimlar topilmadi
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($chiqimlar->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-2">
            <small class="text-muted">{{ $chiqimlar->firstItem() }}–{{ $chiqimlar->lastItem() }} / {{ $chiqimlar->total() }} ta</small>
            {{ $chiqimlar->links('pagination::bootstrap-5') }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
