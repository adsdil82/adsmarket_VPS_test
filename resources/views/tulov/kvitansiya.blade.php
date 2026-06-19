<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kvitansiya #{{ $tulov->id }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'Times New Roman', Times, serif; font-size:10pt; color:#000; background:#fff; }

  /* === NAMUNA suv belgisi === */
  .namuna-bg {
    position:fixed; top:0; left:0; width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    pointer-events:none; z-index:0;
  }
  .namuna-bg span {
    font-size:96pt; font-weight:900; color:#e00;
    opacity:0.12; transform:rotate(-35deg);
    white-space:nowrap; letter-spacing:8px;
    font-family: Arial, sans-serif;
  }

  /* === ASOSIY TUZILMA === */
  .varaq {
    position:relative; z-index:1;
    width:100%; display:flex; flex-direction:row; min-height:130mm;
  }

  /* === KASSIR (60%) === */
  .yarmi-kassir {
    width:60%;
    padding:5mm 5mm 5mm 15mm;   /* chap 15mm — tiklash uchun otstup */
    border-right:2px dashed #666;
  }

  /* === MIJOZ (40%) === */
  .yarmi-talon {
    width:40%;
    padding:5mm 4mm 5mm 6mm;
  }

  /* --- Sarlavhalar --- */
  .tashkilot { text-align:center; font-size:9pt; font-weight:bold; line-height:1.3; margin-bottom:1.5mm; }
  .tashkilot-kichik { font-size:7.5pt; font-weight:normal; }
  .nom { text-align:center; font-size:12pt; font-weight:bold; margin-bottom:1mm; }
  .raqam { text-align:center; font-size:8.5pt; margin-bottom:2.5mm; }

  /* --- Info jadval --- */
  table.info { width:100%; border-collapse:collapse; margin-bottom:2.5mm; font-size:9pt; }
  table.info td { padding:1mm 1.5mm; vertical-align:top; }
  table.info td:first-child { width:36%; font-weight:bold; white-space:nowrap; }
  table.info td:last-child { border-bottom:0.5pt solid #bbb; }

  /* --- SUMMA QUTISI 1: to'langan (ushbu to'lov) --- */
  .summa-box-1 {
    border:1.5pt solid #000; padding:2.5mm 3mm; margin-bottom:2mm; text-align:center;
  }
  .summa-box-1 .s-label { font-size:7.5pt; color:#555; margin-bottom:1mm; }
  .summa-box-1 .s-big   { font-size:18pt; font-weight:bold; line-height:1.1; }
  .summa-box-1 .s-som   { font-size:8.5pt; color:#444; margin-bottom:1mm; }
  .summa-box-1 .s-words { font-size:8pt; font-style:italic; color:#333;
                           border-top:0.5pt solid #ccc; padding-top:1.5mm; margin-top:1mm; }

  /* --- SUMMA QUTISI 2: jami hisoblar --- */
  .summa-box-2 {
    border:1.5pt solid #000; padding:2mm 3mm; margin-bottom:2.5mm;
  }
  .summa-box-2 .r { display:flex; justify-content:space-between; align-items:baseline;
                     padding:1mm 0; border-bottom:0.3pt solid #ddd; font-size:8.5pt; }
  .summa-box-2 .r:last-child { border-bottom:none; }
  .summa-box-2 .r .lbl { color:#444; }
  .summa-box-2 .r .val { font-weight:bold; }
  .summa-box-2 .r.total .lbl { font-weight:bold; }
  .summa-box-2 .r.total .val { font-size:10pt; }

  /* --- Imzolar --- */
  .imzolar { margin-top:2mm; }
  .imzo-row { display:flex; justify-content:space-between; margin-bottom:3.5mm; font-size:8.5pt; }
  .imzo-item { flex:1; margin-right:3mm; }
  .imzo-item:last-child { margin-right:0; }
  .imzo-label { margin-bottom:1mm; }
  .imzo-chiziq { border-bottom:0.5pt solid #000; height:6mm; margin-bottom:0.5mm; }
  .imzo-fio { font-size:7pt; color:#555; text-align:center; }

  /* --- Muhr --- */
  .muhr-wrap { display:flex; align-items:center; gap:3mm; margin-top:1mm; }
  .muhr { width:20mm; height:20mm; border:1.5pt solid #aaa; border-radius:50%;
          display:inline-flex; align-items:center; justify-content:center;
          font-size:6.5pt; color:#888; text-align:center; flex-shrink:0; }

  /* --- Footer --- */
  .nusxa-belgi { margin-top:2mm; text-align:center; font-size:7pt; color:#666;
                  font-style:italic; border-top:0.5pt solid #ccc; padding-top:1.5mm; }

  /* === TALON sarlavha === */
  .talon-nom { text-align:center; font-size:10pt; font-weight:bold; margin-bottom:1mm;
               border:1.5pt solid #000; padding:1.5mm; }
  .talon-tashkilot { text-align:center; font-size:8pt; margin-bottom:0.5mm; }
  .talon-raqam { text-align:center; font-size:8.5pt; margin-bottom:2mm; color:#333; }

  table.talon-info { width:100%; border-collapse:collapse; font-size:8pt; margin-bottom:2mm; }
  table.talon-info td { padding:1mm; vertical-align:top; }
  table.talon-info td:first-child { font-weight:bold; width:42%; }
  table.talon-info td:last-child { border-bottom:0.5pt solid #bbb; }

  /* Talon summa 1 */
  .talon-s1 { border:1.5pt solid #000; padding:2mm; margin-bottom:2mm; text-align:center; }
  .talon-s1 .ts-label { font-size:7.5pt; color:#555; }
  .talon-s1 .ts-val   { font-size:16pt; font-weight:bold; margin:0.5mm 0; line-height:1.1; }
  .talon-s1 .ts-som   { font-size:8pt; color:#555; }
  .talon-s1 .ts-words { font-size:7.5pt; font-style:italic; color:#333;
                         border-top:0.5pt solid #ccc; padding-top:1mm; margin-top:1mm; }

  /* Talon summa 2 */
  .talon-s2 { border:1.5pt solid #000; padding:2mm; margin-bottom:2mm; }
  .talon-s2 .r { display:flex; justify-content:space-between; align-items:baseline;
                  padding:0.8mm 0; border-bottom:0.3pt solid #ddd; font-size:8pt; }
  .talon-s2 .r:last-child { border-bottom:none; }
  .talon-s2 .r .lbl { color:#444; }
  .talon-s2 .r .val  { font-weight:bold; }
  .talon-s2 .r.total .lbl { font-weight:bold; }
  .talon-s2 .r.total .val  { font-size:9.5pt; }

  .talon-imzo { font-size:8pt; margin-bottom:2.5mm; }
  .talon-imzo .imzo-chiziq { border-bottom:0.5pt solid #000; height:6mm; margin-bottom:0.5mm; }
  .talon-imzo .imzo-fio { font-size:7pt; color:#555; text-align:center; }
  .talon-muhr-wrap { display:flex; align-items:center; gap:2mm; margin-bottom:2.5mm; }
  .talon-muhr { width:16mm; height:16mm; border:1.5pt solid #aaa; border-radius:50%;
                display:inline-flex; align-items:center; justify-content:center;
                font-size:6pt; color:#888; text-align:center; flex-shrink:0; }

  .talon-nusxa { text-align:center; font-size:7pt; color:#666; font-style:italic;
                  border-top:0.5pt solid #ccc; padding-top:1.5mm; margin-top:2mm; font-weight:bold; }

  @media print {
    @page { size: A4 landscape; margin: 5mm 6mm; }
    body { font-size:10pt; }
    .no-print { display:none !important; }
    .varaq { page-break-inside: avoid; }
  }
</style>
</head>
<body>

@php
  $kredit    = $tulov->kredit ?? null;
  $mijoz     = $kredit->mijoz ?? null;
  $tulovSana = $tulov->tulov_sana
               ? \Carbon\Carbon::parse($tulov->tulov_sana)
               : now();
  $sana      = $tulovSana->format('d.m.Y');
  $namuna    = $tulovSana->startOfDay()->lt(now()->startOfDay());

  $sum          = number_format($tulov->summa, 0, '.', ' ');
  $shartnoma    = $kredit->shartnoma_raqam ?? '—';
  $mijozFio     = $mijoz
    ? trim(($mijoz->familiya ?? '').' '.($mijoz->ism ?? '').' '.($mijoz->otasining_ismi ?? ''))
    : '—';
  $tulovTuri    = $tulov->tulovTuri->nomi ?? 'Naqd';

  $nasiySumma   = $kredit ? number_format($kredit->kredit_summa,     0, '.', ' ') : '—';
  $oldinTolov   = $kredit ? number_format($kredit->boshlangich_tolov, 0, '.', ' ') : '—';
  $jamiTolangan = $kredit ? number_format($kredit->tolov_qilingan,    0, '.', ' ') : '—';
  $qoldiq       = number_format($qoldiqSana, 0, '.', ' ');
@endphp

@if($namuna)
<div class="namuna-bg"><span>NAMUNA</span></div>
@endif

<div class="varaq">

  <!-- ====== CHAP 60% — KASSIR NUSXASI ====== -->
  <div class="yarmi-kassir">

    <div class="tashkilot">
      IMKONPLUS MChJ<br>
      <span class="tashkilot-kichik">Nasiya savdo tizimi</span>
    </div>
    <div class="nom">KASSA KIRIM ORDERI</div>
    <div class="raqam">
      № <strong>{{ $tulov->id }}</strong> &nbsp;|&nbsp; Sana: <strong>{{ $sana }}</strong>
    </div>

    <table class="info">
      <tr><td>Shartnoma:</td><td>{{ $shartnoma }}</td></tr>
      <tr><td>Mijoz:</td><td>{{ $mijozFio }}</td></tr>
      @if($mijoz && $mijoz->telefon)
      <tr><td>Telefon:</td><td>{{ $mijoz->telefon }}</td></tr>
      @endif
      <tr><td>To'lov turi:</td><td>{{ $tulovTuri }}</td></tr>
      @if($tulov->izoh)
      <tr><td>Izoh:</td><td>{{ $tulov->izoh }}</td></tr>
      @endif
    </table>

    {{-- QUTI 1: ushbu to'lov summasi --}}
    <div class="summa-box-1">
      <div class="s-label">To'langan summa (ushbu to'lov)</div>
      <div class="s-big">{{ $sum }}</div>
      <div class="s-som">so'm</div>
      <div class="s-words">( {{ $summaSoz }} )</div>
    </div>

    {{-- QUTI 2: jami hisoblar --}}
    <div class="summa-box-2">
      <div class="r">
        <span class="lbl">Jami nasiya summa:</span>
        <span class="val">{{ $nasiySumma }} so'm</span>
      </div>
      <div class="r">
        <span class="lbl">Oldindan to'lov:</span>
        <span class="val">{{ $oldinTolov }} so'm</span>
      </div>
      <div class="r">
        <span class="lbl">Jami to'langan summa:</span>
        <span class="val">{{ $jamiTolangan }} so'm</span>
      </div>
      <div class="r total">
        <span class="lbl">Nasiya qoldiq summa:</span>
        <span class="val">{{ $qoldiq }} so'm</span>
      </div>
    </div>

    <div class="imzolar">
      <div class="imzo-row">
        <div class="imzo-item">
          <div class="imzo-label">Kassir:</div>
          <div class="imzo-chiziq"></div>
          <div class="imzo-fio">imzo / F.I.O.</div>
        </div>
        <div class="imzo-item">
          <div class="imzo-label">Direktor:</div>
          <div class="imzo-chiziq"></div>
          <div class="imzo-fio">imzo / F.I.O.</div>
        </div>
        <div class="imzo-item">
          <div class="imzo-label">Buxgalter:</div>
          <div class="imzo-chiziq"></div>
          <div class="imzo-fio">imzo / F.I.O.</div>
        </div>
      </div>
      <div class="muhr-wrap">
        <div class="muhr">M.O.</div>
        <span style="font-size:7pt;color:#777;">Muhr o'rni</span>
      </div>
    </div>

    <div class="nusxa-belgi">Kassir nusxasi — Buxgalteriyada saqlanadi</div>
  </div>

  <!-- ====== O'NG 40% — MIJOZ KVITANSIYASI ====== -->
  <div class="yarmi-talon">

    <div class="talon-nom">Kvitansiya</div>
    <div class="talon-tashkilot">IMKONPLUS MChJ</div>
    <div class="talon-raqam">№ <strong>{{ $tulov->id }}</strong> &nbsp;|&nbsp; {{ $sana }}</div>

    <table class="talon-info">
      <tr><td>Shartnoma:</td><td>{{ $shartnoma }}</td></tr>
      <tr><td>Mijoz:</td><td>{{ $mijozFio }}</td></tr>
      <tr><td>To'lov turi:</td><td>{{ $tulovTuri }}</td></tr>
    </table>

    {{-- QUTI 1: ushbu to'lov --}}
    <div class="talon-s1">
      <div class="ts-label">To'langan summa</div>
      <div class="ts-val">{{ $sum }}</div>
      <div class="ts-som">so'm</div>
      <div class="ts-words">( {{ $summaSoz }} )</div>
    </div>

    {{-- QUTI 2: jami hisoblar --}}
    <div class="talon-s2">
      <div class="r">
        <span class="lbl">Jami nasiya summa:</span>
        <span class="val">{{ $nasiySumma }} so'm</span>
      </div>
      <div class="r">
        <span class="lbl">Oldindan to'lov:</span>
        <span class="val">{{ $oldinTolov }} so'm</span>
      </div>
      <div class="r">
        <span class="lbl">Jami to'langan:</span>
        <span class="val">{{ $jamiTolangan }} so'm</span>
      </div>
      <div class="r total">
        <span class="lbl">Nasiya qoldiq:</span>
        <span class="val">{{ $qoldiq }} so'm</span>
      </div>
    </div>

    <div class="talon-imzo">
      <div class="imzo-label">Kassir:</div>
      <div class="imzo-chiziq"></div>
      <div class="imzo-fio">imzo / F.I.O.</div>
    </div>

    <div class="talon-muhr-wrap">
      <div class="talon-muhr">M.O.</div>
      <span style="font-size:7pt;color:#777;">Muhr o'rni</span>
    </div>

    <div class="talon-imzo">
      <div class="imzo-label">Mijoz:</div>
      <div class="imzo-chiziq"></div>
      <div class="imzo-fio">imzo / F.I.O.</div>
    </div>

    <div class="talon-nusxa">Mijoz nusxasi — Saqlang!</div>
  </div>

</div>

<div class="no-print" style="text-align:center;margin-top:5mm;">
  <button onclick="window.print()" style="padding:4px 18px;font-size:9pt;cursor:pointer;">
    Chop etish (Print)
  </button>
</div>

</body>
</html>
