@extends('layouts.app')
@section('title','Sotuv tarixi')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">Kassa</a></li>
<li class="breadcrumb-item active">Sotuv tarixi</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Sotuv tarixi</h5>
    <a href="{{ route('pos.index') }}" class="btn btn-success">
        <i class="bi bi-cart me-1"></i>Kassaga qaytish
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small">Bugungi savdo</div>
            <div class="fs-5 fw-bold text-success">{{ number_format($bugun_jami,0,'.',' ') }} so'm</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(Auth::user()->isAdmin())
            <div class="col-sm-3">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filiallar</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-3">
                <input type="date" name="sana" class="form-control form-control-sm" value="{{ request('sana', date('Y-m-d')) }}">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('pos.tarix') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Chek #</th>
                    <th>Sana/Vaqt</th>
                    <th>Kassir</th>
                    <th>Mijoz</th>
                    <th class="text-center">To'lov turi</th>
                    <th class="text-end">Summa</th>
                    <th class="text-end">Chegirma</th>
                    <th class="text-end fw-bold">Jami</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($sotuvlar as $s)
                <tr>
                    <td class="font-monospace text-muted small">{{ $s->check_raqam }}</td>
                    <td>
                        <div>{{ $s->sana->format('d.m.Y') }}</div>
                        <small class="text-muted">{{ $s->created_at->format('H:i') }}</small>
                    </td>
                    <td class="small text-muted">{{ $s->xodim?->ism_familiya }}</td>
                    <td class="small">{{ $s->mijoz_ism ?? '—' }}</td>
                    <td class="text-center">
                        @php $turlarikonlar = ['naqd'=>'cash text-success','plastik'=>'credit-card text-primary','aralash'=>'cash-coin text-warning']; @endphp
                        <i class="bi bi-{{ $turlarikonlar[$s->tolov_turi] ?? 'cash' }}"></i>
                        <small class="d-block text-muted">{{ $s->tolov_turi }}</small>
                    </td>
                    <td class="text-end text-muted">{{ number_format($s->umumiy_summa,0,'.',' ') }}</td>
                    <td class="text-end text-danger small">{{ $s->chegirma > 0 ? '-'.number_format($s->chegirma,0,'.',' ') : '—' }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($s->jami_tolov,0,'.',' ') }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $s->holat==='tugallangan'?'success':'danger' }}">{{ $s->holat }}</span>
                    </td>
                    <td>
                        <a href="{{ route('pos.chek',$s) }}" class="btn btn-sm btn-outline-primary py-0" title="Chek">
                            <i class="bi bi-receipt"></i>
                        </a>
                        @if($s->holat === 'tugallangan')
                        <a href="{{ route('pos.qaytim.boshlash',$s) }}" class="btn btn-sm btn-outline-danger py-0" title="Qaytim">
                            <i class="bi bi-arrow-return-left"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Sotuvlar topilmadi
                </td></tr>
                @endforelse
            </tbody>
            @if($sotuvlar->count())
            <tfoot class="table-light">
                <tr>
                    <td colspan="7" class="text-end fw-bold">Sahifa jami:</td>
                    <td class="text-end fw-bold text-success">{{ number_format($sotuvlar->sum('jami_tolov'),0,'.',' ') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @if($sotuvlar->hasPages())
    <div class="card-footer">{{ $sotuvlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
