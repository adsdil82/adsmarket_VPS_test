@extends('layouts.app')

@section('title', 'Mening profilim')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profil</li>
@endsection

@push('styles')
<style>
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:6px 6px 0 0;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #93c5fd; border-radius:0 0 6px 6px; overflow:hidden; }
.bft-table { width:100%; margin-bottom:0 !important; font-size:.85rem; }
.bft-table td { padding:8px 12px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:100%; }
</style>
@endpush

@section('content')

<div class="row g-3 justify-content-center">
    <div class="col-lg-7">

        {{-- ── Foydalanuvchi ma'lumotlari ──────────────────────────── --}}
        <div class="bft-header-card">
            <span class="fw-bold"><i class="bi bi-person-circle me-1"></i>Mening profilim</span>
        </div>
        <div class="bft-wrap mb-3">
            <table class="bft-table">
                <tbody>
                    <tr>
                        <td class="bft-label">Ism Familiya</td>
                        <td class="bft-wide fw-medium">{{ $user->ism_familiya }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Email</td>
                        <td class="bft-wide">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Rol</td>
                        <td class="bft-wide">
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
                        <td class="bft-label">Filial</td>
                        <td class="bft-wide">{{ $user->filial->nomi ?? 'Barcha filiallar (admin)' }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Holat</td>
                        <td class="bft-wide">
                            <span class="badge bg-{{ $user->holat === 'faol' ? 'success' : 'secondary' }}">
                                {{ $user->holat === 'faol' ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">Ro'yxatdan o'tgan</td>
                        <td class="bft-wide text-muted small">{{ $user->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- ── Parol o'zgartirish ──────────────────────────────────── --}}
        <div class="bft-header-card">
            <span class="fw-bold"><i class="bi bi-shield-lock me-1"></i>Parolni o'zgartirish</span>
        </div>
        <div class="bft-wrap">
            <div class="p-3">

                @if(session('muvaffaqiyat'))
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('muvaffaqiyat') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('profil.parol') }}" method="POST">
                    @csrf

                    <table class="bft-table">
                        <tbody>
                            <tr>
                                <td class="bft-label">Joriy parol</td>
                                <td class="bft-wide">
                                    <input type="password" name="joriy_parol"
                                           class="form-control form-control-sm @error('joriy_parol') is-invalid @enderror"
                                           style="max-width:280px" autocomplete="current-password">
                                    @error('joriy_parol')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="bft-label">Yangi parol</td>
                                <td class="bft-wide">
                                    <input type="password" name="yangi_parol"
                                           class="form-control form-control-sm @error('yangi_parol') is-invalid @enderror"
                                           style="max-width:280px" autocomplete="new-password">
                                    @error('yangi_parol')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text small mb-0">Kamida 8 ta belgi</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="bft-label">Yangi parolni tasdiqlang</td>
                                <td class="bft-wide">
                                    <input type="password" name="yangi_parol_confirmation"
                                           class="form-control form-control-sm"
                                           style="max-width:280px" autocomplete="new-password">
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Parolni saqlash
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
