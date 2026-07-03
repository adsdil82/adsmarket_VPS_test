@extends('layouts.app')

@section('title', 'Kelayotgan to\'lovlar')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Kelayotgan to'lovlar</li>
@endsection

@push('styles')
<style>
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:8px 14px; }
.filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#1e3a8a,#1d4ed8);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; align-items:center; gap:10px; flex-wrap:wrap;
}
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
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
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

<div class="filter-bar mb-3">
    <form method="GET" action="{{ route('hisobotlar.kelayotgan') }}" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-calendar-check" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Kelayotgan to'lovlar</span>
            <span class="badge bg-secondary">{{ $tulovlar->count() }}</span>
        </div>

        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Kunlar soni</label>
            <select name="kunlar" class="form-select" style="width:110px">
                @foreach([1,2,3,5,7,10,14,21,30,31] as $k)
                    <option value="{{ $k }}" {{ $kunlar == $k ? 'selected' : '' }}>{{ $k }} kun</option>
                @endforeach
            </select>
        </div>

        @if(Auth::user()->isAdmin())
        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Filial</label>
            <select name="filial_id" class="form-select" style="width:180px">
                <option value="">Barcha filial</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="form-label small mb-1 fw-medium" style="color:#1e3a8a">Mas'ul xodim</label>
            <select name="xodim_id" class="form-select" style="width:200px">
                <option value="">Barcha xodimlar</option>
                @foreach($xodimlar as $x)
                    <option value="{{ $x->id }}" {{ $xodimId == $x->id ? 'selected' : '' }}>{{ $x->ism_familiya }}</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px">
                <i class="bi bi-arrow-clockwise me-1"></i>Yangilash
            </button>
            @if(request()->anyFilled(['kunlar','xodim_id','filial_id']))
            <a href="{{ route('hisobotlar.kelayotgan') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </div>

        <div class="ms-auto text-muted small">
            Keyingi <strong>{{ $kunlar }}</strong> kun ({{ now()->format('d.m.Y') }} — {{ now()->addDays($kunlar)->format('d.m.Y') }})
        </div>
    </form>
</div>

@if($tulovlar->isEmpty())
<div class="bank-wrap">
    <div class="p-5 text-center text-muted">
        <i class="bi bi-calendar-check fs-3 d-block mb-2"></i>
        Keyingi {{ $kunlar }} kun ichida to'lov kutilmayapti
    </div>
</div>
@else

{{-- Kunlik guruhlash --}}
@php
    $guruhlangan = $tulovlar->groupBy(fn($t) => $t->tolov_sana?->format('Y-m-d'));
@endphp

@foreach($guruhlangan as $sana => $guruh)
<div class="bft-section-title">
    <span>{{ \Carbon\Carbon::parse($sana)->isoFormat('D MMMM, dddd') }}</span>
    @if(\Carbon\Carbon::parse($sana)->isToday())
        <span class="badge bg-warning text-dark">Bugun</span>
    @elseif(\Carbon\Carbon::parse($sana)->isTomorrow())
        <span class="badge bg-info text-dark">Ertaga</span>
    @endif
    <span class="ms-auto small" style="opacity:.9">
        {{ $guruh->count() }} ta to'lov — jami: <strong>{{ number_format($guruh->sum('tolov_summa'), 0, '.', ' ') }} so'm</strong>
    </span>
</div>
<div class="bank-wrap shadow-sm mb-3">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Shartnoma</th>
                <th class="tl">Mijoz</th>
                @if(Auth::user()->isAdmin())<th class="tl">Filial</th>@endif
                <th class="tl">Mas'ul xodim</th>
                <th>To'lov summasi</th>
                <th>Qoldiq</th>
                <th class="tl">Oy tartib</th>
                <th class="tl">Holat</th>
                <th style="width:46px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($guruh as $g)
            @php
                $masulXodim = $g->kredit?->joriyXodim ?? $g->kredit?->xodim;
            @endphp
            <tr>
                <td class="tl">
                    @if($g->kredit)
                    <a href="{{ route('kreditlar.show', $g->kredit) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                        {{ $g->kredit->shartnoma_raqam }}
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="tl">
                    @if($g->kredit?->mijoz)
                    <a href="{{ route('mijozlar.show', $g->kredit->mijoz) }}" class="text-decoration-none" style="color:#1e293b">
                        {{ $g->kredit->mijoz->familiya }} {{ $g->kredit->mijoz->ism }}
                    </a>
                    <div class="text-muted" style="font-size:.7rem">{{ $g->kredit->mijoz->telefon }}</div>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                @if(Auth::user()->isAdmin())
                <td class="tl">
                    <span class="badge bg-secondary">{{ $g->kredit?->filial?->kod ?? '—' }}</span>
                </td>
                @endif
                <td class="tl text-muted">
                    {{ $masulXodim?->ism_familiya ?? '—' }}
                </td>
                <td class="num fw-bold">
                    {{ number_format($g->tolov_summa, 0, '.', ' ') }}
                </td>
                <td class="num text-danger">
                    {{ number_format($g->qoldiq_suma, 0, '.', ' ') }}
                </td>
                <td class="tl text-muted">
                    {{ $g->oylik_tartib }}-oy
                </td>
                <td class="tl">
                    <span class="badge bg-{{ $g->holat_rangi }}">{{ $g->holat }}</span>
                </td>
                <td class="text-center">
                    @if($g->kredit)
                    <a href="{{ route('kreditlar.tulov.create', $g->kredit) }}"
                       class="btn btn-sm btn-success py-0 px-1">
                        <i class="bi bi-cash"></i>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

@endif
@endsection
