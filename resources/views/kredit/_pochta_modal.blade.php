{{-- ═══ Hybrid Pochta — Xat Yuborish Modali ═══════════════════════════ --}}
<div class="modal fade" id="pochtaXatModal" tabindex="-1" aria-labelledby="pochtaXatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">

      <div class="modal-header py-2" style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff">
        <h6 class="modal-title mb-0" id="pochtaXatModalLabel">
          <i class="bi bi-envelope-paper me-2"></i>Pochta Xat Yuborish
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">

        {{-- ─── QADAM 1: Ma'lumotlar ─────────────────────────────────── --}}
        <div id="hp-step1">
          <div class="p-3 border-bottom bg-light-subtle">
            <div class="d-flex align-items-center gap-2">
              <span class="badge rounded-pill bg-primary">1</span>
              <strong class="small">Xat ma'lumotlari</strong>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-secondary">2</span>
              <span class="small text-muted">E-IMZO imzo</span>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-secondary">3</span>
              <span class="small text-muted">Natija</span>
            </div>
          </div>

          <div class="p-3">
            {{-- Shablon --}}
            <div class="mb-3">
              <label class="form-label small fw-medium mb-1">
                Xat shabloni <span class="text-danger">*</span>
              </label>
              <select id="hp-shablon" class="form-select form-select-sm">
                <option value="">— Shablon tanlang —</option>
                @foreach($pochta_shablonlar as $sh)
                  <option value="{{ $sh->id }}"
                    data-kun="{{ $sh->qayta_yuborish_kun }}">
                    {{ $sh->nomi }}
                    @if($sh->qayta_yuborish_kun > 0)
                      ({{ $sh->qayta_yuborish_kun }} kun)
                    @endif
                  </option>
                @endforeach
              </select>
            </div>

            <div id="hp-limit-info" class="alert alert-warning py-2 small d-none"></div>

            {{-- FIO --}}
            <div class="mb-3">
              <label class="form-label small fw-medium mb-1">Qabul qiluvchi FIO <span class="text-danger">*</span></label>
              <input type="text" id="hp-receiver" class="form-control form-control-sm"
                value="{{ $kredit->mijoz->tolik_ism }}">
            </div>

            {{-- Manzil --}}
            <div class="mb-3">
              <label class="form-label small fw-medium mb-1">
                To'liq pochta manzili <span class="text-danger">*</span>
                <span class="text-muted fw-normal">(ko'cha, uy raqami, shahar/tuman)</span>
              </label>
              <textarea id="hp-address" class="form-control form-control-sm" rows="2">{{ $kredit->mijoz->manzil }}</textarea>
            </div>

            {{-- Viloyat / Tuman --}}
            <div class="row g-2 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-medium mb-1">Viloyat <span class="text-danger">*</span></label>
                <select id="hp-region" class="form-select form-select-sm">
                  <option value="">— Yuklanmoqda... —</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-medium mb-1">Tuman/Shahar <span class="text-danger">*</span></label>
                <select id="hp-area" class="form-select form-select-sm" disabled>
                  <option value="">— Avval viloyat tanlang —</option>
                </select>
              </div>
            </div>

            {{-- Ko'rinish --}}
            <div id="hp-preview-box" class="d-none">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="form-label small fw-medium mb-0">Xat matni ko'rinishi:</label>
                <a id="hp-preview-link" href="#" target="_blank" class="btn btn-xs btn-outline-secondary">
                  <i class="bi bi-file-pdf me-1"></i>PDF ko'rish
                </a>
              </div>
              <div id="hp-preview-text"
                class="border rounded p-2 small bg-light" style="white-space:pre-wrap;max-height:150px;overflow:auto;font-size:11px"></div>
            </div>
          </div>

          <div class="modal-footer py-2 bg-light-subtle border-top">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Bekor</button>
            <button id="hp-step1-next" type="button" class="btn btn-sm btn-primary" disabled>
              E-IMZO imzolash <i class="bi bi-arrow-right ms-1"></i>
            </button>
          </div>
        </div>

        {{-- ─── QADAM 2: E-IMZO imzolash ───────────────────────────── --}}
        <div id="hp-step2" class="d-none">
          <div class="p-3 border-bottom bg-light-subtle">
            <div class="d-flex align-items-center gap-2">
              <span class="badge rounded-pill bg-success">1</span>
              <span class="small text-muted">Ma'lumotlar</span>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-primary">2</span>
              <strong class="small">E-IMZO imzo</strong>
              <span class="mx-2 text-muted">→</span>
              <span class="badge rounded-pill bg-secondary">3</span>
              <span class="small text-muted">Natija</span>
            </div>
          </div>

          <div class="p-3">
            <div id="hp-eimzo-loading" class="text-center py-3 text-muted small">
              <div class="spinner-border spinner-border-sm me-2" role="status"></div>
              Xat yaratilmoqda va E-IMZO sertifikatlari yuklanmoqda...
            </div>

            <div id="hp-eimzo-error" class="alert alert-warning small d-none">
              <strong><i class="bi bi-exclamation-triangle me-1"></i>E-IMZO topilmadi.</strong><br>
              E-IMZO plugin o'rnatilgan va ishlab turganini tekshiring (<code>http://127.0.0.1:64443</code>).
              <br><a href="https://e-imzo.uz" target="_blank">e-imzo.uz</a> dan yuklab o'rnating.
            </div>

            <div id="hp-eimzo-keys-box" class="d-none">
              <div class="alert alert-info small py-2 mb-3">
                <i class="bi bi-info-circle me-1"></i>
                Sertifikat tanlang va "Imzolash va Yuborish" tugmasini bosing.
              </div>
              <label class="form-label small fw-medium mb-1">E-IMZO Sertifikat:</label>
              <select id="hp-eimzo-key" class="form-select form-select-sm mb-2">
                <option value="">— Sertifikat tanlang —</option>
              </select>
              <div id="hp-cert-info" class="text-muted small mt-1"></div>
            </div>
          </div>

          <div class="modal-footer py-2 bg-light-subtle border-top">
            <button id="hp-step2-back" type="button" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i>Orqaga
            </button>
            <button id="hp-sign-btn" type="button" class="btn btn-sm btn-primary d-none">
              <i class="bi bi-pen me-1"></i>Imzolash va Yuborish
            </button>
          </div>
        </div>

        {{-- ─── QADAM 3: Natija ──────────────────────────────────────── --}}
        <div id="hp-step3" class="d-none">
          <div class="p-4 text-center">
            <div id="hp-result-ok" class="d-none">
              <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
              <h5 class="mt-3 text-success">Xat muvaffaqiyatli yuborildi!</h5>
              <p class="text-muted small" id="hp-result-msg"></p>
              <a id="hp-receipt-link" href="#" target="_blank" class="btn btn-sm btn-outline-success mt-1 d-none">
                <i class="bi bi-file-pdf me-1"></i>Kvitansiya PDF
              </a>
            </div>
            <div id="hp-result-err" class="d-none">
              <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem"></i>
              <h5 class="mt-3 text-danger">Xatolik yuz berdi</h5>
              <p class="text-muted small" id="hp-result-err-msg"></p>
            </div>
          </div>
          <div class="modal-footer py-2 bg-light-subtle border-top">
            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"
              onclick="window.location.reload()">Yopish</button>
          </div>
        </div>

      </div>{{-- /modal-body --}}
    </div>
  </div>
</div>

@push('scripts')
<script>
(function() {
"use strict";

// ─── State ───────────────────────────────────────────────────────────────────
let state = {
  letterId: null,
  hash: null,
  logId: null,
  eimzoLoaded: false,
  eimzoKeys: [],
};
const csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
const kreditId = {{ $kredit->id }};

// ─── Elements ─────────────────────────────────────────────────────────────────
const step1     = () => document.getElementById('hp-step1');
const step2     = () => document.getElementById('hp-step2');
const step3     = () => document.getElementById('hp-step3');
const btnNext   = document.getElementById('hp-step1-next');
const btnBack   = document.getElementById('hp-step2-back');
const btnSign   = document.getElementById('hp-sign-btn');
const selShablon = document.getElementById('hp-shablon');
const selRegion  = document.getElementById('hp-region');
const selArea    = document.getElementById('hp-area');
const selKey     = document.getElementById('hp-eimzo-key');

// ─── Regions/Areas yüklash ────────────────────────────────────────────────────
async function loadRegions() {
  try {
    const r = await fetch('{{ route("malumotnamalar.viloyatlar.api") }}', {
      headers: { 'Accept': 'application/json' }
    });
    const arr = await r.json();

    arr.forEach(reg => {
      const opt = new Option(reg.nomi, reg.id);
      selRegion.appendChild(opt);
    });
  } catch(e) {
    selRegion.innerHTML = '<option value="">Viloyatlar yuklanmadi</option>';
  }
}

async function loadAreas(regionId, selectedId = '') {
  selArea.innerHTML = '<option value="">Yuklanmoqda...</option>';
  selArea.disabled = true;
  try {
    const r = await fetch('{{ route("malumotnamalar.viloyatlar.api.tumanlar") }}?viloyat_id=' + regionId + '', {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
    });
    const filtered = await r.json();

    selArea.innerHTML = '<option value="">— Tuman tanlang —</option>';
    filtered.forEach(area => {
      const opt = new Option(area.nomi, area.id, false, String(area.id) === String(selectedId));
      selArea.appendChild(opt);
    });
    selArea.disabled = false;
  } catch(e) {
    selArea.innerHTML = '<option value="">Tumanlar yuklanmadi</option>';
    selArea.disabled = false;
  }
}

selRegion.addEventListener('change', () => {
  if (selRegion.value) loadAreas(selRegion.value);
  else { selArea.innerHTML = '<option value="">— Avval viloyat tanlang —</option>'; selArea.disabled = true; }
  validateForm();
});
selArea.addEventListener('change', validateForm);

// ─── Shablon o'zgaruvchilari ──────────────────────────────────────────────────
const shablonlar = @json($pochta_shablonlar->pluck('matn', 'id'));
const kreditVars = @json($kredit_vars);

selShablon.addEventListener('change', function() {
  const id = this.value;
  const previewBox = document.getElementById('hp-preview-box');
  if (id && shablonlar[id]) {
    let matn = shablonlar[id];
    Object.entries(kreditVars).forEach(([k, v]) => {
      matn = matn.replaceAll('{{' + k + '}}', v);
    });
    document.getElementById('hp-preview-text').textContent = matn;
    const previewLink = document.getElementById('hp-preview-link');
    previewLink.href = `/kreditlar/${kreditId}/pochta/preview?shablon_id=${id}`;
    previewBox.classList.remove('d-none');
  } else {
    previewBox.classList.add('d-none');
  }
  checkLimit();
  validateForm();
});

// ─── Limit tekshirish ─────────────────────────────────────────────────────────
const limitInfo = document.getElementById('hp-limit-info');
const pochtaLoglar = @json($pochta_loglar->map(fn($l) => ['shablon_id' => $l->shablon_id, 'yuborildi_vaqt' => $l->yuborildi_vaqt?->toISOString(), 'holat' => $l->holat]));

function checkLimit() {
  const id = selShablon.value;
  const kun = selShablon.selectedOptions[0]?.dataset.kun;
  limitInfo.classList.add('d-none');
  if (!id || !kun || kun === '0') return;

  const lastLog = pochtaLoglar.find(l => String(l.shablon_id) === id && l.holat === 'yuborildi');
  if (!lastLog) return;

  const daysSince = Math.floor((Date.now() - new Date(lastLog.yuborildi_vaqt)) / 86400000);
  if (daysSince < parseInt(kun)) {
    limitInfo.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i>Oxirgi xat: <strong>${daysSince} kun</strong> oldin yuborilgan. Minimum oraliq: ${kun} kun. <strong>${kun - daysSince} kun qolgan.</strong>`;
    limitInfo.classList.remove('d-none');
  }
}

// ─── Form validatsiya ─────────────────────────────────────────────────────────
function validateForm() {
  const ok = selShablon.value && document.getElementById('hp-receiver').value.trim()
    && document.getElementById('hp-address').value.trim()
    && selRegion.value && selArea.value;
  btnNext.disabled = !ok;
}
['hp-receiver','hp-address'].forEach(id =>
  document.getElementById(id).addEventListener('input', validateForm)
);

// ─── E-IMZO ───────────────────────────────────────────────────────────────────
function loadEIMZO() {
  if (typeof EIMZOClient !== 'undefined') { initEIMZO(); return; }
  const s = document.createElement('script');
  s.src = 'http://127.0.0.1:64443/eimzo/eimzo.js';
  s.onload = initEIMZO;
  s.onerror = () => {
    document.getElementById('hp-eimzo-loading').classList.add('d-none');
    document.getElementById('hp-eimzo-error').classList.remove('d-none');
  };
  document.head.appendChild(s);
}

function initEIMZO() {
  try {
    EIMZOClient.API_KEYS = [['localhost', '96D0C1491615C82B9A54D9989779DF825B690748A7C9E9B0B5DA85F2FF7A7E29']];
    EIMZOClient.loadKeys(
      function(keys) {
        state.eimzoKeys = keys || [];
        document.getElementById('hp-eimzo-loading').classList.add('d-none');
        if (keys && keys.length > 0) {
          const sel = document.getElementById('hp-eimzo-key');
          sel.innerHTML = '<option value="">— Sertifikat tanlang —</option>';
          keys.forEach((k, i) => {
            const exp = k.validTo ? ` (${k.validTo.substring(0,10)})` : '';
            sel.appendChild(new Option(`${k.CN || k.alias}${exp}`, i));
          });
          document.getElementById('hp-eimzo-keys-box').classList.remove('d-none');
          sel.addEventListener('change', function() {
            btnSign.classList.toggle('d-none', !this.value && this.value !== '0');
            if (this.value !== '') {
              const k = state.eimzoKeys[parseInt(this.value)];
              document.getElementById('hp-cert-info').textContent =
                k ? `PINFL: ${k.TIN || '—'} | Tashkilot: ${k.O || '—'} | Muddati: ${k.validTo || '—'}` : '';
            }
          });
        } else {
          document.getElementById('hp-eimzo-error').innerHTML =
            '<i class="bi bi-exclamation-triangle me-1"></i>E-IMZO da sertifikat topilmadi. Sertifikat o\'rnatilganini tekshiring.';
          document.getElementById('hp-eimzo-error').classList.remove('d-none');
        }
        state.eimzoLoaded = true;
      },
      function(e, r) {
        document.getElementById('hp-eimzo-loading').classList.add('d-none');
        document.getElementById('hp-eimzo-error').classList.remove('d-none');
        document.getElementById('hp-eimzo-error').textContent = 'E-IMZO xato: ' + (e || r || 'Noma\'lum xato');
      }
    );
  } catch(ex) {
    document.getElementById('hp-eimzo-loading').classList.add('d-none');
    document.getElementById('hp-eimzo-error').classList.remove('d-none');
  }
}

// ─── Qadam 1 → 2 ─────────────────────────────────────────────────────────────
btnNext.addEventListener('click', async function() {
  btnNext.disabled = true;
  btnNext.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Xat yaratilmoqda...';

  const body = {
    shablon_id : selShablon.value,
    receiver   : document.getElementById('hp-receiver').value.trim(),
    address    : document.getElementById('hp-address').value.trim(),
    region_id  : selRegion.value,
    area_id    : selArea.value,
  };

  try {
    const resp = await fetch(`/kreditlar/${kreditId}/pochta/create`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await resp.json();

    if (!resp.ok || !data.ok) {
      alert('Xato: ' + (data.xato || 'Noma\'lum xato'));
      btnNext.disabled = false;
      btnNext.innerHTML = 'E-IMZO imzolash <i class="bi bi-arrow-right ms-1"></i>';
      return;
    }

    state.letterId = data.letter_id;
    state.hash     = data.hash;
    state.logId    = data.log_id;

    // Step 1 → Step 2
    step1().classList.add('d-none');
    step2().classList.remove('d-none');
    loadEIMZO();

  } catch(e) {
    alert('Tarmoq xatosi: ' + e.message);
    btnNext.disabled = false;
    btnNext.innerHTML = 'E-IMZO imzolash <i class="bi bi-arrow-right ms-1"></i>';
  }
});

// ─── Qadam 2 → 1 (orqaga) ────────────────────────────────────────────────────
btnBack.addEventListener('click', () => {
  step2().classList.add('d-none');
  step1().classList.remove('d-none');
  btnNext.disabled = false;
  btnNext.innerHTML = 'E-IMZO imzolash <i class="bi bi-arrow-right ms-1"></i>';
});

// ─── Imzolash va yuborish ─────────────────────────────────────────────────────
btnSign.addEventListener('click', function() {
  const keyIdx = parseInt(selKey.value);
  if (isNaN(keyIdx)) { alert('Sertifikat tanlang'); return; }

  const key = state.eimzoKeys[keyIdx];
  if (!key) { alert('Sertifikat topilmadi'); return; }

  btnSign.disabled = true;
  btnSign.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Imzolash...';

  // Hash → base64 konversiya
  function hexToBase64(hex) {
    const bytes = new Uint8Array(hex.match(/../g).map(h => parseInt(h, 16)));
    let bin = '';
    bytes.forEach(b => bin += String.fromCharCode(b));
    return btoa(bin);
  }

  const dataB64 = hexToBase64(state.hash);

  EIMZOClient.createPkcs7(
    key.id ?? key.serialNumber ?? keyIdx,
    dataB64,
    null,
    async function(pkcs7b64) {
      btnSign.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Yuborilmoqda...';

      try {
        const resp = await fetch(`/kreditlar/${kreditId}/pochta/send`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: JSON.stringify({ letter_id: state.letterId, signature: pkcs7b64, log_id: state.logId }),
        });
        const data = await resp.json();

        step2().classList.add('d-none');
        step3().classList.remove('d-none');

        if (resp.ok && data.ok) {
          document.getElementById('hp-result-ok').classList.remove('d-none');
          document.getElementById('hp-result-msg').textContent =
            `Xat ID: #${data.letter_id} · Log: #${data.log_id}`;
        } else {
          document.getElementById('hp-result-err').classList.remove('d-none');
          document.getElementById('hp-result-err-msg').textContent = data.xato || 'Noma\'lum xato';
        }
      } catch(e) {
        step2().classList.add('d-none');
        step3().classList.remove('d-none');
        document.getElementById('hp-result-err').classList.remove('d-none');
        document.getElementById('hp-result-err-msg').textContent = 'Tarmoq xatosi: ' + e.message;
      }
    },
    function(e, r) {
      btnSign.disabled = false;
      btnSign.innerHTML = '<i class="bi bi-pen me-1"></i>Imzolash va Yuborish';
      alert('E-IMZO imzolash xatosi: ' + (e || r || 'Noma\'lum'));
    }
  );
});

// ─── Modal ochilganda boshlash ────────────────────────────────────────────────
document.getElementById('pochtaXatModal').addEventListener('show.bs.modal', function() {
  // Reset state
  state = { letterId: null, hash: null, logId: null, eimzoLoaded: false, eimzoKeys: [] };
  step1().classList.remove('d-none');
  step2().classList.add('d-none');
  step3().classList.add('d-none');
  document.getElementById('hp-result-ok').classList.add('d-none');
  document.getElementById('hp-result-err').classList.add('d-none');
  document.getElementById('hp-eimzo-loading').classList.remove('d-none');
  document.getElementById('hp-eimzo-error').classList.add('d-none');
  document.getElementById('hp-eimzo-keys-box').classList.add('d-none');
  btnNext.disabled = true;
  btnNext.innerHTML = 'E-IMZO imzolash <i class="bi bi-arrow-right ms-1"></i>';

  // Viloyatlar ni load qil
  if (selRegion.options.length <= 1) loadRegions();
  validateForm();
});

})();
</script>
@endpush
