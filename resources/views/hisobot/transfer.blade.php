@extends('layouts.app')
@section('title', 'Transfer hisobotlari')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Transfer hisobotlari</li>
@endsection

@push('styles')
<style>
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:8px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }

.tr-tabs { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px; }
.tr-tab { border:1px solid #93c5fd; border-radius:20px; padding:5px 14px; font-size:.8rem; font-weight:600;
    color:#1e3a8a; background:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.tr-tab.active { background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-color:#1d4ed8; }
.tr-tab .badge { font-size:.65rem; }

.bft-section-title {
    font-weight:700; color:#fff; background:linear-gradient(90deg,#1e3a8a,#1d4ed8);
    padding:6px 12px; border-radius:6px 6px 0 0; margin-bottom:0; font-size:.85rem;
    display:flex; justify-content:space-between; align-items:center;
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
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-arrow-left-right" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Transfer hisobotlari</span>
        </div>
        <input type="hidden" name="tur" value="{{ $tur }}">
        <div>
            <input type="date" name="dan_sana" class="form-control" style="width:160px" value="{{ $danSana }}">
        </div>
        <div>
            <input type="date" name="gacha_sana" class="form-control" style="width:160px" value="{{ $gachaSana }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <select name="filial_id" class="form-select" style="width:180px">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Ko'rsatish</button>
            <a href="{{ route('hisobotlar.transfer') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        </div>
    </form>
</div>

{{-- Tablar --}}
<div class="tr-tabs">
    <a class="tr-tab {{ $tur==='tovar'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tur'=>'tovar']) }}">
        <i class="bi bi-box"></i>Tovar transferlari
        <span class="badge bg-secondary">{{ $tovarTransferlar->count() }}</span>
    </a>
    <a class="tr-tab {{ $tur==='kassa'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tur'=>'kassa']) }}">
        <i class="bi bi-cash-coin"></i>Kassa transferlari
        <span class="badge bg-secondary">{{ $kassaTransferlar->count() }}</span>
    </a>
    <a class="tr-tab {{ $tur==='xodim'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tur'=>'xodim']) }}">
        <i class="bi bi-person-gear"></i>Xodim tayinlash
        <span class="badge bg-secondary">{{ $xodimTayinlash->count() }}</span>
    </a>
    <a class="tr-tab {{ $tur==='filial'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tur'=>'filial']) }}">
        <i class="bi bi-building-gear"></i>Filial ko'chirish
        <span class="badge bg-secondary">{{ $filialKochirish->count() }}</span>
    </a>
    <a class="tr-tab {{ $tur==='tulov_tur'?'active':'' }}" href="{{ request()->fullUrlWithQuery(['tur'=>'tulov_tur']) }}">
        <i class="bi bi-credit-card"></i>To'lov turlari
    </a>
</div>

{{-- ── Tovar transferlari ─────────────────────────────────────── --}}
@if($tur==='tovar')
@php $jami = $tovarTransferlar->flatMap->tafsilot->sum(fn($t) => $t->miqdor * $t->narx); @endphp
<div class="bft-section-title">
    <span>Tovar transferlari</span>
    <span>Jami: <strong>{{ number_format($jami,0,'.',' ') }} so'm</strong></span>
</div>
<div class="bank-wrap shadow-sm mb-3">
    <table class="bank-table">
        <thead>
            <tr><th class="tl" style="width:40px">#</th><th class="tl">Raqam</th><th class="tl">Sana</th><th class="tl">Jo'natuvchi</th><th class="tl">Qabul qiluvchi</th><th class="tl">Tovarlar</th><th class="tl">Holat</th></tr>
        </thead>
        <tbody>
            @forelse($tovarTransferlar as $t)
            <tr>
                <td class="tl text-muted">{{ $t->id }}</td>
                <td class="tl fw-semibold">{{ $t->transfer_raqam ?? '—' }}</td>
                <td class="tl text-muted">{{ $t->created_at->format('d.m.Y') }}</td>
                <td class="tl"><span class="badge bg-danger bg-opacity-75">{{ $t->fromFilial?->kod }}</span></td>
                <td class="tl"><span class="badge bg-success bg-opacity-75">{{ $t->toFilial?->kod }}</span></td>
                <td class="tl text-muted">{{ $t->tafsilot->count() }} ta tovar</td>
                <td class="tl"><span class="badge bg-{{ $t->holat_rangi }}">{{ $t->holat }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- ── Kassa transferlari ─────────────────────────────────────── --}}
@if($tur==='kassa')
<div class="bft-section-title">
    <span>Kassa transferlari</span>
    <span>Jami pul harakati: <strong>{{ number_format($kassaTransferlar->sum('summa_uzs'),0,'.',' ') }} so'm</strong></span>
</div>
<div class="bank-wrap shadow-sm mb-3">
    <table class="bank-table">
        <thead>
            <tr><th class="tl" style="width:40px">#</th><th class="tl">Raqam</th><th class="tl">Sana</th><th class="tl">Jo'natuvchi</th><th class="tl">Qabul qiluvchi</th><th>Summa</th><th class="tl">Holat</th></tr>
        </thead>
        <tbody>
            @forelse($kassaTransferlar as $t)
            <tr>
                <td class="tl text-muted">{{ $t->id }}</td>
                <td class="tl fw-semibold">{{ $t->transfer_raqam }}</td>
                <td class="tl text-muted">{{ $t->sana->format('d.m.Y') }}</td>
                <td class="tl"><span class="badge bg-danger bg-opacity-75">{{ $t->fromFilial?->kod }}</span></td>
                <td class="tl"><span class="badge bg-success bg-opacity-75">{{ $t->toFilial?->kod }}</span></td>
                <td class="num fw-bold">{{ number_format($t->summa_uzs,0,'.',' ') }}</td>
                <td class="tl"><span class="badge bg-{{ $t->holat_rangi }}">{{ $t->holat }}</span></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- ── Xodim tayinlash ─────────────────────────────────────────── --}}
