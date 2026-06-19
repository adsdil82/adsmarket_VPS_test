@extends('layouts.app')
@section('title','Audit log')

@push('styles')
<style>
.audit-row td { vertical-align: top; font-size: .82rem; }
.audit-row:hover { background: rgba(0,0,0,.02); }
.diff-block { font-size: .78rem; }
.diff-old { color: #dc3545; text-decoration: line-through; opacity: .75; }
.diff-new { color: #198754; font-weight: 600; }
.diff-arrow { color: #aaa; margin: 0 3px; }
.field-label { color: #666; font-size: .72rem; display: block; margin-bottom: 1px; }
.context-link { font-weight: 600; color: #0d6efd; text-decoration: none; }
.context-link:hover { text-decoration: underline; }
.event-badge-created { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.event-badge-updated { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.event-badge-deleted { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.event-badge { font-size: .72rem; font-weight: 700; padding: 2px 8px;
    border-radius: 10px; letter-spacing: .3px; }
.model-tag { background: #e0e7ff; color: #3730a3; font-size: .72rem;
    padding: 2px 7px; border-radius: 8px; font-weight: 600; }
.val-money { font-family: monospace; }
.toggle-detail { cursor: pointer; color: #6366f1; font-size: .72rem; border: none;
    background: none; padding: 0; text-decoration: underline dotted; }
.detail-panel { background: #f8f9fa; border-radius: 6px; padding: 8px 12px;
    margin-top: 4px; display: none; }
</style>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
<li class="breadcrumb-item active">Audit log</li>
@endsection

@section('content')

@php
// Pul formatlovchi
function auditSumma($v) {
    if (is_numeric($v) && abs($v) > 100) return number_format((float)$v, 0, '.', ' ') . ' so\'m';
    return htmlspecialchars((string)$v);
}
// Kalit nomi
function auditMaydon($key, $maydonlar) {
    // _nom qo'shimchasini tozalash
    $k = preg_replace('/^_(.+)_nom$/', '$1', $key);
    return $maydonlar[$k] ?? $maydonlar[$key] ?? ucfirst(str_replace('_',' ',$key));
}
// Qiymatni formatlash
function auditQiymat($key, $val, $maydonlar) {
    if ($val === null) return '<em class="text-muted">bo\'sh</em>';
    $label = auditMaydon($key, $maydonlar);
    if (str_contains($label, 'summa') || str_contains($label,'qarz') || $key === 'summa') {
        return '<span class="val-money">' . number_format((float)$val, 0, '.', ' ') . ' so\'m</span>';
    }
    if (str_contains($key, 'sana') || str_contains($key, '_at')) {
        try { return \Carbon\Carbon::parse($val)->format('d.m.Y H:i'); } catch(\Exception $e) {}
    }
    if ($key === 'holat') {
        $map = ['faol'=>'AKTIV','yopilgan'=>'PASSIV','nofaol'=>'PASSIV','muddati_otgan'=>"Muddati o'tgan",'muzlatilgan'=>'Muzlatilgan'];
        return $map[$val] ?? $val;
    }
    if (str_ends_with($key, '_id') || str_ends_with($key, '_nom')) return '#' . htmlspecialchars((string)$val);
    return htmlspecialchars(mb_substr((string)$val, 0, 100));
}
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-journal-text me-2 text-info"></i>Audit log — Barcha harakatlar
    </h5>
    <span class="badge bg-secondary fs-6">{{ number_format($auditlar->total()) }} ta yozuv</span>
</div>

{{-- ── Filter ─────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-3">
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Barcha foydalanuvchilar</option>
                    @foreach($foydalanuvchilar as $u)
                        <option value="{{ $u->id }}" {{ request('user_id')==$u->id?'selected':'' }}>
                            {{ $u->ism_familiya }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="event" class="form-select form-select-sm">
                    <option value="">Barcha amallar</option>
                    <option value="created" {{ request('event')==='created'?'selected':'' }}>Yaratildi</option>
                    <option value="updated" {{ request('event')==='updated'?'selected':'' }}>Tahrirlandi</option>
                    <option value="deleted" {{ request('event')==='deleted'?'selected':'' }}>O'chirildi</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select name="model" class="form-select form-select-sm">
                    <option value="">Barcha ob'ektlar</option>
                    @foreach($modellar_nomi as $key => $nom)
                        <option value="{{ $key }}" {{ request('model')===$key?'selected':'' }}>{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <input type="date" name="dan_sana" class="form-control form-control-sm"
                       placeholder="Dan" value="{{ request('dan_sana') }}">
            </div>
            <div class="col-sm-2">
                <input type="date" name="gacha_sana" class="form-control form-control-sm"
                       placeholder="Gacha" value="{{ request('gacha_sana') }}">
            </div>
            <div class="col-sm-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('admin.audit') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── Jadval ─────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:120px">Sana / Vaqt</th>
                    <th style="width:160px">Foydalanuvchi</th>
                    <th style="width:90px">Amal</th>
                    <th style="width:110px">Ob'ekt</th>
                    <th>Tafsilot</th>
                    <th style="width:110px">IP manzil</th>
                </tr>
            </thead>
            <tbody>
            @forelse($auditlar as $a)
            @php
                $model   = class_basename($a->auditable_type ?? '');
                $modelNom= $modellar_nomi[$model] ?? $model;
                $newVals = $a->_new_vals ?? (json_decode($a->new_values ?? '{}', true) ?? []);
                $oldVals = $a->_old_vals ?? (json_decode($a->old_values ?? '{}', true) ?? []);
                $ctx     = $a->_context ?? "#$a->auditable_id";

                // O'zgargan maydonlar
                $ozgarganlar = [];
                foreach ($newVals as $key => $newVal) {
                    if (str_starts_with($key,'_')) continue; // helper fields
                    $oldVal = $oldVals[$key] ?? null;
                    if ($a->event === 'updated' && (string)$newVal !== (string)$oldVal) {
                        $ozgarganlar[$key] = ['eski' => $oldVal, 'yangi' => $newVal];
                    }
                }
                // password ni maxfiy qilamiz
                if (isset($ozgarganlar['password'])) {
                    $ozgarganlar['password'] = ['eski' => '***', 'yangi' => '***'];
                }

                $evRangi = match($a->event) {
                    'created' => 'created', 'updated' => 'updated',
                    'deleted' => 'deleted', default   => 'secondary',
                };
                $evNom = match($a->event) {
                    'created' => 'Yaratildi', 'updated' => 'Tahrirlandi',
                    'deleted' => "O'chirildi", default => $a->event,
                };
                $rowId = 'adr-' . $a->id;
            @endphp
            <tr class="audit-row {{ $a->event === 'deleted' ? 'table-danger' : '' }}">

                {{-- Sana/Vaqt --}}
                <td>
                    <div class="fw-medium">{{ \Carbon\Carbon::parse($a->created_at)->format('d.m.Y') }}</div>
                    <div class="text-muted" style="font-size:.75rem">
                        {{ \Carbon\Carbon::parse($a->created_at)->format('H:i:s') }}
                    </div>
                    <div class="text-muted" style="font-size:.68rem">
                        {{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}
                    </div>
                </td>

                {{-- Foydalanuvchi --}}
                <td>
                    <div class="fw-medium">{{ $a->ism_familiya ?? '🤖 Tizim' }}</div>
                    @if($a->login)
                    <div class="text-muted" style="font-size:.72rem">{{ $a->login }}</div>
                    @endif
                    @if($a->rol)
                    <span class="badge bg-secondary" style="font-size:.65rem">{{ $a->rol }}</span>
                    @endif
                </td>

                {{-- Amal --}}
                <td>
                    <span class="event-badge event-badge-{{ $evRangi }}">{{ $evNom }}</span>
                </td>

                {{-- Ob'ekt --}}
                <td>
                    <span class="model-tag">{{ $modelNom }}</span>
                    <div class="mt-1" style="font-size:.75rem">
                        @if($model === 'RegKredit')
                            <a href="{{ route('kreditlar.show', $a->auditable_id) }}"
                               class="context-link" target="_blank">
                                {{ $ctx }}
                            </a>
                        @elseif($model === 'Mijoz')
                            <a href="{{ route('mijozlar.show', $a->auditable_id) }}"
                               class="context-link" target="_blank">
                                {{ $ctx }}
                            </a>
                        @else
                            <span class="text-muted">{{ $ctx }}</span>
                        @endif
                        <div class="text-muted" style="font-size:.68rem">ID: #{{ $a->auditable_id }}</div>
                    </div>
                </td>

                {{-- Tafsilot --}}
                <td>
                    @if($a->event === 'created')
                        <div class="diff-block">
                            <span class="text-success fw-medium">✓ Yangi yozuv yaratildi</span>
                            @php $crVals = array_filter($newVals, fn($k) => !str_starts_with($k,'_') && !in_array($k,['password','remember_token','created_at','updated_at','eski_id']), ARRAY_FILTER_USE_KEY); @endphp
                            @if(count($crVals))
                            <div class="mt-1">
                            @foreach(array_slice($crVals, 0, 4, true) as $key => $val)
                                <div class="mb-1">
                                    <span class="field-label">{{ auditMaydon($key, $maydonlar) }}</span>
                                    <span>{!! auditQiymat($key, $val, $maydonlar) !!}</span>
                                </div>
                            @endforeach
                            @if(count($crVals) > 4)
                            <button class="toggle-detail" onclick="toggleDetail('{{ $rowId }}')">
                                + {{ count($crVals)-4 }} ta maydon ko'rish ▼
                            </button>
                            <div class="detail-panel" id="{{ $rowId }}">
                                @foreach(array_slice($crVals, 4, null, true) as $key => $val)
                                <div class="mb-1">
                                    <span class="field-label">{{ auditMaydon($key, $maydonlar) }}</span>
                                    <span>{!! auditQiymat($key, $val, $maydonlar) !!}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            </div>
                            @endif
                        </div>

                    @elseif($a->event === 'updated' && count($ozgarganlar))
                        <div class="diff-block">
                            @foreach(array_slice($ozgarganlar, 0, 3, true) as $key => $diff)
                            <div class="mb-1">
                                <span class="field-label">{{ auditMaydon($key, $maydonlar) }}</span>
                                <span class="diff-old">{!! auditQiymat($key, $diff['eski'], $maydonlar) !!}</span>
                                <span class="diff-arrow">→</span>
                                <span class="diff-new">{!! auditQiymat($key, $diff['yangi'], $maydonlar) !!}</span>
                            </div>
                            @endforeach
                            @if(count($ozgarganlar) > 3)
                            <button class="toggle-detail" onclick="toggleDetail('{{ $rowId }}')">
                                + {{ count($ozgarganlar)-3 }} ta o'zgarish ▼
                            </button>
                            <div class="detail-panel" id="{{ $rowId }}">
                                @foreach(array_slice($ozgarganlar, 3, null, true) as $key => $diff)
                                <div class="mb-1">
                                    <span class="field-label">{{ auditMaydon($key, $maydonlar) }}</span>
                                    <span class="diff-old">{!! auditQiymat($key, $diff['eski'], $maydonlar) !!}</span>
                                    <span class="diff-arrow">→</span>
                                    <span class="diff-new">{!! auditQiymat($key, $diff['yangi'], $maydonlar) !!}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                    @elseif($a->event === 'deleted')
                        <div class="diff-block">
                            <span class="text-danger fw-medium">✗ Yozuv o'chirildi</span>
                            @php $delVals = array_filter($oldVals, fn($k) => !str_starts_with($k,'_') && !in_array($k,['password','remember_token']), ARRAY_FILTER_USE_KEY); @endphp
                            @if(count($delVals))
                            <button class="toggle-detail ms-2" onclick="toggleDetail('{{ $rowId }}')">
                                Ma'lumotlarni ko'rish ▼
                            </button>
                            <div class="detail-panel" id="{{ $rowId }}">
                                @foreach(array_slice($delVals, 0, 10, true) as $key => $val)
                                <div class="mb-1">
                                    <span class="field-label">{{ auditMaydon($key, $maydonlar) }}</span>
                                    <span class="diff-old">{!! auditQiymat($key, $val, $maydonlar) !!}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>

                {{-- IP --}}
                <td class="font-monospace text-muted" style="font-size:.75rem">
                    {{ $a->ip_address ?? '—' }}
                    @if($a->user_agent)
                    <div style="font-size:.65rem;color:#bbb" title="{{ $a->user_agent }}">
                        {{ Str::limit($a->user_agent, 25) }}
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <i class="bi bi-journal-x fs-2 d-block mb-2 opacity-25"></i>
                    Audit log bo'sh
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($auditlar->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <small class="text-muted">
            {{ $auditlar->firstItem() }}–{{ $auditlar->lastItem() }} / {{ number_format($auditlar->total()) }}
        </small>
        {{ $auditlar->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function toggleDetail(id) {
    var el  = document.getElementById(id);
    var btn = el.previousElementSibling;
    if (el.style.display === 'block') {
        el.style.display = 'none';
        btn.textContent = btn.textContent.replace('▲','▼');
    } else {
        el.style.display = 'block';
        btn.textContent = btn.textContent.replace('▼','▲');
    }
}
</script>
@endpush
