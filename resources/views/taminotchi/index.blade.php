@extends('layouts.app')
@section('title', "Ta'minotchilar")
@section('breadcrumb')
    <li class="breadcrumb-item active">Ta'minotchilar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-truck me-2 text-warning"></i>Ta'minotchilar
        <span class="badge bg-secondary ms-1">{{ $taminotchilar->total() }}</span>
    </h5>
    @if(Auth::user()->isMenejerYoki())
    <a href="{{ route('taminotchi.create') }}" class="btn btn-warning btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Yangi ta'minotchi
    </a>
    @endif
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="search" name="qidiruv" class="form-control form-control-sm"
                       placeholder="Nomi yoki telefon..." value="{{ request('qidiruv') }}">
            </div>
            <div class="col-sm-2">
                <select name="holat" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    <option value="faol" {{ request('holat')==='faol'?'selected':'' }}>Faol</option>
                    <option value="nofaol" {{ request('holat')==='nofaol'?'selected':'' }}>Nofaol</option>
                </select>
            </div>
            <div class="col-sm-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('taminotchi.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ta'minotchi</th>
                    <th>Telefon</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th class="text-center">Kirimlar</th>
                    <th class="text-end">Jami kirim</th>
                    <th class="text-end">To'langan</th>
                    <th class="text-end">Qoldiq</th>
                    <th>Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($taminotchilar as $t)
                @php
                    $kirim  = (float)$t->kirimlar_sum_jami_summa;
                    $tolov  = (float)$t->tulovlar_sum_summa;
                    $qoldiq = $kirim - $tolov;
                    $qRangi = $qoldiq > 0 ? 'danger' : ($qoldiq < 0 ? 'success' : 'secondary');
                    $qHolat = $qoldiq > 0 ? 'Qarazdor' : ($qoldiq < 0 ? "Haq'dor" : 'Teng');
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('taminotchi.show', $t) }}" class="text-decoration-none fw-medium">
                            {{ $t->nomi }}
                        </a>
                        @if($t->kontakt_shaxs)
                        <div class="text-muted small">{{ $t->kontakt_shaxs }}</div>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $t->telefon ?? '—' }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>
                        @if($t->filial)
                        <span class="badge bg-secondary">{{ $t->filial->kod }}</span>
                        @else
                        <span class="text-muted small">Umumiy</span>
                        @endif
                    </td>
                    @endif
                    <td class="text-center">{{ $t->kirimlar_count }}</td>
                    <td class="text-end text-muted small">{{ number_format($kirim,0,'.',' ') }}</td>
                    <td class="text-end text-success small">{{ number_format($tolov,0,'.',' ') }}</td>
                    <td class="text-end fw-bold text-{{ $qRangi }}">
                        {{ number_format(abs($qoldiq),0,'.',' ') }}
                        <div style="font-size:.68rem;font-weight:400" class="text-{{ $qRangi }}">{{ $qHolat }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $t->holat==='faol' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $t->holat==='faol' ? 'FAOL' : 'NOFAOL' }}
                        </span>
                    </td>
                    <td class="text-end" style="white-space:nowrap">
                        <a href="{{ route('taminotchi.show', $t) }}"
                           class="btn btn-sm btn-outline-primary py-0 px-1" title="Ko'rish">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(Auth::user()->isMenejerYoki())
                        <a href="{{ route('taminotchi.edit', $t) }}"
                           class="btn btn-sm btn-outline-warning py-0 px-1 ms-1" title="Tahrirlash">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                        <a href="{{ route('taminotchi.akt_sverka', $t) }}"
                           class="btn btn-sm btn-outline-info py-0 px-1 ms-1" title="Akt sverka">
                            <i class="bi bi-file-earmark-text"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-truck fs-2 d-block mb-2 opacity-25"></i>
                        Ta'minotchilar topilmadi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($taminotchilar->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">{{ $taminotchilar->firstItem() }}–{{ $taminotchilar->lastItem() }} / {{ $taminotchilar->total() }}</small>
        {{ $taminotchilar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
