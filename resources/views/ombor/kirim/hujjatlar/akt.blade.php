<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}p{line-height:1.8;margin:8px 0;}.imzo{margin-top:30px;}.imzo table,.imzo td{border:none;}.imzo td{vertical-align:top;padding:10px 6px;}</style>
</head><body><div style='padding:15mm'>
<h2>TOVARLARNI QABUL QILISH AKTI</h2>
<p class='sub'>№ KIR-{{ $kirim->id }}-AKT &nbsp;|&nbsp; {{ $kirim->kirim_sana->format('d.m.Y') }}</p>

<p>{{ $kirim->filial?->nomi }} filiali bo'yicha quyida tuzilgan komissiya
"<strong>{{ $kirim->taminotchi?->nomi ?: 'noma\'lum ta\'minotchi' }}</strong>" tomonidan yetkazib berilgan
quyidagi tovarlarni miqdori va sifati bo'yicha tekshirib, omborxona qoldig'iga qabul qilish to'g'risida ushbu aktni tuzdi:</p>

<table><thead><tr><th>#</th><th>Tovar nomi</th><th>Miqdori</th><th>Narxi</th><th>Jami qiymati</th></tr></thead>
<tbody>@foreach($kirim->qatorlar as $i => $t)
<tr><td align='center'>{{ $i+1 }}</td><td>{{ $t->nomi }}</td><td align='center'>{{ $t->miqdor }} {{ $t->birlik ?? 'dona' }}</td>
<td align='right'>{{ number_format($t->narx,0,'.',' ') }} so'm</td>
<td align='right'>{{ number_format($t->jami,0,'.',' ') }} so'm</td></tr>
@endforeach<tr style='font-weight:bold;background:#eee'><td colspan='4' align='right'>Jami qiymati:</td>
<td align='right'>{{ number_format($kirim->jami_summa,0,'.',' ') }} so'm</td></tr></tbody></table>

@if($kirim->izoh)<p><strong>Izoh:</strong> {{ $kirim->izoh }}</p>@endif

<p>Komissiya xulosasi: yuqorida ko'rsatilgan tovarlar miqdori va sifati hujjatlarga mos, kamomad va nuqsonlarsiz
qabul qilindi hamda omborxona hisobiga olindi deb topildi.</p>

<div class='imzo'><table>
<tr><td width='34%'>Komissiya raisi: ___________________</td>
    <td width='33%'>A'zo: ___________________</td>
    <td width='33%'>A'zo: ___________________</td></tr>
<tr><td>Mas'ul xodim (omborchi): {{ $kirim->xodim?->ism_familiya }}<br>Imzo: ___________________</td>
    <td colspan='2'>Sana: {{ $kirim->kirim_sana->format('d.m.Y') }}</td></tr>
</table></div>
</div></body></html>
