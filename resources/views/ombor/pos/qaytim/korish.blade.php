@extends('layouts.app')
@section('title', 'Qaytim — '.$qaytim->qaytim_raqami)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item"><a href="{{ route('pos.qaytim.royxat') }}">Qaytim / Vozvrat</a></li>
<li class="breadcrumb-item active">{{ $qaytim->qaytim_raqami }}</li>
@endsection

@push('styles')
<style>
.pos-card { background:#fff; border:1px solid #fecaca; border-radius:8px; padding:12px 14px; text-align:center; }
.pos-card .label { font-size:.68rem; color:#7a89a8; text-transform:uppercase; letter-spacing:.03em; font-weight:700; }
.pos-card .value { font-size:1.1rem; font-weight:800; color:#0f172a; margin-top:2px; }
.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #fecaca; }
.bank-table thead th { background:linear-gradient(180deg,#b91c1c,#7f1d1d); color:#fff; font-weight:800; font-size:.66rem; text-transform:uppercase; padding:6px 8px; text-align:right; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(even) td { background:#fef2f2; }
.bank-table tbody td { padding:5px 8px; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-return-left me-2 text-danger"></i>Qaytim — {{ $qaytim->qaytim_raqami }}</h5>
    <a href="{{ route('pos.qaytim.royxat') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Asl chek</div><div class="value"><a href="{{ route('pos.chek',$qaytim->sotuv) }}">{{ $qaytim->sotuv->check_raqam ?? '—' }}</a></div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Sana</div><div class="value">{{ $qaytim->sana->format('d.m.Y') }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Kassir</div><div class="value">{{ $qaytim->xodim->ism_familiya ?? '—' }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Smena</div><div class="value"><a href="{{ route('pos.smena.korish',$qaytim->smena) }}">{{ $qaytim->smena->smena_raqami ?? '—' }}</a></div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Jami summa</div><div class="value text-danger">{{ number_format($qaytim->jami_summa,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">To'lov turi</div><div class="value">{{ $qaytim->tolov_turi }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Sabab</div><div class="value" style="font-size:.85rem">{{ $qaytim->sabab }}</div></div></div>
    <div class="col-6 col-md-3"><div class="pos-card"><div class="label">Filial</div><div class="value">{{ $qaytim->filial->nomi ?? '—' }}</div></div></div>
</div>

@if($qaytim->izoh)
<div class="alert alert-secondary py-2 small"><strong>Izoh:</strong> {{ $qaytim->izoh }}</div>
@endif

<div class="bank-wrap shadow-sm" style="overflow:auto">
    <table class="bank-table">
        <thead><tr><th class="tl">Tovar</th><th>Miqdor</th><th>Narx</th><th>Jami</th></tr></thead>
        <tbody>
            @foreach($qaytim->tafsilot as $t)
            <tr>
                <td class="tl">{{ $t->tovar->nomi ?? 'Nomsiz' }}</td>
                <td class="num">{{ rtrim(rtrim(number_format($t->miqdor,3,'.',' '),'0'),'.') }}</td>
                <td class="num">{{ number_format($t->narx,0,'.',' ') }}</td>
                <td class="num">{{ number_format($t->jami_summa,0,'.',' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
