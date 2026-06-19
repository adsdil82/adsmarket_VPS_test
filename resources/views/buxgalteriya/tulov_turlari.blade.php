@extends('layouts.app')
@section('title', "To'lov turlari (yangi)")

@section('content')
<div class="container-fluid px-3 py-3">

  {{-- Sarlavha --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front text-primary me-2"></i>To'lov turlari (yangi)</h5>
      <small class="text-muted">Buxgalteriya hisoblariga bog'langan to'lov turlari</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#yangiForm">
      <i class="bi bi-plus-lg me-1"></i>Yangi tur
    </button>
  </div>

  @if(session('muvaffaqiyat'))
    <div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
  @endif

  {{-- Yangi tur formasi --}}
  <div class="collapse mb-3" id="yangiForm">
    <div class="card border-success shadow-sm">
      <div class="card-header bg-success text-white py-2 fw-bold">Yangi to'lov turi qo'shish</div>
      <div class="card-body">
        <form method="POST" action="{{ route('buxgalteriya.tulov_turlari.store') }}">
          @csrf
          <div class="row g-2">
            <div class="col-md-2">
              <label class="form-label small fw-bold">Kod <span class="text-danger">*</span></label>
              <input type="text" name="kod" class="form-control form-control-sm" placeholder="KASSA" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
              <input type="text" name="nomi" class="form-control form-control-sm" placeholder="To'lov turi nomi" required>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold">Kategoriya <span class="text-danger">*</span></label>
              <select name="kategoriya" class="form-select form-select-sm">
                <option value="kassa">Kassa</option>
                <option value="terminal">Terminal</option>
                <option value="bank">Bank</option>
                <option value="boshqa">Boshqa</option>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold">Debet hisob</label>
              <select name="debet_hisob_id" class="form-select form-select-sm">
                <option value="">— tanlanmagan —</option>
                @foreach($hisoblar as $h)
                  <option value="{{ $h->id }}">{{ $h->hisob_raqam }} — {{ Str::limit($h->nomi, 30) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold">Kredit hisob</label>
              <select name="kredit_hisob_id" class="form-select form-select-sm">
                <option value="">— tanlanmagan —</option>
                @foreach($hisoblar as $h)
                  <option value="{{ $h->id }}">{{ $h->hisob_raqam }} — {{ Str::limit($h->nomi, 30) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-9">
              <label class="form-label small fw-bold">Izoh</label>
              <input type="text" name="izoh" class="form-control form-control-sm" placeholder="Ixtiyoriy izoh">
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" class="btn btn-success btn-sm w-100">
                <i class="bi bi-save me-1"></i>Saqlash
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Jadval --}}
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover table-sm mb-0 align-middle">
        <thead class="table-dark">
          <tr>
            <th style="width:90px">Kod</th>
            <th>Nomi</th>
            <th style="width:100px">Kategoriya</th>
            <th>Debet hisob</th>
            <th>Kredit hisob</th>
            <th style="width:80px">Holat</th>
            <th style="width:90px" class="text-center">Amal</th>
          </tr>
        </thead>
        <tbody>
          @foreach($turlar as $t)
          <tr style="{{ $t->holat === 'nofaol' ? 'opacity:0.5' : '' }}">
            <td><strong class="text-primary font-monospace">{{ $t->kod }}</strong></td>
            <td>{{ $t->nomi }}
              @if($t->izoh)<br><small class="text-muted">{{ $t->izoh }}</small>@endif
            </td>
            <td>
              @php
                $katBadge = ['kassa'=>'bg-success','terminal'=>'bg-primary','bank'=>'bg-info text-dark','boshqa'=>'bg-secondary'];
              @endphp
              <span class="badge {{ $katBadge[$t->kategoriya] ?? 'bg-secondary' }}">{{ $t->kategoriya }}</span>
            </td>
            <td class="small">
              @if($t->debetHisob)
                <span class="text-success fw-bold">Dt</span> {{ $t->debetHisob->hisob_raqam }} — {{ $t->debetHisob->nomi }}
              @else <span class="text-muted">—</span> @endif
            </td>
            <td class="small">
              @if($t->kreditHisob)
                <span class="text-danger fw-bold">Kt</span> {{ $t->kreditHisob->hisob_raqam }} — {{ $t->kreditHisob->nomi }}
              @else <span class="text-muted">—</span> @endif
            </td>
            <td>
              <span class="badge {{ $t->holat === 'faol' ? 'bg-success' : 'bg-secondary' }}">{{ $t->holat }}</span>
            </td>
            <td class="text-center" style="white-space:nowrap">
              <button class="btn btn-sm btn-outline-primary py-0 px-1"
                      onclick="turTahrirlashOch({{ $t->id }}, '{{ addslashes($t->nomi) }}', '{{ $t->kategoriya }}', '{{ $t->holat }}', {{ $t->debet_hisob_id ?? 'null' }}, {{ $t->kredit_hisob_id ?? 'null' }}, '{{ addslashes($t->izoh ?? '') }}')"
                      title="Tahrirlash">
                <i class="bi bi-pencil"></i>
              </button>
              <form method="POST" action="{{ route('buxgalteriya.tulov_turlari.destroy', $t) }}" class="d-inline"
                    onsubmit="return confirm('{{ addslashes($t->nomi) }} turini o\'chirish?')">
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
      Jami: <strong>{{ $turlar->count() }}</strong> ta to'lov turi
    </div>
  </div>
</div>

{{-- Tahrirlash modali --}}
<div class="modal fade" id="turTahrirlashModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2 bg-primary text-white">
        <h6 class="modal-title fw-bold">To'lov turini tahrirlash</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="turTahrirlashForm" method="POST">
        @csrf @method('PUT')
        <div class="modal-body">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
              <input type="text" name="nomi" id="tt-nomi" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Kategoriya</label>
              <select name="kategoriya" id="tt-kategoriya" class="form-select form-select-sm">
                <option value="kassa">Kassa</option>
                <option value="terminal">Terminal</option>
                <option value="bank">Bank</option>
                <option value="boshqa">Boshqa</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Holat</label>
              <select name="holat" id="tt-holat" class="form-select form-select-sm">
                <option value="faol">Faol</option>
                <option value="nofaol">Nofaol</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Debet hisob</label>
              <select name="debet_hisob_id" id="tt-debet" class="form-select form-select-sm">
                <option value="">— tanlanmagan —</option>
                @foreach($hisoblar as $h)
                  <option value="{{ $h->id }}">{{ $h->hisob_raqam }} — {{ Str::limit($h->nomi, 35) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Kredit hisob</label>
              <select name="kredit_hisob_id" id="tt-kredit" class="form-select form-select-sm">
                <option value="">— tanlanmagan —</option>
                @foreach($hisoblar as $h)
                  <option value="{{ $h->id }}">{{ $h->hisob_raqam }} — {{ Str::limit($h->nomi, 35) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-12">
              <label class="form-label small fw-bold">Izoh</label>
              <input type="text" name="izoh" id="tt-izoh" class="form-control form-control-sm">
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
var turModal = null;
function turTahrirlashOch(id, nomi, kat, holat, debetId, kreditId, izoh) {
    document.getElementById('turTahrirlashForm').action = '/buxgalteriya/tulov-turlari/' + id;
    document.getElementById('tt-nomi').value = nomi;
    document.getElementById('tt-izoh').value = izoh;
    document.querySelector('#tt-kategoriya [value="'+kat+'"]').selected = true;
    document.querySelector('#tt-holat [value="'+holat+'"]').selected = true;
    document.getElementById('tt-debet').value = debetId || '';
    document.getElementById('tt-kredit').value = kreditId || '';
    if (!turModal) turModal = new bootstrap.Modal(document.getElementById('turTahrirlashModal'));
    turModal.show();
}
</script>
@endpush
@endsection
