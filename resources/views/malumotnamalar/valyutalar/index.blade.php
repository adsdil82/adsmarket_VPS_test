@extends('layouts.app')
@section('title','Valyutalar')
@section('content')
<div class="container-fluid py-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Valyutalar</h5>
  <div class="d-flex gap-2">
    @if(Auth::user()->isAdmin())
    <form method="POST" action="{{ route('malumotnamalar.valyutalar.cbu-update') }}">@csrf
      <button class="btn btn-sm btn-success" title="cbu.uz rasmiy saytidan bugungi kurslarni avtomatik yuklash">
        <i class="bi bi-cloud-download me-1"></i>CBU dan yangilash
      </button>
    </form>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Qo'shish</button>
    @endif
  </div>
</div>

<div class="alert alert-light border py-2 small mb-3">
  <i class="bi bi-info-circle text-primary me-1"></i>
  <strong>CBU dan yangilash</strong> — Markaziy bank rasmiy saytidan (<code>cbu.uz</code>) bugungi rasmiy kurslarni avtomatik yuklaydi.
  UZS (so'm) kursi doim <strong>1</strong> bo'lib qoladi.
  Qo'lda tahrirlash uchun jadvaldagi <em>Tahrir</em> tugmasini bosing.
</div>

@if(session('muvaffaqiyat'))<div class="alert alert-success py-2">{{ session('muvaffaqiyat') }}</div>@endif
@if(session('xato'))<div class="alert alert-danger py-2">{{ session('xato') }}</div>@endif

<div class="card shadow-sm">
<table class="table table-sm table-hover mb-0">
<thead class="table-light"><tr>
  <th>#</th><th>Kod</th><th>Nomi</th><th>Belgi</th><th>Kurs (so'm)</th><th>Kurs sanasi</th><th>Asosiy</th><th>Holat</th><th></th>
</tr></thead>
<tbody>
@forelse($valyutalar as $v)
<tr>
  <td>{{ $loop->iteration }}</td>
  <td><strong>{{ $v->kod }}</strong></td>
  <td>{{ $v->nomi }}</td>
  <td>{{ $v->belgi }}</td>
  <td>{{ number_format($v->kurs,2,',',' ') }}</td>
  <td>{{ $v->kurs_sana ? $v->kurs_sana->format('d.m.Y') : '—' }}</td>
  <td>@if($v->asosiy)<span class="badge bg-success">Ha</span>@endif</td>
  <td><span class="badge bg-{{ $v->holat=='faol'?'success':'secondary' }}">{{ $v->holat }}</span></td>
  <td class="text-end">
    @if(Auth::user()->isAdmin())
    <button class="btn btn-xs btn-outline-secondary" onclick="editValyuta({{ $v->id }},'{{ $v->kod }}','{{ addslashes($v->nomi) }}','{{ $v->belgi }}',{{ $v->kurs }},'{{ $v->kurs_sana?$v->kurs_sana->format('Y-m-d'):'' }}',{{ $v->asosiy?1:0 }},'{{ $v->holat }}')">Tahrir</button>
    <form method="POST" action="{{ route('malumotnamalar.valyutalar.destroy',$v->id) }}" class="d-inline" onsubmit="return confirm('O\'chirilsinmi?')">
      @csrf @method('DELETE') <button class="btn btn-xs btn-outline-danger">O'chir</button>
    </form>
    @endif
  </td>
</tr>
@empty
<tr><td colspan="9" class="text-center text-muted py-3">Valyutalar mavjud emas</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('malumotnamalar.valyutalar.store') }}">@csrf
<div class="modal-header"><h6 class="modal-title">Yangi valyuta</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
  <div class="row g-2">
    <div class="col-4"><label class="form-label small">Kod *</label><input name="kod" class="form-control form-control-sm" placeholder="USD" required></div>
    <div class="col-8"><label class="form-label small">Nomi *</label><input name="nomi" class="form-control form-control-sm" placeholder="AQSh dollari" required></div>
    <div class="col-4"><label class="form-label small">Belgi</label><input name="belgi" class="form-control form-control-sm" placeholder="$"></div>
    <div class="col-4"><label class="form-label small">Kurs *</label><input name="kurs" type="number" step="0.0001" class="form-control form-control-sm" required></div>
    <div class="col-4"><label class="form-label small">Kurs sanasi</label><input name="kurs_sana" type="date" class="form-control form-control-sm"></div>
    <div class="col-12"><div class="form-check"><input name="asosiy" value="1" type="checkbox" class="form-check-input" id="a_asosiy"><label class="form-check-label small" for="a_asosiy">Asosiy valyuta</label></div></div>
  </div>
</div>
<div class="modal-footer"><button class="btn btn-sm btn-primary">Saqlash</button></div>
</form>
</div></div></div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" id="editForm">@csrf @method('PUT')
<div class="modal-header"><h6 class="modal-title">Tahrirlash</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
  <div class="row g-2">
    <div class="col-4"><label class="form-label small">Kod *</label><input name="kod" id="e_kod" class="form-control form-control-sm" required></div>
    <div class="col-8"><label class="form-label small">Nomi *</label><input name="nomi" id="e_nomi" class="form-control form-control-sm" required></div>
    <div class="col-4"><label class="form-label small">Belgi</label><input name="belgi" id="e_belgi" class="form-control form-control-sm"></div>
    <div class="col-4"><label class="form-label small">Kurs</label><input name="kurs" id="e_kurs" type="number" step="0.0001" class="form-control form-control-sm"></div>
    <div class="col-4"><label class="form-label small">Kurs sanasi</label><input name="kurs_sana" id="e_sana" type="date" class="form-control form-control-sm"></div>
    <div class="col-6"><div class="form-check mt-2"><input name="asosiy" value="1" type="checkbox" id="e_asosiy" class="form-check-input"><label class="form-check-label small" for="e_asosiy">Asosiy</label></div></div>
    <div class="col-6"><label class="form-label small">Holat</label><select name="holat" id="e_holat" class="form-select form-select-sm"><option value="faol">Faol</option><option value="nofaol">Nofaol</option></select></div>
  </div>
</div>
<div class="modal-footer"><button class="btn btn-sm btn-primary">Saqlash</button></div>
</form>
</div></div></div>

<script>
function editValyuta(id,kod,nomi,belgi,kurs,sana,asosiy,holat){
  document.getElementById('editForm').action='/malumotnamalar/valyutalar/'+id;
  document.getElementById('e_kod').value=kod;
  document.getElementById('e_nomi').value=nomi;
  document.getElementById('e_belgi').value=belgi||'';
  document.getElementById('e_kurs').value=kurs;
  document.getElementById('e_sana').value=sana;
  document.getElementById('e_asosiy').checked=asosiy==1;
  document.getElementById('e_holat').value=holat;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
