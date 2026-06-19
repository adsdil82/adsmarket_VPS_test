@extends('layouts.app')
@section('title','SMS Sozlamalari')
@section('breadcrumb')
    <li class="breadcrumb-item active">SMS Sozlamalari</li>
@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-gear me-2 text-warning"></i>SMS Sozlamalari</h5>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.guruhli') }}">Guruhli</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.yakka') }}">Yakka</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.tarix') }}">Tarix</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.shablonlar.index') }}">Shablonlar</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('xabarnoma.sms.sozlamalar') }}">Sozlamalar</a></li>
</ul>

<div class="row g-3">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold"><i class="bi bi-sliders me-2"></i>SMS API Sozlamalari</div>
    <div class="card-body">
        <form method="POST" action="{{ route('xabarnoma.sms.sozlamalar.saqlash') }}">
        @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Provider</label>
                <select name="provider" class="form-select">
                    <option value="test_mode" {{ ($sozlamalar['provider']->value??'')==='test_mode'?'selected':'' }}>Test Mode (real SMS yuborilmaydi)</option>
                    <option value="eskiz"     {{ ($sozlamalar['provider']->value??'')==='eskiz'    ?'selected':'' }}>Eskiz.uz</option>
                    <option value="playmobile"{{ ($sozlamalar['provider']->value??'')==='playmobile'?'selected':'' }}>PlayMobile</option>
                    <option value="custom"    {{ ($sozlamalar['provider']->value??'')==='custom'   ?'selected':'' }}>Custom API</option>
                </select>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label fw-medium">API URL</label>
                    <input type="url" name="api_url" class="form-control"
                           value="{{ $sozlamalar['api_url']->value ?? '' }}"
                           placeholder="https://notify.eskiz.uz/api">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Sender ID</label>
                    <input type="text" name="sender_id" class="form-control"
                           value="{{ $sozlamalar['sender_id']->value ?? 'NasiyaPro' }}"
                           placeholder="NasiyaPro">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Login / Email</label>
                    <input type="text" name="login" class="form-control"
                           value="{{ $sozlamalar['login']->value ?? '' }}"
                           placeholder="your@email.com">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Parol / Token</label>
                    <div class="input-group">
                        <input type="password" name="password" id="sms-pwd" class="form-control"
                               placeholder="{{ ($sozlamalar['password']->value ?? '') ? '••••••••' : 'Yangi parol kiriting' }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('sms-pwd')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Bo'sh qoldirsangiz eski parol saqlanadi.</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Test telefon raqam</label>
                    <input type="text" name="test_phone" class="form-control"
                           value="{{ $sozlamalar['test_phone']->value ?? '' }}"
                           placeholder="+998901234567">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="sms-enabled"
                               {{ ($sozlamalar['enabled']->value ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="sms-enabled">SMS moduli yoqilgan</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="test_mode" id="sms-test-mode"
                               {{ ($sozlamalar['test_mode']->value ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="sms-test-mode">Test rejimi (real SMS ketmaydi)</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Saqlash</button>
                <button type="button" class="btn btn-outline-info" onclick="testApiStatus()">
                    <i class="bi bi-wifi me-1"></i>API holatini tekshir
                </button>
            </div>
        </form>
    </div>
</div>
</div>

<div class="col-lg-5">
    {{-- Test SMS --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header py-2 fw-bold"><i class="bi bi-send me-2"></i>Test SMS yuborish</div>
        <div class="card-body">
            <p class="small text-muted mb-2">Test telefon raqamga SMS yuborish:</p>
            <div class="fw-medium mb-2">{{ $sozlamalar['test_phone']->value ?? 'Test tel kiritilmagan' }}</div>
            <button type="button" class="btn btn-warning btn-sm" onclick="testSmsYuborish()">
                <i class="bi bi-send me-1"></i>Test SMS yuborish
            </button>
            <div id="test-natija" class="mt-2 small d-none"></div>
        </div>
    </div>

    {{-- Provider holati --}}
    @if($providerStatus)
    <div class="card border-0 shadow-sm">
        <div class="card-header py-2 fw-bold"><i class="bi bi-info-circle me-2"></i>Provider holati</div>
        <div class="card-body">
            <div class="small">
                <div><strong>Provider:</strong> {{ $providerStatus['provider_name'] ?? '—' }}</div>
                <div><strong>Test rejimi:</strong> {{ $providerStatus['test_mode'] ? 'Ha' : 'Yo\'q' }}</div>
                @if(isset($providerStatus['status']))
                <div><strong>Holat:</strong> <span class="badge bg-{{ $providerStatus['status']==='ok'?'success':'danger' }}">{{ $providerStatus['status'] }}</span></div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd(id) {
    var inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

function testSmsYuborish() {
    var el = document.getElementById('test-natija');
    el.className = 'mt-2 small'; el.textContent = 'Yuborilmoqda...';
    fetch('{{ route("xabarnoma.sms.test") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    })
    .then(r=>r.json())
    .then(d=>{
        var ok = d.status === 'test' || d.status === 'sent';
        el.className = 'mt-2 small ' + (ok ? 'text-success' : 'text-danger');
        el.textContent = ok ? 'Test SMS yuborildi! Provider: ' + (d.provider||'—') : 'Xato: ' + (d.error||d.message||'Unknown');
    });
}

function testApiStatus() {
    alert('Provider status tekshirish - sahifani yangilang sozlamalar saqlagandan so\'ng.');
}
</script>
@endpush