@extends('layouts.app')
@section('title','Qurilma loglar')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.index') }}">Qurilmalar</a></li>
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.show',$qurilma) }}">{{ $qurilma->toliq_nomi }}</a></li>
<li class="breadcrumb-item active">Loglar</li>
@endsection
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>{{ $qurilma->toliq_nomi }} — Loglar</h5>
    <a href="{{ route('qurilmalar.show',$qurilma) }}" class="btn btn-outline-secondary btn-sm">← Orqaga</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.85rem">
            <thead class="table-light">
                <tr>
                    <th>Vaqt</th>
                    <th>Amal</th>
                    <th>Holat</th>
                    <th>Provider</th>
                    <th>Sabab / Javob</th>
                    <th>Xodim</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loglar as $l)
                <tr>
                    <td class="text-muted small text-nowrap">{{ $l->created_at->format('d.m.Y H:i:s') }}</td>
                    <td><strong>{{ $l->amal_nomi }}</strong></td>
                    <td><span class="badge bg-{{ $l->holat_rangi }}" style="font-size:.7rem">{{ $l->holat }}</span></td>
                    <td class="text-muted small">{{ $l->provayder?->nomi ?? '—' }}</td>
                    <td class="text-muted small">
                        @if($l->sabab)<div>{{ Str::limit($l->sabab, 80) }}</div>@endif
                        @if($l->javob)
                        <details><summary class="text-primary" style="cursor:pointer;font-size:.7rem">API javob</summary>
                        <pre style="font-size:.65rem;max-height:100px;overflow:auto">{{ json_encode(json_decode($l->javob), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $l->xodim?->ism_familiya ?? 'Tizim' }}</td>
                    <td class="text-muted small font-monospace">{{ $l->ip_manzil }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Log yozuvlari yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($loglar->hasPages())
    <div class="card-footer">{{ $loglar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
