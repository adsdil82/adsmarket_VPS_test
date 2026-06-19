@extends('layouts.app')

@section('title', 'Versiyalar — ' . $kredit->shartnoma_raqam)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.show', $kredit) }}">{{ $kredit->shartnoma_raqam }}</a></li>
    <li class="breadcrumb-item active">Versiyalar tarixi</li>
@endsection

@section('content')

{{-- ── Sarlavha ───────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-1">
            <i class="bi bi-clock-history me-2"></i>Versiyalar tarixi
        </h5>
        <div class="text-muted small">
            <a href="{{ route('kreditlar.show', $kredit) }}" class="text-decoration-none">
                {{ $kredit->shartnoma_raqam }}
            </a>
            · {{ $kredit->mijoz->tolik_ism ?? '—' }}
        </div>
    </div>
    <a href="{{ route('kreditlar.show', $kredit) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Shartnomaga qaytish
    </a>
</div>

{{-- ── Versiyalar ro'yxati ─────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:80px">Versiya</th>
                    <th style="width:140px">Sana</th>
                    <th>Xodim</th>
                    <th>Sabab</th>
                    <th>O'zgargan maydonlar</th>
                    <th style="width:80px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($versiyalar as $v)
                <tr>
                    <td>
                        <span class="badge bg-primary fs-6">v{{ $v->versiya_raqam }}</span>
                    </td>
                    <td class="text-muted small">
                        {{ $v->created_at->format('d.m.Y') }}<br>
                        <span class="text-muted">{{ $v->created_at->format('H:i') }}</span>
                    </td>
                    <td>
                        {{ $v->xodim->ism_familiya ?? '—' }}
                        <div class="text-muted small">{{ $v->xodim->rol ?? '' }}</div>
                    </td>
                    <td>{{ $v->sabab ?? '—' }}</td>
                    <td>
                        @if($v->ozgargan_maydonlar && count($v->ozgargan_maydonlar) > 0)
                            @foreach($v->ozgargan_maydonlar as $maydon)
                                <span class="badge bg-warning text-dark me-1">{{ $maydon }}</span>
                            @endforeach
                        @else
                            <span class="text-muted small">Yangi yaratilgan</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('kreditlar.versiyalar.show', [$kredit, $v]) }}"
                           class="btn btn-sm btn-outline-secondary py-0"
                           title="Batafsil ko'rish">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-clock-history fs-3 d-block mb-2 opacity-25"></i>
                        Versiyalar tarixi yo'q
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($versiyalar->count() > 0)
    <div class="card-footer text-muted small">
        Jami {{ $versiyalar->count() }} ta versiya
    </div>
    @endif
</div>

@endsection
