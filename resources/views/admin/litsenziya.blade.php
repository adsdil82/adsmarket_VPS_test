@extends('layouts.app')
@section('title','Litsenziya')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
<li class="breadcrumb-item active">Litsenziya</li>
@endsection

@php
$rangXarita = [
    'faol' => 'success',
    'ogohlantirish' => 'warning',
    'yengillik' => 'warning',
    'yopiq' => 'danger',
];
$matnXarita = [
    'faol' => 'Faol',
    'ogohlantirish' => 'Faol (muddat tugashiga oz qoldi)',
    'yengillik' => "Muddat tugagan — yengillik davri",
    'yopiq' => 'Bloklangan',
];
$rang = $rangXarita[$holati] ?? 'secondary';
$matn = $matnXarita[$holati] ?? $holati;
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2"></i>Litsenziya holati</h5>
            <span class="badge bg-{{ $rang }} fs-6 px-3 py-2">{{ $matn }}</span>
        </div>

        @if(session('muvaffaqiyat'))
            <div class="alert alert-success">{{ session('muvaffaqiyat') }}</div>
        @endif
        @error('kod')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        @if(!$yoqilganmi)
            <div class="alert alert-secondary">
                Litsenziya nazorati hozircha o'chirilgan (sozlanmagan). Mijoz/tovar/shartnoma qo'shishda
                cheklov yo'q.
            </div>
        @else
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="text-muted small">Amal qilish muddati</div>
                    <div class="fw-bold fs-5">{{ $muddati ? $muddati->format('d.m.Y') : '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">Qolgan kun</div>
                    <div class="fw-bold fs-5">{{ $qolganKun !== null ? $qolganKun : '—' }}</div>
                </div>
            </div>

            @if($holati === 'yopiq')
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Litsenziya muddati tugagan. <b>Yangi mijoz, tovar va shartnoma qo'shish</b> vaqtincha
                    to'xtatilgan. Faollashtirish kodi olish uchun quyidagi do'kon kodini administratorga
                    yuboring.
                </div>
            @elseif($holati === 'yengillik')
                <div class="alert alert-warning">
                    Litsenziya muddati tugagan, lekin yengillik davri davom etmoqda — tez orada
                    faollashtiring, aks holda yangi mijoz/tovar/shartnoma qo'shish to'xtaydi.
                </div>
            @elseif($holati === 'ogohlantirish')
                <div class="alert alert-warning">
                    Litsenziya muddati tugashiga oz qoldi — vaqtida faollashtirishni unutmang.
                </div>
            @endif
        @endif

        <hr>

        <div class="mb-4">
            <div class="text-muted small mb-1">Do'kon kodi (so'rov kodi) — buni administratorga yuboring</div>
            <div class="d-flex align-items-center gap-2">
                <code id="dukon-kodi" class="fs-5 px-3 py-2 bg-light rounded border">{{ $dukonKodi }}</code>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="dukonKodiNusxa()">
                    <i class="bi bi-clipboard me-1"></i>Nusxa olish
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.litsenziya.faollashtir') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-6">
                <label class="form-label small text-muted">Faollashtirish kodi</label>
                <input type="text" name="kod" class="form-control" placeholder="20271231-A1B2C3D4E5" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-key me-1"></i>Faollashtirish
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function dukonKodiNusxa() {
    const el = document.getElementById('dukon-kodi');
    navigator.clipboard.writeText(el.textContent.trim());
}
</script>
@endpush
