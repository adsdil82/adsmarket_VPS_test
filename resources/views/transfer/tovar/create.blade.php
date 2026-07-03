@extends('layouts.app')
@section('title', 'Yangi tovar transferi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('transfer.tovar.index') }}">Tovar transferlari</a></li>
    <li class="breadcrumb-item active">Yangi</li>
@endsection

@push('styles')
<style>
.del-btn { cursor:pointer;color:#dc3545;border:none;background:none; }
.tovar-row td { vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#14532d,#16a34a)">
        <h6 class="mb-0 text-white fw-bold">
            <i class="bi bi-box-arrow-right me-2"></i>Yangi tovar transferi
        </h6>
    </div>
    <div class="card-body">
        @if($errors->has('general'))
        <div class="alert alert-danger py-2">{{ $errors->first('general') }}</div>
        @endif

        <form method="POST" action="{{ route('transfer.tovar.store') }}" id="transfer-form">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Jo'natuvchi filial <span class="text-danger">*</span></label>
                    <select name="from_filial_id" id="from-filial" class="form-select" required onchange="omborlarFilter('from','from-ombor',this.value)">
                        <option value="">— Tanlang —</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ $f->id == $mening_filial_id ? 'selected':'' }}>{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Jo'natuvchi ombor</label>
                    <select name="from_ombor_id" id="from-ombor" class="form-select">
                        <option value="">— Ombor tanlang —</option>
                        @foreach($omborlar as $filialId => $ombList)
                            @foreach($ombList as $o)
                            <option value="{{ $o->id }}" data-filial="{{ $filialId }}">{{ $o->nomi }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Qabul qiluvchi filial <span class="text-danger">*</span></label>
                    <select name="to_filial_id" id="to-filial" class="form-select" required onchange="omborlarFilter('to','to-ombor',this.value)">
                        <option value="">— Tanlang —</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Qabul qiluvchi ombor</label>
                    <select name="to_ombor_id" id="to-ombor" class="form-select">
                        <option value="">— Ombor tanlang —</option>
                        @foreach($omborlar as $filialId => $ombList)
                            @foreach($ombList as $o)
                            <option value="{{ $o->id }}" data-filial="{{ $filialId }}">{{ $o->nomi }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control" placeholder="Ixtiyoriy...">
                </div>
            </div>

            {{-- Tovarlar jadvali --}}
            <h6 class="fw-bold mb-2">Tovarlar ro'yxati</h6>
            <div class="mb-2" style="max-width:320px">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" id="barkod-skan" class="form-control" placeholder="Shtrix-kod skaner..." autocomplete="off"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();barkodSkan();}">
                </div>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-sm border" id="tovarlar-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40%">Tovar</th>
                            <th style="width:20%">Miqdor</th>
                            <th style="width:20%">Qoldiq</th>
                            <th style="width:15%">Birlik</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tovarlar-body">
                        <tr class="tovar-row">
                            <td>
                                <select name="tovarlar[0][tovar_id]" class="form-select form-select-sm tovar-select" onchange="qoldiqKorsatish(this,0)" required>
                                    <option value="">— Tovar tanlang —</option>
                                    @foreach($tovarlar as $tv)
                                    <option value="{{ $tv->id }}" data-qoldiq="{{ $tv->qoldiq }}" data-birlik="{{ $tv->birlik }}" data-barkod="{{ $tv->barkod }}">
                                        {{ $tv->nomi }} ({{ $tv->qoldiq }} {{ $tv->birlik }})
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="tovarlar[0][miqdor]" class="form-control form-control-sm miqdor-input" min="0.001" step="0.001" value="1" required></td>
                            <td class="text-muted small qoldiq-td" id="qoldiq-0">—</td>
                            <td class="text-muted small birlik-td" id="birlik-0">—</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mb-4">
                <button type="button" class="btn btn-outline-success btn-sm" onclick="tovarQosh()">
                    <i class="bi bi-plus-lg me-1"></i>Qator qo'shish
                </button>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success fw-bold px-4">
                    <i class="bi bi-send me-1"></i>Transferni yuborish
                </button>
                <a href="{{ route('transfer.tovar.index') }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var qatorCount = 1;
var tovarOptions = '';

document.addEventListener('DOMContentLoaded', function() {
    tovarOptions = document.querySelector('.tovar-select').innerHTML;
    omborlarFilter('from','from-ombor', document.getElementById('from-filial').value);
});

function omborlarFilter(prefix, selectId, filialId) {
    var sel = document.getElementById(selectId);
    var opts = sel.querySelectorAll('option');
    opts.forEach(function(o) {
        if (!o.value) return;
        o.style.display = o.dataset.filial == filialId ? '' : 'none';
    });
    sel.value = '';
}

function qoldiqKorsatish(sel, idx) {
    var opt = sel.options[sel.selectedIndex];
    document.getElementById('qoldiq-'+idx).textContent = opt.dataset.qoldiq ? opt.dataset.qoldiq + ' ' + (opt.dataset.birlik||'') : '—';
    document.getElementById('birlik-'+idx).textContent  = opt.dataset.birlik || '—';
}

function tovarQosh() {
    var idx = qatorCount++;
    var tbody = document.getElementById('tovarlar-body');
    var tr = document.createElement('tr');
    tr.className = 'tovar-row';
    tr.innerHTML = `
        <td><select name="tovarlar[${idx}][tovar_id]" class="form-select form-select-sm" onchange="qoldiqKorsatish(this,${idx})" required>${tovarOptions}</select></td>
        <td><input type="number" name="tovarlar[${idx}][miqdor]" class="form-control form-control-sm" min="0.001" step="0.001" value="1" required></td>
        <td class="text-muted small" id="qoldiq-${idx}">—</td>
        <td class="text-muted small" id="birlik-${idx}">—</td>
        <td><button type="button" class="del-btn" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle"></i></button></td>
    `;
    tbody.appendChild(tr);
    return idx;
}

/**
 * Shtrix-kod skaneri: kiritilgan qiymatga mos tovar topilsa — bo'sh (hali
 * tovar tanlanmagan) qatorga yoki yangi qatorga avtomat qo'yiladi, miqdor
 * maydoniga fokus o'tadi (tezkor ketma-ket skaner uchun qulay).
 */
function barkodSkan() {
    var inp = document.getElementById('barkod-skan');
    var q = inp.value.trim();
    if (!q) return;

    var mosOption = null;
    document.querySelectorAll('.tovar-select option, #tovarlar-body select option').forEach(function(o) {
        if (o.dataset.barkod === q) mosOption = o;
    });

    if (!mosOption) {
        alert("Bu shtrix-kodga mos tovar topilmadi!");
        inp.value = '';
        return;
    }

    // Bo'sh (tovar tanlanmagan) qatorni qidiramiz, bo'lmasa yangisini qo'shamiz
    var boshQator = null, boshIdx = null;
    document.querySelectorAll('#tovarlar-body tr.tovar-row').forEach(function(tr) {
        var sel = tr.querySelector('select');
        if (!boshQator && sel && !sel.value) {
            boshQator = tr;
            boshIdx = sel.name.match(/\[(\d+)\]/)[1];
        }
    });

    var idx;
    if (boshQator) {
        idx = boshIdx;
    } else {
        idx = tovarQosh();
    }

    var sel = document.querySelector(`[name="tovarlar[${idx}][tovar_id]"]`);
    sel.value = mosOption.value;
    qoldiqKorsatish(sel, idx);

    inp.value = '';
    var miqdorInp = document.querySelector(`[name="tovarlar[${idx}][miqdor]"]`);
    if (miqdorInp) { miqdorInp.focus(); miqdorInp.select(); }
}
</script>
@endpush
