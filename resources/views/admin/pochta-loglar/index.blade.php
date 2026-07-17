@extends('layouts.app')
@section('title','Pochta Log Jurnali')

@push('styles')
<style>
.holat-tabs { display:flex; gap:5px; flex-wrap:nowrap; overflow-x:auto; padding-bottom:2px; }
.holat-tabs::-webkit-scrollbar { height:4px; }
.holat-tab {
    display:inline-flex; align-items:center; gap:5px; white-space:nowrap; text-decoration:none;
    padding:6px 13px; border-radius:7px; font-size:.74rem; font-weight:700;
    background:#eef4ff; color:#1e3a8a; border:1px solid #93c5fd;
}
.holat-tab:hover { background:#dbeafe; color:#1e3a8a; }
.holat-tab.active { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
</style>
@endpush

@php
$holatTablari = [
    ''            => ['label' => 'Barchasi',   'icon' => 'bi-list-ul'],
    'yuborildi'   => ['label' => 'Yuborildi',  'icon' => 'bi-check-circle'],
    'xato'        => ['label' => 'Xato',       'icon' => 'bi-exclamation-triangle'],
    'yaratildi'   => ['label' => 'Yaratildi',  'icon' => 'bi-file-earmark-plus'],
    'kutilmoqda'  => ['label' => 'Kutilmoqda', 'icon' => 'bi-hourglass-split'],
];
@endphp

@section('content')
<div class="container-fluid px-3 py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-journal-text me-1 text-primary"></i>
            Pochta Xatlar Jurnali
        </h5>
        <a href="{{ route('admin.sozlamalar') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-gear me-1"></i>Sozlamalar
        </a>
    </div>

    {{-- Statistika --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="h4 mb-0">{{ $statistika['jami'] }}</div>
                <div class="small text-muted">Jami</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="h4 mb-0 text-success">{{ $statistika['yuborildi'] }}</div>
                <div class="small text-muted">Yuborildi</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="h4 mb-0 text-danger">{{ $statistika['xato'] }}</div>
                <div class="small text-muted">Xato</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-2">
                <div class="h4 mb-0 text-info">{{ $statistika['bugun'] }}</div>
                <div class="small text-muted">Bugun</div>
            </div>
        </div>
    </div>

    {{-- Holat tab --}}
    <div class="holat-tabs mb-2">
        @foreach($holatTablari as $key => $h)
        <a class="holat-tab {{ request('holat', '') === $key ? 'active' : '' }}"
           href="{{ route('admin.gibrid-pochta.pochta-loglar.index', array_merge(request()->except(['holat', 'page']), $key !== '' ? ['holat' => $key] : [])) }}">
            <i class="bi {{ $h['icon'] }}"></i> {{ $h['label'] }}
        </a>
        @endforeach
    </div>

    {{-- Filtr --}}
    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <input type="hidden" name="holat" value="{{ request('holat') }}">
                <div class="col-md-2">
                    <input type="number" name="kredit_id" class="form-control form-control-sm"
                        placeholder="Kredit ID" value="{{ request('kredit_id') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="dan" class="form-control form-control-sm"
                        value="{{ request('dan') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="gacha" class="form-control form-control-sm"
                        value="{{ request('gacha') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Filtrlash
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.gibrid-pochta.pochta-loglar.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        Tozalash
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Jadval --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Sana</th>
                        <th>Kredit</th>
                        <th>Mijoz</th>
                        <th>Shablon</th>
                        <th>Manzil</th>
                        <th>Holat</th>
                        <th style="width:80px">Amal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loglar as $log)
                    <tr>
                        <td class="text-muted small">{{ $log->id }}</td>
                        <td class="small">{{ $log->created_at->format('d.m.y H:i') }}</td>
                        <td class="small">
                            @if($log->kredit)
                                <a href="{{ route('kreditlar.show', $log->reg_kredit_id) }}" class="text-decoration-none">
                                    {{ $log->kredit->shartnoma_raqam ?? 'K-'.$log->reg_kredit_id }}
                                </a>
                            @else
                                <span class="text-muted">{{ $log->reg_kredit_id }}</span>
                            @endif
                        </td>
                        <td class="small">{{ $log->receiver }}</td>
                        <td class="small text-muted">{{ $log->shablon?->nomi ?? '—' }}</td>
                        <td class="small text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                            title="{{ $log->address }}">
                            {{ $log->address }}
                        </td>
                        <td>{!! $log->holatBadge() !!}</td>
                        <td>
                            <button class="btn btn-xs btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                <i class="bi bi-eye"></i>
                            </button>
                        @if($log->holat === 'yuborildi' && $log->api_letter_id)
                        <a href="{{ route('admin.gibrid-pochta.kvitansiya', $log) }}"
                           class="btn btn-xs btn-outline-success" title="Kvitansiya PDF">
                            <i class="bi bi-file-pdf"></i>
                        </a>
                        @endif
                        </td>
                    </tr>

                    {{-- Log detail modal --}}
                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header py-2">
                                    <h6 class="modal-title">Log #{{ $log->id }} — {{ $log->receiver }}</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body small">
                                    <dl class="row mb-2">
                                        <dt class="col-4">API Letter ID</dt>
                                        <dd class="col-8">{{ $log->api_letter_id ?? '—' }}</dd>
                                        <dt class="col-4">Holat</dt>
                                        <dd class="col-8">{!! $log->holatBadge() !!}</dd>
                                        <dt class="col-4">Yaratildi</dt>
                                        <dd class="col-8">{{ $log->yaratildi_vaqt?->format('d.m.Y H:i:s') ?? '—' }}</dd>
                                        <dt class="col-4">Yuborildi</dt>
                                        <dd class="col-8">{{ $log->yuborildi_vaqt?->format('d.m.Y H:i:s') ?? '—' }}</dd>
                                        @if($log->xato_xabar)
                                        <dt class="col-4 text-danger">Xato</dt>
                                        <dd class="col-8 text-danger">{{ $log->xato_xabar }}</dd>
                                        @endif
                                    </dl>
                                    @if($log->so_rov)
                                    <div class="mb-2">
                                        <strong>So'rov (API):</strong>
                                        <pre class="bg-light p-2 rounded small" style="max-height:150px;overflow:auto;">{{ json_encode($log->so_rov, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                    @if($log->javob)
                                    <div>
                                        <strong>Javob (API):</strong>
                                        <pre class="bg-light p-2 rounded small" style="max-height:150px;overflow:auto;">{{ json_encode($log->javob, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer py-2 gap-2">
                                    @if($log->holat === 'yuborildi' && $log->api_letter_id)
                                    <a href="{{ route('admin.gibrid-pochta.kvitansiya', $log) }}"
                                       class="btn btn-sm btn-outline-success me-auto">
                                        <i class="bi bi-file-pdf me-1"></i>Kvitansiya PDF
                                    </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Yopish</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Hozircha log yozuvlari yo'q.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loglar->hasPages())
        <div class="card-footer py-2">
            {{ $loglar->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>

@endsection
