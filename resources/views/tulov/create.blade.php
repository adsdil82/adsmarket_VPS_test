@extends('layouts.app')

@section('title', "To'lov qabul qilish")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.index') }}">Shartnomalar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('kreditlar.show', $kredit) }}">{{ $kredit->shartnoma_raqam }}</a></li>
    <li class="breadcrumb-item active">To'lov</li>
@endsection

@section('content')

<div class="row justify-content-center">
<div class="col-lg-7">

{{-- Shartnoma ma'lumotlari --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="fw-bold">{{ $kredit->shartnoma_raqam }}</span>
                <span class="ms-2 text-muted">{{ $kredit->mijoz->tolik_ism }}</span>
            </div>
            <span class="badge bg-{{ $kredit->holat_rangi }}">{{ $kredit->holat }}</span>
        </div>
        <div class="row g-2 mt-1 text-center">
            <div class="col">
                <div class="text-muted small">Kredit summa</div>
                <div class="fw-bold">{{ number_format($kredit->kredit_summa, 0, '.', ' ') }}</div>
            </div>
            <div class="col">
                <div class="text-muted small">To'langan</div>
                <div class="fw-bold text-success">{{ number_format($kredit->tolov_qilingan, 0, '.', ' ') }}</div>
            </div>
            <div class="col">
                <div class="text-muted small">Qoldiq qarz</div>
                <div class="fw-bold text-danger" id="qoldiq-display">{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</div>
            </div>
            <div class="col">
                <div class="text-muted small">Oylik</div>
                <div class="fw-bold">{{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }}</div>
            </div>
        </div>
        {{-- Progress --}}
        <div class="progress mt-2" style="height: 6px;">
            <div class="progress-bar bg-success" style="width: {{ $kredit->tolov_foizi }}%"></div>
        </div>
    </div>
</div>

{{-- Keyingi to'lov grafiki --}}
@if($kredit->grafik->isNotEmpty())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2">
        <h6 class="mb-0 small fw-medium">
            <i class="bi bi-calendar3 me-1"></i> To'lanmagan oylar
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th class="small">Oy</th>
                    <th class="small">Sana</th>
                    <th class="small text-end">Summa</th>
                    <th class="small">Holat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kredit->grafik->take(5) as $g)
                <tr class="{{ $g->holat === 'muddati_otgan' ? 'table-danger' : '' }}">
                    <td class="small">{{ $g->oylik_tartib }}-oy</td>
                    <td class="small">{{ $g->tolov_sana?->format('d.m.Y') }}</td>
                    <td class="small text-end">{{ number_format($g->tolov_summa ?? 0, 0, '.', ' ') }}</td>
                    <td><span class="badge bg-{{ $g->holat_rangi }}" style="font-size:10px">{{ $g->holat }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@endif

{{-- To'lov formasi --}}
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-cash-coin me-1"></i> To'lov qabul qilish</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('kreditlar.tulov.store', $kredit) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-medium">To'lov turi <span class="text-danger">*</span></label>
                <select name="tulov_turi_id" class="form-select @error('tulov_turi_id') is-invalid @enderror" required>
                    <option value="">— Tanlang —</option>
                    @foreach($tulovTurlari as $tur)
                        <option value="{{ $tur->id }}" {{ old('tulov_turi_id') == $tur->id ? 'selected' : '' }}>
                            {{ $tur->nomi }}
                        </option>
                    @endforeach
                </select>
                @error('tulov_turi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Summa <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="summa" id="summa"
                           class="form-control form-control-lg @error('summa') is-invalid @enderror"
                           value="{{ old('summa', $kredit->oylik_tolov_miqdori) }}"
                           min="0.01" max="{{ $kredit->qoldiq_qarz }}"
                           step="1000" required autofocus>
                    <span class="input-group-text">so'm</span>
                </div>
                <div class="form-text">
                    Maksimal: <strong>{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }}</strong> so'm
                    <button type="button" class="btn btn-link btn-sm py-0 px-1"
                            onclick="$('#summa').val({{ $kredit->qoldiq_qarz }})">
                        To'liq yopish
                    </button>
                </div>
                @error('summa')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">To'lov sanasi <span class="text-danger">*</span></label>
                <input type="date" name="tolov_sana"
                       class="form-control @error('tolov_sana') is-invalid @enderror"
                       value="{{ old('tolov_sana', date('Y-m-d')) }}" required>
                @error('tolov_sana')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Kvitansiya raqami (ixtiyoriy)</label>
                <input type="text" name="kvitansiya_raqam" class="form-control"
                       value="{{ old('kvitansiya_raqam') }}" placeholder="KV-001">
            </div>

            <div class="mb-4">
                <label class="form-label">Izoh</label>
                <textarea name="izoh" class="form-control" rows="2">{{ old('izoh') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success btn-lg flex-grow-1">
                    <i class="bi bi-check-lg me-1"></i> To'lovni qabul qilish
                </button>
                <a href="{{ route('kreditlar.show', $kredit) }}" class="btn btn-outline-secondary">
                    Bekor qilish
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
