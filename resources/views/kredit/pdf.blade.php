<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Shartnoma {{ $kredit->shartnoma_raqam }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 5px; }
        h3 { font-size: 13px; margin: 15px 0 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .header-info { text-align: center; color: #666; margin-bottom: 20px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        td, th { border: 1px solid #ddd; padding: 4px 8px; }
        th { background: #f5f5f5; font-weight: bold; }
        .info-table td:first-child { width: 35%; background: #fafafa; font-weight: bold; }
        .no-border td { border: none; padding: 3px 0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 3px; font-size: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
        .page-break { page-break-after: always; }
        .signature-block { margin-top: 40px; }
        .signature-block table td { border: none; padding: 2px; }
    </style>
</head>
<body>

<h1>NASIYA SHARTNOMASI</h1>
<div class="header-info">
    {{ $kredit->shartnoma_raqam }} · {{ $kredit->filial->nomi }} ·
    Tuzilgan sana: {{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }}
</div>

{{-- Umumiy ma'lumotlar --}}
<h3>Shartnoma ma'lumotlari</h3>
<table class="info-table">
    <tr><td>Shartnoma raqami</td><td>{{ $kredit->shartnoma_raqam }}</td></tr>
    <tr><td>Mijoz</td><td>{{ $kredit->mijoz->tolik_ism }}</td></tr>
    <tr><td>Telefon</td><td>{{ $kredit->mijoz->telefon }}</td></tr>
    <tr><td>Passport</td><td>{{ $kredit->mijoz->passport_tolik }}</td></tr>
    <tr><td>Manzil</td><td>{{ $kredit->mijoz->manzil ?? '—' }}</td></tr>
    <tr><td>Filial</td><td>{{ $kredit->filial->nomi }}</td></tr>
    <tr><td>Xodim</td><td>{{ $kredit->xodim->ism_familiya }}</td></tr>
    <tr><td>Holat</td><td>{{ $kredit->holat }}</td></tr>
</table>

{{-- Moliyaviy --}}
<h3>Moliyaviy shartlar</h3>
<table class="info-table">
    <tr><td>Jami summa</td><td class="text-right"><strong>{{ number_format($kredit->jami_summa, 0, '.', ' ') }} so'm</strong></td></tr>
    <tr><td>Boshlang'ich to'lov</td><td class="text-right">{{ number_format($kredit->boshlangich_tolov, 0, '.', ' ') }} so'm</td></tr>
    <tr><td>Nasiya summasi</td><td class="text-right">{{ number_format($kredit->kredit_summa, 0, '.', ' ') }} so'm</td></tr>
    <tr><td>Muddat</td><td>{{ $kredit->muddati_oy }} oy</td></tr>
    <tr><td>Oylik to'lov</td><td class="text-right">{{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }} so'm</td></tr>
    <tr><td>Foiz stavkasi</td><td>{{ $kredit->foiz_stavka }}%</td></tr>
    <tr><td>Boshlanish sanasi</td><td>{{ $kredit->boshlanish_sana?->format('d.m.Y') ?? '—' }}</td></tr>
    <tr><td>Tugash sanasi</td><td>{{ $kredit->tugash_sana?->format('d.m.Y') ?? '—' }}</td></tr>
    <tr><td>To'langan</td><td class="text-right">{{ number_format($kredit->tolov_qilingan, 0, '.', ' ') }} so'm</td></tr>
    <tr><td>Qoldiq qarz</td><td class="text-right"><strong>{{ number_format($kredit->qoldiq_qarz, 0, '.', ' ') }} so'm</strong></td></tr>
</table>

{{-- Tovarlar --}}
<h3>Tovarlar ro'yxati</h3>
<table>
    <thead>
        <tr>
            <th class="text-center">#</th>
            <th>Tovar nomi</th>
            <th class="text-center">Soni</th>
            <th class="text-right">Narx</th>
            <th class="text-right">Jami</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kredit->tovarlar as $i => $tovar)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $tovar->nomi }}</td>
            <td class="text-center">{{ $tovar->soni }}</td>
            <td class="text-right">{{ number_format($tovar->narx, 0, '.', ' ') }}</td>
            <td class="text-right"><strong>{{ number_format($tovar->jami_narx, 0, '.', ' ') }}</strong></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong>Jami:</strong></td>
            <td class="text-right"><strong>{{ number_format($kredit->tovarlar->sum('jami_narx'), 0, '.', ' ') }}</strong></td>
        </tr>
    </tfoot>
</table>

{{-- To'lov grafigi --}}
<h3>To'lov grafigi</h3>
<table>
    <thead>
        <tr>
            <th class="text-center">Oy</th>
            <th>Sana</th>
            <th class="text-right">Summa</th>
            <th class="text-right">Qoldiq</th>
            <th class="text-center">Holat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kredit->grafik as $g)
        <tr>
            <td class="text-center">{{ $g->oylik_tartib }}</td>
            <td>{{ $g->tolov_sana?->format('d.m.Y') ?? '—' }}</td>
            <td class="text-right">{{ $g->tolov_summa !== null ? number_format($g->tolov_summa, 0, '.', ' ') : '—' }}</td>
            <td class="text-right">{{ $g->qoldiq_suma !== null ? number_format($g->qoldiq_suma, 0, '.', ' ') : '—' }}</td>
            <td class="text-center">{{ $g->holat }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($kredit->kafil_ism)
<h3>Kafil ma'lumotlari</h3>
<table class="info-table">
    <tr><td>F.I.O.</td><td>{{ $kredit->kafil_ism }}</td></tr>
    <tr><td>Telefon</td><td>{{ $kredit->kafil_telefon ?? '—' }}</td></tr>
    <tr><td>Manzil</td><td>{{ $kredit->kafil_manzil ?? '—' }}</td></tr>
</table>
@endif

{{-- Imzolar --}}
<div class="signature-block">
    <table>
        <tr>
            <td style="width:50%; padding: 20px 0;">
                <div style="border-top: 1px solid #333; margin-top: 40px; padding-top: 5px;">
                    <strong>Xodim imzosi:</strong> {{ $kredit->xodim->ism_familiya }}
                </div>
            </td>
            <td style="width:50%; padding: 20px 0;">
                <div style="border-top: 1px solid #333; margin-top: 40px; padding-top: 5px;">
                    <strong>Mijoz imzosi:</strong> {{ $kredit->mijoz->tolik_ism }}
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Chop etilgan: {{ now()->format('d.m.Y H:i') }} · NasiyaPro · {{ $kredit->filial->nomi }}
</div>

</body>
</html>
