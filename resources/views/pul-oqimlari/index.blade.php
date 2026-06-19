@extends('layouts.app')
@section('title','Pul Oqimlari — CashFlow')
@section('breadcrumb')
<li class="breadcrumb-item active">Pul Oqimlari</li>
@endsection

@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
    <i class="bi bi-check-circle me-1"></i>{{ session('muvaffaqiyat') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-arrow-left-right me-2" style="color:#6366f1"></i>Pul Oqimlari
        <span class="badge bg-secondary bg-opacity-15 text-secondary ms-1" style="font-size:.7rem;font-weight:600">CashFlow</span>
    </h5>
    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki() || Auth::user()->isKassir())
    <div class="d-flex gap-2">
        <a href="{{ route('pul-oqimlari.create', ['yunalish'=>'kirim']) }}" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg me-1"></i>Kirim
        </a>
        <a href="{{ route('pul-oqimlari.create', ['yunalish'=>'chiqim']) }}" class="btn btn-sm btn-danger">
            <i class="bi bi-dash-lg me-1"></i>Chiqim
        </a>
    </div>
    @endif
</div>

{{-- KPI kartalar --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #22c55e !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Jami Kirim</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:#16a34a">
                    {{ number_format($stat['kirim'],0,'.',' ') }}
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem">so'm</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ef4444 !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Jami Chiqim</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:#dc2626">
                    {{ number_format($stat['chiqim'],0,'.',' ') }}
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem">so'm</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid {{ $stat['sof'] >= 0 ? '#3b82f6' : '#f59e0b' }} !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Sof Oqim (Net)</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:{{ $stat['sof'] >= 0 ? '#1d4ed8' : '#b45309' }}">
                    {{ $stat['sof'] >= 0 ? '+' : '' }}{{ number_format($stat['sof'],0,'.',' ') }}
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem">so'm</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #8b5cf6 !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Yozuvlar</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:#7c3aed">{{ number_format($oqimlar->total(),0,'.',' ') }}</div>
                <div class="text-muted mt-1" style="font-size:.7rem">{{ \Carbon\Carbon::parse($danSana)->format('d.m') }} — {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Kategoriya breakdown (chiqim) --}}
@if($chiqimByKat->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="text-muted mb-2" style="font-size:.72rem;font-weight:600;text-transform:uppercase">Chiqim — Kategoriyalar bo'yicha</div>
        <div class="d-flex flex-wrap gap-2">
            @foreach($chiqimByKat->take(8) as $ck)
            @php $kat = $ck->kategoriya; @endphp
            <div class="d-flex align-items-center gap-1" style="font-size:.75rem">
                <span class="fw-600 text-muted">{{ $kat?->kod ?? '—' }}</span>
                <span>{{ Str::limit($kat?->nomi ?? '—', 22) }}</span>
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">{{ number_format($ck->jami,0,'.',' ') }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Filtr --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(Auth::user()->isAdmin())
            <div class="col-sm-2">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filial</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-2">
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-2">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            <div class="col-sm-auto">
                <select name="yunalish" class="form-select form-select-sm">
                    <option value="">Kirim + Chiqim</option>
                    <option value="kirim" {{ request('yunalish')==='kirim'?'selected':'' }}>Faqat Kirim</option>
                    <option value="chiqim" {{ request('yunalish')==='chiqim'?'selected':'' }}>Faqat Chiqim</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select name="kategoriya" class="form-select form-select-sm">
                    <option value="">Barcha kategoriya</option>
                    @foreach($kategoriyalar as $kat)
                        <optgroup label="{{ $kat->kod }} — {{ $kat->nomi }}">
                            @foreach($kat->bolalar as $b)
                            <option value="{{ $b->id }}" {{ request('kategoriya')==$b->id?'selected':'' }}>{{ $b->kod }} — {{ $b->nomi }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="kassa_id" class="form-select form-select-sm">
                    <option value="">Barcha kassa</option>
                    @foreach($kassalar as $k)
                        <option value="{{ $k->id }}" {{ request('kassa_id')==$k->id?'selected':'' }}>{{ $k->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('pul-oqimlari.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Jadval --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.85rem">
            <thead class="table-light">
                <tr>
                    <th style="width:80px">Sana</th>
                    <th style="width:60px">Tur</th>
                    <th>Kategoriya</th>
                    <th>Izoh</th>
                    <th>Kassa</th>
                    @if(Auth::user()->isAdmin())<th>Xodim</th>@endif
                    <th class="text-end" style="width:140px">Summa</th>
                    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())<th style="width:60px"></th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($oqimlar as $o)
                @php
                    $isKirim = $o->yunalish === 'kirim';
                    $kat = $o->kategoriya;
                    $rangMap = ['green'=>'success','red'=>'danger','orange'=>'warning','blue'=>'primary','purple'=>'purple','yellow'=>'warning','gray'=>'secondary'];
                    $bsRang = $rangMap[$kat?->rang ?? 'gray'] ?? 'secondary';
                @endphp
                <tr style="border-left:3px solid {{ $isKirim ? '#22c55e' : '#ef4444' }}">
                    <td class="text-nowrap text-muted small">{{ $o->sana->format('d.m.Y') }}</td>
                    <td>
                        @if($isKirim)
                            <span class="badge" style="background:#dcfce7;color:#15803d;font-size:.7rem">↑ Kirim</span>
                        @else
                            <span class="badge" style="background:#fee2e2;color:#dc2626;font-size:.7rem">↓ Chiqim</span>
                        @endif
                    </td>
                    <td>
                        @if($kat)
                            @if($kat->ota)
                            <span class="text-muted small">{{ $kat->ota->nomi }} /</span><br>
                            @endif
                            <span class="badge bg-{{ $bsRang }} bg-opacity-15 text-dark" style="font-size:.7rem">
                                {{ $kat->kod }} — {{ $kat->nomi }}
                            </span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ Str::limit($o->izoh, 45) }}</td>
                    <td class="text-muted small">{{ $o->kassa?->nomi ?? '—' }}</td>
                    @if(Auth::user()->isAdmin())
                    <td class="text-muted small">{{ $o->xodim?->ism_familiya }}</td>
                    @endif
                    <td class="text-end fw-bold text-nowrap" style="color:{{ $isKirim ? '#16a34a' : '#dc2626' }}">
                        {{ $isKirim ? '+' : '−' }}{{ number_format($o->summa,0,'.',' ') }}
                    </td>
                    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
                    <td>
                        @if($o->manba_tur === 'manual' || Auth::user()->isAdmin())
                        <a href="{{ route('pul-oqimlari.edit',$o) }}" class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if(Auth::user()->isAdmin())
                        <form method="POST" action="{{ route('pul-oqimlari.destroy',$o) }}" class="d-inline"
                              onsubmit="return confirm('Bekor qilish?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger py-0 px-1" style="font-size:.7rem">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                        @endif
                        @else
                            <span class="text-muted" style="font-size:.7rem" title="Auto yozuv">🔗</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-arrow-left-right fs-3 d-block mb-2 opacity-25"></i>
                    Tanlangan davr uchun operatsiyalar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($oqimlar->hasPages())
    <div class="card-footer">{{ $oqimlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
