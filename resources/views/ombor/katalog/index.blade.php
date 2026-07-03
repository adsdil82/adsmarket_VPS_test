@extends('layouts.app')
@section('title','Tovar katalogi')
@section('breadcrumb')
<li class="breadcrumb-item active">Tovar katalogi</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; padding:7px 8px;
    white-space:nowrap; text-align:right; position:relative;
}
.bank-table thead th.tl { text-align:left; }
.bank-table thead th.tl.sticky-col { position:sticky; left:0; z-index:7; min-width:180px; }
.bank-table tbody td.sticky-col { position:sticky; left:0; z-index:2; background:inherit; border-right:2px solid #93c5fd; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody tr.row-kam-qoldiq td { background:#fef3c7 !important; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }

.bank-wrap { overflow:auto; height:calc(100vh - 150px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
@media (max-width: 768px) { .bank-wrap { height:calc(100vh - 170px); } }

.badge-modern { font-size:.62rem; font-weight:800; padding:2px 7px; border-radius:4px; letter-spacing:.03em; }
.b-faol { background:#22c55e; color:#fff; }
.b-nofaol { background:#64748b; color:#fff; }

.col-resizer { position:absolute; right:0; top:0; bottom:0; width:5px; cursor:col-resize; background:transparent; z-index:2; }
.col-resizer:hover, .col-resizer.resizing { background:rgba(255,255,255,.4); }

.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 14px; }
.filter-bar .form-control, .filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
.tk-stat { text-align:center; padding:0 10px; border-right:1px solid #93c5fd; }
.tk-stat:last-child { border-right:none; }
.tk-stat .lbl { font-size:.6rem; text-transform:uppercase; letter-spacing:.03em; color:#3b5fc0; font-weight:700; }
.tk-stat .val { font-size:.9rem; font-weight:800; color:#1e293b; }
</style>
@endpush

@section('content')

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-box-seam" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Tovar katalogi</span>
        </div>
        <div class="d-flex align-items-center">
            <div class="tk-stat">
                <div class="lbl">Jami</div>
                <div class="val text-primary">{{ $tovarlar->total() }}</div>
            </div>
            <div class="tk-stat">
                <div class="lbl">Omborda bor</div>
                <div class="val text-success">{{ \App\Models\TovarKatalog::where('qoldiq','>',0)->count() }}</div>
            </div>
            <div class="tk-stat">
                <div class="lbl">Kam qoldiq</div>
                <div class="val text-danger">{{ \App\Models\TovarKatalog::whereColumn('qoldiq','<=','min_qoldiq')->where('min_qoldiq','>',0)->count() }}</div>
            </div>
            <div class="tk-stat">
                <div class="lbl">Guruhlar</div>
                <div class="val text-warning">{{ $guruhlar->count() }}</div>
            </div>
        </div>
        <div style="width:200px">
            <input type="search" name="qidiruv" class="form-control" placeholder="Nomi yoki shtrix-kod..." value="{{ request('qidiruv') }}">
        </div>
        <div>
            <select name="guruh_id" class="form-select" style="width:170px">
                <option value="">Barcha guruhlar</option>
                @foreach($guruhlar as $g)
                    <option value="{{ $g->id }}" {{ request('guruh_id')==$g->id?'selected':'' }}>{{ $g->nomi }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="holat" class="form-select" style="width:130px">
                <option value="">Barcha holat</option>
                <option value="faol" {{ request('holat')==='faol'?'selected':'' }}>Faol</option>
                <option value="nofaol" {{ request('holat')==='nofaol'?'selected':'' }}>Nofaol</option>
            </select>
        </div>
        <div class="d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm px-3" style="height:32px"><i class="bi bi-search me-1"></i>Qidirish</button>
            <a href="{{ route('katalog.index') }}" class="btn btn-outline-secondary btn-sm px-2" style="height:32px"><i class="bi bi-x-lg"></i></a>
        </div>
        <div class="d-flex gap-1 ms-auto">
            <a href="{{ route('katalog.create') }}" class="btn btn-warning btn-sm fw-bold" onclick="return litsenziyaTekshir('tovar', 'Tovar qo\'shish')">
                <i class="bi bi-plus-lg me-1"></i>Yangi tovar
            </a>
            <a href="{{ route('tovar-guruhlar.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-tags me-1"></i>Guruhlar
            </a>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
    <table class="bank-table" id="tovar-katalog-table">
        <thead>
            <tr>
                <th class="tl sticky-col">Tovar nomi</th>
                <th class="tl">Guruh</th>
                <th class="tl">Shtrix-kod</th>
                <th>Tan narx</th>
                <th>Naqd/POS narx</th>
                <th>Nasiya narx</th>
                <th>Qoldiq</th>
                <th>Holat</th>
                <th style="width:70px"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tovarlar as $t)
            <tr class="{{ $t->kam_qoldiq ? 'row-kam-qoldiq' : '' }}">
                <td class="tl sticky-col">
                    <span class="fw-semibold">{{ $t->nomi }}</span>
                    <span class="text-muted small">{{ $t->birlik }}</span>
                </td>
                <td class="tl"><span class="badge-modern" style="background:#e0e7ff;color:#3730a3">{{ $t->guruh?->nomi ?? '—' }}</span></td>
                <td class="tl text-muted font-monospace">{{ $t->barkod ?? '—' }}</td>
                <td class="num text-muted">{{ number_format($t->tan_narx,0,'.',' ') }}</td>
                <td class="num fw-bold">{{ number_format($t->sotish_narx,0,'.',' ') }}</td>
                <td class="num fw-bold" style="color:#1d4ed8">{{ number_format($t->nasiya_narx,0,'.',' ') }}</td>
                <td class="text-center">
                    <span class="badge-modern" style="background:{{ $t->qoldiq>0?'#22c55e':'#ef4444' }};color:#fff">
                        {{ number_format($t->qoldiq,0,'.',' ') }}
                    </span>
                    @if($t->kam_qoldiq)
                        <i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Kam qoldiq!"></i>
                    @endif
                </td>
                <td class="text-center"><span class="badge-modern b-{{ $t->holat }}">{{ $t->holat }}</span></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="{{ route('katalog.edit', $t) }}" class="btn btn-sm btn-outline-primary py-0 px-1">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('katalog.destroy',$t) }}" class="d-inline"
                              onsubmit="return confirm('«{{$t->nomi}}» o\'chirilsinmi?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger py-0 px-1" {{ $t->qoldiq>0?'disabled':'' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted py-5">
                <i class="bi bi-box fs-3 d-block mb-2 opacity-25"></i>Tovarlar topilmadi
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tovarlar->hasPages())
<div class="d-flex justify-content-between align-items-center mt-2">
    <small class="text-muted">{{ $tovarlar->firstItem() }}–{{ $tovarlar->lastItem() }} / {{ $tovarlar->total() }} ta</small>
    {{ $tovarlar->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection

@push('scripts')
<script>
(function() {
    document.querySelectorAll('#tovar-katalog-table thead th').forEach(th => {
        const r = document.createElement('div');
        r.className = 'col-resizer';
        th.appendChild(r);
        let sx, sw;
        r.addEventListener('mousedown', e => {
            e.preventDefault(); sx = e.clientX; sw = th.offsetWidth;
            r.classList.add('resizing');
            const mm = ev => { th.style.width = th.style.minWidth = Math.max(40, sw + ev.clientX - sx) + 'px'; };
            const mu = () => { r.classList.remove('resizing'); document.removeEventListener('mousemove', mm); document.removeEventListener('mouseup', mu); };
            document.addEventListener('mousemove', mm);
            document.addEventListener('mouseup', mu);
        });
    });
})();
</script>
@endpush
