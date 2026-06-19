@extends('layouts.app')
@section('title','Statuslar va sabablar')
@section('content')
<div class="container-fluid py-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Statuslar va sabablar</h5>
  @if(Auth::user()->isAdmin())
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Qo'shish</button>
  @endif
</div>

@if(session('muvaffaqiyat'))<div class="alert alert-success py-2">{{ session('muvaffaqiyat') }}</div>@endif
@if(session('xato'))<div class="alert alert-danger py-2">{{ session('xato') }}</div>@endif

@forelse($grouped as $modul => $statuslar)
<div class="card mb-3 shadow-sm">
<div class="card-header d-flex justify-content-between align-items-center py-2">
  <strong class="text-uppercase small">{{ $modul }}</strong>
  <span class="badge bg-secondary">{{ $statuslar->count() }}</span>
</div>
<table class="table table-sm mb-0">
<thead class="table-light"><tr><th>Tur</th><th>Kod</th><th>Nomi</th><th>Rang</th><th>Tizim</th><th>Holat</th><th></th></tr></thead>
<tbody>
@foreach($statuslar as $s)
<tr>
  <td><span class="badge bg-info text-dark">{{ $s->tur }}</span></td>
  <td><code>{{ $s->kod }}</code></td>
  <td>
    <span class="badge" style="background-color:{{ in_array($s->rang,['primary','secondary','success','danger','warning','info','dark']) ? '' : $s->rang }};{{ in_array($s->rang,['primary','secondary','success','danger','warning','info','dark']) ? '' : '' }}"
      class="badge bg-{{ in_array($s->rang,['primary','secondary','success','danger','warning','info','dark']) ? $s->rang : 'secondary' }}">
      {{ $s->nomi }}
    </span>
  </td>
  <td><code>{{ $s->rang }}</code></td>
  <td>@if($s->tizim_holati)<span class="badge bg-warning text-dark">Tizim</span>@endif</td>
  <td><span class="badge bg-{{ $s->holat=='faol'?'success':'secondary' }}">{{ $s->holat }}</span></td>
  <td class="text-end">
    @if(Auth::user()->isAdmin() && !$s->tizim_holati)
    <button class="btn btn-xs btn-outline-secondary" onclick="editStatus({{ $s->id }},'{{ addslashes($s->nomi) }}','{{ $s->rang }}','{{ $s->holat }}',{{ $s->sort_order }})">Tahrir</button>
    <form method="POST" action="{{ route('malumotnamalar.statuslar.destroy',$s->id) }}" class="d-inline" onsubmit="return confirm('O\'chirilsinmi?')">
      @csrf @method('DELETE') <button class="btn btn-xs btn-outline-danger">O'chir</button>
    </form>
    @endif
  </td>
</tr>
@endforeach
</tbody>
</table>
</div>
@empty
<div class="alert alert-info">Statuslar mavjud emas. "Qo'shish" tugmasi orqali yangi status qo'shing.</div>
@endforelse
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('malumotnamalar.statuslar.store') }}">@csrf
<div class="modal-header"><h6 class="modal-title">Yangi status / sabab</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
  <div class="row g-2">
    <div class="col-6"><label class="form-label small">Modul *</label>
      <input name="modul" class="form-control form-control-sm" list="modul_list" required>
      <datalist id="modul_list">
        @foreach($modullar as $m)<option value="{{ $m }}">@endforeach
        <option value="kredit"><option value="tolov"><option value="qaytarish"><option value="qurilma"><option value="umumiy">
      </datalist>
    </div>
    <div class="col-6"><label class="form-label small">Tur *</label>
      <select name="tur" class="form-select form-select-sm"><option value="status">Status</option><option value="sabab">Sabab</option><option value="holat">Holat</option></select>
    </div>
    <div class="col-4"><label class="form-label small">Kod *</label><input name="kod" class="form-control form-control-sm" required></div>
    <div class="col-8"><label class="form-label small">Nomi *</label><input name="nomi" class="form-control form-control-sm" required></div>
    <div class="col-4"><label class="form-label small">Rang</label>
      <select name="rang" class="form-select form-select-sm">
        <option value="secondary">secondary (kulrang)</option>
        <option value="primary">primary (ko'k)</option>
        <option value="success">success (yashil)</option>
        <option value="danger">danger (qizil)</option>
        <option value="warning">warning (sariq)</option>
        <option value="info">info (moviy)</option>
        <option value="dark">dark (qora)</option>
      </select>
    </div>
    <div class="col-4"><label class="form-label small">Tartib</label><input name="sort_order" type="number" class="form-control form-control-sm" value="100"></div>
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
    <div class="col-12"><label class="form-label small">Nomi *</label><input name="nomi" id="e_nomi" class="form-control form-control-sm" required></div>
    <div class="col-6"><label class="form-label small">Rang</label>
      <select name="rang" id="e_rang" class="form-select form-select-sm">
        <option value="secondary">secondary</option><option value="primary">primary</option>
        <option value="success">success</option><option value="danger">danger</option>
        <option value="warning">warning</option><option value="info">info</option><option value="dark">dark</option>
      </select>
    </div>
    <div class="col-3"><label class="form-label small">Holat</label><select name="holat" id="e_holat" class="form-select form-select-sm"><option value="faol">Faol</option><option value="nofaol">Nofaol</option></select></div>
    <div class="col-3"><label class="form-label small">Tartib</label><input name="sort_order" id="e_sort" type="number" class="form-control form-control-sm"></div>
  </div>
</div>
<div class="modal-footer"><button class="btn btn-sm btn-primary">Saqlash</button></div>
</form>
</div></div></div>

<script>
function editStatus(id,nomi,rang,holat,sort){
  document.getElementById('editForm').action='/malumotnamalar/statuslar/'+id;
  document.getElementById('e_nomi').value=nomi;
  document.getElementById('e_rang').value=rang;
  document.getElementById('e_holat').value=holat;
  document.getElementById('e_sort').value=sort;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
