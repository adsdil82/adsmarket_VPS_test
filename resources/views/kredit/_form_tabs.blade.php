{{--
  kredit/_form_tabs.blade.php
  6-vkladkali forma: yaratish va tahrirlash uchun
  O'zgaruvchilar:
    $isEdit   — bool (edit rejimi)
    $kredit   — RegKredit model (edit da)
    $filiallar, $tovarGuruhlar — har doim
--}}
@php
  $isEdit  = $isEdit  ?? false;
  $kr      = $isEdit ? $kredit : null;
  $old     = fn($k,$d='') => old($k, $isEdit && $kr ? data_get($kr,$k,$d) : $d);
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     TAB SARLAVHALARI
══════════════════════════════════════════════════════════════════ --}}
<ul class="nav nav-tabs mb-0 flex-nowrap overflow-auto" id="kreditTabs" role="tablist"
    style="border-bottom:2px solid #dee2e6;scrollbar-width:none">
  @php $tabs=[
    ['id'=>'tab1','icon'=>'person-fill','label'=>'Mijoz &amp; Kafil'],
    ['id'=>'tab2','icon'=>'file-text-fill','label'=>'Shartnoma'],
    ['id'=>'tab3','icon'=>'cart-fill','label'=>'Tovarlar'],
    ['id'=>'tab4','icon'=>'calculator-fill','label'=>'Hisob-kitob'],
    ['id'=>'tab5','icon'=>'table','label'=>'Graf'],
    ['id'=>'tab6','icon'=>'printer-fill','label'=>'Hujjatlar'],
  ]; @endphp
  @foreach($tabs as $i=>$t)
  <li class="nav-item" role="presentation">
    <button class="nav-link d-flex align-items-center gap-1 px-3 py-2 {{ $i===0?'active':'' }}"
            id="{{ $t['id'] }}-btn" data-bs-toggle="tab" data-bs-target="#{{ $t['id'] }}"
            type="button" role="tab">
      <i class="bi bi-{{ $t['icon'] }} small"></i>
      <span class="d-none d-sm-inline small fw-semibold">{!! $t['label'] !!}</span>
      <span class="tab-badge d-none badge rounded-pill bg-danger" id="badge-{{ $t['id'] }}"></span>
    </button>
  </li>
  @endforeach
</ul>

{{-- ═══════════════════════════════════════════════════════════════
     TAB KONTENTLARI
══════════════════════════════════════════════════════════════════ --}}
<div class="tab-content" id="kreditTabsContent">

