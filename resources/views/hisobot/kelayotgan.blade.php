@extends('layouts.app')

@section('title', 'Kelayotgan to\'lovlar')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Kelayotgan to'lovlar</li>
@endsection

@section('content')

{{-- Sarlavha --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">
        <i class="bi bi-calendar-check me-1"></i> Kelayotgan to'lovlar
        <span class="badge bg-secondary ms-1">{{ $tulovlar->count() }}</span>
    </h5>
    <small class="text-muted">
        Keyingi <strong>{{ $kunlar }}</strong> kun ichida to'lanishi kerak bo'lgan to'lovlar
        ({{ now()->format('d.m.Y') }} — {{ now()->addDays($kunlar)->format('d.m.Y') }})
    </small>
</div>

{{-- Filtr paneli --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('hisobotlar.kelayotgan') }}" class="row g-2 align-items-end">

            {{-- Kunlar tanlash --}}
            <div class="col-sm-auto">
                <label class="form-label form-label-sm mb-1 text-muted">Kunlar soni</label>
                <select name="kunlar" class="form-select form-select-sm" style="width:auto;min-width:90px">
                    @foreach([1,2,3,5,7,10,14,21,30,31] as $k)
                        <option value="{{ $k }}" {{ $kunlar == $k ? 'selected' : '' }}>{{ $k }} kun</option>
                    @endforeach
                </select>
            </div>

            @if(Auth::user()->isAdmin())
            {{-- Filial --}}
            <div class="col-sm-2">
                <label class="form-label form-label-sm mb-1 text-muted">Filial</label>
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filial</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Mas'ul xodim --}}
            <div class="col-sm-3">
                <label class="form-label form-label-sm mb-1 text-muted">Mas'ul xodim</label>
                <select name="xodim_id" class="form-select form-select-sm">
                    <option value="">Barcha xodimlar</option>
                    @foreach($xodimlar as $x)
                        <option value="{{ $x->id }}" {{ $xodimId == $x->id ? 'selected' : '' }}>{{ $x->ism_familiya }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Obnovit tugmasi --}}
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>Yangilash
                </button>
                @if(request()->anyFilled(['kunlar','xodim_id','filial_id']))
                <a href="{{ route('hisobotlar.kelayotgan') }}" class="btn btn-outline-secondary btn-sm ms-1">
                    <i class="bi bi-x"></i>
                </a>
                @endif
            </div>

        </form>
    </div>
</div>

@if($tulovlar->isEmpty())
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-calendar-check fs-3 d-block mb-2"></i>
        Keyingi {{ $kunlar }} kun ichida to'lov kutilmayapti
    </div>
</div>
@else

{{-- Kunlik guruhlash --}}
@php
    $guruhlangan = $tulovlar->groupBy(fn($t) => $t->tolov_sana?->format('Y-m-d'));
@endphp

@foreach($guruhlangan as $sana => $guruh)
<div class="mb-3">
    <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge bg-primary fs-6 px-3 py-2">
            {{ \Carbon\Carbon::parse($sana)->isoFormat('D MMMM, dddd') }}
        </span>
        <span class="text-muted small">
            {{ $guruh->count() }} ta to'lov —
            jami: <strong>{{ number_format($guruh->sum('tolov_summa'), 0, '.', ' ') }} so'm</strong>
        </span>
        @if(\Carbon\Carbon::parse($sana)->isToday())
            <span class="badge bg-warning text-dark">Bugun</span>
        @elseif(\Carbon\Carbon::parse($sana)->isTomorrow())
            <span class="badge bg-info text-dark">Ertaga</span>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th>Shartnoma</th>
                        <th>Mijoz</th>
                        @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                        <th>Mas'ul xodim</th>
                        <th class="text-end">To'lov summasi</th>
                        <th class="text-end">Qoldiq</th>
                        <th>Oy tartib</th>
                        <th>Holat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($guruh as $g)
                    @php
                        $masulXodim = $g->kredit?->joriyXodim ?? $g->kredit?->xodim;
                    @endphp
                    <tr>
                        <td>
                            @if($g->kredit)
                            <a href="{{ route('kreditlar.show', $g->kredit) }}" class="text-decoration-none fw-medium">
                                {{ $g->kredit->shartnoma_raqam }}
                            </a>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($g->kredit?->mijoz)
                            <a href="{{ route('mijozlar.show', $g->kredit->mijoz) }}" class="text-decoration-none">
                                {{ $g->kredit->mijoz->familiya }} {{ $g->kredit->mijoz->ism }}
                            </a>
                            <div class="text-muted small">{{ $g->kredit->mijoz->telefon }}</div>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        @if(Auth::user()->isAdmin())
                        <td>
                            <span class="badge bg-secondary">{{ $g->kredit?->filial?->kod ?? '—' }}</span>
                        </td>
                        @endif
                        <td>
                            @if($masulXodim)
                                <span class="text-dark small">{{ $masulXodim->ism_familiya }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-medium">
                            {{ number_format($g->tolov_summa, 0, '.', ' ') }}
                        </td>
                        <td class="text-end text-danger">
                            {{ number_format($g->qoldiq_suma, 0, '.', ' ') }}
                        </td>
                        <td class="text-muted small">
                            {{ $g->oylik_tartib }}-oy
                        </td>
                        <td>
                            <span class="badge bg-{{ $g->holat_rangi }}">{{ $g->holat }}</span>
                        </td>
                        <td>
                            @if($g->kredit)
                            <a href="{{ route('kreditlar.tulov.create', $g->kredit) }}"
                               class="btn btn-sm btn-success py-0">
                                <i class="bi bi-cash"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach

@endif
@endsection
