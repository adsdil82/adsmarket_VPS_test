@extends('layouts.app')
@section('title','Tovar guruhlari')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar guruhlari</li>
@endsection

@push('styles')
<style>
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;
}
.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody td { padding:6px 8px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; }
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.badge-modern { font-size:.68rem; font-weight:800; padding:3px 8px; border-radius:4px; letter-spacing:.03em; }
.b-faol { background:#22c55e; color:#fff; }
.b-nofaol { background:#64748b; color:#fff; }
</style>
@endpush

@section('content')

<div class="bft-header-card mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-tags fs-5"></i>
        <span class="fw-bold">Tovar guruhlari</span>
        <span class="badge bg-light text-dark">{{ $guruhlar->total() }} ta</span>
    </div>
    <button type="button" class="btn btn-sm btn-light fw-bold py-1" data-bs-toggle="modal" data-bs-target="#guruh-modal" onclick="tozalash()">
        <i class="bi bi-plus-lg me-1"></i>Yangi guruh
    </button>
</div>

<div class="bank-wrap mb-3">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">#</th>
                <th class="tl">Guruh nomi</th>
                <th class="tl">Tavsif</th>
                <th>Tovarlar</th>
                <th>Holat</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($guruhlar as $g)
            <tr>
                <td class="tl text-muted">{{ $g->id }}</td>
                <td class="tl fw-bold">{{ $g->nomi }}</td>
                <td class="tl text-muted">{{ Str::limit($g->tavsif, 40) ?: '—' }}</td>
                <td class="num">{{ $g->tovarlar_count }}</td>
                <td class="text-center">
                    <span class="badge-modern b-{{ $g->holat==='faol'?'faol':'nofaol' }}">{{ $g->holat }}</span>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#guruh-modal"
                            onclick="tahrirlash({{ $g->id }},'{{ addslashes($g->nomi) }}','{{ addslashes($g->tavsif) }}','{{ $g->holat }}')"
                            title="Tahrirlash">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="{{ route('tovar-guruhlar.destroy',$g) }}" class="d-inline"
                          onsubmit="return confirm('«{{$g->nomi}}» guruhini o\'chirish?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger py-0" {{ $g->tovarlar_count>0?'disabled':'' }}>
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-5">Guruhlar yo'q</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($guruhlar->hasPages())
<div>{{ $guruhlar->links('pagination::bootstrap-5') }}</div>
@endif

{{-- ── Guruh qo'shish/tahrirlash modali ─────────────────────────── --}}
<div class="modal fade" id="guruh-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bft-header-card" style="border-radius:0">
                <h6 class="mb-0 fw-bold" id="forma-sarlavha">
                    <i class="bi bi-plus-circle me-2"></i>Yangi guruh
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="guruh-forma" action="{{ route('tovar-guruhlar.store') }}">
                @csrf
                <input type="hidden" name="_method" id="guruh-method" value="">
                <input type="hidden" name="guruh_id" id="guruh-id" value="">
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-medium">Guruh nomi <span class="text-danger">*</span></label>
                        <input type="text" name="nomi" id="guruh-nomi" class="form-control" required
                               placeholder="Masalan: Elektron tovarlar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tavsif</label>
                        <textarea name="tavsif" id="guruh-tavsif" class="form-control" rows="3"
                                  placeholder="Qo'shimcha ma'lumot..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-medium">Holat</label>
                        <select name="holat" id="guruh-holat" class="form-select">
                            <option value="faol">Faol</option>
                            <option value="nofaol">Nofaol</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i><span id="btn-matn">Saqlash</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('guruh-modal')).show();
});
@endif
function tahrirlash(id, nomi, tavsif, holat) {
    document.getElementById('guruh-id').value = id;
    document.getElementById('guruh-nomi').value = nomi;
    document.getElementById('guruh-tavsif').value = tavsif;
    document.getElementById('guruh-holat').value = holat;
    document.getElementById('guruh-method').value = 'PUT';
    document.getElementById('guruh-forma').action = `/tovar-guruhlar/${id}`;
    document.getElementById('forma-sarlavha').innerHTML = `<i class="bi bi-pencil me-2"></i>Tahrirlash: ${nomi}`;
    document.getElementById('btn-matn').textContent = 'Yangilash';
}
function tozalash() {
    document.getElementById('guruh-forma').action = '{{ route("tovar-guruhlar.store") }}';
    document.getElementById('guruh-forma').reset();
    document.getElementById('guruh-method').value = '';
    document.getElementById('guruh-id').value = '';
    document.getElementById('forma-sarlavha').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Yangi guruh';
    document.getElementById('btn-matn').textContent = 'Saqlash';
}
</script>
@endpush
