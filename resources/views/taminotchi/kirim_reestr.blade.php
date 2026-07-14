@extends('layouts.app')
@section('title', "Kirimlar reestri")
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
<li class="breadcrumb-item active">Kirimlar reestri</li>
@endsection

@push('styles')
<style>
.kr-wrap { display:flex; gap:0; border:1px solid #c7d7f8; border-radius:8px; overflow:hidden; height:calc(100vh - 260px); }

/* ── Chap panel: ta'minotchilar ro'yxati ─────────────── */
.kr-left {
    width:280px; min-width:220px; max-width:420px; flex-shrink:0;
    background:#f8fafd; border-right:1px solid #c7d7f8;
    display:flex; flex-direction:column;
}
.kr-left-head {
    background:linear-gradient(180deg,#2563eb,#1d4ed8); padding:10px 12px;
}
.kr-left-head input {
    background:#fff; border:1px solid #93c5fd; color:#1e3a8a; font-size:.8rem;
    height:32px; width:100%; border-radius:5px; padding:4px 10px;
}
.kr-left-list { flex:1; overflow-y:auto; }
.kr-item {
    display:flex; justify-content:space-between; align-items:center;
    padding:9px 12px; border-bottom:1px solid #e8ecf5; text-decoration:none;
    color:#1e293b; font-size:.83rem; transition:background .1s;
}
.kr-item:hover { background:#e8f0fe; color:#1e293b; }
.kr-item.active { background:#2563eb; color:#fff; font-weight:600; }
.kr-item.active .kr-count { background:rgba(255,255,255,.25); color:#fff; }
.kr-count { background:#dbeafe; color:#1d4ed8; font-size:.68rem; font-weight:700; padding:1px 7px; border-radius:10px; }

/* ── O'ng panel ───────────────────────────────────────── */
.kr-right { flex:1; display:flex; flex-direction:column; overflow:hidden; background:#fff; }
.kr-right-head {
    background:linear-gradient(90deg,#eef3ff,#e8f0fe); border-bottom:1px solid #c7d7f8;
    padding:10px 16px; display:flex; justify-content:space-between; align-items:center;
}
.kr-right-body { flex:1; overflow-y:auto; }

.bank-table { border-collapse:collapse; font-size:.83rem; width:100%; }
.bank-table thead th {
    position:sticky; top:0; z-index:5;
    background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; font-weight:700;
    font-size:.7rem; letter-spacing:.05em; text-transform:uppercase; padding:8px 10px;
    border-right:1px solid rgba(255,255,255,.15); white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { border-bottom:1px solid #e2e8f4; }
.bank-table tbody tr:hover { background:#eff6ff; }
.bank-table tbody tr:nth-child(even) { background:#f5f8fd; }
.bank-table tbody tr:nth-child(odd)  { background:#fff; }
.bank-table tbody td { padding:6px 10px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }

.bank-table tfoot td {
    position:sticky; bottom:0;
    background:linear-gradient(90deg,#1e3a8a,#1e40af); color:#fff; font-weight:700;
    font-size:.8rem; padding:7px 10px; border-top:2px solid #60a5fa;
}

.status-toliq    { background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }
.status-qisman   { background:#fef9c3; color:#a16207; border:1px solid #fde047; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }
.status-kutilmoqda { background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; border-radius:3px; padding:1px 6px; font-size:.68rem; font-weight:700; }
.badge-uzs { background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:3px; padding:1px 5px; font-size:.64rem; font-weight:700; }
.badge-usd { background:#fef9c3; color:#a16207; border:1px solid #fde047; border-radius:3px; padding:1px 5px; font-size:.64rem; font-weight:700; }

.kr-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#94a3b8; }

/* ── Filtr paneli (tab tanlashdan tashqarida, doim ko'rinadi) ── */
.kr-filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px 8px 0 0; padding:8px 12px; }
.kr-filter-bar .form-control, .kr-filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
.kr-tabs { border-bottom:none !important; }
.kr-tabs .nav-link { font-weight:600; color:#1e3a8a; }
.kr-tabs .nav-link.active { background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; border-color:#1d4ed8; }
</style>
@endpush

@section('content')
@php
    $n  = fn($v) => number_format((float)$v,0,'.',' ');
    $nu = fn($v) => number_format((float)$v,2,'.',' ');
    $toValyuta = fn($uzs, $kurs) => $kurs > 0 ? (float)$uzs / (float)$kurs : 0;
    $isUsd = $tanlangan && $tanlangan->asosiy_valyuta === 'USD';
@endphp

<div class="d-flex align-items-center gap-2 mb-2">
    <i class="bi bi-journal-text text-info" style="font-size:1.1rem"></i>
    <span class="fw-bold" style="color:#1e3a8a;font-size:.95rem">Kirimlar reestri</span>
    <span class="badge bg-secondary">{{ $taminotchilar->count() }} ta'minotchi</span>
    @if($isUsd)
    <small class="text-muted ms-2"><i class="bi bi-currency-dollar text-warning"></i> 1 USD = {{ $n($usdKurs) }} so'm</small>
    @endif
</div>

<ul class="nav nav-tabs kr-tabs mb-0">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'taminotchi' ? 'active' : '' }}"
           href="{{ route('taminotchi.kirim_reestr', ['tab'=>'taminotchi','sana_dan'=>$sanaDan,'sana_gacha'=>$sanaGacha,'qidiruv'=>request('qidiruv'),'taminotchi_id'=>$tanlangan?->id]) }}">
            <i class="bi bi-people me-1"></i>Ta'minotchi bo'yicha
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'barchasi' ? 'active' : '' }}"
           href="{{ route('taminotchi.kirim_reestr', ['tab'=>'barchasi','sana_dan'=>$sanaDan,'sana_gacha'=>$sanaGacha,'qidiruv'=>request('qidiruv')]) }}">
            <i class="bi bi-list-columns-reverse me-1"></i>Barcha kirimlar
        </a>
    </li>
</ul>

{{-- ── Umumiy filtr paneli — tab/ta'minotchi tanlanishidan qat'i nazar doim ko'rinadi ── --}}
<div class="kr-filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end flex-wrap gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        @if($tab === 'taminotchi' && $tanlangan)
        <input type="hidden" name="taminotchi_id" value="{{ $tanlangan->id }}">
        @endif
        <div>
            <label class="form-label small mb-1 text-dark">Sana dan</label>
            <input type="date" name="sana_dan" class="form-control" value="{{ $sanaDan }}">
        </div>
        <div>
            <label class="form-label small mb-1 text-dark">Sana gacha</label>
            <input type="date" name="sana_gacha" class="form-control" value="{{ $sanaGacha }}">
        </div>
        <div>
            <label class="form-label small mb-1 text-dark">Ta'minotchi qidirish</label>
            <input type="search" name="qidiruv" class="form-control" placeholder="Nomi..." value="{{ request('qidiruv') }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-filter me-1"></i>Filtrlash</button>
        @if($sanaDan || $sanaGacha || request('qidiruv'))
        <a href="{{ route('taminotchi.kirim_reestr', ['tab'=>$tab,'taminotchi_id'=>$tanlangan?->id]) }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px">
            <i class="bi bi-x-lg"></i>
        </a>
        @endif
    </form>
</div>

{{-- ─────────────────────────────────────────────────────────────
     Tab 1: Ta'minotchi bo'yicha (chap: ro'yxat, o'ng: tanlangan kirimlari)
────────────────────────────────────────────────────────────── --}}
@if($tab === 'taminotchi')
<div class="kr-wrap shadow-sm" style="border-radius:0 0 8px 8px">
    <div class="kr-left">
        <div class="kr-left-head">
            <form method="GET">
                <input type="hidden" name="tab" value="taminotchi">
                <input type="hidden" name="sana_dan" value="{{ $sanaDan }}">
                <input type="hidden" name="sana_gacha" value="{{ $sanaGacha }}">
                <input type="search" name="qidiruv" placeholder="Ta'minotchi qidirish..." value="{{ request('qidiruv') }}"
                       onkeyup="if(event.key==='Enter') this.form.submit()">
                @if($tanlangan)<input type="hidden" name="taminotchi_id" value="{{ $tanlangan->id }}">@endif
            </form>
        </div>
        <div class="kr-left-list">
            @forelse($taminotchilar as $t)
            <a href="{{ route('taminotchi.kirim_reestr', ['tab'=>'taminotchi','taminotchi_id'=>$t->id, 'qidiruv'=>request('qidiruv'), 'sana_dan'=>$sanaDan, 'sana_gacha'=>$sanaGacha]) }}"
               class="kr-item {{ $tanlangan && $tanlangan->id === $t->id ? 'active' : '' }}">
                <span>{{ $t->nomi }}</span>
                <span class="kr-count">{{ $t->kirimlar_count }}</span>
            </a>
            @empty
            <div class="text-center text-muted py-4 small">Ta'minotchi topilmadi</div>
            @endforelse
        </div>
    </div>

    <div class="kr-right">
        @if(!$tanlangan)
        <div class="kr-empty">
            <i class="bi bi-arrow-left-circle fs-1 mb-2 opacity-50"></i>
            <div>Chap tomondan ta'minotchini tanlang</div>
        </div>
        @else
        <div class="kr-right-head">
            <div>
                <a href="{{ route('taminotchi.show', $tanlangan) }}" class="fw-bold text-decoration-none" style="color:#1d4ed8;font-size:1rem">
                    {{ $tanlangan->nomi }}
                </a>
                <span class="{{ $isUsd ? 'badge-usd' : 'badge-uzs' }} ms-2">{{ $tanlangan->asosiy_valyuta }}</span>
                <span class="text-muted small ms-2">{{ $kirimlar->count() }} ta kirim</span>
            </div>
            <a href="{{ route('taminotchi.kirim.create', $tanlangan) }}" class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Yangi kirim
            </a>
        </div>
        <div class="kr-right-body">
            <table class="bank-table">
                <thead>
                    <tr>
                        <th class="tl" style="width:36px">#</th>
                        <th class="tl">Sana</th>
                        <th class="tl">Hujjat №</th>
                        <th>Tovar soni</th>
                        <th>Valyuta</th>
                        <th>Summa (so'm)</th>
                        <th>Summa (valyutada)</th>
                        <th>To'langan</th>
                        <th>Qoldiq</th>
                        <th class="tl">Holat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kirimlar as $i => $k)
                    <tr>
                        <td class="text-muted text-center" style="font-size:.75rem">{{ $i+1 }}</td>
                        <td class="tl">
                            <a href="{{ route('taminotchi.kirim.edit', [$tanlangan, $k]) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                                {{ $k->kirim_sana->format('d.m.Y') }}
                            </a>
                        </td>
                        <td class="tl text-muted">{{ $k->hujjat_raqam ?? '—' }}</td>
                        <td class="num text-center">{{ $k->qatorlar_count }}</td>
                        <td class="text-center"><span class="{{ $isUsd ? 'badge-usd' : 'badge-uzs' }}">{{ $tanlangan->asosiy_valyuta }}</span></td>
                        <td class="num" style="color:#1e293b">{{ $n($k->jami_summa) }}</td>
                        <td class="num" style="color:#1d4ed8">
                            {{ $isUsd ? '$'.$nu($toValyuta($k->jami_summa, $usdKurs)) : $n($k->jami_summa) }}
                        </td>
                        <td class="num" style="color:#15803d">{{ $n($k->tolangan) }}</td>
                        <td class="num" style="color:{{ $k->qoldiq > 0 ? '#dc2626' : '#94a3b8' }}; font-weight:700">{{ $n($k->qoldiq) }}</td>
                        <td class="tl">
                            <span class="status-{{ $k->holat }}">
                                {{ $k->holat === 'toliq' ? "To'liq" : ($k->holat === 'qisman' ? 'Qisman' : 'Kutilmoqda') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>Kirimlar yo'q
                    </td></tr>
                    @endforelse
                </tbody>
                @if($kirimlar->count())
                <tfoot>
                    <tr>
                        <td colspan="3">Jami {{ $kirimlar->count() }} ta kirim</td>
                        <td class="num">{{ $kirimlar->sum('qatorlar_count') }}</td>
                        <td></td>
                        <td class="num">{{ $n($kirimlar->sum('jami_summa')) }}</td>
                        <td class="num">{{ $isUsd ? '$'.$nu($toValyuta($kirimlar->sum('jami_summa'), $usdKurs)) : $n($kirimlar->sum('jami_summa')) }}</td>
                        <td class="num">{{ $n($kirimlar->sum('tolangan')) }}</td>
                        <td class="num" style="color:{{ $kirimlar->sum('qoldiq') > 0 ? '#fca5a5' : '#e0eaff' }}">{{ $n($kirimlar->sum('qoldiq')) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ─────────────────────────────────────────────────────────────
     Tab 2: Barcha kirimlar — bitta yassi (flat) reestr jadvali
────────────────────────────────────────────────────────────── --}}
@if($tab === 'barchasi')
<div class="bank-wrap shadow-sm" style="overflow:auto; max-height:calc(100vh - 320px); border:1px solid #93c5fd; border-radius:0 0 8px 8px">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl" style="width:36px">#</th>
                <th class="tl">Sana</th>
                <th class="tl">Ta'minotchi</th>
                <th class="tl">Hujjat №</th>
                <th>Tovar soni</th>
                <th>Valyuta</th>
                <th>Jami summa (so'm)</th>
                <th>To'langan</th>
                <th>Qoldiq</th>
                <th class="tl">Holat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barchaKirimlar as $i => $k)
            <tr>
                <td class="text-muted text-center" style="font-size:.75rem">{{ $barchaKirimlar->firstItem() + $i }}</td>
                <td class="tl">
                    <a href="{{ route('taminotchi.kirim.edit', [$k->taminotchi, $k]) }}" class="text-decoration-none fw-semibold" style="color:#1d4ed8">
                        {{ $k->kirim_sana->format('d.m.Y') }}
                    </a>
                </td>
                <td class="tl">
                    <a href="{{ route('taminotchi.kirim_reestr', ['tab'=>'taminotchi','taminotchi_id'=>$k->taminotchi_id]) }}" class="text-decoration-none" style="color:#1e293b">
                        {{ $k->taminotchi->nomi ?? '—' }}
                    </a>
                </td>
                <td class="tl text-muted">{{ $k->hujjat_raqam ?? '—' }}</td>
                <td class="num text-center">{{ $k->qatorlar_count }}</td>
                <td class="text-center">
                    <span class="{{ ($k->taminotchi->asosiy_valyuta ?? 'UZS') === 'USD' ? 'badge-usd' : 'badge-uzs' }}">{{ $k->taminotchi->asosiy_valyuta ?? 'UZS' }}</span>
                </td>
                <td class="num" style="color:#1e293b">{{ $n($k->jami_summa) }}</td>
                <td class="num" style="color:#15803d">{{ $n($k->tolangan) }}</td>
                <td class="num" style="color:{{ $k->qoldiq > 0 ? '#dc2626' : '#94a3b8' }}; font-weight:700">{{ $n($k->qoldiq) }}</td>
                <td class="tl">
                    <span class="status-{{ $k->holat }}">
                        {{ $k->holat === 'toliq' ? "To'liq" : ($k->holat === 'qisman' ? 'Qisman' : 'Kutilmoqda') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>Kirimlar topilmadi
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($barchaKirimlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $barchaKirimlar->firstItem() }}–{{ $barchaKirimlar->lastItem() }} / {{ $barchaKirimlar->total() }} ta</small>
    {{ $barchaKirimlar->links('pagination::bootstrap-5') }}
</div>
@endif
@endif
@endsection
