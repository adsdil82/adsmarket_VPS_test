@extends('layouts.app')
@section('title', 'Ruxsatlar boshqaruvi')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
    <li class="breadcrumb-item active">Ruxsatlar</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0"><i class="bi bi-key me-2 text-warning"></i>Ruxsatlar boshqaruvi</h5>
        <small class="text-muted">Har bir rol uchun modul bo'yicha CRUD ruxsatlarini sozlang</small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="ruxsat-hammasini-och">
            <i class="bi bi-arrows-expand me-1"></i>Hammasini ochish
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="ruxsat-hammasini-yop">
            <i class="bi bi-arrows-collapse me-1"></i>Hammasini yopish
        </button>
    </div>
</div>

<form method="POST" action="{{ route('admin.ruxsatlar.saqlash') }}">
    @csrf

    <div class="accordion" id="ruxsatlarAccordion">
        @foreach($resurslar as $resurs => $resursInfo)
        @php
            $jamiRuxsat = 0;
            foreach ($rollar as $rolObj) {
                if ($rolObj->kalit === 'admin') continue;
                foreach ($amallar as $amal => $amalInfo) {
                    if (($ruxsatlar[$rolObj->kalit][$resurs][$amal] ?? 0)) $jamiRuxsat++;
                }
            }
        @endphp
        <div class="accordion-item border-0 shadow-sm mb-2">
            <h2 class="accordion-header">
                <button type="button"
                        class="accordion-button collapsed py-2 fw-bold"
                        data-bs-toggle="collapse"
                        data-bs-target="#ruxsat-{{ $resurs }}">
                    <i class="bi bi-{{ $resursInfo['icon'] }} text-primary me-2"></i>
                    {{ $resursInfo['nomi'] }}
                    <span class="badge bg-light text-dark border ms-2">{{ $jamiRuxsat }} ruxsat faol</span>
                </button>
            </h2>
            <div id="ruxsat-{{ $resurs }}" class="accordion-collapse collapse">
                <div class="accordion-body p-0">
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
                                @foreach($rollar as $rolObj)
                                @php
                                    $rol = $rolObj->kalit;
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
                                        <i class="bi bi-{{ $rolObj->icon }} text-{{ $rolRang }} me-1"></i>
                                        <span class="badge bg-{{ $rolRang }}">{{ $rolObj->nomi }}</span>
                                        @if($isAdmin)
                                            <small class="text-muted ms-1">(to'liq ruxsat)</small>
                                        @endif
                                        @if(!$rolObj->tizim)
                                            <span class="badge bg-light text-dark border ms-1" style="font-size:.6rem">maxsus</span>
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
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
        <div class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Admin roli doim to'liq ruxsatga ega — o'zgartirib bo'lmaydi. "Ko'rish" ruxsati o'chirilgan
            modul foydalanuvchi menyusida (chap panelda) butunlay ko'rinmay qoladi.
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
// Tez tanlash: ustun sarlavhasini bosib, butun ustunni belgilash/olib tashlash
document.querySelectorAll('.accordion-body thead').forEach(thead => {
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

// Hammasini ochish / yopish
document.getElementById('ruxsat-hammasini-och').addEventListener('click', () => {
    document.querySelectorAll('#ruxsatlarAccordion .accordion-collapse').forEach(el => {
        new bootstrap.Collapse(el, { toggle: false }).show();
    });
});
document.getElementById('ruxsat-hammasini-yop').addEventListener('click', () => {
    document.querySelectorAll('#ruxsatlarAccordion .accordion-collapse').forEach(el => {
        new bootstrap.Collapse(el, { toggle: false }).hide();
    });
});
</script>
@endpush
