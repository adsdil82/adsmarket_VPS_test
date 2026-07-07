@extends('layouts.app')
@section('title','Chiqim #'.$chiqim->id)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chiqim.index') }}">Chiqim</a></li>
<li class="breadcrumb-item active">#{{ $chiqim->id }}</li>
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
.bft-table { width:100%; margin-bottom:0 !important; font-size:.86rem; }
.bft-table td { padding:9px 12px; vertical-align:middle; border-bottom:1px solid #fee2e2; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#fef8f8; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#fef2f2; }
.bft-wide { width:100%; }

.bank-table { border-collapse:collapse; font-size:.84rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #fca5a5; }
.bank-table thead th {
    background:linear-gradient(180deg,#dc2626,#7f1d1d); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.02em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#fef8f8; }
.bank-table tbody td { padding:7px 8px; vertical-align:middle; }
.bank-table tfoot td {
    background:linear-gradient(90deg,#7f1d1d,#b91c1c) !important; color:#fff; font-weight:800;
    padding:7px 8px; border-top:2px solid #dc2626;
}
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-wrap { overflow:auto; border:1px solid #fca5a5; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')

{{-- ── Sarlavha ─────────────────────────────────────────────────── --}}
<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-box-arrow-up fs-5"></i>
        <span class="fw-bold">Chiqim #{{ $chiqim->id }}</span>
        <span class="badge bg-light text-dark">{{ \App\Models\OmbordanChiqim::$sabablar[$chiqim->sabab] ?? $chiqim->sabab }}</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-light py-1"
                onclick="hujjatModalOch('{{ route('chiqim.hujjat.html', [$chiqim, 'yuk_xati']) }}', 'Yuk xati — CHQ-{{ $chiqim->id }}', false)">
            <i class="bi bi-truck me-1"></i>Yuk xati
        </button>
        <button type="button" class="btn btn-sm btn-light py-1"
                onclick="hujjatModalOch('{{ route('chiqim.hujjat.html', [$chiqim, 'akt']) }}', 'Hisobdan chiqarish akti — CHQ-{{ $chiqim->id }}', false)">
            <i class="bi bi-file-earmark-ruled me-1"></i>Akt
        </button>
        <button type="button" class="btn btn-sm btn-light py-1"
                onclick="hujjatModalOch('{{ route('chiqim.hujjat.html', [$chiqim, 'schyot']) }}', 'Schyot-faktura — CHQ-{{ $chiqim->id }}', false)">
            <i class="bi bi-receipt me-1"></i>Schyot-faktura
        </button>
        <a href="{{ route('chiqim.index') }}" class="btn btn-sm btn-light py-1">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- ── Chiqim ma'lumotlari ──────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="bft-section-title mb-0"><span><i class="bi bi-card-list me-1"></i>Chiqim ma'lumotlari</span></div>
        <div class="bft-wrap mb-3">
            <table class="bft-table">
                <tbody>
                    <tr>
                        <td class="bft-label">ID</td>
                        <td class="bft-wide">#{{ $chiqim->id }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Sana</td>
                        <td class="bft-wide">{{ $chiqim->sana->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Sabab</td>
                        <td class="bft-wide">
                            @php $sababRanglar = ['nasiya_sotish'=>'primary','naqd_sotish'=>'success','bonus'=>'warning','yoqolgan'=>'danger','brak'=>'dark','boshqa'=>'secondary']; @endphp
                            <span class="badge bg-{{ $sababRanglar[$chiqim->sabab]??'secondary' }}">
                                {{ \App\Models\OmbordanChiqim::$sabablar[$chiqim->sabab] ?? $chiqim->sabab }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Xodim</td>
                        <td class="bft-wide">{{ $chiqim->xodim?->ism_familiya ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Filial</td>
                        <td class="bft-wide">{{ $chiqim->filial?->nomi ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Holat</td>
                        <td class="bft-wide">
                            <span class="badge bg-{{ $chiqim->holat==='tasdiqlangan'?'success':'danger' }}">{{ $chiqim->holat }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Jami summa</td>
                        <td class="bft-wide">
                            <span class="fw-bold fs-5 text-danger">{{ number_format($chiqim->umumiy_summa,0,'.',' ') }} so'm</span>
                        </td>
                    </tr>
                    @if($chiqim->izoh)
                    <tr>
                        <td class="bft-label">Izoh</td>
                        <td class="bft-wide text-muted">{{ $chiqim->izoh }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Tovarlar ─────────────────────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="bft-section-title mb-0">
            <span><i class="bi bi-box-seam me-1"></i>Tovarlar</span>
            <span class="badge bg-light text-dark">{{ $chiqim->tafsilot->count() }} ta</span>
        </div>
        <div class="bank-wrap mb-2">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th class="tl" style="width:36px">#</th>
                        <th class="tl">Tovar</th>
                        <th style="width:130px">Miqdor</th>
                        <th style="width:140px">Narx</th>
                        <th style="width:150px">Jami</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chiqim->tafsilot as $i => $t)
                    <tr>
                        <td class="tl text-muted">{{ $i+1 }}</td>
                        <td class="tl">
                            <span class="fw-medium">{{ $t->tovar?->nomi }}</span>
                            <span class="text-muted small d-block">{{ $t->tovar?->guruh?->nomi }}</span>
                        </td>
                        <td class="num">{{ $t->miqdor }} {{ $t->tovar?->birlik }}</td>
                        <td class="num text-muted">{{ number_format($t->narx,0,'.',' ') }}</td>
                        <td class="num fw-bold text-danger">{{ number_format($t->jami_summa,0,'.',' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="tl" colspan="4">Jami:</td>
                        <td class="num">{{ number_format($chiqim->umumiy_summa,0,'.',' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
