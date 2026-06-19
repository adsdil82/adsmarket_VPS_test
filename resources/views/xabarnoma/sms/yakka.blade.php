@extends('layouts.app')
@section('title','SMS — Yakka yuborish')
@section('breadcrumb')
    <li class="breadcrumb-item active">SMS — Yakka yuborish</li>
@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-chat-dots me-2 text-warning"></i>SMS — Yakka yuborish</h5>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.guruhli') }}">Guruhli</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('xabarnoma.sms.yakka') }}">Yakka</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.tarix') }}">Tarix</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.shablonlar.index') }}">Shablonlar</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.sozlamalar') }}">Sozlamalar</a></li>
</ul>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold"><i class="bi bi-person-check me-2"></i>Yakka SMS yuborish</div>
    <div class="card-body">
        <form method="POST" action="{{ route('xabarnoma.sms.yakka.send') }}">
        @csrf
            {{-- Mijoz qidirish --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Mijoz</label>
                <input type="hidden" id="customer_id" name="customer_id">
                <input type="hidden" id="contract_id" name="contract_id">
                <div class="input-group">
                    <input type="text" id="mijoz-qidiruv" class="form-control"
                           placeholder="Ism yoki telefon yozing..." autocomplete="off">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
                <div id="mijoz-dropdown" class="list-group shadow-sm mt-1" style="position:absolute;z-index:100;width:100%;max-height:200px;overflow-y:auto;display:none"></div>
                <div id="mijoz-info" class="mt-1 small text-muted"></div>
            </div>

            {{-- Telefon --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Telefon <span class="text-danger">*</span></label>
                <input type="text" name="phone" id="phone" class="form-control"
                       placeholder="+998 90 000 00 00" required>
            </div>

            {{-- Shablon --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Shablon (ixtiyoriy)</label>
                <select name="template_id" id="shablon-select" class="form-select" onchange="shablon_yakka_preview(this)">
                    <option value="">— Qo'lda yozish —</option>
                    @foreach($shablonlar as $s)
                    <option value="{{ $s->id }}" data-body="{{ $s->body }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Xabar matni --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Xabar matni <span class="text-danger">*</span></label>
                <textarea name="message" id="message-text" class="form-control" rows="4"
                          placeholder="Xabar matni..." required minlength="5" maxlength="800"
                          oninput="charHisob()"></textarea>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted" id="char-count">0 / 160 belgi (1 segment)</small>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-bold">
                    <i class="bi bi-send me-1"></i>Yuborish
                </button>
                <a href="{{ route('xabarnoma.sms.guruhli') }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
var mijozTimer;
document.getElementById('mijoz-qidiruv').addEventListener('input', function() {
    clearTimeout(mijozTimer);
    var q = this.value.trim();
    if (q.length < 2) { document.getElementById('mijoz-dropdown').style.display='none'; return; }
    mijozTimer = setTimeout(function() {
        $.getJSON('{{ route("mijozlar.ajax.qidiruv") }}', {q: q})
            .done(function(data) {
                var dd = document.getElementById('mijoz-dropdown');
                dd.innerHTML = '';
                if (!data.length) { dd.style.display='none'; return; }
                data.forEach(function(m) {
                    var el = document.createElement('a');
                    el.className = 'list-group-item list-group-item-action small';
                    el.href = '#';
                    el.textContent = m.fio + ' — ' + m.telefon;
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('customer_id').value = m.id;
                        document.getElementById('phone').value = m.telefon;
                        document.getElementById('mijoz-qidiruv').value = m.fio;
                        document.getElementById('mijoz-info').innerHTML = '<i class="bi bi-check-circle text-success me-1"></i>' + m.fio + ' · ' + m.telefon + ' · ' + m.passport;
                        dd.style.display = 'none';
                    });
                    dd.appendChild(el);
                });
                dd.style.display = '';
            });
    }, 300);
});

document.getElementById('shablon-select').addEventListener('change', function() {
    var body = this.options[this.selectedIndex].dataset.body || '';
    if (body) document.getElementById('message-text').value = body;
    charHisob();
});

function charHisob() {
    var len = document.getElementById('message-text').value.length;
    var segs = len <= 160 ? 1 : Math.ceil(len/153);
    document.getElementById('char-count').textContent = len + ' / 160 belgi (' + segs + ' segment)';
}
</script>
@endpush