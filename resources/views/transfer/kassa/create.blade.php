@extends('layouts.app')
@section('title', 'Yangi kassa transferi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('transfer.index') }}">Transferlar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('transfer.kassa.index') }}">Kassa transferlari</a></li>
    <li class="breadcrumb-item active">Yangi</li>
@endsection
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2" style="background:linear-gradient(135deg,#1e3a5f,#2563eb)">
        <h6 class="mb-0 text-white fw-bold"><i class="bi bi-cash-coin me-2"></i>Yangi kassa transferi</h6>
    </div>
    <div class="card-body">
        @if($errors->has('general'))
        <div class="alert alert-danger py-2">{{ $errors->first('general') }}</div>
        @endif
        <form method="POST" action="{{ route('transfer.kassa.store') }}" id="kassa-transfer-form">
            @csrf
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Jo'natuvchi filial <span class="text-danger">*</span></label>
                    <select name="from_filial_id" class="form-select" required id="from-fil" onchange="kassaFilter('from-kas',this.value)">
                        <option value="">— Tanlang —</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ $f->id==$mening_filial_id?'selected':'' }}>{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Jo'natuvchi kassa <span class="text-danger">*</span></label>
                    <select name="from_kassa_id" id="from-kas" class="form-select" required>
                        <option value="">— Kassa tanlang —</option>
                        @foreach($kassalar as $fId => $kassaList)
                            @foreach($kassaList as $k)
                            <option value="{{ $k->id }}" data-filial="{{ $fId }}">{{ $k->nomi }} ({{ number_format($k->qoldiq,0,'.',' ') }} so'm)</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Qabul qiluvchi filial <span class="text-danger">*</span></label>
                    <select name="to_filial_id" class="form-select" required onchange="kassaFilter('to-kas',this.value)">
                        <option value="">— Tanlang —</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Qabul qiluvchi kassa <span class="text-danger">*</span></label>
                    <select name="to_kassa_id" id="to-kas" class="form-select" required>
                        <option value="">— Kassa tanlang —</option>
                        @foreach($kassalar as $fId => $kassaList)
                            @foreach($kassaList as $k)
                            <option value="{{ $k->id }}" data-filial="{{ $fId }}">{{ $k->nomi }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Valyuta</label>
                    <div class="d-flex gap-1 flex-wrap" id="valyuta-tabs">
                        @foreach(['UZS'=>"so'm",'USD'=>'$','EUR'=>'€','RUB'=>'₽'] as $kod=>$belgi)
                        <button type="button" class="btn btn-sm {{ $kod==='UZS'?'btn-primary':'btn-outline-secondary' }} valyuta-btn"
                            data-kod="{{ $kod }}" data-kurs="{{ $kurslar[$kod] ?? 1 }}"
                            onclick="valyutaTanla(this)">{{ $kod }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="valyuta" id="valyuta-val" value="UZS">
                    <input type="hidden" name="kurs"    id="kurs-val"    value="1">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Summa <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="summa" id="summa-inp" class="form-control text-end fw-bold"
                               placeholder="0" inputmode="numeric" autocomplete="off"
                               oninput="formatSumma(this)" required>
                        <span class="input-group-text" id="summa-birlik">so'm</span>
                    </div>
                    <div id="uzs-hint" class="text-muted small mt-1" style="display:none"></div>
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium">Sana <span class="text-danger">*</span></label>
                    <input type="date" name="sana" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Sabab</label>
                    <input type="text" name="sabab" class="form-control" placeholder="Transfer sababi...">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Izoh</label>
                    <input type="text" name="izoh" class="form-control" placeholder="Ixtiyoriy...">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-send me-1"></i>Transferni yuborish
                    </button>
                    <a href="{{ route('transfer.kassa.index') }}" class="btn btn-outline-secondary">Bekor</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
@push('scripts')
<script>
var joriyKurs = 1;
function kassaFilter(selId, filialId) {
    var sel = document.getElementById(selId);
    sel.querySelectorAll('option').forEach(function(o){
        if(!o.value) return;
        o.style.display = o.dataset.filial==filialId ? '' : 'none';
    });
    sel.value = '';
}
function valyutaTanla(btn) {
    document.querySelectorAll('.valyuta-btn').forEach(b=>{ b.className='btn btn-sm btn-outline-secondary valyuta-btn'; });
    btn.className='btn btn-sm btn-primary valyuta-btn';
    document.getElementById('valyuta-val').value = btn.dataset.kod;
    joriyKurs = parseFloat(btn.dataset.kurs) || 1;
    document.getElementById('kurs-val').value = joriyKurs;
    document.getElementById('summa-birlik').textContent = btn.dataset.kod;
    kursHint();
}
function formatSumma(inp) {
    var raw = inp.value.replace(/[^0-9]/g,'');
    inp.dataset.raw = raw;
    inp.value = raw ? parseInt(raw).toLocaleString('uz-UZ').replace(/,/g,' ') : '';
    kursHint();
}
function kursHint() {
    var hint = document.getElementById('uzs-hint');
    var raw = parseInt(document.getElementById('summa-inp').dataset.raw || 0);
    var valyuta = document.getElementById('valyuta-val').value;
    if (valyuta !== 'UZS' && raw > 0) {
        hint.style.display = 'block';
        hint.textContent = '≈ ' + (raw*joriyKurs).toLocaleString('uz-UZ') + " so'm";
    } else { hint.style.display = 'none'; }
}
document.getElementById('kassa-transfer-form').addEventListener('submit', function(){
    var inp = document.getElementById('summa-inp');
    inp.value = inp.dataset.raw || inp.value.replace(/[^0-9]/g,'');
});
document.addEventListener('DOMContentLoaded', function(){
    kassaFilter('from-kas', document.getElementById('from-fil').value);
});
</script>
@endpush
