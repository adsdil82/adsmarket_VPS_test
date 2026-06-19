@extends('layouts.app')
@section('title','Email Xabarnoma')
@section('breadcrumb')<li class="breadcrumb-item active">Email</li>@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-envelope me-2 text-primary"></i>Email Xabarnoma</h5>

<div class="row g-3">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold">Email SMTP Sozlamalari</div>
    <div class="card-body">
        <form method="POST" action="{{ route('xabarnoma.email.sozlamalar.saqlash') }}">
        @csrf
            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <label class="form-label fw-medium small">Mailer</label>
                    <select name="mailer" class="form-select form-select-sm">
                        <option value="smtp" {{ ($sozlamalar['mailer']->value??'smtp')==='smtp'?'selected':'' }}>SMTP</option>
                        <option value="log"  {{ ($sozlamalar['mailer']->value??'')==='log' ?'selected':'' }}>Log (test)</option>
                    </select>
                </div>
                <div class="col-sm-5">
                    <label class="form-label fw-medium small">Host</label>
                    <input type="text" name="host" class="form-control form-control-sm"
                           value="{{ $sozlamalar['host']->value??'' }}" placeholder="smtp.gmail.com">
                </div>
                <div class="col-sm-3">
                    <label class="form-label fw-medium small">Port</label>
                    <input type="number" name="port" class="form-control form-control-sm"
                           value="{{ $sozlamalar['port']->value??'587' }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium small">Username</label>
                    <input type="text" name="username" class="form-control form-control-sm"
                           value="{{ $sozlamalar['username']->value??'' }}" placeholder="your@gmail.com">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium small">Parol</label>
                    <input type="password" name="password" class="form-control form-control-sm"
                           placeholder="{{ ($sozlamalar['password']->value??'')?'••••••••':'Parol kiriting' }}">
                </div>
                <div class="col-sm-4">
                    <label class="form-label fw-medium small">Encryption</label>
                    <select name="encryption" class="form-select form-select-sm">
                        <option value="tls" {{ ($sozlamalar['encryption']->value??'tls')==='tls'?'selected':'' }}>TLS</option>
                        <option value="ssl" {{ ($sozlamalar['encryption']->value??'')==='ssl'?'selected':'' }}>SSL</option>
                        <option value=""    {{ ($sozlamalar['encryption']->value??'')==''?'selected':'' }}>None</option>
                    </select>
                </div>
                <div class="col-sm-8">
                    <label class="form-label fw-medium small">From Address</label>
                    <input type="email" name="from_address" class="form-control form-control-sm"
                           value="{{ $sozlamalar['from_address']->value??'' }}" placeholder="noreply@nasiyapro.uz">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium small">From Name</label>
                    <input type="text" name="from_name" class="form-control form-control-sm"
                           value="{{ $sozlamalar['from_name']->value??'NasiyaPro' }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium small">Test Email</label>
                    <input type="email" name="test_email" class="form-control form-control-sm"
                           value="{{ $sozlamalar['test_email']->value??'' }}">
                </div>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="enabled" id="email-enabled"
                       {{ ($sozlamalar['enabled']->value??'0')==='1'?'checked':'' }}>
                <label class="form-check-label small" for="email-enabled">Email moduli yoqilgan</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="testEmail()">
                    <i class="bi bi-send me-1"></i>Test email
                </button>
            </div>
        </form>
        <div id="email-natija" class="mt-2 small d-none"></div>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function testEmail() {
    var el = document.getElementById('email-natija');
    el.className = 'mt-2 small'; el.textContent = 'Yuborilmoqda...';
    fetch('{{ route("xabarnoma.email.test") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    })
    .then(r=>r.json())
    .then(d=>{
        el.className = 'mt-2 small ' + (d.status==='test'?'text-success':'text-danger');
        el.textContent = d.message || d.error || 'OK';
    });
}
</script>
@endpush