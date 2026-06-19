@extends('layouts.app')
@section('title', isset($rekvizit) ? 'Rekvizit tahrirlash' : 'Yangi rekvizit')
@section('content')
<div class="container py-3" style="max-width:800px">
<h5 class="mb-3">{{ isset($rekvizit) ? 'Rekvizit tahrirlash' : 'Yangi tashkilot rekviziti' }}</h5>

@if($errors->any())<div class="alert alert-danger py-2">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<form method="POST" action="{{ isset($rekvizit) ? route('malumotnamalar.tashkilot-rekvizit.update',$rekvizit->id) : route('malumotnamalar.tashkilot-rekvizit.store') }}">
@csrf @if(isset($rekvizit)) @method('PUT') @endif

<div class="card mb-3"><div class="card-header small fw-bold">Asosiy ma'lumotlar</div><div class="card-body">
  <div class="row g-2">
    <div class="col-8"><label class="form-label small">To'liq nomi *</label><input name="nomi" class="form-control form-control-sm" value="{{ old('nomi',$rekvizit->nomi??'') }}" required></div>
    <div class="col-4"><label class="form-label small">Qisqa nomi</label><input name="qisqa_nomi" class="form-control form-control-sm" value="{{ old('qisqa_nomi',$rekvizit->qisqa_nomi??'') }}"></div>
    <div class="col-4"><label class="form-label small">STIR</label><input name="stir" class="form-control form-control-sm" value="{{ old('stir',$rekvizit->stir??'') }}"></div>
    <div class="col-4"><label class="form-label small">MFO</label><input name="mfo" class="form-control form-control-sm" value="{{ old('mfo',$rekvizit->mfo??'') }}"></div>
    <div class="col-4"><label class="form-label small">Filial</label>
      <select name="filial_id" class="form-select form-select-sm">
        <option value="">— Tanlanmagan —</option>
        @foreach($filiallar as $f)<option value="{{ $f->id }}" {{ old('filial_id',($rekvizit->filial_id??''))==$f->id?'selected':'' }}>{{ $f->nomi }}</option>@endforeach
      </select>
    </div>
  </div>
</div></div>

<div class="card mb-3"><div class="card-header small fw-bold">Bank ma'lumotlari</div><div class="card-body">
  <div class="row g-2">
    <div class="col-12"><label class="form-label small">Bank nomi</label><input name="bank_nomi" class="form-control form-control-sm" value="{{ old('bank_nomi',$rekvizit->bank_nomi??'') }}"></div>
    <div class="col-6"><label class="form-label small">Hisob raqam</label><input name="hisob_raqam" class="form-control form-control-sm" value="{{ old('hisob_raqam',$rekvizit->hisob_raqam??'') }}"></div>
    <div class="col-6"><label class="form-label small">Tranzit hisob</label><input name="tranzit_hisob" class="form-control form-control-sm" value="{{ old('tranzit_hisob',$rekvizit->tranzit_hisob??'') }}"></div>
  </div>
</div></div>

<div class="card mb-3"><div class="card-header small fw-bold">Aloqa va manzil</div><div class="card-body">
  <div class="row g-2">
    <div class="col-6"><label class="form-label small">Telefon</label><input name="telefon" class="form-control form-control-sm" value="{{ old('telefon',$rekvizit->telefon??'') }}"></div>
    <div class="col-6"><label class="form-label small">Email</label><input name="email" type="email" class="form-control form-control-sm" value="{{ old('email',$rekvizit->email??'') }}"></div>
    <div class="col-6"><label class="form-label small">Yuridik manzil</label><textarea name="yuridik_manzil" class="form-control form-control-sm" rows="2">{{ old('yuridik_manzil',$rekvizit->yuridik_manzil??'') }}</textarea></div>
    <div class="col-6"><label class="form-label small">Haqiqiy manzil</label><textarea name="haqiqiy_manzil" class="form-control form-control-sm" rows="2">{{ old('haqiqiy_manzil',$rekvizit->haqiqiy_manzil??'') }}</textarea></div>
  </div>
</div></div>

<div class="card mb-3"><div class="card-header small fw-bold">Mas'ul shaxslar</div><div class="card-body">
  <div class="row g-2">
    <div class="col-6"><label class="form-label small">Direktor F.I.O.</label><input name="direktor_ism" class="form-control form-control-sm" value="{{ old('direktor_ism',$rekvizit->direktor_ism??'') }}"></div>
    <div class="col-6"><label class="form-label small">Bosh hisobchi F.I.O.</label><input name="hisobchi_ism" class="form-control form-control-sm" value="{{ old('hisobchi_ism',$rekvizit->hisobchi_ism??'') }}"></div>
    <div class="col-6"><label class="form-label small">Imzochi F.I.O.</label><input name="imzochi_ism" class="form-control form-control-sm" value="{{ old('imzochi_ism',$rekvizit->imzochi_ism??'') }}"></div>
    <div class="col-6"><label class="form-label small">Imzochi lavozimi</label><input name="imzochi_lavozim" class="form-control form-control-sm" value="{{ old('imzochi_lavozim',$rekvizit->imzochi_lavozim??'') }}"></div>
  </div>
</div></div>

<div class="row g-2 mb-3">
  <div class="col-auto">
    <div class="form-check">
      <input name="asosiy" value="1" type="checkbox" class="form-check-input" id="chk_asosiy"
        {{ old('asosiy', ($rekvizit->asosiy??false)) ? 'checked' : '' }}>
      <label class="form-check-label small" for="chk_asosiy">Asosiy rekvizit</label>
    </div>
  </div>
  @if(isset($rekvizit))
  <div class="col-auto">
    <select name="holat" class="form-select form-select-sm">
      <option value="faol" {{ ($rekvizit->holat=='faol')?'selected':'' }}>Faol</option>
      <option value="nofaol" {{ ($rekvizit->holat=='nofaol')?'selected':'' }}>Nofaol</option>
    </select>
  </div>
  @else
  <input type="hidden" name="holat" value="faol">
  @endif
</div>

<div class="d-flex gap-2">
  <button class="btn btn-primary btn-sm">Saqlash</button>
  <a href="{{ route('malumotnamalar.tashkilot-rekvizit.index') }}" class="btn btn-outline-secondary btn-sm">Bekor qilish</a>
</div>
</form>
</div>
@endsection
