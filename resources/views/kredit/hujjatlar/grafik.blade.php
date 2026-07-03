<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;}
h2{text-align:center;font-size:13px;text-transform:uppercase;margin-bottom:4px;}
.sub{text-align:center;color:#555;margin-bottom:12px;}
table{width:100%;border-collapse:collapse;margin:10px 0;}
th{background:#222;color:#fff;padding:5px;text-align:center;}
td{padding:4px 6px;border:1px solid #ddd;text-align:center;}
tr:nth-child(even) td{background:#f9f9f9;}
.tolangan{background:#d4edda!important;}
.tfoot td{font-weight:bold;background:#e8f5e9;}
.imzo{margin-top:20px;}
.imzo table{border:none;}
.imzo td{border:none;padding:6px;}
</style></head><body>
<h2>TO'LOV GRAFIGI</h2>
<p class='sub'>Shartnoma № {{ $kredit->shartnoma_raqam }} &nbsp;|&nbsp;
{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }} &nbsp;|&nbsp;
Kredit summasi: {{ number_format($kredit->kredit_summa,0,'.',' ') }} so'm &nbsp;|&nbsp;
Muddat: {{ $kredit->muddati_oy }} oy</p>
<table>
<thead><tr><th>#</th><th>To'lov sanasi</th><th>To'lov summasi</th><th>shu jumladan ustama</th><th>Qoldiq qarz</th><th>Holat</th></tr></thead>
<tbody>
@foreach($kredit->grafik->filter(fn($x) => $x->tolov_sana !== null) as $g)
<tr class='{{ $g->holat === "tolangan" ? "tolangan" : "" }}'>
  <td>{{ $g->oylik_tartib }}</td>
  <td>{{ $g->tolov_sana?->format('d.m.Y') }}</td>
  <td>{{ number_format($g->tolov_summa,0,'.',' ') }} so'm</td>
  <td>{{ number_format($g->ustama_summa,0,'.',' ') }} so'm</td>
  <td>{{ number_format($g->qoldiq_suma,0,'.',' ') }} so'm</td>
  <td>{{ $g->holat==='tolangan'?'To\'langan':($g->holat==='qisman'?'Qisman':'To\'lanmagan') }}</td>
</tr>
@endforeach
<tr class='tfoot'>
  <td colspan='2'>Jami:</td>
  <td>{{ number_format($kredit->grafik->sum('tolov_summa'),0,'.',' ') }} so'm</td>
  <td>{{ number_format($kredit->grafik->sum('ustama_summa'),0,'.',' ') }} so'm</td>
  <td></td><td></td>
</tr>
</tbody>
</table>
<div class='imzo'>
<table><tr>
<td width='50%'>Tashkilot imzosi: ___________________<br>M.O.</td>
<td width='50%'>Mijoz imzosi: ___________________<br>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</td>
</tr></table>
</div>
</body></html>
