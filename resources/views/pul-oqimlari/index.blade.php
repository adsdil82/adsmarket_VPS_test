@extends('layouts.app')
@section('title','Pul Oqimlari — CashFlow')
@section('breadcrumb')
<li class="breadcrumb-item active">Pul Oqimlari</li>
@endsection

@section('content')
@if(session('muvaffaqiyat'))
<div class="alert alert-success alert-dismissible fade show py-2 mb-3">
    <i class="bi bi-check-circle me-1"></i>{{ session('muvaffaqiyat') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-arrow-left-right me-2" style="color:#6366f1"></i>Pul Oqimlari
        <span class="badge bg-secondary bg-opacity-15 text-secondary ms-1" style="font-size:.7rem;font-weight:600">CashFlow</span>
    </h5>
    <div class="d-flex gap-2">
        <a href="{{ route('pul-oqimlari.hisobot') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-file-earmark-bar-graph me-1"></i>Hisobot
        </a>
        @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki() || Auth::user()->isKassir())
        <a href="{{ route('pul-oqimlari.create', ['yunalish'=>'kirim']) }}" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg me-1"></i>Kirim
        </a>
        <a href="{{ route('pul-oqimlari.create', ['yunalish'=>'chiqim']) }}" class="btn btn-sm btn-danger">
            <i class="bi bi-dash-lg me-1"></i>Chiqim
        </a>
        @endif
    </div>
</div>

{{-- KPI kartalar --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #22c55e !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Jami Kirim</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:#16a34a">
                    {{ number_format($stat['kirim'],0,'.',' ') }}
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem">so'm</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ef4444 !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Jami Chiqim</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:#dc2626">
                    {{ number_format($stat['chiqim'],0,'.',' ') }}
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem">so'm</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid {{ $stat['sof'] >= 0 ? '#3b82f6' : '#f59e0b' }} !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Sof Oqim (Net)</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:{{ $stat['sof'] >= 0 ? '#1d4ed8' : '#b45309' }}">
                    {{ $stat['sof'] >= 0 ? '+' : '' }}{{ number_format($stat['sof'],0,'.',' ') }}
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem">so'm</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #8b5cf6 !important">
            <div class="card-body py-3 px-3">
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.5px">Yozuvlar</div>
                <div class="fw-bold mt-1" style="font-size:1.15rem;color:#7c3aed">{{ number_format($oqimlar->total(),0,'.',' ') }}</div>
                <div class="text-muted mt-1" style="font-size:.7rem">{{ \Carbon\Carbon::parse($danSana)->format('d.m') }} — {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtr --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            @if(Auth::user()->isAdmin())
            <div class="col-sm-2">
                <select name="filial_id" class="form-select form-select-sm">
                    <option value="">Barcha filial</option>
                    @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id')==$f->id?'selected':'' }}>{{ $f->nomi }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-sm-2">
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-2">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            <div class="col-sm-auto">
                <select name="yunalish" class="form-select form-select-sm">
                    <option value="">Kirim + Chiqim</option>
                    <option value="kirim" {{ request('yunalish')==='kirim'?'selected':'' }}>Faqat Kirim</option>
                    <option value="chiqim" {{ request('yunalish')==='chiqim'?'selected':'' }}>Faqat Chiqim</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select name="kategoriya" class="form-select form-select-sm">
                    <option value="">Barcha kategoriya</option>
                    @foreach($kategoriyalar as $kat)
                        <optgroup label="{{ $kat->kod }} — {{ $kat->nomi }}">
                            @foreach($kat->bolalar as $b)
                            <option value="{{ $b->id }}" {{ request('kategoriya')==$b->id?'selected':'' }}>{{ $b->kod }} — {{ $b->nomi }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="kassa_id" class="form-select form-select-sm">
                    <option value="">Barcha kassa</option>
                    @foreach($kassalar as $k)
                        <option value="{{ $k->id }}" {{ request('kassa_id')==$k->id?'selected':'' }}>{{ $k->nomi }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filtr</button>
                <a href="{{ route('pul-oqimlari.index') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Kategoriya breakdown (kirim va chiqim alohida) --}}
@if($kirimByKat->count() || $chiqimByKat->count())
<div class="row g-3 mb-3">
    @if($kirimByKat->count())
    <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header py-2 px-3 bg-white d-flex justify-content-between align-items-center" style="cursor:pointer" onclick="blokToggle('blok-kirim-kat', this)">
            <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase">Kirim — Kategoriyalar bo'yicha</div>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-plus-lg"></i></button>
        </div>
        <div id="blok-kirim-kat" class="table-responsive" style="display:none">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kod</th>
                        <th>Kategoriya</th>
                        <th class="text-end">Soni</th>
                        <th class="text-end">Summa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kirimByKat as $ck)
                    @php $kat = $ck->kategoriya; @endphp
                    <tr>
                        <td class="small text-muted">{{ $kat?->kod ?? '—' }}</td>
                        <td class="small">{{ $kat?->nomi ?? '—' }}</td>
                        <td class="text-end small text-muted">{{ number_format($ck->soni) }}</td>
                        <td class="text-end small text-success fw-bold">{{ number_format($ck->jami,0,'.',' ') }}</td>
                    </tr>
                    @endforeach
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="small">Jami:</td>
                        <td class="text-end small">{{ number_format($kirimByKat->sum('soni')) }}</td>
                        <td class="text-end small text-success">{{ number_format($kirimByKat->sum('jami'),0,'.',' ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
    @endif
    @if($chiqimByKat->count())
    <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header py-2 px-3 bg-white d-flex justify-content-between align-items-center" style="cursor:pointer" onclick="blokToggle('blok-chiqim-kat', this)">
            <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase">Chiqim — Kategoriyalar bo'yicha</div>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-plus-lg"></i></button>
        </div>
        <div id="blok-chiqim-kat" class="table-responsive" style="display:none">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kod</th>
                        <th>Kategoriya</th>
                        <th class="text-end">Soni</th>
                        <th class="text-end">Summa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chiqimByKat as $ck)
                    @php $kat = $ck->kategoriya; @endphp
                    <tr>
                        <td class="small text-muted">{{ $kat?->kod ?? '—' }}</td>
                        <td class="small">{{ $kat?->nomi ?? '—' }}</td>
                        <td class="text-end small text-muted">{{ number_format($ck->soni) }}</td>
                        <td class="text-end small text-danger fw-bold">{{ number_format($ck->jami,0,'.',' ') }}</td>
                    </tr>
                    @endforeach
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="small">Jami:</td>
                        <td class="text-end small">{{ number_format($chiqimByKat->sum('soni')) }}</td>
                        <td class="text-end small text-danger">{{ number_format($chiqimByKat->sum('jami'),0,'.',' ') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    </div>
    @endif
</div>
@endif

{{-- Kassalar bo'yicha harakat (davr boshiga / oxiriga qoldiq) --}}
@if($kassaHarakati->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2 px-3 bg-white d-flex justify-content-between align-items-center" style="cursor:pointer" onclick="blokToggle('blok-kassa-harakat', this)">
        <div class="text-muted" style="font-size:.72rem;font-weight:600;text-transform:uppercase">
            Kassalar bo'yicha harakat — {{ \Carbon\Carbon::parse($danSana)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-plus-lg"></i></button>
    </div>
    <div id="blok-kassa-harakat" class="table-responsive" style="display:none">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kassa</th>
                    <th class="text-end">Davr boshiga qoldiq</th>
                    <th class="text-end text-success">Kirim</th>
                    <th class="text-end text-danger">Chiqim</th>
                    <th class="text-end">Davr oxiriga qoldiq</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kassaHarakati as $h)
                <tr>
                    <td class="small fw-medium">
                        <i class="bi bi-{{ $h->kassa->tur === 'naqd' ? 'cash-coin' : ($h->kassa->tur === 'terminal' ? 'credit-card' : 'bank') }} me-1 text-muted"></i>
                        {{ $h->kassa->nomi }}
                    </td>
                    <td class="text-end small text-muted">{{ number_format($h->ochilish_qoldiq,0,'.',' ') }}</td>
                    <td class="text-end small text-success">+{{ number_format($h->kirim,0,'.',' ') }}</td>
                    <td class="text-end small text-danger">-{{ number_format($h->chiqim,0,'.',' ') }}</td>
                    <td class="text-end small fw-bold {{ $h->yopilish_qoldiq >= 0 ? '' : 'text-danger' }}">
                        {{ number_format($h->yopilish_qoldiq,0,'.',' ') }}
                    </td>
                </tr>
                @endforeach
                <tr class="table-light fw-bold">
                    <td class="small">Jami:</td>
                    <td class="text-end small">{{ number_format($kassaHarakati->sum('ochilish_qoldiq'),0,'.',' ') }}</td>
                    <td class="text-end small text-success">+{{ number_format($kassaHarakati->sum('kirim'),0,'.',' ') }}</td>
                    <td class="text-end small text-danger">-{{ number_format($kassaHarakati->sum('chiqim'),0,'.',' ') }}</td>
                    <td class="text-end small">{{ number_format($kassaHarakati->sum('yopilish_qoldiq'),0,'.',' ') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Jadval --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0 align-middle" style="font-size:.83rem">
            <thead class="table-light">
                <tr>
                    <th style="width:80px">Sana</th>
                    <th style="width:60px">Tur</th>
                    <th>Kategoriya</th>
                    <th>Izoh</th>
                    <th>Kassa</th>
                    @if(Auth::user()->isAdmin())<th>Xodim</th>@endif
                    <th class="text-end" style="width:115px">Kirim</th>
                    <th class="text-end" style="width:115px">Chiqim</th>
                    <th class="text-end" style="width:130px">Qoldiq</th>
                    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())<th class="text-center" style="width:80px">Amal</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($oqimlar as $o)
                @php
                    $isKirim = $o->yunalish === 'kirim';
                    $kat = $o->kategoriya;
                @endphp
                <tr>
                    <td class="text-nowrap">{{ $o->sana->format('d.m.Y') }}</td>
                    <td class="text-nowrap fw-medium" style="color:{{ $isKirim ? '#16a34a' : '#dc2626' }}">
                        {{ $isKirim ? '↑ Kirim' : '↓ Chiqim' }}
                    </td>
                    <td class="text-nowrap">
                        @if($kat)
                            {{ $kat->kod }} — {{ $kat?->ota?->nomi ? $kat->ota->nomi.' / ' : '' }}{{ $kat->nomi }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($o->izoh, 45) }}</td>
                    <td>{{ $o->kassa?->nomi ?? '—' }}</td>
                    @if(Auth::user()->isAdmin())
                    <td>{{ $o->xodim?->ism_familiya }}</td>
                    @endif
                    <td class="text-end fw-bold text-nowrap" style="color:#16a34a">
                        {{ $isKirim ? number_format($o->summa,0,'.',' ') : '' }}
                    </td>
                    <td class="text-end fw-bold text-nowrap" style="color:#dc2626">
                        {{ !$isKirim ? number_format($o->summa,0,'.',' ') : '' }}
                    </td>
                    <td class="text-end text-nowrap fw-medium {{ $o->qoldiq_keyin < 0 ? 'text-danger' : '' }}">
                        {{ number_format($o->qoldiq_keyin,0,'.',' ') }}
                    </td>
                    @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
                    <td class="text-center text-nowrap">
                        @if($o->manba_tur === 'manual')
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('pul-oqimlari.edit',$o) }}" class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(Auth::user()->isAdmin())
                            <form method="POST" action="{{ route('pul-oqimlari.destroy',$o) }}" class="d-inline"
                                  onsubmit="return confirm('Bekor qilish?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger py-0 px-1" style="font-size:.7rem">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                        @else
                            <span class="text-muted" style="font-size:.7rem" title="Avtomatik yozuv — manba modulidan o'chiriladi/tahrirlanadi">🔗</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="11" class="text-center text-muted py-5">
                    <i class="bi bi-arrow-left-right fs-3 d-block mb-2 opacity-25"></i>
                    Tanlangan davr uchun operatsiyalar topilmadi
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($oqimlar->hasPages())
    <div class="card-footer">{{ $oqimlar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function blokToggle(blokId, headerEl) {
    var blok = document.getElementById(blokId);
    var icon = headerEl.querySelector('i');
    var yashirin = blok.style.display === 'none';
    blok.style.display = yashirin ? '' : 'none';
    if (icon) icon.className = 'bi ' + (yashirin ? 'bi-dash-lg' : 'bi-plus-lg');
}
</script>
@endpush