{{-- ─────────────────────── TAB 1: MIJOZ & KAFIL ─────────────────── --}}
<div class="tab-pane fade show active p-3" id="tab1" role="tabpanel">
  <div class="row g-3">

    {{-- Mijoz tanlash --}}
    <div class="col-12">
      <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
        <i class="bi bi-person-check me-1"></i>Asosiy mijoz
      </h6>
      <input type="hidden" name="mijoz_id" id="mijoz_id"
             value="{{ $old('mijoz_id') }}" required>
      <div class="input-group">
        <input type="text" id="mijoz-tanlangan" class="form-control fw-semibold"
               placeholder="Mijoz tanlanmagan — qidirish uchun bosing..."
               value="{{ $isEdit && $kr?->mijoz ? $kr->mijoz->familiya.' '.$kr->mijoz->ism : '' }}"
               readonly style="cursor:pointer;background:#fff" onclick="mijozModalOch()">
        <button type="button" class="btn btn-primary" onclick="mijozModalOch()">
          <i class="bi bi-person-search me-1"></i><span class="d-none d-sm-inline">Qidirish</span>
        </button>
      </div>
      <div id="mijoz-info" class="small mt-1">
        @if($isEdit && $kr?->mijoz)
          <span class="text-success"><i class="bi bi-check-circle me-1"></i>
            {{ $kr->mijoz->familiya }} {{ $kr->mijoz->ism }}
            · {{ $kr->mijoz->telefon }}</span>
        @else
          <span class="text-danger" id="mijoz-info-xato"><i class="bi bi-exclamation-circle me-1"></i>Mijoz tanlanmagan</span>
        @endif
      </div>
    </div>

    {{-- Filial --}}
    <div class="col-sm-6">
      <label class="form-label fw-medium">Filial <span class="text-danger">*</span></label>
      <select name="filial_id" class="form-select @error('filial_id') is-invalid @enderror"
              {{ count($filiallar) === 1 ? 'disabled' : '' }}>
        @foreach($filiallar as $f)
          <option value="{{ $f->id }}"
            {{ $old('filial_id', count($filiallar)===1?$filiallar->first()->id:'') == $f->id ? 'selected':'' }}>
            {{ $f->nomi }}
          </option>
        @endforeach
      </select>
      @if(count($filiallar) === 1)
        <input type="hidden" name="filial_id" value="{{ $filiallar->first()->id }}">
      @endif
    </div>

    {{-- Kafil --}}
    <div class="col-12 mt-2">
      <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">
        <i class="bi bi-people me-1"></i>Kafil ma'lumotlari <small class="fw-normal text-muted">(ixtiyoriy)</small>
      </h6>
    </div>
    <div class="col-sm-4">
      <label class="form-label">F.I.O.</label>
      <input type="text" name="kafil_ism" class="form-control"
             value="{{ $old('kafil_ism') }}" placeholder="Kafil ismi familiyasi">
    </div>
    <div class="col-sm-4">
      <label class="form-label">Telefon</label>
      <input type="text" name="kafil_telefon" class="form-control"
             value="{{ $old('kafil_telefon') }}" placeholder="+998 90 000 00 00">
    </div>
    <div class="col-sm-4">
      <label class="form-label">Manzil</label>
      <input type="text" name="kafil_manzil" class="form-control"
             value="{{ $old('kafil_manzil') }}" placeholder="Kafil manzili">
    </div>

    {{-- Izoh --}}
    <div class="col-12">
      <label class="form-label">Izoh / Eslatma</label>
      <textarea name="izoh" class="form-control" rows="2"
                placeholder="Qo'shimcha ma'lumot...">{{ $old('izoh') }}</textarea>
    </div>
  </div>

  <div class="d-flex justify-content-end mt-4">
    <button type="button" class="btn btn-primary" onclick="tabKetish('tab2')">
      Keyingi: Shartnoma shartlari <i class="bi bi-arrow-right ms-1"></i>
    </button>
  </div>
</div>

