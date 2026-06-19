<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111; }
  .page { padding:20mm 20mm; }
  h2 { text-align:center; font-size:13px; font-weight:bold; text-transform:uppercase; margin-bottom:6px; }
  .subtitle { text-align:center; font-size:11px; color:#555; margin-bottom:24px; }
  .body-text { line-height:2.0; font-size:11px; margin-bottom:14px; text-align:justify; }
  table.info { width:100%; border-collapse:collapse; margin:12px 0; }
  table.info td { padding:4px 6px; border:1px solid #ccc; }
  table.info td:first-child { background:#f5f5f5; font-weight:bold; width:40%; }
  .bold { font-weight:bold; }
  .imzo { margin-top:40px; }
  .imzo table { width:100%; }
  .imzo td { vertical-align:top; padding:6px; }
  .imzo-line { border-top:1px solid #333; margin-top:40px; font-size:10px; color:#555; text-align:center; }
</style>
</head>
<body>
<div class="page">

  <h2>TIL XAT</h2>
  <p class="subtitle">(O'z vaqtida to'lash majburiyati to'g'risida)</p>

  <div class="body-text">
    <p>
      Men, quyida imzo qo'yuvchi <strong>{{ $kredit->mijoz?->familiya }} {{ $kredit->mijoz?->ism }} {{ $kredit->mijoz?->otasining_ismi }}</strong>,
      passport seriyasi <strong>{{ $kredit->mijoz?->passport_seriya }} {{ $kredit->mijoz?->passport_raqam }}</strong>,
      yashash manzili: <strong>{{ $kredit->mijoz?->manzil ?? '___________________' }}</strong>,
      telefon: <strong>{{ $kredit->mijoz?->telefon }}</strong>,
    </p><br>
    <p>
      {{ $kredit->filial?->nomi }} bilan tuzilgan <strong>№ {{ $kredit->shartnoma_raqam }}</strong> sonli
      nasiya shartnomasi bo'yicha ushbu til xatni beraman.
    </p>
  </div>

  <table class="info">
    <tr><td>Shartnoma raqami</td><td><strong>{{ $kredit->shartnoma_raqam }}</strong></td></tr>
    <tr><td>Shartnoma sanasi</td><td>{{ $kredit->boshlanish_sana?->format('d.m.Y') }}</td></tr>
    <tr><td>Jami nasiya summasi</td><td><strong>{{ number_format($kredit->kredit_summa, 0, '.', ' ') }} so'm</strong></td></tr>
    <tr><td>Oylik to'lov miqdori</td><td><strong>{{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }} so'm</strong></td></tr>
    <tr><td>To'lov muddati</td><td>{{ $kredit->muddati_oy }} oy ({{ $kredit->tugash_sana?->format('d.m.Y') }} gacha)</td></tr>
    <tr><td>Har oyning to'lov kuni</td><td>{{ $kredit->tolov_kuni ?? 5 }}-si</td></tr>
  </table>

  <div class="body-text">
    <p>
      1. Har oyning <strong>{{ $kredit->tolov_kuni ?? 5 }}-</strong> sanasiga qadar
      <strong>{{ number_format($kredit->oylik_tolov_miqdori, 0, '.', ' ') }} so'm</strong>
      miqdorida oylik to'lovni amalga oshirishga <strong>majburlanaman</strong>.
    </p><br>
    <p>
      2. To'lov kechiktirilsa, shartnomada belgilangan miqdorda <strong>jarimaga rozilik</strong> beraman.
    </p><br>
    <p>
      3. Barcha to'lovlar {{ $kredit->tugash_sana?->format('d.m.Y') }} sanasiga qadar
      to'liq amalga oshirilishini ta'minlayman.
    </p><br>
    <p>
      4. Ushbu til xat shartnoma bilan birga yuridik kuchga ega bo'lib, ikki nusxada tuzilgan.
    </p>
  </div>

  <div class="imzo">
    <table>
      <tr>
        <td width="50%">
          <strong>Tashkilot vakili:</strong><br><br>
          {{ $kredit->filial?->nomi }}<br>
          Imzo: ___________________<br>
          M.O. &nbsp;&nbsp;&nbsp;
          <span style="display:inline-block;width:60px;height:60px;border:2px dashed #999;text-align:center;font-size:9px;color:#aaa;padding-top:20px">M.O.</span>
        </td>
        <td width="50%">
          <strong>Majburiyat beruvchi:</strong><br><br>
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
