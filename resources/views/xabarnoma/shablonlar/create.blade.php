@extends('layouts.app')
@section('title', isset($shablon) ? 'Shabl. tahrirlash' : 'Yangi shablon')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('xabarnoma.shablonlar.index') }}">Shablonlar</a></li>
    <li class="breadcrumb-item active">{{ isset($shablon) ? 'Tahrirlash' : 'Yangi' }}</li>
@endsection
@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-header py-2 fw-bold">
        <i class="bi bi-file-text me-2"></i>{{ isset($shablon) ? 'Shabl. tahrirlash: '.$shablon->name : 'Yangi shablon' }}
    </div>
    <div class="card-body">
        @if(isset($shablon))
        <form method="POST" action="{{ route('xabarnoma.shablonlar.update', $shablon) }}">
        @csrf @method('PUT')
        @else
        <form method="POST" action="{{ route('xabarnoma.shablonlar.store') }}">
        @csrf
        @endif

            @if(!isset($shablon))
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Kanal <span class="text-danger">*</span></label>
                    <select name="channel" class="form-select @error('channel') is-invalid @enderror" required>
                        @foreach($channels as $k => $v)
                        <option value="{{ $k }}" {{ old('channel','sms')===$k?'selected':'' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-medium">Kod <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code') }}" placeholder="masalan: sms_muddati_otgan" required>
                    <div class="form-text">Kichik harf, _, raqam (boshqa belgi yo'q)</div>
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-medium">Nomi <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $shablon->name ?? '') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Xabar matni <span class="text-danger">*</span></label>
                <textarea name="body" id="tmpl-body" class="form-control @error('body') is-invalid @enderror"
                          rows="5" required oninput="charHisob()">{{ old('body', $shablon->body ?? '') }}</textarea>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted" id="char-info">0 belgi</small>
                    <button type="button" class="btn btn-xs btn-outline-secondary btn-sm" onclick="previewKor()">
                        <i class="bi bi-eye me-1"></i>Sample preview
                    </button>
                </div>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- O'zgaruvchilar --}}
            <div class="mb-3">
                <label class="form-label small fw-medium text-muted">Tez kiritish (bosing):</label>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($variables as $key => $desc)
                    <button type="button" class="btn btn-outline-secondary btn-sm py-0"
                            style="font-size:.65rem"
                            onclick="qoshVar('{{'{'.$key.'}'}}')"
                            title="{{ $desc }}">{{'{'.$key.'}'}}</button>
                    @endforeach
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is-active"
                               {{ old('is_active', $shablon->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is-active">Faol</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is-default"
                               {{ old('is_default', $shablon->is_default ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is-default">Default shablon</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Saqlash</button>
                <a href="{{ route('xabarnoma.shablonlar.index') }}" class="btn btn-outline-secondary">Bekor</a>
            </div>
        </form>
    </div>
</div>

{{-- Preview --}}
<div class="card border-0 shadow-sm mt-3 d-none" id="preview-karta">
    <div class="card-header py-2 fw-bold small"><i class="bi bi-eye me-1"></i>Sample Preview</div>
    <div class="card-body">
        <pre id="preview-text" class="small bg-light p-3 rounded" style="white-space:pre-wrap"></pre>
    </div>
</div>

</div>
</div>
@endsection

@push('scripts')
<script>
function charHisob() {
    var len = document.getElementById('tmpl-body').value.length;
    document.getElementById('char-info').textContent = len + ' belgi (SMS: ' + (len <= 160 ? 1 : Math.ceil(len/153)) + ' segment)';
}
function qoshVar(v) {
    var ta = document.getElementById('tmpl-body');
    var start = ta.selectionStart, end = ta.selectionEnd;
    ta.value = ta.value.slice(0, start) + v + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + v.length;
    ta.focus();
    charHisob();
}
function previewKor() {
    var body = document.getElementById('tmpl-body').value;
    var sample = {
        client_name:'Aliyev Jasur', contract_number:'IST-2026-00123',
        branch_name:'Istiqlol filiali', payment_date:'10.06.2026',
        monthly_payment:'375 000', overdue_days:'5', overdue_amount:'375 000',
        total_debt:'1 500 000', paid_amount:'500 000', remaining_amount:'1 000 000',
        company_name:'NasiyaPro', manager_phone:'+998901234567'
    };
    for (var k in sample) body = body.split('{'+k+'}').join(sample[k]);
    document.getElementById('preview-text').textContent = body;
    document.getElementById('preview-karta').classList.remove('d-none');
}
charHisob();
</script>
@endpush