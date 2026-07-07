<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111; line-height:1.5; }
  .page { padding:14mm 15mm; }
  .top-row { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px; }
  .top-row .sana { font-size:11px; }
  .top-row .filial-manzil { font-size:11px; font-weight:bold; text-align:right; }
  h2.title { text-align:center; font-size:14px; margin-bottom:2px; text-transform:uppercase; }
  .subtitle { text-align:center; font-size:10.5px; color:#333; margin-bottom:10px; }
  h3.bolim { text-align:center; font-size:12px; margin:12px 0 6px; text-transform:uppercase; }
  p.matn { text-align:justify; margin-bottom:6px; }
  table.tovar { width:100%; border-collapse:collapse; margin:8px 0; font-size:10.5px; }
  table.tovar th { border:1px solid #333; padding:4px 5px; text-align:center; background:#f0f0f0; }
  table.tovar td { border:1px solid #333; padding:4px 5px; }
  .tovar-jami { text-align:right; font-weight:bold; margin:6px 0 10px; }
  table.shart { width:100%; border-collapse:collapse; margin:8px 0 4px; font-size:10.5px; }
  table.shart th { border:1px solid #333; padding:5px; text-align:center; background:#f0f0f0; }
  table.shart td { border:1px solid #333; padding:5px; text-align:center; }
  .summa-soz { text-align:center; font-style:italic; font-weight:bold; text-decoration:underline; margin:6px 0 10px; }
  p.band { margin-bottom:5px; text-align:justify; }
  .band-sarlavha { font-weight:bold; margin-top:8px; margin-bottom:3px; }
  table.manzil { width:100%; border-collapse:collapse; margin-top:6px; }
  table.manzil td { vertical-align:top; padding:0 8px; width:50%; }
  table.manzil-ichki { width:100%; border-collapse:collapse; font-size:10.5px; }
  table.manzil-ichki td { border:1px solid #333; padding:4px 6px; }
  table.manzil-ichki td:first-child { font-weight:bold; width:35%; background:#f7f7f7; }
  /* MUHIM: DomPDF flexbox'ni to'liq qo'llab-quvvatlamaydi — shu sabab .imzo-row
     avval display:flex bilan yozilgan bo'lib, ikkala imzo qutisi tekislanmay,
     ikkisi ham chap tomonga (Sotuvchi ustuniga) yopishib qolardi. Shuning
     uchun manzil/imzo qatorlari kabi haqiqiy <table> orqali tekislanadi —
     DomPDF jadvallarni to'g'ri render qiladi. */
  table.imzo-row { width:100%; border-collapse:collapse; margin-top:14px; }
  table.imzo-row td { width:50%; padding:0 8px; }
  .imzo-kvadrat { border:1px solid #333; padding:4px 10px; text-align:center; }
  .rozilik { margin-top:18px; font-size:10.5px; }
  .page-break { page-break-before: always; }
</style>
</head>
<body>
<div class="page">

  @php
      $mijoz   = $kredit->mijoz;
      $filial  = $kredit->filial;
      $kompNomi     = \App\Models\Sozlama::ol('kompaniya_nomi', $filial?->nomi ?? '');
      $kompManzil   = $filial?->manzil ?: \App\Models\Sozlama::ol('kompaniya_manzil', '');
      $kompBank     = \App\Models\Sozlama::ol('kompaniya_bank', '');
      $kompHisob    = \App\Models\Sozlama::ol('kompaniya_hisob', '');
      $kompMfo      = \App\Models\Sozlama::ol('kompaniya_mfo', '');
      $kompStir     = \App\Models\Sozlama::ol('kompaniya_inn', '');
      $kompTelefon  = $filial?->telefon ?: \App\Models\Sozlama::ol('kompaniya_telefon', '');
      $kompDirektor = \App\Models\Sozlama::ol('kompaniya_direktor', '');

      // Eski "manzil" matnida tuman nomi allaqachon yozilgan bo'lishi mumkin — takrorlanmasligi uchun tekshiramiz
      $tumanNomi  = $mijoz?->tuman?->nomi ?? '';
      $tumanCore  = trim(preg_replace('/\s*(тумани|шахри|шахар)\s*$/iu', '', $tumanNomi));
      $manzilMatn = $mijoz?->manzil ?? '';
      $tumanAllaqachonBor = $tumanCore !== '' && mb_stripos($manzilMatn, $tumanCore) !== false;

      $mijozManzil = trim(
          ($mijoz?->viloyat?->nomi ?? '') . ' ' .
          (!$tumanAllaqachonBor ? $tumanNomi . ' ' : '') .
          $manzilMatn
      );
      $mijozIshJoyi = trim(($mijoz?->ish_joyi ?? '') . ' ' . ($mijoz?->lavozimi ?? '') . ' ' . ($mijoz?->pinfl ?? ''));
  @endphp

  <div class="top-row">
    <div class="sana">{{ $kredit->boshlanish_sana?->format('d.m.Y') }}й.</div>
    <div class="filial-manzil">{{ $filial?->nomi }} {{ $kompManzil }}</div>
  </div>

  <h2 class="title">ШАРТНОМА № {{ $kredit->shartnoma_raqam }}</h2>
  <p class="subtitle">(Муддатли тўлов шарти билан олди-сотди шартномаси)</p>

  <h3 class="bolim">1. ШАРТНОМА МАҚСАДИ</h3>
  <p class="matn">
    Бир тарафдан ўз Гувоҳномаси асосида фаолият кўрсатувчи <strong>{{ $kompNomi }}</strong>
    (кейинги ўринларда «Сотувчи») ва
    <strong>{{ $mijozManzil }}</strong> да яшовчи
    <strong>{{ $mijoz?->familiya }} {{ $mijoz?->ism }} {{ $mijoz?->otasining_ismi }}</strong>
    (Паспорт серия {{ $mijoz?->passport_seriya }} {{ $mijoz?->passport_raqam }}
    {{ $mijoz?->passport_berilgan_joy }} томонидан берилган)
    {{ $mijozIshJoyi }}
    (кейинги ўринларда «Харидор») иккинчи томондан ушбу шартномани тарафлар ўртасида ўзаро
    келишув асосида қуйидагилар тўғрисида тузилди.
  </p>
  <p class="matn">
    1.1. «Сотувчи» қуйидаги маҳсулотларни «Харидор»га маҳсулотни ўзаро келишув асосида
    {{ $kredit->muddati_oy }} ой (тўлов жадвалида кўрсатилган) муддат давомида қийматини бўлиб
    тўлаш шарти билан сотади, ушбу маҳсулотлар тўлов шартлари тўлиқ бажарилиб, тўловлар тўлиқ
    тўлангандан сўнг «Харидор»нинг шахсий мулкига айланади.
  </p>

  <table class="tovar">
    <thead>
      <tr><th>№</th><th>Маҳсулот номи</th><th>Ўлчов бир.</th><th>Миқдори</th><th>Нархи</th><th>Суммаси</th></tr>
    </thead>
    <tbody>
      @foreach($kredit->tovarlar->where('turi', 'kredit') as $i => $t)
      <tr>
        <td align="center">{{ $i + 1 }}</td>
        <td>{{ $t->nomi }}</td>
        <td align="center">{{ $t->birlik ?? '' }}</td>
        <td align="center">{{ $t->soni }}</td>
        <td align="right">{{ number_format($t->narx, 0, '.', ' ') }}</td>
        <td align="right">{{ number_format($t->jami_narx, 0, '.', ' ') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <p class="tovar-jami">Жами маҳсулот суммаси&nbsp;&nbsp;{{ number_format($kredit->jami_summa, 0, '.', ' ') }}</p>

  <h3 class="bolim">2. ШАРТНОМА ҚИЙМАТИ ВА ТЎЛОВ ШАРТЛАРИ</h3>
  <p class="matn">2.1. Мазкур шартнома суммаси қуйидаги жадвал бўйича аниқланди;</p>
  <p class="summa-soz">
    {{ (int) $kredit->jami_summa }} ({{ \App\Models\RegKredit::summaSozda($kredit->jami_summa) }}) ташкил қилади.
  </p>
  <p class="matn">
    2.2. Сотиб олинаётган маҳсулотнинг қолган суммасини «Харидор» ўз ойлик иш ҳақисидан ёки бошқа
    даромадлари ҳисобидан ушбу шартномага илова қилинган (тўлов жадвали) бўйича қолган тўлиқ
    тўлангунига қадар тўлаб боради.
  </p>

  <table class="shart">
    <thead>
      <tr><th>Товар суммаси</th><th>Ойлик тўлови</th><th>Олдиндан тўлови</th><th>Қолдиқ қарз суммаси</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>{{ number_format($kredit->jami_summa, 2, '.', ' ') }}</td>
        <td>{{ number_format($kredit->oylik_tolov_miqdori, 2, '.', ' ') }}</td>
        <td>{{ number_format($kredit->boshlangich_tolov, 2, '.', ' ') }}</td>
        <td>{{ number_format($kredit->kredit_summa, 2, '.', ' ') }}</td>
      </tr>
    </tbody>
  </table>

  {!! \App\Models\HujjatBand::asosiyMatn('shartnoma') !!}
  @if($kredit->shartnoma_qoshimcha_band)
  <p class="band"><strong>6.5.Қўшимча шартлар:</strong></p>
  <div class="band" style="white-space:pre-wrap;">{{ $kredit->shartnoma_qoshimcha_band }}</div>
  @endif

  <h3 class="bolim">7. ТОМОНЛАРНИНГ МАНЗИЛЛАРИ</h3>
  <table class="manzil">
    <tr>
      <td>
        <p style="text-align:center;font-weight:bold;margin-bottom:4px">"Сотувчи":</p>
        <table class="manzil-ichki">
          <tr><td>Номи:</td><td>{{ $kompNomi }}</td></tr>
          <tr><td>Манзили:</td><td>{{ $kompManzil }}</td></tr>
          <tr><td>Банк номи:</td><td>{{ $kompBank }}</td></tr>
          <tr><td>Ҳисоб/р:</td><td>{{ $kompHisob }}</td></tr>
          <tr><td>МФО:</td><td>{{ $kompMfo }}</td></tr>
          <tr><td>СТИР:</td><td>{{ $kompStir }}</td></tr>
          <tr><td>Телефони:</td><td>{{ $kompTelefon }}</td></tr>
          <tr><td>Раҳбари:</td><td>{{ $kompDirektor }}</td></tr>
        </table>
      </td>
      <td>
        <p style="text-align:center;font-weight:bold;margin-bottom:4px">"Харидор":</p>
        <table class="manzil-ichki">
          <tr><td colspan="2"><strong>{{ $mijoz?->familiya }} {{ $mijoz?->ism }} {{ $mijoz?->otasining_ismi }}</strong></td></tr>
          <tr><td>Паспорт:</td><td>{{ $mijoz?->passport_seriya }} {{ $mijoz?->passport_raqam }}</td></tr>
          <tr><td>Берилган:</td><td>{{ $mijoz?->passport_berilgan_joy }}</td></tr>
          <tr><td>Манзили:</td><td>{{ $mijozManzil }}</td></tr>
          <tr><td>Тел:</td><td>{{ $mijoz?->telefon }}</td></tr>
        </table>
      </td>
    </tr>
  </table>

  <table class="imzo-row">
    <tr>
      <td><div class="imzo-kvadrat">Сотувчи имзоси</div></td>
      <td><div class="imzo-kvadrat">Харидор имзоси</div></td>
    </tr>
  </table>

  <p class="rozilik">Шартнома шартлари билан танишиб чиқдим. Ушбу шартнома шартларига розиман_________________</p>

</div>
</body>
</html>
