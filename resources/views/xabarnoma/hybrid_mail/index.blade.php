@extends('layouts.app')
@section('title','Gibrid Pochta')
@section('breadcrumb')<li class="breadcrumb-item active">Gibrid Pochta</li>@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-envelope-paper me-2" style="color:#8b5cf6"></i>Gibrid Pochta Xabarnoma</h5>

<div class="alert alert-secondary py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    <strong>Gibrid Pochta</strong> — jismoniy va elektron xat yuborish uchun API tizimi.
    Hozircha <strong>Test Mode</strong> da ishlaydi va logga yozadi.
    Real API ulash uchun sozlamalarni to'ldiring.
</div>

<div class="row g-3">
<div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold"><i class="bi bi-gear me-2"></i>API Sozlamalari</div>
    <div class="card-body">
        <form method="POST" action="{{ route('xabarnoma.hybrid_mail.sozlamalar.saqlash') }}">
        @csrf
            <div class="mb-3">
                <label class="form-label fw-medium small">API Base URL</label>
                <input type="url" name="api_url" class="form-control form-control-sm"
                       value="{{ $sozlamalar['api_url']->value ?? '' }}"
                       placeholder="https://api.hybridmail.uz/v1">
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium small">Login / Client ID</label>
                <input type="text" name="login" class="form-control form-control-sm"
                       value="{{ $sozlamalar['login']->value ?? '' }}"
                       placeholder="client_id">
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium small">Token / Secret</label>
                <div class="input-group input-group-sm">
                    <input type="password" name="token" id="hm-token" class="form-control"
                           placeholder="{{ ($sozlamalar['token']->value ?? '') ? '••••••••' : 'Token kiriting' }}">
                    <button type="button" class="btn btn-outline-secondary" onclick="var i=document.getElementById('hm-token');i.type=i.type==='password'?'text':'password'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium small">Jo'natuvchi nomi</label>
                <input type="text" name="sender_name" class="form-control form-control-sm"
                       value="{{ $sozlamalar['sender_name']->value ?? 'NasiyaPro' }}"
                       placeholder="NasiyaPro">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="hm-enabled"
                               {{ ($sozlamalar['enabled']->value ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="hm-enabled">Modul yoqilgan</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="test_mode" id="hm-test"
                               {{ ($sozlamalar['test_mode']->value ?? '1') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="hm-test">Test rejimi</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="testYuborish()">
                    <i class="bi bi-send me-1"></i>Test yuborish
                </button>
            </div>
        </form>
        <div id="hm-natija" class="mt-2 small d-none"></div>
    </div>
</div>
</div>

<div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold small"><i class="bi bi-clock-history me-2"></i>Oxirgi yuborishlar</div>
    <div class="card-body p-0">
        @if($loglar->count())
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Sana</th><th>Mavzu</th><th>Holat</th></tr></thead>
                <tbody>
                @foreach($loglar as $l)
                <tr>
                    <td class="small">{{ $l->created_at->format('d.m.Y H:i') }}</td>
                    <td class="small">{{ Str::limit($l->subject ?? $l->message, 30) }}</td>
                    <td><span class="badge bg-{{ $l->status_rangi }}" style="font-size:.6rem">{{ $l->status }}</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-4 small">Yozuvlar yo'q</div>
        @endif
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
function testYuborish() {
    var el = document.getElementById('hm-natija');
    el.className = 'mt-2 small'; el.textContent = 'Yuborilmoqda...';
    fetch('{{ route("xabarnoma.hybrid_mail.test") }}', {
        method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
    })
    .then(r => r.json())
    .then(d => {
        el.className = 'mt-2 small ' + (d.status === 'test' ? 'text-info' : 'text-danger');
        el.textContent = d.message || d.error || 'OK';
    });
}
</script>
@endpush