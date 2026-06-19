@extends('layouts.app')
@section('title','Chek #'.$sotuv->check_raqam)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.tarix') }}">Sotuv tarixi</a></li>
<li class="breadcrumb-item active">Chek</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-sm-6 col-md-4">

<div class="card border-0 shadow-sm mb-3" style="font-family: monospace;">
    @php $soz = \App\Models\Sozlama::barchasi(); @endphp
    <div class="card-body text-center">
        <h5 class="fw-bold">{{ $soz['brand_nomi'] ?? 'NasiyaPro' }}</h5>
        @if($soz['kompaniya_nomi'] ?? '')
        <div class="small">{{ $soz['kompaniya_nomi'] }}</div>
        @endif
        @if($soz['kompaniya_telefon'] ?? '')
        <div class="small text-muted">{{ $soz['kompaniya_telefon'] }}</div>
        @endif
        <hr>
        <div class="small text-muted">Chek: <strong>{{ $sotuv->check_raqam }}</strong></div>
        <div class="small text-muted">{{ $sotuv->created_at->format('d.m.Y H:i:s') }}</div>
        @if($sotuv->mijoz_ism)
        <div class="small">Mijoz: {{ $sotuv->mijoz_ism }}</div>
        @endif
        <div class="small text-muted">Kassir: {{ $sotuv->xodim?->ism_familiya }}</div>
        <hr class="border-dashed">

        {{-- Tovarlar --}}
        <table class="table table-sm mb-0" style="font-size:12px">
            <tbody>
                @foreach($sotuv->tafsilot as $t)
                <tr>
                    <td class="text-start ps-0">{{ $t->tovar?->nomi }}<br>
                        <small class="text-muted">{{ $t->miqdor }} × {{ number_format($t->narx,0,'.',' ') }}</small>
                    </td>
                    <td class="text-end pe-0 fw-bold">{{ number_format($t->jami_summa,0,'.',' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="border-dashed">
        <table class="table table-sm mb-0" style="font-size:13px">
            <tr><td class="text-start ps-0 text-muted">Jami:</td><td class="text-end pe-0">{{ number_format($sotuv->umumiy_summa,0,'.',' ') }}</td></tr>
            @if($sotuv->chegirma > 0)
            <tr><td class="text-start ps-0 text-muted">Chegirma:</td><td class="text-end pe-0 text-danger">−{{ number_format($sotuv->chegirma,0,'.',' ') }}</td></tr>
            @endif
            <tr><td class="text-start ps-0 fw-bold fs-6">TO'LOV:</td><td class="text-end pe-0 fw-bold fs-6 text-success">{{ number_format($sotuv->jami_tolov,0,'.',' ') }}</td></tr>
        </table>

        <div class="mt-2 small text-muted">
            <div>{{ ucfirst($sotuv->tolov_turi) }}:
                @if($sotuv->tolov_turi === 'aralash')
                    Naqd {{ number_format($sotuv->naqd_summa,0,'.',' ') }} + Plastik {{ number_format($sotuv->plastik_summa,0,'.',' ') }}
                @elseif($sotuv->tolov_turi === 'naqd')
                    {{ number_format($sotuv->naqd_summa,0,'.',' ') }}
                @else
                    {{ number_format($sotuv->plastik_summa,0,'.',' ') }}
                @endif
            </div>
            @if($sotuv->qayta_pul > 0)
            <div>Qayta: <strong>{{ number_format($sotuv->qayta_pul,0,'.',' ') }}</strong></div>
            @endif
        </div>
        <hr>
        <div class="small text-muted">Rahmat! Qayta kelishingizni kutamiz.</div>
        <div class="small text-muted">{{ $sotuv->filial?->nomi }}</div>
    </div>
</div>

<div class="d-flex gap-2 mb-4">
    <button onclick="window.print()" class="btn btn-primary flex-grow-1">
        <i class="bi bi-printer me-1"></i>Chop etish
    </button>
    <a href="{{ route('pos.index') }}" class="btn btn-success">
        <i class="bi bi-cart me-1"></i>Kassaga
    </a>
    <a href="{{ route('pos.tarix') }}" class="btn btn-outline-secondary">
        <i class="bi bi-list"></i>
    </a>
</div>

</div>
</div>
@endsection
