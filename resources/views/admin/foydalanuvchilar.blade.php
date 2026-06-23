@extends('layouts.app')
@section('title', 'Foydalanuvchilar')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
    <li class="breadcrumb-item active">Foydalanuvchilar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-people me-2 text-primary"></i>Foydalanuvchilar</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#yangiUserModal">
        <i class="bi bi-person-plus me-1"></i>Yangi foydalanuvchi
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Ism Familiya</th><th>Email / Login</th>
                    <th>Rol</th><th>Filial</th><th>Holat</th><th>Amallar</th>
                </tr>
            </thead>
            <tbody>
                @php $rollarXarita = $rollar->keyBy('kalit'); @endphp
                @foreach($foydalanuvchilar as $u)
                @php
                    $rolRang = match($u->rol) {
                        'admin'    => 'danger',
                        'menejer'  => 'primary',
                        'kassir'   => 'success',
                        'omborchi' => 'warning',
                        'hisobchi' => 'info',
                        'auditor'  => 'dark',
                        default    => 'secondary'
                    };
                    $rolNomi = $rollarXarita[$u->rol]->nomi ?? $u->rol;
                @endphp
                <tr>
                    <td class="text-muted small">{{ $u->id }}</td>
                    <td class="fw-medium">{{ $u->ism_familiya }}</td>
                    <td class="text-muted small">{{ $u->email }}</td>
                    <td><span class="badge bg-{{ $rolRang }}">{{ $rolNomi }}</span></td>
                    <td class="text-muted small">
                        @if($u->filial)
                            {{ $u->filial->nomi }}
                        @else
                            <span class="badge bg-secondary">Barcha</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $u->holat === 'faol' ? 'success' : 'secondary' }}">
                            {{ $u->holat === 'faol' ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            {{-- Tahrirlash --}}
                            <button class="btn btn-xs btn-outline-primary py-0 px-1"
                                    style="font-size:.72rem"
                                    title="Tahrirlash"
                                    onclick="tahrirModalOch({{ $u->id }}, '{{ addslashes($u->ism_familiya) }}', '{{ addslashes($u->email) }}', '{{ $u->rol }}', '{{ $u->filial_id }}', '{{ $u->holat }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            {{-- Holat o'zgartirish --}}
                            @if($u->id !== 1)
                            <form method="POST" action="{{ route('admin.foydalanuvchilar.holat', $u) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-outline-{{ $u->holat==='faol' ? 'danger' : 'success' }} py-0 px-1"
                                        style="font-size:.72rem"
                                        title="{{ $u->holat==='faol' ? 'Bloklash' : 'Faollashtirish' }}"
                                        onclick="return confirm('{{ $u->holat==='faol' ? 'Bloklash' : 'Faollashtirish' }}?')">
                                    <i class="bi bi-{{ $u->holat==='faol' ? 'lock' : 'unlock' }}"></i>
                                </button>
                            </form>
                            @endif
                            {{-- Parol reset --}}
                            <button class="btn btn-xs btn-outline-warning py-0 px-1"
                                    style="font-size:.72rem"
                                    title="Parol o'zgartirish"
                                    onclick="parolModalOch({{ $u->id }}, '{{ addslashes($u->ism_familiya) }}')">
                                <i class="bi bi-key"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- === Yangi foydalanuvchi Modal === --}}
