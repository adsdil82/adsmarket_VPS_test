@extends('layouts.app')
@section('title','Harajat turlari')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('malumotnamalar.index') }}">Ma'lumotnomalar</a></li>
<li class="breadcrumb-item active">Harajat turlari</li>
@endsection

@push('styles')
<style>
.htur-row:hover { background: #f8f9fa; }
.modal-content { border: none; border-radius: .75rem; }
.modal-header { background: linear-gradient(135deg,#7f1d1d,#dc2626); color: #fff; border-radius: .75rem .75rem 0 0; }
.modal-header .btn-close { filter: invert(1) grayscale(1) brightness(2); }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-tags text-danger me-2"></i>Harajat turlari</h5>
        <small class="text-muted">Har bir tur Pul Oqimlari moddasiga (CF) bog'langan — harajat kiritilganda avtomat hisoblanadi</small>
    </div>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#yangiTurModal">
        <i class="bi bi-plus-lg me-1"></i>Yangi tur
    </button>
</div>

@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2">{{ session('muvaffaqiyat') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('xato'))
<div class="alert alert-danger alert-dismissible fade show py-2">{{ session('xato') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if($errors->any())
<div class="alert alert-danger py-2">{{ $errors->first() }}</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.875rem">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Nomi</th><th>Pul oqimi moddasi</th>
                    <th class="text-center">Xodim?</th><th class="text-center">Schyotchik?</th>
                    <th class="text-center">Ishlatilgan</th><th>Holat</th><th class="text-end">Amallar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($turlar as $t)
                <tr class="htur-row">
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $t->nomi }}</td>
                    <td class="small">
                        @if($t->kategoriya)
                            <span class="badge bg-{{ $t->kategoriya->yunalish === 'kirim' ? 'success' : 'danger' }} bg-opacity-75">
                                {{ $t->kategoriya->kod }}
                            </span>
                            {{ $t->kategoriya->ota ? $t->kategoriya->ota->nomi.' / ' : '' }}{{ $t->kategoriya->nomi }}
                        @else
                            <span class="text-danger">— yo'q —</span>
                        @endif
                    </td>
                    <td class="text-center">{!! $t->talab_xodim ? '<i class="bi bi-check-lg text-success"></i>' : '—' !!}</td>
                    <td class="text-center">{!! $t->talab_schetchik ? '<i class="bi bi-check-lg text-success"></i>' : '—' !!}</td>
                    <td class="text-center text-muted">{{ $t->harajatlar_count }} ta</td>
                    <td>
                        <span class="badge bg-{{ $t->holat==='faol' ? 'success' : 'secondary' }}">
                            {{ $t->holat==='faol' ? 'Faol' : 'Nofaol' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $t->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="{{ route('malumotnamalar.harajat-turlari.destroy', $t) }}" class="d-inline"
                              onsubmit="return confirm('Harajat turini o\'chirish?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Harajat turlari yo'q</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($taminotchiSoni > 0)
<div class="card border-0 shadow-sm border-start border-4 border-purple mb-4" style="border-left-color:#7c3aed!important">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-1 fw-bold"><i class="bi bi-truck me-2" style="color:#7c3aed"></i>
                Eski "Ta'minotchilar" harajatlari — {{ number_format($taminotchiSoni,0,'.',' ') }} ta yozuv
            </h6>
            <small class="text-muted">Bular alohida vositada — har biri qaysi ta'minotchiga tegishli ekanini taxmin qilib, guruhlarga bo'lib ko'rsatadi (siz tasdiqlaysiz).</small>
        </div>
        <a href="{{ route('malumotnamalar.harajat-turlari.taminotchi-migratsiya') }}" class="btn btn-sm text-white" style="background:#7c3aed">
            <i class="bi bi-arrow-right-circle me-1"></i>Ko'rib chiqish
        </a>
    </div>
</div>
@endif

@if($bogLanmaganlar->count())
<div class="card border-0 shadow-sm border-start border-4 border-warning">
    <div class="card-header bg-warning bg-opacity-25 py-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>
            Eski harajatlar — hali turga bog'lanmagan ({{ $bogLanmaganlar->count() }} xil nom)
        </h6>
        <small class="text-muted">Bular Pul Oqimlari hisobotida ko'rinmaydi. Har birini bir martalik tur tanlab bog'lang — shunda tarixiy CHIQIM/KIRIM yozuvi ham yaratiladi.</small>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.85rem">
            <thead class="table-light">
                <tr>
                    <th>Eski nom</th><th class="text-center">Soni</th><th class="text-end">Jami summa</th>
                    <th>Yangi tur</th><th>Kassa turi</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bogLanmaganlar as $b)
                <tr>
                    <td>{{ $b->turi }}</td>
                    <td class="text-center">{{ $b->soni }}</td>
                    <td class="text-end {{ $b->jami < 0 ? 'text-success' : 'text-danger' }}">{{ number_format($b->jami,0,'.',' ') }}</td>
                    <td>
                        <select name="harajat_turi_id" form="boglashForm{{ $loop->index }}" class="form-select form-select-sm" required style="min-width:200px">
                            <option value="">— tur tanlang —</option>
                            @foreach($turlar as $t)
                                <option value="{{ $t->id }}">{{ $t->nomi }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select name="kassa_turi" form="boglashForm{{ $loop->index }}" class="form-select form-select-sm" required>
                            <option value="naqd">Naqd</option>
                            <option value="terminal">Terminal</option>
                            <option value="bank">Bank</option>
                        </select>
                    </td>
                    <td>
                        <button type="submit" form="boglashForm{{ $loop->index }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-link-45deg me-1"></i>Bog'lash
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Har bir "Bog'lash" qatori uchun alohida forma — jadval tashqarisida,
     <table> ichida <form> joylashtirib bo'lmaydi, shuning uchun
     input/select/button'lar form="..." atributi orqali bog'lanadi. --}}
@foreach($bogLanmaganlar as $b)
<form id="boglashForm{{ $loop->index }}" method="POST" action="{{ route('malumotnamalar.harajat-turlari.boglash') }}"
      onsubmit="return confirm('{{ $b->soni }} ta yozuv bog\'lanadi va Pul Oqimlariga yoziladi. Davom etilsinmi?')" class="d-none">
    @csrf
    <input type="hidden" name="eski_turi" value="{{ $b->turi }}">
</form>
@endforeach
@endif

</div>

{{-- ─── Yangi tur qo'shish modali ─────────────────────────────── --}}
<div class="modal fade" id="yangiTurModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('malumotnamalar.harajat-turlari.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Yangi harajat turi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nomi <span class="text-danger">*</span></label>
                    <input type="text" name="nomi" class="form-control" placeholder="Masalan: Internet xarajati" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pul oqimi moddasi (CF) <span class="text-danger">*</span></label>
                    <select name="pul_kategoriya_id" class="form-select" required>
                        <option value="">— tanlang —</option>
                        @foreach($kategoriyalar as $k)
                            <option value="{{ $k->id }}">
                                {{ $k->yunalish === 'kirim' ? '↓' : '↑' }} {{ $k->kod }} — {{ $k->ota ? $k->ota->nomi.' / ' : '' }}{{ $k->nomi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" name="talab_xodim" value="1" id="yangiXodim">
                    <label class="form-check-label small" for="yangiXodim">Xodim tanlash so'raladi (masalan: Ish haqi)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="talab_schetchik" value="1" id="yangiSchetchik">
                    <label class="form-check-label small" for="yangiSchetchik">Schyotchik raqami so'raladi (masalan: Elektr/Gaz/Suv)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
            </div>
        </form>
    </div>
</div>

{{-- ─── Tahrirlash modallari (jadvaldan tashqarida) ──────────────── --}}
@foreach($turlar as $t)
<div class="modal fade" id="editModal{{ $t->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('malumotnamalar.harajat-turlari.update', $t) }}" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Harajat turini tahrirlash</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nomi</label>
                    <input type="text" name="nomi" class="form-control" value="{{ $t->nomi }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pul oqimi moddasi (CF)</label>
                    <select name="pul_kategoriya_id" class="form-select" required>
                        @foreach($kategoriyalar as $k)
                            <option value="{{ $k->id }}" {{ $t->pul_kategoriya_id == $k->id ? 'selected' : '' }}>
                                {{ $k->yunalish === 'kirim' ? '↓' : '↑' }} {{ $k->kod }} — {{ $k->ota ? $k->ota->nomi.' / ' : '' }}{{ $k->nomi }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="talab_xodim" value="1" id="exXodim{{ $t->id }}" {{ $t->talab_xodim ? 'checked' : '' }}>
                            <label class="form-check-label small" for="exXodim{{ $t->id }}">Xodim tanlanadi</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="talab_schetchik" value="1" id="exSchetchik{{ $t->id }}" {{ $t->talab_schetchik ? 'checked' : '' }}>
                            <label class="form-check-label small" for="exSchetchik{{ $t->id }}">Schyotchik raqami</label>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label small fw-bold">Holat</label>
                    <select name="holat" class="form-select">
                        <option value="faol" {{ $t->holat==='faol' ? 'selected' : '' }}>Faol</option>
                        <option value="nofaol" {{ $t->holat==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                <button type="submit" class="btn btn-primary btn-sm">Saqlash</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
