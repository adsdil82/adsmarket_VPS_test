<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}.info td:first-child{background:#f5f5f5;font-weight:bold;width:40%;}p{line-height:1.8;margin:8px 0;}.imzo table,.imzo td{border:none;}.imzo{margin-top:30px;}.imzo td{vertical-align:top;padding:6px;}</style>
</head><body><div style='padding:15mm'>
<h2>KIRIM VARAQASI</h2><p class='sub'>№ KIR-{{ $kirim->id }} &nbsp;|&nbsp; {{ $kirim->kirim_sana->format('d.m.Y') }}</p>
<table class='info'>
<tr><td>Qabul qiluvchi tashkilot</td><td>{{ $kirim->filial?->nomi }}</td></tr>
<tr><td>Yetkazuvchi (ta'minotchi)</td><td>{{ $kirim->taminotchi?->nomi ?: '—' }}</td></tr>
<tr><td>Hujjat raqami</td><td>{{ $kirim->hujjat_raqam ?: '—' }}</td></tr>
<tr><td>Mas'ul xodim</td><td>{{ $kirim->xodim?->ism_familiya }}</td></tr>
</table>
<table><thead><tr><th>#</th><th>Tovar nomi</th><th>Miqdori</th><th>Narxi</th><th>Jami</th></tr></thead>
<tbody>@foreach($kirim->qatorlar as $i => $t)
<tr><td align='center'>{{ $i+1 }}</td><td>{{ $t->nomi }}</td><td align='center'>{{ $t->miqdor }} {{ $t->birlik ?? 'dona' }}</td>
<td align='right'>{{ number_format($t->narx,0,'.',' ') }} so'm</td>
<td align='right'>{{ number_format($t->jami,0,'.',' ') }} so'm</td></tr>
@endforeach<tr style='font-weight:bold;background:#eee'><td colspan='4' align='right'>Jami:</td>
<td align='right'>{{ number_format($kirim->jami_summa,0,'.',' ') }} so'm</td></tr></tbody></table>
@if($kirim->izoh)<p><strong>Izoh:</strong> {{ $kirim->izoh }}</p>@endif
<p>Yuqorida ko'rsatilgan tovarlar omborxonaga qabul qilib olindi.</p>
<div class='imzo'><table><tr>
<td width='50%'>Topshirdi (yetkazuvchi): ___________________<br>{{ $kirim->taminotchi?->nomi }}</td>
<td width='50%'>Qabul qildi (omborchi): ___________________<br>{{ $kirim->xodim?->ism_familiya }}<br>Sana: ___________________</td>
</tr></table></div></div></body></html>
