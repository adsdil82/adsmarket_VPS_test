@extends('layouts.app')
@section('title', 'Kechikish analizi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Kechikish analizi</li>
@endsection

@push('styles')
<style>
.aging-header { white-space: nowrap; font-size: .72rem; }
.aging-cell   { text-align: right; font-size: .8rem; white-space: nowrap; }
.d30-col  { background: rgba(255,193,7,.08); }
.d60-col  { background: rgba(255,152,0,.1); }
.d90-col  { background: rgba(255,87,34,.12); }
.d120-col { background: rgba(244,67,54,.13); }
.d150-col { background: rgba(211,47,47,.14); }
.d180-col { background: rgba(183,28,28,.15); }
.d180p-col{ background: rgba(100,0,0,.15); }
.jami-kech{ background: rgba(183,28,28,.1); font-weight:700; }
.bucket-bar { height: 4px; border-radius: 2px; margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-clock-history me-1 text-danger"></i> Kechikish analizi (Aging Report)
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('hisobotlar.excel','aging') }}?sana={{ $sana }}&filial_id={{ $filialId }}"
           class="btn btn-sm btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Excel yuklab olish
        </a>
        <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label small mb-1">Hisobot sanasi (shu kunga qoldiq)</label>
                <input type="date" name="sana" class="form-control form-control-sm" value="{{ $sana }}">
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
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="bi bi-funnel me-1"></i> Hisoblash
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Jami satrlar --}}
<div class="row g-2 mb-3">
    @php
    $buckets = [
        ['d30','1-30 kun','warning','#ffc107'],
        ['d60','31-60 kun','orange','#ff9800'],
        ['d90','61-90 kun','danger','#f44336'],
        ['d120','91-120 kun','danger','#e53935'],
        ['d150','121-150 kun','danger','#c62828'],
        ['d180','151-180 kun','danger','#b71c1c'],
        ['d180p','180+ kun','dark','#6d0000'],
    ];
    $jamiAll = max(1, $jami['jami']);
    @endphp

    @foreach($buckets as [$key,$label,$color,$hex])
    <div class="col-6 col-sm-3 col-lg">
        <div class="card border-0 shadow-sm text-center py-2 px-1">
            <div class="small fw-bold" style="color:{{ $hex }}">{{ $label }}</div>
            <div class="fw-bold" style="font-size:.9rem">{{ number_format($jami[$key]/1000000,1) }} mln</div>
            <div style="height:4px;background:#eee;border-radius:2px;margin-top:4px">
                <div style="height:100%;border-radius:2px;background:{{ $hex }};width:{{ min(100,$jami[$key]/$jamiAll*100) }}%"></div>
            </div>
        </div>
    </div>
    @endforeach
    <div class="col-6 col-sm-3 col-lg">
        <div class="card border-0 shadow-sm text-center py-2 px-1 border-danger">
            <div class="small fw-bold text-danger">JAMI</div>
            <div class="fw-bold text-danger" style="font-size:.9rem">{{ number_format($jami['jami']/1000000,1) }} mln</div>
            <div class="text-muted" style="font-size:.72rem">{{ $jami['soni'] }} ta kredit</div>
        </div>
    </div>
</div>

