@extends('layouts.app')
@section('title', 'Qaytim / Vozvrat')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
<li class="breadcrumb-item"><a href="{{ route('pos.chek',$sotuv) }}">Chek #{{ $sotuv->check_raqam }}</a></li>
<li class="breadcrumb-item active">Qaytim</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.85rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead th { background:linear-gradient(180deg,#b91c1c,#7f1d1d); color:#fff; font-weight:800; font-size:.68rem; text-transform:uppercase; padding:7px 8px; text-align:right; }
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr:nth-child(even) td { background:#fef2f2; }
.bank-table tbody td { padding:6px 8px; vertical-align:middle; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; }
.qaytim-miqdor { width:90px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-return-left me-2 text-danger"></i>Qaytim / Vozvrat — Chek #{{ $sotuv->check_raqam }}</h5>
    <a href="{{ route('pos.tarix') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
</div>

<div class="alert alert-secondary py-2 small mb-3">
    Sotuv sanasi: {{ $sotuv->created_at->format('d.m.Y H:i') }} &nbsp;|&nbsp;
    Jami sotuv summasi: {{ number_format($sotuv->jami_tolov,0,'.',' ') }} so'm &nbsp;|&nbsp;
    Qaytim smenasi: <strong>{{ $smena->smena_raqami }}</strong>
</div>

<div id="xato-box" class="alert alert-danger py-2" style="display:none"></div>

<form id="qaytim-form">
    <div class="bank-wrap shadow-sm mb-3" style="overflow:auto">
        <table class="bank-table">
            <thead>
                <tr>
                    <th style="width:30px"></th>
                    <th class="tl">Tovar</th>
                    <th>Sotilgan</th>
                    <th>Qaytarilgan</th>
                    <th>Qaytarish mumkin</th>
                    <th>Narx</th>
                    <th style="width:110px">Qaytim miqdori</th>
                    <th>Jami</th>
                </tr>
            </thead>
            <tbody>
                @foreach($qatorlar as $q)
                <tr>
                    <td class="text-center"><input type="checkbox" class="qator-chk" data-tafsilot="{{ $q->tafsilot_id }}" onchange="qatorYoqish(this)"></td>
                    <td class="tl">{{ $q->nomi }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($q->sotilgan,3,'.',' '),'0'),'.') }} {{ $q->birlik }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($q->qaytarilgan,3,'.',' '),'0'),'.') }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($q->qolgan,3,'.',' '),'0'),'.') }}</td>
                    <td class="num">{{ number_format($q->narx,0,'.',' ') }}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm qaytim-miqdor" data-tafsilot="{{ $q->tafsilot_id }}"
                               data-narx="{{ $q->narx }}" min="0" max="{{ $q->qolgan }}" step="0.001" value="0" disabled oninput="jamiHisobla()">
                    </td>
                    <td class="num qator-jami" data-tafsilot="{{ $q->tafsilot_id }}">0</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-medium small">To'lov turi (qanday qaytariladi)</label>
            <select name="tolov_turi" class="form-select" required>
                <option value="naqd">Naqd</option>
                <option value="plastik">Karta/terminal</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium small">Sabab</label>
            <select name="sabab" class="form-select" required>
                <option value="fikr_ozgardi">Mijoz fikri o'zgardi</option>
                <option value="nosoz_mahsulot">Nosoz/yaroqsiz mahsulot</option>
                <option value="notogri_mahsulot">Noto'g'ri mahsulot</option>
                <option value="boshqa">Boshqa</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-medium small">Jami qaytariladigan summa</label>
            <div class="form-control form-control-lg fw-bold text-danger" id="jami-summa-box">0 so'm</div>
        </div>
        <div class="col-12">
            <label class="form-label fw-medium small">Izoh (ixtiyoriy)</label>
            <textarea name="izoh" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-danger btn-lg w-100">
                <i class="bi bi-arrow-return-left me-1"></i>Qaytimni tasdiqlash
            </button>
        </div>
    </div>
</form>

<script>
function qatorYoqish(chk) {
    var inp = document.querySelector('.qaytim-miqdor[data-tafsilot="'+chk.dataset.tafsilot+'"]');
    inp.disabled = !chk.checked;
    if (chk.checked) { inp.value = inp.max; } else { inp.value = 0; }
    jamiHisobla();
}

function jamiHisobla() {
    var jami = 0;
    document.querySelectorAll('.qaytim-miqdor').forEach(function(inp) {
        var miqdor = parseFloat(inp.value) || 0;
        var narx = parseFloat(inp.dataset.narx) || 0;
        var qatorJami = miqdor * narx;
        document.querySelector('.qator-jami[data-tafsilot="'+inp.dataset.tafsilot+'"]').textContent = qatorJami.toLocaleString('uz-UZ');
        jami += qatorJami;
    });
    document.getElementById('jami-summa-box').textContent = jami.toLocaleString('uz-UZ') + " so'm";
}

document.getElementById('qaytim-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var qatorlar = [];
    document.querySelectorAll('.qaytim-miqdor').forEach(function(inp) {
        var miqdor = parseFloat(inp.value) || 0;
        if (miqdor > 0) qatorlar.push({ tafsilot_id: inp.dataset.tafsilot, miqdor: miqdor });
    });
    if (!qatorlar.length) { alert("Kamida bitta tovarni belgilang!"); return; }

    var xatoBox = document.getElementById('xato-box');
    xatoBox.style.display = 'none';

    fetch('{{ route("pos.qaytim.saqlash", $sotuv) }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({
            tolov_turi: document.querySelector('[name=tolov_turi]').value,
            sabab: document.querySelector('[name=sabab]').value,
            izoh: document.querySelector('[name=izoh]').value,
            qatorlar: qatorlar,
        }),
    }).then(function(res) { return res.json().then(function(data){ return {ok: res.ok, data: data}; }); })
      .then(function(r) {
        if (!r.ok) {
            xatoBox.textContent = r.data.xato || 'Xatolik yuz berdi';
            xatoBox.style.display = '';
            return;
        }
        window.location.href = '{{ route("pos.qaytim.royxat") }}';
    });
});
</script>
@endsection
