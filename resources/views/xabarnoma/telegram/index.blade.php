@extends('layouts.app')
@section('title','Telegram')
@section('breadcrumb')<li class="breadcrumb-item active">Telegram</li>@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-telegram me-2 text-info"></i>Telegram Xabarnoma</h5>

<div class="alert alert-info py-2 small mb-3">
    <i class="bi bi-info-circle me-1"></i>
    Telegram orqali xabar yuborish uchun: <strong>Bot Token</strong> va mijoz <strong>Chat ID</strong> kerak.
    Hozircha test chat ID orqali sinab ko'rishingiz mumkin.
</div>

<div class="row g-3">
<div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold"><i class="bi bi-gear me-2"></i>Bot Sozlamalari</div>
    <div class="card-body">
        <form method="POST" action="{{ route('xabarnoma.telegram.sozlamalar.saqlash') }}">
        @csrf
            <div class="mb-3">
                <label class="form-label fw-medium">Bot Token</label>
                <div class="input-group">
                    <input type="password" name="bot_token" id="tg-token" class="form-control"
                           placeholder="{{ ($sozlamalar['bot_token']->value ?? '') ? '••••••••' : 'Bot tokenini kiriting' }}">
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('tg-token').type = document.getElementById('tg-token').type === 'password' ? 'text' : 'password'">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Bot Username</label>
                <input type="text" name="bot_username" class="form-control"
                       value="{{ $sozlamalar['bot_username']->value ?? '' }}" placeholder="@YourBotName">
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Test Chat ID</label>
                <input type="text" name="test_chat_id" class="form-control"
                       value="{{ $sozlamalar['test_chat_id']->value ?? '' }}" placeholder="123456789">
            </div>
            <div class="mb-3">
                <label class="form-label fw-medium">Parse Mode</label>
                <select name="parse_mode" class="form-select">
                    <option value="HTML"     {{ ($sozlamalar['parse_mode']->value ?? 'HTML') === 'HTML'     ? 'selected' : '' }}>HTML</option>
                    <option value="Markdown" {{ ($sozlamalar['parse_mode']->value ?? '') === 'Markdown' ? 'selected' : '' }}>Markdown</option>
                    <option value=""         {{ ($sozlamalar['parse_mode']->value ?? '') === ''         ? 'selected' : '' }}>Plain text</option>
                </select>
            </div>
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="enabled" id="tg-enabled"
                       {{ ($sozlamalar['enabled']->value ?? '0') === '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="tg-enabled">Telegram moduli yoqilgan</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Saqlash</button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="testTelegram()">
                    <i class="bi bi-send me-1"></i>Test xabar yuborish
                </button>
            </div>
        </form>
        <div id="tg-test-natija" class="mt-2 small d-none"></div>
    </div>
</div>
</div>

<div class="col-lg-6">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold"><i class="bi bi-clock-history me-2"></i>Oxirgi yuborishlar</div>
    <div class="card-body p-0">
        @if($loglar->count())
        <table class="table table-sm mb-0">
            <thead class="table-light"><tr><th>Sana</th><th>Chat ID</th><th>Holat</th></tr></thead>
            <tbody>
            @foreach($loglar as $l)
            <tr>
                <td class="small">{{ $l->created_at->format('d.m.Y H:i') }}</td>
                <td class="small">{{ $l->telegram_chat_id }}</td>
                <td><span class="badge bg-{{ $l->status_rangi }}" style="font-size:.6rem">{{ $l->status }}</span></td>
            </tr>
            @endforeach
            </tbody>
        </table>
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
function testTelegram() {
    var el = document.getElementById('tg-test-natija');
    el.className = 'mt-2 small'; el.textContent = 'Yuborilmoqda...';
    fetch('{{ route("xabarnoma.telegram.test") }}', {
        method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}
    })
    .then(r=>r.json())
    .then(d=>{
        el.className = 'mt-2 small ' + (d.ok ? 'text-success' : 'text-danger');
        el.textContent = d.ok ? 'Test xabar muvaffaqiyatli yuborildi!' : 'Xato: ' + JSON.stringify(d.result?.description || d.error || d);
    });
}
</script>
@endpush