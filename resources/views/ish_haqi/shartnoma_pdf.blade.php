<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Mehnat shartnomasi {{ $shartnoma->shartnoma_raqami ?? $shartnoma->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; line-height: 1.5; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 5px; }
        .header-info { text-align: center; color: #666; margin-bottom: 20px; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 9px; color: #fff; }
        .badge-loyiha { background: #d97706; }
        .badge-imzolangan { background: #16a34a; }
        .badge-bekor { background: #6b7280; }
        .matn { white-space: pre-wrap; text-align: justify; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>

<h1>MEHNAT SHARTNOMASI</h1>
<div class="header-info">
    @if($shartnoma->shartnoma_raqami)№ {{ $shartnoma->shartnoma_raqami }} · @endif
    Xodim: {{ $shartnoma->xodim->ism_familiya }} ·
    Sana: {{ $shartnoma->sana->format('d.m.Y') }} ·
    <span class="badge {{ $shartnoma->holat === 'imzolangan' ? 'badge-imzolangan' : ($shartnoma->holat === 'bekor_qilingan' ? 'badge-bekor' : 'badge-loyiha') }}">
        {{ $shartnoma->holat === 'imzolangan' ? "Imzolangan" : ($shartnoma->holat === 'bekor_qilingan' ? "Bekor qilingan" : "Loyiha") }}
    </span>
</div>

<div class="matn">{{ $shartnoma->matn }}</div>

<div class="footer">Chop etilgan: {{ now()->format('d.m.Y H:i') }} — {{ config('app.name') }}</div>

</body>
</html>
