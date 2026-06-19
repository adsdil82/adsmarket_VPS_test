@extends('layouts.app')
@section('title', 'Ombor qoldig\'i')
@section('breadcrumb')
    <li class="breadcrumb-item active">Ombor qoldig'i</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-boxes me-2 text-warning"></i>Ombor qoldig'i</h5>
</div>

{{-- Statistika --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Ombordagi tovarlar</div>
            <div class="fs-4 fw-bold text-warning">
                {{ number_format(\App\Models\Tovar::sum('soni'), 0, '.', ' ') }}
            </div>
            <div class="text-muted small">dona</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Jami tovar turlari</div>
            <div class="fs-4 fw-bold text-primary">
                {{ number_format(\App\Models\Tovar::distinct('nomi')->count('nomi'), 0, '.', ' ') }}
            </div>
            <div class="text-muted small">xil</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Umumiy qiymat</div>
            <div class="fs-4 fw-bold text-success">
                {{ number_format(\App\Models\Tovar::sum('jami_narx'), 0, '.', ' ') }}
            </div>
            <div class="text-muted small">so'm</div>
        </div>
    </div>
</div>

{{-- Tovarlar ro'yxati --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h6 class="mb-0">Sotilgan tovarlar ro'yxati</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tovar nomi</th>
                    <th class="text-end">Soni</th>
                    <th class="text-end">Narx</th>
                    <th class="text-end">Jami</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Tovar::select('nomi', \Illuminate\Support\Facades\DB::raw('SUM(soni) as jami_soni'), \Illuminate\Support\Facades\DB::raw('AVG(narx) as ort_narx'), \Illuminate\Support\Facades\DB::raw('SUM(jami_narx) as jami_summa'))->groupBy('nomi')->orderByDesc('jami_summa')->limit(50)->get() as $t)
                <tr>
                    <td>{{ $t->nomi }}</td>
                    <td class="text-end">{{ number_format($t->jami_soni, 0, '.', ' ') }}</td>
                    <td class="text-end text-muted">{{ number_format($t->ort_narx, 0, '.', ' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format($t->jami_summa, 0, '.', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Ombor moduli ishlab chiqilmoqda. Hozirda shartnomalardan sotilgan tovarlar ko'rsatilmoqda.
    </div>
</div>
@endsection
