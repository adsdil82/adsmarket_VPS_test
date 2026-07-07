<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}.info td:first-child{background:#f5f5f5;font-weight:bold;width:40%;}.total td{font-weight:bold;background:#e8f5e9;}.imzo table,.imzo td{border:none;}.imzo{margin-top:30px;}.imzo td{vertical-align:top;padding:6px;}</style>
</head><body><div style='padding:15mm'>
<h2>SCHYOT-FAKTURA (ICHKI HISOBOT)</h2><p class='sub'>№ KIR-{{ $kirim->id }}-SF &nbsp;|&nbsp; {{ $kirim->kirim_sana->format('d.m.Y') }}</p>
<table class='info'>
<tr><td>Tashkilot (qabul qiluvchi)</td><td>{{ $kirim->filial?->nomi }}</td></tr>
<tr><td>Yetkazuvchi (ta'minotchi)</td><td>{{ $kirim->taminotchi?->nomi ?: '—' }}</td></tr>
<tr><td>Hujjat raqami</td><td>{{ $kirim->hujjat_raqam ?: '—' }}</td></tr>
<tr><td>Mas'ul xodim</td><td>{{ $kirim->xodim?->ism_familiya }}</td></tr>
</table>
<table><thead><tr><th>#</th><th>Nomi</th><th>O.B.</th><th>Miqdori</th><th>Narxi</th><th>Jami</th></tr></thead>
<tbody>@foreach($kirim->qatorlar as $i => $t)
<tr><td align='center'>{{ $i+1 }}</td><td>{{ $t->nomi }}</td><td align='center'>{{ $t->birlik ?? 'dona' }}</td>
<td align='center'>{{ $t->miqdor }}</td><td align='right'>{{ number_format($t->narx,0,'.',' ') }}</td>
<td align='right'>{{ number_format($t->jami,0,'.',' ') }}</td></tr>
@endforeach<tr class='total'><td colspan='5' align='right'>Jami qiymat:</td>
<td align='right'>{{ number_format($kirim->jami_summa,0,'.',' ') }} so'm</td></tr></tbody></table>
<div class='imzo'><table><tr>
<td width='50%'>Tuzdi: ___________________<br>{{ $kirim->xodim?->ism_familiya }}</td>
<td width='50%'>Tasdiqladi: ___________________<br>M.O.</td>
</tr></table></div></div></body></html>