{{-- ─────────────────────── TAB 2: SHARTNOMA SHARTLARI ──────────── --}}
<div class="tab-pane fade p-3" id="tab2" role="tabpanel">
  @if($isEdit)
  <div class="alert alert-warning py-2 mb-3">
    <label class="form-label fw-medium mb-1">
      O'zgartirish sababi <span class="text-danger">*</span>
    </label>
    <input type="text" name="sabab" class="form-control @error('sabab') is-invalid @enderror"
           value="{{ old('sabab') }}"
           placeholder="Masalan: Muddat o'zgardi, foiz yangilandi..."
           minlength="5" required>
  </div>
  @endif

  @if($isEdit)
  <div class="mb-3">
    <label class="form-label fw-medium">Holat</label>
    <select name="holat" class="form-select @error('holat') is-invalid @enderror">
      @foreach(['faol'=>'Faol','muddati_otgan'=>"Muddati o'tgan",'muzlatilgan'=>'Muzlatilgan','yopilgan'=>'Yopilgan'] as $v=>$l)
        <option value="{{ $v }}" {{ $old('holat','faol') === $v ? 'selected':'' }}>{{ $l }}</option>
      @endforeach
    </select>
  </div>
  @endif

  <div class="row g-3">
    @if($isEdit)
    <div class="col-sm-6">
      <label class="form-label fw-medium">Shartnoma raqami</label>
      <input type="text" class="form-control bg-body-secondary fw-bold text-primary"
             value="{{ $kr->shartnoma_raqam }}" readonly>
    </div>
    @endif

    <div class="col-sm-6">
      <label class="form-label fw-medium">Boshlanish sanasi <span class="text-danger">*</span></label>
      <input type="date" name="boshlanish_sana" id="boshlanish_sana"
             class="form-control @error('boshlanish_sana') is-invalid @enderror"
             value="{{ $old('boshlanish_sana', date('Y-m-d')) }}"
             onchange="tugashSanaHisoblash();grafikKorsatish()">
    </div>

    <div class="col-sm-6">
      <label class="form-label fw-medium">Tugash sanasi</label>
      <input type="date" name="tugash_sana" id="tugash_sana"
             class="form-control bg-body-secondary"
             value="{{ $old('tugash_sana', $isEdit && $kr?->tugash_sana ? $kr->tugash_sana->format('Y-m-d') : '') }}"
             readonly>
    </div>

    <div class="col-sm-4">
      <label class="form-label fw-medium">Muddat <span class="text-danger">*</span></label>
      <select name="muddati_oy" id="muddati_oy"
              class="form-select @error('muddati_oy') is-invalid @enderror"
              onchange="hisoblash();tugashSanaHisoblash();grafikKorsatish()">
        @for($m=1; $m<=36; $m++)
          <option value="{{ $m }}" {{ $old('muddati_oy',12) == $m ? 'selected':'' }}>{{ $m }} oy</option>
        @endfor
      </select>
    </div>

    <div class="col-sm-4">
      <label class="form-label fw-medium">To'lov kuni <span class="text-danger">*</span></label>
      <select name="tolov_kuni" id="tolov_kuni"
              class="form-select @error('tolov_kuni') is-invalid @enderror"
              onchange="tugashSanaHisoblash();grafikKorsatish()">
        @for($d=1; $d<=31; $d++)
          <option value="{{ $d }}" {{ $old('tolov_kuni',5) == $d ? 'selected':'' }}>Har oyning {{ $d }}-si</option>
        @endfor
      </select>
    </div>

    <div class="col-sm-4">
      <label class="form-label fw-medium">Foiz stavkasi (%/yil)</label>
      <div class="input-group">
        <input type="number" name="foiz_stavka" id="foiz_stavka"
               class="form-control" value="{{ $old('foiz_stavka',0) }}"
               min="0" max="100" step="0.1" oninput="hisoblash();grafikKorsatish()">
        <span class="input-group-text">%</span>
      </div>
      <div class="form-text">0 = foizsiz</div>
    </div>
  </div>

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" onclick="tabKetish('tab1')">
      <i class="bi bi-arrow-left me-1"></i>Oldingi
    </button>
    <button type="button" class="btn btn-primary" onclick="tabKetish('tab3')">
      Keyingi: Tovarlar <i class="bi bi-arrow-right ms-1"></i>
    </button>
  </div>
</div>

