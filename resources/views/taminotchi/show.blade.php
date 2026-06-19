@extends('layouts.app')
@section('title', $taminotchi->nomi)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item active">{{ $taminotchi->nomi }}</li>
@endsection

@push('styles')
<style>
.balans-card { border-radius:12px; padding:16px 20px; color:#fff; }
.balans-card .sum { font-size:1.4rem; font-weight:800; }
.balans-card .lbl { font-size:.78rem; opacity:.85; }
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-1">
            <i class="bi bi-truck me-2 text-warning"></i>{{ $taminotchi->nomi }}
        </h5>
        <div class="d-flex gap-3 text-muted small">
            @if($taminotchi->telefon)
            <span><i class="bi bi-telephone me-1"></i>{{ $taminotchi->telefon }}</span>
            @endif
            @if($taminotchi->inn)
            <span><i class="bi bi-building me-1"></i>INN: {{ $taminotchi->inn }}</span>
            @endif
            @if($taminotchi->manzil)
            <span><i class="bi bi-geo-alt me-1"></i>{{ $taminotchi->manzil }}</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(Auth::user()->isOmborchi())
        <a href="{{ route('taminotchi.kirim.create', $taminotchi) }}" class="btn btn-sm btn-success">
            <i class="bi bi-box-arrow-in-down me-1"></i>Kirim kiritish
        </a>
        @endif
        <a href="{{ route('taminotchi.akt_sverka', $taminotchi) }}" class="btn btn-sm btn-outline-info">
            <i class="bi bi-file-earmark-text me-1"></i>Akt sverka
        </a>
        @if(Auth::user()->isMenejerYoki())
        <a href="{{ route('taminotchi.edit', $taminotchi) }}" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil me-1"></i>Tahrirlash
        </a>
        @endif
    </div>
</div>

{{-- Balans kartalar --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="balans-card" style="background:linear-gradient(135deg,#1e3a5f,#2563eb)">
            <div class="lbl">Jami yetkazib berdi</div>
            <div class="sum">{{ number_format($balans['jami_kirim'],0,'.',' ') }}</div>
            <div class="lbl">so'm</div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="balans-card" style="background:linear-gradient(135deg,#14532d,#16a34a)">
            <div class="lbl">Jami to'landi</div>
            <div class="sum">{{ number_format($balans['jami_tolov'],0,'.',' ') }}</div>
            <div class="lbl">so'm</div>
        </div>
    </div>
    <div class="col-sm-4">
        @php $q = $balans['qoldiq']; $isQarazdor = $q > 0; @endphp
        <div class="balans-card" style="background:linear-gradient(135deg,{{ $isQarazdor ? '#7f1d1d,#dc2626' : '#14532d,#16a34a' }})">
            <div class="lbl">{{ $isQarazdor ? 'Biz qarazdormiz' : ($q < 0 ? "Ta'minotchi qarazdor" : 'Balanslangan') }}</div>
            <div class="sum">{{ number_format(abs($q),0,'.',' ') }}</div>
            <div class="lbl">so'm</div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label small mb-1">Dan</label>
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ $danSana }}">
            </div>
            <div class="col-sm-3">
                <label class="form-label small mb-1">Gacha</label>
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ $gachaSana }}">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-funnel me-1"></i>Filtrlash
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    {{-- Kirimlar jadvali --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 small fw-bold">
                    <i class="bi bi-box-arrow-in-down me-1 text-success"></i>Kirimlar
                </h6>
                <span class="badge bg-secondary">{{ $kirimlar->count() }} ta</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sana</th>
                            <th>Hujjat</th>
                            <th class="text-end">Jami</th>
                            <th class="text-end">To'langan</th>
                            <th class="text-end">Qoldiq</th>
                            <th>Holat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kirimlar as $k)
                        <tr>
                            <td class="small">{{ $k->kirim_sana->format('d.m.Y') }}</td>
                            <td class="small text-muted">{{ $k->hujjat_raqam ?? '—' }}</td>
                            <td class="text-end small">{{ number_format($k->jami_summa,0,'.',' ') }}</td>
                            <td class="text-end small text-success">{{ number_format($k->tolangan,0,'.',' ') }}</td>
                            <td class="text-end small {{ $k->qoldiq > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($k->qoldiq,0,'.',' ') }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $k->holat_rangi }}" style="font-size:.65rem">
                                    {{ match($k->holat) { 'toliq'=>'To\'liq','qisman'=>'Qisman',default=>"To'lanmagan" } }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3 small">Bu davrda kirim yo'q</td></tr>
                        @endforelse
                    </tbody>
                    @if($kirimlar->count())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2">Jami:</td>
                            <td class="text-end">{{ number_format($kirimlar->sum('jami_summa'),0,'.',' ') }}</td>
                            <td class="text-end text-success">{{ number_format($kirimlar->sum('tolangan'),0,'.',' ') }}</td>
                            <td class="text-end text-danger">{{ number_format($kirimlar->sum('qoldiq'),0,'.',' ') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- To'lovlar va to'lov kiritish --}}
    <div class="col-lg-5">
        {{-- To'lov kiritish formasi --}}
        @if(Auth::user()->isTaminotKira())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2" style="background:linear-gradient(135deg,#14532d,#16a34a)">
                <h6 class="mb-0 small fw-bold text-white">
                    <i class="bi bi-cash-coin me-1"></i>To'lov kiritish
                </h6>
            </div>
            <div class="card-body">
@php
$soz = \App\Models\Sozlama::barchasi();
$kurslar = [
    'UZS' => ['sotish'=>1, 'olish'=>1, 'nomi'=>"UZS — So'm", 'belgi'=>"so'm"],
    'USD' => ['sotish'=>(float)($soz['usd_sotish_kurs']??12700), 'olish'=>(float)($soz['usd_olish_kurs']??12600), 'nomi'=>'USD — Dollar','belgi'=>'$'],
    'EUR' => ['sotish'=>(float)($soz['eur_sotish_kurs']??13800), 'olish'=>(float)($soz['eur_olish_kurs']??13700), 'nomi'=>'EUR — Yevro', 'belgi'=>'€'],
    'RUB' => ['sotish'=>(float)($soz['rub_sotish_kurs']??140),   'olish'=>(float)($soz['rub_olish_kurs']??138),   'nomi'=>'RUB — Rubl',  'belgi'=>'₽'],
    'CNY' => ['sotish'=>(float)($soz['cny_sotish_kurs']??1750),  'olish'=>(float)($soz['cny_olish_kurs']??1730),  'nomi'=>'CNY — Yuan',  'belgi'=>'¥'],
];
@endphp
                <form method="POST" action="{{ route('taminotchi.tulov.store', $taminotchi) }}"
                      id="taminot-tulov-form">
                    @csrf
                    <div class="row g-2">
                        {{-- Valyuta tanlash --}}
                        <div class="col-12">
                            <label class="form-label small mb-1 fw-medium">Valyuta</label>
                            <div class="d-flex gap-1 flex-wrap" id="valyuta-tabs">
                                @foreach($kurslar as $kod => $info)
                                <button type="button"
                                    class="btn btn-sm {{ $kod==='UZS' ? 'btn-warning' : 'btn-outline-secondary' }} valyuta-tab"
                                    data-kod="{{ $kod }}"
                                    data-sotish="{{ $info['sotish'] }}"
                                    data-olish="{{ $info['olish'] }}"
                                    data-belgi="{{ $info['belgi'] }}">
                                    {{ $kod }}
                                </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="valyuta" id="valyuta-input" value="UZS">
                        </div>

                        {{-- Summa --}}
                        <div class="col-12">
                            <label class="form-label small mb-1">
                                Summa <span class="text-danger">*</span>
                                <span id="valyuta-belgi" class="text-muted ms-1"></span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="summa" id="summa-input"
                                       class="form-control text-end fw-bold"
                                       placeholder="0"
                                       autocomplete="off"
                                       value="{{ old('summa', $balans['qoldiq'] > 0 ? number_format($balans['qoldiq'],0,'.',',') : '') }}"
                                       inputmode="numeric"
                                       oninput="summaFormat(this)">
                                <span class="input-group-text" id="summa-birlik">so'm</span>
                            </div>
                        </div>

                        {{-- Kurs (UZS emas bo'lsa) --}}
                        <div class="col-12" id="kurs-blok" style="display:none">
                            <div class="card border-warning border-opacity-50 p-2" style="background:#fffef0">
                                <div class="row g-2 align-items-center">
                                    <div class="col-5">
                                        <label class="form-label small mb-1 text-success">
                                            <i class="bi bi-arrow-down me-1"></i>Sotish kursi
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="kurs-sotish" class="form-control"
                                                   readonly style="background:#f8f9fa">
                                            <span class="input-group-text text-muted" style="font-size:.7rem">so'm</span>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <label class="form-label small mb-1 text-danger">
                                            <i class="bi bi-arrow-up me-1"></i>Olish kursi
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="kurs-olish" class="form-control"
                                                   readonly style="background:#f8f9fa">
                                            <span class="input-group-text text-muted" style="font-size:.7rem">so'm</span>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <label class="form-label small mb-1">&nbsp;</label>
                                        <select id="kurs-tur" class="form-select form-select-sm">
                                            <option value="sotish">Sotish</option>
                                            <option value="olish">Olish</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="kurs" id="kurs-input" value="1">
                                <div class="mt-2 p-2 rounded" style="background:#fff3cd;font-size:.78rem">
                                    <i class="bi bi-calculator me-1 text-warning"></i>
                                    <span id="kurs-hisob">—</span>
                                </div>
                            </div>
                        </div>

                        {{-- Sana va to'lov turi --}}
                        <div class="col-6">
                            <label class="form-label small mb-1">Sana <span class="text-danger">*</span></label>
                            <input type="date" name="tolov_sana" class="form-control form-control-sm"
                                   value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">To'lov turi</label>
                            <select name="tolov_turi" class="form-select form-select-sm">
                                <option value="naqd">💵 Naqd</option>
                                <option value="bank">🏦 Bank o'tkazma</option>
                                <option value="plastik">💳 Plastik</option>
                                <option value="offset">⚖️ Hisob-kitob</option>
                            </select>
                        </div>

                        {{-- Kirim ulash --}}
                        <div class="col-12">
                            <label class="form-label small mb-1">Kirim #</label>
                            <select name="kirim_id" class="form-select form-select-sm">
                                <option value="">Umumiy to'lov</option>
                                @foreach($kirimlar->where('qoldiq','>',0) as $k)
                                <option value="{{ $k->id }}">
                                    {{ $k->kirim_sana->format('d.m.Y') }}
                                    — {{ number_format($k->qoldiq,0,'.',' ') }} so'm
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small mb-1">Izoh</label>
                            <input type="text" name="izoh" class="form-control form-control-sm" placeholder="Ixtiyoriy...">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                <i class="bi bi-check2 me-1"></i>To'lovni tasdiqlash
                            </button>
                        </div>
                    </div>
                </form>

@push('scripts')
<script>
// ── Raqam formatlash (1 000 000 000.00 ko'rinishida) ─────────────
function summaFormat(inp) {
    var raw = inp.value.replace(/[^0-9]/g, '');
    if (!raw) { inp.value = ''; return; }
    var n = parseInt(raw, 10);
    inp.value = n.toLocaleString('uz-UZ').replace(/,/g, ' ');
    inp.dataset.raw = raw;
}

// Form submit oldida raw qiymatni name ga yozamiz
document.getElementById('taminot-tulov-form').addEventListener('submit', function() {
    var inp = document.getElementById('summa-input');
    inp.value = (inp.dataset.raw || inp.value.replace(/[^0-9]/g, ''));
});

// ── Valyuta tanlash ──────────────────────────────────────────────
var joriyKod = 'UZS';

document.querySelectorAll('.valyuta-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.valyuta-tab').forEach(b => {
            b.className = 'btn btn-sm btn-outline-secondary valyuta-tab';
        });
        this.className = 'btn btn-sm btn-warning valyuta-tab';

        joriyKod = this.dataset.kod;
        document.getElementById('valyuta-input').value = joriyKod;
        document.getElementById('summa-birlik').textContent = this.dataset.belgi;
        document.getElementById('valyuta-belgi').textContent = joriyKod;

        var kursBlok  = document.getElementById('kurs-blok');
        if (joriyKod === 'UZS') {
            kursBlok.style.display = 'none';
            document.getElementById('kurs-input').value = 1;
        } else {
            kursBlok.style.display = 'block';
            document.getElementById('kurs-sotish').value = parseFloat(this.dataset.sotish);
            document.getElementById('kurs-olish').value  = parseFloat(this.dataset.olish);
            kursHisob();
        }
    });
});

// Sotish/Olish kurs tanlash
document.getElementById('kurs-tur').addEventListener('change', kursHisob);
document.getElementById('summa-input').addEventListener('input', kursHisob);

function kursHisob() {
    if (joriyKod === 'UZS') return;
    var tur   = document.getElementById('kurs-tur').value;
    var kurs  = parseFloat(document.getElementById('kurs-' + tur).value) || 1;
    var raw   = parseInt(document.getElementById('summa-input').dataset.raw || 0);
    var uzs   = Math.round(raw * kurs);
    document.getElementById('kurs-input').value = kurs;
    document.getElementById('kurs-hisob').innerHTML =
        '<strong>' + raw.toLocaleString('uz-UZ') + '</strong> ' + joriyKod +
        ' × <strong>' + kurs.toLocaleString('uz-UZ') + '</strong> so'm = ' +
        '<strong class="text-success">' + uzs.toLocaleString('uz-UZ') + ' so'm</strong>';
}

// Boshlang'ich format
(function() {
    var inp = document.getElementById('summa-input');
    if (inp.value) summaFormat(inp);
    document.getElementById('summa-birlik').textContent = 'so'm';
})();
</script>
@endpush
            </div>
        </div>
        @endif

        {{-- To'lovlar tarixi --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 small fw-bold">
                    <i class="bi bi-receipt me-1 text-primary"></i>To'lovlar
                </h6>
                <span class="badge bg-secondary">{{ $tulovlar->count() }} ta</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Sana</th>
                            <th>Tur</th>
                            <th class="text-end">Summa</th>
                            <th>Kim</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tulovlar as $tv)
                        <tr>
                            <td class="small">{{ $tv->tolov_sana->format('d.m.Y') }}</td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info" style="font-size:.65rem">
                                    {{ $tv->tolov_turi }}
                                </span>
                            </td>
                            <td class="text-end small fw-bold text-success">
                                {{ number_format($tv->summa,0,'.',' ') }}
                            </td>
                            <td class="text-muted" style="font-size:.72rem">
                                {{ $tv->xodim->ism_familiya ?? '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3 small">To'lovlar yo'q</td></tr>
                        @endforelse
                    </tbody>
                    @if($tulovlar->count())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2">Jami:</td>
                            <td class="text-end text-success">{{ number_format($tulovlar->sum('summa'),0,'.',' ') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
