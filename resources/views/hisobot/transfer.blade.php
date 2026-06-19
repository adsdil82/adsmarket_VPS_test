@extends('layouts.app')
@section('title', 'Transfer hisobotlari')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Transfer hisobotlari</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-arrow-left-right me-2 text-info"></i>Transfer hisobotlari
    </h5>
</div>

{{-- Filtr --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="tur" value="{{ $tur }}">
            <div class="col-sm-3">
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-3">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Ko'rsatish</button>
                <a href="{{ route('hisobotlar.transfer') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Tablar --}}
<ul class="nav nav-tabs mb-3" id="transferTab">
    <li class="nav-item">
        <a class="nav-link {{ $tur==='tovar'?'active':'' }}"
           href="{{ request()->fullUrlWithQuery(['tur'=>'tovar']) }}">
            <i class="bi bi-box me-1"></i>Tovar transferlari
            <span class="badge bg-secondary ms-1">{{ $tovarTransferlar->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tur==='kassa'?'active':'' }}"
           href="{{ request()->fullUrlWithQuery(['tur'=>'kassa']) }}">
            <i class="bi bi-cash-coin me-1"></i>Kassa transferlari
            <span class="badge bg-secondary ms-1">{{ $kassaTransferlar->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tur==='xodim'?'active':'' }}"
           href="{{ request()->fullUrlWithQuery(['tur'=>'xodim']) }}">
            <i class="bi bi-person-gear me-1"></i>Xodim tayinlash
            <span class="badge bg-secondary ms-1">{{ $xodimTayinlash->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tur==='filial'?'active':'' }}"
           href="{{ request()->fullUrlWithQuery(['tur'=>'filial']) }}">
            <i class="bi bi-building-gear me-1"></i>Filial ko'chirish
            <span class="badge bg-secondary ms-1">{{ $filialKochirish->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tur==='tulov_tur'?'active':'' }}"
           href="{{ request()->fullUrlWithQuery(['tur'=>'tulov_tur']) }}">
            <i class="bi bi-credit-card me-1"></i>To'lov turlari
        </a>
    </li>
</ul>

{{-- ── Tovar transferlari ─────────────────────────────────────── --}}
@if($tur==='tovar')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2">
        <strong>Jami tovar summasi:</strong>
        @php
            $jami = $tovarTransferlar->flatMap->tafsilot->sum(fn($t) => $t->miqdor * $t->narx);
        @endphp
        <span class="text-success fw-bold">{{ number_format($jami,0,'.',' ') }} so'm</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Raqam</th><th>Sana</th><th>Jo'natuvchi</th><th>Qabul qiluvchi</th><th>Tovarlar</th><th>Holat</th></tr>
            </thead>
            <tbody>
                @forelse($tovarTransferlar as $t)
                <tr>
                    <td class="text-muted small">{{ $t->id }}</td>
                    <td class="small fw-bold">{{ $t->transfer_raqam ?? '—' }}</td>
                    <td class="small">{{ $t->created_at->format('d.m.Y') }}</td>
                    <td><span class="badge bg-danger bg-opacity-75 small">{{ $t->fromFilial?->kod }}</span></td>
                    <td><span class="badge bg-success bg-opacity-75 small">{{ $t->toFilial?->kod }}</span></td>
                    <td class="small text-muted">{{ $t->tafsilot->count() }} ta tovar</td>
                    <td><span class="badge bg-{{ $t->holat_rangi }} small">{{ $t->holat }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Kassa transferlari ─────────────────────────────────────── --}}
@if($tur==='kassa')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2">
        <strong>Jami pul harakati:</strong>
        <span class="text-primary fw-bold">{{ number_format($kassaTransferlar->sum('summa_uzs'),0,'.',' ') }} so'm</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Raqam</th><th>Sana</th><th>Jo'natuvchi</th><th>Qabul qiluvchi</th><th>Summa</th><th>Holat</th></tr>
            </thead>
            <tbody>
                @forelse($kassaTransferlar as $t)
                <tr>
                    <td class="text-muted small">{{ $t->id }}</td>
                    <td class="small fw-bold">{{ $t->transfer_raqam }}</td>
                    <td class="small">{{ $t->sana->format('d.m.Y') }}</td>
                    <td><span class="badge bg-danger bg-opacity-75 small">{{ $t->fromFilial?->kod }}</span></td>
                    <td><span class="badge bg-success bg-opacity-75 small">{{ $t->toFilial?->kod }}</span></td>
                    <td class="fw-bold small">{{ number_format($t->summa_uzs,0,'.',' ') }}</td>
                    <td><span class="badge bg-{{ $t->holat_rangi }} small">{{ $t->holat }}</span></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Xodim tayinlash ─────────────────────────────────────────── --}}
