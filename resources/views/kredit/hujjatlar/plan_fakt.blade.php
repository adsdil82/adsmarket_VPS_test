<!DOCTYPE html><html lang='uz'><head><meta charset='UTF-8'>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;color:#222;}
h2{text-align:center;font-size:14px;text-transform:uppercase;margin-bottom:2px;}
.sub{text-align:center;color:#555;margin-bottom:10px;}
.karta{width:100%;border-collapse:collapse;margin-bottom:10px;}
.karta td{border:1px solid #ccc;padding:6px 8px;}
.karta .lab{color:#666;width:24%;}
.karta .val{font-weight:bold;width:26%;}
table.grafik{width:100%;border-collapse:collapse;margin-top:8px;}
table.grafik th{background:#222;color:#fff;padding:5px;text-align:center;font-size:9px;}
table.grafik td{padding:4px 6px;border:1px solid #ddd;text-align:center;font-size:9.5px;}
table.grafik tr:nth-child(even) td{background:#f9f9f9;}
.tolangan{background:#d4edda!important;}
.kechikkan{background:#f8d7da!important;}
.imzo{margin-top:18px;}
.imzo table{border:none;width:100%;}
.imzo td{border:none;padding:6px;}
</style></head><body>

<h2>To'lov holati bo'yicha ko'chirma (PLAN / FAKT)</h2>
<p class='sub'>Shartnoma № {{ $kredit->shartnoma_raqam }} &nbsp;|&nbsp; Sana: {{ now()->format('d.m.Y') }}</p>

@php
    // "Hozirgacha to'langan" — faqat grafik bo'yicha (oylik) to'lovlar, boshlang'ich
    // to'lov bu yerga kirmaydi (u alohida ko'rsatiladi).
    $jamiToplangan   = (float) $kredit->tolov_qilingan;
    $dastlabkiQoldiq = (float) $kredit->jami_summa - (float) $kredit->boshlangich_tolov;
    $foiz = $dastlabkiQoldiq > 0 ? round($jamiToplangan / $dastlabkiQoldiq * 100, 1) : 0;
@endphp

<table class='karta'>
<tr>
  <td class='lab'>Mijoz</td><td class='val'>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</td>
  <td class='lab'>Telefon</td><td class='val'>{{ $kredit->mijoz?->telefon }}</td>
</tr>
<tr>
  <td class='lab'>Shartnoma summasi</td><td class='val'>{{ number_format($kredit->jami_summa,0,'.',' ') }} so'm</td>
  <td class='lab'>Boshlang'ich to'lov</td><td class='val'>{{ number_format($kredit->boshlangich_tolov,0,'.',' ') }} so'm</td>
</tr>
<tr>
  <td class='lab'>Muddat</td><td class='val'>{{ $kredit->muddati_oy }} oy</td>
  <td class='lab'>Boshlanish — tugash</td><td class='val'>{{ $kredit->boshlanish_sana?->format('d.m.Y') }} — {{ $kredit->tugash_sana?->format('d.m.Y') }}</td>
</tr>
<tr>
  <td class='lab'>Hozirgacha to'langan</td><td class='val' style='color:#1a7d36'>{{ number_format($jamiToplangan,0,'.',' ') }} so'm ({{ $foiz }}%)</td>
  <td class='lab'>Dastlabki qoldiq</td><td class='val'>{{ number_format($dastlabkiQoldiq,0,'.',' ') }} so'm</td>
</tr>
<tr>
  <td class='lab'>Holat</td>
  <td class='val'>{{ $kredit->holatNomi }}</td>
  <td class='lab'>Joriy ({{ now()->format('d.m.Y') }}) sanaga qoldiq</td>
  <td class='val' style='color:#b02a37'>{{ number_format($kredit->qoldiq_qarz,0,'.',' ') }} so'm</td>
</tr>
</table>

<table class='grafik'>
<thead><tr>
  <th>#</th><th>Reja sanasi</th><th>Reja summasi</th><th>Qoldiq (reja)</th>
  <th>Fakt to'langan</th><th>Fakt sanasi</th><th>Qoldiq (fakt)</th>
  <th>Farq (fakt−reja)</th><th>Kam to'langan</th><th>Kechikkan kun</th>
</tr></thead>
<tbody>
@php
    $faktTolangan = 0; $jamiKechikkanKun = 0; $jamiKamTolangan = 0;
    $korsatilganQatorlar = $kredit->grafik->filter(fn($x) => $x->tolov_sana !== null);
    // "Farq" ustuni har bir qatorda takrorlanib chalkashtirmasligi uchun —
    // faqat ENG OXIRGI haqiqiy faollik bo'lgan qatorda (oxirgi to'langan yoki
    // joriy kechikkan/qisman qator) ko'rsatiladi, qolganlarida "—".
    $oxirgiFaolQator = $korsatilganQatorlar->last(fn($x) => $x->holat !== 'tolanmagan') ?? $korsatilganQatorlar->last();
@endphp
@foreach($korsatilganQatorlar as $g)
@php
    $faktTolangan += (float) $g->tolangan_summa;
    $faktQoldiq = max(0, $kredit->kredit_summa - $faktTolangan);
    $rowClass = $g->holat === 'tolangan' ? 'tolangan' : ($g->holat === 'muddati_otgan' ? 'kechikkan' : '');

    // Reja qoldig'iga nisbatan fakt qoldig'i farqi: musbat — rejadan orqada
    // (qarz ko'proq qolgan), manfiy — rejadan oldinda (erta to'lagan).
    $rejaQoldiq = (float) ($g->qoldiq_suma ?? 0);
    $farq = $faktQoldiq - $rejaQoldiq;

    // Shu oy bo'yicha to'liq to'lanmagan (chala) qism
    $kamTolangan = $g->holat === 'qisman'
        ? max(0, (float) $g->tolov_summa - (float) $g->tolangan_summa)
        : 0;

    $jamiKechikkanKun += $g->kechikish_kunlari;
    $jamiKamTolangan  += $kamTolangan;
@endphp
<tr class='{{ $rowClass }}'>
  <td>{{ $g->oylik_tartib }}</td>
  <td>{{ $g->tolov_sana?->format('d.m.Y') }}</td>
  <td>{{ number_format($g->tolov_summa,0,'.',' ') }}</td>
  <td>{{ $g->qoldiq_suma !== null ? number_format($g->qoldiq_suma,0,'.',' ') : '—' }}</td>
  <td>{{ $g->tolangan_summa > 0 ? number_format($g->tolangan_summa,0,'.',' ') : '—' }}</td>
  <td>{{ $g->tolangan_sana?->format('d.m.Y') ?? '—' }}</td>
  <td>{{ number_format($faktQoldiq,0,'.',' ') }}</td>
  @if($g->is($oxirgiFaolQator))
  <td style='{{ $farq > 0 ? "color:#b02a37" : ($farq < 0 ? "color:#1a7d36" : "") }}'>
    {{ $farq != 0 ? ($farq > 0 ? '+' : '') . number_format($farq,0,'.',' ') : '—' }}
  </td>
  @else
  <td>—</td>
  @endif
  <td style='{{ $kamTolangan > 0 ? "color:#b02a37;font-weight:bold" : "" }}'>
    {{ $kamTolangan > 0 ? number_format($kamTolangan,0,'.',' ') : '—' }}
  </td>
  <td style='{{ $g->kechikish_kunlari > 0 ? "color:#b02a37;font-weight:bold" : "" }}'>
    {{ $g->kechikish_matni }}
  </td>
</tr>
@endforeach
<tr style='font-weight:bold;background:#eef'>
  <td colspan='2'>Jami:</td>
  <td>{{ number_format($kredit->grafik->sum('tolov_summa'),0,'.',' ') }}</td>
  <td></td>
  <td>{{ number_format($kredit->grafik->sum('tolangan_summa'),0,'.',' ') }}</td>
  <td></td>
  <td>{{ number_format(max(0, $kredit->kredit_summa - $kredit->grafik->sum('tolangan_summa')),0,'.',' ') }}</td>
  <td></td>
  <td>{{ $jamiKamTolangan > 0 ? number_format($jamiKamTolangan,0,'.',' ') : '—' }}</td>
  <td>
    @php
        $jOy = intdiv($jamiKechikkanKun, 30); $jKun = $jamiKechikkanKun % 30;
    @endphp
    {{ $jamiKechikkanKun > 0 ? ($jOy > 0 ? "{$jOy} oy " : '') . ($jKun > 0 ? "{$jKun} kun" : ($jOy > 0 ? '' : '')) : '—' }}
  </td>
</tr>
</tbody>
</table>

<div class='imzo'>
<table><tr>
<td width='50%'>Tashkilot vakili: ___________________<br>M.O.</td>
<td width='50%'>Mijoz (tasdiqlayman): ___________________<br>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</td>
</tr></table>
</div>

</body></html>
