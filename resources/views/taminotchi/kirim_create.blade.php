@extends('layouts.app')
@section('title', 'Kirim kiritish')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.show',$taminotchi) }}">{{ $taminotchi->nomi }}</a></li>
    <li class="breadcrumb-item active">Kirim kiritish</li>
@endsection

@push('styles')
<style>
.qator-row td { vertical-align: middle; }
.del-qator { cursor: pointer; color: #dc3545; }
</style>
@endpush

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#14532d,#16a34a)">
        <h6 class="mb-0 text-white fw-bold">
            <i class="bi bi-box-arrow-in-down me-2"></i>
            {{ $taminotchi->nomi }} — Kirim kiritish
        </h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('taminotchi.kirim.store',$taminotchi) }}" id="kirim-form">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Hujjat raqami (Schyot-faktura)</label>
                    <input type="text" name="hujjat_raqam" class="form-control" placeholder="SF-2025-001">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Kirim sanasi <span class="text-danger">*</span></label>
                    <input type="date" name="kirim_sana" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control" placeholder="Ixtiyoriy...">
                </div>
            </div>

            {{-- Tovarlar jadvali --}}
            <h6 class="fw-bold mb-2">Tovarlar ro'yxati</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm border" id="qatorlar-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:35%">Tovar nomi <span class="text-danger">*</span></th>
                            <th style="width:20%">Katalogdan</th>
                            <th style="width:10%">Miqdor</th>
                            <th style="width:10%">Birlik</th>
                            <th style="width:15%">Narx (so'm)</th>
                            <th style="width:10%">Jami</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="qatorlar-body">
                        <tr class="qator-row">
                            <td><input type="text" name="qatorlar[0][nomi]" class="form-control form-control-sm" required placeholder="Tovar nomi"></td>
                            <td>
                                <select class="form-select form-select-sm katalog-select" data-idx="0">
                                    <option value="">— Tanlang —</option>
                                    @foreach($tovarlar as $tv)
                                    <option value="{{ $tv->id }}" data-nomi="{{ $tv->nomi }}" data-narx="{{ $tv->sotish_narx }}" data-birlik="{{ $tv->birlik }}">
                                        {{ $tv->nomi }}
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="qatorlar[0][tovar_id]" class="tovar-id-input">
                            </td>
                            <td><input type="number" name="qatorlar[0][miqdor]" class="form-control form-control-sm miqdor-input" value="1" min="0.001" step="0.001" required></td>
                            <td><input type="text" name="qatorlar[0][birlik]" class="form-control form-control-sm" value="dona"></td>
                            <td><input type="number" name="qatorlar[0][narx]" class="form-control form-control-sm narx-input" min="0" step="100" required></td>
                            <td class="fw-bold text-end jami-td">0</td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="fw-bold text-end">Jami:</td>
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
                <button type="submit" class="btn btn-success fw-bold px-4">
                    <i class="bi bi-check2 me-1"></i>Kirimni saqlash
                </button>
                <a href="{{ route('taminotchi.show',$taminotchi) }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var qatorCount = 1;

function qatorQosh() {
    var idx = qatorCount++;
    var tbody = document.getElementById('qatorlar-body');
    var tovarOptions = document.querySelector('.katalog-select').innerHTML;
    var tr = document.createElement('tr');
    tr.className = 'qator-row';
    tr.innerHTML = `
        <td><input type="text" name="qatorlar[${idx}][nomi]" class="form-control form-control-sm" required placeholder="Tovar nomi"></td>
        <td>
            <select class="form-select form-select-sm katalog-select" data-idx="${idx}">
                ${tovarOptions}
            </select>
            <input type="hidden" name="qatorlar[${idx}][tovar_id]" class="tovar-id-input">
        </td>
        <td><input type="number" name="qatorlar[${idx}][miqdor]" class="form-control form-control-sm miqdor-input" value="1" min="0.001" step="0.001" required></td>
        <td><input type="text" name="qatorlar[${idx}][birlik]" class="form-control form-control-sm" value="dona"></td>
        <td><input type="number" name="qatorlar[${idx}][narx]" class="form-control form-control-sm narx-input" min="0" step="100" required></td>
        <td class="fw-bold text-end jami-td">0</td>
        <td><i class="bi bi-x-circle del-qator" onclick="qatorOchir(this)"></i></td>
    `;
    tbody.appendChild(tr);
    jamiYangilash();
    bindEvents(tr);
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

function bindEvents(row) {
    row.querySelectorAll('.miqdor-input,.narx-input').forEach(function(inp) {
        inp.addEventListener('input', jamiYangilash);
    });
    var sel = row.querySelector('.katalog-select');
    if (sel) {
        sel.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            if (opt.value) {
                var nomiInput = this.closest('tr').querySelector('input[type=text]');
                var narxInput = this.closest('tr').querySelector('.narx-input');
                var birlikInput = this.closest('tr').querySelectorAll('input[type=text]')[1];
                var idInput = this.closest('td').querySelector('.tovar-id-input');
                nomiInput.value = opt.dataset.nomi;
                narxInput.value = opt.dataset.narx;
                if (birlikInput) birlikInput.value = opt.dataset.birlik || 'dona';
                if (idInput) idInput.value = opt.value;
                jamiYangilash();
            }
        });
    }
}

// Boshlang'ich qatorga event ulash
document.querySelectorAll('.qator-row').forEach(function(tr) { bindEvents(tr); });
</script>
@endpush
