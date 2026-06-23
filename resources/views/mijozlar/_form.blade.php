{{-- Mijoz forma partial — create va edit uchun --}}

@php
    $telefonQatorlari = old('telefonlar');
    if ($telefonQatorlari === null) {
        $telefonQatorlari = isset($mijoz) && $mijoz->relationLoaded('telefonlar')
            ? $mijoz->telefonlar->map(fn($t) => ['telefon' => $t->telefon, 'sms_yuborilsin' => $t->sms_yuborilsin])->toArray()
            : [];
    }
@endphp

<div class="row g-3">

    {{-- ── Tashkiliy ma'lumotlar ───────────────────────────────────── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2">
                <span class="fw-bold"><i class="bi bi-building me-2 text-primary"></i>Tashkiliy ma'lumotlar</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Filial --}}
                    <div class="col-md-6">
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

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Holat</label>
                        <select name="holat" class="form-select">
                            <option value="faol"   {{ old('holat', $mijoz->holat ?? 'faol') === 'faol'   ? 'selected' : '' }}>AKTIV</option>
                            <option value="nofaol" {{ old('holat', $mijoz->holat ?? '')     === 'nofaol' ? 'selected' : '' }}>PASSIV</option>
                            <option value="sudda"  {{ old('holat', $mijoz->holat ?? '')     === 'sudda'  ? 'selected' : '' }}>SUDDA (sud jarayonida)</option>
                            <option value="yomon"  {{ old('holat', $mijoz->holat ?? '')     === 'yomon'  ? 'selected' : '' }}>YOMON (qora ro'yxat)</option>
                        </select>
                        <div class="form-text text-danger small">
                            <i class="bi bi-exclamation-triangle me-1"></i>"Sudda" yoki "Yomon" holatdagi mijozga yangi shartnoma tuzish taqiqlanadi.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Shaxsiy ma'lumotlar (umrbod o'zgarmas) ──────────────────── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2">
                <span class="fw-bold"><i class="bi bi-person-vcard me-2 text-primary"></i>Shaxsiy ma'lumotlar</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- F.I.O. --}}
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Familiya <span class="text-danger">*</span></label>
                        <input type="text" name="familiya" class="form-control @error('familiya') is-invalid @enderror"
                               value="{{ old('familiya', $mijoz->familiya ?? '') }}" required>
                        @error('familiya')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Ism <span class="text-danger">*</span></label>
                        <input type="text" name="ism" class="form-control @error('ism') is-invalid @enderror"
                               value="{{ old('ism', $mijoz->ism ?? '') }}" required>
                        @error('ism')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Otasining ismi</label>
                        <input type="text" name="otasining_ismi" class="form-control"
                               value="{{ old('otasining_ismi', $mijoz->otasining_ismi ?? '') }}">
                    </div>

                    {{-- Tug'ilgan sana + Passport --}}
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Tug'ilgan sana</label>
                        <input type="date" name="tug_sana" class="form-control @error('tug_sana') is-invalid @enderror"
                               value="{{ old('tug_sana', isset($mijoz) ? $mijoz->tug_sana?->format('Y-m-d') : '') }}">
                        @error('tug_sana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-medium">Passport seriya</label>
                        <input type="text" name="passport_seriya" class="form-control text-uppercase"
                               value="{{ old('passport_seriya', $mijoz->passport_seriya ?? '') }}"
                               placeholder="AA" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">Passport raqam</label>
                        <input type="text" name="passport_raqam" class="form-control"
                               value="{{ old('passport_raqam', $mijoz->passport_raqam ?? '') }}"
                               placeholder="1234567" maxlength="20">
                    </div>
                    <div class="col-md-4">
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

                    <div class="col-md-8">
                        <label class="form-label fw-medium">Passport berilgan joy</label>
                        <input type="text" name="passport_berilgan_joy" class="form-control"
                               value="{{ old('passport_berilgan_joy', $mijoz->passport_berilgan_joy ?? '') }}"
                               placeholder="Tuman IIB">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Plastik karta raqami</label>
                        <input type="text" name="karta_raqami" class="form-control"
                               value="{{ old('karta_raqami', $mijoz->karta_raqami ?? '') }}"
                               placeholder="8600 1234 5678 9012" maxlength="20">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kontakt va manzil ────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2">
                <span class="fw-bold"><i class="bi bi-geo-alt me-2 text-primary"></i>Kontakt va manzil</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Telefon (asosiy) <span class="text-danger">*</span></label>
                        <input type="text" name="telefon" class="form-control @error('telefon') is-invalid @enderror"
                               value="{{ old('telefon', $mijoz->telefon ?? '') }}"
                               placeholder="+998 90 123 45 67" required>
                        @error('telefon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text small text-muted">Asosiy raqamga SMS doim yuboriladi.</div>
                    </div>

                    {{-- Qo'shimcha telefon raqamlar (3 tagacha, jami 4 ta) --}}
                    <div class="col-md-8">
                        <label class="form-label fw-medium d-flex justify-content-between align-items-center mb-1">
                            <span>Qo'shimcha telefon raqamlar</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="mijozTelAdd()">
                                <i class="bi bi-plus-lg"></i> Qo'shish
                            </button>
                        </label>
                        <div id="mijoz-tel-qator-list">
                            @foreach($telefonQatorlari as $i => $t)
                            <div class="row g-2 mb-2 mijoz-tel-qator">
                                <div class="col-7">
                                    <input type="text" name="telefonlar[{{ $i }}][telefon]" class="form-control form-control-sm"
                                           value="{{ $t['telefon'] ?? '' }}" placeholder="+998 90 123 45 67">
                                </div>
                                <div class="col-3 d-flex align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="telefonlar[{{ $i }}][sms_yuborilsin]" value="1"
                                               id="mijoz-tel-sms-{{ $i }}"
                                               {{ ($t['sms_yuborilsin'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="mijoz-tel-sms-{{ $i }}">SMS</label>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest('.mijoz-tel-qator').remove()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="form-text small text-muted">Jami 4 tagacha raqam (asosiy + 3 ta). Galochka belgilanganlarga SMS shablonlari yuboriladi.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Viloyat</label>
                        <select name="viloyat_id" id="mijoz-viloyat" class="form-select">
                            <option value="">— Tanlang —</option>
                            @foreach($viloyatlar as $v)
                            <option value="{{ $v->id }}" {{ old('viloyat_id', $mijoz->viloyat_id ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->nomi }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tuman</label>
                        <select name="tuman_id" id="mijoz-tuman" class="form-select">
                            <option value="">— Avval viloyatni tanlang —</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Manzil (ko'cha, uy)</label>
                        <textarea name="manzil" class="form-control" rows="2">{{ old('manzil', $mijoz->manzil ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Qo'shimcha ma'lumot ──────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-2">
                <span class="fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Qo'shimcha ma'lumot</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Ish joyi</label>
                        <input type="text" name="ish_joyi" class="form-control" list="ish-joyi-list"
                               autocomplete="off"
                               placeholder="Kiriting yoki ro'yxatdan tanlang"
                               value="{{ old('ish_joyi', $mijoz->ish_joyi ?? '') }}">
                        <datalist id="ish-joyi-list">
                            @foreach($ishJoyilar as $ij)
                            <option value="{{ $ij }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Lavozimi</label>
                        <input type="text" name="lavozimi" class="form-control" list="lavozim-list"
                               autocomplete="off"
                               placeholder="Kiriting yoki ro'yxatdan tanlang"
                               value="{{ old('lavozimi', $mijoz->lavozimi ?? '') }}">
                        <datalist id="lavozim-list">
                            @foreach($lavozimlar as $lv)
                            <option value="{{ $lv }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-medium">Izoh</label>
                        <textarea name="izoh" class="form-control" rows="2">{{ old('izoh', $mijoz->izoh ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// ─── Viloyat → Tuman kaskad ─────────────────────────────────────
(function() {
    const barchaTumanlar = @json($tumanlar->map(fn($t) => ['id' => $t->id, 'viloyat_id' => $t->viloyat_id, 'nomi' => $t->nomi]));
    @php $tanlanganTumanId = old('tuman_id', isset($mijoz) ? $mijoz->tuman_id : null); @endphp
    const tanlanganTuman = {{ $tanlanganTumanId ?? 'null' }};
    const viloyatSelect  = document.getElementById('mijoz-viloyat');
    const tumanSelect    = document.getElementById('mijoz-tuman');

    function tumanlarniYukla(viloyatId, tanlangan) {
        tumanSelect.innerHTML = '';
        if (!viloyatId) {
            tumanSelect.innerHTML = '<option value="">— Avval viloyatni tanlang —</option>';
            return;
        }
        tumanSelect.innerHTML = '<option value="">— Tanlang —</option>';
        barchaTumanlar
            .filter(function(t) { return t.viloyat_id == viloyatId; })
            .forEach(function(t) {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nomi;
                if (tanlangan && t.id == tanlangan) opt.selected = true;
                tumanSelect.appendChild(opt);
            });
    }

    if (viloyatSelect && tumanSelect) {
        viloyatSelect.addEventListener('change', function() {
            tumanlarniYukla(this.value, null);
        });
        if (viloyatSelect.value) {
            tumanlarniYukla(viloyatSelect.value, tanlanganTuman);
        }
    }
})();

let _mijozTelIndex = {{ count($telefonQatorlari) }};

function mijozTelAdd() {
    const list = document.getElementById('mijoz-tel-qator-list');
    if (list.children.length >= 3) {
        alert("Qo'shimcha telefon raqamlari 3 tadan oshmasligi kerak (asosiy raqam bilan jami 4 ta).");
        return;
    }
    const idx = _mijozTelIndex++;
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 mijoz-tel-qator';
    div.innerHTML =
        '<div class="col-7">' +
            '<input type="text" name="telefonlar[' + idx + '][telefon]" class="form-control form-control-sm" placeholder="+998 90 123 45 67">' +
        '</div>' +
        '<div class="col-3 d-flex align-items-center">' +
            '<div class="form-check">' +
                '<input type="checkbox" class="form-check-input" name="telefonlar[' + idx + '][sms_yuborilsin]" value="1" id="mijoz-tel-sms-' + idx + '" checked>' +
                '<label class="form-check-label small" for="mijoz-tel-sms-' + idx + '">SMS</label>' +
            '</div>' +
        '</div>' +
        '<div class="col-2">' +
            '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest(\'.mijoz-tel-qator\').remove()">' +
                '<i class="bi bi-trash"></i>' +
            '</button>' +
        '</div>';
    list.appendChild(div);
}
</script>
@endpush
