@extends('layouts.app')

@section('title', $mijoz->familiya . ' ' . $mijoz->ism)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.index') }}">Mijozlar</a></li>
    <li class="breadcrumb-item active">{{ $mijoz->familiya }} {{ $mijoz->ism }}</li>
@endsection

@section('content')
<div class="row g-3">
    {{-- ── Mijoz ma'lumotlari ────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-person-circle me-1"></i> Mijoz ma'lumotlari
                </h6>
                @if(Auth::user()->isMenejerYoki())
                <a href="{{ route('mijozlar.edit', $mijoz) }}" class="btn btn-sm btn-outline-secondary py-0">
                    <i class="bi bi-pencil me-1"></i> Tahrirlash
                </a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:40%">F.I.O.</td>
                        <td class="fw-medium">{{ $mijoz->tolik_ism }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telefon (asosiy)</td>
                        <td>
                            <a href="tel:{{ $mijoz->telefon }}">{{ $mijoz->telefon }}</a>
                            <span class="badge bg-success ms-1" style="font-size:.68rem">SMS</span>
                        </td>
                    </tr>
                    @foreach($mijoz->telefonlar as $t)
                    <tr>
                        <td class="text-muted">Qo'shimcha telefon</td>
                        <td>
                            <a href="tel:{{ $t->telefon }}">{{ $t->telefon }}</a>
                            @if($t->sms_yuborilsin)
                            <span class="badge bg-success ms-1" style="font-size:.68rem">SMS</span>
                            @else
                            <span class="badge bg-light text-muted border ms-1" style="font-size:.68rem">SMS yo'q</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="text-muted">Passport</td>
                        <td>{{ $mijoz->passport_tolik }}</td>
                    </tr>
                    @if($mijoz->passport_berilgan_joy)
                    <tr>
                        <td class="text-muted">Passport berilgan joy</td>
                        <td>{{ $mijoz->passport_berilgan_joy }}</td>
                    </tr>
                    @endif
                    @if($mijoz->pinfl)
                    <tr>
                        <td class="text-muted">PINFL</td>
                        <td><code>{{ $mijoz->pinfl }}</code></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Tug'ilgan</td>
                        <td>{{ $mijoz->tug_sana?->format('d.m.Y') ?? '—' }}</td>
                    </tr>
                    @if($mijoz->viloyat || $mijoz->tuman)
                    <tr>
                        <td class="text-muted">Viloyat / Tuman</td>
                        <td>{{ $mijoz->viloyat?->nomi ?? '—' }} / {{ $mijoz->tuman?->nomi ?? '—' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Manzil</td>
                        <td>{{ $mijoz->manzil ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ish joyi</td>
                        <td>{{ $mijoz->ish_joyi ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lavozimi</td>
                        <td>{{ $mijoz->lavozimi ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Filial</td>
                        <td><span class="badge bg-secondary">{{ $mijoz->filial->nomi }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Holat</td>
                        <td>
                            <span class="badge bg-{{ $mijoz->holat_rangi }}">
                                {{ $mijoz->holat_nomi }}
                            </span>
                        </td>
                    </tr>
                    @if($mijoz->izoh)
                    <tr>
                        <td class="text-muted">Izoh</td>
                        <td>{{ $mijoz->izoh }}</td>
                    </tr>
                    @endif
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Shartnomalar ro'yxati ───────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold mb-0">
                <i class="bi bi-file-earmark-text me-1"></i>
                Shartnomalar ({{ $mijoz->kreditlar->count() }} ta)
            </h6>
            @if(Auth::user()->isMenejerYoki())
                @if($mijoz->shartnomaTaqiqlanganmi())
                <button type="button" class="btn btn-sm btn-secondary" disabled
                        title="Mijoz holati «{{ $mijoz->holat_nomi }}» — yangi shartnoma tuzish taqiqlangan">
                    <i class="bi bi-slash-circle me-1"></i> Yangi shartnoma (taqiqlangan)
                </button>
                @else
                <a href="{{ route('kreditlar.create', ['mijoz_id' => $mijoz->id]) }}"
                   class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Yangi shartnoma
                </a>
                @endif
            @endif
        </div>
        @if($mijoz->shartnomaTaqiqlanganmi())
        <div class="alert alert-danger py-2 small mb-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Mijoz holati <strong>«{{ $mijoz->holat_nomi }}»</strong> — bu mijoz uchun yangi shartnoma tuzish taqiqlangan.
        </div>
        @endif

        @forelse($mijoz->kreditlar as $kredit)
        <div class="card border-0 shadow-sm mb-2">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <a href="{{ route('kreditlar.show', $kredit) }}"
                           class="text-decoration-none fw-bold">
                            {{ $kredit->shartnoma_raqam }}
                        </a>
                        <div class="text-muted small mt-1">
                            {{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }} —
                            {{ $kredit->tugash_sana?->format('d.m.Y') ?? '—' }}
                            · {{ $kredit->muddati_oy }} oy
                            · {{ $kredit->xodim->ism_familiya }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-{{ $kredit->holat_rangi }} badge-holat">
                            {{ $kredit->holatNomi }}
                        </span>
                        <a href="{{ route('kreditlar.show', $kredit) }}"
                           class="btn btn-sm btn-outline-primary py-0 px-2"
                           title="Shartnomani ko'rish">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>

                {{-- Moliyaviy progress --}}
                <div class="row g-2 mt-1">
                    <div class="col">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Jami kredit</span>
                            <span>{{ number_format($kredit->kredit_summa, 0, '.', ' ') }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $kredit->tolov_foizi }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between small mt-1">
                            <span class="text-success">To'langan: {{ number_format($kredit->tolov_qilingan, 0, '.', ' ') }}</span>
                            <span class="text-danger">Qoldiq: {{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-file-earmark fs-3 d-block mb-2"></i>
                Bu mijozning shartnomasi yo'q
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
