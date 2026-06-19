<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  body        { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12pt; color: #000; margin: 0; padding: 0; }
  .page       { padding: 20mm 25mm 20mm 25mm; min-height: 257mm; }
  .header     { border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 20px; }
  .org-name   { font-size: 14pt; font-weight: bold; }
  .org-sub    { font-size: 9pt; color: #555; }
  .title      { text-align: center; font-size: 13pt; font-weight: bold; margin: 20px 0 15px; text-transform: uppercase; }
  .body       { line-height: 1.8; white-space: pre-wrap; font-size: 11pt; }
  .footer     { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 9pt; color: #555; }
  .sign-block { margin-top: 30px; }
</style>
</head>
<body>
<div class="page">
  <div class="header">
    <div class="org-name">{{ $vars['tashkilot_nomi'] }}</div>
    <div class="org-sub">Pochta xati &bull; {{ $vars['yuborish_sana'] }}</div>
  </div>

  <div class="title">OGOHLANTIRISH XATI</div>

  <div class="body">{{ $matn }}</div>

  <div class="sign-block">
    <p>Hurmat bilan,</p>
    <p><strong>{{ $vars['tashkilot_nomi'] }}</strong> ma'muriyati</p>
    <p>Sana: {{ $vars['yuborish_sana'] }}</p>
  </div>

  <div class="footer">
    Shartnoma: {{ $vars['shartnoma_raqam'] }} &bull; Mijoz: {{ $vars['mijoz_fio'] }}
  </div>
</div>
</body>
</html>
