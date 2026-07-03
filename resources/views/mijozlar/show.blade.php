@extends('layouts.app')

@section('title', $mijoz->familiya . ' ' . $mijoz->ism)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('mijozlar.index') }}">Mijozlar</a></li>
    <li class="breadcrumb-item active">{{ $mijoz->familiya }} {{ $mijoz->ism }}</li>
@endsection

@push('styles')
<style>
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-wrap { border:1px solid #93c5fd; border-radius:6px; overflow:hidden; }
.bft-table { width:100%; margin-bottom:0 !important; font-size:.83rem; }
.bft-table td, .bft-table th { padding:7px 10px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:100%; }
.bft-doc-table { width:100%; }
.bft-doc-table thead th {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; font-weight:600;
    padding:7px 10px; border-bottom:none; white-space:nowrap;
}
.bft-doc-table tbody tr:hover { background:#eef3ff !important; }
.bft-mini-progress { width:80px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; display:inline-block; vertical-align:middle; }
.bft-mini-progress-bar { height:100%; }
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:6px 6px 0 0;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center;
}
.mijoz-rasm-katta:hover { transform:scale(1.08); box-shadow:0 4px 12px rgba(30,58,138,.35); }
</style>
@endpush

@section('content')
<div class="row g-3">
    {{-- ── Mijoz ma'lumotlari (chap) ────────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="bft-header-card">
            <span class="fw-bold"><i class="bi bi-person-circle me-1"></i> Mijoz ma'lumotlari</span>
            @if(Auth::user()->isMenejerYoki())
            <a href="{{ route('mijozlar.edit', $mijoz) }}" class="btn btn-sm btn-light py-0">
                <i class="bi bi-pencil me-1"></i> Tahrirlash
            </a>
            @endif
        </div>
        <div class="bft-wrap" style="border-top:none;border-radius:0 0 6px 6px">
            <table class="bft-table">
                <tbody>
                    <tr>
                        <td class="bft-label">Rasm</td>
                        <td class="bft-wide">
                            @if($mijoz->rasm)
                            <img src="{{ $mijoz->rasm_url }}" alt="{{ $mijoz->tolik_ism }}"
                                 class="mijoz-rasm-katta"
                                 style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #93c5fd;cursor:zoom-in;transition:transform .15s"
                                 onclick="mijozRasmModalOch('{{ $mijoz->rasm_url }}', '{{ $mijoz->tolik_ism }}')"
                                 title="Kattalashtirish uchun bosing">
                            @else
                            <div style="width:80px;height:80px;border-radius:8px;border:2px dashed #93c5fd;display:flex;align-items:center;justify-content:center;background:#f1f5fd">
                                <i class="bi bi-person-circle text-muted" style="font-size:2rem"></i>
                            </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="bft-label">F.I.O.</td>
                        <td class="bft-wide fw-medium">{{ $mijoz->tolik_ism }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Telefon (asosiy)</td>
                        <td class="bft-wide">
                            <a href="tel:{{ $mijoz->telefon }}">{{ $mijoz->telefon }}</a>
                            <span class="badge bg-success ms-1" style="font-size:.68rem">SMS</span>
                        </td>
                    </tr>
                    @foreach($mijoz->telefonlar as $t)
                    <tr>
                        <td class="bft-label">Qo'shimcha telefon</td>
                        <td class="bft-wide">
                            <a href="tel:{{ $t->telefon }}">{{ $t->telefon }}</a>
                            @if($t->egasi_ismi)
                            <span class="text-muted small">({{ $t->egasi_ismi }})</span>
                            @endif
                            @if($t->sms_yuborilsin)
                            <span class="badge bg-success ms-1" style="font-size:.68rem">SMS</span>
                            @else
                            <span class="badge bg-light text-muted border ms-1" style="font-size:.68rem">SMS yo'q</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td class="bft-label">Passport</td>
                        <td class="bft-wide">{{ $mijoz->passport_tolik }}</td>
                    </tr>
                    @if($mijoz->passport_berilgan_sana || $mijoz->passport_amal_muddati)
                    <tr>
                        <td class="bft-label">Passport berilgan / amal muddati</td>
                        <td class="bft-wide">
                            {{ $mijoz->passport_berilgan_sana?->format('d.m.Y') ?? '—' }}
                            — {{ $mijoz->passport_amal_muddati?->format('d.m.Y') ?? '—' }}
                        </td>
                    </tr>
                    @endif
                    @if($mijoz->passport_berilgan_joy)
                    <tr>
                        <td class="bft-label">Passport berilgan joy</td>
                        <td class="bft-wide">{{ $mijoz->passport_berilgan_joy }}</td>
                    </tr>
                    @endif
                    @if($mijoz->pinfl)
                    <tr>
                        <td class="bft-label">PINFL</td>
                        <td class="bft-wide"><code>{{ $mijoz->pinfl }}</code></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="bft-label">Tug'ilgan</td>
                        <td class="bft-wide">{{ $mijoz->tug_sana?->format('d.m.Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Jinsi</td>
                        <td class="bft-wide">{{ $mijoz->jinsi_nomi }}</td>
                    </tr>
                    @if($mijoz->kartalar->isNotEmpty())
                    <tr>
                        <td class="bft-label">Plastik kartalar</td>
                        <td class="bft-wide">
                            @foreach($mijoz->kartalar as $k)
                                <span class="badge bg-light text-dark border">{{ $k->karta_raqami }}</span>
                            @endforeach
                        </td>
                    </tr>
                    @endif
                    @if($mijoz->viloyat || $mijoz->tuman)
                    <tr>
                        <td class="bft-label">Viloyat / Tuman</td>
                        <td class="bft-wide">{{ $mijoz->viloyat?->nomi ?? '—' }} / {{ $mijoz->tuman?->nomi ?? '—' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="bft-label">Manzil</td>
                        <td class="bft-wide">{{ $mijoz->manzil ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Ish joyi</td>
                        <td class="bft-wide">{{ $mijoz->ish_joyi ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="bft-label">Lavozimi</td>
                        <td class="bft-wide">{{ $mijoz->lavozimi ?? '—' }}</td>
                    </tr>
                    @if($mijoz->oila_azolari_soni)
                    <tr>
                        <td class="bft-label">Oila a'zolari soni</td>
                        <td class="bft-wide">{{ $mijoz->oila_azolari_soni }}</td>
                    </tr>
                    @endif
                    @if($mijoz->daromad_manbai)
                    <tr>
                        <td class="bft-label">Daromad manbai</td>
                        <td class="bft-wide">{{ $mijoz->daromad_manbai }}</td>
                    </tr>
                    @endif
                    @if($mijoz->oylik_daromad || $mijoz->oylik_harajat)
                    <tr>
                        <td class="bft-label">Oylik daromad / harajat</td>
                        <td class="bft-wide">
                            <span class="text-success">{{ $mijoz->oylik_daromad ? number_format($mijoz->oylik_daromad,0,'.',' ') : '—' }}</span>
                            /
                            <span class="text-danger">{{ $mijoz->oylik_harajat ? number_format($mijoz->oylik_harajat,0,'.',' ') : '—' }}</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="bft-label">Filial</td>
                        <td class="bft-wide"><span class="badge bg-secondary">{{ $mijoz->filial->nomi }}</span></td>
                    </tr>
                    <tr>
                        <td class="bft-label">Holat</td>
                        <td class="bft-wide">
                            <span class="badge bg-{{ $mijoz->holat_rangi }}">
                                {{ $mijoz->holat_nomi }}
                            </span>
                        </td>
                    </tr>
                    @if($mijoz->izoh)
                    <tr>
                        <td class="bft-label">Izoh</td>
                        <td class="bft-wide">{{ $mijoz->izoh }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Shartnomalar ro'yxati (o'ng) ───────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="bft-header-card mb-0">
            <span class="fw-bold"><i class="bi bi-file-earmark-text me-1"></i> Shartnomalar ({{ $mijoz->kreditlar->count() }} ta)</span>
            @if(Auth::user()->isMenejerYoki())
                @if($mijoz->shartnomaTaqiqlanganmi())
                <button type="button" class="btn btn-sm btn-secondary" disabled
                        title="Mijoz holati «{{ $mijoz->holat_nomi }}» — yangi shartnoma tuzish taqiqlangan">
                    <i class="bi bi-slash-circle me-1"></i> Yangi shartnoma (taqiqlangan)
                </button>
                @else
                <a href="{{ route('kreditlar.create', ['mijoz_id' => $mijoz->id]) }}"
                   class="btn btn-sm btn-warning fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Yangi shartnoma
                </a>
                @endif
            @endif
        </div>
        @if($mijoz->shartnomaTaqiqlanganmi())
        <div class="alert alert-danger py-2 small mb-0 rounded-0">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            Mijoz holati <strong>«{{ $mijoz->holat_nomi }}»</strong> — bu mijoz uchun yangi shartnoma tuzish taqiqlangan.
        </div>
        @endif

        <div class="bft-wrap" style="border-top:none;border-radius:0 0 6px 6px">
            <table class="bft-table bft-doc-table">
                <thead>
                    <tr>
                        <th class="text-start">Shartnoma</th>
                        <th class="text-start">Muddat</th>
                        <th class="text-start">Xodim</th>
                        <th class="text-end">Jami kredit</th>
                        <th class="text-end">To'langan</th>
                        <th class="text-end">Qoldiq</th>
                        <th>Progress</th>
                        <th>Holat</th>
                        <th style="width:46px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mijoz->kreditlar as $kredit)
                    <tr>
                        <td class="text-start">
                            <a href="{{ route('kreditlar.show', $kredit) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                                {{ $kredit->shartnoma_raqam }}
                            </a>
                        </td>
                        <td class="text-start text-muted small">
                            {{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }} — {{ $kredit->tugash_sana?->format('d.m.Y') ?? '—' }}
                            <div>{{ $kredit->muddati_oy }} oy</div>
                        </td>
                        <td class="text-start text-muted small">{{ $kredit->xodim->ism_familiya }}</td>
                        <td class="text-end">{{ number_format($kredit->kredit_summa, 0, '.', ' ') }}</td>
                        <td class="text-end text-success fw-semibold">{{ number_format($kredit->tolov_qilingan, 0, '.', ' ') }}</td>
                        <td class="text-end text-danger fw-semibold">{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</td>
                        <td class="text-center">
                            <div class="bft-mini-progress" title="{{ $kredit->tolov_foizi }}%">
                                <div class="bft-mini-progress-bar bg-success" style="width:{{ $kredit->tolov_foizi }}%"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $kredit->holat_rangi }} badge-holat">{{ $kredit->holatNomi }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('kreditlar.show', $kredit) }}"
                               class="btn btn-sm btn-outline-primary py-0 px-1"
                               title="Shartnomani ko'rish">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-file-earmark fs-3 d-block mb-2"></i>
                            Bu mijozning shartnomasi yo'q
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Mijoz rasmini kattalashtirib ko'rish --}}
<div class="modal fade" id="mijoz-rasm-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <img id="mijoz-rasm-modal-img" src="" alt="" class="img-fluid rounded shadow" style="max-height:80vh">
            </div>
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function mijozRasmModalOch(url, nomi) {
    const img = document.getElementById('mijoz-rasm-modal-img');
    img.src = url;
    img.alt = nomi;
    new bootstrap.Modal(document.getElementById('mijoz-rasm-modal')).show();
}
</script>
@endpush
@endsection
