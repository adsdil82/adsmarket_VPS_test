@extends('layouts.app')
@section('title', "Ta'minotchi migratsiyasi")
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.harajat-turlari.index') }}">Harajat turlari</a></li>
<li class="breadcrumb-item active">Ta'minotchi migratsiyasi</li>
@endsection

@push('styles')
<style>
.guruh-card { transition: box-shadow .15s; }
.guruh-card:hover { box-shadow: 0 .25rem .75rem rgba(0,0,0,.08); }
.misol-matn { font-size: .78rem; color: #6c757d; border-left: 2px solid #e9ecef; padding-left: 8px; margin-bottom: 2px; }
.aniqlanmagan { border-left: 4px solid #dc3545 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-truck me-2" style="color:#7c3aed"></i>Eski harajatlarni ta'minotchiga bog'lash</h5>
        <small class="text-muted">
            Har bir guruh uchun tizim eng yaqin ta'minotchini taklif qildi (so'z mosligiga qarab). <strong>Ko'rsatilgan misollarni o'qib chiqing</strong> — agar noto'g'ri bo'lsa, dropdown'dan to'g'ri ta'minotchini tanlang yoki "— o'tkazib yuborish —" qiling.
        </small>
    </div>
</div>

@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
<div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

<div class="alert alert-info py-2 small mb-4">
    <i class="bi bi-info-circle me-1"></i>
    Tasdiqlangan guruh — har bir yozuv o'z sanasi bilan tanlangan ta'minotchiga <strong>"Umumiy to'lov"</strong> sifatida kiritiladi
    (eng eski ochiq qarzlarni avtomatik ketma-ket yopadi) va Pul Oqimlariga ham yoziladi.
</div>

@forelse($guruhlar as $i => $g)
<div class="card border-0 shadow-sm mb-3 guruh-card {{ $g['taminotchi_id'] ? '' : 'aniqlanmagan' }}">
    <div class="card-body">
        <form method="POST" action="{{ route('malumotnamalar.harajat-turlari.taminotchi-migratsiya.tasdiq') }}" class="row g-3 align-items-start">
            @csrf
            @foreach($g['harajat_idlar'] as $hid)
                <input type="hidden" name="harajat_idlar[]" value="{{ $hid }}">
            @endforeach

            <div class="col-md-3">
                <div class="fw-bold">
                    @if($g['taminotchi_id'])
                        <i class="bi bi-check-circle text-success me-1"></i>{{ $g['nomi'] }}
                    @else
                        <i class="bi bi-question-circle text-danger me-1"></i>Aniqlanmagan
                    @endif
                </div>
                <div class="small text-muted">{{ $g['soni'] }} ta yozuv — {{ number_format($g['jami'],0,'.',' ') }} so'm</div>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Misollar (mazmuni):</label>
                @foreach($g['misollar'] as $m)
                    <div class="misol-matn">{{ $m }}</div>
                @endforeach
                @if($g['soni'] > count($g['misollar']))
                    <div class="misol-matn text-muted">... va yana {{ $g['soni'] - count($g['misollar']) }} ta</div>
                @endif
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Ta'minotchi</label>
                <select name="taminotchi_id" class="form-select form-select-sm mb-2">
                    <option value="">— o'tkazib yuborish —</option>
                    @foreach($taminotchilar as $t)
                        <option value="{{ $t->id }}" {{ $g['taminotchi_id'] == $t->id ? 'selected' : '' }}>{{ $t->nomi }}</option>
                    @endforeach
                </select>
                <select name="tolov_turi" class="form-select form-select-sm">
                    <option value="naqd">Naqd</option>
                    <option value="plastik">Terminal</option>
                    <option value="bank">Bank</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-start justify-content-end">
                <button type="submit" class="btn btn-sm" style="background:#7c3aed;color:#fff"
                        onclick="return confirm('{{ $g['soni'] }} ta yozuv tasdiqlanadi. Davom etilsinmi?')">
                    <i class="bi bi-check2 me-1"></i>Tasdiqlash
                </button>
            </div>
        </form>
    </div>
</div>
@empty
<div class="alert alert-success">Barcha eski ta'minotchi harajatlari allaqachon bog'langan.</div>
@endforelse

@if($manfiylar->count())
<div class="card border-0 shadow-sm border-start border-4 border-secondary mt-4">
    <div class="card-header bg-light py-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-return-left me-2"></i>
            Manfiy/nol summali yozuvlar — qo'lda ko'rib chiqish kerak ({{ $manfiylar->count() }} ta)
        </h6>
        <small class="text-muted">Bular (tuzatish, qaytarish va h.k.) avtomatik vositaga kiritilmagan — alohida tekshiring.</small>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.82rem">
            <thead class="table-light"><tr><th>Sana</th><th class="text-end">Summa</th><th>Mazmuni</th></tr></thead>
            <tbody>
                @foreach($manfiylar as $m)
                <tr>
                    <td>{{ $m->sana->format('d.m.Y') }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format(abs($m->summa),0,'.',' ') }}</td>
                    <td class="text-muted">{{ $m->mazmuni }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="card-body border-top bg-light">
            <form method="POST" action="{{ route('malumotnamalar.harajat-turlari.manfiy-daromad') }}"
                  onsubmit="return confirm('{{ $manfiylar->count() }} ta manfiy yozuv CF-1900 \"Boshqa kirimlar\" sifatida Pul Oqimlariga KIRIM yoziladi. Davom etilsinmi?')">
                @csrf
                @foreach($manfiylar as $m)
                    <input type="hidden" name="harajat_idlar[]" value="{{ $m->id }}">
                @endforeach
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <label class="form-label small fw-bold mb-1">Kassa turi</label>
                        <select name="kassa_turi" class="form-select form-select-sm" style="width:140px">
                            <option value="naqd">Naqd</option>
                            <option value="terminal">Terminal</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-arrow-up-circle me-1"></i>
                            Hammasini CF-1900 "Boshqa kirimlar" (prochi daromad) sifatida yozish
                        </button>
                    </div>
                    <small class="text-muted mt-3">Jami: {{ number_format($manfiylar->sum(fn($m)=>abs($m->summa)),0,'.',' ') }} so'm kirim yoziladi</small>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

</div>
@endsection