@if($tur==='xodim')
<div class="bft-section-title"><span>Xodim tayinlash</span></div>
<div class="bank-wrap shadow-sm mb-3">
    <table class="bank-table">
        <thead>
            <tr><th class="tl">Sana</th><th class="tl">Shartnoma</th><th class="tl">Eski xodim</th><th class="tl">Yangi xodim</th><th class="tl">O'zgartirgan</th><th class="tl">Sabab</th></tr>
        </thead>
        <tbody>
            @forelse($xodimTayinlash as $t)
            <tr>
                <td class="tl text-muted">{{ $t->created_at->format('d.m.Y') }}</td>
                <td class="tl">
                    @if($t->shartnoma)
                    <a href="{{ route('kreditlar.show', $t->shartnoma_id) }}" class="fw-semibold text-decoration-none" style="color:#1d4ed8">
                        {{ $t->shartnoma->shartnoma_raqam }}
                    </a>
                    @else<span class="text-muted">O'chirilgan</span>
                    @endif
                </td>
                <td class="tl text-danger">{{ $t->eskiXodim?->ism_familiya ?? '—' }}</td>
                <td class="tl text-success fw-bold">{{ $t->yangiXodim?->ism_familiya }}</td>
                <td class="tl text-muted">{{ $t->ozgartirgan?->ism_familiya }}</td>
                <td class="tl text-muted">{{ $t->sabab }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- ── Filial ko'chirish ───────────────────────────────────────── --}}
@if($tur==='filial')
<div class="bft-section-title"><span>Filial ko'chirish</span></div>
<div class="bank-wrap shadow-sm mb-3">
    <table class="bank-table">
        <thead>
            <tr><th class="tl">Sana</th><th class="tl">Shartnoma</th><th class="tl">Eski filial</th><th class="tl">Yangi filial</th><th class="tl">O'zgartirgan</th><th class="tl">Sabab</th></tr>
        </thead>
        <tbody>
            @forelse($filialKochirish as $t)
            <tr>
                <td class="tl text-muted">{{ $t->created_at->format('d.m.Y') }}</td>
                <td class="tl">
                    @if($t->shartnoma)
                    <a href="{{ route('kreditlar.show', $t->shartnoma_id) }}" class="fw-semibold text-decoration-none" style="color:#1d4ed8">
                        {{ $t->shartnoma->shartnoma_raqam }}
                    </a>
                    @else<span class="text-muted">O'chirilgan</span>
                    @endif
                </td>
                <td class="tl"><span class="badge bg-secondary">{{ $t->eskiFilial?->kod }}</span></td>
                <td class="tl"><span class="badge bg-primary">{{ $t->yangiFilial?->kod }}</span></td>
                <td class="tl text-muted">{{ $t->ozgartirgan?->ism_familiya }}</td>
                <td class="tl text-muted">{{ $t->sabab }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- ── To'lov turlari statistikasi ────────────────────────────── --}}
@if($tur==='tulov_tur')
<div class="row g-3">
    <div class="col-md-6">
        <div class="bft-section-title"><span><span class="badge bg-success me-2">Yangi</span>Faol to'lov turlari</span></div>
        <div class="bank-wrap shadow-sm">
            <table class="bank-table">
                <thead>
                    <tr><th class="tl">Nomi</th><th class="tl">Kategoriya</th><th>Soni</th><th>Jami summa</th></tr>
                </thead>
                <tbody>
                    @foreach($tulovTurlari->where('is_legacy', false) as $tt)
                    <tr>
                        <td class="tl fw-semibold">{{ $tt->nomi }}</td>
                        <td class="tl"><span class="badge bg-info bg-opacity-50 text-dark">{{ $tt->kategoriya }}</span></td>
                        <td class="num">{{ $tt->jami_count ?? 0 }}</td>
                        <td class="num">{{ number_format($tt->jami_summa ?? 0, 0, '.', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="bft-section-title"><span><span class="badge bg-secondary me-2">Legacy</span>Eski to'lov turlari (top 20)</span></div>
        <div class="bank-wrap shadow-sm" style="max-height:420px">
            <table class="bank-table">
                <thead>
                    <tr><th class="tl">Nomi</th><th>Soni</th><th>Jami summa</th></tr>
                </thead>
                <tbody>
                    @foreach($tulovTurlari->where('is_legacy', true)->sortByDesc('jami_count')->take(20) as $tt)
                    <tr>
                        <td class="tl text-muted">{{ $tt->nomi }}</td>
                        <td class="num">{{ $tt->jami_count ?? 0 }}</td>
                        <td class="num">{{ number_format($tt->jami_summa ?? 0, 0, '.', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection
