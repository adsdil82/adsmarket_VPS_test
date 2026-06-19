@extends('layouts.app')
@section('title', 'Hisobot konstruktori')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Konstruktor</li>
@endsection

@push('styles')
<style>
.modul-card { border: 2px solid #dee2e6; border-radius: 10px; padding: 12px 16px;
    cursor: pointer; transition: all .2s; }
.modul-card:hover { border-color: #6366f1; background: #f8f7ff; }
.modul-card.selected { border-color: #6366f1; background: #ede9fe; }
.modul-card .mc-icon { font-size: 1.5rem; }
.col-check { display: flex; gap: 8px; align-items: center; padding: 4px 8px;
    border-radius: 6px; cursor: pointer; transition: background .15s; }
.col-check:hover { background: #f3f4f6; }
.col-check input { cursor: pointer; }
.shart-row { background: #f8f9fa; border-radius: 8px; padding: 10px 14px; margin-bottom: 6px; }
.result-table th { font-size: .72rem; white-space: nowrap; }
.result-table td { font-size: .8rem; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        <i class="bi bi-tools me-1" style="color:#6366f1"></i> Hisobot konstruktori
    </h5>
    <a href="{{ route('hisobotlar.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
</div>

<form method="POST" action="{{ route('hisobotlar.konstruktor.hisobot') }}" id="konstruktor-form">
@csrf

<div class="row g-3">

    {{-- ── CHAP: Konstruktor sozlamalari ─────────────────────────── --}}
    <div class="col-lg-4">

        {{-- 1. Modul tanlash --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 fw-bold small">
                <span class="badge bg-primary me-1">1</span> Modul tanlang
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2">
                    @foreach($modullar as $key => $mod)
                    <label class="modul-card {{ $modul === $key ? 'selected' : '' }}"
                           onclick="modulTanla('{{ $key }}', this)">
                        <input type="radio" name="modul" value="{{ $key }}"
                               {{ $modul === $key ? 'checked' : '' }}
                               class="d-none">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi {{ $mod['icon'] }} mc-icon" style="color:#6366f1"></i>
                            <div>
                                <div class="fw-bold" style="font-size:.88rem">{{ $mod['nomi'] }}</div>
                                <div class="text-muted" style="font-size:.75rem">{{ $mod['sana_tur'] }} sanasi bo'yicha</div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 2. Davr --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 fw-bold small">
                <span class="badge bg-primary me-1">2</span> Davr & Filial
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small mb-1">Dan</label>
                        <input type="date" name="dan_sana" class="form-control form-control-sm"
                               value="{{ $danSana ?? now()->startOfMonth()->toDateString() }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-1">Gacha</label>
                        <input type="date" name="gacha_sana" class="form-control form-control-sm"
                               value="{{ $gachaSana ?? now()->toDateString() }}">
                    </div>
                    @if(Auth::user()->isAdmin())
                    <div class="col-12">
                        <label class="form-label small mb-1">Filial</label>
                        <select name="filial_id" class="form-select form-select-sm">
                            <option value="">Barcha filiallar</option>
                            @foreach($filiallar as $f)
                            <option value="{{ $f->id }}" {{ request('filial_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->nomi }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                {{-- Tezkor davr --}}
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @php
                    $tezkorlar = [
                        ['Bugun',     now()->toDateString(),            now()->toDateString()],
                        ['Bu oy',     now()->startOfMonth()->toDateString(), now()->toDateString()],
                        ['O\'tgan oy',now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
                        ['Bu yil',   now()->startOfYear()->toDateString(), now()->toDateString()],
                    ];
                    @endphp
                    @foreach($tezkorlar as [$label,$dan,$gacha])
                    <button type="button" class="btn btn-xs btn-outline-secondary"
                            style="font-size:.7rem;padding:2px 8px"
                            onclick="davrTanla('{{ $dan }}','{{ $gacha }}')">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. Shartlar --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 fw-bold small">
                <span class="badge bg-primary me-1">3</span> Shartlar (ixtiyoriy)
            </div>
            <div class="card-body" id="shartlar-body">
                {{-- JS orqali to'ldiriladi --}}
            </div>
        </div>

        {{-- 4. Ko'rinish --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 fw-bold small">
                <span class="badge bg-primary me-1">4</span> Ko'rinish
            </div>
            <div class="card-body">
                <div class="d-flex gap-3">
                    <label class="d-flex align-items-center gap-2">
                        <input type="radio" name="korinish" value="jadval" checked> Jadval
                    </label>
                    <label class="d-flex align-items-center gap-2">
                        <input type="radio" name="korinish" value="kichik"> Ixcham
                    </label>
                </div>
            </div>
        </div>

        {{-- Tugmalar --}}
        <div class="d-flex flex-column gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-play-fill me-1"></i> Hisobotni shakllantirish
            </button>
            <button type="submit" formaction="{{ route('hisobotlar.konstruktor.excel') }}"
                    name="format" value="excel" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Excel yuklab olish
            </button>
        </div>
    </div>

    {{-- ── O'NG: Ustunlar va natija ────────────────────────────────── --}}
    <div class="col-lg-8">

        {{-- Ustunlar tanlash --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 fw-bold small d-flex justify-content-between align-items-center">
                <span><span class="badge bg-primary me-1">5</span> Ustunlar tanlang</span>
                <div class="d-flex gap-2">
                    <button type="button" onclick="barchasini(true)" class="btn btn-xs btn-outline-success" style="font-size:.72rem;padding:2px 8px">Barchasi</button>
                    <button type="button" onclick="barchasini(false)" class="btn btn-xs btn-outline-danger" style="font-size:.72rem;padding:2px 8px">Tozalash</button>
                </div>
            </div>
            <div class="card-body">
                <div id="ustunlar-body" class="row g-1">
                    {{-- JS orqali --}}
                </div>
            </div>
        </div>

        {{-- Natija --}}
        @if($natija !== null)
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 small">
                    <i class="bi bi-table me-1"></i> Natija
                    <span class="badge bg-primary ms-1">{{ $natija['soni'] }} qator</span>
                </h6>
                <button type="submit" form="konstruktor-form"
                        formaction="{{ route('hisobotlar.konstruktor.excel') }}"
                        name="format" value="excel"
                        class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </button>
            </div>
            <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                <table class="table table-hover table-sm mb-0 result-table">
                    <thead class="table-light sticky-top">
                        <tr>
                            @if(!empty($natija['rows']))
                                @foreach(array_keys($natija['rows'][0]) as $col)
                                @if(empty($ustunlar) || in_array($col,$ustunlar))
                                <th>{{ $modullar[$modul]['ustunlar'][$col] ?? $col }}</th>
                                @endif
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($natija['rows'] as $row)
                        <tr>
                            @foreach($row as $col => $val)
                            @if(empty($ustunlar) || in_array($col,$ustunlar))
                            <td @if(is_numeric($val) && $val > 0) class="text-end" @endif>
                                @if(is_numeric($val) && $val > 100)
                                    {{ number_format((float)$val,0,'.',' ') }}
                                @else
                                    {{ $val }}
                                @endif
                            </td>
                            @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($natija['soni'] >= 5000)
            <div class="card-footer py-1">
                <small class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>
                    Maksimal 5000 qator ko'rsatildi. To'liq ma'lumot uchun Excel yuklab oling.</small>
            </div>
            @endif
        </div>
        @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-tools fs-2 d-block mb-2" style="color:#6366f1;opacity:.4"></i>
                <div>Chap paneldan modul, davr va ustunlarni tanlang,</div>
                <div>so'ng <strong>"Hisobotni shakllantirish"</strong> tugmasini bosing.</div>
            </div>
        </div>
        @endif
    </div>
</div>
</form>

@endsection

@push('scripts')
<script>
// Modullar ma'lumotlari
var MODULLAR = @json($modullar);
var JORIY_MODUL = '{{ $modul ?? "kreditlar" }}';
var TANLANGAN_USTUNLAR = @json($ustunlar ?? []);

function modulTanla(key, el) {
    document.querySelectorAll('.modul-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    JORIY_MODUL = key;
    document.querySelector('input[name=modul][value="'+key+'"]').checked = true;
    ustunlarKorsatish(key);
    shartlarKorsatish(key);
}

function ustunlarKorsatish(modulKey) {
    var mod = MODULLAR[modulKey] || {};
    var ustunlar = mod.ustunlar || {};
    var html = '';
    for (var k in ustunlar) {
        var checked = TANLANGAN_USTUNLAR.length === 0 || TANLANGAN_USTUNLAR.includes(k);
        html += '<div class="col-6 col-sm-4">';
        html += '<label class="col-check">';
        html += '<input type="checkbox" name="ustunlar[]" value="'+k+'" '+( checked?'checked':'')+' style="width:15px;height:15px">';
        html += '<span style="font-size:.8rem">'+ustunlar[k]+'</span>';
        html += '</label></div>';
    }
    document.getElementById('ustunlar-body').innerHTML = html;
}

function shartlarKorsatish(modulKey) {
    var mod = MODULLAR[modulKey] || {};
    var shartlar = mod.shartlar || {};
    var html = '';
    for (var k in shartlar) {
        var s = shartlar[k];
        html += '<div class="shart-row">';
        html += '<label class="form-label small mb-1 fw-medium">'+s.nomi+'</label>';
        if (s.tur === 'select') {
            html += '<select name="shartlar['+k+']" class="form-select form-select-sm">';
            html += '<option value="">— Hammasi —</option>';
            (s.qiymatlar||[]).forEach(function(v) {
                html += '<option value="'+v+'">'+v+'</option>';
            });
            html += '</select>';
        } else if (s.tur === 'number') {
            html += '<input type="number" name="shartlar['+k+']" class="form-control form-control-sm" placeholder="Minimal qiymat...">';
        } else {
            html += '<input type="text" name="shartlar['+k+']" class="form-control form-control-sm">';
        }
        html += '</div>';
    }
    document.getElementById('shartlar-body').innerHTML = html || '<p class="text-muted small mb-0">Bu modul uchun shartlar yo\'q</p>';
}

function barchasini(belgi) {
    document.querySelectorAll('#ustunlar-body input[type=checkbox]').forEach(cb => cb.checked = belgi);
}

function davrTanla(dan, gacha) {
    document.querySelector('input[name=dan_sana]').value = dan;
    document.querySelector('input[name=gacha_sana]').value = gacha;
}

document.addEventListener('DOMContentLoaded', function() {
    ustunlarKorsatish(JORIY_MODUL);
    shartlarKorsatish(JORIY_MODUL);
});
</script>
@endpush
