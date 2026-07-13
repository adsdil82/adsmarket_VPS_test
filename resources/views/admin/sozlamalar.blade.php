@extends('layouts.app')
@section('title', 'Sozlamalar')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
    <li class="breadcrumb-item active">Sozlamalar</li>
<script>
(function() {
  const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';

  document.getElementById('hpTestBtn')?.addEventListener('click', function() {
    const btn = this, res = document.getElementById('hpTestResult');
    btn.disabled = true;
    res.textContent = 'Tekshirilmoqda...';
    res.className = 'small text-muted';
    fetch('{{ route("admin.gibrid-pochta.test-connection") }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
      res.textContent = d.xabar;
      res.className = 'small ' + (d.ok ? 'text-success' : 'text-danger');
    })
    .catch(() => { res.textContent = 'So\'rov xatosi'; res.className = 'small text-danger'; })
    .finally(() => { btn.disabled = false; });
  });

})();
</script>

@endsection

@section('content')
<form method="POST" action="{{ route('admin.sozlamalar.saqlash') }}">
@csrf

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="fw-bold mb-0"><i class="bi bi-gear me-2 text-secondary"></i>Tizim sozlamalari</h5>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="soz-hammasini-och">
            <i class="bi bi-arrows-expand me-1"></i>Hammasini ochish
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="soz-hammasini-yop">
            <i class="bi bi-arrows-collapse me-1"></i>Hammasini yopish
        </button>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Saqlash
        </button>
    </div>
</div>

