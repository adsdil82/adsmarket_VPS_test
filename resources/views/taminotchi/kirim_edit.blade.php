@extends('layouts.app')
@section('title', 'Kirimni tahrirlash')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.show',$taminotchi) }}">{{ $taminotchi->nomi }}</a></li>
    <li class="breadcrumb-item active">Kirimni tahrirlash</li>
@endsection

@push('styles')
<style>
.qator-row td { vertical-align: middle; }
.del-qator { cursor: pointer; color: #dc3545; }
.yangi-tovar-belgi { font-size:.65rem; color:#b45309; background:#fff7ed; border:1px solid #fdba74; border-radius:4px; padding:1px 5px; display:inline-flex; align-items:center; gap:3px; margin-top:3px; cursor:default; white-space:nowrap; }
.yangi-tovar-belgi .bekor-link { cursor:pointer; color:#dc3545; font-weight:bold; }
.ustama-bosh { background:#fffbeb; min-width:90px; }
.ustama-bosh input { min-width:80px; }
#qatorlar-table th { font-size:.78rem; white-space:nowrap; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#78350f,#f59e0b)">
        <h6 class="mb-0 text-white fw-bold">
            <i class="bi bi-pencil-square me-2"></i>
            {{ $taminotchi->nomi }} — Kirimni tahrirlash ({{ $kirim->kirim_sana->format('d.m.Y') }})
        </h6>
    </div>
    <div class="card-body">
        @if($kirim->tolangan > 0)
        <div class="alert alert-warning py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Bu kirimga allaqachon <strong>{{ number_format($kirim->tolangan,0,'.',' ') }} so'm</strong> to'lov qilingan.
            Jami summani o'zgartirsangiz, qoldiq shunga mos qayta hisoblanadi.
        </div>
        @endif
        <form method="POST" action="{{ route('taminotchi.kirim.update',[$taminotchi,$kirim]) }}" id="kirim-form">
            @csrf
            @method('PUT')
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Hujjat raqami (Schyot-faktura)</label>
                    <input type="text" name="hujjat_raqam" class="form-control" value="{{ old('hujjat_raqam', $kirim->hujjat_raqam) }}" placeholder="SF-2025-001">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Kirim sanasi <span class="text-danger">*</span></label>
                    <input type="date" name="kirim_sana" class="form-control" value="{{ old('kirim_sana', $kirim->kirim_sana->toDateString()) }}" required>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control" value="{{ old('izoh', $kirim->izoh) }}" placeholder="Ixtiyoriy...">
                </div>
            </div>

            {{-- Tovarlar jadvali --}}
            <h6 class="fw-bold mb-2">Tovarlar ro'yxati</h6>
            <div class="small text-muted mb-2">
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">POS %/Nasiya %</span>
                ustunlari faqat <strong>yangi tovar</strong> (katalogda topilmagan) qatorlarda ko'rinadi — kirim narxidan keyin ketma-ket joylashgan.
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-sm border align-middle" id="qatorlar-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:13%">Tovar guruhi</th>
                            <th style="width:17%">Tovar nomi <span class="text-danger">*</span></th>
                            <th style="width:7%">Miqdor</th>
                            <th style="width:7%">Birlik</th>
                            <th style="width:9%">Kirim narxi</th>
                            <th style="width:7%">POS %</th>
                            <th style="width:9%">POS narxi</th>
                            <th style="width:7%">Nasiya %</th>
                            <th style="width:9%">Nasiya narxi</th>
                            <th style="width:8%">Jami</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="qatorlar-body"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" class="fw-bold text-end">Jami:</td>
                            <td class="fw-bold text-end" id="umumiy-jami">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="qatorQosh()">
                    <i class="bi bi-plus-lg me-1"></i>Qator qo'shish
                </button>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-bold px-4">
                    <i class="bi bi-check2 me-1"></i>O'zgarishlarni saqlash
                </button>
                <a href="{{ route('taminotchi.show',$taminotchi) }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>
@endsection

@php
$tovarlarJs = $tovarlar->map(fn($t) => [
    'id' => $t->id, 'nomi' => $t->nomi, 'narx' => $t->sotish_narx,
    'birlik' => $t->birlik, 'guruh_id' => $t->guruh_id,
]);
$mavjudQatorlarJs = $kirim->qatorlar->map(fn($q) => [
    'guruh_id' => $q->tovar?->guruh_id, 'nomi' => $q->nomi, 'tovar_id' => $q->tovar_id,
    'miqdor' => $q->miqdor, 'birlik' => $q->birlik, 'narx' => $q->narx,
]);
@endphp
@push('scripts')
<script>
var qatorCount = 0;
var BARCHA_TOVARLAR = @json($tovarlarJs);
var MAVJUD_QATORLAR = @json($mavjudQatorlarJs);
var GURUH_OPTIONS_HTML = @json($guruhlar)
    .map(function(g) { return '<option value="' + g.id + '">' + g.nomi + '</option>'; })
    .join('');

function qatorQosh() {
    var idx = qatorCount++;
    var tbody = document.getElementById('qatorlar-body');
    var tr = document.createElement('tr');
    tr.className = 'qator-row';
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm guruh-select" data-idx="${idx}">
                <option value="">— Guruh tanlang —</option>
                ${GURUH_OPTIONS_HTML}
            </select>
            <input type="hidden" name="qatorlar[${idx}][guruh_id]" class="guruh-id-input">
        </td>
        <td>
            <input type="text" name="qatorlar[${idx}][nomi]" class="form-control form-control-sm nomi-input"
                   list="tovar-list-${idx}" required placeholder="Guruh tanlang yoki to'g'ridan-to'g'ri yozing" autocomplete="off">
            <datalist id="tovar-list-${idx}"></datalist>
            <input type="hidden" name="qatorlar[${idx}][tovar_id]" class="tovar-id-input">
            <div class="yangi-tovar-belgi" style="display:none">
                🆕 Yangi tovar
                <span class="bekor-link" title="Bekor qilish">✕</span>
            </div>
        </td>
        <td><input type="number" name="qatorlar[${idx}][miqdor]" class="form-control form-control-sm miqdor-input" value="1" min="0.001" step="0.001" required></td>
        <td><input type="text" name="qatorlar[${idx}][birlik]" class="form-control form-control-sm birlik-input" value="dona"></td>
        <td><input type="number" name="qatorlar[${idx}][narx]" class="form-control form-control-sm narx-input" min="0" step="100" required></td>
        <td class="ustama-bosh" style="display:none"><input type="number" class="form-control form-control-sm ustama-pos-foiz" min="0" step="1" value="0"></td>
        <td class="ustama-bosh" style="display:none">
            <input type="number" class="form-control form-control-sm pos-narx-input" min="0" step="100">
            <input type="hidden" name="qatorlar[${idx}][pos_narx]" class="pos-narx-hidden">
        </td>
        <td class="ustama-bosh" style="display:none"><input type="number" class="form-control form-control-sm ustama-nasiya-foiz" min="0" step="1" value="20"></td>
        <td class="ustama-bosh" style="display:none">
            <input type="number" class="form-control form-control-sm nasiya-narx-input" min="0" step="100">
            <input type="hidden" name="qatorlar[${idx}][nasiya_narx]" class="nasiya-narx-hidden">
        </td>
        <td class="fw-bold text-end jami-td">0</td>
        <td><i class="bi bi-x-circle del-qator" onclick="qatorOchir(this)"></i></td>
    `;
    tbody.appendChild(tr);
    jamiYangilash();
    bindEvents(tr);
    return tr;
}

function qatorOchir(el) {
    el.closest('tr').remove();
    jamiYangilash();
}

function jamiYangilash() {
    var jami = 0;
    document.querySelectorAll('.qator-row').forEach(function(tr) {
        var m = parseFloat(tr.querySelector('.miqdor-input').value) || 0;
        var n = parseFloat(tr.querySelector('.narx-input').value) || 0;
        var q = Math.round(m * n);
        tr.querySelector('.jami-td').textContent = q.toLocaleString('uz-UZ');
        jami += q;
    });
    document.getElementById('umumiy-jami').textContent = jami.toLocaleString('uz-UZ');
}

function guruhOzgardi(row) {
    var guruhSel  = row.querySelector('.guruh-select');
    var guruhId   = guruhSel.value;
    row.querySelector('.guruh-id-input').value = guruhId;

    var datalist = row.querySelector('datalist');
    datalist.innerHTML = '';
    var royxat = guruhId ? BARCHA_TOVARLAR.filter(t => String(t.guruh_id) === guruhId) : BARCHA_TOVARLAR;
    royxat.forEach(function(t) {
        var opt = document.createElement('option');
        opt.value = t.nomi;
        datalist.appendChild(opt);
    });

    row.querySelector('.tovar-id-input').value = '';
    tovarMosligiTekshir(row);
}

function tovarMosligiTekshir(row) {
    var nomiInput = row.querySelector('.nomi-input');
    var nomi = nomiInput.value.trim();
    var guruhId = row.querySelector('.guruh-id-input').value;
    var belgi = row.querySelector('.yangi-tovar-belgi');

    if (!nomi) { belgi.style.display = 'none'; ustamaUstunlarKorsat(row, false); return; }

    var royxat = guruhId ? BARCHA_TOVARLAR.filter(t => String(t.guruh_id) === guruhId) : BARCHA_TOVARLAR;
    var mos = royxat.find(t => t.nomi.toLowerCase() === nomi.toLowerCase());

    if (mos) {
        row.querySelector('.tovar-id-input').value = mos.id;
        row.querySelector('.narx-input').value = mos.narx;
        row.querySelector('.birlik-input').value = mos.birlik || 'dona';
        belgi.style.display = 'none';
        ustamaUstunlarKorsat(row, false);
        jamiYangilash();
    } else {
        row.querySelector('.tovar-id-input').value = '';
        var yangiTovar = !!guruhId;
        belgi.style.display = yangiTovar ? 'inline-flex' : 'none';
        ustamaUstunlarKorsat(row, yangiTovar);
        if (yangiTovar) ustamaHisobla(row, 'foiz');
    }
}

function ustamaUstunlarKorsat(row, korsat) {
    row.querySelectorAll('.ustama-bosh').forEach(function(td) {
        td.style.display = korsat ? 'table-cell' : 'none';
    });
}

function ustamaHisobla(row, manba) {
    var tanNarx = parseFloat(row.querySelector('.narx-input').value) || 0;

    ['pos', 'nasiya'].forEach(function(tur) {
        var foizInp = row.querySelector('.ustama-' + tur + '-foiz');
        var narxInp = row.querySelector('.' + tur + '-narx-input');
        var hiddenInp = row.querySelector('.' + tur + '-narx-hidden');
        if (!foizInp) return;

        if (manba === 'narx') {
            var narx = parseFloat(narxInp.value) || 0;
            var foiz = tanNarx > 0 ? Math.round(((narx - tanNarx) / tanNarx) * 100) : 0;
            foizInp.value = foiz;
        } else {
            var foiz = parseFloat(foizInp.value) || 0;
            var narx = Math.round(tanNarx * (1 + foiz / 100));
            narxInp.value = narx;
        }
        hiddenInp.value = narxInp.value;
    });
}

function bindEvents(row) {
    row.querySelectorAll('.miqdor-input,.narx-input').forEach(function(inp) {
        inp.addEventListener('input', jamiYangilash);
    });
    row.querySelector('.narx-input').addEventListener('input', function() { ustamaHisobla(row, 'foiz'); });

    var guruhSel = row.querySelector('.guruh-select');
    if (guruhSel) guruhSel.addEventListener('change', function() { guruhOzgardi(row); });

    var nomiInput = row.querySelector('.nomi-input');
    if (nomiInput) nomiInput.addEventListener('input', function() { tovarMosligiTekshir(row); });

    var bekorLink = row.querySelector('.bekor-link');
    if (bekorLink) bekorLink.addEventListener('click', function() {
        nomiInput.value = '';
        row.querySelector('.tovar-id-input').value = '';
        row.querySelector('.yangi-tovar-belgi').style.display = 'none';
        ustamaUstunlarKorsat(row, false);
        nomiInput.focus();
    });

    row.querySelectorAll('.ustama-pos-foiz,.ustama-nasiya-foiz').forEach(function(inp) {
        inp.addEventListener('input', function() { ustamaHisobla(row, 'foiz'); });
    });
    row.querySelectorAll('.pos-narx-input,.nasiya-narx-input').forEach(function(inp) {
        inp.addEventListener('input', function() { ustamaHisobla(row, 'narx'); });
    });
}

// Mavjud qatorlarni yuklaymiz
MAVJUD_QATORLAR.forEach(function(q) {
    var tr = qatorQosh();
    if (q.guruh_id) {
        tr.querySelector('.guruh-select').value = q.guruh_id;
        guruhOzgardi(tr);
    }
    tr.querySelector('.nomi-input').value = q.nomi;
    tr.querySelector('.tovar-id-input').value = q.tovar_id || '';
    tr.querySelector('.miqdor-input').value = q.miqdor;
    tr.querySelector('.birlik-input').value = q.birlik;
    tr.querySelector('.narx-input').value = q.narx;
});
if (!MAVJUD_QATORLAR.length) qatorQosh();
jamiYangilash();
</script>
@endpush
