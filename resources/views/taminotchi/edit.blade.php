@extends('layouts.app')
@section('title', "Ta'minotchini tahrirlash")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.index') }}">Ta'minotchilar</a></li>
    <li class="breadcrumb-item"><a href="{{ route('taminotchi.show',$taminotchi) }}">{{ $taminotchi->nomi }}</a></li>
    <li class="breadcrumb-item active">Tahrirlash</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 d-flex align-items-center gap-2" style="background:linear-gradient(135deg,#78350f,#f59e0b)">
                <i class="bi bi-truck text-white fs-5"></i>
                <h6 class="mb-0 text-white fw-bold">Ta'minotchini tahrirlash</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('taminotchi.update', $taminotchi) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium">Tashkilot nomi <span class="text-danger">*</span></label>
                            <input type="text" name="nomi" class="form-control @error('nomi') is-invalid @enderror"
                                   value="{{ old('nomi', $taminotchi->nomi) }}" placeholder="Masalan: Iqtisod Servis MChJ" autofocus>
                            @error('nomi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Kontakt shaxs</label>
                            <input type="text" name="kontakt_shaxs" class="form-control"
                                   value="{{ old('kontakt_shaxs', $taminotchi->kontakt_shaxs) }}" placeholder="F.I.Sh.">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Telefon</label>
                            <input type="text" name="telefon" class="form-control"
                                   value="{{ old('telefon', $taminotchi->telefon) }}" placeholder="+998 90 000 00 00">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Qo'shimcha telefon</label>
                            <input type="text" name="telefon2" class="form-control"
                                   value="{{ old('telefon2', $taminotchi->telefon2) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">INN raqami</label>
                            <input type="text" name="inn" class="form-control"
                                   value="{{ old('inn', $taminotchi->inn) }}" placeholder="9 yoki 14 raqam">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Manzil</label>
                            <input type="text" name="manzil" class="form-control"
                                   value="{{ old('manzil', $taminotchi->manzil) }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Bank hisob raqami</label>
                            <input type="text" name="bank_hisob" class="form-control"
                                   value="{{ old('bank_hisob', $taminotchi->bank_hisob) }}" placeholder="20 ta raqam">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Bank nomi</label>
                            <input type="text" name="bank_nomi" class="form-control"
                                   value="{{ old('bank_nomi', $taminotchi->bank_nomi) }}">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-medium">MFO</label>
                            <input type="text" name="mfo" class="form-control"
                                   value="{{ old('mfo', $taminotchi->mfo) }}" placeholder="5 ta raqam">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-medium">Holat</label>
                            <select name="holat" class="form-select">
                                <option value="faol" {{ old('holat',$taminotchi->holat)==='faol' ? 'selected' : '' }}>Faol</option>
                                <option value="nofaol" {{ old('holat',$taminotchi->holat)==='nofaol' ? 'selected' : '' }}>Nofaol</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label fw-medium">Asosiy valyuta</label>
                            <select name="asosiy_valyuta" class="form-select">
                                <option value="UZS" {{ old('asosiy_valyuta',$taminotchi->asosiy_valyuta)==='UZS' ? 'selected' : '' }}>UZS — So'm</option>
                                <option value="USD" {{ old('asosiy_valyuta',$taminotchi->asosiy_valyuta)==='USD' ? 'selected' : '' }}>USD — Dollar</option>
                            </select>
                            <div class="form-text">To'lov kiritishda shu valyuta avtomatik tanlanadi</div>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Filial</label>
                            <select name="filial_id" class="form-select">
                                <option value="">Barcha filiallar</option>
                                @foreach($filiallar as $f)
                                <option value="{{ $f->id }}" {{ old('filial_id',$taminotchi->filial_id)==$f->id?'selected':'' }}>
                                    {{ $f->nomi }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label fw-medium">Izoh</label>
                            <textarea name="izoh" class="form-control" rows="2">{{ old('izoh', $taminotchi->izoh) }}</textarea>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-warning fw-bold">
                                <i class="bi bi-check2 me-1"></i>Saqlash
                            </button>
                            <a href="{{ route('taminotchi.show',$taminotchi) }}" class="btn btn-outline-secondary">Bekor</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
