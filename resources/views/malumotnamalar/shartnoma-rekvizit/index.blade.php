@extends('layouts.app')
@section('title','Shartnoma rekvizitlari')
@section('content')
<div class="container-fluid py-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Shartnoma rekvizitlari</h5>
  @if(Auth::user()->isAdmin())
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Qo'shish</button>
  @endif
</div>

@if(session('muvaffaqiyat'))<div class="alert alert-success py-2">{{ session('muvaffaqiyat') }}</div>@endif
@if(session('xato'))<div class="alert alert-danger py-2">{{ session('xato') }}</div>@endif

<div class="card shadow-sm">
<table class="table table-sm table-hover mb-0">
<thead class="table-light"><tr>
  <th>#</th><th>Nomi</th><th>Prefiks</th><th>Format</th><th>Keyingi №</th><th>Imzochi</th><th>Asosiy</th><th>Holat</th><th></th>
</tr></thead>
<tbody>
@forelse($rekvizitlar as $r)
<tr>
  <td>{{ $loop->iteration }}</td>
  <td>{{ $r->nomi }}<br><small class="text-muted">{{ $r->filial->nomi ?? '' }}</small></td>
  <td><code>{{ $r->prefiks }}</code></td>
  <td><code>{{ $r->raqam_formati }}</code></td>
  <td>{{ $r->keyingi_raqam }}</td>
  <td>{{ $r->imzochi_ism }}<br><small class="text-muted">{{ $r->imzochi_lavozim }}</small></td>
  <td>@if($r->asosiy)<span class="badge bg-success">Ha</span>@endif</td>
  <td><span class="badge bg-{{ $r->holat=='faol'?'success':'secondary' }}">{{ $r->holat }}</span></td>
  <td class="text-end">
    @if(Auth::user()->isAdmin())
    <button class="btn btn-xs btn-outline-secondary" onclick="editRekv({{ $r->id }},'{{ addslashes($r->nomi) }}',{{ $r->filial_id??'null' }},{{ $r->tashkilot_rekvizit_id??'null' }},'{{ $r->prefiks }}',{{ $r->keyingi_raqam }},'{{ $r->raqam_formati }}','{{ addslashes($r->imzochi_ism??'') }}','{{ addslashes($r->imzochi_lavozim??'') }}',{{ $r->asosiy?1:0 }},'{{ $r->holat }}')">Tahrir</button>
    <form method="POST" action="{{ route('malumotnamalar.shartnoma-rekvizit.destroy',$r->id) }}" class="d-inline" onsubmit="return confirm('O\'chirilsinmi?')">
      @csrf @method('DELETE') <button class="btn btn-xs btn-outline-danger">O'chir</button>
    </form>
    @endif
  </td>
</tr>
@empty
<tr><td colspan="9" class="text-center text-muted py-3">Rekvizitlar mavjud emas</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog modal-lg"><div class="modal-content">
<form method="POST" action="{{ route('malumotnamalar.shartnoma-rekvizit.store') }}">@csrf
<div class="modal-header"><h6 class="modal-title">Yangi shartnoma rekviziti</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
  <div class="row g-2">
    <div class="col-12"><label class="form-label small">Nomi *</label><input name="nomi" class="form-control form-control-sm" required></div>
    <div class="col-6"><label class="form-label small">Filial</label>
      <select name="filial_id" class="form-select form-select-sm"><option value="">— Hammasi uchun —</option>
      @foreach($filiallar as $f)<option value="{{ $f->id }}">{{ $f->nomi }}</option>@endforeach
      </select>
    </div>
    <div class="col-6"><label class="form-label small">Tashkilot rekviziti</label>
      <select name="tashkilot_rekvizit_id" class="form-select form-select-sm"><option value="">— Tanlanmagan —</option>
      @foreach($tashkilotlar as $t)<option value="{{ $t->id }}">{{ $t->nomi }}</option>@endforeach
      </select>
    </div>
    <div class="col-3"><label class="form-label small">Prefiks</label><input name="prefiks" class="form-control form-control-sm" placeholder="S-2025"></div>
    <div class="col-3"><label class="form-label small">Keyingi №</label><input name="keyingi_raqam" type="number" class="form-control form-control-sm" value="1" min="1"></div>
    <div class="col-6"><label class="form-label small">Raqam formati</label><input name="raqam_formati" class="form-control form-control-sm" value="{PREFIX}-{RAQAM}"></div>
    <div class="col-6"><label class="form-label small">Imzochi F.I.O.</label><input name="imzochi_ism" class="form-control form-control-sm"></div>
    <div class="col-6"><label class="form-label small">Imzochi lavozimi</label><input name="imzochi_lavozim" class="form-control form-control-sm"></div>
    <div class="col-6"><div class="form-check mt-3"><input name="asosiy" value="1" type="checkbox" class="form-check-input" id="a_asosiy"><label class="form-check-label small" for="a_asosiy">Asosiy</label></div></div>
    <input type="hidden" name="holat" value="faol">
  </div>
