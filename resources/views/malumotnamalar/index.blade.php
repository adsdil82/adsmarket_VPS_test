@extends('layouts.app')
@section('title',"Ma'lumotnomalar")
@section('content')
<div class="container-fluid px-3 py-3">

<h5 class="fw-bold mb-4"><i class="bi bi-journal-bookmark text-primary me-2"></i>Ma'lumotnomalar</h5>

@php
$guruhlar = [
    [
        'sarlavha' => 'Tashkiliy',
        'icon'     => 'bi-building',
        'rang'     => 'primary',
        'bandlar'  => [
            ['nomi'=>'Filiallar',       'route'=>'malumotnamalar.filiallar.index',  'icon'=>'bi-building',     'sana'=>$stats['filiallar']     ?? 0],
            ['nomi'=>'Foydalanuvchilar','route'=>'admin.foydalanuvchilar',           'icon'=>'bi-people',       'sana'=>$stats['foydalanuvchilar'] ?? 0],
            ['nomi'=>'Kassalar',        'route'=>'malumotnamalar.kassalar.index',    'icon'=>'bi-cash-stack',   'sana'=>$stats['kassalar']      ?? 0],
            ['nomi'=>'POS to\'lov usullari', 'route'=>'malumotnamalar.pos-tolov-usullari.index', 'icon'=>'bi-credit-card-2-front', 'sana'=>$stats['pos_tolov_usullari'] ?? 0],
        ],
    ],
    [
        'sarlavha' => 'Tovar va Ombor',
        'icon'     => 'bi-box-seam',
        'rang'     => 'success',
        'bandlar'  => [
            ['nomi'=>'Tovar guruhlari', 'route'=>'tovar-guruhlar.index',           'icon'=>'bi-folder2',      'sana'=>$stats['tovar_guruhlar'] ?? 0],
            ['nomi'=>'Tovar katalogi',  'route'=>'katalog.index',                  'icon'=>'bi-upc-scan',     'sana'=>$stats['tovar_katalog']  ?? 0],
            ['nomi'=>'Birliklar',       'route'=>'malumotnamalar.birliklar.index', 'icon'=>'bi-rulers',       'sana'=>$stats['birliklar']      ?? 0],
        ],
    ],
    [
        'sarlavha' => 'Moliya',
        'icon'     => 'bi-currency-exchange',
        'rang'     => 'warning',
        'bandlar'  => [
            ['nomi'=>"To'lov turlari",           'route'=>'buxgalteriya.tulov_turlari.index',        'icon'=>'bi-credit-card',     'sana'=>$stats['tulov_turlari']     ?? 0],
            ['nomi'=>'Harajat turlari',           'route'=>'malumotnamalar.harajat-turlari.index',    'icon'=>'bi-tags',            'sana'=>$stats['harajat_turlari']   ?? 0],
            ['nomi'=>'Pul oqimi kategoriyalari',  'route'=>'malumotnamalar.pul-kategoriyalar.index',  'icon'=>'bi-arrow-left-right','sana'=>$stats['pul_kategoriyalar'] ?? 0],
        ],
    ],
    [
        'sarlavha' => 'Buxgalteriya',
        'icon'     => 'bi-calculator',
        'rang'     => 'info',
        'bandlar'  => [
            ['nomi'=>'Hisoblar rejasi',  'route'=>'buxgalteriya.hisoblar.index', 'icon'=>'bi-list-ol',  'sana'=>$stats['hisoblar_rejasi'] ?? 0],
        ],
    ],
    [
        'sarlavha' => 'Hujjatlar va Xabarnomalar',
        'icon'     => 'bi-file-earmark-text',
        'rang'     => 'secondary',
        'bandlar'  => [
            ['nomi'=>'Xabarnoma shablonlari', 'route'=>'xabarnoma.shablonlar.index', 'icon'=>'bi-bell', 'sana'=>$stats['notification_templates'] ?? 0],
        ],
    ],
    [
        'sarlavha' => 'Integratsiyalar',
        'icon'     => 'bi-plug',
        'rang'     => 'dark',
        'bandlar'  => [
            ['nomi'=>'Qurilma provayderlar', 'route'=>'qurilma-provayderlar.index', 'icon'=>'bi-plug', 'sana'=>$stats['qurilma_provayderlar'] ?? 0],
        ],
    ],
    [
        'sarlavha' => 'Tizim',
        'icon'     => 'bi-gear',
        'rang'     => 'secondary',
        'bandlar'  => [
            ['nomi'=>'Sozlamalar',   'route'=>'admin.sozlamalar',  'icon'=>'bi-gear',    'sana'=>null],
            ['nomi'=>'Ruxsatlar',    'route'=>'admin.ruxsatlar',   'icon'=>'bi-shield',  'sana'=>null],
        ],
    ],
];
@endphp

<div class="row g-3">
    @foreach($guruhlar as $g)
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="fw-bold text-{{ $g['rang'] }} mb-0">
                    <i class="bi {{ $g['icon'] }} me-2"></i>{{ $g['sarlavha'] }}
                </h6>
            </div>
            <div class="card-body pt-2">
                <div class="row g-2">
                    @foreach($g['bandlar'] as $b)
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route($b['route']) }}"
                           class="card border-0 bg-light text-decoration-none h-100 p-3 d-flex flex-row align-items-center gap-3">
                            <div class="rounded-3 bg-{{ $g['rang'] }} bg-opacity-10 p-2">
                                <i class="bi {{ $b['icon'] }} text-{{ $g['rang'] }} fs-5"></i>
                            </div>
                            <div>
                                <div class="small fw-semibold text-dark">{{ $b['nomi'] }}</div>
                                @if($b['sana'] !== null)
                                <div class="text-muted" style="font-size:.75rem">{{ $b['sana'] }} ta yozuv</div>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

</div>
@endsection
