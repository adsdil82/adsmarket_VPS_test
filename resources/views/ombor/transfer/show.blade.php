@extends('layouts.app')
@section('title','Transfer #'.$transfer->id)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transfer</a></li>
<li class="breadcrumb-item active">#{{ $transfer->id }}</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Transfer ma'lumotlari</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">ID</td><td>#{{ $transfer->id }}</td></tr>
                    <tr><td class="text-muted">Sana</td><td>{{ $transfer->sana->format('d.m.Y') }}</td></tr>
                    <tr><td class="text-muted">Jo'natuvchi</td>
                        <td><span class="badge bg-danger">{{ $transfer->fromFilial?->nomi }}</span></td></tr>
                    <tr><td class="text-muted">Qabul qiluvchi</td>
                        <td><span class="badge bg-success">{{ $transfer->toFilial?->nomi }}</span></td></tr>
                    <tr><td class="text-muted">Xodim</td><td>{{ $transfer->xodim?->ism_familiya }}</td></tr>
                    <tr><td class="text-muted">Holat</td>
                        <td><span class="badge bg-{{ $transfer->holat_rangi }}">{{ $transfer->holat }}</span></td></tr>
                    @if($transfer->tasdiqlagan)
                    <tr><td class="text-muted">Tasdiqlagan</td><td>{{ $transfer->tasdiqlagan?->ism_familiya }}</td></tr>
                    <tr><td class="text-muted">Vaqt</td><td class="small">{{ $transfer->tasdiqlangan_vaqt?->format('d.m.Y H:i') }}</td></tr>
                    @endif
                </table>
                @if($transfer->izoh)
                <div class="alert alert-light mt-2 mb-0 small">{{ $transfer->izoh }}</div>
                @endif

                @if($transfer->holat === 'kutilmoqda')
                <div class="mt-3 d-flex gap-2">
                    @if(Auth::user()->isAdmin() || Auth::user()->filial_id === $transfer->to_filial_id)
                    <form method="POST" action="{{ route('transfer.tasdiqla',$transfer) }}" class="flex-grow-1">
                        @csrf
                        <button class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Qabul qilish</button>
                    </form>
                    @endif
                    @if(Auth::user()->isAdmin() || Auth::user()->filial_id === $transfer->from_filial_id)
                    <form method="POST" action="{{ route('transfer.bekor',$transfer) }}"
                          onsubmit="return confirm('Bekor qilinsinmi?')">
                        @csrf
                        <button class="btn btn-outline-danger"><i class="bi bi-x"></i></button>
                    </form>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Tovarlar ({{ $transfer->tafsilot->count() }} ta)</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Tovar</th><th>Guruh</th><th class="text-end">Miqdor</th></tr>
                    </thead>
                    <tbody>
                        @foreach($transfer->tafsilot as $i => $t)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td class="fw-medium">{{ $t->tovar?->nomi }}</td>
                            <td><small class="text-muted">{{ $t->tovar?->guruh?->nomi ?? '—' }}</small></td>
                            <td class="text-end">{{ $t->miqdor }} {{ $t->tovar?->birlik }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
