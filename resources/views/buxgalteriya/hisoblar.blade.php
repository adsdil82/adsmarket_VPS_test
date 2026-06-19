@extends('layouts.app')
@section('title', 'Hisoblar rejasi')

@section('content')
<div class="container-fluid px-3 py-3">

  {{-- Sarlavha --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-0 fw-bold"><i class="bi bi-journal-bookmark text-warning me-2"></i>Hisoblar rejasi</h5>
      <small class="text-muted">O'zbekiston Respublikasi milliy buxgalteriya hisoblar rejasi</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
      <i class="bi bi-plus-lg me-1"></i>Yangi hisob
    </button>
  </div>

  @if(session('muvaffaqiyat'))
    <div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
  @endif

  {{-- Yangi hisob formasi --}}
  <div class="collapse mb-3" id="yangiForm">
    <div class="card border-success shadow-sm">
      <div class="card-header bg-success text-white py-2 fw-bold">Yangi hisob qo'shish</div>
      <div class="card-body">
        <form method="POST" action="{{ route('buxgalteriya.hisoblar.store') }}">
          @csrf
          <div class="row g-2">
            <div class="col-md-2">
              <label class="form-label small fw-bold">Hisob raqami <span class="text-danger">*</span></label>
              <input type="text" name="hisob_raqam" class="form-control form-control-sm" placeholder="5010-1" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
              <input type="text" name="nomi" class="form-control form-control-sm" placeholder="Hisob nomi" required>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold">Turi</label>
              <select name="turi" class="form-select form-select-sm">
                <option value="faol">Faol (Aktiv)</option>
                <option value="passiv">Passiv</option>
                <option value="faol-passiv">Faol-Passiv</option>
              </select>
            </div>
            <div class="col-md-1">
              <label class="form-label small fw-bold">Daraja</label>
              <select name="daraja" class="form-select form-select-sm">
                <option value="1">1 — Asosiy</option>
                <option value="2" selected>2 — Sub</option>
                <option value="3">3 — Sub-sub</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Ota hisob</label>
              <select name="ota_id" class="form-select form-select-sm">
                <option value="">— Ota hisob yo'q —</option>
                @foreach($otalar as $ota)
                  <option value="{{ $ota->id }}">{{ $ota->hisob_raqam }} — {{ $ota->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-10">
              <label class="form-label small fw-bold">Izoh</label>
              <input type="text" name="izoh" class="form-control form-control-sm" placeholder="Ixtiyoriy izoh">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="bi bi-save me-1"></i>Saqlash
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Qidiruv --}}
  <div class="mb-2">
    <input type="text" id="qidiruv" class="form-control form-control-sm" placeholder="Hisob raqami yoki nomini qidirish..." style="max-width:320px">
  </div>

  {{-- Hisoblar jadvali --}}
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover table-sm mb-0 align-middle" id="hisoblarJadvali">
        <thead class="table-dark">
          <tr>
            <th style="width:120px">Hisob raqami</th>
            <th>Nomi</th>
            <th style="width:110px">Turi</th>
            <th style="width:80px">Daraja</th>
            <th>Ota hisob</th>
            <th style="width:80px">Holat</th>
            <th style="width:90px" class="text-center">Amal</th>
          </tr>
        </thead>
        <tbody>
          @foreach($hisoblar as $h)
          <tr class="hisob-qator" data-daraja="{{ $h->daraja }}"
              style="{{ $h->holat === 'nofaol' ? 'opacity:0.5' : '' }}">
            <td>
              @if($h->daraja === 2) <span class="ms-2 text-muted">└</span> @endif
              @if($h->daraja === 3) <span class="ms-4 text-muted">└</span> @endif
              <strong class="text-primary">{{ $h->hisob_raqam }}</strong>
            </td>
            <td>{{ $h->nomi }}</td>
            <td>
              <span class="badge {{ $h->turi === 'faol' ? 'bg-success' : ($h->turi === 'passiv' ? 'bg-primary' : 'bg-secondary') }}">
                {{ $h->turi }}
              </span>
            </td>
            <td class="text-center">{{ $h->daraja }}</td>
            <td class="text-muted small">{{ $h->ota?->hisob_raqam }} {{ $h->ota ? '— '.$h->ota->nomi : '' }}</td>
            <td>
              <span class="badge {{ $h->holat === 'faol' ? 'bg-success' : 'bg-secondary' }}">{{ $h->holat }}</span>
            </td>
            <td class="text-center" style="white-space:nowrap">
              <button class="btn btn-sm btn-outline-primary py-0 px-1"
                      onclick="tahrirlashOch({{ $h->id }}, '{{ addslashes($h->nomi) }}', '{{ $h->turi }}', '{{ $h->holat }}', {{ $h->ota_id ?? 'null' }}, '{{ addslashes($h->izoh ?? '') }}')"
                      title="Tahrirlash">
                <i class="bi bi-pencil"></i>
              </button>
              <form method="POST" action="{{ route('buxgalteriya.hisoblar.destroy', $h) }}" class="d-inline"
                    onsubmit="return confirm('{{ $h->hisob_raqam }} hisobni o\'chirish?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="O'chirish">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-footer py-1 text-muted small">
      Jami: <strong>{{ $hisoblar->count() }}</strong> ta hisob
    </div>
  </div>
</div>

{{-- Tahrirlash modali --}}
<div class="modal fade" id="tahrirlashModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2 bg-primary text-white">
        <h6 class="modal-title fw-bold">Hisobni tahrirlash</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="tahrirlashForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
              <input type="text" name="nomi" id="t-nomi" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Turi</label>
              <select name="turi" id="t-turi" class="form-select form-select-sm">
                <option value="faol">Faol (Aktiv)</option>
                <option value="passiv">Passiv</option>
                <option value="faol-passiv">Faol-Passiv</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Holat</label>
              <select name="holat" id="t-holat" class="form-select form-select-sm">
                <option value="faol">Faol</option>
                <option value="nofaol">Nofaol</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Ota hisob</label>
              <select name="ota_id" id="t-ota" class="form-select form-select-sm">
                <option value="">— Ota hisob yo'q —</option>
                @foreach($otalar as $ota)
                  <option value="{{ $ota->id }}">{{ $ota->hisob_raqam }} — {{ $ota->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Izoh</label>
              <input type="text" name="izoh" id="t-izoh" class="form-control form-control-sm">
            </div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
var tahrirlashModal = null;
function tahrirlashOch(id, nomi, turi, holat, otaId, izoh) {
    document.getElementById('tahrirlashForm').action = '/buxgalteriya/hisoblar/' + id;
    document.getElementById('t-nomi').value  = nomi;
    document.getElementById('t-izoh').value  = izoh;
    document.querySelector('#t-turi [value="'+turi+'"]').selected = true;
    document.querySelector('#t-holat [value="'+holat+'"]').selected = true;
    var otaSel = document.getElementById('t-ota');
    otaSel.value = otaId || '';
    if (!tahrirlashModal) tahrirlashModal = new bootstrap.Modal(document.getElementById('tahrirlashModal'));
    tahrirlashModal.show();
}

// Qidiruv
document.getElementById('qidiruv').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#hisoblarJadvali tbody tr').forEach(function(tr) {
        var txt = tr.textContent.toLowerCase();
        tr.style.display = txt.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
