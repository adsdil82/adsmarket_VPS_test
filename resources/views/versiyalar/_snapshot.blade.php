{{-- Shartnoma snapshot ko'rsatuvchi partial view --}}
@php
$fields = [
    'shartnoma_raqam'     => 'Shartnoma raqami',
    'holat'               => 'Holat',
    'jami_summa'          => 'Jami summa',
    'boshlangich_tolov'   => 'Boshlang\'ich to\'lov',
    'kredit_summa'        => 'Kredit summasi',
    'tolov_qilingan'      => 'To\'langan',
    'qoldiq_qarz'         => 'Qoldiq qarz',
    'boshlanish_sana'     => 'Boshlanish sanasi',
    'tugash_sana'         => 'Tugash sanasi',
    'muddati_oy'          => 'Muddati (oy)',
    'oylik_tolov_miqdori' => 'Oylik to\'lov',
    'foiz_stavka'         => 'Foiz stavkasi',
    'kafil_ism'           => 'Kafil F.I.O.',
    'kafil_telefon'       => 'Kafil telefon',
    'kafil_manzil'        => 'Kafil manzil',
    'izoh'                => 'Izoh',
];

$pul = ['jami_summa','boshlangich_tolov','kredit_summa','tolov_qilingan','qoldiq_qarz','oylik_tolov_miqdori'];
@endphp

<table class="table table-sm table-borderless mb-0">
    @foreach($fields as $key => $label)
        @if(isset($snapshot[$key]))
        <tr class="{{ in_array($key, $ozgarganlar) ? 'table-warning' : '' }}">
            <td class="text-muted ps-3" style="width:45%">
                {{ $label }}
                @if(in_array($key, $ozgarganlar))
                    <i class="bi bi-pencil-fill text-warning ms-1" style="font-size:10px"></i>
                @endif
            </td>
            <td class="pe-3">
                @if(in_array($key, $pul))
                    {{ number_format((float)$snapshot[$key], 0, '.', ' ') }} so'm
                @elseif($key === 'holat')
                    <span class="badge bg-secondary">{{ $snapshot[$key] }}</span>
                @else
                    {{ $snapshot[$key] ?? '—' }}
                @endif
            </td>
        </tr>
        @endif
    @endforeach
</table>
