@extends('layouts.app')

@section('title', 'Mijozlar')

@section('breadcrumb')
    <li class="breadcrumb-item active">Mijozlar</li>

{{-- Mobile FAB: yangi mijoz --}}
@if(Auth::user()->isMenejerYoki())
<a href="{{ route('mijozlar.create') }}"
   class="mobile-fab btn btn-primary"
   title="Yangi mijoz">
    <i class="bi bi-person-plus-fill"></i>
</a>
@endif
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold">
        <i class="bi bi-people me-1"></i> Mijozlar
        <span class="badge bg-secondary ms-1">{{ $mijozlar->total() }}</span>
    </h5>
    @if(Auth::user()->isMenejerYoki())
    <a href="{{ route('mijozlar.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Yangi mijoz
    </a>
    @endif
</div>

{{-- Filter paneli --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-5">
                <input type="search" name="qidiruv" class="form-control form-control-sm"
                       placeholder="Ism, familiya, telefon, passport..."
                       value="{{ request('qidiruv') }}" id="qidiruv-input">
            </div>
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->nomi }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-2">
                <select name="holat" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    <option value="faol" {{ request('holat') === 'faol' ? 'selected' : '' }}>AKTIV</option>
                    <option value="nofaol" {{ request('holat') === 'nofaol' ? 'selected' : '' }}>PASSIV</option>
                    <option value="sudda" {{ request('holat') === 'sudda' ? 'selected' : '' }}>SUDDA</option>
                    <option value="yomon" {{ request('holat') === 'yomon' ? 'selected' : '' }}>YOMON</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i> Qidirish
                </button>
                <a href="{{ route('mijozlar.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Jadval --}}
{{-- Mobile card ro'yxat --}}
<div class="d-md-none">
    @forelse($mijozlar as $mijoz)
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <a href="{{ route('mijozlar.show', $mijoz) }}" class="fw-bold text-decoration-none">
                    {{ $mijoz->familiya }} {{ $mijoz->ism }}
                </a>
                <span class="badge bg-{{ $mijoz->holat_rangi }}">
                    {{ $mijoz->holat_nomi }}
                </span>
            </div>
            @if($mijoz->otasining_ismi)
            <div class="text-muted small mb-1">{{ $mijoz->otasining_ismi }}</div>
            @endif
            <div class="d-flex gap-3 mb-2" style="font-size:.85rem">
                <a href="tel:{{ $mijoz->telefon }}" class="text-decoration-none">
                    <i class="bi bi-telephone me-1 text-muted"></i>{{ $mijoz->telefon }}
                </a>
                @if(Auth::user()->isAdmin() && $mijoz->filial)
                <span class="badge bg-secondary">{{ $mijoz->filial->kod }}</span>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $mijoz->passport_tolik }}</small>
                <a href="{{ route('mijozlar.show', $mijoz) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                    <i class="bi bi-eye me-1"></i>Ko'rish
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-people fs-3 d-block mb-2"></i>
        Mijozlar topilmadi
    </div>
    @endforelse
    @if($mijozlar->hasPages())
    <div class="mt-2">{{ $mijozlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>F.I.O.</th>
                    <th>Telefon</th>
                    <th>Passport</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th>Holat</th>
                    <th>Shartnomalar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($mijozlar as $mijoz)
                <tr>
                    <td class="text-muted small">{{ $mijozlar->firstItem() + $loop->index }}</td>
                    <td>
                        <a href="{{ route('mijozlar.show', $mijoz) }}" class="text-decoration-none fw-medium">
                            {{ $mijoz->familiya }} {{ $mijoz->ism }}
                        </a>
                        @if($mijoz->otasining_ismi)
                            <div class="text-muted small">{{ $mijoz->otasining_ismi }}</div>
                        @endif
                    </td>
                    <td>
                        <a href="tel:{{ $mijoz->telefon }}" class="text-decoration-none">
                            <i class="bi bi-telephone me-1 text-muted small"></i>{{ $mijoz->telefon }}
                        </a>
                    </td>
                    <td class="text-muted small">{{ $mijoz->passport_tolik }}</td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary">{{ $mijoz->filial->kod ?? '—' }}</span></td>
                    @endif
                    <td>
                        <span class="badge bg-{{ $mijoz->holat_rangi }}">
                            {{ $mijoz->holat_nomi }}
                        </span>
                    </td>
                    <td class="text-muted small">
                        {{ $mijoz->kreditlar_count ?? $mijoz->kreditlar()->count() }} ta
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('mijozlar.show', $mijoz) }}"
                               class="btn btn-sm btn-outline-primary py-0" title="Ko'rish">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(Auth::user()->isMenejerYoki())
                            <a href="{{ route('mijozlar.edit', $mijoz) }}"
                               class="btn btn-sm btn-outline-secondary py-0" title="Tahrirlash">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        Mijozlar topilmadi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($mijozlar->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            {{ $mijozlar->firstItem() }}–{{ $mijozlar->lastItem() }} / {{ $mijozlar->total() }}
        </small>
        {{ $mijozlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Qidiruv faqat "Enter" yoki tugma bosilganda ishlaydi
$('#qidiruv-input').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        $(this).closest('form').submit();
    }
});
</script>
@endpush
