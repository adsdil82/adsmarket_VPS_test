@extends('layouts.app')
@section('title', 'Admin panel')
@section('breadcrumb')
    <li class="breadcrumb-item active">Admin panel</li>
@endsection

@push('styles')
<style>
.bft-section-title {
    font-weight:700; color:#1e3a8a; background:#eef3ff; border-left:4px solid #2563eb;
    padding:6px 12px; border-radius:0 6px 6px 0; margin-bottom:8px; font-size:.85rem;
}
.bft-header-card {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; border-radius:8px 8px 0 0;
    padding:10px 14px; display:flex; justify-content:space-between; align-items:center;
}
.bft-wrap { border:1px solid #93c5fd; border-radius:6px; overflow:hidden; }
.bft-table { width:100%; margin-bottom:0 !important; font-size:.85rem; }
.bft-table td { padding:8px 12px; vertical-align:middle; border-bottom:1px solid #e5edfb; }
.bft-table tbody tr:last-child td { border-bottom:none; }
.bft-table tbody tr:nth-child(even) { background:#f8fafd; }
.bft-label { font-weight:700; color:#334155; white-space:nowrap; width:1%; background:#f1f5fd; }
.bft-wide { width:100%; }

.adm-stat { border:1px solid #93c5fd; border-radius:8px; overflow:hidden; background:#fff; }
.adm-stat-head {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; font-weight:700;
    font-size:.68rem; letter-spacing:.03em; text-transform:uppercase; text-align:center; padding:6px;
}
.adm-stat-body { text-align:center; padding:10px 6px; }
.adm-stat-val { font-size:1.4rem; font-weight:800; color:#1e293b; }
.adm-stat-lbl { font-size:.72rem; color:#64748b; }

.adm-rol-head {
    color:#fff; font-weight:700; font-size:.68rem; letter-spacing:.03em; text-transform:uppercase;
    text-align:center; padding:6px;
}
.adm-rol-admin    { background:linear-gradient(90deg,#dc2626,#b91c1c); }
.adm-rol-menejer  { background:linear-gradient(90deg,#2563eb,#1d4ed8); }
.adm-rol-kassir   { background:linear-gradient(90deg,#16a34a,#15803d); }
.adm-rol-hisobchi { background:linear-gradient(90deg,#64748b,#475569); }

.adm-action-card { border:1px solid #93c5fd; border-radius:8px; overflow:hidden; background:#fff; height:100%; }
.adm-action-head {
    background:linear-gradient(90deg,#1e3a8a,#1d4ed8); color:#fff; padding:8px 12px;
    display:flex; align-items:center; gap:8px; font-weight:700; font-size:.85rem;
}
.adm-action-body { padding:14px; text-align:center; }
</style>
@endpush

@section('content')

<div class="bft-header-card mb-3">
    <span class="fw-bold"><i class="bi bi-shield-lock me-1"></i>Admin panel</span>
</div>

{{-- Statistika --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="adm-stat">
            <div class="adm-stat-head">Jami foydalanuvchilar</div>
            <div class="adm-stat-body">
                <i class="bi bi-people text-primary mb-1 d-block" style="font-size:1.3rem"></i>
                <div class="adm-stat-val text-primary">{{ $statistika['foydalanuvchilar'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="adm-stat">
            <div class="adm-stat-head">Faol foydalanuvchilar</div>
            <div class="adm-stat-body">
                <i class="bi bi-person-check text-success mb-1 d-block" style="font-size:1.3rem"></i>
                <div class="adm-stat-val text-success">{{ $statistika['faol_users'] }}</div>
            </div>
        </div>
    </div>
    @foreach(['admin'=>'admin','menejer'=>'menejer','kassir'=>'kassir','hisobchi'=>'hisobchi'] as $rol => $klass)
    <div class="col-6 col-md-3">
        <div class="adm-stat">
            <div class="adm-rol-head adm-rol-{{ $klass }}">{{ $rol }}</div>
            <div class="adm-stat-body">
                <div class="adm-stat-val">{{ $statistika['rollar'][$rol] ?? 0 }}</div>
                <div class="adm-stat-lbl">ta</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Amallar --}}
<div class="row g-3 mb-3">
    {{-- Sozlamalar --}}
    <div class="col-md-4">
        <div class="adm-action-card">
            <div class="adm-action-head"><i class="bi bi-gear-wide-connected"></i>Sozlamalar</div>
            <div class="adm-action-body">
                <p class="text-muted small mb-3">Brend nomi, kompaniya rekvizitlari, interfeys temasi</p>
                <a href="{{ route('admin.sozlamalar') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-gear me-1"></i> Sozlash
                </a>
            </div>
        </div>
    </div>
    {{-- Ruxsatlar --}}
    <div class="col-md-4">
        <div class="adm-action-card">
            <div class="adm-action-head"><i class="bi bi-key"></i>Ruxsatlar</div>
            <div class="adm-action-body">
                <p class="text-muted small mb-3">Har bir rol uchun CRUD ruxsatlarini sozlang</p>
                <a href="{{ route('admin.ruxsatlar') }}" class="btn btn-warning btn-sm fw-bold">
                    <i class="bi bi-sliders me-1"></i> Boshqarish
                </a>
            </div>
        </div>
    </div>
    {{-- Foydalanuvchilar --}}
    <div class="col-md-4">
        <div class="adm-action-card">
            <div class="adm-action-head"><i class="bi bi-people"></i>Foydalanuvchilar</div>
            <div class="adm-action-body">
                <p class="text-muted small mb-3">{{ $statistika['foydalanuvchilar'] }} ta foydalanuvchi, {{ $statistika['faol_users'] }} ta faol</p>
                <a href="{{ route('admin.foydalanuvchilar') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-gear me-1"></i> Ko'rish
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Hozirgi sozlamalar --}}
@if(!empty($sozlamalar['kompaniya_nomi']))
<div class="bft-section-title"><i class="bi bi-building me-1"></i>Hozirgi kompaniya</div>
<div class="bft-wrap">
    <table class="bft-table">
        <tbody>
            <tr>
                <td class="bft-label">Nomi</td>
                <td class="bft-wide fw-semibold">{{ $sozlamalar['kompaniya_nomi'] }}</td>
            </tr>
            @if($sozlamalar['kompaniya_inn'] ?? '')
            <tr>
                <td class="bft-label">STIR</td>
                <td class="bft-wide">{{ $sozlamalar['kompaniya_inn'] }}</td>
            </tr>
            @endif
            @if($sozlamalar['kompaniya_telefon'] ?? '')
            <tr>
                <td class="bft-label">Telefon</td>
                <td class="bft-wide">{{ $sozlamalar['kompaniya_telefon'] }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif
@endsection
