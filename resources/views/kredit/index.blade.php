@extends('layouts.app')

@section('title', 'Shartnomalar')

@section('breadcrumb')
    <li class="breadcrumb-item active">Shartnomalar</li>

{{-- Mobile FAB: yangi shartnoma --}}
@if(Auth::user()->isMenejerYoki())
<a href="{{ route('kreditlar.create') }}"
   class="mobile-fab btn btn-primary"
   title="Yangi shartnoma">
    <i class="bi bi-plus-lg"></i>
</a>
@endif
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0 fw-bold">
        <i class="bi bi-file-earmark-text me-1"></i> Shartnomalar
        <span class="badge bg-secondary ms-1">{{ $kreditlar->total() }}</span>
    </h5>
    @if(Auth::user()->isMenejerYoki())
    <a href="{{ route('kreditlar.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Yangi shartnoma
    </a>
    @endif
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4">
                <input type="search" name="qidiruv" class="form-control form-control-sm"
                       placeholder="Shartnoma raqam, mijoz ismi, telefon..."
                       value="{{ request('qidiruv') }}">
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
                    <option value="faol"          {{ request('holat') === 'faol'          ? 'selected' : '' }}>AKTIV</option>
                    <option value="yopilgan"      {{ request('holat') === 'yopilgan'      ? 'selected' : '' }}>PASSIV</option>
                    <option value="muddati_otgan" {{ request('holat') === 'muddati_otgan' ? 'selected' : '' }}>Muddati o'tgan</option>
                    <option value="muzlatilgan"   {{ request('holat') === 'muzlatilgan'   ? 'selected' : '' }}>Muzlatilgan</option>
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i> Qidirish
                </button>
                <a href="{{ route('kreditlar.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>


{{-- Mobile card ro'yxat (faqat telefonda) --}}
<div class="d-md-none">
    @forelse($kreditlar as $k)
    <div class="card border-0 shadow-sm mb-2 {{ $k->holat === 'muddati_otgan' ? 'border-danger border-opacity-25' : '' }}">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <a href="{{ route('kreditlar.show', $k) }}" class="fw-bold text-decoration-none fs-6">
                    {{ $k->shartnoma_raqam }}
                </a>
                <span class="badge bg-{{ $k->holat_rangi }}">{{ $k->holatNomi }}</span>
            </div>
            <div class="fw-medium mb-1">{{ $k->mijoz->familiya }} {{ $k->mijoz->ism }}</div>
            <div class="text-muted small mb-2">{{ $k->mijoz->telefon }}</div>
            <div class="row g-1 mb-2" style="font-size:.8rem">
                <div class="col-6">
                    <span class="text-muted">Jami:</span>
                    <strong>{{ number_format($k->jami_summa, 0, '.', ' ') }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Qoldiq:</span>
                    <strong class="{{ $k->qoldiq_qarz > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($k->qoldiq_qarz, 0, '.', ' ') }}
                    </strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Muddat:</span>
                    {{ $k->tugash_sana ? $k->tugash_sana->format('d.m.Y') : '—' }}
                </div>
                @if(Auth::user()->isAdmin())
                <div class="col-6">
                    <span class="text-muted">Filial:</span>
                    <span class="badge bg-secondary">{{ $k->filial->kod }}</span>
                </div>
                @endif
            </div>
            @php
                $foiz = $k->tolov_foizi;
            @endphp
            <div class="progress" style="height:5px;border-radius:3px;margin-bottom:6px">
                <div class="progress-bar bg-{{ $k->holat_rangi }}" style="width:{{ $foiz }}%"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ $k->xodim->ism_familiya }}</small>
                <a href="{{ route('kreditlar.show', $k) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                    <i class="bi bi-eye me-1"></i>Ko'rish
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center text-muted py-5">
        <i class="bi bi-search fs-3 d-block mb-2"></i>
        Shartnomalar topilmadi
    </div>
    @endforelse
    @if($kreditlar->hasPages())
    <div class="mt-2">
        {{ $kreditlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Jadval (desktop) --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Shartnoma</th>
                    <th>Mijoz</th>
                    @if(Auth::user()->isAdmin())<th>Filial</th>@endif
                    <th>Sana</th>
                    <th class="text-end" title="Jami shartnoma summasi">Jami</th>
                    <th class="text-end" title="Boshlangich to'lov (avans)">Oldindan</th>
                    <th class="text-end" title="Kredit summasi">Kredit</th>
                    <th class="text-end">Qoldiq</th>
                    <th>Holat</th>
                    <th style="min-width:90px">Progress</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($kreditlar as $k)
                <tr class="{{ $k->holat === 'muddati_otgan' ? 'row-muddati-otgan' : '' }}">
                    <td>
                        <a href="{{ route('kreditlar.show', $k) }}" class="text-decoration-none fw-medium">
                            {{ $k->shartnoma_raqam }}
                        </a>
                        <div class="text-muted small">{{ $k->xodim->ism_familiya }}</div>
                    </td>
                    <td>
                        <a href="{{ route('mijozlar.show', $k->mijoz) }}" class="text-decoration-none">
                            {{ $k->mijoz->familiya }} {{ $k->mijoz->ism }}
                        </a>
                        <div class="text-muted small">{{ $k->mijoz->telefon }}</div>
                    </td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary">{{ $k->filial->kod }}</span></td>
                    @endif
                    <td class="small text-muted">
                        {{ $k->boshlanish_sana ? $k->boshlanish_sana->format('d.m.Y') : '—' }}<br>
                        {{ $k->tugash_sana ? $k->tugash_sana->format('d.m.Y') : '—' }}
                    </td>
                    <td class="text-end text-muted small">
                        {{ number_format($k->jami_summa, 0, '.', ' ') }}
                    </td>
                    <td class="text-end small" style="color:#0077b6">
                        @if($k->boshlangich_tolov > 0)
                            {{ number_format($k->boshlangich_tolov, 0, '.', ' ') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($k->kredit_summa, 0, '.', ' ') }}</td>
                    <td class="text-end {{ $k->qoldiq_qarz > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($k->qoldiq_qarz, 0, '.', ' ') }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $k->holat_rangi }} badge-holat">{{ $k->holatNomi }}</span>
                    </td>
                    <td>
                        @php
                            $foiz = $k->tolov_foizi;
                            $barRang = $foiz >= 100 ? 'bg-success'
                                : ($foiz >= 70 ? 'bg-info'
                                : ($foiz >= 40 ? 'bg-warning'
                                : 'bg-danger'));
                        @endphp
                        <div class="progress" style="height:6px"
                             title="Jami: {{ number_format($k->jami_summa,0,'.',' ') }} | To'landi: {{ number_format($k->boshlangich_tolov+$k->tolov_qilingan,0,'.',' ') }} ({{ $foiz }}%)">
                            <div class="progress-bar {{ $barRang }}" style="width:{{ $foiz }}%"></div>
                        </div>
                        <div class="text-muted" style="font-size:10px">{{ $foiz }}%</div>
                    </td>
                    <td>
                        <a href="{{ route('kreditlar.show', $k) }}"
                           class="btn btn-sm btn-outline-primary py-0">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted py-5">
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        Shartnomalar topilmadi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kreditlar->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            {{ $kreditlar->firstItem() }}–{{ $kreditlar->lastItem() }} / {{ $kreditlar->total() }}
        </small>
        {{ $kreditlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection