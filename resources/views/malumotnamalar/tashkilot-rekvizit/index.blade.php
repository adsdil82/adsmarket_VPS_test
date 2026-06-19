@extends('layouts.app')
@section('title','Tashkilot rekvizitlari')
@section('content')
<div class="container-fluid py-3">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Tashkilot rekvizitlari</h5>
  @if(Auth::user()->isAdmin())
  <a href="{{ route('malumotnamalar.tashkilot-rekvizit.create') }}" class="btn btn-sm btn-primary">+ Qo'shish</a>
  @endif
</div>

@if(session('muvaffaqiyat'))<div class="alert alert-success py-2">{{ session('muvaffaqiyat') }}</div>@endif
@if(session('xato'))<div class="alert alert-danger py-2">{{ session('xato') }}</div>@endif

<div class="card shadow-sm">
<table class="table table-sm table-hover mb-0">
<thead class="table-light"><tr>
  <th>#</th><th>Nomi</th><th>STIR</th><th>Filial</th><th>Telefon</th><th>Asosiy</th><th>Holat</th><th></th>
</tr></thead>
<tbody>
@forelse($rekvizitlar as $r)
<tr>
  <td>{{ $loop->iteration }}</td>
  <td><strong>{{ $r->nomi }}</strong>@if($r->qisqa_nomi)<br><small class="text-muted">{{ $r->qisqa_nomi }}</small>@endif</td>
  <td>{{ $r->stir }}</td>
  <td>{{ $r->filial->nomi ?? '—' }}</td>
  <td>{{ $r->telefon }}</td>
  <td>@if($r->asosiy)<span class="badge bg-success">Ha</span>@endif</td>
  <td><span class="badge bg-{{ $r->holat=='faol'?'success':'secondary' }}">{{ $r->holat }}</span></td>
  <td class="text-end">
    @if(Auth::user()->isAdmin())
    <a href="{{ route('malumotnamalar.tashkilot-rekvizit.edit',$r->id) }}" class="btn btn-xs btn-outline-secondary">Tahrir</a>
    <form method="POST" action="{{ route('malumotnamalar.tashkilot-rekvizit.destroy',$r->id) }}" class="d-inline" onsubmit="return confirm('O\'chirilsinmi?')">
      @csrf @method('DELETE') <button class="btn btn-xs btn-outline-danger">O'chir</button>
    </form>
    @endif
  </td>
</tr>
@empty
<tr><td colspan="8" class="text-center text-muted py-3">Rekvizitlar mavjud emas</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@endsection
