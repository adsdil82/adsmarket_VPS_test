<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  /* DIQQAT: Hybrid Pochta 1-varaqdan ortiq xat uchun qo'shimcha to'lov oladi —
     shu sabab bu shablon ATAYLAB ixcham qilingan (kam margin, kichikroq
     line-height). HybridPochtaService::generatePdfBase64() PDF chiqqandan
     keyin haqiqiy sahifa sonini DomPDF'dan so'rab tekshiradi va 1 tadan
     ko'p bo'lsa xat yaratilishini bloklaydi. */
  body        { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11pt; color: #000; margin: 0; padding: 0; }
  .page       { padding: 15mm 20mm; }
  .header     { border-bottom: 2px solid #333; padding-bottom: 6px; margin-bottom: 14px; }
  .org-name   { font-size: 13pt; font-weight: bold; }
  .org-sub    { font-size: 8.5pt; color: #555; }
  .title      { text-align: center; font-size: 12.5pt; font-weight: bold; margin: 14px 0 10px; text-transform: uppercase; }
  .body       { line-height: 1.4; white-space: pre-wrap; font-size: 10.5pt; }
  .footer     { margin-top: 24px; border-top: 1px solid #ccc; padding-top: 8px; font-size: 8.5pt; color: #555; }
  .sign-block { margin-top: 18px; }
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
