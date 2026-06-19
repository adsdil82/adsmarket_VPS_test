@extends('layouts.app')
@section('title', 'Qaytarish')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.supplier-return.index') }}">Qaytarishlar</a></li>
    <li class="breadcrumb-item active">{{ $qaytarish->hujjat_raqam }}</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1"><i class="bi bi-arrow-return-left me-2"></i>{{ $qaytarish->hujjat_raqam }}</h5>
        <span class="badge bg-{{ $qaytarish->holat_rangi }} fs-6">{{ $qaytarish->holat }}</span>
    </div>
    <div class="d-flex gap-2">
        @if($qaytarish->holat === "qoralama" && Auth::user()->isMenejerYoki())
        <form method="POST" action="{{ route("transfer.supplier-return.tasdiqla",$qaytarish) }}" style="display:inline">
            @csrf <button class="btn btn-sm btn-success"><i class="bi bi-check2 me-1"></i>Tasdiqlash</button>
        </form>
        @endif
        @if($qaytarish->holat === "tasdiqlangan")
        <form method="POST" action="{{ route("transfer.supplier-return.qaytarildi",$qaytarish) }}" style="display:inline">
            @csrf <button class="btn btn-sm btn-info"><i class="bi bi-check2-all me-1"></i>Qaytarildi</button>
        </form>
        @endif
    </div>
</div>
<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Hujjat #</td><td><strong>{{ $qaytarish->hujjat_raqam }}</strong></td></tr>
                    <tr><td class="text-muted">Ta'minotchi</td><td>{{ $qaytarish->taminotchi->nomi }}</td></tr>
                    <tr><td class="text-muted">Ombor</td><td>{{ $qaytarish->ombor->nomi }}</td></tr>
                    <tr><td class="text-muted">Sana</td><td>{{ $qaytarish->sana->format("d.m.Y") }}</td></tr>
                    <tr><td class="text-muted">Sabab</td><td>{{ $qaytarish->sabab }}</td></tr>
                    <tr><td class="text-muted">Xodim</td><td>{{ $qaytarish->xodim->ism_familiya }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2"><h6 class="mb-0 small fw-bold">Qaytariladigan tovarlar</h6></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Tovar</th><th class="text-end">Miqdor</th><th class="text-end">Narx</th><th class="text-end">Jami</th></tr></thead>
                    <tbody>
                        @foreach($qaytarish->qatorlar as $q)
                        <tr>
                            <td class="small">{{ $q->nomi }}</td>
                            <td class="text-end small">{{ $q->miqdor }} {{ $q->birlik }}</td>
                            <td class="text-end small text-muted">{{ number_format($q->narx,0,"."," ") }}</td>
                            <td class="text-end small fw-medium">{{ number_format($q->jami,0,"."," ") }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr><td colspan="3" class="text-end">Jami:</td><td class="text-end">{{ number_format($qaytarish->jami_summa,0,"."," ") }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
