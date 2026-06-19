@extends('layouts.app')
@section('title','Qurilmalar nazorati')
@section('breadcrumb')
<li class="breadcrumb-item active">Qurilmalar nazorati</li>
@endsection

@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
    <i class="bi bi-check-circle me-1"></i>{{ session('muvaffaqiyat') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('xato'))
<div class="alert alert-danger alert-dismissible fade show py-2 mb-3">
    <i class="bi bi-exclamation-triangle me-1"></i>{{ session('xato') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-phone me-2" style="color:#6366f1"></i>Qurilmalar nazorati
        <span class="badge bg-secondary bg-opacity-15 text-secondary ms-1" style="font-size:.7rem">Device Control</span>
    </h5>
    @if(Auth::user()->isMenejerYoki())
    <a href="{{ route('qurilmalar.create') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Qurilma qo'shish
    </a>
    @endif
</div>

{{-- Holat statistikasi --}}
<div class="row g-2 mb-3">
    @php
        $statRanglari = ['in_stock'=>'secondary','reserved'=>'info','sold'=>'primary','active'=>'success','warning'=>'warning','locked'=>'danger','unlock_pending'=>'warning','released'=>'success','returned'=>'secondary','lost'=>'dark','failed'=>'danger'];
        $jami = array_sum($holatStat);
    @endphp
    <div class="col-6 col-sm-4 col-md-2">
        <a href="{{ route('qurilmalar.index') }}" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body py-2 text-center">
                <div class="fw-bold fs-5">{{ $jami }}</div>
                <div class="text-muted small">Jami</div>
            </div>
        </a>
    </div>
    @foreach(\App\Models\Qurilma::$holatlar as $holat => $nom)
        @if(isset($holatStat[$holat]) && $holatStat[$holat] > 0)
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('qurilmalar.index', ['holat'=>$holat]) }}" class="card border-0 shadow-sm text-decoration-none h-100"
               style="border-top:3px solid var(--bs-{{ $statRanglari[$holat] ?? 'secondary' }}) !important">
                <div class="card-body py-2 text-center">
                    <div class="fw-bold fs-5 text-{{ $statRanglari[$holat] ?? 'secondary' }}">{{ $holatStat[$holat] }}</div>
                    <div class="text-muted small" style="font-size:.7rem">{{ $nom }}</div>
                </div>
            </a>
        </div>
        @endif
    @endforeach
</div>

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
                <select name="holat" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    @foreach(\App\Models\Qurilma::$holatlar as $h=>$n)
                        <option value="{{ $h }}" {{ request('holat')===$h?'selected':'' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="text" name="brend" class="form-control form-control-sm" placeholder="Brend..." value="{{ request('brend') }}">
            </div>
            <div class="col-sm-3">
                <input type="text" name="qidiruv" class="form-control form-control-sm" placeholder="IMEI, model, mijoz..." value="{{ request('qidiruv') }}">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('qurilmalar.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
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
                    <th>#</th>
                    <th>Qurilma</th>
                    <th>IMEI</th>
                    <th>Mijoz / Shartnoma</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th>Holat</th>
                    <th>Qo'shilgan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($qurilmalar as $q)
                <tr>
                    <td class="text-muted small">{{ $q->id }}</td>
                    <td>
                        <div class="fw-medium">{{ $q->toliq_nomi }}</div>
                        @if($q->rang || $q->xotira)
                        <div class="text-muted small">{{ collect([$q->rang,$q->xotira])->filter()->implode(' | ') }}</div>
                        @endif
                    </td>
                    <td class="text-muted small font-monospace">
                        {{ $q->imei1 ?? '—' }}
                        @if($q->imei2)<br><small>{{ $q->imei2 }}</small>@endif
                    </td>
                    <td>
                        @if($q->mijoz)
                        <a href="{{ route('mijozlar.show',$q->mijoz) }}" class="text-decoration-none small">
                            {{ $q->mijoz->familiya }} {{ $q->mijoz->ism }}
                        </a>
                        @endif
                        @if($q->kredit)
                        <div><a href="{{ route('kreditlar.show',$q->kredit) }}" class="text-decoration-none text-muted small">
                            {{ $q->kredit->shartnoma_raqam }}
                        </a></div>
                        @endif
                        @if(!$q->mijoz && !$q->kredit)<span class="text-muted">—</span>@endif
                    </td>
                    @if(Auth::user()->isAdmin())
                    <td class="text-muted small">{{ $q->filial?->kod ?? '—' }}</td>
                    @endif
                    <td>
                        <span class="badge bg-{{ $q->holat_rangi }}">{{ $q->holat_nomi }}</span>
                    </td>
                    <td class="text-muted small">{{ $q->qoshilgan_sana?->format('d.m.Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('qurilmalar.show',$q) }}" class="btn btn-outline-secondary btn-sm py-0 px-2">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-phone fs-3 d-block mb-2 opacity-25"></i>
                    Qurilmalar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($qurilmalar->hasPages())
    <div class="card-footer">{{ $qurilmalar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