{{-- Aging jadval --}}
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 small">
            <i class="bi bi-table me-1"></i>
            Kechikkan kreditlar — <strong>{{ \Carbon\Carbon::parse($sana)->format('d.m.Y') }}</strong> sanasiga
        </h6>
        <span class="badge bg-danger">{{ $jami['soni'] }} ta</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 table-sm" style="font-size:.8rem">
            <thead class="table-light">
                <tr>
                    <th class="aging-header">#</th>
                    <th class="aging-header">Familiya</th>
                    <th class="aging-header">Shartnoma</th>
                    @if(Auth::user()->isAdmin())
                    <th class="aging-header">Filial</th>
                    @endif
                    <th class="aging-header text-end">Kredit</th>
                    <th class="aging-header text-end">Qoldiq</th>
                    <th class="aging-header text-end d30-col">1-30</th>
                    <th class="aging-header text-end d60-col">31-60</th>
                    <th class="aging-header text-end d90-col">61-90</th>
                    <th class="aging-header text-end d120-col">91-120</th>
                    <th class="aging-header text-end d150-col">121-150</th>
                    <th class="aging-header text-end d180-col">151-180</th>
                    <th class="aging-header text-end d180p-col">180+</th>
                    <th class="aging-header text-end jami-kech">Jami kechikkan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $r)
                <tr>
                    <td class="text-muted">{{ $i+1 }}</td>
                    <td>
                        <div class="fw-medium">{{ $r->familiya }}</div>
                        <div class="text-muted" style="font-size:.7rem">{{ $r->telefon }}</div>
                    </td>
                    <td>
                        <a href="{{ route('kreditlar.show',$r->id) }}" class="text-decoration-none">
                            {{ $r->shartnoma_raqam }}
                        </a>
                        @if($r->min_kun > 180)
                            <span class="badge bg-dark" style="font-size:.6rem">{{ $r->min_kun }}k</span>
                        @elseif($r->min_kun > 90)
                            <span class="badge bg-danger" style="font-size:.6rem">{{ $r->min_kun }}k</span>
                        @endif
                    </td>
                    @if(Auth::user()->isAdmin())
                    <td><span class="badge bg-secondary" style="font-size:.65rem">{{ $r->filial_kod }}</span></td>
                    @endif
                    <td class="aging-cell text-muted">{{ number_format($r->kredit_summa/1000000,1) }}m</td>
                    <td class="aging-cell">{{ number_format($r->qoldiq_qarz/1000000,1) }}m</td>
                    <td class="aging-cell d30-col">{{ $r->d30 > 0 ? number_format($r->d30/1000,0) : '—' }}</td>
                    <td class="aging-cell d60-col">{{ $r->d60 > 0 ? number_format($r->d60/1000,0) : '—' }}</td>
                    <td class="aging-cell d90-col">{{ $r->d90 > 0 ? number_format($r->d90/1000,0) : '—' }}</td>
                    <td class="aging-cell d120-col">{{ $r->d120 > 0 ? number_format($r->d120/1000,0) : '—' }}</td>
                    <td class="aging-cell d150-col">{{ $r->d150 > 0 ? number_format($r->d150/1000,0) : '—' }}</td>
                    <td class="aging-cell d180-col">{{ $r->d180 > 0 ? number_format($r->d180/1000,0) : '—' }}</td>
                    <td class="aging-cell d180p-col" style="color:#8b0000">{{ $r->d180p > 0 ? number_format($r->d180p/1000,0) : '—' }}</td>
                    <td class="aging-cell jami-kech text-danger">{{ number_format($r->jami_kechikkan/1000,0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="{{ Auth::user()->isAdmin() ? 6 : 5 }}" class="text-end">JAMI:</td>
                    <td class="aging-cell d30-col">{{ number_format($jami['d30']/1000,0) }}</td>
                    <td class="aging-cell d60-col">{{ number_format($jami['d60']/1000,0) }}</td>
                    <td class="aging-cell d90-col">{{ number_format($jami['d90']/1000,0) }}</td>
                    <td class="aging-cell d120-col">{{ number_format($jami['d120']/1000,0) }}</td>
                    <td class="aging-cell d150-col">{{ number_format($jami['d150']/1000,0) }}</td>
                    <td class="aging-cell d180-col">{{ number_format($jami['d180']/1000,0) }}</td>
                    <td class="aging-cell d180p-col">{{ number_format($jami['d180p']/1000,0) }}</td>
                    <td class="aging-cell jami-kech text-danger">{{ number_format($jami['jami']/1000,0) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @if($rows->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">
            {{ $rows->firstItem() }}–{{ $rows->lastItem() }} / {{ $jami['soni'] }} ta kredit
            &nbsp;|&nbsp; Sahifa {{ $rows->currentPage() }} / {{ $rows->lastPage() }}
        </small>
        {{ $rows->links('pagination::bootstrap-5') }}
    </div>
    @endif
    <div class="card-footer py-1">
        <small class="text-muted">* Summalar ming so'mda ko'rsatilgan. Excel da to'liq summa.</small>
    </div>
</div>
@endsection
