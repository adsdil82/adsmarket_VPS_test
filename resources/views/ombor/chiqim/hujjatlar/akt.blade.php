<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}p{line-height:1.8;margin:8px 0;}.imzo{margin-top:30px;}.imzo table,.imzo td{border:none;}.imzo td{vertical-align:top;padding:10px 6px;}</style>
</head><body><div style='padding:15mm'>
<h2>TOVARLARNI HISOBDAN CHIQARISH AKTI</h2>
<p class='sub'>№ CHQ-{{ $chiqim->id }}-AKT &nbsp;|&nbsp; {{ $chiqim->sana->format('d.m.Y') }}</p>

<p>{{ $chiqim->filial?->nomi }} filiali bo'yicha quyida tuzilgan komissiya
"<strong>{{ \App\Models\OmbordanChiqim::$sabablar[$chiqim->sabab] ?? $chiqim->sabab }}</strong>"
sababi bilan quyidagi tovarlarni omborxona qoldig'idan hisobdan chiqarish to'g'risida ushbu aktni tuzdi:</p>

<table><thead><tr><th>#</th><th>Tovar nomi</th><th>Miqdori</th><th>Narxi</th><th>Jami qiymati</th></tr></thead>
<tbody>@foreach($chiqim->tafsilot as $i => $t)
<tr><td align='center'>{{ $i+1 }}</td><td>{{ $t->tovar?->nomi }}</td><td align='center'>{{ $t->miqdor }} {{ $t->tovar?->birlik }}</td>
<td align='right'>{{ number_format($t->narx,0,'.',' ') }} so'm</td>
<td align='right'>{{ number_format($t->jami_summa,0,'.',' ') }} so'm</td></tr>
@endforeach<tr style='font-weight:bold;background:#eee'><td colspan='4' align='right'>Jami qiymati:</td>
<td align='right'>{{ number_format($chiqim->umumiy_summa,0,'.',' ') }} so'm</td></tr></tbody></table>

@if($chiqim->izoh)<p><strong>Izoh:</strong> {{ $chiqim->izoh }}</p>@endif

<p>Komissiya xulosasi: yuqorida ko'rsatilgan tovarlar "{{ \App\Models\OmbordanChiqim::$sabablar[$chiqim->sabab] ?? $chiqim->sabab }}"
sababiga ko'ra qayta sotib bo'lmaydi va omborxona hisobidan chiqarilishi lozim deb topildi.</p>

<div class='imzo'><table>
<tr><td width='34%'>Komissiya raisi: ___________________</td>
    <td width='33%'>A'zo: ___________________</td>
    <td width='33%'>A'zo: ___________________</td></tr>
<tr><td>Mas'ul xodim: {{ $chiqim->xodim?->ism_familiya }}<br>Imzo: ___________________</td>
    <td colspan='2'>Sana: {{ $chiqim->sana->format('d.m.Y') }}</td></tr>
</table></div>
</div></body></html>
