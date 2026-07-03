@extends('layouts.app')
@section('title', "Balans hisoboti")
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pul-oqimlari.index') }}">Pul Oqimlari</a></li>
<li class="breadcrumb-item active">Balans hisoboti</li>
@endsection

@push('styles')
<style>
.bank-table { border-collapse:collapse; font-size:.85rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.72rem; letter-spacing:.03em; text-transform:uppercase; padding:9px 8px;
    white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; position:sticky; left:0; z-index:7; min-width:280px; }

.bank-table tbody td.tl { position:sticky; left:0; z-index:2; background:inherit; text-align:left; border-right:2px solid #93c5fd; white-space:nowrap; min-width:280px; }
.bank-table tbody tr.qator-row:hover td { background:#e0edff !important; }
.bank-table tbody tr.qator-row:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr.qator-row:nth-child(even) td { background:#eef4ff; }
.bank-table tbody tr { height:38px; }
.bank-table tbody td { padding:8px 9px; vertical-align:middle; white-space:nowrap; }
.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; white-space:nowrap; font-size:.86rem; }

.bolim-header td {
    background:#0f172a; color:#fbbf24; font-weight:800; font-size:.78rem;
    text-transform:uppercase; letter-spacing:.05em; padding:8px 9px; position:sticky; left:0;
}
.jami-row td {
    background:linear-gradient(90deg,#bfdbfe,#dbeafe) !important; font-weight:800;
    border-top:2px solid #60a5fa; border-bottom:2px solid #60a5fa; color:#1e3a8a;
}
.balans-row td { font-weight:800; font-size:.86rem; }
.balans-row.ok td    { background:linear-gradient(90deg,#bbf7d0,#dcfce7) !important; color:#15803d; }
.balans-row.xato td  { background:linear-gradient(90deg,#fecaca,#fee2e2) !important; color:#b91c1c; }

.qator-nomi { padding-left:16px; }
.badge-avto { background:#22c55e; color:#fff; font-size:.6rem; font-weight:800; padding:1px 6px; border-radius:3px; margin-left:5px; }
.badge-qolda { background:#f59e0b; color:#fff; font-size:.6rem; font-weight:800; padding:1px 6px; border-radius:3px; margin-left:5px; }
.badge-joriy { background:#ef4444; color:#fff; font-size:.58rem; font-weight:800; padding:1px 6px; border-radius:3px; margin-left:3px; }
.del-modda { color:#dc2626; cursor:pointer; margin-left:6px; font-size:.8rem; opacity:.55; }
.del-modda:hover { opacity:1; }
.qator-nomi-matn { cursor:pointer; border-bottom:1px dotted #94a3b8; }
.qator-nomi-matn:hover { background:#dbeafe; }

.cell-qolda { cursor:pointer; background:#fffbeb !important; }
.cell-qolda:hover { background:#fef3c7 !important; outline:2px solid #f59e0b; outline-offset:-2px; }
.cell-edit-inp { width:120px; font-size:.85rem; text-align:right; padding:3px 6px; border:2px solid #2563eb; border-radius:4px; }
.cell-future { color:#cbd5e1; background:#f8fafc !important; }

.filter-bar { background:linear-gradient(90deg,#dbeafe,#bfdbfe); border:1px solid #93c5fd; border-bottom:none; border-radius:8px 8px 0 0; padding:10px 14px; }
.filter-bar .form-select { background:#fff; border:1px solid #60a5fa; color:#1e3a8a; font-size:.82rem; height:34px; font-weight:600; }

.bank-wrap { overflow:auto; max-height:calc(100vh - 260px); border:1px solid #93c5fd; border-radius:0 0 6px 6px; }

.birlik-toggle { display:flex; gap:2px; background:#1e3a8a; border-radius:6px; padding:2px; }
.birlik-btn { background:transparent; color:#bfdbfe; border:none; padding:5px 12px; font-size:.75rem; font-weight:700; border-radius:5px; cursor:pointer; }
.birlik-btn.active { background:#fbbf24; color:#1e293b; }
</style>
@endpush

@section('content')
@php
    $oylar = ['','Yan','Fev','Mar','Apr','May','Iyun','Iyul','Avg','Sen','Okt','Noy','Dek'];
@endphp

<div class="filter-bar mb-0">
    <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 me-2">
            <i class="bi bi-bar-chart-steps" style="font-size:1.2rem;color:#1e3a8a"></i>
            <span class="fw-bold" style="color:#1e3a8a;font-size:1rem">Balans hisoboti</span>
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

        <div class="birlik-toggle ms-2">
            <button type="button" class="birlik-btn active" data-birlik="1" onclick="birlikTanlash(this,1)">so'm</button>
            <button type="button" class="birlik-btn" data-birlik="1000" onclick="birlikTanlash(this,1000)">ming</button>
            <button type="button" class="birlik-btn" data-birlik="1000000" onclick="birlikTanlash(this,1000000)">mln</button>
        </div>

        <div class="ms-auto d-flex gap-2 align-items-center">
            <span class="badge-avto">AVTO</span>
            <span class="badge-qolda">QO'LDA</span>
            <span class="badge-joriy">JORIY</span>
            <button type="button" class="btn btn-warning btn-sm ms-2 fw-bold" data-bs-toggle="modal" data-bs-target="#yangiModdaModal">
                <i class="bi bi-plus-lg me-1"></i>Yangi modda
            </button>
        </div>
    </form>
</div>

<div class="bank-wrap shadow-sm">
<table class="bank-table">
    <thead>
        <tr>
            <th class="tl">Ko'rsatkich</th>
            @foreach(range(1,12) as $oy)
            <th style="width:120px">{{ $oylar[$oy] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($bolimlar as $bolim)
        <tr class="bolim-header"><td colspan="13">{{ $bolim->nomi }}</td></tr>

        @foreach($bolim->qatorlar as $qator)
        <tr class="qator-row">
            <td class="tl qator-nomi">
                <span class="qator-nomi-matn" data-qator-id="{{ $qator->id }}" onclick="nomiTahrirlash(this)" title="Bosib nomni tahrirlang">{{ $qator->nomi }}</span>
                <span class="{{ $qator->hisoblash_turi === 'qolda' ? 'badge-qolda' : 'badge-avto' }}">{{ $qator->hisoblash_turi === 'qolda' ? "QO'LDA" : 'AVTO' }}</span>
                @if($qator->joriy_holat_faqat)<span class="badge-joriy">JORIY</span>@endif
                <i class="bi bi-pencil-square del-modda" style="color:#2563eb" onclick="nomiTahrirlashBoshlash({{ $qator->id }})" title="Nomini tahrirlash"></i>
                @if($qator->hisoblash_turi === 'qolda')
                <i class="bi bi-x-circle del-modda" onclick="moddaOchir({{ $qator->id }}, '{{ addslashes($qator->nomi) }}')" title="Moddani o'chirish"></i>
                @endif
            </td>
            @foreach(range(1,12) as $oy)
            @php $qiymat = $qator->oylik[$oy] ?? null; @endphp
            <td class="num {{ $qiymat === null ? 'cell-future' : ($qator->hisoblash_turi === 'qolda' ? 'cell-qolda' : '') }}"
                data-raw="{{ $qiymat ?? '' }}"
                @if($qator->hisoblash_turi === 'qolda' && $qiymat !== null)
                data-qator="{{ $qator->id }}" data-oy="{{ $oy }}" data-yil="{{ $yil }}" data-filial="{{ $filialId }}"
                onclick="qiymatTahrirlash(this)"
                @endif
            >{{ $qiymat === null ? '·' : ($qiymat != 0 ? number_format($qiymat,2,'.',' ') : '—') }}</td>
            @endforeach
        </tr>
        @endforeach

        <tr class="jami-row" data-bolim-id="{{ $bolim->id }}">
            <td class="tl">Jami {{ $bolim->nomi }}</td>
            @foreach(range(1,12) as $oy)
            @php $jamiQiymat = $bolim_jami_oylik[$bolim->id][$oy] ?? null; @endphp
            <td class="num" data-raw="{{ $jamiQiymat ?? '' }}">{{ $jamiQiymat === null ? '·' : number_format($jamiQiymat,2,'.',' ') }}</td>
            @endforeach
        </tr>
        @endforeach

        {{-- Balans tekshiruvi qatori --}}
        <tr class="balans-row">
            <td class="tl">Aktivlar = Majburiyat+Kapital?</td>
            @foreach(range(1,12) as $oy)
            @php
                $farq = $balans_farqi_oylik[$oy] ?? null;
                $ok = $farq !== null && abs($farq) < 1;
            @endphp
            <td class="num" data-raw="{{ ($farq !== null && !$ok) ? $farq : '' }}" style="{{ $farq === null ? '' : ($ok ? 'color:#15803d' : 'color:#b91c1c') }}">
                {{ $farq === null ? '·' : ($ok ? '✓ 0' : number_format($farq,2,'.',' ')) }}
            </td>
            @endforeach
        </tr>
    </tbody>
</table>
</div>

@php
    $joriyFarq = $balans_farqi_oylik[$oxirgi_oy] ?? 0;
    $joriyOk = abs($joriyFarq) < 1;
@endphp
@if(!$joriyOk)
<div class="alert alert-warning mt-2 py-2 small">
    <i class="bi bi-info-circle me-1"></i>
    Joriy oy uchun balans farqi: <strong>{{ number_format($joriyFarq,2,'.',' ') }} so'm</strong>.
    Odatda tarixiy ma'lumotlarda kuzatilmagan aktivlar (Asosiy vositalar) yoki
    hisobga olinmagan kapital operatsiyalari sababli. "Yangi modda" tugmasi orqali
    yetishmayotgan qatorlarni qo'shib, qiymatlarini kiriting.
</div>
@endif

{{-- Yangi modda qo'shish modali --}}
<div class="modal fade" id="yangiModdaModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('pul-oqimlari.balans.modda.store') }}" class="modal-content">
            @csrf
            <div class="modal-header py-2" style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
                <h6 class="modal-title fw-bold text-white"><i class="bi bi-plus-circle me-2"></i>Yangi balans moddasi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Bo'lim <span class="text-danger">*</span></label>
                    <select name="bolim_id" class="form-select" required>
                        @foreach($bolimlarRoyxat as $b)
                        <option value="{{ $b->id }}">{{ $b->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Modda nomi <span class="text-danger">*</span></label>
                    <input type="text" name="nomi" class="form-control" placeholder="Masalan: Transport vositalari" required>
                </div>
                <div class="form-text">Yangi modda "qo'lda kiritiladigan" turida qo'shiladi — jadvalda katakchani bosib qiymat kiritasiz.</div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Qo'shish</button>
            </div>
        </form>
    </div>
</div>

{{-- Modda o'chirish formalari (har biri uchun alohida, tashqarida) --}}
@foreach($bolimlar as $bolim)
    @foreach($bolim->qatorlar as $qator)
        @if($qator->hisoblash_turi === 'qolda')
        <form id="ochirForm{{ $qator->id }}" method="POST" action="{{ route('pul-oqimlari.balans.modda.destroy', $qator) }}" class="d-none">
            @csrf @method('DELETE')
        </form>
        @endif
    @endforeach
@endforeach

<script>
var joriyBirlik = 1;

function birlikTanlash(btn, birlik) {
    document.querySelectorAll('.birlik-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    joriyBirlik = birlik;
    qayta_render();
}

/**
 * toLocaleString('uz-UZ') brauzerga qarab vergul (,) bilan ajratib
 * qo'yishi mumkin (uz-UZ lokale to'liq qo'llab-quvvatlanmasa) — shuning
 * uchun probel-ajratuvchi formatni o'zimiz qo'lda hisoblaymiz, doimo
 * bir xil ko'rinishda (2 kasr xonali).
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

function qayta_render() {
    document.querySelectorAll('td.num[data-raw]').forEach(function(td) {
        if (td.querySelector('input')) return; // tahrirlash rejimida bo'lsa tegilmaydi
        var raw = td.dataset.raw;
        if (raw === '' || raw === undefined) return; // '·' yoki '✓ 0' kabi maxsus qiymatlar — o'zgartirmaymiz
        var son = parseFloat(raw);
        if (isNaN(son)) return;
        var ko_rinish = son / joriyBirlik;
        var formatted = joriyBirlik === 1
            ? (son != 0 ? formatSum(son) : '—')
            : formatSum(ko_rinish);
        td.textContent = formatted;
    });
}

function moddaOchir(id, nomi) {
    var xabar = '"' + nomi + '" moddasini oʻchirasizmi? Barcha kiritilgan qiymatlar ham oʻchadi.';
    if (confirm(xabar)) {
        document.getElementById('ochirForm' + id).submit();
    }
}

function qiymatTahrirlash(td) {
    if (td.querySelector('input')) return;

    var eskiRaw = parseFloat(td.dataset.raw) || 0;
    td.dataset.eskiHtml = td.innerHTML;

    var inp = document.createElement('input');
    inp.type = 'number';
    inp.className = 'cell-edit-inp';
    inp.value = eskiRaw;
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
        var yil = td.dataset.yil, oy = td.dataset.oy;
        var bugun = new Date();
        var oyOxiri;
        if (parseInt(yil) === bugun.getFullYear() && parseInt(oy) === (bugun.getMonth()+1)) {
            oyOxiri = bugun.toISOString().slice(0,10);
        } else {
            oyOxiri = new Date(yil, oy, 0).toISOString().slice(0,10);
        }

        fetch('{{ route("pul-oqimlari.balans.qiymat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                qator_id: td.dataset.qator, sana: oyOxiri, yil: yil,
                filial_id: td.dataset.filial || null, summa: yangi,
            }),
        }).then(function(res) { return res.json().then(function(data){ return {ok: res.ok, data: data}; }); })
          .then(function(r) {
            if (!r.ok) {
                alert(r.data.xato || 'Xato yuz berdi');
                td.innerHTML = td.dataset.eskiHtml;
                return;
            }
            // To'liq sahifa qayta yuklanmaydi — faqat shu katakcha va
            // "Jami"/"Balans" qatorlarini serverdan qaytgan yangi
            // qiymatlar bilan darhol yangilaymiz (tezkor).
            td.dataset.raw = yangi;
            td.textContent = yangi != 0 ? formatSum(yangi) : '—';
            jamiVaBalansYangilash(r.data);
            qayta_render();
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

/** Server qaytargan yangilangan bo'lim jamilari va balans farqlarini jadvalga joylaydi (reload'siz). */
function jamiVaBalansYangilash(data) {
    document.querySelectorAll('tr.jami-row').forEach(function(tr) {
        var bolimId = tr.dataset.bolimId;
        if (!bolimId || !data.bolim_jami_oylik[bolimId]) return;
        var tds = tr.querySelectorAll('td.num');
        tds.forEach(function(td, idx) {
            var oy = idx + 1;
            var qiymat = data.bolim_jami_oylik[bolimId][oy];
            td.dataset.raw = qiymat === null ? '' : qiymat;
            td.textContent = qiymat === null ? '·' : formatSum(qiymat);
        });
    });

    var balansTr = document.querySelector('tr.balans-row');
    if (balansTr) {
        var tds = balansTr.querySelectorAll('td.num');
        tds.forEach(function(td, idx) {
            var oy = idx + 1;
            var farq = data.balans_farqi_oylik[oy];
            if (farq === null || farq === undefined) {
                td.dataset.raw = ''; td.textContent = '·'; td.style.color = '';
                return;
            }
            var ok = Math.abs(farq) < 1;
            td.dataset.raw = ok ? '' : farq;
            td.textContent = ok ? '✓ 0' : formatSum(farq);
            td.style.color = ok ? '#15803d' : '#b91c1c';
        });
    }
}

/** Modda nomini bosib tahrirlash rejimiga o'tish. */
function nomiTahrirlashBoshlash(qatorId) {
    var span = document.querySelector('.qator-nomi-matn[data-qator-id="' + qatorId + '"]');
    if (span) nomiTahrirlash(span);
}

function nomiTahrirlash(span) {
    if (span.querySelector('input')) return;

    var eskiNomi = span.textContent.trim();
    var qatorId = span.dataset.qatorId;
    span.dataset.eskiHtml = span.innerHTML;

    var inp = document.createElement('input');
    inp.type = 'text';
    inp.className = 'cell-edit-inp';
    inp.style.width = '220px';
    inp.value = eskiNomi;

    span.innerHTML = '';
    span.appendChild(inp);
    inp.focus();
    inp.select();

    var saqlandi = false;
    var saqlash = function() {
        if (saqlandi) return;
        saqlandi = true;
        var yangiNomi = inp.value.trim();
        if (!yangiNomi) { span.innerHTML = span.dataset.eskiHtml; return; }

        fetch('/pul-oqimlari/balans/modda/' + qatorId, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ nomi: yangiNomi }),
        }).then(function(res) { return res.json().then(function(data){ return {ok: res.ok, data: data}; }); })
          .then(function(r) {
            if (!r.ok) {
                alert((r.data && r.data.xato) || 'Xato yuz berdi');
                span.innerHTML = span.dataset.eskiHtml;
                return;
            }
            span.textContent = r.data.nomi;
        }).catch(function() {
            alert('Server bilan aloqa xatosi');
            span.innerHTML = span.dataset.eskiHtml;
        });
    };

    inp.addEventListener('blur', saqlash);
    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); inp.blur(); }
        if (e.key === 'Escape') { saqlandi = true; span.innerHTML = span.dataset.eskiHtml; }
    });
}
</script>
@endsection
