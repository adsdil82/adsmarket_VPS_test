@extends('layouts.app')
@section('title', 'Transfer #' . ($transfer->transfer_raqam ?? $transfer->id))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('transfer.tovar.index') }}">Tovar transferlari</a></li>
    <li class="breadcrumb-item active">{{ $transfer->transfer_raqam ?? "T-{$transfer->id}" }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">
            <i class="bi bi-box-seam me-2 text-success"></i>
            Transfer {{ $transfer->transfer_raqam ?? "T-{$transfer->id}" }}
        </h5>
        <span class="badge bg-{{ $transfer->holat_rangi }} fs-6">
            {{ match($transfer->holat) {
                'yuborildi'=>'Yuborildi','qabul_qilindi'=>'Qabul qilindi',
                'bekor'=>'Bekor','qoralama'=>'Qoralama',default=>$transfer->holat
            } }}
        </span>
    </div>

    {{-- Amallar --}}
    <div class="d-flex gap-2 flex-wrap">
        @if($transfer->holat === 'yuborildi')
            {{-- Faqat qabul qiluvchi filial yoki admin --}}
            @if(Auth::user()->isAdmin() || Auth::user()->filial_id == $transfer->to_filial_id)
            <form method="POST" action="{{ route('transfer.tovar.qabul', $transfer) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-check2-circle me-1"></i>Qabul qilish
                </button>
            </form>
            @endif

            {{-- Bekor qilish modal trigger --}}
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#bekorModal">
                <i class="bi bi-x-circle me-1"></i>Bekor qilish
            </button>
        @endif
    </div>
</div>

{{-- Info --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Transfer ma'lumotlari</h6>
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted w-50">Transfer raqami</td><td><strong>{{ $transfer->transfer_raqam ?? "T-{$transfer->id}" }}</strong></td></tr>
                    <tr><td class="text-muted">Sana</td><td>{{ $transfer->sana->format('d.m.Y') }}</td></tr>
                    <tr><td class="text-muted">Jo'natuvchi</td><td>
                        <span class="badge bg-secondary">{{ $transfer->fromFilial->kod }}</span>
                        {{ $transfer->fromFilial->nomi }}
                        @if($fromOmbor) <br><small class="text-muted">Ombor: {{ $fromOmbor->nomi }}</small>@endif
                    </td></tr>
                    <tr><td class="text-muted">Qabul qiluvchi</td><td>
                        <span class="badge bg-primary">{{ $transfer->toFilial->kod }}</span>
                        {{ $transfer->toFilial->nomi }}
                        @if($toOmbor) <br><small class="text-muted">Ombor: {{ $toOmbor->nomi }}</small>@endif
                    </td></tr>
                    <tr><td class="text-muted">Yaratgan xodim</td><td>{{ $transfer->xodim->ism_familiya }}</td></tr>
                    @if($transfer->tasdiqlagan)
                    <tr><td class="text-muted">Tasdiqlagan</td><td>{{ $transfer->tasdiqlagan->ism_familiya }}<br>
                        <small class="text-muted">{{ $transfer->tasdiqlangan_vaqt?->format('d.m.Y H:i') }}</small></td></tr>
                    @endif
                    @if($transfer->sabab)
                    <tr><td class="text-muted">Sabab</td><td class="text-danger">{{ $transfer->sabab }}</td></tr>
                    @endif
                    @if($transfer->izoh)
                    <tr><td class="text-muted">Izoh</td><td>{{ $transfer->izoh }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header py-2"><h6 class="mb-0 small fw-bold">Tovarlar tarkibi</h6></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Tovar</th><th class="text-end">Miqdor</th><th class="text-end">Narx</th><th class="text-end">Jami</th></tr>
                    </thead>
                    <tbody>
                        @foreach($transfer->tafsilot as $t)
                        <tr>
                            <td class="small">{{ $t->tovar->nomi ?? "ID:{$t->tovar_id}" }}</td>
                            <td class="text-end small">{{ $t->miqdor }} {{ $t->tovar->birlik ?? 'dona' }}</td>
                            <td class="text-end small text-muted">{{ number_format($t->narx,0,'.',' ') }}</td>
                            <td class="text-end small fw-medium">{{ number_format($t->miqdor * $t->narx,0,'.',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Jami:</td>
                            <td class="text-end">{{ number_format($transfer->tafsilot->sum(fn($t)=>$t->miqdor*$t->narx),0,'.',' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Bekor qilish modali --}}
<div class="modal fade" id="bekorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-x-circle me-2"></i>Transferni bekor qilish</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('transfer.tovar.bekor', $transfer) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small">
                        Bekor qilishda tovarlar jo'natuvchi omborga qaytariladi!
                    </div>
                    <label class="form-label fw-medium">Sabab <span class="text-danger">*</span></label>
                    <textarea name="sabab" class="form-control" rows="3" required minlength="5"
                              placeholder="Bekor qilish sababi..."></textarea>
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
