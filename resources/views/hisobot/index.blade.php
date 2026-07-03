@extends('layouts.app')
@section('title', 'Hisobotlar')
@section('breadcrumb')
    <li class="breadcrumb-item active">Hisobotlar</li>
@endsection

@push('styles')
<style>
.report-btn-card {
    border: 2px solid transparent; border-radius: 8px;
    padding: 10px 8px; cursor: pointer; transition: all .15s;
    text-decoration: none; display: block;
}
.report-btn-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.12); }
.report-btn-card .rbc-icon { font-size: 1.4rem; margin-bottom: 4px; }
.report-btn-card .rbc-title { font-weight: 700; font-size: .82rem; }
.report-btn-card .rbc-desc { font-size: .68rem; opacity: .8; margin-top: 1px; }

.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:8px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
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
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap-sm { overflow:auto; max-height:260px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.bank-wrap { overflow:auto; height:calc(100vh - 420px); min-height:200px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

{{-- ═══ Tezkor hisobotlar ════════════════════════════════════════ --}}
<div class="bft-section-title mb-2"><span><i class="bi bi-lightning-charge me-1 text-warning"></i>Tezkor hisobotlar</span></div>
<div class="row g-2 mb-3">

    <div class="col-6 col-sm-4 col-lg-2">
        <a href="{{ route('hisobotlar.kredit_portfolio') }}"
           class="report-btn-card bg-success bg-opacity-10 border-success text-success text-center">
            <div class="rbc-icon">📊</div>
            <div class="rbc-title">Kredit portfeli</div>
            <div class="rbc-desc">Filial bo'yicha</div>
        </a>
    </div>

    <div class="col-6 col-sm-4 col-lg-2">
        <a href="{{ route('hisobotlar.chiqarilgan') }}"
           class="report-btn-card bg-primary bg-opacity-10 border-primary text-primary text-center">
            <div class="rbc-icon">📋</div>
            <div class="rbc-title">Chiqarilgan kreditlar</div>
            <div class="rbc-desc">Davr bo'yicha</div>
        </a>
    </div>

    <div class="col-6 col-sm-4 col-lg-2">
        <a href="{{ route('hisobotlar.kechikish_analiz') }}"
           class="report-btn-card bg-danger bg-opacity-10 border-danger text-danger text-center">
            <div class="rbc-icon">⏰</div>
            <div class="rbc-title">Kechikish analizi</div>
            <div class="rbc-desc">0-30-60-90-180+ kun</div>
        </a>
    </div>

    <div class="col-6 col-sm-4 col-lg-2">
        <a href="{{ route('hisobotlar.kelayotgan') }}"
           class="report-btn-card bg-warning bg-opacity-10 border-warning text-warning text-center">
            <div class="rbc-icon">📅</div>
            <div class="rbc-title">Kelayotgan to'lovlar</div>
            <div class="rbc-desc">Keyingi 7 kun</div>
        </a>
    </div>

    <div class="col-6 col-sm-4 col-lg-2">
        <a href="{{ route('hisobotlar.konstruktor') }}"
           class="report-btn-card text-center"
           style="background:linear-gradient(135deg,#6366f1,#7c3aed);color:#fff;border-color:#6366f1">
            <div class="rbc-icon">🔧</div>
            <div class="rbc-title">Konstruktor</div>
            <div class="rbc-desc">Ixtiyoriy hisobot</div>
        </a>
    </div>

    <div class="col-6 col-sm-4 col-lg-2">
        <a href="{{ route('hisobotlar.excel', 'portfolio') }}?{{ request()->getQueryString() }}"
           class="report-btn-card bg-info bg-opacity-10 border-info text-info text-center">
            <div class="rbc-icon">📥</div>
            <div class="rbc-title">Excel export</div>
            <div class="rbc-desc">Portfelni yuklab ol</div>
        </a>
    </div>
</div>

{{-- ═══ To'lovlar filtri ══════════════════════════════════════════ --}}
<div class="filter-bar mb-3">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Dan</label>
            <input type="date" name="dan_sana" class="form-control" style="width:160px" value="{{ $danSana }}">
        </div>
        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Gacha</label>
            <input type="date" name="gacha_sana" class="form-control" style="width:160px" value="{{ $gachaSana }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <select name="filial_id" class="form-select" style="width:180px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary px-3" style="height:32px">
                <i class="bi bi-funnel me-1"></i>Filtrlash
            </button>
            <a href="{{ route('hisobotlar.excel', 'chiqarilgan') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
               class="btn btn-sm btn-success px-3" style="height:32px">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
        </div>
    </form>
</div>

<div class="row g-3 mb-3">
    {{-- To'lov turlari --}}
    <div class="col-lg-6">
        <div class="bft-section-title">
            <span>To'lov turlari bo'yicha</span>
            <span class="badge bg-success">{{ number_format($tulovTurlariStatistika->sum('jami'),0,'.', ' ') }} so'm</span>
        </div>
        <div class="bank-wrap-sm">
            <table class="bank-table">
                <thead><tr><th class="tl">Tur</th><th>Soni</th><th>Jami</th></tr></thead>
                <tbody>
                    @forelse($tulovTurlariStatistika as $t)
                    <tr>
                        <td class="tl">{{ $t->tulovTuri->nomi }}</td>
                        <td class="num">{{ $t->soni }}</td>
                        <td class="num fw-bold">{{ number_format($t->jami,0,'.',' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- Xodimlar --}}
    <div class="col-lg-6">
        <div class="bft-section-title"><span>Xodimlar bo'yicha</span></div>
        <div class="bank-wrap-sm">
            <table class="bank-table">
                <thead><tr><th class="tl">Xodim</th><th>Soni</th><th>Jami</th></tr></thead>
                <tbody>
                    @forelse($xodimlarStatistika as $x)
                    <tr>
                        <td class="tl">{{ $x->xodim->ism_familiya }}</td>
                        <td class="num">{{ $x->soni }}</td>
                        <td class="num fw-bold">{{ number_format($x->jami,0,'.',' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- To'lovlar tarixi --}}
<div class="bft-section-title">
    <span>To'lovlar tarixi</span>
    <span class="d-flex gap-2 align-items-center">
        <span class="badge bg-secondary">{{ $tulovlarHisoboti->total() }} ta</span>
        <a href="{{ route('hisobotlar.excel', 'chiqarilgan') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
           class="btn btn-sm btn-outline-success py-0">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel
        </a>
    </span>
</div>
<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Sana</th><th class="tl">Shartnoma</th><th class="tl">Mijoz</th>
                @if(Auth::user()->isAdmin())<th class="tl">Filial</th>@endif
                <th>Summa</th><th class="tl">Tur</th><th class="tl">Kassir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tulovlarHisoboti as $t)
            <tr>
                <td class="tl text-muted">{{ $t->tolov_sana->format('d.m.Y') }}</td>
                <td class="tl">
                    <a href="{{ route('kreditlar.show', $t->kredit) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                        {{ $t->kredit->shartnoma_raqam }}
                    </a>
                </td>
                <td class="tl">{{ $t->kredit->mijoz->familiya }} {{ $t->kredit->mijoz->ism }}</td>
                @if(Auth::user()->isAdmin())
                <td class="tl"><span class="badge" style="background:#1e293b;color:#fff;font-size:10px">{{ $t->kredit->filial->kod }}</span></td>
                @endif
                <td class="num fw-bold text-success">{{ number_format($t->summa,0,'.',' ') }}</td>
                <td class="tl">{{ $t->tulovTuri->nomi }}</td>
                <td class="tl text-muted">{{ $t->xodim->ism_familiya }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-search fs-3 d-block mb-2"></i>
                    To'lovlar topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($tulovlarHisoboti->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $tulovlarHisoboti->firstItem() }}–{{ $tulovlarHisoboti->lastItem() }} / {{ $tulovlarHisoboti->total() }} ta</small>
    {{ $tulovlarHisoboti->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection
