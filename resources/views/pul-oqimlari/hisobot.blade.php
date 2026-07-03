@extends('layouts.app')
@section('title', 'Pul oqimi hisoboti')

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.82rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.7rem; letter-spacing:.03em; text-transform:uppercase; padding:8px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; position:sticky; left:0; z-index:7; min-width:230px; }
.bank-table thead th.th-yillik { position:sticky; right:0; z-index:7; }

.bank-table tbody td.tl { position:sticky; left:0; z-index:2; background:inherit; text-align:left; border-right:2px solid #93c5fd; white-space:nowrap; }
.bank-table tbody td.td-yillik { position:sticky; right:0; z-index:2; background:inherit; border-left:2px solid #93c5fd; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:6px 8px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }

.bolim-header td {
    background:#0f172a; color:#fbbf24; font-weight:800; font-size:.74rem;
    text-transform:uppercase; letter-spacing:.04em; padding:6px 8px;
    position:sticky; left:0; z-index:1;
}

.boshlangich-row td {
    background:linear-gradient(90deg,#fef9c3,#fefce8) !important; font-weight:800;
    border-top:2px solid #0f172a; border-bottom:1px solid #eab308; color:#854d0e;
    position:sticky; top:0; z-index:5;
}
.jami-row td {
    background:linear-gradient(90deg,#bfdbfe,#dbeafe) !important; font-weight:800;
    border-top:2px solid #60a5fa; border-bottom:2px solid #60a5fa; color:#1e3a8a;
}
.sof-oqim-row td {
    background:linear-gradient(90deg,#e9d5ff,#f3e8ff) !important; font-weight:800; color:#6b21a8;
    border-top:2px solid #a855f7; border-bottom:2px solid #a855f7;
}
.yakuniy-row td {
    background:linear-gradient(90deg,#bbf7d0,#dcfce7) !important; font-weight:800; color:#15803d;
    border-top:3px double #16a34a; border-bottom:3px double #16a34a; font-size:.88rem;
    position:sticky; bottom:0; z-index:6;
}

.qator-nomi { padding-left:16px; }
.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:10px 14px; }
.filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.8rem; height:32px; font-weight:600; }
.bank-wrap { overflow:auto; max-height:calc(100vh - 200px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }

@media print {
    .d-print-none { display:none !important; }
    .bank-wrap { max-height:none; overflow:visible; }
    .bank-table thead th, .yakuniy-row td, .boshlangich-row td { position:static; }
}
</style>
@endpush

@section('content')
@php
    $oyNomlari = ['','Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'];
    $kasr = $birlik === 'som' ? 2 : 2;
    $fmt = function($n) use ($bolgich, $kasr) {
        $v = $n / $bolgich;
        if (abs($v) < 0.005) return '<span class="text-muted">—</span>';
        return $v < 0
            ? '<span class="text-danger">(' . number_format(abs($v),$kasr,'.',' ') . ')</span>'
            : number_format($v,$kasr,'.',' ');
    };
    $jamiOylar = fn($arr) => array_sum($arr);
@endphp

<div class="filter-bar mb-0 d-print-none">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-clipboard-data" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Pul oqimi hisoboti</span>
        </div>
        <div>
            <select name="yil" class="form-select" style="width:90px" onchange="this.form.submit()">
                @foreach($yillarRoyxati as $y)
                    <option value="{{ $y }}" {{ $y == $yil ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        @if($filiallar->count())
        <div>
            <select name="filial_id" class="form-select" style="width:160px" onchange="this.form.submit()">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                    <option value="{{ $f->id }}" {{ $filialId == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <select name="kassa_turi" class="form-select" style="width:200px" onchange="this.form.submit()">
                <option value="">Barchasi (naqd+terminal+bank)</option>
                <option value="naqd"     {{ $kassaTuri === 'naqd'     ? 'selected' : '' }}>Naqd</option>
                <option value="terminal" {{ $kassaTuri === 'terminal' ? 'selected' : '' }}>Terminal</option>
                <option value="bank"     {{ $kassaTuri === 'bank'     ? 'selected' : '' }}>Bank</option>
            </select>
        </div>
        <div>
            <select name="birlik" class="form-select" style="width:130px" onchange="this.form.submit()">
                <option value="som"  {{ $birlik === 'som'  ? 'selected' : '' }}>So'm</option>
                <option value="ming" {{ $birlik === 'ming' ? 'selected' : '' }}>Ming so'm</option>
                <option value="mln"  {{ $birlik === 'mln'  ? 'selected' : '' }}>Mln so'm</option>
            </select>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('pul-oqimlari.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Orqaga
            </a>
            <button type="button" onclick="window.print()" class="btn btn-dark btn-sm fw-bold">
                <i class="bi bi-printer me-1"></i>Chop etish
            </button>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
<table class="bank-table">
    <thead>
        <tr>
            <th class="tl">Ko'rsatkich</th>
            @for($m=1;$m<=12;$m++)
            <th style="width:100px">{{ $oyNomlari[$m] }}</th>
            @endfor
            <th class="th-yillik" style="width:120px">{{ $yil }} Jami</th>
        </tr>
    </thead>
    <tbody>
        <tr class="boshlangich-row">
            <td class="tl">Davr boshidagi kassa qoldig'i</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt($oylikBoshlangich[$m]) !!}</td>
            @endfor
            <td class="num td-yillik">{!! $fmt($oylikBoshlangich[1]) !!}</td>
        </tr>

        <tr class="bolim-header"><td colspan="14">Tushumlar</td></tr>
        @foreach($tushumlar as $q)
        <tr>
            <td class="tl qator-nomi">{{ $q['kategoriya']->nomi }}</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt($q['oylar'][$m]) !!}</td>
            @endfor
            <td class="num fw-semibold td-yillik">{!! $fmt($q['jami']) !!}</td>
        </tr>
        @endforeach
        <tr class="jami-row">
            <td class="tl">Jami tushum</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt($oylikSofKirim[$m]) !!}</td>
            @endfor
            <td class="num td-yillik">{!! $fmt($jamiOylar($oylikSofKirim)) !!}</td>
        </tr>

        <tr class="bolim-header"><td colspan="14">To'lovlar</td></tr>
        @foreach($tolovlar as $q)
        <tr>
            <td class="tl qator-nomi">{{ $q['kategoriya']->nomi }}</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt(-$q['oylar'][$m]) !!}</td>
            @endfor
            <td class="num fw-semibold td-yillik">{!! $fmt(-$q['jami']) !!}</td>
        </tr>
        @endforeach
        <tr class="jami-row">
            <td class="tl">Jami to'lovlar</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt(-$oylikSofChiqim[$m]) !!}</td>
            @endfor
            <td class="num td-yillik">{!! $fmt(-$jamiOylar($oylikSofChiqim)) !!}</td>
        </tr>

        <tr class="sof-oqim-row">
            <td class="tl">Sof pul oqimi (tushum − to'lov)</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt($oylikSofKirim[$m] - $oylikSofChiqim[$m]) !!}</td>
            @endfor
            <td class="num td-yillik">{!! $fmt(array_sum($oylikSofKirim) - array_sum($oylikSofChiqim)) !!}</td>
        </tr>

        <tr class="yakuniy-row">
            <td class="tl">Davr oxiridagi kassa qoldig'i</td>
            @for($m=1;$m<=12;$m++)
                <td class="num">{!! $fmt($oylikYakuniy[$m]) !!}</td>
            @endfor
            <td class="num td-yillik">{!! $fmt($oylikYakuniy[12]) !!}</td>
        </tr>
    </tbody>
</table>
</div>

<script>
(function() {
    // "Davr boshidagi qoldiq" qatori header ostida yopishib turishi uchun
    // header'ning HAQIQIY balandligini o'lchab, sticky "top" qiymatini
    // shunga moslab qo'yamiz (shrift/brauzerga qarab qat'iy piksel farq
    // qilishi mumkin, shuning uchun statik qiymat emas, o'lchab olamiz).
    function moslashtirish() {
        var thead = document.querySelector('.bank-table thead');
        var boshQator = document.querySelector('.boshlangich-row');
        if (!thead || !boshQator) return;
        var balandlik = thead.offsetHeight;
        boshQator.querySelectorAll('td').forEach(function(td) {
            td.style.top = balandlik + 'px';
        });
    }
    window.addEventListener('load', moslashtirish);
    window.addEventListener('resize', moslashtirish);
})();
</script>
@endsection
