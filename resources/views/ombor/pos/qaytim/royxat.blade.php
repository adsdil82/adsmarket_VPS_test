@extends('layouts.app')
@section('title', 'Qaytimlar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item active">Qaytim / Vozvrat</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #fecaca; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th { background:linear-gradient(180deg,#b91c1c,#7f1d1d); color:#fff; font-weight:800; font-size:.68rem; text-transform:uppercase; padding:7px 8px; text-align:right; white-space:nowrap; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:hover td { background:#fee2e2 !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#fef2f2; }
.bank-table tbody td { padding:6px 8px; white-space:nowrap; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; }
.bank-table tfoot td { background:linear-gradient(90deg,#7f1d1d,#b91c1c) !important; color:#fff; font-weight:800; padding:7px 8px; }
.bank-wrap { overflow:auto; border:1px solid #fca5a5; border-radius:0 0 6px 6px; }
.filter-bar { background:linear-gradient(90deg,#fee2e2,#fecaca); border:1px solid #fca5a5; border-radius:8px; padding:10px 14px; }
.filter-bar .form-select, .filter-bar .form-control { background:#fff; border:1px solid #f87171; font-size:.82rem; height:34px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-return-left me-2 text-danger"></i>Qaytim / Vozvrat</h5>
    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label class="form-label small mb-1">Dan</label>
            <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ request('dan_sana') }}">
        </div>
        <div>
            <label class="form-label small mb-1">Gacha</label>
            <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ request('gacha_sana') }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <label class="form-label small mb-1">Filial</label>
            <select name="filial_id" class="form-select form-select-sm" style="width:170px">
                <option value="">Barchasi</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label small mb-1">Sabab</label>
            <select name="sabab" class="form-select form-select-sm" style="width:180px">
                <option value="">Barchasi</option>
                <option value="fikr_ozgardi" {{ request('sabab')=='fikr_ozgardi'?'selected':'' }}>Mijoz fikri o'zgardi</option>
                <option value="nosoz_mahsulot" {{ request('sabab')=='nosoz_mahsulot'?'selected':'' }}>Nosoz/yaroqsiz</option>
                <option value="notogri_mahsulot" {{ request('sabab')=='notogri_mahsulot'?'selected':'' }}>Noto'g'ri mahsulot</option>
                <option value="boshqa" {{ request('sabab')=='boshqa'?'selected':'' }}>Boshqa</option>
            </select>
        </div>
        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-funnel me-1"></i>Filtrlash</button>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Qaytim №</th>
                <th class="tl">Sana</th>
                <th class="tl">Asl chek</th>
                <th class="tl">Mijoz</th>
                <th class="tl">Kassir</th>
                <th class="tl">Smena</th>
                <th>Summa</th>
                <th class="tl">To'lov turi</th>
                <th class="tl">Sabab</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($qaytimlar as $q)
            <tr>
                <td class="tl">{{ $q->qaytim_raqami }}</td>
                <td class="tl">{{ $q->sana->format('d.m.Y') }}</td>
                <td class="tl"><a href="{{ route('pos.chek',$q->sotuv) }}">{{ $q->sotuv->check_raqam ?? '—' }}</a></td>
                <td class="tl">{{ $q->mijoz_ism ?: '—' }}</td>
                <td class="tl">{{ $q->xodim->ism_familiya ?? '—' }}</td>
                <td class="tl">{{ $q->smena->smena_raqami ?? '—' }}</td>
                <td class="num">{{ number_format($q->jami_summa,0,'.',' ') }}</td>
                <td class="tl">{{ $q->tolov_turi }}</td>
                <td class="tl">{{ $q->sabab }}</td>
                <td class="tl"><a href="{{ route('pos.qaytim.korish',$q) }}" class="btn btn-sm btn-outline-danger py-0"><i class="bi bi-eye"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center text-muted py-4">Qaytim topilmadi</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="6" class="tl">JAMI</td><td class="num">{{ number_format($jamiSumma,0,'.',' ') }}</td><td colspan="3"></td></tr>
        </tfoot>
    </table>
</div>
<div class="mt-2">{{ $qaytimlar->links('pagination::bootstrap-5') }}</div>
@endsection
