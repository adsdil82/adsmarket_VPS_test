@extends('layouts.app')
@section('title', "To'lovlar reestri")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item active">To'lovlar reestri</li>
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-list-check me-2 text-success"></i>Ta'minotchilar — To'lovlar reestri
    </h5>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-3">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            <div class="col-sm-3">
                <select name="taminotchi_id" class="form-select form-select-sm">
                    <option value="">Barcha ta'minotchilar</option>
                    @foreach($taminotchilar as $t)
                    <option value="{{ $t->id }}" {{ request('taminotchi_id')==$t->id?'selected':'' }}>{{ $t->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
                <a href="{{ route('taminotchi.tulov_reestr') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between">
        <h6 class="mb-0 small">Jami: {{ $tulovlar->total() }} ta to'lov</h6>
        <strong class="text-success">{{ number_format($tulovlar->sum('summa'),0,'.',' ') }} so'm</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sana</th>
                    <th>Ta'minotchi</th>
                    <th>To'lov turi</th>
                    <th class="text-end">Summa</th>
                    <th>Hujjat #</th>
                    <th>Kassir</th>
                    <th>Izoh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tulovlar as $tv)
                <tr>
                    <td class="small">{{ $tv->tolov_sana->format('d.m.Y') }}</td>
                    <td>
                        <a href="{{ route('taminotchi.show',$tv->taminotchi) }}" class="text-decoration-none fw-medium small">
                            {{ $tv->taminotchi->nomi }}
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-info bg-opacity-10 text-info" style="font-size:.65rem">
                            {{ $tv->tolov_turi }}
                        </span>
                    </td>
                    <td class="text-end fw-bold text-success">{{ number_format($tv->summa,0,'.',' ') }}</td>
                    <td class="text-muted small">{{ $tv->hujjat_raqam ?? '—' }}</td>
                    <td class="text-muted small">{{ $tv->xodim->ism_familiya ?? '—' }}</td>
                    <td class="text-muted small">{{ $tv->izoh ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">To'lovlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tulovlar->hasPages())
    <div class="card-footer d-flex justify-content-between py-2">
        <small class="text-muted">{{ $tulovlar->firstItem() }}–{{ $tulovlar->lastItem() }} / {{ $tulovlar->total() }}</small>
        {{ $tulovlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
