@extends('layouts.app')
@section('title','Pochta Shablonlari')
@section('content')
<div class="container-fluid px-3 py-3">

    {{-- Flash --}}
    @if(session('muvaffaqiyat'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            {{ session('muvaffaqiyat') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('xato'))
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            {{ session('xato') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-envelope-paper me-1 text-primary"></i>
            Pochta Shablonlari
        </h5>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg me-1"></i>Yangi shablon
        </button>
    </div>

    {{-- O'zgaruvchilar yordam paneli --}}
    <div class="alert alert-light border py-2 small mb-3">
        <strong><i class="bi bi-braces me-1"></i>O'zgaruvchilar:</strong>
        @foreach($ozgaruvchilar as $kalit => $tavsif)
            <code class="me-2">{{{{ $kalit }}}}</code> — {{ $tavsif }}@if(!$loop->last), @endif
        @endforeach
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Nomi</th>
                    <th style="width:120px">Qayta yuborish</th>
                    <th style="width:80px">Holat</th>
                    <th style="width:60px">Tartib</th>
                    <th style="width:100px">Amallar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shablonlar as $sh)
                <tr>
                    <td class="text-muted small">{{ $sh->id }}</td>
                    <td>
                        <strong>{{ $sh->nomi }}</strong>
                        <div class="text-muted small" style="white-space:pre-wrap;max-height:40px;overflow:hidden;">
                            {{ Str::limit($sh->matn, 80) }}
                        </div>
                    </td>
                    <td class="small">
                        @if($sh->qayta_yuborish_kun > 0)
                            <span class="badge bg-light text-dark border">{{ $sh->qayta_yuborish_kun }} kun</span>
                        @else
                            <span class="text-muted">Cheklovsiz</span>
                        @endif
                    </td>
                    <td>
                        @if($sh->holat === 'faol')
                            <span class="badge bg-success">Faol</span>
                        @else
                            <span class="badge bg-secondary">Nofaol</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $sh->sort_order }}</td>
                    <td>
                        <button class="btn btn-xs btn-outline-secondary"
                            onclick="editShablon({{ $sh->id }}, @json($sh->nomi), @json($sh->matn), {{ $sh->qayta_yuborish_kun }}, '{{ $sh->holat }}', {{ $sh->sort_order }})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('malumotnamalar.pochta-shablonlar.destroy', $sh) }}"
                              class="d-inline" onsubmit="return confirm('O\'chirishni tasdiqlaysizmi?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Hozircha shablon yo'q. Yangi shablon qo'shing.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ADD Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('malumotnamalar.pochta-shablonlar.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Yangi pochta shabloni</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('malumotnamalar.pochta-shablonlar._form', ['sh' => null, 'prefix' => ''])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-sm btn-primary">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- EDIT Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">Shablonni tahrirlash</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('malumotnamalar.pochta-shablonlar._form', ['sh' => null, 'prefix' => 'edit_'])
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-sm btn-primary">Saqlash</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function editShablon(id, nomi, matn, kun, holat, sort) {
    document.getElementById('editForm').action = '/malumotnamalar/pochta-shablonlar/' + id;
    document.getElementById('edit_nomi').value               = nomi;
    document.getElementById('edit_matn').value               = matn;
    document.getElementById('edit_qayta_yuborish_kun').value = kun;
    document.getElementById('edit_holat').value              = holat;
    document.getElementById('edit_sort_order').value         = sort;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
