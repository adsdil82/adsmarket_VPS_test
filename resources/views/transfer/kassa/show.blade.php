@extends('layouts.app')
@section('title', 'Kassa transferi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('transfer.kassa.index') }}">Kassa transferlari</a></li>
    <li class="breadcrumb-item active">{{ $transfer->transfer_raqam }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1"><i class="bi bi-cash-coin me-2 text-primary"></i>{{ $transfer->transfer_raqam }}</h5>
        <span class="badge bg-{{ $transfer->holat_rangi }} fs-6">{{ $transfer->holat }}</span>
    </div>
    <div class="d-flex gap-2">
        @if($transfer->holat === "yuborildi")
            @if(Auth::user()->isAdmin() || Auth::user()->filial_id == $transfer->to_filial_id)
            <form method="POST" action="{{ route("transfer.kassa.qabul",$transfer) }}" style="display:inline">
                @csrf <button class="btn btn-sm btn-success"><i class="bi bi-check2-circle me-1"></i>Qabul qilish</button>
            </form>
            @endif
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#bekorModal">
                <i class="bi bi-x-circle me-1"></i>Bekor
            </button>
        @endif
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-sm mb-0">
            <tr><td class="text-muted w-40">Transfer raqami</td><td><strong>{{ $transfer->transfer_raqam }}</strong></td></tr>
            <tr><td class="text-muted">Sana</td><td>{{ $transfer->sana->format("d.m.Y") }}</td></tr>
            <tr><td class="text-muted">Jo'natuvchi</td><td>{{ $transfer->fromFilial->nomi }} — {{ $transfer->fromKassa->nomi }}</td></tr>
            <tr><td class="text-muted">Qabul qiluvchi</td><td>{{ $transfer->toFilial->nomi }} — {{ $transfer->toKassa->nomi }}</td></tr>
            <tr><td class="text-muted">Summa</td><td>
                <strong class="fs-5">{{ number_format($transfer->summa,0,"."," ") }} {{ $transfer->valyuta }}</strong>
                @if($transfer->valyuta !== "UZS")
                <br><small class="text-muted">≈ {{ number_format($transfer->summa_uzs,0,"."," ") }} so'm (kurs: {{ $transfer->kurs }})</small>
                @endif
            </td></tr>
            @if($transfer->sabab)<tr><td class="text-muted">Sabab</td><td>{{ $transfer->sabab }}</td></tr>@endif
            @if($transfer->izoh)<tr><td class="text-muted">Izoh</td><td>{{ $transfer->izoh }}</td></tr>@endif
            <tr><td class="text-muted">Xodim</td><td>{{ $transfer->xodim->ism_familiya }}</td></tr>
            @if($transfer->tasdiqlagan)
            <tr><td class="text-muted">Tasdiqlagan</td><td>{{ $transfer->tasdiqlagan->ism_familiya }} — {{ $transfer->tasdiqlangan_vaqt?->format("d.m.Y H:i") }}</td></tr>
            @endif
        </table>
        </div>
    </div>
</div>
<div class="modal fade" id="bekorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold mb-0">Transferni bekor qilish</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route("transfer.kassa.bekor",$transfer) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">Bekor qilishda pul jo'natuvchi kassaga qaytariladi!</div>
                    <label class="form-label fw-medium">Sabab <span class="text-danger">*</span></label>
                    <textarea name="sabab" class="form-control" rows="3" required minlength="5" placeholder="Bekor qilish sababi..."></textarea>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Yopish</button>
                    <button type="submit" class="btn btn-danger">Ha, bekor qilish</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
