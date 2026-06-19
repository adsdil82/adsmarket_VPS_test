<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111; }
  .page { padding:20mm 20mm; }
  .top-right { text-align:right; margin-bottom:20px; line-height:1.7; }
  h2 { text-align:center; font-size:13px; font-weight:bold; text-transform:uppercase; margin:20px 0 6px; }
  .subtitle { text-align:center; font-size:11px; margin-bottom:20px; }
  .body-text { line-height:2.0; font-size:11px; margin-bottom:16px; text-align:justify; }
  table.tovar { width:100%; border-collapse:collapse; margin:14px 0; font-size:10px; }
  table.tovar th { background:#333; color:#fff; padding:5px; text-align:center; }
  table.tovar td { padding:4px 6px; border:1px solid #ccc; }
  table.tovar tr:nth-child(even) td { background:#f9f9f9; }
  .total-row td { font-weight:bold; background:#e8f5e9; }
  .imzo { margin-top:30px; }
  .imzo table { width:100%; }
  .imzo td { vertical-align:top; padding:6px; }
</style>
</head>
<body>
<div class="page">

  <div class="top-right">
    <strong>{{ $kredit->filial?->nomi }}</strong><br>
    Rahbariyatiga<br>
    <strong>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }} {{ $kredit->mijoz?->otasining_ismi }}</strong> dan
  </div>

  <h2>ARIZA</h2>
  <p class="subtitle">(Tovar nasiya olish to'g'risida)</p>

  <div class="body-text">
    <p>Hurmatli rahbar,</p><br>
    <p>
      Men, <strong>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }} {{ $kredit->mijoz?->otasining_ismi }}</strong>
      (passport: {{ $kredit->mijoz?->passport_seriya }} {{ $kredit->mijoz?->passport_raqam }}),
      {{ $kredit->filial?->nomi }} dan quyidagi tovarlarni nasiya sharti bilan olishimga ruxsat so'rayman.
    </p><br>
    <p>
      Nasiya muddati: <strong>{{ $kredit->muddati_oy }} oy</strong> ({{ $kredit->boshlanish_sana?->format('d.m.Y') }} dan
      {{ $kredit->tugash_sana?->format('d.m.Y') }} gacha).
    </p><br>
    <p>
      Boshlang'ich to'lov: <strong>{{ number_format($kredit->boshlangich_tolov, 0, '.', ' ') }} so'm</strong> to'lanadi.
      Qolgan nasiya summasi {{ number_format($kredit->kredit_summa, 0, '.', ' ') }} so'm oylik
      {{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }} so'm dan to'lab boriladi.
    </p>
  </div>

  <table class="tovar">
    <thead>
      <tr><th>#</th><th>Tovar nomi</th><th>Soni</th><th>Narxi</th><th>Jami</th></tr>
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

  <div class="body-text">
    <p>
      Ushbu ariza asosida zarur shartnoma tuzilib, tovar berilishini so'rayman.
      Barcha shartnoma shartlariga rioya qilishga va to'lovlarni o'z vaqtida amalga oshirishga
      o'zimni majburlayman.
    </p>
  </div>

  <div class="imzo">
    <table>
      <tr>
        <td width="60%">
          Sana: {{ $kredit->boshlanish_sana?->format('d.m.Y') }}
        </td>
        <td width="40%" style="text-align:right">
          Arizachi imzosi: ___________________<br><br>
          <small>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }}</small>
        </td>
      </tr>
    </table>
  </div>

</div>
</body>
</html>
