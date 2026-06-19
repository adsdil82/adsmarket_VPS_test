@extends('layouts.app')
@section('title','Tovar guruhlari')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar guruhlari</li>
@endsection

@section('content')
<div class="row g-3">

{{-- ── Guruhlar ro'yxati ──────────────────────────────────────────── --}}
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Tovar guruhlari</h6>
        <span class="badge bg-secondary">{{ $guruhlar->total() }} ta</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Guruh nomi</th>
                    <th>Tavsif</th>
                    <th class="text-center">Tovarlar</th>
                    <th class="text-center">Holat</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($guruhlar as $g)
                <tr>
                    <td class="text-muted small">{{ $g->id }}</td>
                    <td class="fw-medium">{{ $g->nomi }}</td>
                    <td class="text-muted small">{{ Str::limit($g->tavsif, 40) ?? '—' }}</td>
                    <td class="text-center"><span class="badge bg-primary">{{ $g->tovarlar_count }}</span></td>
                    <td class="text-center">
                        <span class="badge bg-{{ $g->holat==='faol'?'success':'secondary' }}">{{ $g->holat }}</span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary py-0"
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
    <div class="card-footer">{{ $guruhlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
</div>

{{-- ── Guruh qo'shish/tahrirlash formasi ─────────────────────────── --}}
<div class="col-lg-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <h6 class="mb-0 fw-bold" id="forma-sarlavha">
                <i class="bi bi-plus-circle me-2 text-success"></i>Yangi guruh
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" id="guruh-forma" action="{{ route('tovar-guruhlar.store') }}">
                @csrf
                <input type="hidden" name="_method" id="guruh-method" value="">
                <input type="hidden" name="guruh_id" id="guruh-id" value="">

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
                <div class="mb-3">
                    <label class="form-label fw-medium">Holat</label>
                    <select name="holat" id="guruh-holat" class="form-select">
                        <option value="faol">Faol</option>
                        <option value="nofaol">Nofaol</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1">
                        <i class="bi bi-save me-1"></i><span id="btn-matn">Saqlash</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="tozalash()">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function tahrirlash(id, nomi, tavsif, holat) {
    document.getElementById('guruh-id').value = id;
    document.getElementById('guruh-nomi').value = nomi;
    document.getElementById('guruh-tavsif').value = tavsif;
    document.getElementById('guruh-holat').value = holat;
    document.getElementById('guruh-method').value = 'PUT';
    document.getElementById('guruh-forma').action = `/tovar-guruhlar/${id}`;
    document.getElementById('forma-sarlavha').innerHTML = `<i class="bi bi-pencil me-2 text-warning"></i>Tahrirlash: ${nomi}`;
    document.getElementById('btn-matn').textContent = 'Yangilash';
    window.scrollTo(0, 0);
}
function tozalash() {
    document.getElementById('guruh-forma').action = '{{ route("tovar-guruhlar.store") }}';
    document.getElementById('guruh-forma').reset();
    document.getElementById('guruh-method').value = '';
    document.getElementById('guruh-id').value = '';
    document.getElementById('forma-sarlavha').innerHTML = '<i class="bi bi-plus-circle me-2 text-success"></i>Yangi guruh';
    document.getElementById('btn-matn').textContent = 'Saqlash';
}
</script>
@endpush
