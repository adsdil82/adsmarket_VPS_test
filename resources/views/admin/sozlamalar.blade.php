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

  function loadSpravochnik(url) {
    const panel = document.getElementById('hpSpravochnik');
    const pre   = document.getElementById('hpSpravochnikPre');
    panel.classList.remove('d-none');
    pre.textContent = 'Yuklanmoqda...';
    fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(d => { pre.textContent = JSON.stringify(d, null, 2); })
    .catch(e => { pre.textContent = 'Xato: ' + e; });
  }

  window.loadRegions = () => loadSpravochnik('{{ route("admin.gibrid-pochta.regions") }}');
  window.loadAreas   = () => loadSpravochnik('{{ route("admin.gibrid-pochta.areas") }}');
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
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="tema-karta {{ ($soz['tema'] ?? '1') == $id ? 'selected' : '' }}"
                             data-tema="{{ $id }}"
                             onclick="tanlaTemani({{ $id }})"
                             style="cursor:pointer; border-radius:10px; overflow:hidden; border:3px solid {{ ($soz['tema'] ?? '1') == $id ? $tema['accent'] : 'transparent' }}; transition:border 0.2s">
                            {{-- Preview --}}
                            <div style="display:flex;height:70px;">
                                <div style="width:40%;background:{{ $tema['sidebar'] }};display:flex;flex-direction:column;gap:4px;padding:6px 4px;">
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
                            </div>
                        </div>
                    </div>
                    @endforeach
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
                            Viloyat ID
                            <a href="#" onclick="loadRegions()" class="ms-1 small text-primary">ro'yxatni ko'rish</a>
                        </label>
                        <input type="number" name="hybrid_pochta_region_id" class="form-control form-control-sm"
                            value="{{ \App\Models\Sozlama::ol('hybrid_pochta_region_id') }}"
                            placeholder="GET /api/region dan ID">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small mb-1">
                            Tuman ID
                            <a href="#" onclick="loadAreas()" class="ms-1 small text-primary">ro'yxatni ko'rish</a>
                        </label>
                        <input type="number" name="hybrid_pochta_area_id" class="form-control form-control-sm"
                            value="{{ \App\Models\Sozlama::ol('hybrid_pochta_area_id') }}"
                            placeholder="GET /api/area dan ID">
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
                    <div id="hpSpravochnik" class="col-12 d-none">
                        <pre id="hpSpravochnikPre" class="bg-light border rounded p-2 small" style="max-height:200px;overflow:auto;"></pre>
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
                @php $smsTest = \App\Models\NotificationSetting::get('sms','test_mode','1'); @endphp
                @if($smsTest === '1')
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
                            <label class="form-label small fw-medium">Test telefon</label>
                            <input type="text" name="test_phone" class="form-control form-control-sm"
                                   value="{{ $smsSoz['test_phone']->value ?? '' }}" placeholder="+998901234567">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Login</label>
                            <input type="text" name="login" class="form-control form-control-sm"
                                   value="{{ $smsSoz['login']->value ?? '' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Parol/Token</label>
                            <input type="password" name="password" class="form-control form-control-sm"
                                   placeholder="{{ ($smsSoz['password']->value??'')?'••••••••':'Kiriting' }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3 align-items-center">
                        <div class="form-check form-switch me-3">
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
}
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
