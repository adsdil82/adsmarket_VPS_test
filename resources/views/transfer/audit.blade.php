@extends('layouts.app')
@section('title', 'Transferlar audit jurnali')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Audit jurnali</li>
@endsection
@push('styles')
<style>
.audit-tabs .nav-link { font-size:.82rem;padding:5px 12px; }
</style>
@endpush
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-journal-text me-2 text-info"></i>Transferlar audit jurnali</h5>

<ul class="nav nav-tabs audit-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ $tur==="barchasi"?"active":"" }}" href="?tur=barchasi">Barchasi</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="tovar"?"active":"" }}" href="?tur=tovar">Tovar transferlari ({{ $tovar->count() }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="kassa"?"active":"" }}" href="?tur=kassa">Kassa ({{ $kassa->count() }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="xodim"?"active":"" }}" href="?tur=xodim">Xodim tayinlash ({{ $xodimTayinlash->count() }})</a></li>
    <li class="nav-item"><a class="nav-link {{ $tur==="filial"?"active":"" }}" href="?tur=filial">Filial ko'chirish ({{ $filialKochirish->count() }})</a></li>
</ul>

@if(in_array($tur,["barchasi","tovar"]) && $tovar->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2"><h6 class="mb-0 small fw-bold"><i class="bi bi-box-seam me-1 text-success"></i>Tovar transferlari</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>#</th><th>Yo'nalish</th><th>Holat</th><th>Sana</th><th>Xodim</th><th></th></tr></thead>
            <tbody>
                @foreach($tovar as $t)
                <tr>
                    <td class="small fw-medium">{{ $t->transfer_raqam ?? "T-{$t->id}" }}</td>
                    <td class="small">{{ $t->fromFilial->kod }} → {{ $t->toFilial->kod }}</td>
                    <td><span class="badge bg-{{ $t->holat_rangi }}" style="font-size:.65rem">{{ $t->holat }}</span></td>
                    <td class="text-muted small">{{ $t->sana->format("d.m.Y") }}</td>
                    <td class="text-muted small">{{ $t->xodim->ism_familiya }}</td>
                    <td><a href="{{ route("transfer.tovar.show",$t) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:.7rem"><i class="bi bi-eye"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(in_array($tur,["barchasi","kassa"]) && $kassa->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2"><h6 class="mb-0 small fw-bold"><i class="bi bi-cash-coin me-1 text-primary"></i>Kassa transferlari</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>#</th><th>Yo'nalish</th><th class="text-end">Summa</th><th>Holat</th><th>Sana</th><th></th></tr></thead>
            <tbody>
                @foreach($kassa as $t)
                <tr>
                    <td class="small fw-medium">{{ $t->transfer_raqam }}</td>
                    <td class="small">{{ $t->fromFilial->kod }}/{{ $t->fromKassa->nomi }} → {{ $t->toFilial->kod }}/{{ $t->toKassa->nomi }}</td>
                    <td class="text-end small fw-bold">{{ number_format($t->summa,0,"."," ") }} {{ $t->valyuta }}</td>
                    <td><span class="badge bg-{{ $t->holat_rangi }}" style="font-size:.65rem">{{ $t->holat }}</span></td>
                    <td class="text-muted small">{{ $t->sana->format("d.m.Y") }}</td>
                    <td><a href="{{ route("transfer.kassa.show",$t) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size:.7rem"><i class="bi bi-eye"></i></a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(in_array($tur,["barchasi","xodim"]) && $xodimTayinlash->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2"><h6 class="mb-0 small fw-bold"><i class="bi bi-person-check me-1 text-warning"></i>Xodim qayta tayinlash</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Shartnoma</th><th>Eski xodim</th><th>Yangi xodim</th><th>Sabab</th><th>Sana</th></tr></thead>
            <tbody>
                @foreach($xodimTayinlash as $t)
                <tr>
                    <td><a href="{{ route("kreditlar.show",$t->shartnoma) }}" class="text-decoration-none small fw-medium">{{ $t->shartnoma->shartnoma_raqam }}</a></td>
                    <td class="text-muted small">{{ $t->eskiXodim?->ism_familiya ?? "—" }}</td>
                    <td class="small fw-medium">{{ $t->yangiXodim->ism_familiya }}</td>
                    <td class="small">{{ Str::limit($t->sabab,50) }}</td>
                    <td class="text-muted small">{{ $t->created_at->format("d.m.Y H:i") }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if(in_array($tur,["barchasi","filial"]) && $filialKochirish->count())
<div class="card border-0 shadow-sm">
    <div class="card-header py-2"><h6 class="mb-0 small fw-bold"><i class="bi bi-building-fill-slash me-1 text-danger"></i>Filial ko'chirish</h6></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Shartnoma</th><th>Eski filial</th><th>Yangi filial</th><th>Sabab</th><th>Sana</th></tr></thead>
            <tbody>
                @foreach($filialKochirish as $t)
                <tr>
                    <td><a href="{{ route("kreditlar.show",$t->shartnoma) }}" class="text-decoration-none small fw-medium">{{ $t->shartnoma->shartnoma_raqam }}</a></td>
                    <td><span class="badge bg-secondary">{{ $t->eskiFilial->kod }}</span></td>
                    <td><span class="badge bg-primary">{{ $t->yangiFilial->kod }}</span></td>
                    <td class="small">{{ Str::limit($t->sabab,50) }}</td>
                    <td class="text-muted small">{{ $t->created_at->format("d.m.Y H:i") }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
