@extends('layouts.app')
@section('title', 'POS Dashboard')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
.pos-card { background:#fff; border:1px solid #d7e2f5; border-radius:8px; padding:12px 14px; }
.pos-card .label { font-size:.68rem; color:#7a89a8; text-transform:uppercase; letter-spacing:.03em; font-weight:700; }
.pos-card .value { font-size:1.25rem; font-weight:800; color:#0f172a; margin-top:2px; }
.pos-card .value.green { color:#15803d; }
.pos-card .value.blue { color:#1d4ed8; }
.pos-card .value.amber { color:#b45309; }
.pos-card .value.red { color:#b91c1c; }

.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.03em; text-transform:uppercase; padding:6px 8px; white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:5px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; }
.bank-wrap { overflow:auto; max-height:320px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.panel-title { font-weight:800; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb; padding:6px 12px; font-size:.82rem; margin-bottom:0; border-radius:6px 6px 0 0; }
.badge-kam { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; font-size:.65rem; font-weight:800; padding:2px 6px; border-radius:4px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-grid-1x2 me-2" style="color:#1d4ed8"></i>POS Dashboard</h5>
    <form method="GET" class="d-flex gap-2">
        @if(Auth::user()->isAdmin())
        <select name="filial_id" class="form-select form-select-sm" onchange="this.form.submit()" style="width:180px">
            <option value="">Barcha filiallar</option>
            @foreach($filiallar as $f)
            <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
            @endforeach
        </select>
        @endif
        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success"><i class="bi bi-cash-register me-1"></i>Kassaga o'tish</a>
    </form>
</div>

{{-- ── Kartochkalar ────────────────────────────────────────────── --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Bugungi savdo</div><div class="value blue">{{ number_format($bugunSotuv,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Naqd tushum</div><div class="value green">{{ number_format($naqdTushum,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Karta/terminal</div><div class="value blue">{{ number_format($kartaTushum,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Qarzga sotuv (nasiya)</div><div class="value amber">{{ number_format($qarzgaSotuv,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Qaytim summasi</div><div class="value red">{{ number_format($qaytimSumma,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Sof tushum</div><div class="value green">{{ number_format($sofTushum,0,'.',' ') }}</div></div></div>
    <div class="col-6 col-md-3 col-lg-2"><div class="pos-card"><div class="label">Cheklar soni</div><div class="value">{{ $chekSoni }}</div></div></div>
</div>

<div class="row g-3">
    {{-- Oxirgi sotuvlar --}}
    <div class="col-lg-7">
        <div class="panel-title"><i class="bi bi-receipt me-1"></i>Oxirgi sotuvlar</div>
        <div class="bank-wrap">
            <table class="bank-table">
                <thead><tr><th class="tl">Chek №</th><th class="tl">Sana</th><th class="tl">Kassir</th><th class="tl">Filial</th><th>Summa</th><th class="tl">To'lov</th></tr></thead>
                <tbody>
                    @forelse($oxirgiSotuvlar as $s)
                    <tr>
                        <td class="tl"><a href="{{ route('pos.chek',$s) }}">{{ $s->check_raqam }}</a></td>
                        <td class="tl">{{ $s->created_at->format('d.m.Y H:i') }}</td>
                        <td class="tl">{{ $s->xodim->ism_familiya ?? '—' }}</td>
                        <td class="tl">{{ $s->filial->nomi ?? '—' }}</td>
                        <td class="num">{{ number_format($s->jami_tolov,0,'.',' ') }}</td>
                        <td class="tl">{{ $s->tolov_turi }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kam qoldiq --}}
    <div class="col-lg-5">
        <div class="panel-title"><i class="bi bi-exclamation-triangle me-1"></i>Kam qoldiqdagi tovarlar</div>
        <div class="bank-wrap">
            <table class="bank-table">
                <thead><tr><th class="tl">Tovar</th><th>Qoldiq</th><th>Min</th></tr></thead>
                <tbody>
                    @forelse($kamQoldiq as $t)
                    <tr>
                        <td class="tl">{{ $t->nomi }}</td>
                        <td class="num"><span class="badge-kam">{{ $t->qoldiq }} {{ $t->birlik }}</span></td>
                        <td class="num">{{ $t->min_qoldiq }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Kam qoldiqdagi tovar yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Oxirgi qaytimlar --}}
    <div class="col-lg-7">
        <div class="panel-title"><i class="bi bi-arrow-return-left me-1"></i>Oxirgi qaytimlar</div>
        <div class="bank-wrap">
            <table class="bank-table">
                <thead><tr><th class="tl">Qaytim №</th><th class="tl">Asl chek</th><th class="tl">Kassir</th><th>Summa</th></tr></thead>
                <tbody>
                    @forelse($oxirgiQaytimlar as $q)
                    <tr>
                        <td class="tl"><a href="{{ route('pos.qaytim.korish',$q) }}">{{ $q->qaytim_raqami }}</a></td>
                        <td class="tl">{{ $q->sotuv->check_raqam ?? '—' }}</td>
                        <td class="tl">{{ $q->xodim->ism_familiya ?? '—' }}</td>
                        <td class="num">{{ number_format($q->jami_summa,0,'.',' ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Qaytim yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kassirlar bo'yicha tushum --}}
    <div class="col-lg-4">
        <div class="panel-title"><i class="bi bi-people me-1"></i>Kassirlar bo'yicha tushum</div>
        <div class="bank-wrap">
            <table class="bank-table">
                <thead><tr><th class="tl">Kassir</th><th>Chek</th><th>Summa</th></tr></thead>
                <tbody>
                    @forelse($kassirlarKesimi as $k)
                    <tr><td class="tl">{{ $k->ism_familiya }}</td><td class="num">{{ $k->soni }}</td><td class="num">{{ number_format($k->summa,0,'.',' ') }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- To'lov turlari bo'yicha tushum --}}
    <div class="col-lg-4">
        <div class="panel-title"><i class="bi bi-credit-card me-1"></i>To'lov turlari bo'yicha</div>
        <div class="bank-wrap">
            <table class="bank-table">
                <thead><tr><th class="tl">Turi</th><th>Chek</th><th>Summa</th></tr></thead>
                <tbody>
                    @forelse($tolovTurlariKesimi as $t)
                    <tr><td class="tl">{{ $t->tolov_turi }}</td><td class="num">{{ $t->soni }}</td><td class="num">{{ number_format($t->summa,0,'.',' ') }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Eng ko'p sotilganlar --}}
    <div class="col-lg-4">
        <div class="panel-title"><i class="bi bi-star me-1"></i>Bugun eng ko'p sotilganlar</div>
        <div class="bank-wrap">
            <table class="bank-table">
                <thead><tr><th class="tl">Tovar</th><th>Soni</th><th>Summa</th></tr></thead>
                <tbody>
                    @forelse($engKopSotilgan as $t)
                    <tr><td class="tl">{{ $t->nomi }}</td><td class="num">{{ $t->soni }}</td><td class="num">{{ number_format($t->summa,0,'.',' ') }}</td></tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- So'nggi asosiy kassaga topshirishlar --}}
    <div class="col-12">
        <div class="panel-title"><i class="bi bi-arrow-left-right me-1"></i>So'nggi kassa transferlari</div>
        <div class="bank-wrap" style="max-height:220px">
            <table class="bank-table">
                <thead><tr><th class="tl">№</th><th class="tl">Sana</th><th class="tl">Qayerdan</th><th class="tl">Qayerga</th><th>Summa</th><th class="tl">Xodim</th><th class="tl">Holat</th></tr></thead>
                <tbody>
                    @forelse($songgiTopshirishlar as $t)
                    <tr>
                        <td class="tl">{{ $t->transfer_raqam }}</td>
                        <td class="tl">{{ $t->sana?->format('d.m.Y') }}</td>
                        <td class="tl">{{ $t->fromFilial->nomi ?? '—' }}</td>
                        <td class="tl">{{ $t->toFilial->nomi ?? '—' }}</td>
                        <td class="num">{{ number_format($t->summa_uzs,0,'.',' ') }}</td>
                        <td class="tl">{{ $t->xodim->ism_familiya ?? '—' }}</td>
                        <td class="tl"><span class="badge bg-{{ $t->holat_rangi }}">{{ $t->holat }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">Ma'lumot yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
