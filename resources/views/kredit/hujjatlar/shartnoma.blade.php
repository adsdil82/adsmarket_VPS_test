<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111; }
  .page { padding:20mm 15mm; }
  h2 { text-align:center; font-size:14px; margin-bottom:4px; text-transform:uppercase; }
  .subtitle { text-align:center; font-size:11px; color:#555; margin-bottom:16px; }
  table.info { width:100%; border-collapse:collapse; margin-bottom:12px; }
  table.info td { padding:4px 6px; border:1px solid #ccc; }
  table.info td:first-child { background:#f5f5f5; font-weight:bold; width:40%; }
  table.tovar { width:100%; border-collapse:collapse; margin:12px 0; font-size:10px; }
  table.tovar th { background:#222; color:#fff; padding:5px 6px; text-align:center; }
  table.tovar td { padding:4px 6px; border:1px solid #ddd; }
  table.tovar tr:nth-child(even) td { background:#f9f9f9; }
  .total-row td { font-weight:bold; background:#e8f5e9; }
  .section { margin:14px 0; }
  .section h4 { font-size:11px; font-weight:bold; border-bottom:1px solid #999; padding-bottom:3px; margin-bottom:8px; }
  .imzo { margin-top:30px; }
  .imzo table { width:100%; }
  .imzo td { vertical-align:top; padding:6px; }
  .imzo-line { border-top:1px solid #333; margin-top:40px; font-size:10px; color:#555; text-align:center; }
  .stamp { width:80px; height:80px; border:2px dashed #999; display:inline-block; text-align:center; font-size:9px; color:#aaa; padding-top:30px; }
  @media print { .no-print { display:none; } }
</style>
</head>
<body>
<div class="page">

  <h2>NASIYA SHARTNOMASI</h2>
  <p class="subtitle">№ {{ $kredit->shartnoma_raqam }}</p>

  <div class="section">
    <table class="info">
      <tr><td>Shartnoma sanasi</td><td>{{ $kredit->boshlanish_sana?->format('d.m.Y') }}</td></tr>
      <tr><td>Tugash sanasi</td><td>{{ $kredit->tugash_sana?->format('d.m.Y') }}</td></tr>
      <tr><td>Mijoz F.I.O.</td><td><strong>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }} {{ $kredit->mijoz?->otasining_ismi }}</strong></td></tr>
      <tr><td>Telefon</td><td>{{ $kredit->mijoz?->telefon }}</td></tr>
      <tr><td>Passport</td><td>{{ $kredit->mijoz?->passport_seriya }} {{ $kredit->mijoz?->passport_raqam }}</td></tr>
      <tr><td>Filial</td><td>{{ $kredit->filial?->nomi }}</td></tr>
    </table>
  </div>

  <div class="section">
    <h4>Moliyaviy shartlar</h4>
    <table class="info">
      <tr><td>Jami summa</td><td><strong>{{ number_format($kredit->jami_summa, 0, '.', ' ') }} so'm</strong></td></tr>
      <tr><td>Boshlang'ich to'lov</td><td>{{ number_format($kredit->boshlangich_tolov, 0, '.', ' ') }} so'm</td></tr>
      <tr><td>Nasiya summasi</td><td><strong>{{ number_format($kredit->kredit_summa, 0, '.', ' ') }} so'm</strong></td></tr>
      <tr><td>Muddat</td><td>{{ $kredit->muddati_oy }} oy</td></tr>
      <tr><td>Foiz stavkasi</td><td>{{ $kredit->foiz_stavka > 0 ? $kredit->foiz_stavka.'%' : 'Foizsiz' }}</td></tr>
      <tr><td>Oylik to'lov</td><td><strong>{{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }} so'm</strong></td></tr>
    </table>
  </div>

  <div class="section">
    <h4>Tovarlar ro'yxati</h4>
    <table class="tovar">
      <thead>
        <tr><th>#</th><th>Tovar nomi</th><th>Soni</th><th>Narxi (so'm)</th><th>Jami (so'm)</th></tr>
      </thead>
      <tbody>
        @foreach($kredit->tovarlar as $i=>$t)
        <tr>
          <td align="center">{{ $i+1 }}</td>
          <td>{{ $t->nomi }}</td>
          <td align="center">{{ $t->soni }}</td>
          <td align="right">{{ number_format($t->narx, 0, '.', ' ') }}</td>
          <td align="right">{{ number_format($t->jami_narx, 0, '.', ' ') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
          <td colspan="4" align="right">Jami:</td>
          <td align="right">{{ number_format($kredit->jami_summa, 0, '.', ' ') }} so'm</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <h4>Shartnoma shartlari</h4>
    <p style="line-height:1.8">
      1. Mijoz yuqorida ko'rsatilgan tovarlarni nasiyaga olishini tasdiqlaydi.<br>
      2. Nasiya summasi {{ number_format($kredit->kredit_summa, 0, '.', ' ') }} so'm bo'lib, {{ $kredit->muddati_oy }} oy muddatida to'lanadi.<br>
      3. Oylik to'lov miqdori: {{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }} so'm.<br>
      4. To'lov kechiktirilgan taqdirda qo'shimcha jarima qo'llaniladi.<br>
      5. Ushbu shartnoma ikki nusxada tuzildi.
    </p>
  </div>

  <div class="imzo">
    <table>
      <tr>
        <td width="50%">
          <strong>Tashkilot:</strong><br><br>
          {{ $kredit->filial?->nomi }}<br>
          Imzo: ___________________<br>
          M.O. <span class="stamp">M.O.</span>
        </td>
        <td width="50%">
          <strong>Mijoz:</strong><br><br>
          {{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}<br>
          Imzo: ___________________<br><br>
          Sana: ___________________
        </td>
      </tr>
    </table>
  </div>

</div>
</body>
</html>
