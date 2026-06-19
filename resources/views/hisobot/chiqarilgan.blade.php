@extends('layouts.app')
@section('title', 'Chiqarilgan kreditlar')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Chiqarilgan kreditlar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-file-plus me-1 text-primary"></i> Chiqarilgan kreditlar</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('hisobotlar.excel','chiqarilgan') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel
        </a>
        <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label small mb-1">Dan</label>
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-3">
                <label class="form-label small mb-1">Gacha</label>
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i> Filtrlash
                </button>
                <a href="{{ route('hisobotlar.excel','chiqarilgan') }}?dan_sana={{ $danSana }}&gacha_sana={{ $gachaSana }}&filial_id={{ $filialId }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Jami --}}
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-primary">{{ number_format($jami->soni ?? 0) }}</div>
            <div class="small text-muted">Chiqarilgan kreditlar soni</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-5 fw-bold text-success">{{ number_format(($jami->summa ?? 0)/1000000,1) }} mln</div>
            <div class="small text-muted">Jami kredit summasi</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-5 fw-bold">{{ $danSana }} — {{ $gachaSana }}</div>
            <div class="small text-muted">Davr</div>
        </div>
    </div>
</div>

{{-- Jadval --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between py-2">
        <h6 class="mb-0 small">Kreditlar ro'yxati</h6>
        <small class="text-muted">{{ $kreditlar->total() }} ta</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Shartnoma</th>
                    <th>Mijoz</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th>Boshlanish</th>
                    <th>Tugash</th>
                    <th class="text-end">Kredit summa</th>
                    <th class="text-end">Tolov qilingan</th>
                    <th class="text-end">Qoldiq</th>
                    <th>Holat</th>
                    <th>Xodim</th>
                </tr>
            </thead>
            <tbody>
                @php $n = ($kreditlar->currentPage()-1)*$kreditlar->perPage(); @endphp
                @forelse($kreditlar as $k)
                @php $n++; @endphp
                <tr>
                    <td class="text-muted small">{{ $n }}</td>
                    <td>
                        <a href="{{ route('kreditlar.show',$k) }}" class="text-decoration-none small fw-medium">
                            {{ $k->shartnoma_raqam }}
                        </a>
                    </td>
                    <td>
                        <div class="small">{{ $k->mijoz->familiya }} {{ $k->mijoz->ism }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $k->mijoz->telefon }}</div>
                    </td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary" style="font-size:.65rem">{{ $k->filial->kod }}</span></td>
                    @endif
                    <td class="small text-muted">{{ $k->boshlanish_sana?->format('d.m.Y') ?? '—' }}</td>
                    <td class="small text-muted">{{ $k->tugash_sana?->format('d.m.Y') ?? '—' }}</td>
                    <td class="text-end small">{{ number_format($k->kredit_summa,0,'.',' ') }}</td>
                    <td class="text-end small text-success">{{ number_format($k->tolov_qilingan,0,'.',' ') }}</td>
                    <td class="text-end small {{ $k->qoldiq_qarz > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($k->qoldiq_qarz,0,'.',' ') }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $k->holat_rangi }}" style="font-size:.68rem">
                            {{ $k->holatNomi }}
                        </span>
                    </td>
                    <td class="small text-muted">{{ $k->xodim->ism_familiya ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="11" class="text-center py-4 text-muted">Kreditlar topilmadi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kreditlar->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">{{ $kreditlar->firstItem() }}–{{ $kreditlar->lastItem() }} / {{ $kreditlar->total() }}</small>
        {{ $kreditlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
