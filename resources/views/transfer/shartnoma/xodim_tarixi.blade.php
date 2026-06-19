@extends('layouts.app')
@section('title', 'Xodim tayinlash tarixi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item active">Xodim tayinlash tarixi</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-person-check me-2 text-warning"></i>Xodim qayta tayinlash tarixi</h5>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr><th>Shartnoma</th><th>Eski xodim</th><th>Yangi xodim</th><th>Sabab</th><th>O'zgartirgan</th><th>Sana</th></tr>
            </thead>
            <tbody>
                @forelse($tarixi as $t)
                <tr>
                    <td><a href="{{ route("kreditlar.show",$t->shartnoma) }}" class="text-decoration-none fw-medium small">{{ $t->shartnoma->shartnoma_raqam }}</a></td>
                    <td class="text-muted small">{{ $t->eskiXodim?->ism_familiya ?? "—" }}</td>
                    <td class="fw-medium small">{{ $t->yangiXodim->ism_familiya }}</td>
                    <td class="small">{{ Str::limit($t->sabab,60) }}</td>
                    <td class="text-muted small">{{ $t->ozgartirgan->ism_familiya }}</td>
                    <td class="text-muted small">{{ $t->created_at->format("d.m.Y H:i") }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Tarix topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tarixi->hasPages())
    <div class="card-footer py-2">{{ $tarixi->links("pagination::bootstrap-5") }}</div>
    @endif
</div>
@endsection
