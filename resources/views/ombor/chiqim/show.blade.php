@extends('layouts.app')
@section('title','Chiqim #'.$chiqim->id)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('chiqim.index') }}">Chiqim</a></li>
<li class="breadcrumb-item active">#{{ $chiqim->id }}</li>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Chiqim ma'lumotlari</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">ID</td><td>#{{ $chiqim->id }}</td></tr>
                    <tr><td class="text-muted">Sana</td><td>{{ $chiqim->sana->format('d.m.Y') }}</td></tr>
                    <tr><td class="text-muted">Sabab</td>
                        <td><span class="badge bg-danger">{{ \App\Models\OmbordanChiqim::$sabablar[$chiqim->sabab] ?? $chiqim->sabab }}</span></td>
                    </tr>
                    <tr><td class="text-muted">Xodim</td><td>{{ $chiqim->xodim?->ism_familiya }}</td></tr>
                    <tr><td class="text-muted">Filial</td><td>{{ $chiqim->filial?->nomi }}</td></tr>
                    <tr><td class="text-muted">Jami</td>
                        <td class="fw-bold text-danger fs-5">{{ number_format($chiqim->umumiy_summa,0,'.',' ') }} so'm</td>
                    </tr>
                </table>
                @if($chiqim->izoh)
                <div class="alert alert-light mt-2 mb-0 py-2 small">{{ $chiqim->izoh }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header"><h6 class="mb-0">Tovarlar ({{ $chiqim->tafsilot->count() }} ta)</h6></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>#</th><th>Tovar</th><th class="text-end">Miqdor</th><th class="text-end">Narx</th><th class="text-end">Jami</th></tr>
                    </thead>
                    <tbody>
                        @foreach($chiqim->tafsilot as $i => $t)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $t->tovar?->nomi }}<small class="text-muted d-block">{{ $t->tovar?->guruh?->nomi }}</small></td>
                            <td class="text-end">{{ $t->miqdor }} {{ $t->tovar?->birlik }}</td>
                            <td class="text-end text-muted">{{ number_format($t->narx,0,'.',' ') }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($t->jami_summa,0,'.',' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr><td colspan="4" class="text-end fw-bold">Jami:</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($chiqim->umumiy_summa,0,'.',' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
