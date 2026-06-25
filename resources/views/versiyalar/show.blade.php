@extends('layouts.app')

@section('title', 'v' . $versiya->versiya_raqam . ' — ' . $kredit->shartnoma_raqam)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.show', $kredit) }}">{{ $kredit->shartnoma_raqam }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.versiyalar.index', $kredit) }}">Versiyalar</a></li>
    <li class="breadcrumb-item active">v{{ $versiya->versiya_raqam }}</li>
@endsection

@section('content')

{{-- ── Sarlavha ───────────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-1">
            <span class="badge bg-primary me-2">v{{ $versiya->versiya_raqam }}</span>
            {{ $kredit->shartnoma_raqam }} — Versiya tafsiloti
        </h5>
        <div class="text-muted small">
            {{ $versiya->created_at->format('d.m.Y H:i') }}
            · {{ $versiya->xodim->ism_familiya ?? '—' }}
            @if($versiya->sabab)
                · <em>{{ $versiya->sabab }}</em>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @if(Auth::user()->isAdmin() && $versiya->eski_holat)
        <form method="POST" action="{{ route('kreditlar.versiyalar.qaytarish', [$kredit, $versiya]) }}"
              onsubmit="return confirm('Shartnoma ushbu versiyadagi (\'Oldingi holat\') qiymatlarga qaytariladi.\n\nDiqqat: Tovarlar va to\'lov grafigi O\'ZGARTIRILMAYDI — faqat summa/sana maydonlari tiklanadi. Davom etilsinmi?');">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Shu versiyaga qaytarish
            </button>
        </form>
        @endif
        <a href="{{ route('kreditlar.versiyalar.index', $kredit) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Versiyalarga qaytish
        </a>
    </div>
</div>

@if(Auth::user()->isAdmin() && $versiya->eski_holat)
<div class="alert alert-secondary small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    "Shu versiyaga qaytarish" — shartnomaning <strong>"Oldingi holat"</strong> ustunidagi
    qiymatlarini (jami summa, kredit summa, sanalar va h.k.) tiklaydi va bu amal yangi versiya
    sifatida qayd etiladi. <strong>Tovarlar va to'lov grafigi (Graf) o'zgartirilmaydi</strong> —
    agar summalar farq qilsa, ularni "Tahrirlash" orqali qo'lda moslashtirish kerak bo'ladi.
</div>
@endif

{{-- ── O'zgargan maydonlar ─────────────────────────────────────────── --}}
@if($versiya->ozgargan_maydonlar && count($versiya->ozgargan_maydonlar) > 0)
<div class="alert alert-warning mb-3">
    <strong>O'zgargan maydonlar:</strong>
    @foreach($versiya->ozgargan_maydonlar as $maydon)
        <span class="badge bg-warning text-dark me-1">{{ $maydon }}</span>
    @endforeach
</div>
@endif

{{-- ── Solishtirish jadvali ────────────────────────────────────────── --}}
<div class="row g-3">

    {{-- Eski holat --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-danger bg-opacity-10">
                <h6 class="mb-0 text-danger">
                    <i class="bi bi-dash-circle me-1"></i>
                    Oldingi holat
                    @if($versiya->versiya_raqam > 1)
                        <span class="text-muted fw-normal">(v{{ $versiya->versiya_raqam - 1 }})</span>
                    @endif
                </h6>
            </div>
            <div class="card-body p-0">
                @if($versiya->eski_holat)
                    @include('versiyalar._snapshot', ['snapshot' => $versiya->eski_holat, 'ozgarganlar' => $versiya->ozgargan_maydonlar ?? []])
                @else
                    <p class="text-muted text-center py-4 mb-0">Ma'lumot yo'q (yangi yaratilgan)</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Yangi holat --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success bg-opacity-10">
                <h6 class="mb-0 text-success">
                    <i class="bi bi-plus-circle me-1"></i>
                    Yangi holat (v{{ $versiya->versiya_raqam }})
                </h6>
            </div>
            <div class="card-body p-0">
                @include('versiyalar._snapshot', ['snapshot' => $versiya->yangi_holat, 'ozgarganlar' => $versiya->ozgargan_maydonlar ?? []])
            </div>
        </div>
    </div>

</div>

{{-- ── Xom JSON (admin uchun) ──────────────────────────────────────── --}}
@if(Auth::user()->isAdmin())
<div class="mt-3">
    <div class="accordion" id="jsonAcc">
        <div class="accordion-item border-0 shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button"
                        data-bs-toggle="collapse" data-bs-target="#jsonBody">
                    <i class="bi bi-code-slash me-2"></i> Xom JSON ma'lumot (admin)
                </button>
            </h2>
            <div id="jsonBody" class="accordion-collapse collapse">
                <div class="accordion-body p-0">
                    <pre class="m-0 p-3 bg-body-secondary" style="font-size:12px;max-height:400px;overflow:auto;">{{ json_encode($versiya->yangi_holat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
