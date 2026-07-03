@extends('layouts.app')
@section('title', "Moliyaviy natija")
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pul-oqimlari.index') }}">Pul Oqimlari</a></li>
<li class="breadcrumb-item active">Moliyaviy natija</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table thead th {
    position:sticky; top:0; z-index:6;
    background:linear-gradient(180deg,#2563eb,#1d4ed8); color:#fff; font-weight:700;
    font-size:.68rem; letter-spacing:.04em; text-transform:uppercase; padding:7px 8px;
    border-right:1px solid rgba(255,255,255,.15); white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; position:sticky; left:0; z-index:7; }
.bank-table thead th.yillik { background:#1e3a8a; }

.bank-table tbody td.tl {
    position:sticky; left:0; z-index:2; background:inherit;
    text-align:left; border-right:2px solid #c7d7f8;
}
.bank-table tbody tr { border-bottom:1px solid #e2e8f4; }
.bank-table tbody tr.qator-row:hover { background:#eff6ff; }
.bank-table tbody tr.qator-row:nth-child(odd)  td:not(.tl) { background:#fff; }
.bank-table tbody tr.qator-row:nth-child(even) td:not(.tl) { background:#f5f8fd; }
.bank-table tbody tr.qator-row:nth-child(odd)  td.tl { background:#fff; }
.bank-table tbody tr.qator-row:nth-child(even) td.tl { background:#f5f8fd; }
.bank-table tbody td { padding:5px 8px; vertical-align:middle; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }

.bolim-header td {
    background:#1e293b; color:#e2e8f0; font-weight:700; font-size:.72rem;
    text-transform:uppercase; letter-spacing:.04em; padding:6px 8px;
    position:sticky; left:0;
}
.formula-row td {
    background:linear-gradient(90deg,#dbeafe,#eff6ff) !important; font-weight:700;
    border-top:2px solid #93c5fd; border-bottom:2px solid #93c5fd; color:#1e3a8a;
}
.formula-row.sof-daromad td {
    background:linear-gradient(90deg,#1e3a8a,#1e40af) !important; color:#fff;
    font-size:.85rem; border-top:3px solid #60a5fa; border-bottom:3px solid #60a5fa;
}

.qator-nomi { padding-left:16px; }
.badge-avto { background:#dcfce7; color:#15803d; font-size:.58rem; font-weight:700; padding:0 4px; border-radius:2px; margin-left:4px; }
.badge-qolda { background:#fef9c3; color:#a16207; font-size:.58rem; font-weight:700; padding:0 4px; border-radius:2px; margin-left:4px; }

.cell-qolda { cursor:pointer; border-bottom:1px dashed #93c5fd; }
.cell-qolda:hover { background:#fffbeb !important; }
.cell-avto { color:#64748b; }
.cell-edit-inp { width:90px; font-size:.78rem; text-align:right; padding:1px 4px; border:1px solid #2563eb; border-radius:3px; }

.filter-bar { background:linear-gradient(90deg,#eef3ff,#e8f0fe); border:1px solid #c7d7f8; border-bottom:none; border-radius:8px 8px 0 0; padding:8px 12px; }
.filter-bar .form-select { background:#fff; border:1px solid #93c5fd; color:#1e3a8a; font-size:.8rem; height:32px; }

.bank-wrap { overflow:auto; max-height:calc(100vh - 220px); border:1px solid #c7d7f8; border-radius:0 0 6px 6px; }
</style>
@endpush

@section('content')
@php
    $n = fn($v) => number_format((float)$v, 2, '.', ' ');
    $oylar = ['','Yanvar','Fevral','Mart','Aprel','May','Iyun','Iyul','Avgust','Sentabr','Oktabr','Noyabr','Dekabr'];

    // Bo'limdan keyingi kumulyativ formula qatori nomi (Excel'dagi kabi)
    $formulaNomlari = [
        "Savdo hajmi (brutto)"        => "= Jami savdo hajmi (brutto)",
        "Tannarx"                      => "= Marja daromadi qoldig'i",
        "Savdo harajatlari"            => "= Savdo daromadi qoldig'i",
        "Operatsion harajatlar"        => "= Operatsion daromad",
        "Boshqa daromad va harajatlar" => "= Daromad soliq va rezervgacha",
        "Soliq va rezerv"              => "= SOF DAROMAD",
    ];
@endphp

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-graph-up-arrow text-warning" style="font-size:1.1rem"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:.95rem">Moliyaviy natija (Foyda-Zarar)</span>
        </div>
        <div>
            <select name="yil" class="form-select" style="width:100px" onchange="this.form.submit()">
                @for($y = now()->year; $y >= now()->year - 3; $y--)
                <option value="{{ $y }}" {{ $yil == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        @if($filiallar->count() > 1)
        <div>
            <select name="filial_id" class="form-select" style="width:170px" onchange="this.form.submit()">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="ms-auto d-flex gap-2 align-items-center">
            <span class="badge-avto">AVTO</span><small class="text-muted me-2">avtomatik hisoblanadi</small>
            <span class="badge-qolda">QO'LDA</span><small class="text-muted">bosib tahrirlang</small>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
<table class="bank-table">
    <thead>
        <tr>
            <th class="tl" style="min-width:280px">Ko'rsatkich</th>
            @foreach(range(1,12) as $oy)
            <th style="width:95px">{{ $oylar[$oy] }}</th>
            @endforeach
            <th class="yillik" style="width:120px">{{ $yil }} Jami</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bolimlar as $bolim)
        <tr class="bolim-header"><td class="tl" colspan="14">{{ $bolim->nomi }} ({{ $bolim->ishora === 'manfiy' ? '−' : '+' }})</td></tr>

        @foreach($bolim->qatorlar as $qator)
        <tr class="qator-row" data-qator-row="{{ $qator->id }}">
            <td class="tl qator-nomi">
                {{ $qator->nomi }}
                <span class="{{ $qator->hisoblash_turi === 'qolda' ? 'badge-qolda' : 'badge-avto' }}">
                    {{ $qator->hisoblash_turi === 'qolda' ? "Q" : 'A' }}
                </span>
            </td>
            @foreach(range(1,12) as $oy)
            @php $qiymat = $qator->oylik[$oy] ?? 0; @endphp
            <td class="num {{ $qator->hisoblash_turi === 'qolda' ? 'cell-qolda' : 'cell-avto' }}"
                data-raw="{{ $qiymat }}"
                @if($qator->hisoblash_turi === 'qolda')
                data-qator="{{ $qator->id }}" data-oy="{{ $oy }}" data-yil="{{ $yil }}" data-filial="{{ $filialId }}"
                onclick="qiymatTahrirlash(this)"
                @endif
            >{{ $qiymat != 0 ? $n($qiymat) : '—' }}</td>
            @endforeach
            <td class="num fw-bold qator-yillik" data-raw="{{ $qator->yillik }}">{{ $n($qator->yillik) }}</td>
        </tr>
        @endforeach

        <tr class="formula-row {{ $bolim->nomi === 'Soliq va rezerv' ? 'sof-daromad' : '' }}" data-bolim-nomi="{{ $bolim->nomi }}">
            <td class="tl">{{ $formulaNomlari[$bolim->nomi] ?? '' }}</td>
            @foreach(range(1,12) as $oy)
            <td class="num" data-raw="{{ $formulalar[$bolim->nomi]['oylik'][$oy] ?? 0 }}">{{ $n($formulalar[$bolim->nomi]['oylik'][$oy] ?? 0) }}</td>
            @endforeach
            <td class="num" data-raw="{{ $formulalar[$bolim->nomi]['yillik'] ?? 0 }}">{{ $n($formulalar[$bolim->nomi]['yillik'] ?? 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

<script>
/**
 * toLocaleString('uz-UZ') brauzerga qarab vergul (,) bilan ajratib
 * qo'yishi mumkin (uz-UZ lokale to'liq qo'llab-quvvatlanmasa) — shuning
 * uchun probel-ajratuvchi formatni o'zimiz qo'lda hisoblaymiz, doimo
 * bir xil ko'rinishda (server tomon bilan mos: 2 kasr xonali).
 */
function formatSum(v) {
    var son = parseFloat(v) || 0;
    var manfiy = son < 0;
    son = Math.abs(son);
    var butun = Math.floor(son + 0.001);
    var kasr = Math.round((son - butun) * 100);
    if (kasr >= 100) { butun += 1; kasr = 0; }
    var butunStr = butun.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    var kasrStr = (kasr < 10 ? '0' : '') + kasr;
    return (manfiy ? '-' : '') + butunStr + '.' + kasrStr;
}

function qiymatTahrirlash(td) {
    if (td.querySelector('input')) return; // allaqachon tahrirlash rejimida

    // Ko'rsatilgan matnni emas, aniq raqamli data-raw atributini o'qiymiz —
    // aks holda toLocaleString formatidagi bo'shliq belgisi (odatiy probel
    // emas, lokalega xos maxsus belgi bo'lishi mumkin) noto'g'ri parse
    // qilinib, "500 000" o'rniga "500" kabi kesilib qolishi mumkin edi.
    var eskiQiymat = parseFloat(td.dataset.raw) || 0;
    td.dataset.eskiHtml = td.innerHTML;

    var inp = document.createElement('input');
    inp.type = 'number';
    inp.className = 'cell-edit-inp';
    inp.value = eskiQiymat;
    inp.step = '1000';

    td.innerHTML = '';
    td.appendChild(inp);
    inp.focus();
    inp.select();

    var saqlandi = false;
    var saqlash = function() {
        if (saqlandi) return;
        saqlandi = true;
        var yangi = parseFloat(inp.value) || 0;

        fetch('{{ route("pul-oqimlari.moliyaviy-natija.qiymat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                qator_id: td.dataset.qator, oy: td.dataset.oy, yil: td.dataset.yil,
                filial_id: td.dataset.filial || null, summa: yangi,
            }),
        }).then(function(res) { return res.json().then(function(data){ return {ok: res.ok, data: data}; }); })
          .then(function(r) {
            if (!r.ok) {
                alert(r.data.xato || 'Xato yuz berdi');
                td.innerHTML = td.dataset.eskiHtml;
                return;
            }
            // Shu katakni yangilaymiz
            td.dataset.raw = yangi;
            td.textContent = yangi != 0 ? formatSum(yangi) : '—';

            // Shu qatorning yillik jamisini — qatordagi 12 oy yig'indisidan qayta hisoblab yangilaymiz
            var tr = td.closest('tr');
            var yillikJami = 0;
            tr.querySelectorAll('td.num[data-raw]').forEach(function(c) {
                if (c === tr.querySelector('.qator-yillik')) return;
                yillikJami += parseFloat(c.dataset.raw) || 0;
            });
            var yillikTd = tr.querySelector('.qator-yillik');
            if (yillikTd) { yillikTd.dataset.raw = yillikJami; yillikTd.textContent = formatSum(yillikJami); }

            // Sahifa qayta yuklanmaydi — server qaytargan yangi formula
            // (kumulyativ) qatorlarini to'g'ridan-to'g'ri DOM'ga joylaymiz.
            formulaQatorlarniYangilash(r.data.formulalar);
        }).catch(function() {
            alert('Server bilan aloqa xatosi');
            td.innerHTML = td.dataset.eskiHtml;
        });
    };

    inp.addEventListener('blur', saqlash);
    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); inp.blur(); }
        if (e.key === 'Escape') { saqlandi = true; td.innerHTML = td.dataset.eskiHtml; }
    });
}

/** Server qaytargan yangilangan kumulyativ formula qatorlarini jadvalga joylaydi (reload'siz). */
function formulaQatorlarniYangilash(formulalar) {
    document.querySelectorAll('tr.formula-row').forEach(function(tr) {
        var bolimNomi = tr.dataset.bolimNomi;
        if (!bolimNomi || !formulalar[bolimNomi]) return;
        var tds = tr.querySelectorAll('td.num');
        tds.forEach(function(td, idx) {
            var qiymat;
            if (idx < 12) {
                qiymat = formulalar[bolimNomi].oylik[idx + 1];
            } else {
                qiymat = formulalar[bolimNomi].yillik;
            }
            td.dataset.raw = qiymat;
            td.textContent = formatSum(qiymat);
        });
    });
}
</script>
@endsection
