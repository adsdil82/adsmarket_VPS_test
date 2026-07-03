<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<title>Akt Sverka — {{ $taminotchi->nomi }}</title>
<style>
@media print { .no-print{display:none!important} @page{size:A4;margin:12mm} }
body{font-family:'Times New Roman',serif;font-size:10pt;color:#000}
.print-bar{background:#1a3a2a;color:#fff;padding:10px 16px;display:flex;gap:12px;align-items:center}
.btn-p{background:#2d6a4f;color:#fff;border:none;padding:6px 18px;border-radius:5px;cursor:pointer}
.btn-b{background:transparent;color:#aaa;border:1px solid #555;padding:6px 14px;border-radius:5px;text-decoration:none;display:inline-block}
h2{text-align:center;font-size:13pt;margin:8px 0 2px}
.subtitle{text-align:center;font-size:9pt;color:#555;margin-bottom:10px}
.info-box{border:1px solid #000;padding:6px 10px;margin-bottom:10px;font-size:9pt}
.info-box table{width:100%;border-collapse:collapse}
.info-box td{padding:2px 4px}
table.main{width:100%;border-collapse:collapse;margin-bottom:8px}
table.main th{background:#d4edda;border:1px solid #666;padding:4px 6px;font-size:9pt;text-align:center}
table.main td{border:1px solid #888;padding:3px 6px;font-size:9pt}
table.main .num{text-align:right}
table.main .dr{color:#c0392b;font-weight:bold}
table.main .kr{color:#1a7741;font-weight:bold}
table.main tr.start td,.footer-row td{background:#fffde7;font-weight:bold}
table.main tr.end-row td{background:#e8f5e9;font-weight:bold}
.sign-block{display:flex;justify-content:space-between;margin-top:20px;font-size:9pt}
.sign-col{width:45%;text-align:center}
.sign-line{border-top:1px solid #000;margin-top:30px;margin-bottom:4px}
</style>
</head>
<body>

<div class="print-bar no-print">
    <button class="btn-p" onclick="window.print()">🖨️ Chop etish</button>
    <a class="btn-b" href="{{ route('taminotchi.show', $taminotchi) }}">← Orqaga</a>
    <form method="GET" style="display:flex;gap:8px;align-items:center;margin-left:12px">
        <input type="date" name="dan_sana" value="{{ $danSana }}" style="padding:4px 8px;border-radius:4px;border:1px solid #555;background:#2a2a2a;color:#fff">
        <span style="color:#aaa">—</span>
        <input type="date" name="gacha_sana" value="{{ $gachaSana }}" style="padding:4px 8px;border-radius:4px;border:1px solid #555;background:#2a2a2a;color:#fff">
        <button type="submit" style="background:#4f46e5;color:#fff;border:none;padding:5px 14px;border-radius:4px;cursor:pointer">Yangilash</button>
    </form>
</div>

@php
    $soz = \App\Models\Sozlama::barchasi();
    $kompNomi = $soz['kompaniya_nomi'] ?? ($soz['brand_nomi'] ?? 'NasiyaPro');
@endphp

<h2>AKT SVERKA</h2>
<div class="subtitle">
    {{ $kompNomi }} va {{ $taminotchi->nomi }} o'rtasida<br>
    {{ \Carbon\Carbon::parse($danSana)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }} davr uchun
</div>

<div class="info-box">
    <div class="table-responsive">
    <table>
        <tr>
            <td><b>Tuzilgan sana:</b> {{ now()->format('d.m.Y') }}</td>
            <td><b>Ta'minotchi:</b> {{ $taminotchi->nomi }}</td>
        </tr>
        <tr>
            <td><b>Davr:</b> {{ \Carbon\Carbon::parse($danSana)->format('d.m.Y') }} – {{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}</td>
            <td>
                @if($taminotchi->inn)<b>INN:</b> {{ $taminotchi->inn }} @endif
                @if($taminotchi->telefon) &nbsp;|&nbsp; <b>Tel:</b> {{ $taminotchi->telefon }} @endif
            </td>
        </tr>
    </table>
    </div>
</div>

<div class="table-responsive">
<table class="main">
    <thead>
        <tr>
            <th style="width:18%">Sana</th>
            <th style="width:34%">Tavsif</th>
            <th style="width:16%">Debet (Kirim)</th>
            <th style="width:16%">Kredit (To'lov)</th>
            <th style="width:16%">Qoldiq</th>
        </tr>
    </thead>
    <tbody>
        {{-- Boshlanish qoldig'i --}}
        <tr class="start">
            <td>{{ \Carbon\Carbon::parse($danSana)->format('d.m.Y') }}</td>
            <td colspan="3"><b>Boshlanish qoldig'i</b></td>
            <td class="num {{ $boshlQoldiq > 0 ? 'dr' : ($boshlQoldiq < 0 ? 'kr' : '') }}">
                @if($boshlQoldiq != 0)
                    {{ number_format(abs($boshlQoldiq),0,'.',' ') }}
                    {{ $boshlQoldiq > 0 ? '(Q)' : '(H)' }}
                @else —
                @endif
            </td>
        </tr>

        {{-- Harakatlar --}}
        @foreach($harakatlar as $h)
        <tr>
            <td>{{ \Carbon\Carbon::parse($h['sana'])->format('d.m.Y') }}</td>
            <td>{{ $h['tavsif'] }}</td>
            <td class="num">{{ $h['debet'] > 0 ? number_format($h['debet'],0,'.',' ') : '' }}</td>
            <td class="num">{{ $h['kredit'] > 0 ? number_format($h['kredit'],0,'.',' ') : '' }}</td>
            <td class="num {{ $h['qoldiq'] > 0 ? 'dr' : ($h['qoldiq'] < 0 ? 'kr' : '') }}">
                {{ $h['qoldiq'] != 0 ? number_format(abs($h['qoldiq']),0,'.',' ') : '—' }}
            </td>
        </tr>
        @endforeach

        {{-- Jami satr --}}
        <tr style="background:#f0f0f0;font-weight:bold">
            <td colspan="2">JAMI:</td>
            <td class="num">{{ number_format($kirimlar->sum('jami_summa'),0,'.',' ') }}</td>
            <td class="num">{{ number_format($tulovlar->sum('summa_uzs'),0,'.',' ') }}</td>
            <td></td>
        </tr>

        {{-- Yakuniy qoldiq --}}
        <tr class="end-row">
            <td>{{ \Carbon\Carbon::parse($gachaSana)->format('d.m.Y') }}</td>
            <td colspan="3"><b>Yakuniy qoldiq</b></td>
            <td class="num {{ $yakunQoldiq > 0 ? 'dr' : ($yakunQoldiq < 0 ? 'kr' : '') }}">
                @if($yakunQoldiq != 0)
                    <b>{{ number_format(abs($yakunQoldiq),0,'.',' ') }}</b>
                    @if($yakunQoldiq > 0)
                        <br><small style="font-weight:normal">(Biz qarazdormiz)</small>
                    @else
                        <br><small style="font-weight:normal">(Ta'minotchi qarazdor)</small>
                    @endif
                @else <b>Balanslangan</b>
                @endif
            </td>
        </tr>
    </tbody>
</table>
</div>

<div style="font-size:8pt;color:#666;margin-bottom:16px">
    * Q — Qarzdorlik (biz ta'minotchiga qarazdormiz) &nbsp;|&nbsp; H — Haq'dorlik (ta'minotchi bizga qarazdor)
</div>

<div class="sign-block">
    <div class="sign-col">
        <b>{{ $kompNomi }}</b>
        <div class="sign-line"></div>
        <div>Rahbar / Buxgalter</div>
        <div style="margin-top:6px;font-size:8pt;color:#555">M.O.</div>
    </div>
    <div class="sign-col">
        <b>{{ $taminotchi->nomi }}</b>
        <div class="sign-line"></div>
        <div>Rahbar / Buxgalter</div>
        <div style="margin-top:6px;font-size:8pt;color:#555">M.O.</div>
    </div>
</div>

</body>
</html>
