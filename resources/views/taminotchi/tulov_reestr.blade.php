@extends('layouts.app')
@section('title', "To'lovlar reestri")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item active">To'lovlar reestri</li>
@endsection

@push('styles')
<style>
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff; font-weight:800;
    padding:7px 8px; border-top:2px solid #60a5fa;
}
.bank-wrap { overflow:auto; height:calc(100vh - 230px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 250px); } }
.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; background:#0891b2; color:#fff; }
</style>
@endpush

@section('content')

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-list-check" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Ta'minotchilar — To'lovlar reestri</span>
        </div>
        <div>
            <input type="date" name="dan_sana" class="form-control" value="{{ $danSana }}">
        </div>
        <div>
            <input type="date" name="gacha_sana" class="form-control" value="{{ $gachaSana }}">
        </div>
        <div>
            <select name="taminotchi_id" class="form-select" style="width:220px">
                <option value="">Barcha ta'minotchilar</option>
                @foreach($taminotchilar as $t)
                <option value="{{ $t->id }}" {{ request('taminotchi_id')==$t->id?'selected':'' }}>{{ $t->nomi }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
            <a href="{{ route('taminotchi.tulov_reestr') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="badge-modern">{{ $tulovlar->total() }} ta</span>
            <strong class="text-success">{{ number_format($tulovlar->sum('summa_uzs'),0,'.',' ') }} so'm</strong>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Sana</th>
                <th class="tl">Ta'minotchi</th>
                <th class="tl">To'lov turi</th>
                <th>Summa</th>
                <th class="tl">Hujjat #</th>
                <th class="tl">Kassir</th>
                <th class="tl">Izoh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tulovlar as $tv)
            <tr>
                <td class="tl text-muted">{{ $tv->tolov_sana->format('d.m.Y') }}</td>
                <td class="tl">
                    <a href="{{ route('taminotchi.show',$tv->taminotchi) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                        {{ $tv->taminotchi->nomi }}
                    </a>
                </td>
                <td class="tl"><span class="badge-modern" style="background:#e0e7ff;color:#3730a3">{{ $tv->tolov_turi }}</span></td>
                <td class="num fw-bold text-success">
                    @if($tv->valyuta !== 'UZS')
                        {{ number_format($tv->summa,0,'.',' ') }} {{ $tv->valyuta }}
                        <div class="text-muted fw-normal" style="font-size:.68rem">{{ number_format($tv->summa_uzs,0,'.',' ') }} so'm</div>
                    @else
                        {{ number_format($tv->summa_uzs,0,'.',' ') }}
                    @endif
                </td>
                <td class="tl text-muted">{{ $tv->hujjat_raqam ?? '—' }}</td>
                <td class="tl text-muted">{{ $tv->xodim->ism_familiya ?? '—' }}</td>
                <td class="tl text-muted">{{ $tv->izoh ?? '—' }}</td>
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
        @if($tulovlar->count())
        <tfoot>
            <tr>
                <td class="tl" colspan="3">Jami:</td>
                <td class="num">{{ number_format($tulovlar->sum('summa_uzs'),0,'.',' ') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@if($tulovlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $tulovlar->firstItem() }}–{{ $tulovlar->lastItem() }} / {{ $tulovlar->total() }} ta</small>
    {{ $tulovlar->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection
