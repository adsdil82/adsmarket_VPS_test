<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:11px;}
h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}
.sub{text-align:center;color:#555;margin-bottom:16px;}
table.info{width:100%;border-collapse:collapse;margin:10px 0;}
table.info td{padding:4px 6px;border:1px solid #ccc;}
table.info td:first-child{background:#f5f5f5;font-weight:bold;width:40%;}
.imzo{margin-top:30px;}
.imzo table{width:100%;border:none;}
.imzo td{border:none;padding:6px;vertical-align:top;}
.imzo-line{border-top:1px solid #333;margin-top:40px;}
p{line-height:1.8;margin:8px 0;}
</style></head><body>
<div style='padding:20mm 15mm'>
<h2>KAFILLIK SHARTNOMASI</h2>
<p class='sub'>№ {{ ->shartnoma_raqam }}-K</p>
<table class='info'>
<tr><td>Asosiy shartnoma</td><td>№ {{ ->shartnoma_raqam }}</td></tr>
<tr><td>Asosiy qarzdor</td><td>{{ ->mijoz?->familiya }} {{ ->mijoz?->ism }}</td></tr>
<tr><td>Nasiya summasi</td><td>{{ number_format(->kredit_summa,0,'.',' ') }} so'm</td></tr>
<tr><td>Kafil F.I.O.</td><td>{{ ->kafil_ism ?: '—' }}</td></tr>
<tr><td>Kafil telefoni</td><td>{{ ->kafil_telefon ?: '—' }}</td></tr>
<tr><td>Kafil manzili</td><td>{{ ->kafil_manzil ?: '—' }}</td></tr>
</table>
<p>Men, quyida imzo qo'yuvchi kafil, {{ ->mijoz?->familiya }} {{ ->mijoz?->ism }} tomonidan olingan
{{ number_format(->kredit_summa,0,'.',' ') }} so'mlik nasiya majburiyatini o'z vaqtida to'lashiga kafolat beraman.</p>
<p>Asosiy qarzdor to'lovni amalga oshirmagan taqdirda, ushbu majburiyatni o'zim bajarishga roziman.</p>
<div class='imzo'>
<table><tr>
<td width='50%'>Tashkilot: ___________________<br>M.O.</td>
<td width='50%'>Kafil imzosi: ___________________<br>{{ ->kafil_ism ?: '___________________' }}<br>Sana: ___________________</td>
</tr></table>
</div>
</div>
</body></html>
