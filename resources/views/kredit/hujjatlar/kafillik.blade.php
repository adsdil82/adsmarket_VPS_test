<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#111; line-height:1.5; }
  .page { padding:14mm 15mm; }
  .top-row { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px; }
  h2.title { text-align:center; font-size:14px; margin-bottom:10px; text-transform:uppercase; }
  h3.bolim { text-align:center; font-size:12px; margin:12px 0 6px; text-transform:uppercase; }
  p.matn { text-align:justify; margin-bottom:6px; }
  p.band { margin-bottom:5px; text-align:justify; }
  table.manzil { width:100%; border-collapse:collapse; margin-top:6px; }
  table.manzil td { vertical-align:top; padding:0 6px; width:33.33%; }
  table.manzil-ichki { width:100%; border-collapse:collapse; font-size:10px; }
  table.manzil-ichki td { border:1px solid #333; padding:4px 5px; }
  table.manzil-ichki td:first-child { font-weight:bold; width:38%; background:#f7f7f7; }
  .shaxs-box { border:1px solid #333; padding:6px 7px; font-size:10px; min-height:90px; }
  .imzo-row { display:flex; justify-content:space-between; margin-top:14px; }
  .imzo-kvadrat { border:1px solid #333; padding:4px 8px; display:inline-block; font-size:10.5px; }
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

      // Eski "manzil" matnida tuman nomi allaqachon yozilgan bo'lishi mumkin — takrorlanmasligi uchun
      $manzilQurish = function (?\App\Models\Mijoz $m) {
          if (!$m) return '';
          $tumanNomi = $m->tuman?->nomi ?? '';
          $tumanCore = trim(preg_replace('/\s*(тумани|шахри|шахар)\s*$/iu', '', $tumanNomi));
          $manzilMatn = $m->manzil ?? '';
          $bor = $tumanCore !== '' && mb_stripos($manzilMatn, $tumanCore) !== false;
          return trim(($m->viloyat?->nomi ?? '') . ' ' . (!$bor ? $tumanNomi . ' ' : '') . $manzilMatn);
      };

      $mijozManzil = $manzilQurish($mijoz);

      // Kafil — ro'yxatdagi mijoz yoki erkin matn ko'rinishida bo'lishi mumkin
      $kafilMijoz = $kredit->kafil;
      if ($kafilMijoz) {
          $kafilFio        = trim($kafilMijoz->familiya . ' ' . $kafilMijoz->ism . ' ' . $kafilMijoz->otasining_ismi);
          $kafilPassport   = trim(($kafilMijoz->passport_seriya ?? '') . ' ' . ($kafilMijoz->passport_raqam ?? ''));
          $kafilPassportJoy = $kafilMijoz->passport_berilgan_joy ?? '';
          $kafilManzil     = $manzilQurish($kafilMijoz);
          $kafilTelefon    = $kafilMijoz->telefon ?? '';
      } else {
          $kafilFio        = $kredit->kafil_ism ?? '';
          $kafilPassport   = '';
          $kafilPassportJoy = '';
          $kafilManzil     = $kredit->kafil_manzil ?? '';
          $kafilTelefon    = $kredit->kafil_telefon ?? '';
      }
  @endphp

  <div class="top-row">
    <div class="sana">{{ $kredit->boshlanish_sana?->format('d.m.Y') }}й.</div>
    <div class="filial-manzil" style="font-weight:bold">{{ $filial?->nomi }} {{ $kompManzil }}</div>
  </div>

  <h2 class="title">КАФИЛЛИК ШАРТНОМАСИ № {{ $kredit->shartnoma_raqam }}</h2>

  <p class="matn">
    <strong>{{ $kompNomi }}</strong> (кейинги ўринларда «Сотувчи») бир томондан, фуқаро
    <strong>{{ $mijoz?->familiya }} {{ $mijoz?->ism }} {{ $mijoz?->otasining_ismi }}</strong>
    (Паспорт серия {{ $mijoz?->passport_seriya }} {{ $mijoz?->passport_raqam }}
    {{ $mijoz?->passport_berilgan_joy }} томонидан берилган) {{ $mijozManzil }}да яшовчи фуқаро
    (кейинги ўринларда «Харидор») иккинчи томондан ва
    <strong>{{ $kafilFio }}</strong>
    @if($kafilPassport)
    (Паспорт серия {{ $kafilPassport }} {{ $kafilPassportJoy }} томонидан берилган)
    @endif
    {{ $kafilManzil }}да яшовчи фуқаро (кейинги ўринларда «Кафил») учинчи томондан ушбу
    шартномани тарафлар ўртасида ўзаро келишув асосида қуйидагилар тўғрисида тузилди:
  </p>

  <p class="matn">
    1.2. Ушбу кафиллик шартномаси {{ $kredit->boshlanish_sana?->format('d.m.Y') }} йил кунги
    №{{ $kredit->shartnoma_raqam }}-сонли Муддатли тўлов шарти билан олди-сотди шартномасининг
    ажралмас қисми ҳисобланиб, (кейинги ўринларда Олди-сотди шартнома)сига асосан «Сотувчи»
    «Харидор»га {{ (int) $kredit->jami_summa }} ({{ \App\Models\RegKredit::summaSozda($kredit->jami_summa) }})
    миқдоридаги маҳсулотни шартнома тузилган кундан бошлаб {{ $kredit->muddati_oy }} ой муддатга
    муддатли тўлов шарти билан беради. Ушбу муддатли тўлов шарти билан олди-сотди шартномасига
    асосан «Кафил» «Сотиб олувчи»нинг «Сотувчи» олдидаги мажбуриятларидан тўлиқ огоҳ этилган.
  </p>

  <h3 class="bolim">1. ШАРТНОМА МАҚСАДИ</h3>
  <p class="band">
    1.1. Ушбу шартномага мувофиқ, «Кафил», «Сотувчи» ва «Харидор» ўртасида тузилган «Муддатли
    тўлов шарти билан олди-сотди шартномаси» шартлари бўйича «Харидор»нинг барча мажбуриятлари,
    яъни ҳозирда мавжуд бўлган ва кейинчалик вужудга келиши мумкин бўлган мажбуриятларнинг
    бажарилиши юзасидан «Сотувчи» олдида жавоб бериш мажбурияти ўз зиммасига олади.
  </p>

  <h3 class="bolim">2. КАФИЛЛИК БИЛАН ТАЪМИНЛАНГАН МАЖБУРИЯТ</h3>
  <p class="band">
    2.1.Ушбу шартнома бўйича кафиллик билан таъминланган мажбурият асоси бўлиб, «Сотувчи» ва
    «Харидор» ўртасида тузилган {{ $kredit->boshlanish_sana?->format('d.m.Y') }} йил кунги
    №{{ $kredit->shartnoma_raqam }}-сонли Муддатли тўлов шарти билан олди-сотди шартномаси
    ҳисобланади.
  </p>

  {!! \App\Models\HujjatBand::asosiyMatn('kafillik') !!}
  @if($kredit->kafillik_qoshimcha_band)
  <p class="band"><strong>6.5.Қўшимча шартлар:</strong></p>
  <div class="band" style="white-space:pre-wrap;">{{ $kredit->kafillik_qoshimcha_band }}</div>
  @endif

  <h3 class="bolim">ТОМОНЛАРНИНГ МАНЗИЛЛАРИ</h3>
  <table class="manzil">
    <tr>
      <td>
        <p style="text-align:center;font-weight:bold;margin-bottom:4px">"Сотувчи":</p>
        <table class="manzil-ichki">
          <tr><td>Номи:</td><td>{{ $kompNomi }}</td></tr>
          <tr><td>Манзили:</td><td>{{ $kompManzil }}</td></tr>
          <tr><td>Банк:</td><td>{{ $kompBank }}</td></tr>
          <tr><td>х/р:</td><td>{{ $kompHisob }}</td></tr>
          <tr><td>МФО:</td><td>{{ $kompMfo }}</td></tr>
          <tr><td>ИНН:</td><td>{{ $kompStir }}</td></tr>
          <tr><td>Тел:</td><td>{{ $kompTelefon }}</td></tr>
          <tr><td>Раҳбар:</td><td>{{ $kompDirektor }}</td></tr>
        </table>
        <div class="imzo-kvadrat" style="margin-top:8px">Сотувчи имзоси</div>
      </td>
      <td>
        <p style="text-align:center;font-weight:bold;margin-bottom:4px">"Кафил":</p>
        <div class="shaxs-box">
          <strong>{{ $kafilFio }}</strong><br>
          @if($kafilPassport)
          Паспорт серия {{ $kafilPassport }}<br>
          {{ $kafilPassportJoy }} томонидан берилган.<br>
          @endif
          {{ $kafilManzil }}да яшовчи фуқаро.<br>
          Телефон: {{ $kafilTelefon }}
        </div>
        <div class="imzo-kvadrat" style="margin-top:8px">Кафил имзоси</div>
      </td>
      <td>
        <p style="text-align:center;font-weight:bold;margin-bottom:4px">"Харидор":</p>
        <div class="shaxs-box">
          <strong>{{ $mijoz?->familiya }} {{ $mijoz?->ism }} {{ $mijoz?->otasining_ismi }}</strong><br>
          Паспорт серия {{ $mijoz?->passport_seriya }} {{ $mijoz?->passport_raqam }}<br>
          {{ $mijoz?->passport_berilgan_joy }} томонидан берилган.<br>
          {{ $mijozManzil }}да яшовчи фуқаро.<br>
          Телефон: {{ $mijoz?->telefon }}
        </div>
        <div class="imzo-kvadrat" style="margin-top:8px">Харидор имзоси</div>
      </td>
    </tr>
  </table>

</div>
</body>
</html>
