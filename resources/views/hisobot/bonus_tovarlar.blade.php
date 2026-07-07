@extends('layouts.app')
@section('title', 'Bonusga berilgan tovarlar')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Bonusga berilgan tovarlar</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#1e3a8a,#1d4ed8);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; }

.bft-stat-wrap { border:1px solid #93c5fd; border-radius:6px; overflow:hidden; background:#fff; display:flex; flex-wrap:wrap; }
.bft-stat { flex:1 1 150px; text-align:center; padding:10px 6px; border-right:1px solid #e5edfb; border-bottom:1px solid #e5edfb; }
.bft-stat:last-child { border-right:none; }
.bft-stat .lbl { font-size:.68rem; text-transform:uppercase; letter-spacing:.03em; color:#64748b; font-weight:700; }
.bft-stat .val { font-size:1.15rem; font-weight:800; }

.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.02em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:28px; }
.bank-table tbody td { padding:5px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#fffbeb; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800;
    padding:7px 8px; border-top:2px solid #60a5fa;
}
</style>
@endpush

@section('content')

{{-- ── Sarlavha ─────────────────────────────────────────────────── --}}
<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-gift fs-5"></i>
        <span class="fw-bold">Bonusga berilgan tovarlar</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('hisobotlar.excel','bonus_tovarlar') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
           class="btn btn-sm btn-light py-1">
            <i class="bi bi-file-earmark-excel me-1 text-success"></i> Excel
        </a>
        <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-light py-1">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

{{-- ── Ogohlantirish ────────────────────────────────────────────── --}}
<div class="alert alert-warning py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Bonus tovarlar mijozga qo'shib beriladi, ombordan kamayadi, lekin shartnoma va hisob-fakturada ko'rsatilmaydi — bu yerdagi summa Moliyaviy natija hisobotidagi "Bonusga berilgan tovar summasi" xarajat qatoriga avtomatik qo'shiladi.
</div>

{{-- ── Filtr ─────────────────────────────────────────────────────── --}}
<div class="bft-section-title mb-0"><span><i class="bi bi-funnel me-1"></i>Filtr</span></div>
<div class="bft-wrap mb-3">
    <div class="p-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label small mb-1">Dan</label>
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-3">
                <label class="form-label small mb-1">Gacha</label>
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filtrlash
                </button>
                <a href="{{ route('hisobotlar.excel','bonus_tovarlar') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Jami kartalar ─────────────────────────────────────────────── --}}
<div class="bft-stat-wrap mb-3">
    <div class="bft-stat">
        <div class="lbl">Tovar turlari</div>
        <div class="val">{{ number_format($jami->turlar_soni) }}</div>
    </div>
    <div class="bft-stat">
        <div class="lbl">Jami soni</div>
        <div class="val">{{ number_format($jami->soni) }}</div>
    </div>
    <div class="bft-stat">
        <div class="lbl">Shartnomalar soni</div>
        <div class="val" style="color:#0284c7">{{ number_format($jami->shartnoma_soni) }}</div>
    </div>
    <div class="bft-stat">
        <div class="lbl">Jami qiymat (xarajat)</div>
        <div class="val" style="color:#d97706">{{ number_format($jami->jami_qiymat/1000000,1) }} mln</div>
    </div>
</div>

{{-- ── Jadval ────────────────────────────────────────────────────── --}}
<div class="bft-section-title mb-0">
    <span><i class="bi bi-table me-1"></i>Tovarlar bo'yicha yig'indi — {{ $danSana }} — {{ $gachaSana }}</span>
    <span class="badge bg-light text-dark">{{ $tovarlar->count() }} tur</span>
</div>
<div class="bank-wrap">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">#</th>
                <th class="tl">Tovar nomi</th>
                <th class="tl">Birlik</th>
                <th>Soni</th>
                <th>Shartnomalar</th>
                <th>Jami qiymat (xarajat)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tovarlar as $i => $t)
            <tr>
                <td class="tl">{{ $i+1 }}</td>
                <td class="tl">{{ $t->nomi }}</td>
                <td class="tl">{{ $t->birlik }}</td>
                <td class="num">{{ number_format($t->jami_soni) }}</td>
                <td class="num">{{ number_format($t->shartnoma_soni) }}</td>
                <td class="num fw-bold" style="color:#b45309">{{ number_format($t->jami_qiymat,0,'.',' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-4">Tanlangan davrda bonusga berilgan tovar topilmadi</td></tr>
            @endforelse
        </tbody>
        @if($tovarlar->isNotEmpty())
        <tfoot>
            <tr>
                <td class="tl" colspan="3">Jami:</td>
                <td class="num">{{ number_format($jami->soni) }}</td>
                <td class="num">—</td>
                <td class="num">{{ number_format($jami->jami_qiymat,0,'.',' ') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection
