{{-- Mijoz forma partial — create va edit uchun --}}

<div class="row g-3">
    {{-- Filial --}}
    <div class="col-sm-6">
        <label class="form-label fw-medium">Filial <span class="text-danger">*</span></label>
        <select name="filial_id" class="form-select @error('filial_id') is-invalid @enderror"
                {{ count($filiallar) === 1 ? 'disabled' : '' }}>
            <option value="">— Tanlang —</option>
            @foreach($filiallar as $f)
                <option value="{{ $f->id }}"
                    {{ old('filial_id', $mijoz->filial_id ?? '') == $f->id ? 'selected' : '' }}>
                    {{ $f->nomi }}
                </option>
            @endforeach
        </select>
        @if(count($filiallar) === 1)
            <input type="hidden" name="filial_id" value="{{ $filiallar->first()->id }}">
        @endif
        @error('filial_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label class="form-label fw-medium">Holat</label>
        <select name="holat" class="form-select">
            <option value="faol"   {{ old('holat', $mijoz->holat ?? 'faol') === 'faol'   ? 'selected' : '' }}>AKTIV</option>
            <option value="nofaol" {{ old('holat', $mijoz->holat ?? '')     === 'nofaol' ? 'selected' : '' }}>PASSIV</option>
        </select>
    </div>

    {{-- ── F.I.O. ──────────────────────────────────────────────────── --}}
    <div class="col-sm-4">
        <label class="form-label fw-medium">Familiya <span class="text-danger">*</span></label>
        <input type="text" name="familiya" class="form-control @error('familiya') is-invalid @enderror"
               value="{{ old('familiya', $mijoz->familiya ?? '') }}" required>
        @error('familiya')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-4">
        <label class="form-label fw-medium">Ism <span class="text-danger">*</span></label>
        <input type="text" name="ism" class="form-control @error('ism') is-invalid @enderror"
               value="{{ old('ism', $mijoz->ism ?? '') }}" required>
        @error('ism')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-4">
        <label class="form-label fw-medium">Otasining ismi</label>
        <input type="text" name="otasining_ismi" class="form-control"
               value="{{ old('otasining_ismi', $mijoz->otasining_ismi ?? '') }}">
    </div>

    {{-- ── Kontakt ─────────────────────────────────────────────────── --}}
    <div class="col-sm-6">
        <label class="form-label fw-medium">Telefon <span class="text-danger">*</span></label>
        <input type="text" name="telefon" class="form-control @error('telefon') is-invalid @enderror"
               value="{{ old('telefon', $mijoz->telefon ?? '') }}"
               placeholder="+998 90 123 45 67" required>
        @error('telefon')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-sm-6">
        <label class="form-label fw-medium">Tug'ilgan sana</label>
        <input type="date" name="tug_sana" class="form-control @error('tug_sana') is-invalid @enderror"
               value="{{ old('tug_sana', isset($mijoz) ? $mijoz->tug_sana?->format('Y-m-d') : '') }}">
        @error('tug_sana')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- ── Passport ma'lumotlari ──────────────────────────────────── --}}
    <div class="col-sm-2">
        <label class="form-label fw-medium">Passport seriya</label>
        <input type="text" name="passport_seriya" class="form-control text-uppercase"
               value="{{ old('passport_seriya', $mijoz->passport_seriya ?? '') }}"
               placeholder="AA" maxlength="10">
    </div>
    <div class="col-sm-3">
        <label class="form-label fw-medium">Passport raqam</label>
        <input type="text" name="passport_raqam" class="form-control"
               value="{{ old('passport_raqam', $mijoz->passport_raqam ?? '') }}"
               placeholder="1234567" maxlength="20">
    </div>
    <div class="col-sm-3">
        <label class="form-label fw-medium">
            PINFL
            <small class="text-muted">(14 raqam)</small>
        </label>
        <input type="text" name="pinfl"
               class="form-control @error('pinfl') is-invalid @enderror"
               value="{{ old('pinfl', $mijoz->pinfl ?? '') }}"
               placeholder="12345678901234" maxlength="14"
               inputmode="numeric" pattern="\d{0,14}">
        @error('pinfl')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-sm-4">
        <label class="form-label fw-medium">Passport berilgan joy</label>
        <input type="text" name="passport_berilgan_joy" class="form-control"
               value="{{ old('passport_berilgan_joy', $mijoz->passport_berilgan_joy ?? '') }}"
               placeholder="Tuman IIB">
    </div>

    {{-- ── Qo'shimcha ─────────────────────────────────────────────── --}}
    <div class="col-sm-6">
        <label class="form-label fw-medium">Ish joyi</label>
        <input type="text" name="ish_joyi" class="form-control"
               value="{{ old('ish_joyi', $mijoz->ish_joyi ?? '') }}">
    </div>

    <div class="col-sm-6">
        <label class="form-label fw-medium">Lavozimi</label>
        <input type="text" name="lavozimi" class="form-control"
               value="{{ old('lavozimi', $mijoz->lavozimi ?? '') }}">
    </div>

    <div class="col-sm-12">
        <label class="form-label fw-medium">Manzil</label>
        <textarea name="manzil" class="form-control" rows="2">{{ old('manzil', $mijoz->manzil ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label fw-medium">Izoh</label>
        <textarea name="izoh" class="form-control" rows="2">{{ old('izoh', $mijoz->izoh ?? '') }}</textarea>
    </div>
</div>
