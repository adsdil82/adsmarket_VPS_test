@extends('layouts.app')
@section('title', 'Ish kuni')
@section('breadcrumb')
<li class="breadcrumb-item active">Ish kuni</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Ish kuni</h5>
</div>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'holat' ? 'active' : '' }}" href="{{ route('operatsion_kun.index') }}">
            <i class="bi bi-calendar-check me-1"></i> Kun holati
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'tarix' ? 'active' : '' }}" href="{{ route('operatsion_kun.index', ['tab' => 'tarix']) }}">
            <i class="bi bi-clock-history me-1"></i> Yopish tarixi
        </a>
    </li>
</ul>

@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show">{{ session('muvaffaqiyat') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('xato'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('xato') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($tab === 'holat')
<div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>Filial</th>
                <th>Sana</th>
                <th>Holat</th>
                <th>Yopilgan vaqt</th>
                <th>Yopgan</th>
                <th class="text-end">Amal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kunlar as $kun)
            <tr>
                <td>{{ $kun->filial->nomi ?? '—' }}</td>
                <td>{{ $kun->sana->format('d.m.Y') }}</td>
                <td>
                    @if($kun->status === 'yopiq')
                        <span class="badge bg-danger">Yopiq</span>
                    @else
                        <span class="badge bg-success">Ochiq</span>
                    @endif
                </td>
                <td>{{ $kun->yopilgan_vaqt?->format('d.m.Y H:i') ?? '—' }}</td>
                <td>{{ $kun->yopganUser->ism_familiya ?? '—' }}</td>
                <td class="text-end">
                    @if($kun->status === 'ochiq')
                        <button class="btn btn-sm btn-outline-danger btn-yopish"
                                data-filial="{{ $kun->filial_id }}" data-sana="{{ $kun->sana->toDateString() }}">
                            <i class="bi bi-lock"></i> Yopish
                        </button>
                    @else
                        <button class="btn btn-sm btn-outline-warning btn-ochish"
                                data-filial="{{ $kun->filial_id }}" data-sana="{{ $kun->sana->toDateString() }}">
                            <i class="bi bi-unlock"></i> Qayta ochish
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Filial topilmadi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Yopish tasdiqlash modali --}}
<div class="modal fade" id="yopishModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="yopishForm" method="POST" action="{{ route('operatsion_kun.yopish') }}">
            @csrf
            <input type="hidden" name="filial_id" id="yopish_filial_id">
            <input type="hidden" name="sana" id="yopish_sana">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Kunni yopish</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Kunni yopishga ishonchingiz komilmi?</p>
                    <div id="yopishOnkorish" class="text-muted small">Yuklanmoqda...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" class="btn btn-danger">Ha, yopish</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Qayta ochish modali --}}
<div class="modal fade" id="ochishModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('operatsion_kun.ochish') }}">
            @csrf
            <input type="hidden" name="filial_id" id="ochish_filial_id">
            <input type="hidden" name="sana" id="ochish_sana">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Kunni qayta ochish</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-2">
                        Diqqat: kun qayta ochilgach, shu kunga tegishli hujjatlarni tahrirlash
                        faqat admin/ruxsatli foydalanuvchi uchun ochiladi.
                    </div>
                    <label class="form-label">Sabab / izoh <span class="text-danger">*</span></label>
                    <textarea name="izoh" class="form-control" rows="3" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" class="btn btn-warning">Ha, qayta ochish</button>
                </div>
            </div>
        </form>
    </div>
</div>
@else
<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="tab" value="tarix">
    @if($tarixFiliallar->isNotEmpty())
    <div class="col-auto">
        <select name="filial_id" class="form-select form-select-sm">
            <option value="">Barcha filiallar</option>
            @foreach($tarixFiliallar as $f)
            <option value="{{ $f->id }}" @selected($filialId == $f->id)>{{ $f->nomi }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-auto">
        <input type="date" name="sana_dan" class="form-control form-control-sm" value="{{ $sanaDan }}" placeholder="Sanadan">
    </div>
    <div class="col-auto">
        <input type="date" name="sana_gacha" class="form-control form-control-sm" value="{{ $sanaGacha }}" placeholder="Sanagacha">
    </div>
    <div class="col-auto">
        <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> Qidirish</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
            <tr>
                <th>Vaqt</th>
                <th>Filial</th>
                <th>Sana</th>
                <th>Amal</th>
                <th>Foydalanuvchi</th>
                <th>Natija / izoh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loglar as $log)
            <tr>
                <td>{{ $log->vaqt->format('d.m.Y H:i') }}</td>
                <td>{{ $log->operatsionKun->filial->nomi ?? '—' }}</td>
                <td>{{ $log->operatsionKun->sana->format('d.m.Y') ?? '—' }}</td>
                <td>
                    @if($log->amal === 'yopish')
                        <span class="badge bg-danger">Yopish</span>
                    @else
                        <span class="badge bg-warning text-dark">Ochish</span>
                    @endif
                </td>
                <td>{{ $log->user->ism_familiya ?? '—' }}</td>
                <td class="small text-muted">
                    @if($log->amal === 'yopish' && $log->natija_json)
                        {{ $log->natija_json['jami_shartnomalar'] ?? 0 }} ta shartnoma,
                        {{ $log->natija_json['kechikkan_shartnomalar'] ?? 0 }} ta kechikkan
                    @elseif($log->natija_json['izoh'] ?? null)
                        {{ $log->natija_json['izoh'] }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Yozuv topilmadi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{ $loglar->links() }}
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-yopish').forEach(btn => {
    btn.addEventListener('click', () => {
        const filialId = btn.dataset.filial;
        const sana = btn.dataset.sana;
        document.getElementById('yopish_filial_id').value = filialId;
        document.getElementById('yopish_sana').value = sana;
        document.getElementById('yopishOnkorish').textContent = 'Yuklanmoqda...';

        fetch(`{{ route('operatsion_kun.oldin_korish') }}?filial_id=${filialId}&sana=${sana}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('yopishOnkorish').textContent =
                    `${data.jami_shartnomalar} ta faol shartnoma, ${data.kechikkan_shartnomalar} ta kechikkan to'lov aniqlandi.`;
            });

        new bootstrap.Modal(document.getElementById('yopishModal')).show();
    });
});

document.querySelectorAll('.btn-ochish').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('ochish_filial_id').value = btn.dataset.filial;
        document.getElementById('ochish_sana').value = btn.dataset.sana;
        new bootstrap.Modal(document.getElementById('ochishModal')).show();
    });
});
</script>
@endpush
