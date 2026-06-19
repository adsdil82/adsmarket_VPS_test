@extends('layouts.app')

@section('title', 'Mening profilim')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profil</li>
@endsection

@section('content')

<div class="row g-3 justify-content-center">
    <div class="col-lg-7">

        {{-- ── Foydalanuvchi ma'lumotlari ──────────────────────────── --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-person-circle me-2"></i>Mening profilim
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:180px">Ism Familiya</td>
                        <td class="fw-medium">{{ $user->ism_familiya }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Rol</td>
                        <td>
                            @php
                                $rolRangi = match($user->rol) {
                                    'admin'    => 'danger',
                                    'menejer'  => 'primary',
                                    'kassir'   => 'success',
                                    'hisobchi' => 'secondary',
                                    default    => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $rolRangi }}">{{ ucfirst($user->rol) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Filial</td>
                        <td>{{ $user->filial->nomi ?? 'Barcha filiallar (admin)' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Holat</td>
                        <td>
                            <span class="badge bg-{{ $user->holat === 'faol' ? 'success' : 'secondary' }}">
                                {{ $user->holat === 'faol' ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ro'yxatdan o'tgan</td>
                        <td class="text-muted small">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- ── Parol o'zgartirish ──────────────────────────────────── --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-shield-lock me-2"></i>Parolni o'zgartirish
                </h6>
            </div>
            <div class="card-body">

                @if(session('muvaffaqiyat'))
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('muvaffaqiyat') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('profil.parol') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Joriy parol</label>
                        <input type="password" name="joriy_parol"
                               class="form-control @error('joriy_parol') is-invalid @enderror"
                               autocomplete="current-password">
                        @error('joriy_parol')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yangi parol</label>
                        <input type="password" name="yangi_parol"
                               class="form-control @error('yangi_parol') is-invalid @enderror"
                               autocomplete="new-password">
                        @error('yangi_parol')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Kamida 8 ta belgi</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Yangi parolni tasdiqlang</label>
                        <input type="password" name="yangi_parol_confirmation"
                               class="form-control"
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Parolni saqlash
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
