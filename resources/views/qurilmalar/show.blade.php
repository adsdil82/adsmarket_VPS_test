@extends('layouts.app')
@section('title','Qurilma: '.$qurilma->toliq_nomi)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('qurilmalar.index') }}">Qurilmalar</a></li>
<li class="breadcrumb-item active">{{ $qurilma->toliq_nomi }}</li>
@endsection

@section('content')
@foreach(['muvaffaqiyat','xato'] as $tip)
@if(session($tip))
<div class="alert alert-{{ $tip==='muvaffaqiyat'?'success':'danger' }} alert-dismissible fade show py-2 mb-3">
    {{ session($tip) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@endforeach

<div class="row g-3">

{{-- Chap: Qurilma ma'lumotlari --}}
<div class="col-lg-4">

    {{-- Asosiy karta --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-phone me-2"></i>{{ $qurilma->toliq_nomi }}</h6>
            <span class="badge bg-{{ $qurilma->holat_rangi }}">{{ $qurilma->holat_nomi }}</span>
        </div>
        <div class="card-body">
            <table class="table table-sm mb-0" style="font-size:.85rem">
                <tr><td class="text-muted">Brend</td><td>{{ $qurilma->brend ?? '—' }}</td></tr>
                <tr><td class="text-muted">Model</td><td class="fw-medium">{{ $qurilma->model_nomi }}</td></tr>
                <tr><td class="text-muted">Rang</td><td>{{ $qurilma->rang ?? '—' }}</td></tr>
                <tr><td class="text-muted">Xotira</td><td>{{ $qurilma->xotira ?? '—' }}</td></tr>
                <tr><td class="text-muted">Serial</td><td class="font-monospace small">{{ $qurilma->serial_raqam ?? '—' }}</td></tr>
                <tr><td class="text-muted">IMEI 1</td><td class="font-monospace fw-medium">{{ $qurilma->imei1 ?? '—' }}</td></tr>
                @if($qurilma->imei2)<tr><td class="text-muted">IMEI 2</td><td class="font-monospace">{{ $qurilma->imei2 }}</td></tr>@endif
                @if($qurilma->imei3)<tr><td class="text-muted">IMEI 3</td><td class="font-monospace">{{ $qurilma->imei3 }}</td></tr>@endif
                @if($qurilma->imei4)<tr><td class="text-muted">IMEI 4</td><td class="font-monospace">{{ $qurilma->imei4 }}</td></tr>@endif
                <tr><td class="text-muted">Qo'shilgan</td><td>{{ $qurilma->qoshilgan_sana?->format('d.m.Y') ?? '—' }}</td></tr>
                @if($qurilma->sotilgan_sana)<tr><td class="text-muted">Sotilgan</td><td>{{ $qurilma->sotilgan_sana->format('d.m.Y') }}</td></tr>@endif
                <tr><td class="text-muted">Filial</td><td>{{ $qurilma->filial?->nomi ?? '—' }}</td></tr>
            </table>
        </div>
        @if(Auth::user()->isMenejerYoki())
        <div class="card-footer d-flex gap-2">
            <a href="{{ route('qurilmalar.edit',$qurilma) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>Tahrirlash
            </a>
            @if(Auth::user()->isAdmin())
            <form method="POST" action="{{ route('qurilmalar.destroy',$qurilma) }}" onsubmit="return confirm('O\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
            @endif
        </div>
        @endif
    </div>

    {{-- Shartnoma --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-0"><h6 class="fw-bold mb-0">Shartnoma</h6></div>
        <div class="card-body">
            @if($qurilma->kredit)
            <a href="{{ route('kreditlar.show',$qurilma->kredit) }}" class="fw-medium text-decoration-none">
                {{ $qurilma->kredit->shartnoma_raqam }}
            </a>
            <div class="text-muted small mt-1">Holat: {{ $qurilma->kredit->holat_nomi }}</div>
            <div class="text-muted small">Qoldiq: {{ number_format($qurilma->kredit->qoldiq_qarz,0,'.',' ') }} so'm</div>
            @else
            <p class="text-muted small mb-2">Hali shartnomaga biriktirilmagan</p>
            @if(Auth::user()->isMenejerYoki())
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#attachModal">
                <i class="bi bi-link-45deg me-1"></i>Biriktirish
            </button>
            @endif
            @endif
        </div>
    </div>

    {{-- Rozilik --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0">
            <h6 class="fw-bold mb-0">Rozilik
                @if($qurilma->rozilik_imzolangan)
                    <span class="badge bg-success ms-1" style="font-size:.7rem">Imzolangan</span>
                @else
                    <span class="badge bg-warning ms-1" style="font-size:.7rem">Kutilmoqda</span>
                @endif
            </h6>
        </div>
        <div class="card-body p-2">
            @forelse($qurilma->roziliklar as $r)
            <div class="d-flex justify-content-between align-items-center py-1" style="font-size:.8rem">
                <span>{{ $r->kanal }} — {{ $r->holat }}</span>
                <span class="text-muted">{{ $r->imzolangan_sana?->format('d.m.Y') ?? '—' }}</span>
            </div>
            @empty
            <p class="text-muted small mb-0">Rozilik yo'q</p>
            @endforelse
        </div>
    </div>

</div>

{{-- O'ng: Boshqaruv + Loglar --}}
<div class="col-lg-8">

    @if(Auth::user()->isAdmin())
    {{-- Lock/Unlock boshqaruvi --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-0">
            <h6 class="fw-bold mb-0"><i class="bi bi-shield-lock me-2"></i>Nazorat boshqaruvi</h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                {{-- Lock --}}
                @if($qurilma->canBeLocked())
                <div class="col-sm-4">
                    <button class="btn btn-danger w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#lockModal">
                        <i class="bi bi-lock me-1"></i>Bloklash
                    </button>
                </div>
                @endif
                {{-- Unlock --}}
                @if($qurilma->canBeUnlocked())
                <div class="col-sm-4">
                    <button class="btn btn-success w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#unlockModal">
                        <i class="bi bi-unlock me-1"></i>Blokdan chiqarish
                    </button>
                </div>
                @endif
                {{-- Release --}}
                @if(!$qurilma->isReleased())
                <div class="col-sm-4">
                    <form method="POST" action="{{ route('qurilmalar.release',$qurilma) }}" onsubmit="return confirm('Ozod qilinsinmi?')">
                        @csrf
                        <button class="btn btn-outline-secondary w-100 btn-sm"><i class="bi bi-check2-circle me-1"></i>Ozod qilish</button>
                    </form>
                </div>
                @endif
            </div>

            {{-- Ogohlantirish --}}
            @if(Auth::user()->isMenejerYoki())
            <hr class="my-2">
            <form method="POST" action="{{ route('qurilmalar.warn',$qurilma) }}" class="row g-2">
                @csrf
                <div class="col-sm-5">
                    <select name="provayder_kod" class="form-select form-select-sm">
                        @foreach($provayderlar as $p)
                        <option value="{{ $p->kod }}" {{ $p->ogoh_qollab ? '' : 'disabled' }}>{{ $p->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-5">
                    <input type="text" name="xabar" class="form-control form-control-sm" placeholder="Ogohlantirish matni..." required>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-warning btn-sm w-100"><i class="bi bi-bell me-1"></i>Yuborish</button>
                </div>
            </form>
            @endif
        </div>
    </div>
    @endif

    {{-- Provayder ulanishlari --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent border-0">
            <h6 class="fw-bold mb-0"><i class="bi bi-plug me-2"></i>Provider ulanishlari</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:.83rem">
                <thead class="table-light">
                    <tr><th>Provider</th><th>Holat</th><th>Tashqi ID</th><th>Oxirgi sinx</th></tr>
                </thead>
                <tbody>
                    @forelse($qurilma->provayderUlanishlari as $u)
                    <tr>
                        <td>{{ $u->provayder?->nomi }}</td>
                        <td><span class="badge bg-{{ $u->holat==='faol'?'success':($u->holat==='xato'?'danger':'secondary') }}">{{ $u->holat }}</span></td>
                        <td class="text-muted font-monospace small">{{ $u->tashqi_id ?? '—' }}</td>
                        <td class="text-muted small">{{ $u->oxirgi_sinx?->format('d.m.Y H:i') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center py-2">Provider ulanishlari yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Loglar --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between">
            <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Loglar</h6>
            <a href="{{ route('qurilmalar.logs',$qurilma) }}" class="btn btn-outline-secondary btn-sm py-0">Barchasi</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0" style="font-size:.8rem">
                <tbody>
                    @forelse($qurilma->loglar->take(10) as $log)
                    <tr>
                        <td class="text-muted" style="width:130px">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <span class="badge bg-{{ $log->holat_rangi }}" style="font-size:.65rem">{{ $log->holat }}</span>
                            <strong>{{ $log->amal_nomi }}</strong>
                        </td>
                        <td class="text-muted">{{ Str::limit($log->sabab, 60) }}</td>
                        <td class="text-muted small">{{ $log->xodim?->ism_familiya }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">Log yozuvlari yo'q</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>

{{-- Lock modal --}}
@if(Auth::user()->isAdmin() && $qurilma->canBeLocked())
<div class="modal fade" id="lockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold text-danger"><i class="bi bi-lock me-2"></i>Qurilmani bloklash</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('qurilmalar.lock',$qurilma) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Provider</label>
                        <select name="provayder_kod" class="form-select form-select-sm" required>
                            @foreach($provayderlar as $p)
                            @if($p->lock_qollab)
                            <option value="{{ $p->kod }}">{{ $p->nomi }}{{ $p->mock_rejim ? ' (mock)' : '' }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sabab <span class="text-danger">*</span></label>
                        <textarea name="sabab" class="form-control form-control-sm" rows="3" required
                                  placeholder="Bloklash sababi..."></textarea>
                    </div>
                    <div class="alert alert-warning py-2 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Faqat admin tomonidan bajariladi. Logga yoziladi.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-lock me-1"></i>Bloklash</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Unlock modal --}}
@if(Auth::user()->isAdmin() && $qurilma->canBeUnlocked())
<div class="modal fade" id="unlockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold text-success"><i class="bi bi-unlock me-2"></i>Blokdan chiqarish</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('qurilmalar.unlock',$qurilma) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Provider</label>
                        <select name="provayder_kod" class="form-select form-select-sm" required>
                            @foreach($provayderlar as $p)
                            @if($p->unlock_qollab)
                            <option value="{{ $p->kod }}">{{ $p->nomi }}{{ $p->mock_rejim ? ' (mock)' : '' }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Sabab</label>
                        <textarea name="sabab" class="form-control form-control-sm" rows="2"
                                  placeholder="Masalan: To'lov amalga oshirildi"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-unlock me-1"></i>Chiqarish</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Attach modal --}}
@if(Auth::user()->isMenejerYoki() && !$qurilma->kredit)
<div class="modal fade" id="attachModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-link-45deg me-2"></i>Shartnomaga biriktirish</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('qurilmalar.attach',$qurilma) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Shartnoma raqami</label>
                        <input type="number" name="reg_kredit_id" class="form-control" placeholder="Shartnoma ID" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mijoz ID</label>
                        <input type="number" name="mijoz_id" class="form-control" placeholder="Mijoz ID" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-link me-1"></i>Biriktirish</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