<div class="accordion" id="sozlamalarAccordion">

    {{-- ── BREND VA TIZIM NOMI ──────────────────────────────────── --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-brend">
                <i class="bi bi-tag me-2 text-primary"></i>Brend va tizim nomi
            </button>
        </h2>
        <div id="soz-brend" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Tizim nomi (Brend) <span class="text-danger">*</span></label>
                        <input type="text" name="brand_nomi" class="form-control @error('brand_nomi') is-invalid @enderror"
                               value="{{ old('brand_nomi', $soz['brand_nomi'] ?? 'NasiyaPro') }}"
                               placeholder="NasiyaPro">
                        @error('brand_nomi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Sidebar yuqorisida va sahifa sarlavhasida ko'rinadi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── KOMPANIYA REKVIZITLARI ───────────────────────────────── --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-kompaniya">
                <i class="bi bi-building me-2 text-success"></i>Kompaniya rekvizitlari
                <small class="text-muted ms-2 fw-normal">Shartnoma, yuk xati, faktura</small>
            </button>
        </h2>
        <div id="soz-kompaniya" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-medium">Kompaniya nomi</label>
                        <input type="text" name="kompaniya_nomi" class="form-control"
                               value="{{ old('kompaniya_nomi', $soz['kompaniya_nomi'] ?? '') }}"
                               placeholder="MChJ «Tuyona»">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">INN (STIR)</label>
                        <input type="text" name="kompaniya_inn" class="form-control"
                               value="{{ old('kompaniya_inn', $soz['kompaniya_inn'] ?? '') }}"
                               placeholder="123456789">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Yuridik manzil</label>
                        <input type="text" name="kompaniya_manzil" class="form-control"
                               value="{{ old('kompaniya_manzil', $soz['kompaniya_manzil'] ?? '') }}"
                               placeholder="Toshkent sh., ...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Telefon</label>
                        <input type="text" name="kompaniya_telefon" class="form-control"
                               value="{{ old('kompaniya_telefon', $soz['kompaniya_telefon'] ?? '') }}"
                               placeholder="+998 90 123 45 67">
                    </div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-4">
                        <label class="form-label fw-medium">Bank</label>
                        <input type="text" name="kompaniya_bank" class="form-control"
                               value="{{ old('kompaniya_bank', $soz['kompaniya_bank'] ?? '') }}"
                               placeholder="Ipak Yo'li bank">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Hisob raqami</label>
                        <input type="text" name="kompaniya_hisob" class="form-control"
                               value="{{ old('kompaniya_hisob', $soz['kompaniya_hisob'] ?? '') }}"
                               placeholder="2020800001234567">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">MFO</label>
                        <input type="text" name="kompaniya_mfo" class="form-control"
                               value="{{ old('kompaniya_mfo', $soz['kompaniya_mfo'] ?? '') }}"
                               placeholder="00452">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Direktor F.I.O.</label>
                        <input type="text" name="kompaniya_direktor" class="form-control"
                               value="{{ old('kompaniya_direktor', $soz['kompaniya_direktor'] ?? '') }}"
                               placeholder="Karimov Jasur Aliyevich">
                    </div>
                </div>

                {{-- Rekvizit ko'rinishi --}}
                @php
                    $nom  = $soz['kompaniya_nomi'] ?? '';
                    $inn  = $soz['kompaniya_inn'] ?? '';
                    $tel  = $soz['kompaniya_telefon'] ?? '';
                    $bank = $soz['kompaniya_bank'] ?? '';
                    $his  = $soz['kompaniya_hisob'] ?? '';
                    $mfo  = $soz['kompaniya_mfo'] ?? '';
                @endphp
                @if($nom)
                <div class="mt-3 p-3 border rounded bg-light">
                    <div class="small text-muted mb-1">Hujjatlarda ko'rinishi:</div>
                    <div class="fw-bold">{{ $nom }}</div>
                    @if($inn) <div class="small">STIR: {{ $inn }}</div> @endif
                    @if($tel) <div class="small">Tel: {{ $tel }}</div> @endif
                    @if($bank && $his) <div class="small">{{ $bank }} | H/r: {{ $his }} | MFO: {{ $mfo }}</div> @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── INTERFEYS TEMASI ─────────────────────────────────────── --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-tema">
                <i class="bi bi-palette me-2 text-warning"></i>Interfeys temasi
                <small class="text-muted ms-2 fw-normal">Sidebar rangi va aksent rangi</small>
            </button>
        </h2>
        <div id="soz-tema" class="accordion-collapse collapse">
            <div class="accordion-body">
                <input type="hidden" name="tema" id="tema-input" value="{{ old('tema', $soz['tema'] ?? 1) }}">
                <div class="row g-2">
                    @foreach($temalar as $id => $tema)
                    @php $tema2 = $tema['sidebar2'] ?? $tema['sidebar']; @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="tema-karta {{ ($soz['tema'] ?? '1') == $id ? 'selected' : '' }}"
                             data-tema="{{ $id }}"
                             onclick="tanlaTemani({{ $id }})"
                             style="cursor:pointer; border-radius:10px; overflow:hidden; border:3px solid {{ ($soz['tema'] ?? '1') == $id ? $tema['accent'] : 'transparent' }}; transition:border 0.2s; box-shadow:0 1px 4px rgba(0,0,0,.08)">
                            {{-- Preview --}}
                            <div style="display:flex;height:70px;">
                                <div style="width:40%;background:linear-gradient(180deg,{{ $tema['sidebar'] }} 0%,{{ $tema2 }} 100%);display:flex;flex-direction:column;gap:4px;padding:6px 4px;">
                                    <div style="height:4px;background:{{ $tema['accent'] }};border-radius:2px;width:70%"></div>
                                    <div style="height:3px;background:rgba(255,255,255,0.3);border-radius:2px;width:90%"></div>
                                    <div style="height:3px;background:rgba(255,255,255,0.3);border-radius:2px;width:80%"></div>
                                    <div style="height:3px;background:rgba(255,255,255,0.3);border-radius:2px;width:85%"></div>
                                </div>
                                <div style="flex:1;background:#f8f9fa;padding:6px 4px;">
                                    <div style="height:4px;background:#e9ecef;border-radius:2px;margin-bottom:4px"></div>
                                    <div style="height:8px;background:{{ $tema['accent'] }};border-radius:2px;opacity:0.3"></div>
                                </div>
                            </div>
                            <div class="text-center py-1 small fw-medium" style="font-size:11px;background:#f8f9fa;">
                                {{ $tema['nomi'] }}
                                @if($id >= 11)<i class="bi bi-bank2 ms-1" style="color:{{ $tema['sidebar2'] }}" title="Bank uslub"></i>@endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- ── Maxsus ranglar ── --}}
                <hr class="my-3">
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="tema-maxsus-check" name="tema_maxsus"
                           value="1" {{ ($soz['tema_maxsus'] ?? '0') === '1' ? 'checked' : '' }} onchange="temaMaxsusToggle()">
                    <label class="form-check-label fw-medium" for="tema-maxsus-check">
                        <i class="bi bi-eyedropper me-1"></i>Maxsus ranglar ishlatish
                        <small class="text-muted fw-normal">(yuqoridagi tayyor temalar o'rniga o'zingiz rang tanlang)</small>
                    </label>
                </div>
                <div id="tema-maxsus-blok" class="row g-2 {{ ($soz['tema_maxsus'] ?? '0') === '1' ? '' : 'd-none' }}">
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Sidebar — 1-rang</label>
                        <input type="color" class="form-control form-control-color w-100" name="tema_sidebar1"
                               id="tema-sidebar1" value="{{ $soz['tema_sidebar1'] ?? '#1e3a8a' }}" onchange="temaMaxsusPreview()">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Sidebar — 2-rang</label>
                        <input type="color" class="form-control form-control-color w-100" name="tema_sidebar2"
                               id="tema-sidebar2" value="{{ $soz['tema_sidebar2'] ?? '#2563eb' }}" onchange="temaMaxsusPreview()">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Aksent — 1-rang</label>
                        <input type="color" class="form-control form-control-color w-100" name="tema_accent1"
                               id="tema-accent1" value="{{ $soz['tema_accent1'] ?? '#fbbf24' }}" onchange="temaMaxsusPreview()">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small mb-1">Aksent — 2-rang</label>
                        <input type="color" class="form-control form-control-color w-100" name="tema_accent2"
                               id="tema-accent2" value="{{ $soz['tema_accent2'] ?? '#f59e0b' }}" onchange="temaMaxsusPreview()">
                    </div>
                    <div class="col-12">
                        <div id="tema-maxsus-preview" style="display:flex;height:70px;border-radius:10px;overflow:hidden;border:1px solid #dee2e6">
                            <div id="tema-maxsus-preview-sb" style="width:40%;display:flex;flex-direction:column;gap:4px;padding:6px 4px;">
                                <div id="tema-maxsus-preview-ac" style="height:4px;border-radius:2px;width:70%"></div>
                                <div style="height:3px;background:rgba(255,255,255,0.3);border-radius:2px;width:90%"></div>
                                <div style="height:3px;background:rgba(255,255,255,0.3);border-radius:2px;width:80%"></div>
                                <div style="height:3px;background:rgba(255,255,255,0.3);border-radius:2px;width:85%"></div>
                            </div>
                            <div style="flex:1;background:#f8f9fa;padding:6px 4px;">
                                <div style="height:4px;background:#e9ecef;border-radius:2px;margin-bottom:4px"></div>
                                <div id="tema-maxsus-preview-ac2" style="height:8px;border-radius:2px;opacity:0.5"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Guruh nomi shrift rangi ── --}}
                <hr class="my-3">
                <label class="form-label fw-medium mb-2">
                    <i class="bi bi-fonts me-1"></i>Guruh nomi shrift rangi
                    <small class="text-muted fw-normal">(sidebar'dagi MIJOZLAR, SHARTNOMALAR kabi guruh nomlari matni)</small>
                </label>
                @php
                    $grupFontVariantlari = [
                        'qora'  => ['nomi' => 'Qora',  'rang' => '#161616'],
                        'sariq' => ['nomi' => 'Sariq', 'rang' => '#ffe066'],
                        'qizil' => ['nomi' => 'Qizil', 'rang' => '#ff5252'],
                        'oq'    => ['nomi' => "Oq",    'rang' => '#ffffff'],
                    ];
                    $grupFontTanlangan = $soz['grup_font_rang'] ?? 'oq';
                    $onizTemaId = (int)($soz['tema'] ?? 1);
                    $onizTema   = $temalar[$onizTemaId] ?? $temalar[1];
                    $onizAcc1   = ($soz['tema_maxsus'] ?? '0') === '1' ? ($soz['tema_accent1'] ?: $onizTema['accent']) : $onizTema['accent'];
                    $onizAcc2   = ($soz['tema_maxsus'] ?? '0') === '1' ? ($soz['tema_accent2'] ?: ($onizTema['accent2'] ?? $onizTema['accent'])) : ($onizTema['accent2'] ?? $onizTema['accent']);
                @endphp
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @foreach($grupFontVariantlari as $key => $gf)
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="grup_font_rang" id="gfr-{{ $key }}"
                               value="{{ $key }}" {{ $grupFontTanlangan === $key ? 'checked' : '' }}
                               onchange="grupFontPreview('{{ $key }}')">
                        <label class="form-check-label" for="gfr-{{ $key }}">
                            <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:{{ $gf['rang'] }};border:1px solid #ccc;vertical-align:middle;margin-right:3px"></span>
                            {{ $gf['nomi'] }}
                        </label>
                    </div>
                    @endforeach
                </div>
                <div id="grup-font-preview"
                     data-accent1="{{ $onizAcc1 }}" data-accent2="{{ $onizAcc2 }}"
                     style="max-width:260px;padding:6px 10px;border-radius:10px;font-size:12px;font-weight:900;letter-spacing:2.5px;text-transform:uppercase;box-shadow:0 2px 6px rgba(0,0,0,.28);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,{{ $onizAcc1 }} 0%,{{ $onizAcc2 }} 45%,{{ $onizAcc1 }} 100%)">
                    <span id="grup-font-preview-label">Guruh nomi</span>
                    <i class="bi bi-dash-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── OPERATSION KUN NAZORATI ──────────────────────────────────────── --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-operatsion-kun">
                <i class="bi bi-calendar-check me-2 text-danger"></i>Operatsion kun nazorati
                <small class="text-muted ms-2 fw-normal">Shartnoma sanasini orqaga qaytarish</small>
            </button>
        </h2>
        <div id="soz-operatsion-kun" class="accordion-collapse collapse">
            <div class="accordion-body pb-2">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Orqaga sanali shartnoma/to'lov</label>
                        <select name="orqaga_sana_taqiqlansin" class="form-select form-select-sm">
                            <option value="1" {{ \App\Models\Sozlama::ol('orqaga_sana_taqiqlansin','1')==='1' ? 'selected' : '' }}>
                                Taqiqlangan — faqat admin/menejer ruxsat etadi
                            </option>
                            <option value="0" {{ \App\Models\Sozlama::ol('orqaga_sana_taqiqlansin','1')==='0' ? 'selected' : '' }}>
                                Ruxsat — hamma xodim orqaga sana qo'yishi mumkin
                            </option>
                        </select>
                        <div class="form-text">
                            Yoqilganda, oddiy xodim yangi shartnoma yaratishda "Boshlanish sanasi"ni
                            o'tgan kunga qo'ya olmaydi (faqat bugungi yoki kelajakdagi kun). Admin va
                            menejer rollari bu cheklovdan istisno.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── POS / CHEK VA PRINTER SOZLAMALARI ────────────────────────────── --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-pos-chek">
                <i class="bi bi-receipt me-2 text-success"></i>POS / Chek va printer sozlamalari
            </button>
        </h2>
        <div id="soz-pos-chek" class="accordion-collapse collapse">
            <div class="accordion-body pb-2">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small mb-1">Chek pastidagi matn</label>
                        <input type="text" name="chek_footer_matni" class="form-control form-control-sm"
                               value="{{ old('chek_footer_matni', \App\Models\Sozlama::ol('chek_footer_matni', 'Rahmat! Qayta kelishingizni kutamiz.')) }}"
                               maxlength="300">
                        <div class="form-text">Har bir chek pastida ko'rinadigan xayrlashuv matni.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Chek qog'ozi kengligi</label>
                        <select name="chek_qogoz_kengligi" class="form-select form-select-sm">
                            <option value="80" {{ \App\Models\Sozlama::ol('chek_qogoz_kengligi','80')==='80' ? 'selected' : '' }}>80mm (standart)</option>
                            <option value="58" {{ \App\Models\Sozlama::ol('chek_qogoz_kengligi','80')==='58' ? 'selected' : '' }}>58mm (kichik termoprinter)</option>
                        </select>
                        <div class="form-text">Chek chop etilganda qog'oz kengligiga mos formatlanadi.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">Avtomatik-qulflash vaqti (fullscreen terminal)</label>
                        <input type="number" name="pos_auto_lock_daqiqa" class="form-control form-control-sm"
                               value="{{ old('pos_auto_lock_daqiqa', \App\Models\Sozlama::ol('pos_auto_lock_daqiqa','10')) }}"
                               min="1" max="120">
                        <div class="form-text">Necha daqiqa harakatsizlikdan keyin kassa terminali avtomatik qulflanadi.</div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="chek_avtomatik_chop" id="chek-avto-chop" value="1"
                                   {{ \App\Models\Sozlama::ol('chek_avtomatik_chop','0')==='1' ? 'checked' : '' }}>
                            <label class="form-check-label small" for="chek-avto-chop">
                                To'lov qabul qilingandan keyin chekni avtomatik chop etish oynasini ochish
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── HYBRID POCHTA ──────────────────────────────────────────────────── --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-hybrid">
                <i class="bi bi-envelope-paper me-2 text-primary"></i>Hybrid Pochta
                <span class="badge bg-light text-dark border ms-2 fw-normal">hybrid.pochta.uz</span>
                <span class="badge bg-secondary ms-2 fw-normal">Jismoniy pochta xatlari</span>
            </button>
        </h2>
        <div id="soz-hybrid" class="accordion-collapse collapse">
            <div class="accordion-body pb-2">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Login</label>
                        <input type="text" name="hybrid_pochta_login" class="form-control form-control-sm"
                            value="{{ \App\Models\Sozlama::ol('hybrid_pochta_login') }}"
                            placeholder="API login">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Parol</label>
                        <input type="password" name="hybrid_pochta_password" class="form-control form-control-sm"
                            value="{{ \App\Models\Sozlama::ol('hybrid_pochta_password') }}"
                            placeholder="API parol" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small mb-1">Holat</label>
                        <select name="hybrid_pochta_yoqilgan" class="form-select form-select-sm">
                            <option value="0" {{ \App\Models\Sozlama::ol('hybrid_pochta_yoqilgan','0')==='0' ? 'selected' : '' }}>O'chirilgan</option>
                            <option value="1" {{ \App\Models\Sozlama::ol('hybrid_pochta_yoqilgan')==='1'     ? 'selected' : '' }}>Yoqilgan</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">
                            Sertifikat paroli (Variant B — brauzersiz yuborish)
                            @if($hpCertExists)
                            <span class="badge bg-success ms-1" style="font-size:.65rem">Sertifikat fayli topildi</span>
                            @else
                            <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Sertifikat fayli yo'q</span>
                            @endif
                        </label>
                        <input type="password" name="hybrid_pochta_cert_parol" class="form-control form-control-sm"
                            value="{{ \App\Models\Sozlama::ol('hybrid_pochta_cert_parol') }}"
                            placeholder="E-IMZO sertifikat paroli" autocomplete="new-password">
                        <div class="form-text">
                            Sertifikat fayli <code>storage/app/certs/hp_cert.pfx</code> manziliga qo'lda joylanadi (serverga SSH/SCP orqali).
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-center gap-2 mt-1">
                        <button type="button" id="hpTestBtn" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plug me-1"></i>Ulanishni tekshirish
                        </button>
                        <a href="{{ route('admin.gibrid-pochta.pochta-loglar.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-journal-text me-1"></i>Log jurnali
                        </a>
                        <span id="hpTestResult" class="small"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /sozlamalarAccordion --}}

