@extends('layouts.app')
@section('title', 'Kassir smenalari')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item active">Kassir smenalari</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:5; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px; white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:6px 8px; vertical-align:middle; white-space:nowrap; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; }
.bank-wrap { overflow:auto; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-radius:8px; padding:10px 14px; }
.filter-bar .form-select, .filter-bar .form-control { background:#fff; border:1px solid #60a5fa; font-size:.82rem; height:34px; }
.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.b-ochiq { background:#22c55e; color:#fff; } .b-yopiq { background:#64748b; color:#fff; }
.b-yoq { background:#e2e8f0; color:#475569; } .b-kutilmoqda { background:#f59e0b; color:#fff; }
.b-tasdiqlangan { background:#22c55e; color:#fff; } .b-rad_etildi { background:#ef4444; color:#fff; }
.farq-ok { color:#15803d; font-weight:700; } .farq-yomon { color:#b91c1c; font-weight:700; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2" style="color:#1d4ed8"></i>Kassir smenalari</h5>
    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div>
            <label class="form-label small mb-1">Dan</label>
            <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ request('dan_sana') }}">
        </div>
        <div>
            <label class="form-label small mb-1">Gacha</label>
            <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ request('gacha_sana') }}">
        </div>
        @if(Auth::user()->isAdmin())
        <div>
            <label class="form-label small mb-1">Filial</label>
            <select name="filial_id" class="form-select form-select-sm" style="width:170px">
                <option value="">Barchasi</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="form-label small mb-1">Holat</label>
            <select name="holat" class="form-select form-select-sm" style="width:140px">
                <option value="">Barchasi</option>
                <option value="ochiq" {{ request('holat')=='ochiq'?'selected':'' }}>Ochiq</option>
                <option value="yopiq" {{ request('holat')=='yopiq'?'selected':'' }}>Yopiq</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Filtrlash</button>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                <th class="tl">Smena №</th>
                <th class="tl">Filial</th>
                <th class="tl">Kassir</th>
                <th class="tl">Ochilgan</th>
                <th class="tl">Yopilgan</th>
                <th>Dastlabki</th>
                <th>Hisoblangan</th>
                <th>Yakuniy</th>
                <th>Farq</th>
                <th class="tl">Holat</th>
                <th class="tl">Topshirish</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($smenalar as $s)
            <tr>
                <td class="tl">{{ $s->smena_raqami }}</td>
                <td class="tl">{{ $s->filial->nomi ?? '—' }}</td>
                <td class="tl">{{ $s->xodim->ism_familiya ?? '—' }}</td>
                <td class="tl">{{ $s->ochilgan_vaqt->format('d.m.Y H:i') }}</td>
                <td class="tl">{{ $s->yopilgan_vaqt?->format('d.m.Y H:i') ?? '—' }}</td>
                <td class="num">{{ number_format($s->dastlabki_qoldiq,0,'.',' ') }}</td>
                <td class="num">{{ $s->hisoblangan_qoldiq !== null ? number_format($s->hisoblangan_qoldiq,0,'.',' ') : '—' }}</td>
                <td class="num">{{ $s->yakuniy_qoldiq !== null ? number_format($s->yakuniy_qoldiq,0,'.',' ') : '—' }}</td>
                <td class="num {{ $s->farq === null ? '' : (abs($s->farq) < 1 ? 'farq-ok' : 'farq-yomon') }}">
                    {{ $s->farq !== null ? number_format($s->farq,0,'.',' ') : '—' }}
                </td>
                <td class="tl"><span class="badge-modern b-{{ $s->holat }}">{{ $s->holat }}</span></td>
                <td class="tl"><span class="badge-modern b-{{ $s->topshirish_holati }}">{{ $s->topshirish_holati }}</span></td>
                <td class="tl"><a href="{{ route('pos.smena.korish',$s) }}" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-eye"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="12" class="text-center text-muted py-4">Ma'lumot topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-2">{{ $smenalar->links('pagination::bootstrap-5') }}</div>
@endsection
