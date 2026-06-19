@extends('layouts.app')
@section('title','Pochta Log Jurnali')
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

    {{-- Filtr --}}
    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <select name="holat" class="form-select form-select-sm">
                        <option value="">Barcha holatlar</option>
                        <option value="yuborildi" {{ request('holat')==='yuborildi' ? 'selected' : '' }}>Yuborildi</option>
                        <option value="xato"      {{ request('holat')==='xato'      ? 'selected' : '' }}>Xato</option>
                        <option value="yaratildi" {{ request('holat')==='yaratildi' ? 'selected' : '' }}>Yaratildi</option>
                        <option value="kutilmoqda"{{ request('holat')==='kutilmoqda'? 'selected' : '' }}>Kutilmoqda</option>
                    </select>
                </div>
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
                    <a href="{{ route('admin.pochta-loglar.index') }}" class="btn btn-sm btn-outline-secondary w-100">
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
                        @if(in_array($log->holat, ['xato', 'yaratildi']) && $log->api_letter_id)
                        <button class="btn btn-xs btn-outline-warning qayta-yuborish-btn"
                            data-log-id="{{ $log->id }}" title="Qayta yuborish">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
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
                                    @if(in_array($log->holat, ['xato', 'yaratildi']) && $log->api_letter_id)
                                    <button type="button" class="btn btn-sm btn-warning qayta-yuborish-btn me-auto"
                                        data-log-id="{{ $log->id }}" data-bs-dismiss="modal">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Qayta yuborish
                                    </button>
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

@push('scripts')
<script>
(function() {
  const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';

  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.qayta-yuborish-btn');
    if (!btn) return;

    const logId = btn.dataset.logId;
    if (!confirm('Server sertifikati bilan qayta yuborish amalga oshirilsinmi?\n\nFaqat server sertifikati (Variant B) sozlangan bo\'lsa ishlaydi.')) return;

    btn.disabled = true;
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(`/admin/gibrid-pochta/qayta-yuborish/${logId}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
      if (d.ok) {
        alert('Muvaffaqiyat: ' + d.xabar);
        window.location.reload();
      } else {
        alert('Xato: ' + (d.xato || 'Noma\'lum xato'));
        btn.disabled = false;
        btn.innerHTML = origHtml;
      }
    })
    .catch(err => {
      alert('Tarmoq xatosi: ' + err.message);
      btn.disabled = false;
      btn.innerHTML = origHtml;
    });
  });
})();
</script>
@endpush

@endsection