<div class="modal fade" id="yangiUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>Yangi foydalanuvchi</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.foydalanuvchilar.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Ism Familiya <span class="text-danger">*</span></label>
            <input type="text" name="ism_familiya" class="form-control" required minlength="3"
                   placeholder="Familiya Ism Otasining ismi" value="{{ old('ism_familiya') }}">
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Email / Login <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required
                   placeholder="user@nasiyapro.uz" value="{{ old('email') }}">
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-medium">Rol <span class="text-danger">*</span></label>
              <select name="rol" class="form-select" required>
                <option value="">— Tanlang —</option>
                @foreach($rollar as $r)
                <option value="{{ $r->kalit }}">{{ $r->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-medium">Filial</label>
              <select name="filial_id" class="form-select">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Parol <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password" id="np-pwd" class="form-control"
                     required minlength="8" placeholder="Kamida 8 belgi">
              <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('np-pwd','np-eye')">
                <i class="bi bi-eye" id="np-eye"></i>
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Parolni tasdiqlash <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control"
                   required minlength="8" placeholder="Qaytadan kiriting">
          </div>
          <div class="mb-1">
            <label class="form-label fw-medium">Holat</label>
            <select name="holat" class="form-select">
              <option value="faol">Faol</option>
              <option value="nofaol">Nofaol</option>
            </select>
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

{{-- === Tahrirlash Modal === --}}
<div class="modal fade" id="tahrirModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h6 class="modal-title fw-bold mb-0"><i class="bi bi-pencil me-2"></i>Foydalanuvchini tahrirlash</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="tahrir-form">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Ism Familiya <span class="text-danger">*</span></label>
            <input type="text" name="ism_familiya" id="th-ism" class="form-control" required minlength="3">
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Email / Login <span class="text-danger">*</span></label>
            <input type="email" name="email" id="th-email" class="form-control" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label fw-medium">Rol <span class="text-danger">*</span></label>
              <select name="rol" id="th-rol" class="form-select" required>
                @foreach($rollar as $r)
                <option value="{{ $r->kalit }}">{{ $r->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-medium">Filial</label>
              <select name="filial_id" id="th-filial" class="form-select">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mb-1">
            <label class="form-label fw-medium">Holat</label>
            <select name="holat" id="th-holat" class="form-select">
              <option value="faol">Faol</option>
              <option value="nofaol">Nofaol</option>
            </select>
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

{{-- === Parol reset Modal === --}}
<div class="modal fade" id="parolModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-warning">
        <h6 class="modal-title fw-bold mb-0"><i class="bi bi-key me-2"></i>Parol o'zgartirish</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="parol-form">
        @csrf
        <div class="modal-body">
          <p class="small text-muted mb-2" id="parol-user-nomi"></p>
          <div class="mb-2">
            <label class="form-label fw-medium small">Yangi parol <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="yangi_parol" id="pr-pwd" class="form-control form-control-sm"
                     required minlength="8" placeholder="Kamida 8 belgi">
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePwd('pr-pwd','pr-eye')">
                <i class="bi bi-eye" id="pr-eye"></i>
              </button>
            </div>
          </div>
          <div>
            <label class="form-label fw-medium small">Tasdiqlash <span class="text-danger">*</span></label>
            <input type="password" name="yangi_parol_confirmation" class="form-control form-control-sm"
                   required minlength="8" placeholder="Qaytadan">
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
          <button type="submit" class="btn btn-sm btn-warning fw-bold">
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
function tahrirModalOch(userId, ism, email, rol, filialId, holat) {
    document.getElementById('th-ism').value = ism;
    document.getElementById('th-email').value = email;
    document.getElementById('th-rol').value = rol;
    document.getElementById('th-filial').value = filialId || '';
    document.getElementById('th-holat').value = holat;
    document.getElementById('tahrir-form').action = '/admin/foydalanuvchilar/' + userId;
    if (!window._tahrirModal) window._tahrirModal = new bootstrap.Modal(document.getElementById('tahrirModal'));
    window._tahrirModal.show();
}
function parolModalOch(userId, ism) {
    document.getElementById('parol-user-nomi').textContent = 'Foydalanuvchi: ' + ism;
    document.getElementById('parol-form').action = '/admin/foydalanuvchilar/' + userId + '/parol';
    document.getElementById('pr-pwd').value = '';
    if (!window._parolModal) window._parolModal = new bootstrap.Modal(document.getElementById('parolModal'));
    window._parolModal.show();
}
function togglePwd(inputId, iconId) {
    var inp = document.getElementById(inputId);
    var ico = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('yangiUserModal')).show();
});
@endif
</script>
@endpush