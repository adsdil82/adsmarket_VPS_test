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

  // Operatsion kun nazorati: shartnoma boshlanish sanasi bugundan farq qilsa (ya'ni
  // shartnoma boshqa operatsion kunda tuzilgan/tasdiqlangan), grafik sanalarini
  // o'zgartirish faqat admin uchun ochiq — boshqalar uchun bloklanadi.
  $admimi = (Auth::user()->rol ?? null) === 'admin';
  $grafikTahrirMumkin = $admimi || !$isEdit || !$kr?->boshlanish_sana || $kr->boshlanish_sana->isToday();

  /** Tanlangan mijoz/kafil uchun to'liq ma'lumot blokini quradi (telefon, manzil, PINFL, karta, izoh) */
  $mijozInfoHtml = function (?\App\Models\Mijoz $m) {
      if (!$m) return null;
      $fio = trim($m->familiya.' '.$m->ism);
      $passport = trim(($m->passport_seriya ?? '').' '.($m->passport_raqam ?? ''));
      $html  = '<div class="border rounded p-2 bg-light mt-1">';
      $html .= '<div><i class="bi bi-check-circle text-success me-1"></i><strong>'.e($fio).'</strong></div>';
      $html .= '<div class="text-muted mt-1"><i class="bi bi-telephone me-1"></i>'.e($m->telefon ?: '—');
      if ($passport)      $html .= ' &nbsp;&middot;&nbsp; <i class="bi bi-card-text me-1"></i>'.e($passport);
      if ($m->pinfl)       $html .= ' &nbsp;&middot;&nbsp; PINFL: '.e($m->pinfl);
      if ($m->karta_raqami) $html .= ' &nbsp;&middot;&nbsp; <i class="bi bi-credit-card me-1"></i>'.e($m->karta_raqami);
      $html .= '</div>';
      if ($m->manzil) $html .= '<div class="text-muted"><i class="bi bi-geo-alt me-1"></i>'.e($m->manzil).'</div>';
      if ($m->izoh)   $html .= '<div class="text-muted"><i class="bi bi-chat-left-text me-1"></i>'.e($m->izoh).'</div>';
      $html .= '</div>';
      return $html;
  };
@endphp

@push('styles')
<style>
/* ═══ Shartnoma formasi — bank stili skin (mavjud id/JS o'zgarmagan) ═══ */
#kreditTabs { border-bottom:none; background:linear-gradient(90deg,#1e3a8a,#1d4ed8); border-radius:8px 8px 0 0; padding:4px 4px 0; }
#kreditTabs .nav-link { color:#bfdbfe; border:none; border-radius:6px 6px 0 0; font-weight:600; transition:all .15s; }
#kreditTabs .nav-link:hover { color:#fff; background:rgba(255,255,255,.08); }
#kreditTabs .nav-link.active { background:#fff; color:#1e3a8a; }
#kreditTabsContent { border:1px solid #93c5fd; border-top:none; border-radius:0 0 8px 8px; background:#fff; }

#kreditTabsContent h6.fw-bold {
    border-bottom:none !important; border-left:4px solid #2563eb; background:#eef3ff;
    padding:8px 12px !important; border-radius:0 6px 6px 0; color:#1e3a8a !important;
    font-size:.88rem; margin-bottom:16px !important;
}
#kreditTabsContent h6.text-secondary { border-left-color:#94a3b8; background:#f8fafc; }

#kreditTabsContent .form-label { font-weight:700; color:#334155; font-size:.82rem; }
#kreditTabsContent .form-control, #kreditTabsContent .form-select {
    border:1px solid #cbd5e1;
}
#kreditTabsContent .form-control:focus, #kreditTabsContent .form-select:focus {
    border-color:#2563eb; box-shadow:0 0 0 .2rem rgba(37,99,235,.15);
}
#kreditTabsContent .form-control:read-only, #kreditTabsContent .form-control.bg-body-secondary {
    background:#f1f5f9 !important; font-weight:700;
}
#kreditTabsContent .form-text { font-size:.72rem; color:#64748b; }

