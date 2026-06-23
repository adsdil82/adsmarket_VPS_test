<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}.info td:first-child{background:#f5f5f5;font-weight:bold;width:40%;}p{line-height:1.8;margin:8px 0;}.imzo table,.imzo td{border:none;}.imzo{margin-top:30px;}.imzo td{vertical-align:top;padding:6px;}</style>
</head><body><div style='padding:15mm'>
<h2>YUK XATI</h2><p class='sub'>№ {{ $kredit->shartnoma_raqam }}-YX &nbsp;|&nbsp; {{ $kredit->boshlanish_sana?->format('d.m.Y') }}</p>
<table class='info'><tr><td>Beruvchi tashkilot</td><td>{{ $kredit->filial?->nomi }}</td></tr>
<tr><td>Oluvchi (mijoz)</td><td><strong>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</strong></td></tr>
<tr><td>Telefon</td><td>{{ $kredit->mijoz?->telefon }}</td></tr></table>
<table><thead><tr><th>#</th><th>Tovar nomi</th><th>Miqdori</th><th>Narxi</th><th>Jami</th></tr></thead>
<tbody>@foreach($kredit->tovarlar as $i => $t)
<tr><td align='center'>{{ $i+1 }}</td><td>{{ $t->nomi }}</td><td align='center'>{{ $t->soni }} dona</td>
<td align='right'>{{ number_format($t->narx,0,'.',' ') }} so'm</td>
<td align='right'>{{ number_format($t->jami_narx,0,'.',' ') }} so'm</td></tr>
@endforeach<tr style='font-weight:bold;background:#eee'><td colspan='4' align='right'>Jami:</td>
<td align='right'>{{ number_format($kredit->jami_summa,0,'.',' ') }} so'm</td></tr></tbody></table>
<p>Men, {{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}, yuqorida ko'rsatilgan tovarlarni sog'-salomat qabul qildim.</p>
<div class='imzo'><table><tr>
<td width='50%'>Berdi: ___________________<br>{{ $kredit->filial?->nomi }}</td>
<td width='50%'>Qabul qildi: ___________________<br>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}<br>Sana: ___________________</td>
</tr></table></div></div></body></html>