{{-- ─────────────────────── TAB 3: TOVARLAR ─────────────────────── --}}
<div class="tab-pane fade p-3" id="tab3" role="tabpanel">
  <div id="tovarlar-container">
    @if($isEdit && $kr?->tovarlar->count())
      @foreach($kr->tovarlar as $i=>$tv)
      <div class="tovar-qator row g-2 mb-2 align-items-center">
        <div class="col-sm-5">
          <div class="input-group input-group-sm">
            <input type="text" name="tovarlar[{{ $i }}][nomi]"
                   class="form-control form-control-sm tovar-nomi-inp"
                   value="{{ $tv->nomi }}" placeholder="Tovar nomi" required>
            <button type="button" class="btn btn-outline-primary btn-sm tovar-izlash-btn"
                    onclick="tovarModalOch(this)" title="Ombordan tovar tanlash">
              <i class="bi bi-tv"></i>
            </button>
          </div>
          <input type="hidden" name="tovarlar[{{ $i }}][tovar_katalog_id]"
                 class="tovar-katalog-id" value="{{ $tv->tovar_katalog_id }}">
        </div>
        <div class="col-sm-2">
          <input type="number" name="tovarlar[{{ $i }}][soni]"
                 class="form-control form-control-sm tovar-soni"
                 value="{{ $tv->soni }}" min="1" oninput="tovarJamiHisoblash(this)">
        </div>
        <div class="col-sm-3">
          <input type="number" name="tovarlar[{{ $i }}][narx]"
                 class="form-control form-control-sm tovar-narx"
                 value="{{ $tv->narx }}" min="0" step="1000" oninput="tovarJamiHisoblash(this)">
        </div>
        <div class="col-4 col-sm-1">
          <input type="text" class="form-control form-control-sm bg-body-secondary tovar-jami"
                 value="{{ number_format($tv->jami_narx, 0, '.', ' ') }}" readonly>
        </div>
        <div class="col-4 col-sm-1">
          <button type="button" class="btn btn-sm btn-outline-danger" onclick="tovarOchir(this)">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
      @endforeach
    @else
    <div class="tovar-qator row g-2 mb-2 align-items-center">
      <div class="col-sm-5">
        <div class="input-group input-group-sm">
          <input type="text" name="tovarlar[0][nomi]"
                 class="form-control form-control-sm tovar-nomi-inp"
                 placeholder="Tovar nomi" required>
          <button type="button" class="btn btn-outline-primary btn-sm tovar-izlash-btn"
                  onclick="tovarModalOch(this)" title="Ombordan tovar tanlash">
            <i class="bi bi-tv"></i>
          </button>
        </div>
        <input type="hidden" name="tovarlar[0][tovar_katalog_id]" class="tovar-katalog-id" value="">
      </div>
      <div class="col-sm-2">
        <input type="number" name="tovarlar[0][soni]"
               class="form-control form-control-sm tovar-soni"
               value="1" min="1" oninput="tovarJamiHisoblash(this)">
      </div>
      <div class="col-sm-3">
        <input type="number" name="tovarlar[0][narx]"
               class="form-control form-control-sm tovar-narx"
               value="0" min="0" step="1000" oninput="tovarJamiHisoblash(this)">
      </div>
      <div class="col-4 col-sm-1">
        <input type="text" class="form-control form-control-sm bg-body-secondary tovar-jami"
               readonly placeholder="Jami">
      </div>
      <div class="col-4 col-sm-1">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="tovarOchir(this)">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>
    @endif
  </div>

  {{-- Tovar qo'shish tugmasi + sarlavhalar --}}
  <div class="row g-2 mb-1 mt-1 d-none d-sm-flex text-muted" style="font-size:.75rem">
    <div class="col-sm-5">Tovar nomi</div>
    <div class="col-sm-2">Soni</div>
    <div class="col-sm-3">Narx (so'm)</div>
    <div class="col-sm-1">Jami</div>
  </div>

  <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="tovarQosh()">
    <i class="bi bi-plus-lg me-1"></i>Tovar qo'shish
  </button>

  <div class="mt-3 p-3 bg-light rounded border">
    <div class="d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Jami tovar summasi:</span>
      <span class="fs-5 fw-bold text-primary" id="tovar-jami-display">0 so'm</span>
    </div>
  </div>

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" onclick="tabKetish('tab2')">
      <i class="bi bi-arrow-left me-1"></i>Oldingi
    </button>
    <button type="button" class="btn btn-primary" onclick="tovarlarTekshir()">
      Keyingi: Hisob-kitob <i class="bi bi-arrow-right ms-1"></i>
    </button>
  </div>
</div>

{{-- ─────────────────────── TAB 4: HISOB-KITOB ──────────────────── --}}
<div class="tab-pane fade p-3" id="tab4" role="tabpanel">
  <div class="row g-3">

    <div class="col-sm-6">
      <label class="form-label fw-medium">Jami tovar summasi</label>
      <div class="input-group">
        <input type="number" name="jami_summa" id="jami_summa"
               class="form-control fw-bold text-primary @error('jami_summa') is-invalid @enderror"
               value="{{ $old('jami_summa',0) }}" min="0" step="1000"
               oninput="hisoblash();grafikKorsatish()">
        <span class="input-group-text">so'm</span>
      </div>
      <div class="form-text">Tab 3 da tovarlar qo'shilganda avtomatik to'ladi</div>
    </div>

    <div class="col-sm-6">
      <label class="form-label fw-medium">Foiz summasi</label>
      <div class="input-group">
        <input type="text" id="foiz_summa_display" class="form-control bg-body-secondary fw-semibold text-warning-emphasis" readonly>
        <span class="input-group-text">so'm</span>
      </div>
    </div>

    <div class="col-sm-4">
      <label class="form-label fw-medium">Oldindan to'lov <span class="text-danger">*</span></label>
      <div class="input-group">
        <input type="number" name="boshlangich_tolov" id="boshlangich_tolov"
               class="form-control @error('boshlangich_tolov') is-invalid @enderror"
               value="{{ $old('boshlangich_tolov',0) }}" min="0" step="1000"
               oninput="hisoblash();grafikKorsatish()">
        <span class="input-group-text">so'm</span>
      </div>
    </div>

    <div class="col-sm-4">
      <label class="form-label fw-medium">To'lov turi</label>
      <select name="oldin_tolov_turi" id="oldin_tolov_turi" class="form-select">
        <option value="">— tanlang —</option>
        <option value="naqd">Naqd</option>
        <option value="terminal">Terminal (karta)</option>
        <option value="bank">Bank o'tkazmasi</option>
        <option value="online">Online</option>
      </select>
    </div>

    <div class="col-sm-4">
      <label class="form-label fw-medium">Nasiya summasi</label>
      <div class="input-group">
        <input type="text" id="kredit_summa_display" class="form-control bg-body-secondary fw-bold text-success fs-5" readonly>
        <span class="input-group-text">so'm</span>
      </div>
    </div>

    <div class="col-sm-6">
      <label class="form-label fw-medium">Oylik to'lov</label>
      <div class="input-group">
        <input type="text" id="oylik_display" class="form-control bg-body-secondary fw-bold text-info" readonly>
        <span class="input-group-text">so'm</span>
      </div>
    </div>

    {{-- Hidden computed fields --}}
    <input type="hidden" id="kredit_summa_hidden" name="kredit_summa" value="{{ $old('kredit_summa',0) }}">
    <input type="hidden" id="qoldiq_qarz_hidden" name="qoldiq_qarz" value="{{ $old('qoldiq_qarz',0) }}">
    <input type="hidden" id="oylik_hidden" name="oylik_tolov_miqdori" value="{{ $old('oylik_tolov_miqdori',0) }}">
    <input type="hidden" name="tolov_qilingan" value="0">
  </div>

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" onclick="tabKetish('tab3')">
      <i class="bi bi-arrow-left me-1"></i>Oldingi
    </button>
    <button type="button" class="btn btn-primary" onclick="tabKetish('tab5');grafikKorsatish()">
      Keyingi: Graf <i class="bi bi-arrow-right ms-1"></i>
    </button>
  </div>
</div>

{{-- ─────────────────────── TAB 5: GRAF ─────────────────────────── --}}
<div class="tab-pane fade p-3" id="tab5" role="tabpanel">
  <div id="graf-container">
    <div class="text-center text-muted py-5">
      <i class="bi bi-table fs-2 d-block mb-2 opacity-25"></i>
      Graf ko'rish uchun tovar va muddat kiriting
    </div>
  </div>

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" onclick="tabKetish('tab4')">
      <i class="bi bi-arrow-left me-1"></i>Oldingi
    </button>
    <button type="button" class="btn btn-primary" onclick="tabKetish('tab6')">
      Keyingi: Hujjatlar <i class="bi bi-arrow-right ms-1"></i>
    </button>
  </div>
</div>

{{-- ─────────────────────── TAB 6: HUJJATLAR ────────────────────── --}}
<div class="tab-pane fade p-3" id="tab6" role="tabpanel">
  @if($isEdit)
  <div class="row g-3">
    @php
    $hujjatlar = [
      ['key'=>'shartnoma',   'icon'=>'file-earmark-text',  'rang'=>'primary',  'nom'=>'Nasiya shartnoma'],
      ['key'=>'kafillik',    'icon'=>'people-fill',        'rang'=>'secondary','nom'=>'Kafillik shartnomasi'],
      ['key'=>'grafik',      'icon'=>'table',              'rang'=>'info',     'nom'=>"To'lov grafigi"],
      ['key'=>'yuk_xati',    'icon'=>'truck',              'rang'=>'warning',  'nom'=>'Yuk xati'],
      ['key'=>'schyot',      'icon'=>'receipt',            'rang'=>'success',  'nom'=>'Schyot-faktura'],
      ['key'=>'ariza',       'icon'=>'envelope-text',      'rang'=>'danger',   'nom'=>'Rahbarga ariza'],
      ['key'=>'til_xat',     'icon'=>'pen-fill',           'rang'=>'dark',     'nom'=>"Til xat (majburiyat)"],
    ];
    @endphp
    @foreach($hujjatlar as $h)
    <div class="col-sm-6 col-lg-4">
      <div class="card border-{{ $h['rang'] }} border-opacity-25 h-100">
        <div class="card-body d-flex flex-column">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="bg-{{ $h['rang'] }} bg-opacity-10 text-{{ $h['rang'] }} rounded p-2">
              <i class="bi bi-{{ $h['icon'] }} fs-5"></i>
            </span>
            <span class="fw-semibold small">{{ $h['nom'] }}</span>
          </div>
          <div class="mt-auto pt-2">
            <a href="{{ route('kreditlar.hujjat', [$kredit, $h['key']]) }}"
               target="_blank"
               class="btn btn-sm btn-outline-{{ $h['rang'] }} w-100">
              <i class="bi bi-printer me-1"></i>Chop etish
            </a>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  @else
  <div class="alert alert-info py-3">
    <i class="bi bi-info-circle me-2"></i>
    Shartnoma <strong>saqlanganidan keyin</strong> hujjatlarni chop etish imkoni ochiladi.
  </div>
  @endif

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" onclick="tabKetish('tab5')">
      <i class="bi bi-arrow-left me-1"></i>Oldingi
    </button>
    <button type="submit" class="btn btn-success btn-lg px-5">
      <i class="bi bi-check-circle me-2"></i>
      {{ $isEdit ? 'Saqlash' : 'Shartnoma yaratish' }}
    </button>
  </div>
</div>

</div>{{-- /tab-content --}}

@push('scripts')
<script>
// ════════════════════════════════════════════════════════════════
// SHARTNOMA FORM — umumiy JS
// ════════════════════════════════════════════════════════════════

let tovarIndex = {{ $isEdit && $kr?->tovarlar->count() ? $kr->tovarlar->count() : 1 }};

// ── Tab navigatsiya ──────────────────────────────────────────────
function tabKetish(id) {
    const el = document.getElementById(id + '-btn');
    if (!el) return;
    // Bootstrap 5 Tab API — ishonchli usul
    bootstrap.Tab.getOrCreateInstance(el).show();
    // Modal ichida bo'lsa modal-body ni, aks holda sahifani scroll qil
    const modalBody = el.closest('.modal-body');
    if (modalBody) {
        modalBody.scrollTop = 0;
    } else {
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
}

// ── Moliyaviy hisoblash ──────────────────────────────────────────
function hisoblash() {
    const jami   = parseFloat(document.getElementById('jami_summa')?.value) || 0;
    const oldin  = parseFloat(document.getElementById('boshlangich_tolov')?.value) || 0;
    const kredit = Math.max(0, jami - oldin);
    const muddat = parseInt(document.getElementById('muddati_oy')?.value) || 1;
    const foiz   = parseFloat(document.getElementById('foiz_stavka')?.value) || 0;

    const foizSumma = foiz > 0 ? kredit * foiz / 100 : 0;
    let oylik = kredit / muddat;
    if (foiz > 0) {
        oylik = (kredit + foizSumma) / muddat;
    }

    // Displeylar
    var disp = function(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = typeof val === 'number' ? formatSon(Math.round(val)) : val;
    };
    disp('kredit_summa_display', kredit);
    disp('foiz_summa_display', foizSumma);
    disp('oylik_display', oylik);

    // Hidden fields
    var setHid = function(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = Math.round(val);
    };
    setHid('kredit_summa_hidden', kredit);
    setHid('qoldiq_qarz_hidden', kredit);
    setHid('oylik_hidden', oylik);

    tugashSanaHisoblash();
}

// ── Tugash sanasi hisoblash ──────────────────────────────────────
function tugashSanaHisoblash() {
    const bosh   = document.getElementById('boshlanish_sana')?.value;
    const muddat = parseInt(document.getElementById('muddati_oy')?.value) || 1;
    const kuni   = parseInt(document.getElementById('tolov_kuni')?.value) || 5;
    if (!bosh) return;

    const dt = new Date(bosh);
    dt.setMonth(dt.getMonth() + muddat);
    // To'lov kuniga moslashtirish
    var maxDay = new Date(dt.getFullYear(), dt.getMonth() + 1, 0).getDate();
    dt.setDate(Math.min(kuni, maxDay));

    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, '0');
    const d = String(dt.getDate()).padStart(2, '0');
    var el = document.getElementById('tugash_sana');
    if (el) el.value = y + '-' + m + '-' + d;
}

// ── Graf ko'rsatish ──────────────────────────────────────────────
function grafikKorsatish() {
    const jami   = parseFloat(document.getElementById('jami_summa')?.value) || 0;
    const oldin  = parseFloat(document.getElementById('boshlangich_tolov')?.value) || 0;
    const kredit = Math.max(0, jami - oldin);
    const muddat = parseInt(document.getElementById('muddati_oy')?.value) || 1;
    const foiz   = parseFloat(document.getElementById('foiz_stavka')?.value) || 0;
    const kuni   = parseInt(document.getElementById('tolov_kuni')?.value) || 5;
    const bosh   = document.getElementById('boshlanish_sana')?.value;
    const cont   = document.getElementById('graf-container');
    if (!cont) return;

    if (kredit <= 0 || !bosh) {
        cont.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-table fs-2 d-block mb-2 opacity-25"></i>Graf ko\'rish uchun tovar va muddat kiriting</div>';
        return;
    }

    const foizSumma  = foiz > 0 ? kredit * foiz / 100 : 0;
    const jami_kredit = kredit + foizSumma;
    const oylikAsosiy = kredit / muddat;
    const oylikFoiz   = foizSumma / muddat;
    const oylik       = jami_kredit / muddat;

    let rows = '';
    let qoldiq = jami_kredit;
    for (let i = 1; i <= muddat; i++) {
        const dt = new Date(bosh);
        dt.setMonth(dt.getMonth() + i - 1);
        var maxDay = new Date(dt.getFullYear(), dt.getMonth() + 1, 0).getDate();
        dt.setDate(Math.min(kuni, maxDay));

        const buoy = i === muddat ? qoldiq : Math.round(oylik);
        const buoyAsosiy = i === muddat ? Math.round(oylikAsosiy * i - Math.round(oylikAsosiy) * (i - 1)) : Math.round(oylikAsosiy);
        const buoyFoiz   = i === muddat ? Math.round(buoy - buoyAsosiy) : Math.round(oylikFoiz);
        qoldiq -= buoy;

        const sana = dt.toLocaleDateString('uz-UZ', {year:'numeric', month:'2-digit', day:'2-digit'});
        const holat = i <= {{ $isEdit && $kr?->grafik ? 'Math.max(...Array.from(document.querySelectorAll(".graf-tolangan")).map(()=>0))' : '0' }} ? 'table-success' : '';
        rows += `<tr class="${holat}">
            <td class="text-center fw-bold text-muted">${i}</td>
            <td class="text-center">${sana}</td>
            <td class="text-end">${formatSon(Math.abs(Math.round(buoyAsosiy)))}</td>
            <td class="text-end text-warning-emphasis">${formatSon(Math.abs(Math.round(buoyFoiz)))}</td>
            <td class="text-end fw-bold">${formatSon(Math.abs(Math.round(buoy)))}</td>
        </tr>`;
    }

    cont.innerHTML = `
    <div class="table-responsive">
    <table class="table table-sm table-hover table-bordered mb-0">
      <thead class="table-dark">
        <tr>
          <th class="text-center" style="width:50px">#</th>
          <th class="text-center">Sana</th>
          <th class="text-end">Asosiy (so'm)</th>
          <th class="text-end">Ustama (so'm)</th>
          <th class="text-end">Jami (so'm)</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
      <tfoot class="table-secondary fw-bold">
        <tr>
          <td colspan="2" class="text-end">Jami:</td>
          <td class="text-end">${formatSon(Math.round(kredit))}</td>
          <td class="text-end text-warning-emphasis">${formatSon(Math.round(foizSumma))}</td>
          <td class="text-end text-success">${formatSon(Math.round(jami_kredit))}</td>
        </tr>
      </tfoot>
    </table>
    </div>`;
}

// ── Tovar operatsiyalari ─────────────────────────────────────────
function tovarJamiHisoblash(inp) {
    const row  = inp.closest('.tovar-qator');
    const soni = parseFloat(row.querySelector('.tovar-soni')?.value) || 0;
    const narx = parseFloat(row.querySelector('.tovar-narx')?.value) || 0;
    const jami = row.querySelector('.tovar-jami');
    if (jami) jami.value = formatSon(Math.round(soni * narx));
    tovarJamiYig();
}

function tovarJamiYig() {
    let total = 0;
    document.querySelectorAll('.tovar-qator').forEach(function(row) {
        const soni = parseFloat(row.querySelector('.tovar-soni')?.value) || 0;
        const narx = parseFloat(row.querySelector('.tovar-narx')?.value) || 0;
        total += soni * narx;
    });
    // Tab 3 display
    var td = document.getElementById('tovar-jami-display');
    if (td) td.textContent = formatSon(Math.round(total)) + ' so\'m';
    // Tab 4 jami_summa ga yozish
    var js = document.getElementById('jami_summa');
    if (js) { js.value = Math.round(total); hisoblash(); }
}

function tovarQosh() {
    const i = tovarIndex++;
    const row = `<div class="tovar-qator row g-2 mb-2 align-items-center">
      <div class="col-sm-5">
        <div class="input-group input-group-sm">
          <input type="text" name="tovarlar[${i}][nomi]" class="form-control form-control-sm tovar-nomi-inp" placeholder="Tovar nomi" required>
          <button type="button" class="btn btn-outline-primary btn-sm tovar-izlash-btn" onclick="tovarModalOch(this)" title="Ombordan tanlash">
            <i class="bi bi-tv"></i>
          </button>
        </div>
        <input type="hidden" name="tovarlar[${i}][tovar_katalog_id]" class="tovar-katalog-id" value="">
      </div>
      <div class="col-sm-2">
        <input type="number" name="tovarlar[${i}][soni]" class="form-control form-control-sm tovar-soni" value="1" min="1" oninput="tovarJamiHisoblash(this)">
      </div>
      <div class="col-sm-3">
        <input type="number" name="tovarlar[${i}][narx]" class="form-control form-control-sm tovar-narx" value="0" min="0" step="1000" oninput="tovarJamiHisoblash(this)">
      </div>
      <div class="col-4 col-sm-1">
        <input type="text" class="form-control form-control-sm bg-body-secondary tovar-jami" readonly>
      </div>
      <div class="col-4 col-sm-1">
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="tovarOchir(this)"><i class="bi bi-trash"></i></button>
      </div>
    </div>`;
    document.getElementById('tovarlar-container').insertAdjacentHTML('beforeend', row);
}

function tovarOchir(btn) {
    if (document.querySelectorAll('.tovar-qator').length > 1) {
        btn.closest('.tovar-qator').remove();
        tovarJamiYig();
    }
}

function tovarlarTekshir() {
    let bor = false;
    document.querySelectorAll('.tovar-qator').forEach(function(r) {
        const soni = parseFloat(r.querySelector('.tovar-soni')?.value) || 0;
        const narx = parseFloat(r.querySelector('.tovar-narx')?.value) || 0;
        if (soni > 0 && narx > 0) bor = true;
    });
    if (!bor) {
        alert('Kamida 1 ta tovar kiriting (soni va narxi > 0).');
        return;
    }
    tabKetish('tab4');
}

// ── Format yordamchi ─────────────────────────────────────────────
function formatSon(n) {
    if (isNaN(n)) return '0';
    return Math.abs(n).toLocaleString('uz-UZ');
}

// ── Sahifa yuklanishida hisoblash ────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    tovarJamiYig();
    hisoblash();
    tugashSanaHisoblash();
    @if($isEdit) grafikKorsatish(); @endif
});
</script>
@endpush