/* Tovar qatorlari — mini bank-jadval ko'rinishi */
.tovar-qator { background:#fff; border:none; border-bottom:1px solid #e5edfb; border-radius:0; padding:5px 10px; margin-bottom:0 !important; }
.tovar-qator:nth-child(even) { background:#f8fafd; }
.tovar-qator:hover { background:#eef3ff; }
.bft-tovar-head {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; font-weight:600;
    font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; padding:7px 10px; margin:0;
}
.bft-tovar-jami { border-top:1px solid #93c5fd; background:#eef3ff; padding:10px 14px; }
.bft-tovar-jami #tovar-jami-display { color:#1d4ed8; }

/* Graf jadvali — bank-stil, ixcham, sticky header/footer */
.graf-bank-wrap {
    overflow:auto; max-height:480px; border:1px solid #93c5fd; border-radius:6px;
}
.graf-bank-table { font-size:.8rem; margin-bottom:0 !important; }
.graf-bank-table thead { position:sticky; top:0; z-index:5; }
.graf-bank-table thead.table-dark, .graf-bank-table .table-dark {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8) !important;
}
.graf-bank-table thead th {
    color:#fff !important; font-weight:800; font-size:.68rem; text-transform:uppercase;
    letter-spacing:.03em; border-color:rgba(255,255,255,.15) !important; padding:6px 8px;
}
.graf-bank-table tbody td { padding:3px 8px; vertical-align:middle; }
.graf-bank-table tbody tr:nth-child(odd)  td { background:#fff; }
.graf-bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.graf-bank-table tbody tr:hover td { background:#dbeafe !important; }
.graf-bank-table .graf-sana { padding:2px 6px; font-size:.76rem; height:26px; }
.graf-bank-table tfoot { position:sticky; bottom:0; z-index:5; }
.graf-bank-table tfoot.table-secondary td {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8) !important; color:#fff !important;
    padding:7px 8px; font-size:.82rem; border-top:2px solid #60a5fa;
}
.graf-bank-table tfoot .text-warning-emphasis { color:#fde68a !important; }
.graf-bank-table tfoot .text-success { color:#86efac !important; }

/* Bank-uslub forma jadvallari (tab1, tab6) */
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-section-title.bft-secondary { color:#475569; background:#f8fafc; border-left-color:#94a3b8; }
.bft-wrap { max-width:640px; border:1px solid #93c5fd; border-radius:6px; overflow:hidden; }
.bft-wrap-lg { max-width:900px; }
.bft-table { width:auto; margin-bottom:0 !important; font-size:.83rem; }
.bft-table td, .bft-table th { padding:7px 10px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:auto; }
.bft-doc-table { width:100%; table-layout:fixed; }
.bft-doc-table thead th {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; font-weight:600;
    padding:7px 10px; border-bottom:none;
}
.bft-doc-table tbody tr:hover { background:#eef3ff !important; }

/* Hisob-kitob (tab4) — ixcham, chap tomonga tortilgan bank-jadval ko'rinishi */
.hisob-wrap { max-width:640px; border:1px solid #93c5fd; border-radius:8px; overflow:hidden; }
.hisob-table { width:100%; border-collapse:collapse; font-size:.83rem; }
.hisob-table tr { border-bottom:1px solid #e2e8f4; }
.hisob-table tr:nth-child(odd)  td { background:#fff; }
.hisob-table tr:nth-child(even) td { background:#f5f8fd; }
.hisob-table td { padding:6px 12px; vertical-align:middle; }
.hisob-table .h-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; }
.hisob-table .h-inp { width:230px; }
.hisob-table .h-inp .form-control, .hisob-table .h-inp .form-select { font-size:.82rem; }
.hisob-table .h-hint { color:#64748b; font-size:.72rem; }
.hisob-table .h-highlight td { background:#eef3ff !important; font-weight:700; }
.hisob-table tr.h-sep td { padding:0; height:2px; background:#93c5fd !important; border:none; }
</style>
@endpush

{{-- ═══════════════════════════════════════════════════════════════
     TAB SARLAVHALARI
══════════════════════════════════════════════════════════════════ --}}
<ul class="nav nav-tabs mb-0 flex-nowrap overflow-auto" id="kreditTabs" role="tablist"
    style="scrollbar-width:none">
  @php $tabs=[
    ['id'=>'tab1','icon'=>'person-fill','label'=>'Mijoz va kafil'],
    ['id'=>'tab2','icon'=>'file-text-fill','label'=>'Shartnoma shartlari'],
    ['id'=>'tab3','icon'=>'cart-fill','label'=>'Tovarlar'],
    ['id'=>'tab4','icon'=>'calculator-fill','label'=>'Hisob-kitob'],
    ['id'=>'tab5','icon'=>'table','label'=>"To'lov grafigi"],
    ['id'=>'tab6','icon'=>'printer-fill','label'=>'Hujjatlar'],
    ['id'=>'tab7','icon'=>'person-badge-fill','label'=>'Mas\'ul xodim'],
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

  <div class="bft-section-title"><i class="bi bi-person-check me-1"></i>Asosiy mijoz</div>
  <div class="bft-wrap mb-3">
    <table class="bft-table">
      <tbody>
        <tr>
          <td class="bft-label">Mijoz <span class="text-danger">*</span></td>
          <td class="bft-wide">
            @php
              // Mijoz kartochkasidan "Yangi shartnoma" bosilganda controller'dan keladigan oldindan tanlangan mijoz
              $oldindanMijoz = $isEdit ? $kr?->mijoz : ($mijoz ?? null);
            @endphp
            <input type="hidden" name="mijoz_id" id="mijoz_id" value="{{ old('mijoz_id', $oldindanMijoz?->id) }}" required>
            <div class="input-group input-group-sm">
              <input type="text" id="mijoz-tanlangan" class="form-control fw-semibold"
                     placeholder="Mijoz tanlanmagan — qidirish uchun bosing..."
                     value="{{ $oldindanMijoz ? $oldindanMijoz->familiya.' '.$oldindanMijoz->ism : '' }}"
                     readonly style="cursor:pointer;background:#fff" onclick="mijozModalOch()">
              <button type="button" class="btn btn-primary" onclick="mijozModalOch()">
                <i class="bi bi-person-search me-1"></i><span class="d-none d-sm-inline">Qidirish</span>
              </button>
            </div>
            <div id="mijoz-info" class="small mt-1">
              @if($oldindanMijoz)
                {!! $mijozInfoHtml($oldindanMijoz) !!}
              @else
                <span class="text-danger" id="mijoz-info-xato"><i class="bi bi-exclamation-circle me-1"></i>Mijoz tanlanmagan</span>
              @endif
            </div>
          </td>
        </tr>
        <tr>
          <td class="bft-label">Filial <span class="text-danger">*</span></td>
          <td class="bft-wide">
            <select name="filial_id" class="form-select form-select-sm @error('filial_id') is-invalid @enderror"
                    style="max-width:280px" {{ count($filiallar) === 1 ? 'disabled' : '' }}>
              @foreach($filiallar as $f)
                <option value="{{ $f->id }}"
                  {{ $old('filial_id', count($filiallar)===1?$filiallar->first()->id:($oldindanMijoz->filial_id ?? '')) == $f->id ? 'selected':'' }}>
                  {{ $f->nomi }}
                </option>
              @endforeach
            </select>
            @if(count($filiallar) === 1)
              <input type="hidden" name="filial_id" value="{{ $filiallar->first()->id }}">
            @endif
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="bft-section-title bft-secondary"><i class="bi bi-people me-1"></i>Kafil <small class="fw-normal" style="opacity:.8">(ixtiyoriy)</small></div>
  <div class="bft-wrap mb-2">
    <table class="bft-table">
      <tbody>
        <tr>
          <td class="bft-label">Kafil</td>
          <td class="bft-wide">
            <input type="hidden" name="kafil_mijoz_id" id="kafil_mijoz_id" value="{{ $old('kafil_mijoz_id') }}">
            <div class="input-group input-group-sm">
              <input type="text" id="kafil-tanlangan" class="form-control fw-semibold"
                     placeholder="Kafil tanlanmagan — qidirish uchun bosing..."
                     value="{{ $isEdit && $kr?->kafil ? $kr->kafil->familiya.' '.$kr->kafil->ism : '' }}"
                     readonly style="cursor:pointer;background:#fff" onclick="mijozModalOch('kafil')">
              <button type="button" class="btn btn-secondary" onclick="mijozModalOch('kafil')">
                <i class="bi bi-person-search me-1"></i><span class="d-none d-sm-inline">Qidirish</span>
              </button>
              <button type="button" class="btn btn-outline-danger" onclick="kafilTanlovniTozala()" title="Kafilni olib tashlash">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
            <div id="kafil-info" class="small mt-1">
              @if($isEdit && $kr?->kafil)
                {!! $mijozInfoHtml($kr->kafil) !!}
              @else
                <span class="text-muted">Kafil tanlanmagan (ixtiyoriy)</span>
              @endif
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <a href="#" class="small text-decoration-none d-inline-block mb-2"
     onclick="document.getElementById('kafil-qolda').classList.toggle('d-none'); return false;">
    <i class="bi bi-pencil-square me-1"></i>Kafil ro'yxatda topilmasa — qo'lda kiritish
  </a>
  <div id="kafil-qolda" class="bft-wrap mb-3 {{ ($isEdit && $kr?->kafil_ism && !$kr?->kafil_mijoz_id) ? '' : 'd-none' }}">
    <table class="bft-table">
      <tbody>
        <tr>
          <td class="bft-label">F.I.O.</td>
          <td class="bft-wide"><input type="text" name="kafil_ism" class="form-control form-control-sm" style="max-width:320px" value="{{ $old('kafil_ism') }}" placeholder="Kafil ismi familiyasi"></td>
        </tr>
        <tr>
          <td class="bft-label">Telefon</td>
          <td class="bft-wide"><input type="text" name="kafil_telefon" class="form-control form-control-sm" style="max-width:320px" value="{{ $old('kafil_telefon') }}" placeholder="+998 90 000 00 00"></td>
        </tr>
        <tr>
          <td class="bft-label">Manzil</td>
          <td class="bft-wide"><input type="text" name="kafil_manzil" class="form-control form-control-sm" style="max-width:320px" value="{{ $old('kafil_manzil') }}" placeholder="Kafil manzili"></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="bft-wrap mb-3">
    <table class="bft-table">
      <tbody>
        <tr>
          <td class="bft-label">Izoh</td>
          <td class="bft-wide">
            <textarea name="izoh" class="form-control form-control-sm" rows="2"
                      placeholder="Qo'shimcha ma'lumot...">{{ $old('izoh') }}</textarea>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-end mt-2">
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

  @php
    $orqagaSanaTaqiqlangan = \App\Models\Sozlama::ol('orqaga_sana_taqiqlansin','1') === '1'
        && (Auth::user()->rol ?? null) !== 'admin';
  @endphp

  <div class="bft-wrap mb-3">
    <table class="bft-table">
      <tbody>
        @if($isEdit)
        <tr>
          <td class="bft-label">Shartnoma raqami</td>
          <td class="bft-wide">
            <input type="text" class="form-control form-control-sm bg-body-secondary fw-bold text-primary"
                   style="max-width:280px" value="{{ $kr->shartnoma_raqam }}" readonly>
          </td>
        </tr>
        <tr>
          <td class="bft-label">Holat</td>
          <td class="bft-wide">
            <select name="holat" class="form-select form-select-sm @error('holat') is-invalid @enderror" style="max-width:280px">
              @foreach(['faol'=>'Faol','muddati_otgan'=>"Muddati o'tgan",'muzlatilgan'=>'Muzlatilgan','yopilgan'=>'Yopilgan'] as $v=>$l)
                <option value="{{ $v }}" {{ $old('holat','faol') === $v ? 'selected':'' }}>{{ $l }}</option>
              @endforeach
            </select>
          </td>
        </tr>
        @endif
        <tr>
          <td class="bft-label">Boshlanish sanasi <span class="text-danger">*</span></td>
          <td class="bft-wide">
            <input type="date" name="boshlanish_sana" id="boshlanish_sana"
                   class="form-control form-control-sm @error('boshlanish_sana') is-invalid @enderror"
                   style="max-width:200px"
                   value="{{ old('boshlanish_sana', $isEdit && $kr?->boshlanish_sana ? $kr->boshlanish_sana->format('Y-m-d') : date('Y-m-d')) }}"
                   @if($orqagaSanaTaqiqlangan) min="{{ date('Y-m-d') }}" @endif
                   onchange="tugashSanaHisoblash();grafikKorsatish()">
            @if($orqagaSanaTaqiqlangan)
            <div class="form-text"><i class="bi bi-info-circle me-1"></i>O'tgan kunga sana qo'yish faqat admin uchun ochiq.</div>
            @endif
          </td>
        </tr>
        <tr>
          <td class="bft-label">Tugash sanasi</td>
          <td class="bft-wide">
            <input type="date" name="tugash_sana" id="tugash_sana"
                   class="form-control form-control-sm bg-body-secondary" style="max-width:200px"
                   value="{{ $old('tugash_sana', $isEdit && $kr?->tugash_sana ? $kr->tugash_sana->format('Y-m-d') : '') }}"
                   readonly>
          </td>
        </tr>
        <tr>
          <td class="bft-label">Muddat <span class="text-danger">*</span></td>
          <td class="bft-wide">
            <select name="muddati_oy" id="muddati_oy"
                    class="form-select form-select-sm @error('muddati_oy') is-invalid @enderror" style="max-width:200px"
                    onchange="hisoblash();tugashSanaHisoblash();grafikKorsatish()">
              @for($m=1; $m<=36; $m++)
                <option value="{{ $m }}" {{ $old('muddati_oy',12) == $m ? 'selected':'' }}>{{ $m }} oy</option>
              @endfor
            </select>
          </td>
        </tr>
        <tr>
          <td class="bft-label">To'lov kuni <span class="text-danger">*</span></td>
          <td class="bft-wide">
            <select name="tolov_kuni" id="tolov_kuni"
                    class="form-select form-select-sm @error('tolov_kuni') is-invalid @enderror" style="max-width:200px"
                    onchange="tugashSanaHisoblash();grafikKorsatish()">
              @for($d=1; $d<=31; $d++)
                <option value="{{ $d }}" {{ $old('tolov_kuni',5) == $d ? 'selected':'' }}>Har oyning {{ $d }}-si</option>
              @endfor
            </select>
          </td>
        </tr>
      </tbody>
    </table>
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
  <div class="bft-wrap bft-wrap-lg">
  <div class="row g-2 mb-0 fw-bold bft-tovar-head">
    <div class="col-sm-5">Tovar nomi</div>
    <div class="col-sm-2">Soni</div>
    <div class="col-sm-3">Narx (so'm)</div>
    <div class="col-4 col-sm-1">Jami</div>
    <div class="col-4 col-sm-1"></div>
  </div>
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
  <div class="p-2">
    <button type="button" class="btn btn-sm btn-outline-success" onclick="tovarQosh()">
      <i class="bi bi-plus-lg me-1"></i>Tovar qo'shish
    </button>
  </div>
  <div class="bft-tovar-jami d-flex justify-content-between align-items-center">
    <span class="fw-semibold">Jami tovar summasi:</span>
    <span class="fs-5 fw-bold" id="tovar-jami-display">0 so'm</span>
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
  <div class="hisob-wrap">
    <table class="hisob-table">
      <tbody>
        <tr>
          <td class="h-label">1. Jami tovar summasi</td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="text" id="tovar_summa_display" class="form-control bg-body-secondary fw-semibold text-end" readonly>
              <span class="input-group-text">so'm</span>
            </div>
          </td>
          <td class="h-hint">Tab 3 dagi tovarlar yig'indisi</td>
        </tr>
        <tr>
          <td class="h-label">Ustama</td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="number" name="foiz_stavka" id="foiz_stavka"
                     class="form-control text-end" value="{{ $old('foiz_stavka',0) }}"
                     min="0" max="500" step="0.1" oninput="hisoblash();grafikKorsatish()">
              <span class="input-group-text">%</span>
            </div>
          </td>
          <td class="h-hint">0 = ustamasiz</td>
        </tr>
        <tr>
          <td class="h-label">+ Ustama summasi</td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="text" id="foiz_summa_display" class="form-control bg-body-secondary fw-semibold text-warning-emphasis text-end" readonly>
              <span class="input-group-text">so'm</span>
            </div>
          </td>
          <td class="h-hint"></td>
        </tr>
        <tr class="h-highlight">
          <td class="h-label">= Jami summa (ustama bilan)</td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="text" id="jami_summa_display" class="form-control bg-body-secondary fw-bold text-primary text-end" readonly>
              <span class="input-group-text">so'm</span>
            </div>
          </td>
          <td class="h-hint"></td>
        </tr>

        <tr class="h-sep"><td colspan="3"></td></tr>

        <tr>
          <td class="h-label">- Oldindan to'lov <span class="text-danger">*</span></td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="number" name="boshlangich_tolov" id="boshlangich_tolov"
                     class="form-control text-end @error('boshlangich_tolov') is-invalid @enderror"
                     value="{{ $old('boshlangich_tolov',0) }}" min="0" step="1000"
                     oninput="hisoblash();grafikKorsatish()">
              <span class="input-group-text">so'm</span>
            </div>
          </td>
          <td class="h-hint text-danger d-none" id="oldindan-tolov-xato">Jami summadan oshib ketdi!</td>
        </tr>
        <tr>
          <td class="h-label">To'lov turi</td>
          <td class="h-inp">
            <select name="oldin_tolov_turi" id="oldin_tolov_turi" class="form-select form-select-sm">
              <option value="">— tanlang —</option>
              <option value="naqd">Naqd</option>
              <option value="terminal">Terminal (karta)</option>
              <option value="bank">Bank o'tkazmasi</option>
              <option value="online">Online</option>
            </select>
          </td>
          <td class="h-hint"></td>
        </tr>
        <tr class="h-highlight">
          <td class="h-label">= Qoldiq (nasiya summasi)</td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="text" id="kredit_summa_display" class="form-control bg-body-secondary fw-bold text-success text-end" readonly>
              <span class="input-group-text">so'm</span>
            </div>
          </td>
          <td class="h-hint"></td>
        </tr>
        <tr class="h-highlight">
          <td class="h-label">Oylik to'lov</td>
          <td class="h-inp">
            <div class="input-group input-group-sm">
              <input type="text" id="oylik_display" class="form-control bg-body-secondary fw-bold text-info text-end" readonly>
              <span class="input-group-text">so'm</span>
            </div>
          </td>
          <td class="h-hint"></td>
        </tr>
      </tbody>
    </table>

    {{-- Hidden computed fields --}}
    <input type="hidden" id="tovar_summa_hidden" value="{{ $old('jami_summa',0) }}">
    <input type="hidden" id="jami_summa_hidden" name="jami_summa" value="{{ $old('jami_summa',0) }}">
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
  @unless($grafikTahrirMumkin)
  <div class="alert alert-warning d-flex align-items-center mb-3">
    <i class="bi bi-lock-fill me-2"></i>
    Bu shartnoma boshqa operatsion kunda tuzilgan — to'lov sanalarini o'zgartirish faqat
    admin uchun ochiq.
  </div>
  @endunless
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
  <div class="bft-wrap">
    <table class="bft-table bft-doc-table">
      <thead>
        <tr><th style="width:36px"></th><th>Hujjat nomi</th><th style="width:110px">Chop etish</th><th style="width:80px">Ko'rish</th><th style="width:90px">Tahrirlash</th></tr>
      </thead>
      <tbody>
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
    @php $kafilBiriktirilgan = $kredit->kafil_mijoz_id || $kredit->kafil_ism; @endphp
    @foreach($hujjatlar as $h)
      @continue($h['key'] === 'kafillik' && !$kafilBiriktirilgan)
        <tr>
          <td class="text-center"><i class="bi bi-{{ $h['icon'] }} text-{{ $h['rang'] }}"></i></td>
          <td class="fw-semibold">{{ $h['nom'] }}</td>
          <td class="text-center">
            <a href="{{ route('kreditlar.hujjat', [$kredit, $h['key']]) }}"
               target="_blank"
               class="btn btn-sm btn-outline-{{ $h['rang'] }}"
               title="Chop etish">
              <i class="bi bi-printer{{ $admimi ? '' : ' me-1' }}"></i>{{ $admimi ? '' : 'Chop etish' }}
            </a>
          </td>
          <td class="text-center">
            @if($admimi)
            <button type="button" class="btn btn-sm btn-outline-{{ $h['rang'] }}"
                    title="Ko'rish"
                    onclick="hujjatModalOch('{{ route('kreditlar.hujjat.html', [$kredit, $h['key']]) }}', '{{ $h['nom'] }}', false)">
              <i class="bi bi-eye"></i>
            </button>
            @endif
          </td>
          <td class="text-center">
            @if($admimi)
            <button type="button" class="btn btn-sm btn-outline-{{ $h['rang'] }}"
                    title="Tahrirlash"
                    onclick="hujjatModalOch('{{ route('kreditlar.hujjat.html', [$kredit, $h['key']]) }}', '{{ $h['nom'] }}', true)">
              <i class="bi bi-pencil"></i>
            </button>
            @endif
          </td>
        </tr>
    @endforeach
    @if(!$kafilBiriktirilgan)
        <tr class="text-muted">
          <td class="text-center"><i class="bi bi-people-fill"></i></td>
          <td colspan="4">
            Kafillik shartnomasi — <small><i class="bi bi-info-circle me-1"></i>Kafil biriktirilmagan — "Mijoz va kafil" tabidan kafil qo'shing.</small>
          </td>
        </tr>
    @endif
      </tbody>
    </table>
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
    <button type="button" class="btn btn-primary" onclick="tabKetish('tab7')">
      Keyingi<i class="bi bi-arrow-right ms-1"></i>
    </button>
  </div>
</div>

{{-- ─────────────────────── TAB 7: KREDITNIK ──────────────────────── --}}
<div class="tab-pane fade p-3" id="tab7" role="tabpanel">
  <div class="bft-section-title"><i class="bi bi-person-badge-fill me-1"></i>Mas'ul kredit xodimi</div>
  <div class="bft-wrap mb-3">
    <table class="bft-table">
      <tbody>
        <tr>
          <td class="bft-label">Kredit xodimi (kreditnik)</td>
          <td class="bft-wide">
            <select name="joriy_xodim_id" class="form-select form-select-sm" style="max-width:340px">
              <option value="">— Shartnomani tuzayotgan xodim ({{ Auth::user()->ism_familiya }}) —</option>
              @foreach($xodimlar as $x)
                <option value="{{ $x->id }}"
                  {{ $old('joriy_xodim_id', $isEdit ? ($kr->joriy_xodim_id ?? $kr->xodim_id) : '') == $x->id ? 'selected':'' }}>
                  {{ $x->ism_familiya }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Shartnoma bo'yicha to'lovlarni nazorat qiluvchi/mas'ul xodim. Bo'sh qoldirilsa — shartnomani tuzgan xodim mas'ul bo'ladi.</div>
          </td>
        </tr>
        <tr>
          <td class="bft-label">Biriktirilgan sana</td>
          <td class="bft-wide">
            <input type="text" class="form-control form-control-sm bg-body-secondary" style="max-width:220px" readonly
                   value="{{ $isEdit ? (($xodimTarixi->first()?->created_at ?? $kr->created_at)->format('d.m.Y H:i')) : now()->format('d.m.Y H:i') }}">
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  @if($isEdit)
  <div class="bft-section-title bft-secondary"><i class="bi bi-clock-history me-1"></i>Tayinlash tarixi</div>
  @if($xodimTarixi->isEmpty())
  <p class="text-muted small">Bu shartnoma hali boshqa xodimga qayta tayinlanmagan — boshidan beri <strong>{{ $kr->xodim->ism_familiya ?? '—' }}</strong> mas'ul.</p>
  @else
  <div class="bft-wrap bft-wrap-lg mb-3">
    <table class="bft-table bft-doc-table">
      <thead>
        <tr>
          <th>Sana</th>
          <th>Eski xodim</th>
          <th>Yangi xodim</th>
          <th>Sabab</th>
          <th>O'zgartirgan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($xodimTarixi as $tx)
        <tr>
          <td class="text-nowrap small">{{ $tx->created_at->format('d.m.Y H:i') }}</td>
          <td>{{ $tx->eskiXodim->ism_familiya ?? '—' }}</td>
          <td>{{ $tx->yangiXodim->ism_familiya ?? '—' }}</td>
          <td class="small">{{ $tx->sabab }}</td>
          <td class="small text-muted">{{ $tx->ozgartirgan->ism_familiya ?? '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
  @endif

  <div class="d-flex justify-content-between mt-4">
    <button type="button" class="btn btn-outline-secondary" onclick="tabKetish('tab6')">
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
var grafikTahrirMumkin = {!! $grafikTahrirMumkin ? 'true' : 'false' !!};

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
    // 1) Jami tovar summasi (tab3 dan)
    const tovarSumma = parseFloat(document.getElementById('tovar_summa_hidden')?.value) || 0;
    // 2) + Ustama
    const ustama     = parseFloat(document.getElementById('foiz_stavka')?.value) || 0;
    const ustamaSumma = tovarSumma * ustama / 100;
    // 3) = Jami summa (ustama bilan)
    const jami = tovarSumma + ustamaSumma;
    // 4) - Oldindan to'lov
    let oldin = parseFloat(document.getElementById('boshlangich_tolov')?.value) || 0;
    const oldinXatoEl = document.getElementById('oldindan-tolov-xato');
    const oldinInp    = document.getElementById('boshlangich_tolov');
    if (oldin > jami) {
        if (oldinXatoEl) oldinXatoEl.classList.remove('d-none');
        if (oldinInp) oldinInp.setCustomValidity("Oldindan to'lov jami summadan oshib ketdi!");
    } else {
        if (oldinXatoEl) oldinXatoEl.classList.add('d-none');
        if (oldinInp) oldinInp.setCustomValidity('');
    }
    // 5) = Qoldiq (nasiya summasi)
    const kredit = Math.max(0, jami - oldin);
    const muddat = parseInt(document.getElementById('muddati_oy')?.value) || 1;
    const oylik  = kredit / muddat;

    // Displeylar
    var disp = function(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = typeof val === 'number' ? formatSon(Math.round(val)) : val;
    };
    disp('tovar_summa_display', tovarSumma);
    disp('foiz_summa_display', ustamaSumma);
    disp('jami_summa_display', jami);
    disp('kredit_summa_display', kredit);
    disp('oylik_display', oylik);

    // Hidden fields
    var setHid = function(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = Math.round(val);
    };
    setHid('jami_summa_hidden', jami);
    setHid('kredit_summa_hidden', kredit);
    setHid('qoldiq_qarz_hidden', kredit);
    setHid('oylik_hidden', oylik);

    tugashSanaHisoblash();
}

// ── Oy/yil/kun bo'yicha sana hisoblash (Date.setMonth overflow'siz) ──
// MUHIM: oddiy "dt.setMonth(dt.getMonth()+n); dt.setDate(kun)" usuli xato beradi —
// agar boshlang'ich kun (masalan 31) keyingi oyda mavjud bo'lmasa (Fevral),
// JS Date avtomatik keyingi oyga "to'kilib" ketadi (31-yanvar + 1 oy = 3-mart,
// fevral butunlay tashlab ketiladi), va shu oy uchun to'lov sanasi 2 oy keyinga
// siljiydi — grafik qatorlari orasida 31 kundan ortiq farq paydo bo'lib,
// "to'lovlar orasi 1 oydan oshmasligi kerak" xatosini keltirib chiqaradi.
// Shu sabab yil/oy/kunni alohida hisoblab, Date faqat oxirida quriladi.
function oyQushibSana(boshSana, oyQoshish, kuni) {
    const bosh = new Date(boshSana);
    const jamiOy = bosh.getMonth() + oyQoshish;
    const yil = bosh.getFullYear() + Math.floor(jamiOy / 12);
    const oy  = ((jamiOy % 12) + 12) % 12;
    const maxDay = new Date(yil, oy + 1, 0).getDate();
    return new Date(yil, oy, Math.min(kuni, maxDay));
}

// ── Tugash sanasi hisoblash ──────────────────────────────────────
function tugashSanaHisoblash() {
    const bosh   = document.getElementById('boshlanish_sana')?.value;
    const muddat = parseInt(document.getElementById('muddati_oy')?.value) || 1;
    const kuni   = parseInt(document.getElementById('tolov_kuni')?.value) || 5;
    if (!bosh) return;

    const dt = oyQushibSana(bosh, muddat, kuni);

    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, '0');
    const d = String(dt.getDate()).padStart(2, '0');
    var el = document.getElementById('tugash_sana');
    if (el) el.value = y + '-' + m + '-' + d;
}

// ── Graf ko'rsatish ──────────────────────────────────────────────
function grafikKorsatish() {
    const tovarSumma = parseFloat(document.getElementById('tovar_summa_hidden')?.value) || 0;
    const jami   = parseFloat(document.getElementById('jami_summa_hidden')?.value) || 0;
    const oldin  = parseFloat(document.getElementById('boshlangich_tolov')?.value) || 0;
    const kredit = Math.max(0, jami - oldin);
    const muddat = parseInt(document.getElementById('muddati_oy')?.value) || 1;
    const kuni   = parseInt(document.getElementById('tolov_kuni')?.value) || 5;
    const bosh   = document.getElementById('boshlanish_sana')?.value;
    const cont   = document.getElementById('graf-container');
    if (!cont) return;

    if (kredit <= 0 || !bosh) {
        cont.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-table fs-2 d-block mb-2 opacity-25"></i>Graf ko\'rish uchun tovar va muddat kiriting</div>';
        return;
    }

    // Ustamaning jami summadagi ulushi bo'yicha har oylik to'lovdan ustama qismini ajratamiz
    const ustamaJami = Math.max(0, jami - tovarSumma);
    const ustamaUlush = jami > 0 ? ustamaJami / jami : 0;

    const oylik = kredit / muddat;
    const readonlyAttr = grafikTahrirMumkin ? '' : 'readonly';
    let rows = '';
    let qoldiq = kredit;
    let asosiyJami = 0, ustamaJamiYig = 0;
    for (let i = 1; i <= muddat; i++) {
        const dt = oyQushibSana(bosh, i - 1, kuni);
        const isoSana = dt.getFullYear() + '-' + String(dt.getMonth()+1).padStart(2,'0') + '-' + String(dt.getDate()).padStart(2,'0');

        const buoy = i === muddat ? qoldiq : Math.round(oylik);
        qoldiq -= buoy;
        const buoyUstama = Math.round(buoy * ustamaUlush);
        const buoyAsosiy = buoy - buoyUstama;
        asosiyJami += buoyAsosiy;
        ustamaJamiYig += buoyUstama;

        rows += `<tr class="graf-qator" data-tartib="${i}">
            <td class="text-center fw-bold text-muted">${i}</td>
            <td>
              <input type="date" name="grafik[${i}][sana]" value="${isoSana}" class="form-control form-control-sm graf-sana" ${readonlyAttr}
                     onchange="grafikSanaTekshir(this)">
              <div class="small text-danger d-none graf-sana-xato"><i class="bi bi-exclamation-triangle me-1"></i>Oldingi to'lovdan 31 kundan oshib ketdi!</div>
            </td>
            <td class="text-end">${formatSon(Math.abs(buoyAsosiy))}</td>
            <td class="text-end text-warning-emphasis">${formatSon(Math.abs(buoyUstama))}</td>
            <td class="text-end fw-bold">
              <input type="hidden" name="grafik[${i}][summa]" value="${Math.round(buoy)}">
              <input type="hidden" name="grafik[${i}][ustama]" value="${buoyUstama}">
              ${formatSon(Math.abs(Math.round(buoy)))}
            </td>
        </tr>`;
    }

    cont.innerHTML = `
    <div class="graf-bank-wrap">
    <table class="table table-sm table-bordered mb-0 graf-bank-table">
      <thead class="table-dark">
        <tr>
          <th class="text-center" style="width:40px">#</th>
          <th style="min-width:150px">Sana</th>
          <th class="text-end">Asosiy qarz (so'm)</th>
          <th class="text-end">Ustama (so'm)</th>
          <th class="text-end">Jami to'lov (so'm)</th>
        </tr>
      </thead>
      <tbody>${rows}</tbody>
      <tfoot class="table-secondary fw-bold">
        <tr>
          <td colspan="2" class="text-end">Jami:</td>
          <td class="text-end">${formatSon(Math.round(asosiyJami))}</td>
          <td class="text-end text-warning-emphasis">${formatSon(Math.round(ustamaJamiYig))}</td>
          <td class="text-end text-success">${formatSon(Math.round(kredit))}</td>
        </tr>
      </tfoot>
    </table>
    </div>`;
}

// ── Grafik qatoridagi sana o'zgartirilganda — ~1 oylik (31 kun) oraliqni tekshirish ──
// Chegara 31 kun (30 emas): har xil oylar 28-31 kun bo'ladi, oddiy "har oyning shu kunida"
// grafigi ham shu farqni beradi — 30ga qattiq cheklansa standart oylik grafik ham rad etiladi.
function grafikSanaTekshir(input) {
    const row = input.closest('.graf-qator');
    const allRows = Array.from(document.querySelectorAll('.graf-qator'));
    const idx = allRows.indexOf(row);
    const xatoEl = row.querySelector('.graf-sana-xato');
    if (idx > 0 && input.value) {
        const prevInput = allRows[idx - 1].querySelector('.graf-sana');
        const diffKun = Math.round((new Date(input.value) - new Date(prevInput.value)) / 86400000);
        const oshibKetdi = diffKun > 31 || diffKun < 1;
        row.classList.toggle('table-danger', oshibKetdi);
        if (xatoEl) xatoEl.classList.toggle('d-none', !oshibKetdi);
    }
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
    // Tab 4 hisob-kitobiga tovar summasini uzatish
    var ts = document.getElementById('tovar_summa_hidden');
    if (ts) { ts.value = Math.round(total); hisoblash(); }
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

// ── Yaroqsiz (required/invalid) maydon boshqa tabda bo'lsa, avtomatik shu tabga o'tish ──
// Aks holda brauzer ko'rinmas maydonga fokus bera olmay, Saqlash hech narsa qilmagandek bo'lib qoladi.
(function() {
    var form = document.getElementById('kredit-form');
    if (!form) return;
    form.addEventListener('invalid', function(e) {
        var field = e.target;
        var pane = field.closest('.tab-pane');
        if (pane && pane.id) {
            tabKetish(pane.id);
        }
    }, true);
})();
</script>
@endpush
