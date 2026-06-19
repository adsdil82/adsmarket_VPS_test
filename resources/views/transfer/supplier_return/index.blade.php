@extends('layouts.app')
@section('title', "Ta'minotchiga qaytarish")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Ta'minotchiga qaytarish</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-return-left me-2 text-secondary"></i>Ta'minotchiga qaytarish</h5>
    @if(Auth::user()->isOmborchi())
    <a href="{{ route('transfer.supplier-return.create') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Yangi qaytarish
    </a>
    @endif
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <select name="taminotchi_id" class="form-select form-select-sm">
                    <option value="">Barcha ta'minotchilar</option>
                    @foreach($taminotchilar as $t)
                    <option value="{{ $t->id }}" {{ request("taminotchi_id")==$t->id?"selected":"" }}>{{ $t->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="holat" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    @foreach(["qoralama","tasdiqlangan","qaytarildi","bekor"] as $h)
                    <option value="{{ $h }}" {{ request("holat")===$h?"selected":"" }}>{{ ucfirst($h) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route("transfer.supplier-return.index") }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>Hujjat #</th><th>Ta'minotchi</th><th>Ombor</th><th>Sana</th><th class="text-end">Jami</th><th>Holat</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($qaytarishlar as $q)
                <tr>
                    <td class="fw-medium small">{{ $q->hujjat_raqam }}</td>
                    <td class="small">{{ $q->taminotchi->nomi }}</td>
                    <td class="small text-muted">{{ $q->ombor->nomi }}</td>
                    <td class="small text-muted">{{ $q->sana->format("d.m.Y") }}</td>
                    <td class="text-end small">{{ number_format($q->jami_summa,0,"."," ") }}</td>
                    <td><span class="badge bg-{{ $q->holat_rangi }}" style="font-size:.68rem">{{ $q->holat }}</span></td>
                    <td><a href="{{ route("transfer.supplier-return.show",$q) }}" class="btn btn-sm btn-outline-secondary py-0 px-1"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Qaytarishlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($qaytarishlar->hasPages())
    <div class="card-footer py-2">{{ $qaytarishlar->links("pagination::bootstrap-5") }}</div>
    @endif
</div>
@endsection
