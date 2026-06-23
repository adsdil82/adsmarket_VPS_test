<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}.info td:first-child{background:#f5f5f5;font-weight:bold;width:40%;}.total td{font-weight:bold;background:#e8f5e9;}.imzo table,.imzo td{border:none;}.imzo{margin-top:30px;}.imzo td{vertical-align:top;padding:6px;}</style>
</head><body><div style='padding:15mm'>
<h2>SCHYOT-FAKTURA</h2><p class='sub'>№ {{ $kredit->shartnoma_raqam }}-SF &nbsp;|&nbsp; {{ $kredit->boshlanish_sana?->format('d.m.Y') }}</p>
<table class='info'><tr><td>Sotuvchi</td><td>{{ $kredit->filial?->nomi }}</td></tr>
<tr><td>Xaridor</td><td><strong>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</strong></td></tr>
<tr><td>Shartnoma</td><td>№ {{ $kredit->shartnoma_raqam }}</td></tr></table>
<table><thead><tr><th>#</th><th>Nomi</th><th>O.B.</th><th>Miqdori</th><th>Narxi</th><th>Jami</th></tr></thead>
<tbody>@foreach($kredit->tovarlar as $i => $t)
<tr><td align='center'>{{ $i+1 }}</td><td>{{ $t->nomi }}</td><td align='center'>dona</td>
<td align='center'>{{ $t->soni }}</td><td align='right'>{{ number_format($t->narx,0,'.',' ') }}</td>
<td align='right'>{{ number_format($t->jami_narx,0,'.',' ') }}</td></tr>
@endforeach<tr class='total'><td colspan='5' align='right'>Jami to'lov:</td>
<td align='right'>{{ number_format($kredit->jami_summa,0,'.',' ') }} so'm</td></tr></tbody></table>
<div class='imzo'><table><tr>
<td width='50%'>Sotuvchi: ___________________<br>M.O.</td>
<td width='50%'>Xaridor: ___________________<br>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</td>
</tr></table></div></div></body></html>
