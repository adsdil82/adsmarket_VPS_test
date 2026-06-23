@extends('layouts.app')
@section('title', 'Rollar')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
    <li class="breadcrumb-item active">Rollar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-person-badge me-2 text-primary"></i>Rollar boshqaruvi</h5>
        <small class="text-muted">Yangi rol (masalan: Sotuvchi, Yetkazib beruvchi) qo'shing — keyin
            <a href="{{ route('admin.ruxsatlar') }}">Ruxsatlar</a> sahifasida unga modul huquqlarini belgilang</small>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#yangiRolModal">
        <i class="bi bi-plus-circle me-1"></i>Yangi rol qo'shish
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Rol</th><th>Kalit</th><th>Turi</th>
                    <th>Foydalanuvchilar</th><th>Amallar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rollar as $r)
                <tr>
                    <td class="text-muted small">{{ $r->id }}</td>
                    <td class="fw-medium">
                        <i class="bi bi-{{ $r->icon }} me-1 text-primary"></i>{{ $r->nomi }}
                    </td>
                    <td><code class="small">{{ $r->kalit }}</code></td>
                    <td>
                        @if($r->tizim)
                            <span class="badge bg-secondary">Tizim</span>
                        @else
                            <span class="badge bg-light text-dark border">Maxsus</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $r->foydalanuvchi_soni ? 'info' : 'light text-dark border' }}">
                            {{ $r->foydalanuvchi_soni }} ta
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <button class="btn btn-xs btn-outline-primary py-0 px-1" style="font-size:.72rem"
                                    title="Tahrirlash"
                                    onclick="rolTahrirOch({{ $r->id }}, '{{ addslashes($r->nomi) }}', '{{ $r->icon }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if(!$r->tizim)
                            <form method="POST" action="{{ route('admin.rollar.destroy', $r) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger py-0 px-1" style="font-size:.72rem"
                                        title="O'chirish"
                                        onclick="return confirm('\"{{ addslashes($r->nomi) }}\" rolini o\'chirishni tasdiqlaysizmi?')"
                                        {{ $r->foydalanuvchi_soni ? 'disabled' : '' }}>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="text-muted small mt-2">
    <i class="bi bi-info-circle me-1"></i>
    "Tizim" rollarini o'chirib bo'lmaydi — kod ichida ulardan foydalaniladi.
    "Maxsus" rollarni faqat hech kim ishlatmayotgan bo'lsa o'chirish mumkin.
</div>

{{-- === Yangi rol Modal === --}}
<div class="modal fade" id="yangiRolModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>Yangi rol qo'shish</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.rollar.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Rol nomi <span class="text-danger">*</span></label>
            <input type="text" name="nomi" class="form-control @error('nomi') is-invalid @enderror"
                   required placeholder="Sotuvchi" value="{{ old('nomi') }}">
            @error('nomi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Kalit <span class="text-danger">*</span></label>
            <input type="text" name="kalit" class="form-control @error('kalit') is-invalid @enderror"
                   required placeholder="sotuvchi" pattern="[a-z0-9_]+" value="{{ old('kalit') }}">
            @error('kalit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Faqat kichik lotin harf, raqam, "_" — keyin o'zgartirib bo'lmaydi</div>
          </div>
          <div class="mb-1">
            <label class="form-label fw-medium">Ikonka</label>
            <input type="text" name="icon" class="form-control" placeholder="person"
                   value="{{ old('icon', 'person') }}">
            <div class="form-text">
                <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a> nomi (masalan: cart, truck)
            </div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
          <button type="submit" class="btn btn-sm btn-primary fw-bold">
            <i class="bi bi-check2 me-1"></i>Yaratish
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- === Rol tahrirlash Modal === --}}
<div class="modal fade" id="rolTahrirModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title fw-bold mb-0"><i class="bi bi-pencil me-2"></i>Rolni tahrirlash</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="rol-tahrir-form">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Rol nomi <span class="text-danger">*</span></label>
            <input type="text" name="nomi" id="rt-nomi" class="form-control" required>
          </div>
          <div class="mb-1">
            <label class="form-label fw-medium">Ikonka</label>
            <input type="text" name="icon" id="rt-icon" class="form-control">
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
          <button type="submit" class="btn btn-sm btn-primary fw-bold">
            <i class="bi bi-check2 me-1"></i>Saqlash
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function rolTahrirOch(id, nomi, icon) {
    document.getElementById('rt-nomi').value = nomi;
    document.getElementById('rt-icon').value = icon;
    document.getElementById('rol-tahrir-form').action = '/admin/rollar/' + id;
    if (!window._rolTahrirModal) window._rolTahrirModal = new bootstrap.Modal(document.getElementById('rolTahrirModal'));
    window._rolTahrirModal.show();
}
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('yangiRolModal')).show();
});
@endif
</script>
@endpush
