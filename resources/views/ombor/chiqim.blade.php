@extends('layouts.app')
@section('title', 'Tovar chiqim')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('ombor.index') }}">Ombor</a></li>
    <li class="breadcrumb-item active">Tovar chiqim</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-up me-2 text-danger"></i>Tovar chiqim</h5>
</div>

{{-- Sotilgan tovarlar (shartnomalardan) --}}
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="mb-0">Nasiya orqali sotilgan tovarlar</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Shartnoma</th>
                    <th>Tovar nomi</th>
                    <th class="text-end">Soni</th>
                    <th class="text-end">Narx</th>
                    <th class="text-end">Jami</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\Tovar::with('kredit:id,shartnoma_raqam')->orderByDesc('created_at')->limit(100)->get() as $t)
                <tr>
                    <td class="text-muted small">
                        @if($t->kredit)
                        <a href="{{ route('kreditlar.show', $t->kredit) }}" class="text-decoration-none">
                            {{ $t->kredit->shartnoma_raqam }}
                        </a>
                        @else —
                        @endif
                    </td>
                    <td>{{ $t->nomi }}</td>
                    <td class="text-end">{{ $t->soni }}</td>
                    <td class="text-end text-muted">{{ number_format($t->narx, 0, '.', ' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format($t->jami_narx, 0, '.', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small">
        So'nggi 100 ta tovar chiqimi ko'rsatilmoqda.
    </div>
</div>
@endsection
