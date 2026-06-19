@extends('layouts.app')
@section('title', 'Hisobotlar')
@section('breadcrumb')
    <li class="breadcrumb-item active">Hisobotlar</li>
@endsection

@push('styles')
<style>
.report-btn-card {
    border: 2px solid transparent; border-radius: 12px;
    padding: 16px; cursor: pointer; transition: all .2s;
    text-decoration: none; display: block;
}
.report-btn-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
.report-btn-card .rbc-icon { font-size: 2rem; margin-bottom: 8px; }
.report-btn-card .rbc-title { font-weight: 700; font-size: .92rem; }
.report-btn-card .rbc-desc { font-size: .76rem; opacity: .8; margin-top: 2px; }
</style>
@endpush

@section('content')

{{-- ═══ Tezkor hisobotlar ════════════════════════════════════════ --}}
<h5 class="fw-bold mb-3"><i class="bi bi-lightning-charge me-1 text-warning"></i> Tezkor hisobotlar</h5>
<div class="row g-3 mb-4">

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
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
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
                    <i class="bi bi-funnel me-1"></i>Filtrlash
                </button>
                <a href="{{ route('hisobotlar.excel', 'chiqarilgan') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i>Excel
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- To'lov turlari --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-2 d-flex justify-content-between">
                <h6 class="mb-0 small">To'lov turlari bo'yicha</h6>
                <span class="badge bg-success">{{ number_format($tulovTurlariStatistika->sum('jami'),0,'.', ' ') }} so'm</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Tur</th><th class="text-center">Soni</th><th class="text-end">Jami</th></tr></thead>
                    <tbody>
                        @foreach($tulovTurlariStatistika as $t)
                        <tr>
                            <td>{{ $t->tulovTuri->nomi }}</td>
                            <td class="text-center">{{ $t->soni }}</td>
                            <td class="text-end fw-medium">{{ number_format($t->jami,0,'.',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Xodimlar --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-2"><h6 class="mb-0 small">Xodimlar bo'yicha</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Xodim</th><th class="text-center">Soni</th><th class="text-end">Jami</th></tr></thead>
                    <tbody>
                        @foreach($xodimlarStatistika as $x)
                        <tr>
                            <td>{{ $x->xodim->ism_familiya }}</td>
                            <td class="text-center">{{ $x->soni }}</td>
                            <td class="text-end fw-medium">{{ number_format($x->jami,0,'.',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- To'lovlar tarixi --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between py-2">
        <h6 class="mb-0 small">To'lovlar tarixi</h6>
        <div class="d-flex gap-2 align-items-center">
            <small class="text-muted">{{ $tulovlarHisoboti->total() }} ta</small>
            <a href="{{ route('hisobotlar.excel', 'chiqarilgan') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
               class="btn btn-xs btn-outline-success py-0" style="font-size:.75rem">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>Sana</th><th>Shartnoma</th><th>Mijoz</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th class="text-end">Summa</th><th>Tur</th><th>Kassir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tulovlarHisoboti as $t)
                <tr>
                    <td class="small">{{ $t->tolov_sana->format('d.m.Y') }}</td>
                    <td>
                        <a href="{{ route('kreditlar.show', $t->kredit) }}" class="text-decoration-none small">
                            {{ $t->kredit->shartnoma_raqam }}
                        </a>
                    </td>
                    <td class="small">{{ $t->kredit->mijoz->familiya }} {{ $t->kredit->mijoz->ism }}</td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary" style="font-size:10px">{{ $t->kredit->filial->kod }}</span></td>
                    @endif
                    <td class="text-end fw-bold text-success small">{{ number_format($t->summa,0,'.',' ') }}</td>
                    <td class="small">{{ $t->tulovTuri->nomi }}</td>
                    <td class="small text-muted">{{ $t->xodim->ism_familiya }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">To'lovlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tulovlarHisoboti->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">{{ $tulovlarHisoboti->firstItem() }}–{{ $tulovlarHisoboti->lastItem() }} / {{ $tulovlarHisoboti->total() }}</small>
        {{ $tulovlarHisoboti->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
