@extends('errors.layout')

@section('content')
@php
    $xato     = $exception ?? null;
    $xatoNomi = $xato ? class_basename(get_class($xato)) : 'Server xatosi';
    $xatoMsg  = $xato?->getMessage() ?? 'Noma\'lum xato';
    $fayl     = $xato?->getFile() ?? '';
    $satr     = $xato?->getLine() ?? '';

    // Faylni qisqartirish
    $qisqaFayl = str_replace('/var/www/html/', '', $fayl);

    // O'zbekcha xato tavsifi
    $tavsiflar = [
        'Permission denied'        => '❌ Fayl/papkaga yozish huquqi yo\'q. storage/ papkasining ruxsatlarini tekshiring.',
        'Column not found'         => '❌ Jadvalda ustun topilmadi. Migratsiya yoki yangi maydon qo\'shilgan bo\'lishi mumkin.',
        'Table doesn\'t exist'     => '❌ Jadval mavjud emas. Migratsiya ishga tushirilmagan.',
        'Connection refused'       => '❌ MySQL yoki Redis ga ulanib bo\'lmadi.',
        'Undefined variable'       => '❌ O\'zgaruvchi aniqlanmagan. Controller view ga to\'g\'ri ma\'lumot yubormagan.',
        'Call to a member function'=> '❌ NULL qiymatda metod chaqirildi. NULL tekshiruvini qo\'shing.',
        'Class not found'          => '❌ Klass topilmadi. Composer autoload yoki namespace xato.',
        'SQLSTATE'                 => '❌ SQL xatosi. So\'rov yoki jadval tuzilmasini tekshiring.',
        'Route not defined'        => '❌ Route nomi topilmadi. routes/web.php faylini tekshiring.',
        'View not found'           => '❌ View fayli mavjud emas.',
        'TokenMismatch'            => '❌ CSRF token eskirgan. Sahifani yangilang.',
    ];

    $tavsif = '';
    foreach ($tavsiflar as $kalit => $izoh) {
        if (str_contains($xatoMsg, $kalit)) {
            $tavsif = $izoh;
            break;
        }
    }

    // Stack trace
    $trace = '';
    if ($xato) {
        $trace = "XATO TURI:\n{$xatoNomi}\n\nXATO XABARI:\n{$xatoMsg}\n\nFAYL:\n{$qisqaFayl} (satr {$satr})\n\nSTACK TRACE:\n";
        foreach ($xato->getTrace() as $i => $t) {
            $f = str_replace('/var/www/html/', '', $t['file'] ?? '?');
            $l = $t['line'] ?? '?';
            $fn = ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? '?') . '()';
            $trace .= "#{$i} {$f}:{$l} → {$fn}\n";
            if ($i >= 15) { $trace .= "... va boshqalar\n"; break; }
        }
    }

    // Nusxa uchun matn
    $nusxaMatn = "=== NasiyaPro Xato Hisoboti ===\nSana: " . now()->format('d.m.Y H:i:s') . "\nURL: " . request()->fullUrl() . "\n\n" . $trace;
@endphp

<div class="xato-card">
    {{-- Sarlavha --}}
    <div class="xato-header d-flex justify-content-between align-items-center">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <h5 class="mb-0">500 — Server xatosi</h5>
            </div>
            <code class="text-warning">{{ $xatoNomi }}</code>
        </div>
        <div class="d-flex gap-2">
            <button id="nusxa-btn" class="btn btn-outline-light btn-sm" onclick="nusxaOl(this)">
                <i class="bi bi-clipboard me-1"></i>Nusxa olish
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Orqaga
            </a>
        </div>
    </div>

    <div class="xato-body">

        {{-- Xato xabari --}}
        <div class="sabab-box mb-3">
            <div class="fw-bold mb-1"><i class="bi bi-bug me-1"></i>Xato xabari:</div>
            <code style="word-break:break-all">{{ $xatoMsg }}</code>
        </div>

        {{-- O'zbekcha tavsif --}}
        @if($tavsif)
        <div class="tip-box mb-3">
            <i class="bi bi-lightbulb me-1"></i><strong>Sabab:</strong> {{ $tavsif }}
        </div>
        @endif

        {{-- Fayl va satr --}}
        @if($qisqaFayl)
        <div class="mb-3 small">
            <i class="bi bi-file-code me-1 text-muted"></i>
            <span class="code-line">{{ $qisqaFayl }}</span>
            @if($satr) · <strong>{{ $satr }}-satr</strong> @endif
        </div>
        @endif

        {{-- To'liq xato matni (nusxa uchun) --}}
        <div class="mb-2 d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-terminal me-1"></i>To'liq xato (Claude ga yuborish uchun):</strong>
        </div>
        <div class="stack-trace" id="xato-matn">{{ $nusxaMatn }}</div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-danger" onclick="nusxaOl(this)">
                <i class="bi bi-clipboard me-1"></i>Xatoni nusxalash
            </button>
            <a href="/" class="btn btn-outline-secondary">
                <i class="bi bi-house me-1"></i>Bosh sahifaga
            </a>
            <button class="btn btn-outline-secondary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i>Qayta urinish
            </button>
        </div>

        <div class="mt-3 text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Ushbu xatoni Claude ga yuboring — "Xatoni nusxalash" tugmasini bosib, Claude ga joylashtiring.
        </div>

    </div>
</div>
@endsection
