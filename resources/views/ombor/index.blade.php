@extends('layouts.app')
@section('title', "Ombor qoldig'i")
@section('breadcrumb')
<li class="breadcrumb-item active">Ombor qoldig'i</li>
@endsection

@push('styles')
<style>
.om-wrap { display:flex; border:1px solid #c7d7f8; border-radius:8px; overflow:hidden; height:calc(100vh - 160px); }

.om-left { width:280px; min-width:220px; background:#f8fafd; border-right:1px solid #c7d7f8; display:flex; flex-direction:column; }
.om-left-head { background:linear-gradient(180deg,#2563eb,#1d4ed8); padding:10px 12px; color:#fff; font-weight:700; font-size:.8rem; }
.om-left-list { flex:1; overflow-y:auto; }
.om-item { display:block; padding:10px 12px; border-bottom:1px solid #e8ecf5; text-decoration:none; color:#1e293b; transition:background .1s; }
.om-item:hover { background:#e8f0fe; color:#1e293b; }
.om-item.active { background:#2563eb; color:#fff; }
.om-item.active .om-sub { color:#dbeafe; }
.om-item .om-nomi { font-weight:600; font-size:.85rem; }
.om-item .om-sub { font-size:.72rem; color:#94a3b8; }
.om-tur-badge { font-size:.62rem; font-weight:700; padding:1px 6px; border-radius:3px; text-transform:uppercase; }
.om-tur-asosiy { background:#dbeafe; color:#1d4ed8; }
.om-tur-qoshimcha { background:#fef9c3; color:#a16207; }
.om-tur-karantin { background:#fee2e2; color:#b91c1c; }
.om-tur-qaytarish { background:#f3e8ff; color:#7e22ce; }

.om-right { flex:1; display:flex; flex-direction:column; overflow:hidden; background:#fff; }
.om-right-head { background:linear-gradient(90deg,#eef3ff,#e8f0fe); border-bottom:1px solid #c7d7f8; padding:10px 16px; }
.om-right-body { flex:1; overflow-y:auto; }

.bank-table { border-collapse:collapse; font-size:.83rem; width:100%; }
.bank-table thead th { position:sticky; top:0; z-index:5; background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; font-weight:700; font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; padding:8px 10px; border-right:1px solid rgba(255,255,255,.15); white-space:nowrap; text-align:right; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { border-bottom:1px solid #e2e8f4; }
.bank-table tbody tr:hover { background:#eff6ff; }
.bank-table tbody tr:nth-child(even) { background:#f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background:#fff; }
.bank-table tbody td { padding:6px 10px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.bank-table tfoot td { position:sticky; bottom:0; background:linear-gradient(90deg,#1e3a8a,#1e40af); color:#fff; font-weight:700; font-size:.8rem; padding:7px 10px; border-top:2px solid #60a5fa; }

.kam-qoldiq { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }
.yetarli { background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }

.filter-bar { background:linear-gradient(90deg,#eef3ff,#e8f0fe); border:1px solid #c7d7f8; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 12px; }
.filter-bar .form-control,.filter-bar .form-select { background:#fff; border:1px solid #93c5fd; color:#1e3a8a; font-size:.79rem; height:30px; padding:3px 8px; }
</style>
@endpush

@section('content')
@php $n = fn($v) => number_format((float)$v,0,'.',' '); @endphp

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-boxes text-warning" style="font-size:1.1rem"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:.95rem">Ombor qoldig'i</span>
        </div>
        @if($tanlanganOmbor)<input type="hidden" name="ombor_id" value="{{ $tanlanganOmbor->id }}">@endif
        <div style="width:200px">
            <input type="search" name="qidiruv" class="form-control" placeholder="Tovar qidirish..." value="{{ request('qidiruv') }}">
        </div>
        <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" name="faqat_mavjud" value="1" id="faqatMavjud" {{ request('faqat_mavjud') ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label small" for="faqatMavjud">Faqat mavjud (qoldiq &gt; 0)</label>
        </div>
        <button type="submit" class="btn btn-primary btn-sm px-3" style="height:30px"><i class="bi bi-search me-1"></i>Filter</button>
        <a href="{{ route('ombor.index', ['ombor_id'=>$tanlanganOmbor?->id]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:30px"><i class="bi bi-x-lg"></i></a>
        <a href="{{ route('ombor.etiketka') }}" class="btn btn-warning btn-sm ms-auto" style="height:30px">
            <i class="bi bi-upc-scan me-1"></i>Etiketka chop etish
        </a>
    </form>
</div>

<div class="om-wrap shadow-sm">
    {{-- Chap: omborlar ro'yxati --}}
    <div class="om-left">
        <div class="om-left-head"><i class="bi bi-building me-1"></i>Omborlar ({{ $omborlar->count() }})</div>
        <div class="om-left-list">
            @foreach($omborlar as $o)
            <a href="{{ route('ombor.index', ['ombor_id'=>$o->id, 'qidiruv'=>request('qidiruv')]) }}"
               class="om-item {{ $tanlanganOmbor && $tanlanganOmbor->id === $o->id ? 'active' : '' }}">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="om-nomi">{{ $o->nomi }}</span>
                    <span class="om-tur-badge om-tur-{{ $o->tur }}">{{ $o->tur }}</span>
                </div>
                <div class="om-sub">{{ $o->filial->nomi ?? '—' }} · {{ $o->tovar_turlari_soni }} xil tovar</div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- O'ng: tanlangan ombor tovarlari --}}
    <div class="om-right">
        @if(!$tanlanganOmbor)
        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
            <i class="bi bi-arrow-left-circle fs-1 mb-2 opacity-50"></i>Ombor tanlanmagan
        </div>
        @else
        <div class="om-right-head d-flex justify-content-between align-items-center">
            <div>
                <span class="fw-bold" style="color:#1d4ed8;font-size:1rem">{{ $tanlanganOmbor->nomi }}</span>
                <span class="om-tur-badge om-tur-{{ $tanlanganOmbor->tur }} ms-2">{{ $tanlanganOmbor->tur }}</span>
                <span class="text-muted small ms-2">{{ $tanlanganOmbor->filial->nomi ?? '' }}</span>
            </div>
            <div class="text-muted small">Jami tan narxda: <strong style="color:#1e3a8a">{{ $n($jamiSumma) }} so'm</strong></div>
        </div>
        <div class="om-right-body">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th class="tl" style="width:36px">#</th>
                        <th class="tl">Tovar</th>
                        <th class="tl" style="width:110px">Guruh</th>
                        <th>Miqdor</th>
                        <th class="tl" style="width:60px">Birlik</th>
                        <th>Tan narx</th>
                        <th>Jami summa</th>
                        <th class="tl" style="width:80px">Holat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qoldiqlar as $i => $oq)
                    @php $t = $oq->tovar; $kam = $t->min_qoldiq > 0 && $oq->miqdor <= $t->min_qoldiq; @endphp
                    <tr>
                        <td class="text-muted text-center" style="font-size:.75rem">{{ $i+1 }}</td>
                        <td class="tl">
                            <a href="{{ route('ombor.tovar', $t) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">{{ $t->nomi }}</a>
                        </td>
                        <td class="tl text-muted" style="font-size:.76rem">{{ $t->guruh->nomi ?? '—' }}</td>
                        <td class="num fw-bold" style="color:{{ $oq->miqdor > 0 ? '#1e293b' : '#94a3b8' }}">{{ $n($oq->miqdor) }}</td>
                        <td class="tl text-muted">{{ $t->birlik }}</td>
                        <td class="num text-muted">{{ $n($t->tan_narx) }}</td>
                        <td class="num" style="color:#15803d">{{ $n($oq->miqdor * $t->tan_narx) }}</td>
                        <td class="tl">
                            <span class="{{ $kam ? 'kam-qoldiq' : 'yetarli' }}">{{ $kam ? "Kam qoldiq" : 'Yetarli' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>Bu omborda tovar yo'q
                    </td></tr>
                    @endforelse
                </tbody>
                @if($qoldiqlar->count())
                <tfoot>
                    <tr>
                        <td colspan="3">Jami {{ $qoldiqlar->count() }} xil tovar</td>
                        <td class="num">{{ $n($qoldiqlar->sum('miqdor')) }}</td>
                        <td></td><td></td>
                        <td class="num">{{ $n($jamiSumma) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
