<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}.sub{text-align:center;color:#555;margin-bottom:16px;}table{width:100%;border-collapse:collapse;margin:10px 0;}th{background:#222;color:#fff;padding:5px;text-align:center;}td{padding:4px 6px;border:1px solid #ddd;}.info td:first-child{background:#f5f5f5;font-weight:bold;width:40%;}p{line-height:1.8;margin:8px 0;}.imzo table,.imzo td{border:none;}.imzo{margin-top:30px;}.imzo td{vertical-align:top;padding:6px;}</style>
</head><body><div style='padding:15mm'>
<h2>YUK XATI</h2><p class='sub'>№ {{ ->shartnoma_raqam }}-YX &nbsp;|&nbsp; {{ ->boshlanish_sana?->format('d.m.Y') }}</p>
<table class='info'><tr><td>Beruvchi tashkilot</td><td>{{ ->filial?->nomi }}</td></tr>
<tr><td>Oluvchi (mijoz)</td><td><strong>{{ ->mijoz?->familiya }} {{ ->mijoz?->ism }}</strong></td></tr>
<tr><td>Telefon</td><td>{{ ->mijoz?->telefon }}</td></tr></table>
<table><thead><tr><th>#</th><th>Tovar nomi</th><th>Miqdori</th><th>Narxi</th><th>Jami</th></tr></thead>
<tbody>@foreach(->tovarlar as =>)
<tr><td align='center'>{{ +1 }}</td><td>{{ ->nomi }}</td><td align='center'>{{ ->soni }} dona</td>
<td align='right'>{{ number_format(->narx,0,'.',' ') }} so'm</td>
<td align='right'>{{ number_format(->jami_narx,0,'.',' ') }} so'm</td></tr>
@endforeach<tr style='font-weight:bold;background:#eee'><td colspan='4' align='right'>Jami:</td>
<td align='right'>{{ number_format(->jami_summa,0,'.',' ') }} so'm</td></tr></tbody></table>
<p>Men, {{ ->mijoz?->familiya }} {{ ->mijoz?->ism }}, yuqorida ko'rsatilgan tovarlarni sog'-salomat qabul qildim.</p>
<div class='imzo'><table><tr>
<td width='50%'>Berdi: ___________________<br>{{ ->filial?->nomi }}</td>
<td width='50%'>Qabul qildi: ___________________<br>{{ ->mijoz?->familiya }} {{ ->mijoz?->ism }}<br>Sana: ___________________</td>
</tr></table></div></div></body></html>
