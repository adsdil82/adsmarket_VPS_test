@extends('layouts.app')
@section('title', 'Filial ko'chirish tarixi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Filial ko'chirish tarixi</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-building-fill-slash me-2 text-danger"></i>Shartnoma filial ko'chirish tarixi</h5>
</div>
<div class="alert alert-info py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Eski to'lovlar eski filial hisobotida qoladi. Yangi to'lovlar yangi filialda ko'rinadi.
    Asl <code>filial_id</code> o'zgarmaydi — faqat <code>joriy_filial_id</code> yangilanadi.
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Shartnoma</th><th>Eski filial</th><th>Yangi filial</th><th>Sabab</th><th>To'lovlar</th><th>O'zgartirgan</th><th>Sana</th></tr>
            </thead>
            <tbody>
                @forelse($tarixi as $t)
                <tr>
                    <td><a href="{{ route("kreditlar.show",$t->shartnoma) }}" class="text-decoration-none fw-medium small">{{ $t->shartnoma->shartnoma_raqam }}</a></td>
                    <td><span class="badge bg-secondary">{{ $t->eskiFilial->kod }}</span></td>
                    <td><span class="badge bg-primary">{{ $t->yangiFilial->kod }}</span></td>
                    <td class="small">{{ Str::limit($t->sabab,60) }}</td>
                    <td class="small">
                        @if($t->tolovlar_yangi_filialda)
                            <span class="badge bg-success bg-opacity-15 text-success">Yangi filialda</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-15 text-secondary">Eski filialda</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $t->ozgartirgan->ism_familiya }}</td>
                    <td class="text-muted small">{{ $t->created_at->format("d.m.Y H:i") }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Tarix topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tarixi->hasPages())
    <div class="card-footer py-2">{{ $tarixi->links("pagination::bootstrap-5") }}</div>
    @endif
</div>
@endsection
