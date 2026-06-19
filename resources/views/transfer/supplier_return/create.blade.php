@extends('layouts.app')
@section('title', "Yangi qaytarish")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.supplier-return.index') }}">Qaytarishlar</a></li>
    <li class="breadcrumb-item active">Yangi</li>
@endsection
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#374151,#6b7280)">
        <h6 class="mb-0 text-white fw-bold"><i class="bi bi-arrow-return-left me-2"></i>Ta'minotchiga qaytarish hujjati</h6>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('transfer.supplier-return.store') }}">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Ta'minotchi <span class="text-danger">*</span></label>
                    <select name="taminotchi_id" class="form-select" required>
                        <option value="">— Tanlang —</option>
                        @foreach($taminotchilar as $t)
                        <option value="{{ $t->id }}">{{ $t->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Ombor <span class="text-danger">*</span></label>
                    <select name="ombor_id" class="form-select" required>
                        <option value="">— Tanlang —</option>
                        @foreach($omborlar as $o)
                        <option value="{{ $o->id }}">{{ $o->filial->kod }} — {{ $o->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Sana <span class="text-danger">*</span></label>
                    <input type="date" name="sana" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-sm-8">
                    <label class="form-label fw-medium">Qaytarish sababi <span class="text-danger">*</span></label>
                    <input type="text" name="sabab" class="form-control" required minlength="5" placeholder="Sabab...">
                </div>
            </div>
            <h6 class="fw-bold mb-2">Qaytariladigan tovarlar</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm border" id="qatorlar-table">
                    <thead class="table-light">
                        <tr><th style="width:35%">Tovar</th><th>Miqdor</th><th>Narx</th><th>Sabab</th><th></th></tr>
                    </thead>
                    <tbody id="qatorlar-body">
                        <tr>
                            <td>
                                <select name="qatorlar[0][tovar_id]" class="form-select form-select-sm tovar-sel" onchange="nomniToLdur(this,0)">
                                    <option value="">— Tanlang —</option>
                                    @foreach($tovarlar as $tv)
                                    <option value="{{ $tv->id }}" data-nomi="{{ $tv->nomi }}" data-narx="{{ $tv->tan_narx }}" data-birlik="{{ $tv->birlik }}">{{ $tv->nomi }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="qatorlar[0][nomi]" id="nomi-0" class="form-control form-control-sm mt-1" placeholder="Yoki nomi kiriting" required>
                            </td>
                            <td><input type="number" name="qatorlar[0][miqdor]" class="form-control form-control-sm" min="0.001" step="0.001" value="1" required></td>
                            <td><input type="number" name="qatorlar[0][narx]" id="narx-0" class="form-control form-control-sm" min="0" step="100" required></td>
                            <td><input type="text" name="qatorlar[0][sabab]" class="form-control form-control-sm" placeholder="Sababini..."></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex gap-2 mb-4">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="qatorQosh()">
                    <i class="bi bi-plus-lg me-1"></i>Qator qo'shish
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary fw-bold px-4">
                    <i class="bi bi-save me-1"></i>Saqlash
                </button>
                <a href="{{ route('transfer.supplier-return.index') }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
var tovarOptsHtml = document.querySelector('.tovar-sel').innerHTML;
var qCount = 1;
function nomniToLdur(sel, idx) {
    var o = sel.options[sel.selectedIndex];
    if(o.value) {
        document.getElementById('nomi-'+idx).value = o.dataset.nomi;
        document.getElementById('narx-'+idx).value = o.dataset.narx;
    }
}
function qatorQosh() {
    var idx = qCount++;
    var tb = document.getElementById('qatorlar-body');
    var tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="qatorlar[${idx}][tovar_id]" class="form-select form-select-sm" onchange="nomniToLdur(this,${idx})">${tovarOptsHtml}</select>
            <input type="text" name="qatorlar[${idx}][nomi]" id="nomi-${idx}" class="form-control form-control-sm mt-1" placeholder="Tovar nomi" required>
        </td>
        <td><input type="number" name="qatorlar[${idx}][miqdor]" class="form-control form-control-sm" min="0.001" step="0.001" value="1" required></td>
        <td><input type="number" name="qatorlar[${idx}][narx]" id="narx-${idx}" class="form-control form-control-sm" min="0" step="100" required></td>
        <td><input type="text" name="qatorlar[${idx}][sabab]" class="form-control form-control-sm" placeholder="Sabab..."></td>
        <td><button type="button" style="color:#dc3545;border:none;background:none" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle"></i></button></td>
    `;
    tb.appendChild(tr);
}
function nomniToLdur(sel, idx) {
    var o = sel.options[sel.selectedIndex];
    if(o.value) {
        document.getElementById('nomi-'+idx).value = o.dataset.nomi;
        document.getElementById('narx-'+idx).value = o.dataset.narx;
    }
}
</script>
@endpush