@if($tur==='xodim')
<div class="card border-0 shadow-sm mb-3">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Sana</th><th>Shartnoma</th><th>Eski xodim</th><th>Yangi xodim</th><th>Ozgartirgan</th><th>Sabab</th></tr>
            </thead>
            <tbody>
                @forelse($xodimTayinlash as $t)
                <tr>
                    <td class="small">{{ $t->created_at->format('d.m.Y') }}</td>
                    <td>
                        @if($t->shartnoma)
                        <a href="{{ route('kreditlar.show', $t->shartnoma_id) }}" class="small fw-bold">
                            {{ $t->shartnoma->shartnoma_raqam }}
                        </a>
                        @else<span class="text-muted small">O'chirilgan</span>
                        @endif
                    </td>
                    <td class="small text-danger">{{ $t->eskiXodim?->ism_familiya ?? '—' }}</td>
                    <td class="small text-success fw-bold">{{ $t->yangiXodim?->ism_familiya }}</td>
                    <td class="small text-muted">{{ $t->ozgartirgan?->ism_familiya }}</td>
                    <td class="small text-muted">{{ $t->sabab }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Filial ko'chirish ───────────────────────────────────────── --}}
@if($tur==='filial')
<div class="card border-0 shadow-sm mb-3">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Sana</th><th>Shartnoma</th><th>Eski filial</th><th>Yangi filial</th><th>Ozgartirgan</th><th>Sabab</th></tr>
            </thead>
            <tbody>
                @forelse($filialKochirish as $t)
                <tr>
                    <td class="small">{{ $t->created_at->format('d.m.Y') }}</td>
                    <td>
                        @if($t->shartnoma)
                        <a href="{{ route('kreditlar.show', $t->shartnoma_id) }}" class="small fw-bold">
                            {{ $t->shartnoma->shartnoma_raqam }}
                        </a>
                        @else<span class="text-muted small">O'chirilgan</span>
                        @endif
                    </td>
                    <td><span class="badge bg-secondary small">{{ $t->eskiFilial?->kod }}</span></td>
                    <td><span class="badge bg-primary small">{{ $t->yangiFilial?->kod }}</span></td>
                    <td class="small text-muted">{{ $t->ozgartirgan?->ism_familiya }}</td>
                    <td class="small text-muted">{{ $t->sabab }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Ma'lumot yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── To'lov turlari statistikasi ────────────────────────────── --}}
@if($tur==='tulov_tur')
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2">
                <span class="badge bg-success me-2">Yangi</span> Faol to'lov turlari
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Nomi</th><th>Kategoriya</th><th>Soni</th><th>Jami summa</th></tr>
                    </thead>
                    <tbody>
                        @foreach($tulovTurlari->where('is_legacy', false) as $tt)
                        <tr>
                            <td class="small fw-bold">{{ $tt->nomi }}</td>
                            <td><span class="badge bg-info bg-opacity-50 text-dark small">{{ $tt->kategoriya }}</span></td>
                            <td class="small">{{ $tt->jami_count ?? 0 }}</td>
                            <td class="small">{{ number_format($tt->jami_summa ?? 0, 0, '.', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2">
                <span class="badge bg-secondary me-2">Legacy</span> Eski to'lov turlari (top 20)
            </div>
            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Nomi</th><th>Soni</th><th>Jami summa</th></tr>
                    </thead>
                    <tbody>
                        @foreach($tulovTurlari->where('is_legacy', true)->sortByDesc('jami_count')->take(20) as $tt)
                        <tr>
                            <td class="small text-muted">{{ $tt->nomi }}</td>
                            <td class="small">{{ $tt->jami_count ?? 0 }}</td>
                            <td class="small">{{ number_format($tt->jami_summa ?? 0, 0, '.', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@endsection