<div class="d-flex justify-content-end mt-3 mb-4">
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-save me-1"></i> Barcha sozlamalarni saqlash
    </button>
</div>

</form>

{{-- ═══ HUJJAT MATNLARI ════════════════════════════════════════════════════ --}}
<h5 class="fw-bold mb-3 mt-5 border-top pt-4">
    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Hujjat matnlari
</h5>
<div class="accordion" id="hujjatMatnAccordion">

    {{-- Qo'shimcha bandlar --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-qoshimcha-band">
                <i class="bi bi-file-earmark-plus me-2 text-primary"></i>Qo'shimcha bandlar
                <small class="text-muted ms-2 fw-normal">Faqat yangi shartnomalarga ta'sir qiladi</small>
            </button>
        </h2>
        <div id="soz-qoshimcha-band" class="accordion-collapse collapse" data-bs-parent="#hujjatMatnAccordion">
            <div class="accordion-body">
                <p class="text-muted small mb-3">
                    Shartnoma va Kafillik shartnomasi hujjatlarining oxiriga (6-bo'lim, "Boshqa shartlar"dan
                    keyin) qo'shimcha band sifatida qo'shiladigan matn. <strong>Diqqat:</strong> bu yerda
                    matnni o'zgartirish faqat <u>shu kundan boshlab yaratiladigan yangi shartnomalarga</u>
                    ta'sir qiladi — eski (allaqachon yaratilgan) shartnomalar o'zlari yaratilgan paytdagi
                    matnni saqlab qoladi va o'zgarmaydi.
                </p>
                <div class="row g-3">
                    @foreach(['shartnoma' => 'Shartnoma qo\'shimcha bandlari', 'kafillik' => 'Kafillik shartnomasi qo\'shimcha bandlari'] as $bandTuri => $bandSarlavha)
                    @php $faolBand = \App\Models\HujjatBand::faolVersiya($bandTuri); @endphp
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">{{ $bandSarlavha }}</h6>
                                <form method="POST" action="{{ route('admin.hujjatband.saqlash') }}">
                                    @csrf
                                    <input type="hidden" name="turi" value="{{ $bandTuri }}">
                                    <textarea name="matn" class="form-control form-control-sm" rows="6"
                                              placeholder="Masalan: 7.1. Tovar kafolat muddati 12 oy...">{{ old('matn', $faolBand?->matn ?? '') }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="small text-muted">
                                            @if($faolBand)
                                                Joriy versiya #{{ $faolBand->id }}
                                                ({{ $faolBand->created_at?->format('d.m.Y H:i') }})
                                            @else
                                                Hali band qo'shilmagan
                                            @endif
                                        </span>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-save me-1"></i>Yangi versiya saqlash
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Asosiy matnni tahrirlash --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button type="button" class="accordion-button collapsed fw-bold" data-bs-toggle="collapse" data-bs-target="#soz-asosiy-matn">
                <i class="bi bi-pencil-square me-2 text-danger"></i>Asosiy matnni tahrirlash
                <small class="text-muted ms-2 fw-normal">Saqlanganda BARCHA shartnomalarga (eski va yangi) ta'sir qiladi</small>
            </button>
        </h2>
        <div id="soz-asosiy-matn" class="accordion-collapse collapse" data-bs-parent="#hujjatMatnAccordion">
            <div class="accordion-body">
                <p class="text-muted small mb-3">
                    Hujjatning 3-6 bo'limlari (Tomonlarning huquq va majburiyatlari, Javobgarlik, Fors-major,
                    Yakunlovchi holat) — to'liq matn. Bu yerni kengaytirish (yangi band qo'shish), band
                    o'chirish yoki matnni butunlay o'zgartirish mumkin (oddiy HTML:
                    <code>&lt;h3 class="bolim"&gt;</code> — bo'lim sarlavhasi,
                    <code>&lt;p class="band"&gt;</code> — band matni).
                    <strong class="text-danger">Diqqat:</strong> bu — qo'shimcha bandlardan farqli, JONLI
                    (live) matn — saqlangan zahoti <u>BARCHA shartnomalarga, jumladan eski (allaqachon
                    yaratilgan) shartnomalarga ham</u> qo'llaniladi, chunki hujjat har safar "Chop etish"
                    bosilganda shu matndan qayta generatsiya qilinadi.
                </p>
                <div class="row g-3">
                    @foreach(['shartnoma' => 'Shartnoma — asosiy matn (3-6 bo\'lim)', 'kafillik' => 'Kafillik shartnomasi — asosiy matn (3-6 bo\'lim)'] as $matnTuri => $matnSarlavha)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h6 class="fw-bold mb-2">{{ $matnSarlavha }}</h6>
                                <form method="POST" action="{{ route('admin.hujjatmatn.saqlash') }}"
                                      onsubmit="return confirm('Diqqat: bu o\'zgarish BARCHA shartnomalarga (eski va yangi) ta\'sir qiladi. Davom etilsinmi?');">
                                    @csrf
                                    <input type="hidden" name="turi" value="{{ $matnTuri }}">
                                    <textarea name="matn" class="form-control form-control-sm font-monospace" rows="14"
                                              style="font-size:11px;">{{ old('matn', \App\Models\HujjatBand::asosiyMatn($matnTuri)) }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <a href="#" class="small text-secondary"
                                           onclick="event.preventDefault(); this.closest('form').querySelector('textarea').value = {{ \Illuminate\Support\Js::from(\App\Models\HujjatBand::asosiyMatnDefault($matnTuri)) }};">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Zavod matniga qaytarish
                                        </a>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-save me-1"></i>Saqlash (barcha shartnomalarga qo'llaniladi)
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>{{-- /hujjatMatnAccordion --}}

{{-- ═══ XABARNOMA SOZLAMALARI ════════════════════════════════════════════ --}}
<h5 class="fw-bold mb-3 mt-5 border-top pt-4">
    <i class="bi bi-bell me-2 text-warning"></i>Xabarnoma sozlamalari
</h5>
<div class="accordion" id="notifAccordion">

    {{-- SMS Sozlamalari --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseSms">
                <i class="bi bi-chat-dots me-2 text-warning"></i>SMS Sozlamalari
                @php
                    $smsEnabled = \App\Models\NotificationSetting::get('sms','enabled','1');
                    $smsTest    = \App\Models\NotificationSetting::get('sms','test_mode','1');
                @endphp
                @if($smsEnabled !== '1')
                <span class="badge bg-secondary ms-2 fw-normal" style="font-size:.65rem">o'chirilgan</span>
                @elseif($smsTest === '1')
                <span class="badge bg-info ms-2 fw-normal" style="font-size:.65rem">test rejim</span>
                @else
                <span class="badge bg-success ms-2 fw-normal" style="font-size:.65rem">faol</span>
                @endif
            </button>
        </h2>
        <div id="collapseSms" class="accordion-collapse collapse" data-bs-parent="#notifAccordion">
            <div class="accordion-body">
                @php $smsSoz = \App\Models\NotificationSetting::where('channel','sms')->get()->keyBy('key'); @endphp
                <form method="POST" action="{{ route('admin.notif.sms.saqlash') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-medium">Provider</label>
                            <select name="provider" class="form-select form-select-sm">
                                <option value="test_mode" {{ ($smsSoz['provider']->value??'test_mode')==='test_mode'?'selected':'' }}>Test Mode</option>
                                <option value="eskiz"     {{ ($smsSoz['provider']->value??'')==='eskiz'    ?'selected':'' }}>Eskiz.uz</option>
                                <option value="playmobile"{{ ($smsSoz['provider']->value??'')==='playmobile'?'selected':'' }}>PlayMobile</option>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-medium">Sender ID</label>
                            <input type="text" name="sender_id" class="form-control form-control-sm"
                                   value="{{ $smsSoz['sender_id']->value ?? 'NasiyaPro' }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-medium">API URL</label>
                            <input type="url" name="api_url" class="form-control form-control-sm"
                                   value="{{ $smsSoz['api_url']->value ?? '' }}" placeholder="https://notify.eskiz.uz/api">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Login / Email</label>
                            <input type="text" name="login" class="form-control form-control-sm"
                                   value="{{ $smsSoz['login']->value ?? '' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Parol/Token</label>
                            <input type="password" name="password" class="form-control form-control-sm"
                                   placeholder="{{ ($smsSoz['password']->value??'')?'••••••••':'Kiriting' }}">
                            <div class="form-text small">Bo'sh qoldirsangiz eski parol saqlanadi.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Test telefon</label>
                            <input type="text" name="test_phone" class="form-control form-control-sm"
                                   value="{{ $smsSoz['test_phone']->value ?? '' }}" placeholder="+998901234567">
                        </div>
                    </div>
                    <div class="d-flex gap-3 mt-3 align-items-center flex-wrap">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enabled" id="adm-sms-enabled"
                                   {{ ($smsSoz['enabled']->value??'1')==='1'?'checked':'' }}>
                            <label class="form-check-label small fw-medium" for="adm-sms-enabled">SMS moduli yoqilgan</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="test_mode" id="adm-sms-test"
                                   {{ ($smsSoz['test_mode']->value??'1')==='1'?'checked':'' }}>
                            <label class="form-check-label small" for="adm-sms-test">Test rejimi</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="adminTestSms()">
                            <i class="bi bi-send me-1"></i>Test SMS
                        </button>
                        <span id="adm-sms-natija" class="small ms-2"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Telegram Sozlamalari --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseTelegram">
                <i class="bi bi-telegram me-2 text-info"></i>Telegram Sozlamalari
            </button>
        </h2>
        <div id="collapseTelegram" class="accordion-collapse collapse" data-bs-parent="#notifAccordion">
            <div class="accordion-body">
                @php $tgSoz = \App\Models\NotificationSetting::where('channel','telegram')->get()->keyBy('key'); @endphp
                <form method="POST" action="{{ route('admin.notif.telegram.saqlash') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Bot Token</label>
                            <input type="password" name="bot_token" class="form-control form-control-sm"
                                   placeholder="{{ ($tgSoz['bot_token']->value??'')?'••••••••':'Bot token' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Test Chat ID</label>
                            <input type="text" name="test_chat_id" class="form-control form-control-sm"
                                   value="{{ $tgSoz['test_chat_id']->value ?? '' }}" placeholder="123456789">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="adminTestTelegram()">
                            <i class="bi bi-send me-1"></i>Test xabar
                        </button>
                        <span id="adm-tg-natija" class="small ms-2"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Email Sozlamalari --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseEmail">
                <i class="bi bi-envelope me-2 text-primary"></i>Email Sozlamalari
            </button>
        </h2>
        <div id="collapseEmail" class="accordion-collapse collapse" data-bs-parent="#notifAccordion">
            <div class="accordion-body">
                @php $emSoz = \App\Models\NotificationSetting::where('channel','email')->get()->keyBy('key'); @endphp
                <form method="POST" action="{{ route('admin.notif.email.saqlash') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-4"><label class="form-label small fw-medium">Host</label>
                            <input type="text" name="host" class="form-control form-control-sm" value="{{ $emSoz['host']->value??'' }}" placeholder="smtp.gmail.com"></div>
                        <div class="col-sm-2"><label class="form-label small fw-medium">Port</label>
                            <input type="number" name="port" class="form-control form-control-sm" value="{{ $emSoz['port']->value??'587' }}"></div>
                        <div class="col-sm-3"><label class="form-label small fw-medium">Username</label>
                            <input type="text" name="username" class="form-control form-control-sm" value="{{ $emSoz['username']->value??'' }}"></div>
                        <div class="col-sm-3"><label class="form-label small fw-medium">Parol</label>
                            <input type="password" name="password" class="form-control form-control-sm" placeholder="{{ ($emSoz['password']->value??'')?'••••••••':'' }}"></div>
                        <div class="col-sm-6"><label class="form-label small fw-medium">From Address</label>
                            <input type="email" name="from_address" class="form-control form-control-sm" value="{{ $emSoz['from_address']->value??'' }}" placeholder="noreply@nasiyapro.uz"></div>
                        <div class="col-sm-6"><label class="form-label small fw-medium">Test Email</label>
                            <input type="email" name="test_email" class="form-control form-control-sm" value="{{ $emSoz['test_email']->value??'' }}"></div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                        <span id="adm-em-natija" class="small ms-2"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- AutoPay Sozlamalari --}}
    <div class="accordion-item border-0 shadow-sm mb-2">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-bold" type="button"
                    data-bs-toggle="collapse" data-bs-target="#collapseAutopay">
                <i class="bi bi-credit-card-fill me-2 text-primary"></i>AutoPay — avtomatik yechish
                @php $apYoqilgan = \App\Models\NotificationSetting::get('autopay','yoqilgan','0'); @endphp
                @if($apYoqilgan === '1')
                <span class="badge bg-success ms-2 fw-normal" style="font-size:.65rem">yoqilgan</span>
                @else
                <span class="badge bg-secondary ms-2 fw-normal" style="font-size:.65rem">o'chirilgan</span>
                @endif
            </button>
        </h2>
        <div id="collapseAutopay" class="accordion-collapse collapse" data-bs-parent="#notifAccordion">
            <div class="accordion-body">
                <p class="text-muted small mb-3">
                    Muddati o'tgan shartnomalarni <a href="{{ route('autopay.index') }}">AutoPay</a> orqali
                    kuzatib, mijoz kartasida mablag' paydo bo'lganda avtomatik yechish. Har bir shartnoma
                    admin/menejer tomonidan qo'lda tasdiqlanib yuboriladi — bu yerda faqat ulanish
                    (merchant_id, token) sozlanadi.
                </p>
                @php $apSoz = \App\Models\NotificationSetting::where('channel','autopay')->get()->keyBy('key'); @endphp
                <form method="POST" action="{{ route('admin.notif.autopay.saqlash') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-medium">Merchant ID</label>
                            <input type="text" name="merchant_id" class="form-control form-control-sm"
                                   value="{{ $apSoz['merchant_id']->value ?? '' }}" autocomplete="off">
                        </div>
                        <div class="col-sm-5">
                            <label class="form-label small fw-medium">API Token</label>
                            <input type="password" name="token" class="form-control form-control-sm"
                                   placeholder="{{ ($apSoz['token']->value ?? '') ? '••••••••' : '' }}"
                                   autocomplete="new-password">
                        </div>
                        <div class="col-sm-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="yoqilgan" id="ap-yoqilgan" value="1"
                                       {{ $apYoqilgan==='1' ? 'checked' : '' }}>
                                <label class="form-check-label small" for="ap-yoqilgan">Yoqilgan</label>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <p class="text-muted small mb-2">
                        <i class="bi bi-cash-coin me-1 text-warning"></i>
                        <strong>Pullik xizmatlar</strong> (Scoring, Monitoring, Processing, E-GOV) — bular AutoPay'ning
                        oylik hisobiga qo'shimcha xarajat qiladi. Har birini alohida yoqish/o'chirish mumkin.
                    </p>
                    @php
                        $paidXizmatlar = [
                            'scoring_yoqilgan'    => 'Scoring (kredit reytingi)',
                            'monitoring_yoqilgan' => 'Monitoring (karta kuzatish)',
                            'processing_yoqilgan' => 'Processing (karta qidirish)',
                            'egov_yoqilgan'       => 'E-GOV (davlat xizmatlari)',
                        ];
                    @endphp
                    <div class="row g-2">
                        @foreach($paidXizmatlar as $kalit => $nomi)
                        <div class="col-sm-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $kalit }}" id="ap-{{ $kalit }}" value="1"
                                       {{ (\App\Models\NotificationSetting::get('autopay', $kalit, '0') === '1') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="ap-{{ $kalit }}">{{ $nomi }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

{{-- Viloyat/Tuman nomlarini yangilash --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2 d-flex align-items-center gap-2"
         style="background:linear-gradient(135deg,#065f4608,#34d39910)">
        <i class="bi bi-geo-alt text-success"></i>
        <strong class="small flex-grow-1">Вилоят / Туман номлари (Кириллча)</strong>
    </div>
    <div class="card-body py-3">
        <p class="text-muted small mb-2">
            Вилоят номларини манба — маълумотномадан (Excel) кириллча вариантга қайта тиклайди.
        </p>
        <button type="button" class="btn btn-sm btn-outline-success" id="viloyatNomBtn"
                onclick="viloyatNomYangilash()">
            <i class="bi bi-arrow-repeat me-1"></i>Вилоят номларини янгилаш
        </button>
        <span id="viloyatNomNatija" class="small ms-2"></span>
    </div>
</div>


</div>{{-- /accordion --}}

@endsection

@push('scripts')
<script>
function adminTestSms() {
    var el = document.getElementById('adm-sms-natija');
    el.textContent = '...';
    fetch('{{ route("xabarnoma.sms.test") }}', {
        method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    }).then(r=>r.json()).then(d=>{
        el.className='small ms-2 '+(d.status==='test'?'text-success':'text-danger');
        el.textContent=d.status==='test'?'Test yuborildi!':('Xato: '+(d.error||''));
    });
}
function adminTestTelegram() {
    var el = document.getElementById('adm-tg-natija');
    el.textContent = '...';
    fetch('{{ route("xabarnoma.telegram.test") }}', {
        method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    }).then(r=>r.json()).then(d=>{
        el.className='small ms-2 '+(d.ok?'text-success':'text-danger');
        el.textContent=d.ok?'Test yuborildi!':('Xato: '+(d.result?.description||d.error||''));
    });
}
document.getElementById('soz-hammasini-och')?.addEventListener('click', () => {
    document.querySelectorAll('#sozlamalarAccordion .accordion-collapse').forEach(el => {
        new bootstrap.Collapse(el, { toggle: false }).show();
    });
});
document.getElementById('soz-hammasini-yop')?.addEventListener('click', () => {
    document.querySelectorAll('#sozlamalarAccordion .accordion-collapse').forEach(el => {
        new bootstrap.Collapse(el, { toggle: false }).hide();
    });
});
function tanlaTemani(id) {
    document.getElementById('tema-input').value = id;
    document.querySelectorAll('.tema-karta').forEach(k => {
        k.style.border = '3px solid transparent';
    });
    const temalar = @json($temalar);
    const karta = document.querySelector(`[data-tema="${id}"]`);
    if (karta) {
        karta.style.border = `3px solid ${temalar[id].accent}`;
    }
    // Tayyor tema tanlanganda "Maxsus ranglar" avtomatik o'chadi
    const maxsusCheck = document.getElementById('tema-maxsus-check');
    if (maxsusCheck.checked) {
        maxsusCheck.checked = false;
        temaMaxsusToggle();
    }
}
function temaMaxsusToggle() {
    const yoqilgan = document.getElementById('tema-maxsus-check').checked;
    document.getElementById('tema-maxsus-blok').classList.toggle('d-none', !yoqilgan);
    if (yoqilgan) temaMaxsusPreview();
}
function temaMaxsusPreview() {
    const s1 = document.getElementById('tema-sidebar1').value;
    const s2 = document.getElementById('tema-sidebar2').value;
    const a1 = document.getElementById('tema-accent1').value;
    const a2 = document.getElementById('tema-accent2').value;
    document.getElementById('tema-maxsus-preview-sb').style.background = `linear-gradient(180deg, ${s1} 0%, ${s2} 100%)`;
    document.getElementById('tema-maxsus-preview-ac').style.background = `linear-gradient(90deg, ${a1} 0%, ${a2} 100%)`;
    document.getElementById('tema-maxsus-preview-ac2').style.background = `linear-gradient(90deg, ${a1} 0%, ${a2} 100%)`;
    grupFontPreview(null, a1, a2);
}
const grupFontRanglar = { qora: ['#161616', 'rgba(255,255,255,.9)'], sariq: ['#ffe066', 'rgba(0,0,0,.85)'], qizil: ['#ff5252', 'rgba(0,0,0,.85)'], oq: ['#ffffff', 'rgba(0,0,0,.85)'] };
function grupFontPreview(key, a1, a2) {
    const prev = document.getElementById('grup-font-preview');
    if (!key) {
        key = document.querySelector('input[name="grup_font_rang"]:checked')?.value || 'oq';
    }
    a1 = a1 || prev.dataset.accent1;
    a2 = a2 || prev.dataset.accent2;
    prev.dataset.accent1 = a1; prev.dataset.accent2 = a2;
    prev.style.background = `linear-gradient(135deg, ${a1} 0%, ${a2} 45%, ${a1} 100%)`;
    const [rang, outline] = grupFontRanglar[key] || grupFontRanglar.oq;
    const label = document.getElementById('grup-font-preview-label');
    prev.style.color = rang;
    label.style.textShadow = `-1px -1px 0 ${outline}, 1px -1px 0 ${outline}, -1px 1px 0 ${outline}, 1px 1px 0 ${outline}, 0 0 3px ${outline}`;
}
grupFontPreview();
temaMaxsusPreview();
</script>
@endpush

@push('scripts')
<script>
function viloyatNomYangilash() {
    var btn = document.getElementById('viloyatNomBtn');
    var el  = document.getElementById('viloyatNomNatija');
    btn.disabled = true; el.textContent = '...';
    fetch('{{ route("malumotnamalar.viloyatlar.nom-yangilash") }}', {
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'}
    }).then(r=>r.json()).then(d=>{
        btn.disabled = false;
        if (d.ok) {
            el.className = 'small ms-2 text-success';
            el.textContent = d.viloyat + ' вилоят + ' + d.tuman + ' туман номи янгиланди ✓';
        } else {
            el.className = 'small ms-2 text-danger';
            el.textContent = 'Хато: ' + (d.message||'');
        }
    }).catch(e=>{
        btn.disabled = false;
        el.className = 'small ms-2 text-danger';
        el.textContent = 'Хато: ' + e.message;
    });
}
</script>
@endpush
