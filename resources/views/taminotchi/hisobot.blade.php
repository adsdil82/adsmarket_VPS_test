@extends('layouts.app')
@section('title', "Ta'minotchilar hisoboti")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item active">Hisobot</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-bar-chart me-2 text-warning"></i>Ta'minotchilar hisoboti
    </h5>
    <a href="{{ request()->fullUrlWithQuery(['format'=>'excel']) }}" class="btn btn-sm btn-success">
        <i class="bi bi-file-earmark-excel me-1"></i>Excel
    </a>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3"><label class="form-label small mb-1">Dan</label>
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-3"><label class="form-label small mb-1">Gacha</label>
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
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
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filtrlash</button>
                <a href="{{ route('taminotchi.hisobot') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Jami kartalar --}}
<div class="row g-3 mb-4">
    @php $cards = [
        ['Jami kirim', $jami['kirim'], 'primary', 'bi-box-arrow-in-down'],
        ['Jami to\'lov', $jami['tolov'], 'success', 'bi-cash-coin'],
        ['Umumiy qoldiq', $jami['qoldiq'], $jami['qoldiq']>0?'danger':'success', 'bi-scale'],
        ['Biz qarazdor', $jami['qarazdor'].' ta', 'danger', 'bi-exclamation-triangle'],
        ['Ular qarazdor', $jami['hakdor'].' ta', 'success', 'bi-check-circle'],
    ]; @endphp
    @foreach($cards as [$lbl,$val,$rang,$icon])
    <div class="col-6 col-md">
        <div class="card border-0 shadow-sm text-center py-3">
            <i class="bi {{ $icon }} fs-4 text-{{ $rang }} mb-1"></i>
            <div class="fw-bold fs-6 text-{{ $rang }}">
                @if(is_numeric($val)) {{ number_format($val,0,'.',' ') }} @else {{ $val }} @endif
            </div>
            <div class="text-muted small">{{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Ta'minotchi</th>
                    <th class="text-center">Kirimlar</th>
                    <th class="text-end">Jami kirim</th>
                    <th class="text-end">To'langan</th>
                    <th class="text-end">Qoldiq</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($statistika as $i => $t)
                @php $isQ = $t->qoldiq > 0; $isH = $t->qoldiq < 0; @endphp
                <tr>
                    <td class="text-muted small">{{ $i+1 }}</td>
                    <td>
                        <a href="{{ route('taminotchi.show',$t->id) }}" class="text-decoration-none fw-medium">
                            {{ $t->nomi }}
                        </a>
                        @if($t->telefon)
                        <div class="text-muted" style="font-size:.72rem">{{ $t->telefon }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $t->kirim_soni }}</td>
                    <td class="text-end">{{ number_format($t->jami_kirim,0,'.',' ') }}</td>
                    <td class="text-end text-success">{{ number_format($t->jami_tolov,0,'.',' ') }}</td>
                    <td class="text-end fw-bold {{ $isQ?'text-danger':($isH?'text-success':'text-muted') }}">
                        {{ number_format(abs($t->qoldiq),0,'.',' ') }}
                    </td>
                    <td class="text-center">
                        @if($isQ)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Qarazdor</span>
                        @elseif($isH)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Haq'dor</span>
                        @else
                            <span class="badge bg-secondary">Teng</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('taminotchi.akt_sverka',$t->id) }}"
                           class="btn btn-xs btn-outline-info py-0 px-1" style="font-size:.75rem">
                            Akt sverka
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted">Ma'lumot topilmadi</td></tr>
                @endforelse
            </tbody>
            @if($statistika->count())
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="3" class="text-end">Jami:</td>
                    <td class="text-end">{{ number_format($jami['kirim'],0,'.',' ') }}</td>
                    <td class="text-end text-success">{{ number_format($jami['tolov'],0,'.',' ') }}</td>
                    <td class="text-end {{ $jami['qoldiq']>0?'text-danger':'' }}">
                        {{ number_format(abs($jami['qoldiq']),0,'.',' ') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
