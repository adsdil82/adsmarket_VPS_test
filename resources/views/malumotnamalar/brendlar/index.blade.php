@extends('layouts.app')
@section('title','Brendlar')
@section('content')
<div class="container-fluid py-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Brendlar</h5>
  @if(Auth::user()->isAdmin() || Auth::user()->isMenejer())
  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Qo'shish</button>
  @endif
</div>

@if(session('muvaffaqiyat'))<div class="alert alert-success py-2">{{ session('muvaffaqiyat') }}</div>@endif
@if(session('xato'))<div class="alert alert-danger py-2">{{ session('xato') }}</div>@endif

<div class="card shadow-sm">
<table class="table table-sm table-hover mb-0">
<thead class="table-light"><tr>
  <th>#</th><th>Nomi</th><th>Kod</th><th>Mamlakat</th><th>Holat</th><th>Tartib</th><th></th>
</tr></thead>
<tbody>
@forelse($brendlar as $b)
<tr>
  <td>{{ $loop->iteration }}</td>
  <td>{{ $b->nomi }}</td>
  <td><code>{{ $b->kod }}</code></td>
  <td>{{ $b->mamlakat }}</td>
  <td><span class="badge bg-{{ $b->holat=='faol'?'success':'secondary' }}">{{ $b->holat }}</span></td>
  <td>{{ $b->sort_order }}</td>
  <td class="text-end">
    @if(Auth::user()->isAdmin() || Auth::user()->isMenejer())
    <button class="btn btn-xs btn-outline-secondary" onclick="editBrend({{ $b->id }},'{{ addslashes($b->nomi) }}','{{ $b->kod }}','{{ $b->mamlakat }}','{{ $b->holat }}',{{ $b->sort_order }})">Tahrir</button>
    @endif
    @if(Auth::user()->isAdmin())
    <form method="POST" action="{{ route('malumotnamalar.brendlar.destroy',$b->id) }}" class="d-inline" onsubmit="return confirm('O\'chirilsinmi?')">
      @csrf @method('DELETE') <button class="btn btn-xs btn-outline-danger">O'chir</button>
    </form>
    @endif
  </td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-3">Brendlar mavjud emas</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog"><div class="modal-content">
<form method="POST" action="{{ route('malumotnamalar.brendlar.store') }}">@csrf
<div class="modal-header"><h6 class="modal-title">Yangi brend</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
  <div class="mb-2"><label class="form-label small">Nomi *</label><input name="nomi" class="form-control form-control-sm" required></div>
  <div class="mb-2"><label class="form-label small">Kod</label><input name="kod" class="form-control form-control-sm" placeholder="SAMSUNG"></div>
  <div class="mb-2"><label class="form-label small">Mamlakat</label><input name="mamlakat" class="form-control form-control-sm"></div>
  <div class="mb-2"><label class="form-label small">Tartib raqami</label><input name="sort_order" type="number" class="form-control form-control-sm" value="100"></div>
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
  <div class="mb-2"><label class="form-label small">Nomi *</label><input name="nomi" id="e_nomi" class="form-control form-control-sm" required></div>
  <div class="mb-2"><label class="form-label small">Kod</label><input name="kod" id="e_kod" class="form-control form-control-sm"></div>
  <div class="mb-2"><label class="form-label small">Mamlakat</label><input name="mamlakat" id="e_mamlakat" class="form-control form-control-sm"></div>
  <div class="mb-2"><label class="form-label small">Holat</label>
    <select name="holat" id="e_holat" class="form-select form-select-sm">
      <option value="faol">Faol</option><option value="nofaol">Nofaol</option>
    </select>
  </div>
  <div class="mb-2"><label class="form-label small">Tartib</label><input name="sort_order" id="e_sort" type="number" class="form-control form-control-sm"></div>
</div>
<div class="modal-footer"><button class="btn btn-sm btn-primary">Saqlash</button></div>
</form>
</div></div></div>

<script>
function editBrend(id,nomi,kod,mamlakat,holat,sort){
  document.getElementById('editForm').action='/malumotnamalar/brendlar/'+id;
  document.getElementById('e_nomi').value=nomi;
  document.getElementById('e_kod').value=kod||'';
  document.getElementById('e_mamlakat').value=mamlakat||'';
  document.getElementById('e_holat').value=holat;
  document.getElementById('e_sort').value=sort;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
