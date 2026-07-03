@extends('layouts.app')
@section('title', 'Transferlar audit jurnali')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Audit jurnali</li>
@endsection
@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px;
    padding:8px 14px; display:flex; align-items:center; gap:8px; margin-bottom:10px;
}
.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#1e3a8a,#1d4ed8);
    padding:5px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.8rem;
    display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; background:#fff; margin-bottom:14px; }

.audit-tabs { border-bottom:2px solid #1d4ed8; margin-bottom:14px; gap:2px; }
.audit-tabs .nav-link {
    font-size:.78rem; padding:5px 12px; font-weight:600; color:#334155;
    border:1px solid transparent; border-bottom:none; border-radius:6px 6px 0 0;
}
.audit-tabs .nav-link.active {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; border-color:#1d4ed8;
}

.bank-table { border-collapse:collapse; font-size:.78rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.64rem; letter-spacing:.02em; text-transform:uppercase; padding:6px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody tr:hover td { background:#dbeafe !important; }
.num { text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
</style>
@endpush
@section('content')

<div class="bft-header-card">
    <i class="bi bi-journal-text fs-5"></i>
    <span class="fw-bold">Transferlar audit jurnali</span>
</div>

<ul class="nav audit-tabs">
    <li class="nav-item"><a class="nav-link {{ $tur==="barchasi"?"active":"" }}" href="?tur=barchasi">Barchasi</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="tovar"?"active":"" }}" href="?tur=tovar">Tovar transferlari ({{ $tovar->count() }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="kassa"?"active":"" }}" href="?tur=kassa">Kassa ({{ $kassa->count() }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="xodim"?"active":"" }}" href="?tur=xodim">Xodim tayinlash ({{ $xodimTayinlash->count() }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="filial"?"active":"" }}" href="?tur=filial">Filial ko'chirish ({{ $filialKochirish->count() }})</a></li>
</ul>

@if(in_array($tur,["barchasi","tovar"]) && $tovar->count())
<div class="bft-section-title"><span><i class="bi bi-box-seam me-1"></i>Tovar transferlari</span><span class="badge bg-light text-dark">{{ $tovar->count() }}</span></div>
<div class="bank-wrap bft-wrap">
    <table class="bank-table">
        <thead><tr><th class="tl">#</th><th class="tl">Yo'nalish</th><th class="tl">Holat</th><th class="tl">Sana</th><th class="tl">Xodim</th><th></th></tr></thead>
        <tbody>
            @foreach($tovar as $t)
            <tr>
                <td class="tl fw-medium">{{ $t->transfer_raqam ?? "T-{$t->id}" }}</td>
                <td class="tl">{{ $t->fromFilial->kod }} → {{ $t->toFilial->kod }}</td>
                <td class="tl"><span class="badge bg-{{ $t->holat_rangi }}" style="font-size:.65rem">{{ $t->holat }}</span></td>
                <td class="tl">{{ $t->sana->format("d.m.Y") }}</td>
                <td class="tl">{{ $t->xodim->ism_familiya }}</td>
                <td class="tl"><a href="{{ route("transfer.tovar.show",$t) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:.7rem"><i class="bi bi-eye"></i></a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(in_array($tur,["barchasi","kassa"]) && $kassa->count())
<div class="bft-section-title"><span><i class="bi bi-cash-coin me-1"></i>Kassa transferlari</span><span class="badge bg-light text-dark">{{ $kassa->count() }}</span></div>
<div class="bank-wrap bft-wrap">
    <table class="bank-table">
        <thead><tr><th class="tl">#</th><th class="tl">Yo'nalish</th><th>Summa</th><th class="tl">Holat</th><th class="tl">Sana</th><th></th></tr></thead>
        <tbody>
            @foreach($kassa as $t)
            <tr>
                <td class="tl fw-medium">{{ $t->transfer_raqam }}</td>
                <td class="tl">{{ $t->fromFilial->kod }}/{{ $t->fromKassa->nomi }} → {{ $t->toFilial->kod }}/{{ $t->toKassa->nomi }}</td>
                <td class="num fw-bold">{{ number_format($t->summa,0,"."," ") }} {{ $t->valyuta }}</td>
                <td class="tl"><span class="badge bg-{{ $t->holat_rangi }}" style="font-size:.65rem">{{ $t->holat }}</span></td>
                <td class="tl">{{ $t->sana->format("d.m.Y") }}</td>
                <td class="tl"><a href="{{ route("transfer.kassa.show",$t) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:.7rem"><i class="bi bi-eye"></i></a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(in_array($tur,["barchasi","xodim"]) && $xodimTayinlash->count())
<div class="bft-section-title"><span><i class="bi bi-person-check me-1"></i>Xodim qayta tayinlash</span><span class="badge bg-light text-dark">{{ $xodimTayinlash->count() }}</span></div>
<div class="bank-wrap bft-wrap">
    <table class="bank-table">
        <thead><tr><th class="tl">Shartnoma</th><th class="tl">Eski xodim</th><th class="tl">Yangi xodim</th><th class="tl">Sabab</th><th class="tl">Sana</th></tr></thead>
        <tbody>
            @foreach($xodimTayinlash as $t)
            <tr>
                <td class="tl"><a href="{{ route("kreditlar.show",$t->shartnoma) }}" class="text-decoration-none fw-medium">{{ $t->shartnoma->shartnoma_raqam }}</a></td>
                <td class="tl">{{ $t->eskiXodim?->ism_familiya ?? "—" }}</td>
                <td class="tl fw-medium">{{ $t->yangiXodim->ism_familiya }}</td>
                <td class="tl">{{ Str::limit($t->sabab,50) }}</td>
                <td class="tl">{{ $t->created_at->format("d.m.Y H:i") }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(in_array($tur,["barchasi","filial"]) && $filialKochirish->count())
<div class="bft-section-title"><span><i class="bi bi-building-fill-slash me-1"></i>Filial ko'chirish</span><span class="badge bg-light text-dark">{{ $filialKochirish->count() }}</span></div>
<div class="bank-wrap bft-wrap">
    <table class="bank-table">
        <thead><tr><th class="tl">Shartnoma</th><th class="tl">Eski filial</th><th class="tl">Yangi filial</th><th class="tl">Sabab</th><th class="tl">Sana</th></tr></thead>
        <tbody>
            @foreach($filialKochirish as $t)
            <tr>
                <td class="tl"><a href="{{ route("kreditlar.show",$t->shartnoma) }}" class="text-decoration-none fw-medium">{{ $t->shartnoma->shartnoma_raqam }}</a></td>
                <td class="tl"><span class="badge bg-secondary">{{ $t->eskiFilial->kod }}</span></td>
                <td class="tl"><span class="badge bg-primary">{{ $t->yangiFilial->kod }}</span></td>
                <td class="tl">{{ Str::limit($t->sabab,50) }}</td>
                <td class="tl">{{ $t->created_at->format("d.m.Y H:i") }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
