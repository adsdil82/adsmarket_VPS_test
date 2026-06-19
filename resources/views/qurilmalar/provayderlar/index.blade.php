@extends('layouts.app')
@section('title','Nazorat Providerlari')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.index') }}">Qurilmalar</a></li>
<li class="breadcrumb-item active">Provayderlar</li>
@endsection
@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-plug me-2"></i>Nazorat Providerlari</h5>
    <div class="d-flex gap-2 align-items-center">
        @php $autoLock = config('device_control.auto_lock_enabled', false); @endphp
        <span class="badge {{ $autoLock ? 'bg-danger' : 'bg-success' }} py-2 px-3">
            Auto Lock: {{ $autoLock ? 'YOQILGAN ⚠️' : 'O\'CHIQ ✓' }}
        </span>
    </div>
</div>

@if(!config('device_control.enabled', false))
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>DEVICE_CONTROL_ENABLED=false</strong> — Modul hozircha nofaol.
    <code>.env</code> faylga <code>DEVICE_CONTROL_ENABLED=true</code> qo'shing.
</div>
@endif

<div class="row g-3">
    @foreach($provayderlar as $p)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-top:3px solid {{ $p->faol ? '#22c55e' : '#6b7280' }} !important">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="fw-bold mb-0">{{ $p->nomi }}</h6>
                    <span class="badge bg-{{ $p->faol ? 'success' : 'secondary' }}">{{ $p->faol ? 'Faol' : 'Nofaol' }}</span>
                </div>
                <div class="text-muted small mb-2">{{ $p->tavsif }}</div>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <span class="badge bg-light text-dark border" style="font-size:.65rem">{{ $p->tur }}</span>
                    @if($p->mock_rejim)<span class="badge bg-warning text-dark" style="font-size:.65rem">Mock rejim</span>@endif
                    @if($p->lock_qollab)<span class="badge bg-danger bg-opacity-15 text-danger" style="font-size:.65rem">Lock</span>@endif
                    @if($p->unlock_qollab)<span class="badge bg-success bg-opacity-15 text-success" style="font-size:.65rem">Unlock</span>@endif
                    @if($p->ogoh_qollab)<span class="badge bg-warning bg-opacity-20 text-dark" style="font-size:.65rem">Ogoh</span>@endif
                    @if($p->sinx_qollab)<span class="badge bg-info bg-opacity-15 text-info" style="font-size:.65rem">Sinx</span>@endif
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('qurilma-provayderlar.toggle',$p) }}">
                        @csrf
                        <button class="btn btn-{{ $p->faol ? 'outline-secondary' : 'outline-success' }} btn-sm">
                            {{ $p->faol ? 'O\'chirish' : 'Faollashtirish' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('qurilma-provayderlar.toggle-mock',$p) }}">
                        @csrf
                        <button class="btn btn-{{ $p->mock_rejim ? 'warning' : 'outline-secondary' }} btn-sm" title="Mock rejim">
                            {{ $p->mock_rejim ? '🧪 Mock' : '🌐 Real' }}
                        </button>
                    </form>
                    <a href="{{ route('qurilma-provayderlar.sozlamalar',$p) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-gear"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
