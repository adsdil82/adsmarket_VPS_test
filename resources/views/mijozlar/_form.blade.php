{{-- Mijoz forma partial — create va edit uchun --}}

@php
    $telefonQatorlari = old('telefonlar');
    if ($telefonQatorlari === null) {
        $telefonQatorlari = isset($mijoz) && $mijoz->relationLoaded('telefonlar')
            ? $mijoz->telefonlar->map(fn($t) => ['telefon' => $t->telefon, 'egasi_ismi' => $t->egasi_ismi, 'sms_yuborilsin' => $t->sms_yuborilsin])->toArray()
            : [];
    }
    $kartaQatorlari = old('kartalar');
    if ($kartaQatorlari === null) {
        $kartaQatorlari = isset($mijoz) && $mijoz->relationLoaded('kartalar')
            ? $mijoz->kartalar->map(fn($k) => ['karta_raqami' => $k->karta_raqami])->toArray()
            : [];
    }
@endphp

<style>
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-wrap { max-width:900px; border:1px solid #93c5fd; border-radius:6px; overflow:hidden; }
.bft-table { width:auto; margin-bottom:0 !important; font-size:.83rem; }
.bft-table td { padding:6px 10px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:auto; }
.bft-section-title.bft-secondary { color:#475569; background:#f8fafc; border-left-color:#94a3b8; }
</style>

{{-- ── Mijoz rasmi ───────────────────────────────────── --}}
<div class="bft-section-title"><i class="bi bi-camera me-1"></i>Mijoz rasmi</div>
<div class="bft-wrap mb-3">
    <div class="p-3 d-flex align-items-center gap-3 flex-wrap">
        <div id="mijoz-rasm-preview-wrap" class="{{ isset($mijoz) && $mijoz->rasm ? '' : 'd-none' }}">
            <img id="mijoz-rasm-preview" src="{{ isset($mijoz) ? $mijoz->rasm_url : '' }}" alt="Mijoz rasmi"
                 style="width:96px;height:96px;object-fit:cover;border-radius:8px;border:2px solid #93c5fd">
        </div>
        <div id="mijoz-rasm-placeholder" class="{{ isset($mijoz) && $mijoz->rasm ? 'd-none' : '' }}"
             style="width:96px;height:96px;border-radius:8px;border:2px dashed #93c5fd;display:flex;align-items:center;justify-content:center;background:#f1f5fd">
            <i class="bi bi-person-circle text-muted" style="font-size:2.5rem"></i>
        </div>
        <div>
            <input type="file" id="mijoz-rasm-input" name="rasm" accept="image/*" class="d-none" onchange="mijozRasmTanlandi(this)">
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="mijozRasmInputOch('file')">
                    <i class="bi bi-folder2-open me-1"></i>Fayldan tanlash
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="mijozRasmInputOch('camera')">
                    <i class="bi bi-camera-fill me-1"></i>Kameradan olish
                </button>
                @if(isset($mijoz) && $mijoz->rasm)
                <div class="form-check d-flex align-items-center ms-1">
                    <input type="checkbox" class="form-check-input" name="rasm_ochir" id="mijoz-rasm-ochir" value="1">
                    <label class="form-check-label small text-danger ms-1" for="mijoz-rasm-ochir">Rasmni o'chirish</label>
                </div>
                @endif
            </div>
            <div class="form-text small text-muted mb-0">JPG/PNG/WEBP, 8 MB gacha — avtomatik siqiladi va yengil formatga o'giriladi.</div>
            @error('rasm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Modal: Kameradan surat olish (kompyuterga ulangan har qanday kamera — o'rnatilgan, USB yoki Bluetooth orqali telefon kamerasi) --}}