</div>
<div class="modal-footer"><button class="btn btn-sm btn-primary">Saqlash</button></div>
</form>
</div></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog modal-lg"><div class="modal-content">
<form method="POST" id="editForm">@csrf @method('PUT')
<div class="modal-header"><h6 class="modal-title">Tahrirlash</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
  <div class="row g-2">
    <div class="col-12"><label class="form-label small">Nomi *</label><input name="nomi" id="e_nomi" class="form-control form-control-sm" required></div>
    <div class="col-6"><label class="form-label small">Filial</label>
      <select name="filial_id" id="e_filial" class="form-select form-select-sm"><option value="">— Hammasi uchun —</option>
      @foreach($filiallar as $f)<option value="{{ $f->id }}">{{ $f->nomi }}</option>@endforeach
      </select>
    </div>
    <div class="col-6"><label class="form-label small">Tashkilot rekviziti</label>
      <select name="tashkilot_rekvizit_id" id="e_tash" class="form-select form-select-sm"><option value="">— Tanlanmagan —</option>
      @foreach($tashkilotlar as $t)<option value="{{ $t->id }}">{{ $t->nomi }}</option>@endforeach
      </select>
    </div>
    <div class="col-3"><label class="form-label small">Prefiks</label><input name="prefiks" id="e_prefiks" class="form-control form-control-sm"></div>
    <div class="col-3"><label class="form-label small">Keyingi №</label><input name="keyingi_raqam" id="e_raqam" type="number" class="form-control form-control-sm" min="1"></div>
    <div class="col-6"><label class="form-label small">Format</label><input name="raqam_formati" id="e_format" class="form-control form-control-sm"></div>
    <div class="col-6"><label class="form-label small">Imzochi F.I.O.</label><input name="imzochi_ism" id="e_imz" class="form-control form-control-sm"></div>
    <div class="col-6"><label class="form-label small">Imzochi lavozimi</label><input name="imzochi_lavozim" id="e_lav" class="form-control form-control-sm"></div>
    <div class="col-4"><div class="form-check mt-3"><input name="asosiy" value="1" type="checkbox" id="e_asosiy" class="form-check-input"><label class="form-check-label small" for="e_asosiy">Asosiy</label></div></div>
    <div class="col-4"><label class="form-label small">Holat</label><select name="holat" id="e_holat" class="form-select form-select-sm"><option value="faol">Faol</option><option value="nofaol">Nofaol</option></select></div>
  </div>
</div>
<div class="modal-footer"><button class="btn btn-sm btn-primary">Saqlash</button></div>
</form>
</div></div></div>

<script>
function editRekv(id,nomi,filial,tash,prefiks,raqam,format,imz,lav,asosiy,holat){
  document.getElementById('editForm').action='/malumotnamalar/shartnoma-rekvizit/'+id;
  document.getElementById('e_nomi').value=nomi;
  document.getElementById('e_filial').value=filial||'';
  document.getElementById('e_tash').value=tash||'';
  document.getElementById('e_prefiks').value=prefiks||'';
  document.getElementById('e_raqam').value=raqam;
  document.getElementById('e_format').value=format;
  document.getElementById('e_imz').value=imz;
  document.getElementById('e_lav').value=lav;
  document.getElementById('e_asosiy').checked=asosiy==1;
  document.getElementById('e_holat').value=holat;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
