@extends('layouts.app')
@section('title', 'Ruxsatlar boshqaruvi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
    <li class="breadcrumb-item active">Ruxsatlar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-key me-2 text-warning"></i>Ruxsatlar boshqaruvi</h5>
        <small class="text-muted">Har bir rol uchun CRUD ruxsatlarini sozlang</small>
    </div>
</div>

<form method="POST" action="{{ route('admin.ruxsatlar.saqlash') }}">
    @csrf

    @foreach($resurslar as $resurs => $resursInfo)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header py-2 d-flex align-items-center gap-2">
            <i class="bi bi-{{ $resursInfo['icon'] }} text-primary"></i>
            <h6 class="mb-0 fw-bold">{{ $resursInfo['nomi'] }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th class="text-start" style="width:160px">Rol</th>
                        @foreach($amallar as $amal => $amalInfo)
                        <th>
                            <i class="bi bi-{{ $amalInfo['icon'] }} text-{{ $amalInfo['rang'] }}"></i>
                            <div class="small">{{ $amalInfo['nomi'] }}</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rollar as $rol)
                    @php
                        $isAdmin = $rol === 'admin';
                        $rolRang = match($rol) {
                            'admin'    => 'danger',
                            'menejer'  => 'primary',
                            'kassir'   => 'success',
                            'hisobchi' => 'secondary',
                            default    => 'secondary'
                        };
                    @endphp
                    <tr class="{{ $isAdmin ? 'table-light' : '' }}">
                        <td class="text-start">
                            <span class="badge bg-{{ $rolRang }}">{{ $rol }}</span>
                            @if($isAdmin)
                                <small class="text-muted ms-1">(to'liq ruxsat)</small>
                            @endif
                        </td>
                        @foreach($amallar as $amal => $amalInfo)
                        @php
                            $checked = $ruxsatlar[$rol][$resurs][$amal] ?? 0;
                            $key = "{$rol}_{$resurs}_{$amal}";
                        @endphp
                        <td>
                            @if($isAdmin)
                                {{-- Admin: o'zgartirib bo'lmaydi --}}
                                <input type="hidden" name="{{ $key }}" value="on">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            @else
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input rux-check"
                                           type="checkbox"
                                           name="{{ $key }}"
                                           id="{{ $key }}"
                                           {{ $checked ? 'checked' : '' }}
                                           style="width:1.3rem;height:1.3rem;cursor:pointer">
                                </div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
        <div class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Admin roli doim to'liq ruxsatga ega — o'zgartirib bo'lmaydi.
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">Bekor qilish</a>
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-save me-1"></i> Saqlash
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Tez tanlash: barcha satrni belgilash/olib tashlash
document.querySelectorAll('thead').forEach(thead => {
    thead.querySelectorAll('th').forEach((th, i) => {
        if (i === 0) return;
        th.style.cursor = 'pointer';
        th.title = "Hammasini belgilash/olib tashlash";
        th.addEventListener('click', () => {
            const table = th.closest('table');
            const checks = table.querySelectorAll(`tbody tr td:nth-child(${i+1}) input[type=checkbox]`);
            const anyUnchecked = [...checks].some(c => !c.checked);
            checks.forEach(c => c.checked = anyUnchecked);
        });
    });
});
</script>
@endpush
