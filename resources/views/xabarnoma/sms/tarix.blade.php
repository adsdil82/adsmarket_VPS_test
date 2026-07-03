@extends('layouts.app')
@section('title','SMS Tarixi')
@section('breadcrumb')
    <li class="breadcrumb-item active">SMS Tarixi</li>
@endsection
@section('content')
<h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-warning"></i>SMS Yuborish Tarixi</h5>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.guruhli') }}">Guruhli</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.sms.yakka') }}">Yakka</a></li>
    <li class="nav-item"><a class="nav-link active" href="{{ route('xabarnoma.sms.tarix') }}">Tarix</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ route('xabarnoma.shablonlar.index') }}">Shablonlar</a></li>
    <li class="nav-item"><a class="nav-link" href="{{ Auth::user()->isAdmin() ? route('admin.sozlamalar').'#collapseSms' : route('xabarnoma.sms.sozlamalar') }}">Sozlamalar</a></li>
</ul>

{{-- Filtr --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-sm-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Barcha holat</option>
                    <option value="sent"    {{ request('status')==='sent'   ?'selected':'' }}>Yuborildi</option>
                    <option value="test"    {{ request('status')==='test'   ?'selected':'' }}>Test</option>
                    <option value="failed"  {{ request('status')==='failed' ?'selected':'' }}>Xato</option>
                    <option value="skipped" {{ request('status')==='skipped'?'selected':'' }}>O'tkazildi</option>
                </select>
            </div>
            <div class="col-sm-2">
                <input type="date" name="dan_sana" class="form-control form-control-sm" value="{{ request('dan_sana') }}">
            </div>
            <div class="col-sm-2">
                <input type="date" name="gacha_sana" class="form-control form-control-sm" value="{{ request('gacha_sana') }}">
            </div>
            <div class="col-sm-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('xabarnoma.sms.tarix') }}" class="btn btn-sm btn-outline-secondary ms-1"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Oxirgi batch'lar --}}
@if($batchlar->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header py-2 small fw-bold"><i class="bi bi-collection me-1"></i>Oxirgi guruhli yuborishlar</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Sana</th><th>Tur</th><th>Jami</th><th>Yuborildi</th><th>Xato</th><th>Holat</th></tr>
            </thead>
            <tbody>
                @foreach($batchlar as $b)
                <tr>
                    <td class="small">{{ $b->created_at->format('d.m.Y H:i') }}</td>
                    <td class="small">{{ $b->title }}</td>
                    <td class="small">{{ $b->total_recipients }}</td>
                    <td class="small text-success">{{ $b->total_sent }}</td>
                    <td class="small text-danger">{{ $b->total_failed }}</td>
                    <td><span class="badge bg-{{ $b->status_rangi }}" style="font-size:.65rem">{{ $b->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Log jadval --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sana</th><th>Telefon</th><th>Mijoz</th>
                    <th>Shablon</th><th>Holat</th><th>Xabar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loglar as $log)
                <tr>
                    <td class="small text-muted">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                    <td class="small">{{ $log->phone }}</td>
                    <td class="small">{{ $log->customer?->familiya }} {{ $log->customer?->ism }}</td>
                    <td class="small text-muted">{{ $log->template?->name ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $log->status_rangi }}" style="font-size:.65rem">{{ $log->status }}</span>
                        @if($log->error_message)
                        <i class="bi bi-exclamation-triangle text-danger ms-1" title="{{ $log->error_message }}"></i>
                        @endif
                    </td>
                    <td class="small text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                        title="{{ $log->message }}">{{ Str::limit($log->message, 60) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-3 d-block mb-2 opacity-25"></i>Yozuvlar yo'q
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($loglar->hasPages())
    <div class="card-footer py-2">{{ $loglar->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection