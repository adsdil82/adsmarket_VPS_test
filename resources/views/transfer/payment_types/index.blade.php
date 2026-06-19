@extends('layouts.app')
@section('title', "To'lov turlari")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">To'lov turlari</li>
@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-credit-card me-2" style="color:#6366f1"></i>To'lov turlari boshqaruvi</h5>
<div class="row g-3">
    {{-- Yangi to'lov turlari --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between">
                <h6 class="mb-0 small fw-bold text-success"><i class="bi bi-star-fill me-1"></i>Yangi to'lov turlari</h6>
                <small class="text-muted">Yangi shartnomalar uchun</small>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Nomi</th><th>Kategoriya</th><th>Ishlatilishi</th><th class="text-center">Holat</th></tr></thead>
                    <tbody>
                        @foreach($yangilar as $t)
                        <tr>
                            <td class="fw-medium small">{{ $t->nomi }}</td>
                            <td><span class="badge bg-info bg-opacity-10 text-info" style="font-size:.65rem">{{ $t->kategoriya }}</span></td>
                            <td class="small text-muted">
                                @if($statistika[$t->id]??null) {{ number_format($statistika[$t->id]->soni) }} ta @else — @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $t->holat==="faol"?"bg-success":"bg-secondary" }}" style="font-size:.65rem">
                                    {{ $t->holat }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Yangi qo'shish formi --}}
            <div class="card-footer">
                <form method="POST" action="{{ route('transfer.tolov_turi.store') }}" class="row g-2">
                    @csrf
                    <div class="col-sm-4">
                        <input type="text" name="nomi" class="form-control form-control-sm" placeholder="To'lov turi nomi *" required>
                    </div>
                    <div class="col-sm-3">
                        <select name="kategoriya" class="form-select form-select-sm" required>
                            <option value="">Kategoriya</option>
                            @foreach(['naqd','karta','bank','online','terminal','chegirma','tuzatish','oldindan','penya','boshqa'] as $k)
                            <option value="{{ $k }}">{{ ucfirst($k) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <input type="text" name="kod" class="form-control form-control-sm" placeholder="Kod (ixtiyoriy)">
                    </div>
                    <div class="col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-plus-lg me-1"></i>Qo'shish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Legacy to'lov turlari --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2">
                <h6 class="mb-0 small fw-bold text-muted"><i class="bi bi-archive me-1"></i>Legacy to'lov turlari</h6>
                <small class="text-muted">Eski tizimdan kelgan — o'zgartirish mumkin emas</small>
            </div>
            <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top"><tr><th>Nomi</th><th class="text-center">Foydalanilgan</th></tr></thead>
                    <tbody>
                        @foreach($legacylar as $t)
                        <tr>
                            <td class="small text-muted">{{ $t->nomi }}</td>
                            <td class="text-center">
                                @if($statistika[$t->id]??null)
                                <span class="badge bg-secondary bg-opacity-15 text-secondary" style="font-size:.65rem">
                                    {{ number_format($statistika[$t->id]->soni) }}
                                </span>
                                @else — @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($legacylar->hasPages())
            <div class="card-footer py-2">{{ $legacylar->links("pagination::bootstrap-5") }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
