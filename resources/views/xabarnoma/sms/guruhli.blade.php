@extends('layouts.app')
@section('title','SMS — Guruhli yuborish')
@section('breadcrumb')
    <li class="breadcrumb-item active">SMS — Guruhli yuborish</li>
@endsection

@push('styles')
<style>
.yuborish-tur-karta { cursor:pointer;border:2px solid transparent;border-radius:10px;padding:14px 12px;transition:all .2s; }
.yuborish-tur-karta:hover { border-color:var(--bs-primary); }
.yuborish-tur-karta.tanlangan { border-color:var(--bs-primary);background:rgba(13,110,253,.07); }
.yuborish-tur-karta .karta-icon { font-size:1.6rem; }
</style>
@endpush

@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-chat-dots me-2 text-warning"></i>SMS — Guruhli yuborish</h5>

{{-- Navigatsiya tabs --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" href="{{ route('xabarnoma.sms.guruhli') }}">Guruhli</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.yakka') }}">Yakka</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.tarix') }}">Tarix</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.shablonlar.index') }}">Shablonlar</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.sozlamalar') }}">Sozlamalar</a></li>
</ul>

<form method="POST" action="{{ route('xabarnoma.sms.guruhli.send') }}" id="guruhli-form">
@csrf

<div class="row g-3">

  {{-- CHAP: Filtr va yuborish turi --}}
  <div class="col-lg-7">

    {{-- 1. Yuborish turini tanlash --}}
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header py-2 fw-bold"><i class="bi bi-funnel me-2"></i>1. Yuborish turini tanlang</div>
      <div class="card-body">
        <input type="hidden" name="type" id="tur-input" value="">
        <div class="row g-2" id="tur-kartalar">
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="overdue" onclick="turTanla('overdue',this)">
              <div class="karta-icon">⚠️</div>
              <div class="small fw-bold mt-1">Kechikkan kreditlar</div>
              <div class="text-muted" style="font-size:.7rem">Muddati o'tgan</div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="upcoming" onclick="turTanla('upcoming',this)">
              <div class="karta-icon">📅</div>
              <div class="small fw-bold mt-1">Oldindan ogohlantirish</div>
              <div class="text-muted" style="font-size:.7rem">Yaqinlashgan to'lov</div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="branch" onclick="turTanla('branch',this)">
              <div class="karta-icon">🏢</div>
              <div class="small fw-bold mt-1">Filial bo'yicha</div>
              <div class="text-muted" style="font-size:.7rem">Filial mijozlari</div>
            </div>
          </div>
          <div class="col-6 col-md-4">
            <div class="yuborish-tur-karta text-center" data-tur="custom" onclick="turTanla('custom',this)">
              <div class="karta-icon">🔧</div>
              <div class="small fw-bold mt-1">Custom filter</div>
              <div class="text-muted" style="font-size:.7rem">O'zingiz tanlang</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- 2. Filtrlar --}}
    <div class="card border-0 shadow-sm mb-3" id="filtr-blok" style="display:none">
      <div class="card-header py-2 fw-bold"><i class="bi bi-sliders me-2"></i>2. Filtrlar</div>
      <div class="card-body">

        {{-- Kechikkan kunlar (overdue) --}}
        <div id="filtr-overdue" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Kechikkan kun (dan)</label>
              <select name="min_days" class="form-select form-select-sm">
                <option value="1">1 kundan</option><option value="4">4 kundan</option>
                <option value="8">8 kundan</option><option value="16">16 kundan</option>
                <option value="31">31 kundan</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Kechikkan kun (gacha)</label>
              <select name="max_days" class="form-select form-select-sm">
                <option value="3">3 kungacha</option><option value="7">7 kungacha</option>
                <option value="15">15 kungacha</option><option value="30">30 kungacha</option>
                <option value="9999" selected>Cheksiz</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Minimal qarz (so'm)</label>
              <input type="number" name="min_amount" class="form-control form-control-sm" value="0" min="0" step="10000">
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial</label>
              <select name="filial_id" class="form-select form-select-sm">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Oldindan ogohlantirish (upcoming) --}}
        <div id="filtr-upcoming" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Necha kun oldin ogohlantirish</label>
              <select name="days" class="form-select form-select-sm">
                <option value="1">1 kun oldin</option><option value="3" selected>3 kun oldin</option>
                <option value="5">5 kun oldin</option><option value="7">7 kun oldin</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial</label>
              <select name="filial_id_upcoming" class="form-select form-select-sm">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Filial bo'yicha (branch) --}}
        <div id="filtr-branch" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial <span class="text-danger">*</span></label>
              <select name="filial_id_branch" class="form-select form-select-sm" required>
                <option value="">— Filial tanlang —</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Faqat qarzdorlar</label>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="only_debtors" value="1" id="only-debt" checked>
                <label class="form-check-label small" for="only-debt">Faqat qoldig'i bor mijozlar</label>
              </div>
            </div>
          </div>
        </div>

        {{-- Custom --}}
        <div id="filtr-custom" class="filtr-group" style="display:none">
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Filial</label>
              <select name="filial_id_custom" class="form-select form-select-sm">
                <option value="">Barcha filiallar</option>
                @foreach($filiallar as $f)
                <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Minimal qarz</label>
              <input type="number" name="min_debt" class="form-control form-control-sm" value="0" min="0">
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Holat</label>
              <select name="statuses[]" class="form-select form-select-sm" multiple>
                <option value="faol" selected>Faol</option>
                <option value="muddati_otgan" selected>Muddati o'tgan</option>
                <option value="muzlatilgan">Muzlatilgan</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label class="form-label small fw-medium">Limit</label>
              <input type="number" name="limit" class="form-control form-control-sm" value="200" min="1" max="500">
            </div>
          </div>
        </div>

        <div class="mt-3">
          <button type="button" class="btn btn-sm btn-outline-info" onclick="previewOl()">
            <i class="bi bi-eye me-1"></i>Natijani ko'rish
          </button>
        </div>
        {{-- Hidden: filial_id normalize --}}
        <input type="hidden" id="filial_id_resolved" name="filial_id" value="">
      </div>
    </div>

  </div>{{-- /col-lg-7 --}}

  {{-- O'NG: Shablon + natija --}}
  <div class="col-lg-5">

    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header py-2 fw-bold"><i class="bi bi-file-text me-2"></i>3. Shablon tanlang</div>
      <div class="card-body">
        <select name="template_id" id="template-select" class="form-select" required onchange="shablon_preview(this)">
          <option value="">— Shablon tanlang —</option>
          @foreach($shablonlar as $s)
          <option value="{{ $s->id }}" data-body="{{ $s->body }}">{{ $s->name }}</option>
          @endforeach
        </select>
        <div id="shablon-preview" class="mt-2 p-2 bg-light rounded small text-muted" style="display:none;white-space:pre-wrap"></div>
      </div>
    </div>

    {{-- Natija --}}
    <div class="card border-0 shadow-sm mb-3" id="natija-karta" style="display:none">
      <div class="card-header py-2 fw-bold"><i class="bi bi-bar-chart me-2"></i>Natija</div>
      <div class="card-body">
        <div class="row g-2 text-center mb-3">
          <div class="col-4">
            <div class="badge bg-primary fs-5 d-block mb-1" id="n-total">0</div>
            <div class="text-muted small">Jami</div>
          </div>
          <div class="col-4">
            <div class="badge bg-warning text-dark fs-5 d-block mb-1" id="n-nophone">0</div>
            <div class="text-muted small">Tel yo'q</div>
          </div>
          <div class="col-4">
            <div class="badge bg-danger fs-5 d-block mb-1" id="n-badphone">0</div>
            <div class="text-muted small">Noto'g'ri tel</div>
          </div>
        </div>
        <div id="preview-table" class="table-responsive" style="max-height:200px;overflow-y:auto"></div>
      </div>
    </div>

    {{-- Yuborish tugmalar --}}
    <div class="card border-0 shadow-sm" id="yuborish-karta" style="display:none">
      <div class="card-body">
        <div class="alert alert-warning py-2 small mb-3">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Guruhli SMS yuborishdan oldin test rejimda sinab ko'ring.
        </div>
        <div class="d-grid gap-2">
          <button type="button" class="btn btn-outline-info" onclick="testYuborish()">
            <i class="bi bi-send me-1"></i>Test SMS yuborish
          </button>
          <button type="submit" class="btn btn-warning fw-bold" id="guruhli-submit"
                  onclick="return confirm('Haqiqatan ham guruhga SMS yuborilsinmi?')">
            <i class="bi bi-send-fill me-1"></i>Guruhga yuborish
          </button>
        </div>
      </div>
    </div>

  </div>{{-- /col-lg-5 --}}
</div>{{-- /row --}}
</form>
@endsection

@push('scripts')
<script>
var aktivTur = '';

function shablon_preview(sel) {
    var body = sel.options[sel.selectedIndex]?.dataset?.body || '';
    var el = document.getElementById('shablon-preview');
    if (body) { el.textContent = body; el.style.display = ''; }
    else { el.style.display = 'none'; }
}

function turTanla(tur, el) {
    aktivTur = tur;
    document.getElementById('tur-input').value = tur;
    document.querySelectorAll('.yuborish-tur-karta').forEach(k => k.classList.remove('tanlangan'));
    el.classList.add('tanlandan');
    el.classList.add('tanlangan');

    // Filtrlarni ko'rsat
    document.getElementById('filtr-blok').style.display = '';
    document.querySelectorAll('.filtr-group').forEach(g => g.style.display = 'none');
    var filtrEl = document.getElementById('filtr-' + tur);
    if (filtrEl) filtrEl.style.display = '';
}

// Filial_id ni tur ga mos normalize qilish
function getFilialId() {
    var turSelectors = {
        'overdue':  '[name=filial_id]',
        'upcoming': '[name=filial_id_upcoming]',
        'branch':   '[name=filial_id_branch]',
        'custom':   '[name=filial_id_custom]',
    };
    var sel = turSelectors[aktivTur] || '[name=filial_id]';
    var el = document.querySelector(sel);
    return el ? el.value : '';
}

function previewOl() {
    if (!aktivTur) { alert("Yuborish turini tanlang!"); return; }
    var tmplId = document.getElementById('template-select').value;
    if (!tmplId) { alert("Shablon tanlang!"); return; }

    // Filial ID ni normalize qil
    var filialId = getFilialId();
    document.getElementById('filial_id_resolved').value = filialId;

    var fd = new FormData(document.getElementById('guruhli-form'));
    fd.set('type', aktivTur);
    fd.set('template_id', tmplId);
    fd.set('filial_id', filialId);

    fetch('{{ route("xabarnoma.sms.preview") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
        body: fd
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('n-total').textContent    = d.total    || 0;
        document.getElementById('n-nophone').textContent  = d.no_phone || 0;
        document.getElementById('n-badphone').textContent = d.bad_phone|| 0;
        document.getElementById('natija-karta').style.display = '';
        document.getElementById('yuborish-karta').style.display = d.total > 0 ? '' : 'none';

        var rows = (d.preview || []).map(p =>
            '<tr><td class="small">' + (p.name||'') + '</td>' +
            '<td class="small">' + (p.phone||'') + '</td>' +
            '<td class="small text-muted" title="' + (p.message||'').replace(/"/g, "'") + '">' +
            (p.message||'').substring(0, 50) + (p.message?.length > 50 ? '...' : '') +
            '</td></tr>'
        ).join('');

        document.getElementById('preview-table').innerHTML = rows
            ? '<table class="table table-sm mb-0"><thead class="table-light"><tr><th>Mijoz</th><th>Telefon</th><th>Xabar</th></tr></thead><tbody>' + rows + '</tbody></table>'
            : '<p class="text-muted small py-2 text-center">Namuna ko'rsatilmadi</p>';
    })
    .catch(e => { console.error(e); alert('Server xatosi: ' + e.message); });
}

function testYuborish() {
    var el = document.getElementById('test-natija') || document.createElement('div');
    el.id = 'test-natija';
    el.textContent = 'Yuborilmoqda...';
    fetch('{{ route("xabarnoma.sms.test") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
    })
    .then(r => r.json())
    .then(d => {
        var ok = d.status === 'test' || d.status === 'sent';
        alert(ok ? 'Test SMS yuborildi! Provider: ' + (d.provider || '—') : 'Xato: ' + (d.error || d.message || 'Unknown'));
    })
    .catch(e => alert('Xato: ' + e.message));
}

</script>
@endpush