<div class="modal fade" id="mijoz-kamera-modal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0"><i class="bi bi-camera-fill me-1"></i>Kameradan surat olish</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-2">
                    <select id="mijoz-kamera-select" class="form-select form-select-sm mx-auto" style="max-width:320px" onchange="mijozKameraOchQurilma(this.value)"></select>
                </div>
                <div id="mijoz-kamera-xato" class="alert alert-warning py-2 small d-none"></div>
                <video id="mijoz-kamera-video" autoplay playsinline muted style="width:100%;max-width:420px;border-radius:8px;background:#000"></video>
                <canvas id="mijoz-kamera-canvas" class="d-none"></canvas>
                <img id="mijoz-kamera-natija" class="d-none" style="width:100%;max-width:420px;border-radius:8px">
            </div>
            <div class="modal-footer py-2 justify-content-center">
                <button type="button" class="btn btn-success" id="mijoz-kamera-olish-btn" onclick="mijozKameraSuratOl()">
                    <i class="bi bi-camera me-1"></i>Suratga olish
                </button>
                <button type="button" class="btn btn-outline-secondary d-none" id="mijoz-kamera-qaytadan-btn" onclick="mijozKameraQaytadan()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Qayta olish
                </button>
                <button type="button" class="btn btn-primary d-none" id="mijoz-kamera-saqla-btn" onclick="mijozKameraSaqla()">
                    <i class="bi bi-check-lg me-1"></i>Saqlash
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Tashkiliy ma'lumotlar ───────────────────────────────────── --}}
<div class="bft-section-title"><i class="bi bi-building me-1"></i>Tashkiliy ma'lumotlar</div>
<div class="bft-wrap mb-3">
    <table class="bft-table">
        <tbody>
            <tr>
                <td class="bft-label">Filial <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <select name="filial_id" class="form-select form-select-sm @error('filial_id') is-invalid @enderror"
                            style="max-width:260px" {{ count($filiallar) === 1 ? 'disabled' : '' }}>
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
                    @error('filial_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Holat</td>
                <td class="bft-wide">
                    <select name="holat" class="form-select form-select-sm" style="max-width:260px">
                        <option value="faol"   {{ old('holat', $mijoz->holat ?? 'faol') === 'faol'   ? 'selected' : '' }}>AKTIV</option>
                        <option value="nofaol" {{ old('holat', $mijoz->holat ?? '')     === 'nofaol' ? 'selected' : '' }}>PASSIV</option>
                        <option value="sudda"  {{ old('holat', $mijoz->holat ?? '')     === 'sudda'  ? 'selected' : '' }}>SUDDA (sud jarayonida)</option>
                        <option value="yomon"  {{ old('holat', $mijoz->holat ?? '')     === 'yomon'  ? 'selected' : '' }}>YOMON (qora ro'yxat)</option>
                    </select>
                    <div class="form-text text-danger small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>"Sudda"/"Yomon" holatda yangi shartnoma tuzish taqiqlanadi.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Shaxsiy ma'lumotlar ──────────────────── --}}
<div class="bft-section-title"><i class="bi bi-person-vcard me-1"></i>Shaxsiy ma'lumotlar</div>
<div class="bft-wrap mb-3">
    <table class="bft-table">
        <tbody>
            <tr>
                <td class="bft-label">F.I.O. <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="text" name="familiya" class="form-control form-control-sm @error('familiya') is-invalid @enderror"
                               style="max-width:220px" value="{{ old('familiya', $mijoz->familiya ?? '') }}" placeholder="Familiya" required>
                        <input type="text" name="ism" class="form-control form-control-sm @error('ism') is-invalid @enderror"
                               style="max-width:220px" value="{{ old('ism', $mijoz->ism ?? '') }}" placeholder="Ism" required>
                        <input type="text" name="otasining_ismi" class="form-control form-control-sm @error('otasining_ismi') is-invalid @enderror"
                               style="max-width:220px" value="{{ old('otasining_ismi', $mijoz->otasining_ismi ?? '') }}" placeholder="Otasining ismi" required>
                    </div>
                    @error('familiya')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('ism')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('otasining_ismi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Jinsi <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="jinsi" id="mijoz-jinsi-erkak" value="erkak"
                                   {{ old('jinsi', $mijoz->jinsi ?? '') === 'erkak' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="mijoz-jinsi-erkak">Erkak</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="jinsi" id="mijoz-jinsi-ayol" value="ayol"
                                   {{ old('jinsi', $mijoz->jinsi ?? '') === 'ayol' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="mijoz-jinsi-ayol">Ayol</label>
                        </div>
                    </div>
                    @error('jinsi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Tug'ilgan sana <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <input type="date" name="tug_sana" class="form-control form-control-sm @error('tug_sana') is-invalid @enderror"
                           style="max-width:180px" value="{{ old('tug_sana', isset($mijoz) ? $mijoz->tug_sana?->format('Y-m-d') : '') }}" required>
                    @error('tug_sana')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Passport <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <input type="text" name="passport_seriya" class="form-control form-control-sm text-uppercase @error('passport_seriya') is-invalid @enderror"
                               style="max-width:70px" value="{{ old('passport_seriya', $mijoz->passport_seriya ?? '') }}"
                               placeholder="AA" maxlength="10" required>
                        <input type="text" name="passport_raqam" class="form-control form-control-sm @error('passport_raqam') is-invalid @enderror"
                               style="max-width:140px" value="{{ old('passport_raqam', $mijoz->passport_raqam ?? '') }}"
                               placeholder="1234567" maxlength="20" required>
                        <span class="text-muted small">Berilgan:</span>
                        <input type="date" name="passport_berilgan_sana" class="form-control form-control-sm @error('passport_berilgan_sana') is-invalid @enderror"
                               style="max-width:170px" value="{{ old('passport_berilgan_sana', isset($mijoz) ? $mijoz->passport_berilgan_sana?->format('Y-m-d') : '') }}">
                        <span class="text-muted small">Amal muddati:</span>
                        <input type="date" name="passport_amal_muddati" class="form-control form-control-sm @error('passport_amal_muddati') is-invalid @enderror"
                               style="max-width:170px" value="{{ old('passport_amal_muddati', isset($mijoz) ? $mijoz->passport_amal_muddati?->format('Y-m-d') : '') }}">
                    </div>
                    @error('passport_seriya')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('passport_raqam')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('passport_berilgan_sana')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('passport_amal_muddati')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Passport berilgan joy <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <input type="text" name="passport_berilgan_joy" class="form-control form-control-sm @error('passport_berilgan_joy') is-invalid @enderror"
                           style="max-width:320px" value="{{ old('passport_berilgan_joy', $mijoz->passport_berilgan_joy ?? '') }}"
                           placeholder="Tuman IIB" required>
                    @error('passport_berilgan_joy')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">PINFL <span class="text-danger">*</span> <small class="fw-normal">(aynan 14 raqam)</small></td>
                <td class="bft-wide">
                    <input type="text" name="pinfl"
                           class="form-control form-control-sm @error('pinfl') is-invalid @enderror"
                           style="max-width:200px" value="{{ old('pinfl', $mijoz->pinfl ?? '') }}"
                           placeholder="31305824200015" maxlength="14" minlength="14"
                           inputmode="numeric" pattern="\d{14}" title="PINFL aynan 14 ta raqamdan iborat bo'lishi kerak"
                           onkeypress="return /\d/.test(event.key)" required>
                    @error('pinfl')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text small text-muted mb-0">PINFL'ning 2—7 xonalari tug'ilgan sanaga (KKOOYY), 1-raqami esa jinsiga (3/5=erkak, 4/6=ayol) mos bo'lishi shart.</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Plastik kartalar (bir nechta bo'lishi mumkin) ─────────────── --}}
<div class="bft-section-title"><i class="bi bi-credit-card me-1"></i>Plastik kartalar</div>
<div class="bft-wrap mb-3">
    <table class="bft-table">
        <tbody>
            <tr>
                <td class="bft-label align-top pt-2">
                    Karta raqamlari
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 d-block mt-1" onclick="mijozKartaAdd()">
                        <i class="bi bi-plus-lg"></i> Yangi karta
                    </button>
                </td>
                <td class="bft-wide">
                    <div id="mijoz-karta-qator-list">
                        @forelse($kartaQatorlari as $i => $k)
                        <div class="row g-2 mb-2 mijoz-karta-qator">
                            <div class="col-auto" style="width:220px">
                                <input type="text" name="kartalar[{{ $i }}][karta_raqami]" class="form-control form-control-sm"
                                       value="{{ $k['karta_raqami'] ?? '' }}" placeholder="8600 1234 5678 9012" maxlength="20">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest('.mijoz-karta-qator').remove()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="row g-2 mb-2 mijoz-karta-qator">
                            <div class="col-auto" style="width:220px">
                                <input type="text" name="kartalar[0][karta_raqami]" class="form-control form-control-sm"
                                       placeholder="8600 1234 5678 9012" maxlength="20">
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest('.mijoz-karta-qator').remove()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="form-text small text-muted mb-0">Bir mijozda bir nechta plastik karta bo'lishi mumkin (5 tagacha).</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Kontakt va manzil ────────────────────────────────────────── --}}
<div class="bft-section-title"><i class="bi bi-geo-alt me-1"></i>Kontakt va manzil</div>
<div class="bft-wrap mb-3">
    <table class="bft-table">
        <tbody>
            <tr>
                <td class="bft-label">Telefon (asosiy) <span class="text-danger">*</span></td>
                <td class="bft-wide">
                    <input type="text" name="telefon" class="form-control form-control-sm @error('telefon') is-invalid @enderror"
                           style="max-width:260px" value="{{ old('telefon', $mijoz->telefon ?? '') }}"
                           placeholder="+998 90 123 45 67" required>
                    @error('telefon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text small text-muted mb-0">Asosiy raqamga SMS doim yuboriladi.</div>
                </td>
            </tr>
            <tr>
                <td class="bft-label align-top pt-2">
                    Qo'shimcha telefonlar
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 d-block mt-1" onclick="mijozTelAdd()">
                        <i class="bi bi-plus-lg"></i> Qo'shish
                    </button>
                </td>
                <td class="bft-wide">
                    <div id="mijoz-tel-qator-list">
                        @foreach($telefonQatorlari as $i => $t)
                        <div class="row g-2 mb-2 mijoz-tel-qator">
                            <div class="col-auto" style="width:190px">
                                <input type="text" name="telefonlar[{{ $i }}][telefon]" class="form-control form-control-sm"
                                       value="{{ $t['telefon'] ?? '' }}" placeholder="+998 90 123 45 67">
                            </div>
                            <div class="col-auto" style="width:190px">
                                <input type="text" name="telefonlar[{{ $i }}][egasi_ismi]" class="form-control form-control-sm"
                                       value="{{ $t['egasi_ismi'] ?? '' }}" placeholder="Kimga tegishli (F.I.O.)">
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="telefonlar[{{ $i }}][sms_yuborilsin]" value="1"
                                           id="mijoz-tel-sms-{{ $i }}"
                                           {{ ($t['sms_yuborilsin'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="mijoz-tel-sms-{{ $i }}">SMS</label>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest('.mijoz-tel-qator').remove()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="form-text small text-muted mb-0">Jami 4 tagacha raqam (asosiy + 3 ta). Galochka belgilanganlarga SMS shablonlari yuboriladi.</div>
                </td>
            </tr>
            <tr>
                <td class="bft-label">Viloyat / Tuman</td>
                <td class="bft-wide">
                    <div class="d-flex gap-2 flex-wrap">
                        <select name="viloyat_id" id="mijoz-viloyat" class="form-select form-select-sm" style="max-width:220px">
                            <option value="">— Tanlang —</option>
                            @foreach($viloyatlar as $v)
                            <option value="{{ $v->id }}" {{ old('viloyat_id', $mijoz->viloyat_id ?? '') == $v->id ? 'selected' : '' }}>
                                {{ $v->nomi }}
                            </option>
                            @endforeach
                        </select>
                        <select name="tuman_id" id="mijoz-tuman" class="form-select form-select-sm" style="max-width:220px">
                            <option value="">— Avval viloyatni tanlang —</option>
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="bft-label">Manzil (ko'cha, uy)</td>
                <td class="bft-wide">
                    <textarea name="manzil" class="form-control form-control-sm" rows="2" style="max-width:420px">{{ old('manzil', $mijoz->manzil ?? '') }}</textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── Qo'shimcha ma'lumot ──────────────────────────────────────── --}}
<div class="bft-section-title bft-secondary"><i class="bi bi-info-circle me-1"></i>Qo'shimcha ma'lumot</div>
<div class="bft-wrap mb-3">
    <table class="bft-table">
        <tbody>
            <tr>
                <td class="bft-label">Ish joyi</td>
                <td class="bft-wide">
                    <input type="text" name="ish_joyi" class="form-control form-control-sm" list="ish-joyi-list"
                           style="max-width:320px" autocomplete="off"
                           placeholder="Kiriting yoki ro'yxatdan tanlang"
                           value="{{ old('ish_joyi', $mijoz->ish_joyi ?? '') }}">
                    <datalist id="ish-joyi-list">
                        @foreach($ishJoyilar as $ij)
                        <option value="{{ $ij }}">
                        @endforeach
                    </datalist>
                </td>
            </tr>
            <tr>
                <td class="bft-label">Lavozimi</td>
                <td class="bft-wide">
                    <input type="text" name="lavozimi" class="form-control form-control-sm" list="lavozim-list"
                           style="max-width:320px" autocomplete="off"
                           placeholder="Kiriting yoki ro'yxatdan tanlang"
                           value="{{ old('lavozimi', $mijoz->lavozimi ?? '') }}">
                    <datalist id="lavozim-list">
                        @foreach($lavozimlar as $lv)
                        <option value="{{ $lv }}">
                        @endforeach
                    </datalist>
                </td>
            </tr>
            <tr>
                <td class="bft-label">Oila a'zolari soni</td>
                <td class="bft-wide">
                    <input type="number" name="oila_azolari_soni" class="form-control form-control-sm @error('oila_azolari_soni') is-invalid @enderror"
                           style="max-width:120px" min="0" max="50"
                           value="{{ old('oila_azolari_soni', $mijoz->oila_azolari_soni ?? '') }}">
                    @error('oila_azolari_soni')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Daromad manbai</td>
                <td class="bft-wide">
                    <input type="text" name="daromad_manbai" class="form-control form-control-sm"
                           style="max-width:320px" placeholder="Ish haqi, tadbirkorlik va h.k."
                           value="{{ old('daromad_manbai', $mijoz->daromad_manbai ?? '') }}">
                </td>
            </tr>
            <tr>
                <td class="bft-label">Oylik daromad / harajat</td>
                <td class="bft-wide">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="number" name="oylik_daromad" class="form-control form-control-sm @error('oylik_daromad') is-invalid @enderror"
                               style="max-width:180px" min="0" step="1000" placeholder="Oylik daromad"
                               value="{{ old('oylik_daromad', $mijoz->oylik_daromad ?? '') }}">
                        <input type="number" name="oylik_harajat" class="form-control form-control-sm @error('oylik_harajat') is-invalid @enderror"
                               style="max-width:180px" min="0" step="1000" placeholder="Oylik harajat"
                               value="{{ old('oylik_harajat', $mijoz->oylik_harajat ?? '') }}">
                    </div>
                    @error('oylik_daromad')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('oylik_harajat')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <td class="bft-label">Izoh</td>
                <td class="bft-wide">
                    <textarea name="izoh" class="form-control form-control-sm" rows="2" style="max-width:420px">{{ old('izoh', $mijoz->izoh ?? '') }}</textarea>
                </td>
            </tr>
        </tbody>
    </table>
</div>

@push('scripts')
<script>
// ─── Mijoz rasmi: fayl / kamera tanlash + jonli preview ─────────
function mijozRasmInputOch(turi) {
    // Kompyuterda (yoki telefon Bluetooth/USB orqali kamera sifatida ulangan bo'lsa) —
    // brauzerning getUserMedia orqali jonli kamera oynasini ochamiz.
    if (turi === 'camera' && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        mijozKameraModalOch();
        return;
    }
    // Fallback: getUserMedia mavjud bo'lmasa — mobil brauzerning o'z kamera ilovasi ochiladi
    const inp = document.getElementById('mijoz-rasm-input');
    if (turi === 'camera') {
        inp.setAttribute('capture', 'environment');
    } else {
        inp.removeAttribute('capture');
    }
    inp.click();
}
function mijozRasmTanlandi(inp) {
    const file = inp.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('mijoz-rasm-preview').src = e.target.result;
        document.getElementById('mijoz-rasm-preview-wrap').classList.remove('d-none');
        document.getElementById('mijoz-rasm-placeholder').classList.add('d-none');
        const ochirCheck = document.getElementById('mijoz-rasm-ochir');
        if (ochirCheck) ochirCheck.checked = false;
    };
    reader.readAsDataURL(file);
}

// ─── Kameradan surat olish (jonli oyna) ──────────────────────────
let mijozKameraStream = null;
let mijozKameraBlob = null;

function mijozKameraHolatniToza() {
    document.getElementById('mijoz-kamera-video').classList.remove('d-none');
    document.getElementById('mijoz-kamera-natija').classList.add('d-none');
    document.getElementById('mijoz-kamera-olish-btn').classList.remove('d-none');
    document.getElementById('mijoz-kamera-qaytadan-btn').classList.add('d-none');
    document.getElementById('mijoz-kamera-saqla-btn').classList.add('d-none');
    document.getElementById('mijoz-kamera-xato').classList.add('d-none');
    mijozKameraBlob = null;
}

async function mijozKameraModalOch() {
    mijozKameraHolatniToza();
    const modalEl = document.getElementById('mijoz-kamera-modal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    await mijozKameraQurilmalarniYukla();
}

async function mijozKameraQurilmalarniYukla() {
    const xatoEl = document.getElementById('mijoz-kamera-xato');
    try {
        // Ruxsat so'rash va qurilmalar nomini olish uchun avval oddiy oqim ochiladi
        const dastlabki = await navigator.mediaDevices.getUserMedia({ video: true });
        const qurilmalar = (await navigator.mediaDevices.enumerateDevices()).filter(d => d.kind === 'videoinput');
        const select = document.getElementById('mijoz-kamera-select');
        select.innerHTML = qurilmalar.map((d, i) =>
            `<option value="${d.deviceId}">${d.label || ('Kamera ' + (i + 1))}</option>`
        ).join('');
        select.classList.toggle('d-none', qurilmalar.length <= 1);

        if (qurilmalar.length) {
            await mijozKameraOchQurilma(qurilmalar[0].deviceId, dastlabki);
        } else {
            document.getElementById('mijoz-kamera-video').srcObject = dastlabki;
            mijozKameraStream = dastlabki;
        }
    } catch (e) {
        xatoEl.textContent = "Kameraga ruxsat berilmadi yoki kamera topilmadi: " + e.message +
            ". Telefon kamerasini kompyuterga (Bluetooth/USB orqali) ulab, tizim sozlamalarida ruxsat berilganini tekshiring.";
        xatoEl.classList.remove('d-none');
    }
}

async function mijozKameraOchQurilma(deviceId, tayyorOqim) {
    if (mijozKameraStream && mijozKameraStream !== tayyorOqim) {
        mijozKameraStream.getTracks().forEach(t => t.stop());
    }
    const oqim = tayyorOqim || await navigator.mediaDevices.getUserMedia({
        video: deviceId ? { deviceId: { exact: deviceId } } : true,
    });
    mijozKameraStream = oqim;
    document.getElementById('mijoz-kamera-video').srcObject = oqim;
}

function mijozKameraSuratOl() {
    const video = document.getElementById('mijoz-kamera-video');
    const canvas = document.getElementById('mijoz-kamera-canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    canvas.toBlob(function(blob) {
        mijozKameraBlob = blob;
        const natijaImg = document.getElementById('mijoz-kamera-natija');
        natijaImg.src = URL.createObjectURL(blob);
        natijaImg.classList.remove('d-none');
        video.classList.add('d-none');
        document.getElementById('mijoz-kamera-olish-btn').classList.add('d-none');
        document.getElementById('mijoz-kamera-qaytadan-btn').classList.remove('d-none');
        document.getElementById('mijoz-kamera-saqla-btn').classList.remove('d-none');
    }, 'image/jpeg', 0.92);
}

function mijozKameraQaytadan() {
    mijozKameraHolatniToza();
}

function mijozKameraSaqla() {
    if (!mijozKameraBlob) return;
    const file = new File([mijozKameraBlob], 'kamera_' + Date.now() + '.jpg', { type: 'image/jpeg' });
    const dt = new DataTransfer();
    dt.items.add(file);
    const inp = document.getElementById('mijoz-rasm-input');
    inp.files = dt.files;
    mijozRasmTanlandi(inp);
    bootstrap.Modal.getInstance(document.getElementById('mijoz-kamera-modal'))?.hide();
}

document.getElementById('mijoz-kamera-modal')?.addEventListener('hidden.bs.modal', function() {
    if (mijozKameraStream) {
        mijozKameraStream.getTracks().forEach(t => t.stop());
        mijozKameraStream = null;
    }
    mijozKameraHolatniToza();
});

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
        '<div class="col-auto" style="width:190px">' +
            '<input type="text" name="telefonlar[' + idx + '][telefon]" class="form-control form-control-sm" placeholder="+998 90 123 45 67">' +
        '</div>' +
        '<div class="col-auto" style="width:190px">' +
            '<input type="text" name="telefonlar[' + idx + '][egasi_ismi]" class="form-control form-control-sm" placeholder="Kimga tegishli (F.I.O.)">' +
        '</div>' +
        '<div class="col-auto d-flex align-items-center">' +
            '<div class="form-check">' +
                '<input type="checkbox" class="form-check-input" name="telefonlar[' + idx + '][sms_yuborilsin]" value="1" id="mijoz-tel-sms-' + idx + '" checked>' +
                '<label class="form-check-label small" for="mijoz-tel-sms-' + idx + '">SMS</label>' +
            '</div>' +
        '</div>' +
        '<div class="col-auto">' +
            '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest(\'.mijoz-tel-qator\').remove()">' +
                '<i class="bi bi-trash"></i>' +
            '</button>' +
        '</div>';
    list.appendChild(div);
}

let _mijozKartaIndex = {{ max(count($kartaQatorlari), 1) }};

function mijozKartaAdd() {
    const list = document.getElementById('mijoz-karta-qator-list');
    if (list.children.length >= 5) {
        alert("Plastik kartalar 5 tadan oshmasligi kerak.");
        return;
    }
    const idx = _mijozKartaIndex++;
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 mijoz-karta-qator';
    div.innerHTML =
        '<div class="col-auto" style="width:220px">' +
            '<input type="text" name="kartalar[' + idx + '][karta_raqami]" class="form-control form-control-sm" placeholder="8600 1234 5678 9012" maxlength="20">' +
        '</div>' +
        '<div class="col-auto">' +
            '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="this.closest(\'.mijoz-karta-qator\').remove()">' +
                '<i class="bi bi-trash"></i>' +
            '</button>' +
        '</div>';
    list.appendChild(div);
}
</script>
@endpush
