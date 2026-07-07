@php
    use App\Models\Sozlama;
    use App\Http\Controllers\AdminController;
    $soz      = Sozlama::barchasi();
    $brandNom = $soz['brand_nomi'] ?? 'NasiyaPro';
    $temaId   = (int)($soz['tema'] ?? 1);
    $temalar  = AdminController::$temalar;
    $tema     = $temalar[$temaId] ?? $temalar[1];
    $sidebarBg   = $tema['sidebar'];
    $sidebarBg2  = $tema['sidebar2'] ?? $tema['sidebar'];
    $accentRng   = $tema['accent'];
    $accentRng2  = $tema['accent2'] ?? $tema['accent'];
    if (($soz['tema_maxsus'] ?? '0') === '1') {
        $sidebarBg  = $soz['tema_sidebar1'] ?: $sidebarBg;
        $sidebarBg2 = $soz['tema_sidebar2'] ?: $sidebarBg2;
        $accentRng  = $soz['tema_accent1']  ?: $accentRng;
        $accentRng2 = $soz['tema_accent2']  ?: $accentRng2;
    }
    $grupFontXaritasi = [
        'qora'  => ['#161616', 'rgba(255,255,255,.9)'],
        'sariq' => ['#ffe066', 'rgba(0,0,0,.85)'],
        'qizil' => ['#ff5252', 'rgba(0,0,0,.85)'],
        'oq'    => ['#ffffff', 'rgba(0,0,0,.85)'],
    ];
    $grupFontTanlov = $soz['grup_font_rang'] ?? 'oq';
    [$grupFontRang, $grupFontOutline] = $grupFontXaritasi[$grupFontTanlov] ?? $grupFontXaritasi['oq'];
@endphp
<!DOCTYPE html>
<html lang="uz" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e293b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $brandNom) — {{ $brandNom }}</title>

    {{-- Bootstrap 5.3 --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-bg: {{ $sidebarBg }};
            --sidebar-bg-2: {{ $sidebarBg2 }};
            --sidebar-gradient: linear-gradient(180deg, {{ $sidebarBg }} 0%, {{ $sidebarBg2 }} 100%);
            --accent-color: {{ $accentRng }};
            --accent-color-2: {{ $accentRng2 }};
            --accent-gradient: linear-gradient(90deg, {{ $accentRng }} 0%, {{ $accentRng2 }} 100%);
            --grup-font-color: {{ $grupFontRang }};
            --grup-font-outline: {{ $grupFontOutline }};
        }
        /* ── Asosiy stil ─────────────────────────────── */
        body { font-size: 14px; }

        /* Jadval o'lchami */
        .table-compact  td, .table-compact  th  { padding: 0.25rem 0.5rem; font-size: 12px; }
        .table-default  td, .table-default  th  { padding: 0.5rem 0.75rem; }
        .table-comfort  td, .table-comfort  th  { padding: 0.75rem 1rem; font-size: 15px; }

        /* Sidebar */
        #sidebar {
            width: 240px;
            min-height: 100vh;
            transition: width 0.25s;
        }
        @media (max-width: 767.98px) {
            #sidebar.collapsed { width: 280px !important; }
        }
        /* Desktop'da sidebar'ni ixchamlashtirish — faqat ikonkalar qoladi */
        @media (min-width: 768px) {
            #sidebar.collapsed { width: 64px !important; }
            #sidebar.collapsed .nav-label,
            #sidebar.collapsed .sidebar-header-text,
            #sidebar.collapsed .g-label,
            #sidebar.collapsed .grup-toggle .g-icon { display: none !important; }
            #sidebar.collapsed .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
            #sidebar.collapsed .nav-link i { margin-right: 0 !important; }
            #sidebar.collapsed #vendor-logo-btn { justify-content: center; }
            #sidebar.collapsed #vendor-logo-btn i { margin-right: 0 !important; }
            #sidebar.collapsed .grup-toggle { padding: 5px 0; justify-content: center; }
        }

        /* Guruh sarlavhalari — metalik yaltiroq gradient fon */
        /* ── Guruh sarlavhalar (to'liq keng, bosish mumkin) ─── */
        .grup-header { margin-top: 6px; list-style: none; }
        .grup-toggle {
            display: flex; align-items: center;
            justify-content: space-between;
            width: 100%; padding: 6px 10px;
            background: linear-gradient(135deg,
                var(--accent-color) 0%,
                var(--accent-color-2) 22%,
                var(--accent-color) 45%,
                var(--accent-color-2) 68%,
                var(--accent-color) 100%);
            background-size: 220% 220%;
            background-position: 0% 50%;
            color: var(--grup-font-color, #fff);
            border: none; border-radius: 10px;
            cursor: pointer; font-size: 12px;
            font-weight: 900; letter-spacing: 2.5px;
            text-transform: uppercase;
            position: relative; overflow: hidden;
            transition: background-position .35s ease, filter .18s;
            box-shadow: 0 2px 6px rgba(0,0,0,.28), inset 0 1px 0 rgba(255,255,255,.35);
        }
        .grup-toggle::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.45) 47%, rgba(255,255,255,.65) 50%, rgba(255,255,255,.45) 53%, transparent 70%);
            pointer-events: none;
        }
        .grup-toggle:hover { background-position: 100% 50%; filter: brightness(1.06); }
        .grup-toggle .g-label {
            position: relative; z-index: 1;
            text-shadow:
                -1px -1px 0 var(--grup-font-outline, rgba(0,0,0,.85)),
                 1px -1px 0 var(--grup-font-outline, rgba(0,0,0,.85)),
                -1px  1px 0 var(--grup-font-outline, rgba(0,0,0,.85)),
                 1px  1px 0 var(--grup-font-outline, rgba(0,0,0,.85)),
                 0    0   3px var(--grup-font-outline, rgba(0,0,0,.5));
        }
        .grup-toggle .g-icon {
            font-size: 14px; font-weight: 900;
            line-height: 1; min-width: 16px;
            text-align: center; flex-shrink: 0;
            transition: transform .2s;
            color: rgb(231, 40, 40);
          
        }
        .grup-toggle:has(+ ul:not(.closed)) .g-icon,
        .grup-toggle .bi-plus-lg {
            color: var(--sidebar-bg);
        }
        .grup-items {
            overflow: hidden;
            transition: max-height .25s ease;
            max-height: 600px;
            padding-left: 0;
            list-style: none;
        }
        .grup-items.closed { max-height: 0 !important; }
        #sidebar.collapsed .grup-toggle {
            padding: 5px 0; justify-content: center;
        }
        #sidebar.collapsed .g-label,
        #sidebar.collapsed .g-icon { display: none; }
        .grup-label { color: var(--sidebar-bg) !important; }
        .grup-divider { margin: 2px 0; }

        /* Tema ranglari */
        .sidebar-bg { background: var(--sidebar-gradient) var(--sidebar-bg) !important; }
        .text-warning { color: var(--accent-color) !important; }
        .grup-label { color: var(--sidebar-bg) !important; }

        /* ── Aktiv menyu elementi — temadan mustaqil gradient ─── */
        #sidebar .nav-link.active {
            background: linear-gradient(135deg, #0ee9e9 0%, #6366f1 100%) !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 3px 14px rgba(99,102,241,.45),
                        inset 0 1px 0 rgba(255,255,255,.15) !important;
            border-radius: 7px !important;
            position: relative;
        }
        #sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 4px; bottom: 4px;
            width: 3px;
            background: #fff;
            border-radius: 0 3px 3px 0;
            opacity: .7;
        }
        #sidebar .nav-link:not(.active):hover {
            background: rgba(255,255,255,.09) !important;
            color: #fff !important;
            border-radius: 7px;
        }

        /* ── Bootstrap nav-tabs (sidebardan tashqarida) — yengil soya, chegara va faol tab qalin shrift ── */
        .nav-tabs .nav-link {
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            margin-bottom: -1px;
            border-color: rgba(222,226,230,.45) !important;
        }
        .nav-tabs .nav-link.active {
            font-weight: 700 !important;
            box-shadow: 0 2px 6px rgba(0,0,0,.12);
            border-color: #aab1bb #aab1bb #fff !important;
        }
        [data-bs-theme="dark"] .card { border-color: #444; }

        /* Kechikkan to'lov ajratib ko'rsatish */
        .row-muddati-otgan { background-color: rgba(220, 53, 69, 0.08) !important; }

        /* Holat badgelari */
        .badge-holat { font-size: 0.75rem; }

        /* Yuklanish animatsiyasi */
        #ajax-loader { display: none; }
        .htmx-request #ajax-loader { display: inline-block; }

/* Mobile Floating Action Button (FAB) */
        .mobile-fab {
            display: none;
            position: fixed;
            bottom: 76px; right: 16px;
            z-index: 1025;
            width: 52px; height: 52px;
            border-radius: 50%;
            font-size: 22px;
            box-shadow: 0 4px 16px rgba(0,0,0,.35);
            align-items: center; justify-content: center;
            text-decoration: none;
            border: none;
            -webkit-tap-highlight-color: transparent;
        }
        @media (max-width: 767.98px) {
            .mobile-fab { display: flex !important; }
        }

        /* === MOBILE RESPONSIVE === */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.55);
            z-index: 1044;
        }
        #sidebar-overlay.show { display: block; }

        #mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 60px;
            background: var(--sidebar-gradient) var(--sidebar-bg);
            border-top: 1px solid rgba(255,255,255,.12);
            z-index: 1030;
            align-items: stretch;
            box-shadow: 0 -4px 16px rgba(0,0,0,.3);
        }
        .bn-item {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-decoration: none; color: rgba(255,255,255,.55);
            font-size: 9px; font-weight: 700; letter-spacing: .3px;
            text-transform: uppercase; gap: 2px;
            padding: 4px 2px; border: none; background: none;
            cursor: pointer; transition: color .15s;
            -webkit-tap-highlight-color: transparent;
            position: relative;
        }
        .bn-item i { font-size: 19px; line-height: 1; }
        .bn-item.active { color: var(--accent-color) !important; }
        .bn-item.active::before {
            content: "";
            position: absolute; top: 0; left: 20%; right: 20%;
            height: 3px; background: var(--accent-color); border-radius: 0 0 3px 3px;
        }
        #mobile-topbar-title {
            display: none; font-size: .85rem; font-weight: 700;
            white-space: nowrap; overflow: hidden;
            text-overflow: ellipsis; max-width: 150px;
        }
        @media (max-width: 767.98px) {
            #sidebar {
                position: fixed !important;
                left: 0 !important; top: 0 !important; bottom: 0 !important;
                z-index: 1045;
                width: 280px !important; max-width: 85vw !important;
                transform: translateX(-100%);
                transition: transform .28s cubic-bezier(.4,0,.2,1);
                overflow-y: auto; overflow-x: hidden;
            }
            #sidebar.mobile-open {
                transform: translateX(0) !important;
                box-shadow: 6px 0 30px rgba(0,0,0,.4);
            }
            #sidebar.collapsed { width: 280px !important; }
            #sidebar.collapsed .nav-label,
            #sidebar.collapsed .sidebar-header-text,
            #sidebar.collapsed .g-label { display: block !important; }
            .d-flex > main.flex-grow-1 {
                min-width: 0 !important; width: 100% !important;
                padding-bottom: 65px !important;
            }
            .d-flex > main .p-4 { padding: 0.75rem !important; }
            .d-flex > main .px-4 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
            #mobile-bottom-nav { display: flex !important; }
            .table-size-btn-group-wrap { display: none !important; }
            #mobile-topbar-title { display: block !important; }
            nav[aria-label="breadcrumb"] { display: none !important; }
            .modal-dialog:not(.modal-sm) {
                margin: 0.4rem auto !important;
                max-width: calc(100vw - 0.8rem) !important;
            }
            .modal-body { max-height: 70svh; overflow-y: auto; }
            .btn { min-height: 38px; }
            .btn-sm { min-height: 34px !important; }
            .row.g-3 > .col-sm-6, .row.g-3 > .col-sm-4, .row.g-3 > .col-sm-8,
            .row.g-2 > .col-sm-6, .row.g-2 > .col-sm-4, .row.g-2 > .col-sm-8 {
                flex: 0 0 100% !important; max-width: 100% !important;
            }
        }
        @media (min-width: 768px) {
            #sidebar {
                position: sticky !important; top: 0;
                align-self: flex-start; height: 100vh; overflow-y: auto;
            }
            #mobile-bottom-nav { display: none !important; }
        }

    </style>

    @stack('styles')

    <style>
    /* Forma maydonlari (input/textarea/select) bo'sh bo'lganda ko'rinadigan
       placeholder matnining shaffofligi — barcha sahifa va modallarda */
    input::placeholder,
    textarea::placeholder {
        opacity: 0.5 !important;
    }
    input::-webkit-input-placeholder,
    textarea::-webkit-input-placeholder {
        opacity: 0.5 !important;
    }
    input::-moz-placeholder,
    textarea::-moz-placeholder {
        opacity: 0.5 !important;
    }
    select.form-select:invalid,
    select.form-select option[value=""] {
        opacity: 0.5;
    }

    /* Nested modals: mijoz/tovar modal kreditYangiModal ustida ko'rinsin */
    #mijozIzlashModal,
    #tovarIzlashModal {
        z-index: 1080 !important;
    }
    #mijozIzlashModal .modal-dialog,
    #tovarIzlashModal .modal-dialog {
        z-index: 1081;
    }
    /* kreditYangiModal ichida scroll to'g'ri ishlashi uchun */
    #kreditYangiModal .modal-body {
        overflow-y: auto;
        max-height: calc(100vh - 120px);
    }
    </style>
</head>
<body>
<div class="d-flex">

    {{-- ── Sidebar ───────────────────────────────────────────── --}}
    <nav id="sidebar" class="sidebar-bg text-white d-flex flex-column flex-shrink-0 p-2">
        {{-- Logo --}}
        <div id="vendor-logo-btn" class="d-flex align-items-center mb-3 ps-2" style="cursor:default;user-select:none">
            <i class="bi bi-bank2 fs-4 me-2" style="color:var(--accent-color)"></i>
            <span class="sidebar-header-text fw-bold fs-6">{{ $brandNom }}</span>
        </div>

        {{-- Filial nomi (admin emas) --}}
        @if(Auth::user()->filial)
        <div class="px-2 mb-2">
            <small class="text-white-50 nav-label">{{ Auth::user()->filial->nomi }}</small>
        </div>
        @endif

        <hr class="text-white-50 my-1">

        {{-- ── Aktiv guruhni PHP da aniqlaymiz ──────────────── --}}
        @php
        $aktiv_grup = 'none';
        if (request()->routeIs('mijozlar.*'))                                                          $aktiv_grup = 'mijozlar';
        elseif (request()->routeIs('kreditlar.*'))                                                     $aktiv_grup = 'shartnomalar';
        elseif (request()->routeIs('hisobotlar.*'))                                                    $aktiv_grup = 'hisobotlar';
        elseif (request()->routeIs('pos.*','terminal.*','malumotnamalar.kassalar.*','transfer.kassa.*'))  $aktiv_grup = 'pos';
        elseif (request()->routeIs('katalog.*','tovar-guruhlar.*','kirim.*','chiqim.*','ombor.*'))    $aktiv_grup = 'tovarlar';
        elseif (request()->routeIs('harajatlar.*'))                                                    $aktiv_grup = 'harajatlar';
        elseif (request()->routeIs('pul-oqimlari.*'))                                                 $aktiv_grup = 'pul-oqimlari';
        elseif (request()->routeIs('xabarnoma.*'))                                                    $aktiv_grup = 'xabarnoma';
        elseif (request()->routeIs('transfer.*'))                                                      $aktiv_grup = 'transfer';
        elseif (request()->routeIs('taminotchi.*'))                                                    $aktiv_grup = 'taminotchi';
        elseif (request()->routeIs('transfer.*'))                                                      $aktiv_grup = 'transfer';
        elseif (request()->routeIs('qurilmalar.*','qurilma-provayderlar.*'))                          $aktiv_grup = 'qurilmalar-nazorati';
        elseif (request()->routeIs('admin.deploy','admin.github'))                                     $aktiv_grup = 'vendor';
        elseif (request()->routeIs('malumotnamalar.*','buxgalteriya.hisoblar.*'))                   $aktiv_grup = 'malumotnamalar-menu';
        elseif (request()->routeIs('admin.*'))                                                         $aktiv_grup = 'boshqaruv';
        @endphp

        {{-- Navigatsiya --}}
        <ul class="nav nav-pills flex-column gap-1 flex-grow-1">

            {{-- Bosh sahifa --}}
            <li class="nav-item">
                <a href="{{ route('dashboard') }}"
                   class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    <span class="nav-label">{{ __('ui.menu_bosh') }}</span>
                </a>
            </li>

            {{-- ── MIJOZLAR ──────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('mijozlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="mijozlar">
                    <span class="g-label">&#128100; {{ __('ui.menu_mijozlar') }}</span>
                    <i class="g-icon bi {{ $aktiv_grup==='mijozlar' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='mijozlar' ? 'closed' : '' }}" id="grup-mijozlar">
                <li class="nav-item">
                    <a href="{{ route('mijozlar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('mijozlar.*') && !request('holat') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i>
                        <span class="nav-label">{{ __('ui.menu_barcha_mijozlar') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mijozlar.index', ['holat' => 'faol']) }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('mijozlar.index') && request('holat')==='faol' ? 'active' : '' }}">
                        <i class="bi bi-person-check me-2 text-success"></i>
                        <span class="nav-label">{{ __('ui.menu_faol_mijozlar') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('mijozlar.index', ['holat' => 'nofaol']) }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('mijozlar.index') && request('holat')==='nofaol' ? 'active' : '' }}">
                        <i class="bi bi-person-dash me-2 text-secondary"></i>
                        <span class="nav-label">{{ __('ui.menu_nofaol_mijozlar') }}</span>
                    </a>
                </li>
            </ul>
            @endif

            {{-- ── SHARTNOMALAR ───────────────────────────────── --}}
            @if(Auth::user()->ruxsat('kreditlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="shartnomalar">
                    <span class="g-label">&#128196; {{ __('ui.menu_shartnomalar') }}</span>
                    <i class="g-icon bi {{ $aktiv_grup==='shartnomalar' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='shartnomalar' ? 'closed' : '' }}" id="grup-shartnomalar">
                <li class="nav-item">
                    <a href="{{ route('kreditlar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('kreditlar.*') && !request('holat') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        <span class="nav-label">{{ __('ui.menu_barcha_sh') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kreditlar.index', ['holat' => 'faol']) }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('kreditlar.index') && request('holat')==='faol' ? 'active' : '' }}">
                        <i class="bi bi-file-check me-2 text-success"></i>
                        <span class="nav-label">AKTIV</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kreditlar.index', ['holat' => 'muddati_otgan']) }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('kreditlar.index') && request('holat')==='muddati_otgan' ? 'active' : '' }}">
                        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                        <span class="nav-label">Muddati o'tgan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kreditlar.index', ['holat' => 'yopilgan']) }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('kreditlar.index') && request('holat')==='yopilgan' ? 'active' : '' }}">
                        <i class="bi bi-file-x me-2 text-secondary"></i>
                        <span class="nav-label">PASSIV</span>
                    </a>
                </li>
            </ul>
            @endif

            {{-- ── HISOBOTLAR ─────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('hisobotlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="hisobotlar">
                    <span class="g-label">&#128202; {{ __('ui.menu_hisobotlar') }}</span>
                    <i class="g-icon bi {{ $aktiv_grup==='hisobotlar' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='hisobotlar' ? 'closed' : '' }}" id="grup-hisobotlar">
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.index') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart me-2"></i>
                        <span class="nav-label">To'lovlar hisoboti</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.kredit_portfolio') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.kredit_portfolio') ? 'active' : '' }}">
                        <i class="bi bi-pie-chart me-2 text-success"></i>
                        <span class="nav-label">Kredit portfeli</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.chiqarilgan') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.chiqarilgan') ? 'active' : '' }}">
                        <i class="bi bi-file-plus me-2 text-primary"></i>
                        <span class="nav-label">Chiqarilgan kreditlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.sotilgan_tovarlar') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.sotilgan_tovarlar') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2 text-warning"></i>
                        <span class="nav-label">Kreditga sotilgan tovarlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.bonus_tovarlar') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.bonus_tovarlar') ? 'active' : '' }}">
                        <i class="bi bi-gift me-2 text-warning"></i>
                        <span class="nav-label">Bonusga berilgan tovarlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.kechikish_analiz') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.kechikish_analiz') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-2 text-danger"></i>
                        <span class="nav-label">Kechikish analizi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.kelayotgan') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.kelayotgan') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check me-2 text-warning"></i>
                        <span class="nav-label">{{ __('ui.menu_kelayotgan') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.konstruktor') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.konstruktor*') ? 'active' : '' }}">
                        <i class="bi bi-tools me-2" style="color:#a5b4fc"></i>
                        <span class="nav-label">Konstruktor</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('hisobotlar.transfer') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('hisobotlar.transfer') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right me-2 text-info"></i>
                        <span class="nav-label">Transfer hisoboti</span>
                    </a>
                </li>
            </ul>
            @endif

            {{-- ── POS ──────────────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('tovarlar') || Auth::user()->ruxsat('malumotnomalar') || Auth::user()->ruxsat('transferlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="pos">
                    <span class="g-label"><i class="bi bi-cash-register me-1"></i> POS</span>
                    <i class="g-icon bi {{ $aktiv_grup==='pos' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='pos' ? 'closed' : '' }}" id="grup-pos">
                <li class="nav-item">
                    <a href="{{ route('pos.dashboard') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pos.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2 me-2 text-info"></i>
                        <span class="nav-label">POS Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pos.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pos.index') ? 'active' : '' }}"
                       onclick="return (typeof litsenziyaPosTekshir==='function') ? litsenziyaPosTekshir() : true">
                        <i class="bi bi-cash-register me-2 text-warning"></i>
                        <span class="nav-label">{{ __('ui.menu_kassa') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('terminal.pin-forma') }}" target="_blank"
                       class="nav-link text-white py-1 {{ request()->routeIs('terminal.*') ? 'active' : '' }}">
                        <i class="bi bi-fullscreen me-2 text-primary"></i>
                        <span class="nav-label">Fullscreen kassa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pos.tarix') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pos.tarix') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-2"></i>
                        <span class="nav-label">Sotuv tarixi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pos.hisobotlar') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pos.hisobotlar') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line me-2 text-success"></i>
                        <span class="nav-label">POS hisobotlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pos.smena.royxat') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pos.smena.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-2 text-info"></i>
                        <span class="nav-label">Kassir smenalari</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pos.qaytim.royxat') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pos.qaytim.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-return-left me-2 text-danger"></i>
                        <span class="nav-label">Qaytim / Vozvrat</span>
                    </a>
                </li>
                @if(Auth::user()->isAdmin())
                <li class="nav-item"><a href="{{ route('malumotnamalar.kassalar.index') }}"
                    class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.kassalar.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2 me-2"></i>
                    <span class="nav-label">Kassalar</span>
                </a></li>
                @endif
                @if(Auth::user()->ruxsat('transferlar'))
                <li class="nav-item">
                    <a href="{{ route('transfer.kassa.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.kassa.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-coin me-2 text-primary"></i>
                        <span class="nav-label">Kassa transferi</span>
                    </a>
                </li>
                @endif
            </ul>
            @endif

            {{-- ── TOVARLAR ────────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('tovarlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="tovarlar">
                    <span class="g-label">&#127979; {{ __('ui.menu_tovarlar') }}</span>
                    <i class="g-icon bi {{ $aktiv_grup==='tovarlar' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='tovarlar' ? 'closed' : '' }}" id="grup-tovarlar">
                @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
                <li class="nav-item">
                    <a href="{{ route('katalog.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('katalog.*') ? 'active' : '' }}">
                        <i class="bi bi-box me-2"></i>
                        <span class="nav-label">{{ __('ui.menu_katalog') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('tovar-guruhlar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('tovar-guruhlar.*') ? 'active' : '' }}">
                        <i class="bi bi-tags me-2"></i>
                        <span class="nav-label">{{ __('ui.menu_guruhlar') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kirim.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('kirim.*') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-in-down me-2 text-success"></i>
                        <span class="nav-label">{{ __('ui.menu_kirim') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('chiqim.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('chiqim.*') ? 'active' : '' }}">
                        <i class="bi bi-box-arrow-up me-2 text-danger"></i>
                        <span class="nav-label">{{ __('ui.menu_chiqim') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('ombor.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('ombor.index') ? 'active' : '' }}">
                        <i class="bi bi-boxes me-2 text-info"></i>
                        <span class="nav-label">Ombor qoldig'i</span>
                    </a>
                </li>
                @endif
            </ul>
            @endif

            {{-- ── TA'MINOTCHILAR ──────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('taminotchilar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="taminotchi">
                    <span class="g-label">🚛 TA'MINOTCHILAR</span>
                    <i class="g-icon bi {{ $aktiv_grup==='taminotchi' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='taminotchi' ? 'closed' : '' }}" id="grup-taminotchi">
                <li class="nav-item">
                    <a href="{{ route('taminotchi.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('taminotchi.index') ? 'active' : '' }}">
                        <i class="bi bi-truck me-2 text-warning"></i>
                        <span class="nav-label">Ta'minotchilar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('taminotchi.tulov_reestr') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('taminotchi.tulov_reestr') ? 'active' : '' }}">
                        <i class="bi bi-list-check me-2 text-success"></i>
                        <span class="nav-label">To'lovlar reestri</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('taminotchi.kirim_reestr') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('taminotchi.kirim_reestr') ? 'active' : '' }}">
                        <i class="bi bi-journal-text me-2 text-info"></i>
                        <span class="nav-label">Kirimlar reestri</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('taminotchi.hisobot') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('taminotchi.hisobot') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart me-2 text-info"></i>
                        <span class="nav-label">Hisobot / Balans</span>
                    </a>
                </li>
            </ul>
            @endif


            {{-- HARAJATLAR --}}
            @if(Auth::user()->ruxsat('harajatlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="harajatlar">
                    <span class="g-label">&#128181; HARAJATLAR</span>
                    <i class="g-icon bi {{ $aktiv_grup==='harajatlar' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='harajatlar' ? 'closed' : '' }}" id="grup-harajatlar">
                <li class="nav-item">
                    <a href="{{ route('harajatlar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('harajatlar.index') ? 'active' : '' }}">
                        <i class="bi bi-wallet2 me-2 text-danger"></i>
                        <span class="nav-label">Harajatlar</span>
                    </a>
                </li>
            </ul>
            @endif

            {{-- PUL OQIMLARI --}}
            @if(Auth::user()->ruxsat('pul_oqimlari'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="pul-oqimlari">
                    <span class="g-label">&#9889; MOLIYA</span>
                    <i class="g-icon bi {{ $aktiv_grup==='pul-oqimlari' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='pul-oqimlari' ? 'closed' : '' }}" id="grup-pul-oqimlari">
                <li class="nav-item">
                    <a href="{{ route('pul-oqimlari.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pul-oqimlari.index') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right me-2" style="color:#6366f1"></i>
                        <span class="nav-label">Pul Oqimlari</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pul-oqimlari.moliyaviy-natija.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pul-oqimlari.moliyaviy-natija.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow me-2 text-warning"></i>
                        <span class="nav-label">Moliyaviy natija</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pul-oqimlari.balans.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pul-oqimlari.balans.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-steps me-2 text-info"></i>
                        <span class="nav-label">Balans hisoboti</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('pul-oqimlari.hisobot') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('pul-oqimlari.hisobot') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data me-2" style="color:#22c55e"></i>
                        <span class="nav-label">Pul oqimi hisoboti</span>
                    </a>
                </li>
            </ul>
            @endif

        
        {{-- MA'LUMOTNOMALAR --}}
        @if(Auth::user()->ruxsat('malumotnomalar'))
        <li class="grup-header">
            <button class="grup-toggle" data-grup="malumotnamalar-menu">
                <span class="g-label">&#128218; RO'YXATLAR</span>
                <i class="g-icon bi {{ $aktiv_grup==='malumotnamalar-menu' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
            </button>
        </li>
        <ul class="grup-items {{ $aktiv_grup!=='malumotnamalar-menu' ? 'closed' : '' }}" id="grup-malumotnamalar-menu">
            <li class="nav-item"><a href="{{ route('malumotnamalar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.index') ? 'active' : '' }}">
                Umumiy</a></li>
            @if(Auth::user()->isAdmin())
            <li class="nav-item ps-2 mt-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.5">Tashkiliy</li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.filiallar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.filiallar.*') ? 'active' : '' }}">
                Filiallar</a></li>
            <li class="nav-item"><a href="{{ route('admin.foydalanuvchilar') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('admin.foydalanuvchilar') ? 'active' : '' }}">
                Foydalanuvchilar</a></li>
            <li class="nav-item"><a href="{{ route('admin.rollar') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('admin.rollar') ? 'active' : '' }}">
                Rollar</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.tashkilot-rekvizit.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.tashkilot-rekvizit.*') ? 'active' : '' }}">
                Tashkilot rekvizitlari</a></li>
            @endif
            <li class="nav-item ps-2 mt-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.5">Tovar va Ombor</li>
            <li class="nav-item"><a href="{{ route('tovar-guruhlar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('tovar-guruhlar.*') ? 'active' : '' }}">
                Tovar guruhlari</a></li>
            <li class="nav-item"><a href="{{ route('katalog.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('katalog.*') ? 'active' : '' }}">
                Tovar katalogi</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.birliklar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.birliklar.*') ? 'active' : '' }}">
                Birliklar</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.brendlar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.brendlar.*') ? 'active' : '' }}">
                Brendlar</a></li>
            <li class="nav-item ps-2 mt-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.5">Moliya</li>
            @if(Auth::user()->isAdmin() || Auth::user()->isHisobchi())
            <li class="nav-item"><a href="{{ route('buxgalteriya.tulov_turlari.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('buxgalteriya.tulov_turlari.*') ? 'active' : '' }}">
                To'lov turlari</a></li>
            @endif
            <li class="nav-item"><a href="{{ route('malumotnamalar.harajat-turlari.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.harajat-turlari.*') ? 'active' : '' }}">
                Harajat turlari</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.pul-kategoriyalar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.pul-kategoriyalar.*') ? 'active' : '' }}">
                Pul oqimi kategoriyalari</a></li>
            @if(Auth::user()->isAdmin() || Auth::user()->isHisobchi())
            <li class="nav-item"><a href="{{ route('buxgalteriya.hisoblar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('buxgalteriya.hisoblar.*') ? 'active' : '' }}">
                Hisoblar rejasi</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.valyutalar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.valyutalar.*') ? 'active' : '' }}">
                Valyutalar</a></li>
            @endif
            @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
            <li class="nav-item ps-2 mt-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.5">Hujjatlar</li>
            <li class="nav-item"><a href="{{ route('xabarnoma.shablonlar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('xabarnoma.shablonlar.*') ? 'active' : '' }}">
                Xabarnoma shablonlari</a></li>
            @endif
            @if(Auth::user()->isAdmin())
            <li class="nav-item ps-2 mt-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.5">Hujjatlar</li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.shartnoma-rekvizit.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.shartnoma-rekvizit.*') ? 'active' : '' }}">
                Shartnoma rekvizitlari</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.statuslar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.statuslar.*') ? 'active' : '' }}">
                Statuslar va sabablar</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.pochta-shablonlar.index') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.pochta-shablonlar.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper me-1" style="color:#a78bfa"></i>Pochta Shablonlari</a></li>
            <li class="nav-item"><a href="{{ route('malumotnamalar.viloyatlar.index') }}" class="nav-link text-white py-1 {{ request()->routeIs('malumotnamalar.viloyatlar.*') ? 'active' : '' }}"><i class="bi bi-geo-alt me-1" style="color:#34d399"></i>Viloyat/Tumanlar</a></li>
            <li class="nav-item ps-2 mt-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;opacity:.5">Tizim</li>
            <li class="nav-item"><a href="{{ route('admin.sozlamalar') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('admin.sozlamalar') ? 'active' : '' }}">
                Sozlamalar</a></li>
            <li class="nav-item"><a href="{{ route('admin.ruxsatlar') }}"
                class="nav-link text-white py-1 {{ request()->routeIs('admin.ruxsatlar') ? 'active' : '' }}">
                Ruxsatlar</a></li>
            @endif
        </ul>
        @endif

        {{-- QURILMALAR NAZORATI --}}
        @if(Auth::user()->ruxsat('qurilmalar'))
        <li class="grup-header">
            <button class="grup-toggle" data-grup="qurilmalar-nazorati">
                <span class="g-label">&#128241; QURILMALAR</span>
                <i class="g-icon bi {{ $aktiv_grup==='qurilmalar-nazorati' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
            </button>
        </li>
        <ul class="grup-items {{ $aktiv_grup!=='qurilmalar-nazorati' ? 'closed' : '' }}" id="grup-qurilmalar-nazorati">
            <li class="nav-item">
                <a href="{{ route('qurilmalar.index') }}"
                   class="nav-link text-white py-1 {{ request()->routeIs('qurilmalar.index','qurilmalar.show','qurilmalar.edit','qurilmalar.loglar') ? 'active' : '' }}">
                   Qurilmalar ro'yxati
                </a>
            </li>
            @if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki() || Auth::user()->isOmborchi())
            <li class="nav-item">
                <a href="{{ route('qurilmalar.create') }}"
                   class="nav-link text-white py-1 {{ request()->routeIs('qurilmalar.create') ? 'active' : '' }}">
                   Qurilma qo'shish
                </a>
            </li>
            @endif
            @if(Auth::user()->isAdmin())
            <li class="nav-item">
                <a href="{{ route('qurilma-provayderlar.index') }}"
                   class="nav-link text-white py-1 {{ request()->routeIs('qurilma-provayderlar.*') ? 'active' : '' }}">
                   Provayderlar
                </a>
            </li>
            @endif
        </ul>
        @endif

            {{-- XABARNOMA guruh --}}
            @if(Auth::user()->ruxsat('xabarnoma'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="xabarnoma">
                    <span class="g-label">&#128241; XABARNOMA</span>
                    <i class="g-icon bi {{ $aktiv_grup==='xabarnoma' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='xabarnoma' ? 'closed' : '' }}" id="grup-xabarnoma">
                <li class="nav-item">
                    <a href="{{ route('xabarnoma.sms.guruhli') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('xabarnoma.sms.*') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots me-2 text-warning"></i>
                        <span class="nav-label">SMS</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('xabarnoma.telegram.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('xabarnoma.telegram.*') ? 'active' : '' }}">
                        <i class="bi bi-telegram me-2 text-info"></i>
                        <span class="nav-label">Telegram</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('xabarnoma.email.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('xabarnoma.email.*') ? 'active' : '' }}">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        <span class="nav-label">Email</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('xabarnoma.shablonlar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('xabarnoma.shablonlar.*') ? 'active' : '' }}">
                        <i class="bi bi-file-text me-2" style="color:#a5b4fc"></i>
                        <span class="nav-label">Shablonlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('xabarnoma.sms.tarix') }}"
                       class="nav-link text-white py-1">
                        <i class="bi bi-clock-history me-2 text-secondary"></i>
                        <span class="nav-label">Yuborish tarixi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('xabarnoma.hybrid_mail.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('xabarnoma.hybrid_mail.*') ? 'active' : '' }}">
                        <i class="bi bi-envelope-paper me-2" style="color:#8b5cf6"></i>
                        <span class="nav-label">Gibrid Pochta</span>
                    </a>
                </li>
            </ul>
            @endif

                        {{-- ── TRANSFERLAR ─────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('transferlar'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="transfer">
                    <span class="g-label">&#8644; TRANSFERLAR</span>
                    <i class="g-icon bi {{ $aktiv_grup==='transfer' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='transfer' ? 'closed' : '' }}" id="grup-transfer">
                <li class="nav-item">
                    <a href="{{ route('transfer.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.index') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right me-2 text-info"></i>
                        <span class="nav-label">Transferlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('transfer.tovar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.tovar.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2 text-success"></i>
                        <span class="nav-label">Tovar transferi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('transfer.supplier-return.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.supplier-return.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-return-left me-2 text-secondary"></i>
                        <span class="nav-label">Ta'minotchiga qaytarish</span>
                    </a>
                </li>
                @if(Auth::user()->isMenejerYoki())
                <li class="nav-item">
                    <a href="{{ route('transfer.shartnoma.xodim_tarixi') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.shartnoma.*') ? 'active' : '' }}">
                        <i class="bi bi-person-check me-2 text-warning"></i>
                        <span class="nav-label">Qayta tayinlash</span>
                    </a>
                </li>
                @endif
                @if(Auth::user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('transfer.tolov_turi.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.tolov_turi.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card me-2" style="color:#a5b4fc"></i>
                        <span class="nav-label">To'lov turlari</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('transfer.audit') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('transfer.audit') ? 'active' : '' }}">
                        <i class="bi bi-journal-text me-2 text-info"></i>
                        <span class="nav-label">Audit jurnali</span>
                    </a>
                </li>
            </ul>
            @endif

            {{-- ── BOSHQARUV ───────────────────────────────────── --}}
            @if(Auth::user()->ruxsat('boshqaruv'))
            <li class="grup-header">
                <button class="grup-toggle" data-grup="boshqaruv">
                    <span class="g-label">&#9881; {{ __('ui.menu_boshqaruv') }}</span>
                    <i class="g-icon bi {{ $aktiv_grup==='boshqaruv' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items {{ $aktiv_grup!=='boshqaruv' ? 'closed' : '' }}" id="grup-boshqaruv">
                @if(Auth::user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('admin.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                        <i class="bi bi-shield-lock me-2 text-danger"></i>
                        <span class="nav-label">{{ __('ui.menu_admin') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.sozlamalar') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.sozlamalar') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i>
                        <span class="nav-label">{{ __('ui.menu_sozlamalar') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.litsenziya') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.litsenziya') ? 'active' : '' }}">
                        <i class="bi bi-shield-check me-2 {{ \App\Services\Litsenziya::bloklanganmi() ? 'text-danger' : 'text-success' }}"></i>
                        <span class="nav-label">Litsenziya</span>
                        @if(\App\Services\Litsenziya::bloklanganmi())
                            <span class="badge bg-danger ms-1">!</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.ruxsatlar') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.ruxsatlar') ? 'active' : '' }}">
                        <i class="bi bi-key me-2 text-warning"></i>
                        <span class="nav-label">Ruxsatlar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.rollar') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.rollar') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2 text-info"></i>
                        <span class="nav-label">Rollar</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('buxgalteriya.hisoblar.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('buxgalteriya.hisoblar*') ? 'active' : '' }}">
                        <i class="bi bi-journal-bookmark me-2 text-warning"></i>
                        <span class="nav-label">Hisoblar rejasi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('buxgalteriya.tulov_turlari.index') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('buxgalteriya.tulov_turlari*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card-2-front me-2 text-primary"></i>
                        <span class="nav-label">To'lov turlari (yangi)</span>
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="{{ route('admin.audit') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.audit') ? 'active' : '' }}">
                        <i class="bi bi-journal-text me-2 text-info"></i>
                        <span class="nav-label">{{ __('ui.menu_audit') }}</span>
                    </a>
                </li>
            </ul>
            @endif

            {{-- ── VENDOR (yashirin, parol bilan ochiladi) ──────── --}}
            @if(Auth::user()->isAdmin())
            <li id="vendor-menu-group" class="grup-header" style="display:none">
                <button class="grup-toggle" data-grup="vendor" style="background:#7c3aed;color:#fff">
                    <span class="g-label">&#128274; VENDOR</span>
                    <i class="g-icon bi {{ $aktiv_grup==='vendor' ? 'bi-plus-lg' : 'bi-dash-lg' }}"></i>
                </button>
            </li>
            <ul class="grup-items vendor-menu-item {{ $aktiv_grup!=='vendor' ? 'closed' : '' }}"
                id="grup-vendor" style="display:none">
                <li class="nav-item">
                    <a href="{{ route('admin.deploy') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.deploy') ? 'active' : '' }}">
                        <i class="bi bi-cloud-upload me-2 text-warning"></i>
                        <span class="nav-label">Deploy / Xost</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.github') }}"
                       class="nav-link text-white py-1 {{ request()->routeIs('admin.github') ? 'active' : '' }}">
                        <i class="bi bi-github me-2"></i>
                        <span class="nav-label">GitHub</span>
                    </a>
                </li>
            </ul>
            @endif
        </ul>

        <hr class="text-white-50 my-1">

        {{-- Foydalanuvchi --}}
        <div class="px-2 py-1">
            <small class="text-white-50 nav-label d-block">{{ Auth::user()->ism_familiya }}</small>
            <small class="text-warning nav-label">{{ Auth::user()->rol }}</small>
        </div>
        <a href="{{ route('profil') }}" class="nav-link text-white">
            <i class="bi bi-person-gear me-2"></i>
            <span class="nav-label">{{ __('ui.profil') }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link text-white border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-left me-2"></i>
                <span class="nav-label">{{ __('ui.chiqish') }}</span>
            </button>
        </form>
    </nav>

    {{-- Mobile sidebar overlay --}}
    <div id="sidebar-overlay"></div>

    {{-- ── Asosiy kontent ─────────────────────────────────────────── --}}
    <main class="flex-grow-1 overflow-auto" id="main-content" style="max-height: 100vh;">

        {{-- Yuqori panel --}}
        <header class="navbar border-bottom px-3 py-2 sticky-top bg-body">
            <div class="d-flex align-items-center gap-3">
                {{-- Sidebar toggle --}}
                <button id="sidebar-toggle" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-list"></i>
                </button>

                {{-- Mobile sahifa nomi (faqat telefonda) --}}
                <span id="mobile-topbar-title">@yield('title', $brandNom)</span>

                {{-- Ajax loader --}}
                <div id="ajax-loader">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>

                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Jadval o'lchami (mobil da yashirin) --}}
                <div class="table-size-btn-group-wrap">
                <div class="btn-group btn-group-sm" title="Jadval o'lchami">
                    <button class="btn btn-outline-secondary table-size-btn" data-size="compact"
                            title="Kichik">
                        <i class="bi bi-layout-three-columns"></i>
                    </button>
                    <button class="btn btn-outline-secondary table-size-btn active" data-size="default"
                            title="Standart">
                        <i class="bi bi-table"></i>
                    </button>
                    <button class="btn btn-outline-secondary table-size-btn" data-size="comfort"
                            title="Katta">
                        <i class="bi bi-layout-text-window-reverse"></i>
                    </button>
                </div>
                </div>{{-- /table-size-btn-group-wrap --}}

                {{-- Til tanlash --}}
                @php
                    $joriyTil = app()->getLocale();
                    $tillar = ['uz' => ['nom'=>'UZB','flag'=>'🇺🇿'], 'ru' => ['nom'=>'РУС','flag'=>'🇷🇺'], 'en' => ['nom'=>'ENG','flag'=>'🇬🇧']];
                @endphp
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1"
                            type="button" data-bs-toggle="dropdown" title="{{ __('ui.til') }}">
                        <span>{{ $tillar[$joriyTil]['flag'] ?? '🇺🇿' }}</span>
                        <span style="font-size:.75rem;font-weight:700">{{ $tillar[$joriyTil]['nom'] ?? 'UZB' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:110px">
                        @foreach($tillar as $kod => $info)
                        <li>
                            <form method="POST" action="{{ route('til.ozgartir') }}">
                                @csrf
                                <input type="hidden" name="til" value="{{ $kod }}">
                                <button type="submit"
                                    class="dropdown-item d-flex align-items-center gap-2 {{ $joriyTil === $kod ? 'active fw-bold' : '' }}">
                                    <span>{{ $info['flag'] }}</span>
                                    <span>{{ $info['nom'] }}</span>
                                    @if($joriyTil === $kod)
                                    <i class="bi bi-check2 ms-auto text-success"></i>
                                    @endif
                                </button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Dark/Light mode --}}
                <button id="theme-toggle" class="btn btn-sm btn-outline-secondary" title="Mavzu">
                    <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                </button>
            </div>
        </header>

        {{-- Litsenziya holati ogohlantiruvi (faqat admin uchun) --}}
        @if(Auth::user()->isAdmin() && \App\Services\Litsenziya::yoqilganmi() && in_array(\App\Services\Litsenziya::holati(), ['ogohlantirish','yengillik','yopiq']))
            @php $litHolat = \App\Services\Litsenziya::holati(); @endphp
            <div class="px-4 pt-3">
                <div class="alert alert-{{ $litHolat === 'yopiq' ? 'danger' : 'warning' }} alert-dismissible fade show d-flex align-items-center justify-content-between py-2 mb-0">
                    <span>
                        <i class="bi bi-shield-exclamation me-1"></i>
                        @if($litHolat === 'yopiq')
                            <b>Litsenziya muddati tugagan.</b> Ba'zi bo'limlarda (mijoz/tovar/shartnoma qo'shish va h.k.) cheklov bor — qisman foydalanish davom etadi.
                        @else
                            <b>Litsenziya muddati tugashiga oz qoldi.</b> Vaqtida faollashtiring.
                        @endif
                    </span>
                    <span class="d-flex align-items-center gap-2 ms-2">
                        <a href="{{ route('admin.litsenziya') }}" class="btn btn-sm btn-{{ $litHolat === 'yopiq' ? 'light' : 'dark' }}">Litsenziya bo'limi</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Yopish"></button>
                    </span>
                </div>
            </div>
        @endif

        {{-- Flash xabarlar --}}
        <div class="px-4 pt-3">
            @if(session('muvaffaqiyat'))
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('muvaffaqiyat') }}
                    @if(session('kvitansiya_url'))
                    <button type="button"
                       onclick="flashKvitansiyaOch('{{ session('kvitansiya_url') }}')"
                       class="btn btn-sm btn-light ms-2 py-0"
                       style="font-size:.8rem">
                        <i class="bi bi-printer-fill me-1 text-success"></i> Kvitansiya
                    </button>
                    @endif
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('xato'))
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('xato') }}
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Xatoliklar:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        {{-- Sahifa kontenti --}}
        <div class="p-4">
            @yield('content')
        </div>

    </main>
</div>

{{-- Mobile Bottom Navigation (faqat telefonda ko'rinadi, d-md-none) --}}
<nav id="mobile-bottom-nav" aria-label="Pastki navigatsiya">
    <a href="{{ route('dashboard') }}"
       class="bn-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i><span>Bosh</span>
    </a>
    <a href="{{ route('mijozlar.index') }}"
       class="bn-item {{ request()->routeIs('mijozlar.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i><span>Mijozlar</span>
    </a>
    <a href="{{ route('kreditlar.index') }}"
       class="bn-item {{ request()->routeIs('kreditlar.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-text"></i><span>Shartnoma</span>
    </a>
    <a href="{{ route('transfer.index') }}"
       class="bn-item {{ request()->routeIs('transfer.*') ? 'active' : '' }}">
        <i class="bi bi-arrow-left-right"></i><span>Transfer</span>
    </a>
    <button class="bn-item" onclick="mobileMenuToggle()" id="bn-menu-btn">
        <i class="bi bi-grid" id="bn-menu-icon"></i><span>Menyu</span>
    </button>
</nav>


{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
// ── Dark/Light mode ─────────────────────────────────────────────
const htmlEl    = document.documentElement;
const themeBtn  = document.getElementById('theme-toggle');
const themeIcon = document.getElementById('theme-icon');

function setTheme(theme) {
    htmlEl.setAttribute('data-bs-theme', theme);
    localStorage.setItem('nasiya_theme', theme);
    themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
}

const savedTheme = localStorage.getItem('nasiya_theme') || 'light';
setTheme(savedTheme);

themeBtn.addEventListener('click', () => {
    const curr = htmlEl.getAttribute('data-bs-theme');
    setTheme(curr === 'dark' ? 'light' : 'dark');
});

// ── Jadval o'lchami ─────────────────────────────────────────────
function setTableSize(size) {
    document.querySelectorAll('table').forEach(t => {
        t.classList.remove('table-compact', 'table-default', 'table-comfort');
        t.classList.add('table-' + size);
    });
    localStorage.setItem('nasiya_table_size', size);
    document.querySelectorAll('.table-size-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.size === size);
    });
}

const savedSize = localStorage.getItem('nasiya_table_size') || 'default';
setTableSize(savedSize);

document.querySelectorAll('.table-size-btn').forEach(btn => {
    btn.addEventListener('click', () => setTableSize(btn.dataset.size));
});

// ── Sidebar toggle — desktop va mobile uchun ────────────────────
const sidebar      = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebar-toggle");
const sidebarOverlay = document.getElementById("sidebar-overlay");

function isMobile() { return window.innerWidth < 768; }

function openMobileSidebar() {
    sidebar.classList.add("mobile-open");
    sidebarOverlay.classList.add("show");
    document.body.style.overflow = "hidden";
    document.getElementById("bn-menu-icon").className = "bi bi-x-lg";
}
function closeMobileSidebar() {
    sidebar.classList.remove("mobile-open");
    sidebarOverlay.classList.remove("show");
    document.body.style.overflow = "";
    var icon = document.getElementById("bn-menu-icon");
    if (icon) icon.className = "bi bi-grid";
}

// Desktop'da — sidebar'ni ixchamlashtirish/yoyish, holati saqlanadi
if (!isMobile() && localStorage.getItem("nasiya_sidebar") === "collapsed") {
    sidebar.classList.add("collapsed");
}

sidebarToggle.addEventListener("click", function() {
    if (isMobile()) {
        sidebar.classList.contains("mobile-open") ? closeMobileSidebar() : openMobileSidebar();
    } else {
        sidebar.classList.toggle("collapsed");
        localStorage.setItem("nasiya_sidebar", sidebar.classList.contains("collapsed") ? "collapsed" : "expanded");
    }
});

// Overlay bosilganda yopish
if (sidebarOverlay) {
    sidebarOverlay.addEventListener("click", closeMobileSidebar);
}

// ESC bilan yopish
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && sidebar.classList.contains("mobile-open")) {
        closeMobileSidebar();
    }
});

// Sidebar ichidagi linklar bosilganda mobile da yopish
sidebar.querySelectorAll(".nav-link[href]").forEach(function(link) {
    link.addEventListener("click", function() {
        if (isMobile()) closeMobileSidebar();
    });
});

// Mobile bottom nav menu tugmasi
function mobileMenuToggle() {
    if (sidebar.classList.contains("mobile-open")) {
        closeMobileSidebar();
    } else {
        openMobileSidebar();
    }
}

// Ekran kengligida o'zgarganda
window.addEventListener("resize", function() {
    if (!isMobile() && sidebar.classList.contains("mobile-open")) {
        closeMobileSidebar();
    }
});



// ── Guruh accordion (bir ochilib boshqalari yopiladi) ─────────────
(function() {
    // Icon: bi-dash-lg = ochiq, bi-plus-lg = yopiq
    function iconBel(btn, open) {
        var icon = btn ? btn.querySelector('.g-icon') : null;
        if (!icon) return;
        icon.classList.remove('bi-plus-lg','bi-dash-lg');
        icon.classList.add(open ? 'bi-plus-lg' : 'bi-dash-lg');
    }

    // Guruhni ochish yoki yopish
    function grupOch(grupId, open) {
        var items = document.getElementById('grup-' + grupId);
        var btn   = document.querySelector('.grup-toggle[data-grup="' + grupId + '"]');
        if (!items) return;
        if (open) { items.classList.remove('closed'); }
        else       { items.classList.add('closed');   }
        iconBel(btn, open);
    }

    // Bosish: ACCORDION — boshqalarini yop, buni ochilganini yop/och
    document.querySelectorAll('.grup-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var grupId   = this.getAttribute('data-grup');
            var items    = document.getElementById('grup-' + grupId);
            if (!items) return;
            var willOpen = items.classList.contains('closed');

            // Barcha guruhlarni yopamiz
            document.querySelectorAll('.grup-toggle').forEach(function(b) {
                grupOch(b.getAttribute('data-grup'), false);
            });

            // Bosilganni: yopiq edi → och; ochiq edi → yopiq qol
            grupOch(grupId, willOpen);
        });
    });

    // Boshlang'ich holat: server aktiv deb belgilagan guruh ochiq, qolganlari yopiq
    document.querySelectorAll('.grup-toggle').forEach(function(btn) {
        var grupId = btn.getAttribute('data-grup');
        var items  = document.getElementById('grup-' + grupId);
        if (!items) return;
        var isOpen = !items.classList.contains('closed');
        iconBel(btn, isOpen);
    });
})();

// ── CSRF Ajax ────────────────────────────────────────────────────
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});
</script>


{{-- ── VENDOR Modal: parol bilan ochish ──────────────────── --}}
<div class="modal fade" id="vendorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header" style="background:#7c3aed">
        <h6 class="modal-title text-white fw-bold">
          <i class="bi bi-shield-lock me-2"></i>Vendor kirish
        </h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label small text-muted mb-1">Vendor paroli:</label>
        <div class="input-group">
          <input type="password" id="vendor-pass-input" class="form-control"
                 placeholder="••••••" maxlength="10" autocomplete="off">
          <button class="btn btn-outline-secondary" type="button" id="vendor-pass-toggle">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <div id="vendor-pass-error" class="text-danger small mt-1" style="display:none">
          <i class="bi bi-x-circle me-1"></i>Noto'g'ri parol!
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bekor</button>
        <button type="button" class="btn btn-sm text-white" id="vendor-pass-btn"
                style="background:#7c3aed">
          <i class="bi bi-unlock me-1"></i>Ochish
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
    // ── Sozlamalar ────────────────────────────────────────────
    const VENDOR_PASS   = '132253';
    const CLICK_NEEDED  = 10;
    const LS_KEY        = 'nasiya_vendor_open';

    let clickCount = 0;
    let clickTimer  = null;

    // ── Vendor menyu ko'rsatish/yashirish ────────────────────
    function vendorKorsatish(show) {
        const group = document.getElementById('vendor-menu-group');
        const items = document.querySelectorAll('.vendor-menu-item');
        if (group) group.style.display = show ? '' : 'none';
        items.forEach(el => el.style.display = show ? '' : 'none');
        if (show) {
            localStorage.setItem(LS_KEY, '1');
        } else {
            localStorage.removeItem(LS_KEY);
        }
    }

    // ── Sahifa yuklanishida tekshiruv ─────────────────────────
    if (localStorage.getItem(LS_KEY) === '1') {
        vendorKorsatish(true);
    }

    // ── Logo 10 marta bosish ──────────────────────────────────
    const logoBtn = document.getElementById('vendor-logo-btn');
    if (logoBtn) {
        logoBtn.addEventListener('click', function () {
            clickCount++;

            // Taymer: 3 soniyada bosilmasa hisoblagich nolga qaytadi
            clearTimeout(clickTimer);
            clickTimer = setTimeout(() => { clickCount = 0; }, 3000);

            if (clickCount >= CLICK_NEEDED) {
                clickCount = 0;
                clearTimeout(clickTimer);

                // Agar vendor allaqachon ochiq bo'lsa — yopamiz
                if (localStorage.getItem(LS_KEY) === '1') {
                    vendorKorsatish(false);
                    return;
                }

                // Yopiq bo'lsa — parol so'raymiz
                document.getElementById('vendor-pass-input').value = '';
                document.getElementById('vendor-pass-error').style.display = 'none';
                const modal = new bootstrap.Modal(document.getElementById('vendorModal'));
                modal.show();
                setTimeout(() => document.getElementById('vendor-pass-input').focus(), 400);
            }
        });
    }

    // ── Parol ko'rsatish/yashirish ───────────────────────────
    document.getElementById('vendor-pass-toggle').addEventListener('click', function () {
        const inp = document.getElementById('vendor-pass-input');
        const icon = this.querySelector('i');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            inp.type = 'password';
            icon.className = 'bi bi-eye';
        }
    });

    // ── Parol tekshiruv ───────────────────────────────────────
    function checkPass() {
        const val = document.getElementById('vendor-pass-input').value;
        const errEl = document.getElementById('vendor-pass-error');
        if (val === VENDOR_PASS) {
            errEl.style.display = 'none';
            bootstrap.Modal.getInstance(document.getElementById('vendorModal')).hide();
            vendorKorsatish(true);
        } else {
            errEl.style.display = 'block';
            document.getElementById('vendor-pass-input').value = '';
            document.getElementById('vendor-pass-input').focus();
        }
    }

    document.getElementById('vendor-pass-btn').addEventListener('click', checkPass);

    // Enter tugmasi bilan ham tasdiqlash
    document.getElementById('vendor-pass-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') checkPass();
    });

    // ── Vendor menyuni yopish: yana 10 marta bosish ───────────
    // (ixtiyoriy: vendor ochiq bo'lsa logo bosish yopadi)
    let hideClickCount = 0;
    let hideTimer = null;
})();
</script>

{{-- ── Global Kvitansiya Modal (barcha sahifalarda) ──────── --}}
<div class="modal fade" id="kvitansiyaGlobalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:920px">
    <div class="modal-content border-0 shadow-lg" style="border-radius:12px;overflow:hidden">
      <div class="modal-header py-2" style="background:#1a3a2a">
        <h6 class="modal-title text-white fw-bold mb-0">
          <i class="bi bi-receipt me-2"></i>Kvitansiya — Kassa Kirim Orderi
        </h6>
        <div class="d-flex gap-2 ms-auto me-2">
          <button type="button" class="btn btn-sm btn-success px-3"
                  onclick="gKvChopEt()" title="Chop etish (Ctrl+P)">
            <i class="bi bi-printer me-1"></i> Chop etish
          </button>
          <a id="gKv-new-tab" href="#" target="_blank"
             class="btn btn-sm btn-outline-light px-2" title="Yangi oynada">
            <i class="bi bi-box-arrow-up-right"></i>
          </a>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <iframe id="gKv-frame" src="about:blank"
              style="width:100%;height:520px;border:none;display:block"
              onload="gKvFrameYuklandi(this)">
      </iframe>
    </div>
  </div>
</div>

<script>
// ── Global Kvitansiya Modal ───────────────────────────────────────
var gKvModalObj = null;

function kvitansiyaModalOch(url) {
    var frame = document.getElementById('gKv-frame');
    document.getElementById('gKv-new-tab').href = url;
    frame.style.height  = '520px';
    frame.style.display = 'block';
    frame.src = url;
    if (!gKvModalObj) gKvModalObj = new bootstrap.Modal(document.getElementById('kvitansiyaGlobalModal'));
    gKvModalObj.show();
}

// Flash xabar dagi tugma uchun
function flashKvitansiyaOch(url) { kvitansiyaModalOch(url); }

function gKvFrameYuklandi(frame) {
    if (!frame.src || frame.src === 'about:blank') return;
    var h = 620;
    try {
        var doc2 = frame.contentDocument || frame.contentWindow.document;
        h = Math.min(Math.max(500, doc2.body.scrollHeight + 20), 780);
    } catch(e) {}
    frame.style.height  = h + 'px';
    frame.style.display = 'block';
}

function gKvChopEt() {
    var frame = document.getElementById('gKv-frame');
    try {
        frame.contentWindow.print();
    } catch(e) {
        var win = window.open(document.getElementById('gKv-new-tab').href, '_blank');
        if (win) setTimeout(function(){ win.print(); }, 900);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('kvitansiyaGlobalModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            var f = document.getElementById('gKv-frame');
            f.src = 'about:blank';
            f.style.height  = '520px';
            f.style.display = 'block';
        });
    }
});
</script>

{{-- ── Global Hujjat Ko'rish/Tahrirlash Modal (barcha sahifalarda) ──────── --}}
<div class="modal fade" id="hujjatGlobalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width:960px">
    <div class="modal-content border-0 shadow-lg" style="border-radius:12px;overflow:hidden">
      <div class="modal-header py-2" style="background:#1a3a2a">
        <h6 class="modal-title text-white fw-bold mb-0" id="hujjat-modal-title">
          <i class="bi bi-file-earmark-text me-2"></i>Hujjat
        </h6>
        <div class="d-flex gap-2 ms-auto me-2">
          <span id="hujjat-tahrirlash-info" class="badge bg-warning text-dark align-self-center d-none">
            <i class="bi bi-pencil me-1"></i>Tahrirlash rejimi
          </span>
          <button type="button" class="btn btn-sm btn-success px-3"
                  onclick="hujjatChopEt()" title="Chop etish (Ctrl+P)">
            <i class="bi bi-printer me-1"></i> Chop etish
          </button>
          <a id="hujjat-new-tab" href="#" target="_blank"
             class="btn btn-sm btn-outline-light px-2" title="Yangi oynada">
            <i class="bi bi-box-arrow-up-right"></i>
          </a>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <iframe id="hujjat-frame" src="about:blank"
              style="width:100%;height:75vh;border:none;display:block;background:#fff"
              onload="hujjatFrameYuklandi(this)">
      </iframe>
    </div>
  </div>
</div>

<script>
// ── Global Hujjat Ko'rish/Tahrirlash Modal ────────────────────────
var hujjatModalObj = null;

function hujjatModalOch(url, nomi, tahrirlanadimi) {
    var frame = document.getElementById('hujjat-frame');
    frame.dataset.tahrirlanadi = tahrirlanadimi ? '1' : '0';
    document.getElementById('hujjat-new-tab').href = url;
    document.getElementById('hujjat-modal-title').innerHTML =
        '<i class="bi bi-' + (tahrirlanadimi ? 'pencil' : 'eye') + ' me-2"></i>' + nomi;
    document.getElementById('hujjat-tahrirlash-info').classList.toggle('d-none', !tahrirlanadimi);
    frame.src = url;
    if (!hujjatModalObj) hujjatModalObj = new bootstrap.Modal(document.getElementById('hujjatGlobalModal'));
    hujjatModalObj.show();
}

function hujjatFrameYuklandi(frame) {
    if (!frame.src || frame.src === 'about:blank') return;
    // Tahrirlash rejimida — hujjat matnini to'g'ridan-to'g'ri (contenteditable)
    // tahrirlash imkonini yoqamiz. Hech narsa bazaga saqlanmaydi — faqat
    // chop etishdan oldin vaqtinchalik tuzatish uchun (xato so'z, manzil va h.k.).
    if (frame.dataset.tahrirlanadi === '1') {
        try {
            var doc = frame.contentDocument || frame.contentWindow.document;
            doc.body.setAttribute('contenteditable', 'true');
            doc.body.style.cursor = 'text';
        } catch(e) {}
    }
}

function hujjatChopEt() {
    var frame = document.getElementById('hujjat-frame');
    try {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    } catch(e) {
        var win = window.open(document.getElementById('hujjat-new-tab').href, '_blank');
        if (win) setTimeout(function(){ win.print(); }, 900);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var hModal = document.getElementById('hujjatGlobalModal');
    if (hModal) {
        hModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('hujjat-frame').src = 'about:blank';
        });
    }
});
</script>

{{-- ── Litsenziya bloklash modali ──────────────────── --}}
@auth
@if(Auth::user()->isAdmin() || Auth::user()->isMenejerYoki())
<div class="modal fade" id="litsenziyaBlokModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">
      <div class="text-center pt-4 pb-2">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#fef3c7,#fde68a);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
          <i class="bi bi-lock-fill" style="font-size:32px;color:#d97706"></i>
        </div>
        <span class="badge rounded-pill" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:6px 14px;font-size:13px">
          <span id="litsenziya-blok-modul-nomi">—</span> — bloklangan
        </span>
        <h5 class="fw-bold mt-2 mb-1">Litsenziya muddati tugagan</h5>
        <p class="text-muted small px-4 mb-3">
          Bu amalni bajarish uchun litsenziya muddatini yangilang. Quyidagi aloqa orqali
          administratorga murojaat qiling.
        </p>
      </div>
      <div class="px-4 pb-2 d-flex flex-column gap-2">
        <a href="https://t.me/ads253" target="_blank" class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none text-dark">
          <span class="d-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:#229ed9;color:#fff"><i class="bi bi-telegram"></i></span>
          <span><b class="d-block" style="font-size:13.5px">Telegram</b><span class="text-muted small">@ads253</span></span>
        </a>
        <a href="tel:+998945510600" class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none text-dark">
          <span class="d-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:#16a34a;color:#fff"><i class="bi bi-telephone-fill"></i></span>
          <span><b class="d-block" style="font-size:13.5px">Telefon</b><span class="text-muted small">+998 94 551 06 00</span></span>
        </a>
        <a href="mailto:adilshod.82@gmail.com" class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none text-dark">
          <span class="d-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:#ef4444;color:#fff"><i class="bi bi-envelope-fill"></i></span>
          <span><b class="d-block" style="font-size:13.5px">Email</b><span class="text-muted small">adilshod.82@gmail.com</span></span>
        </a>
      </div>
      <div class="px-4 pb-4 pt-2">
        <div class="bg-light rounded-3 p-3">
          <div class="text-muted small fw-bold mb-2 text-uppercase" style="font-size:11px">Do'kon kodingiz</div>
          <div class="font-monospace fw-bold text-center bg-white border rounded-3 py-2 mb-2" style="color:#4f46e5;font-size:18px;letter-spacing:1px" id="litsenziya-blok-dukon-kodi">{{ \App\Services\Litsenziya::dukonKodiChiroyli() }}</div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light flex-fill btn-sm" onclick="litsenziyaModalKodNusxa()"><i class="bi bi-clipboard me-1"></i>Nusxa olish</button>
            <button type="button" class="btn btn-primary flex-fill btn-sm" style="background:#229ed9;border-color:#229ed9" onclick="litsenziyaModalTelegramgaYubor()"><i class="bi bi-send-fill me-1"></i>Telegramga yuborish</button>
          </div>
        </div>
      </div>
      <div class="px-4 pb-4">
        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-1"></i>Yopish
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ── Litsenziya TARIF LIMITI modali (litsenziya FAOL bo'lsa ham ishlaydi) ──────────────────── --}}
<div class="modal fade" id="litsenziyaLimitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius:18px;overflow:hidden">
      <div class="text-center pt-4 pb-2">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#e0e7ff,#c7d2fe);display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
          <i class="bi bi-graph-up-arrow" style="font-size:32px;color:#4f46e5"></i>
        </div>
        <span class="badge rounded-pill" style="background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;padding:6px 14px;font-size:13px">
          <span id="litsenziya-limit-sarlavha">—</span>
        </span>
        <h5 class="fw-bold mt-2 mb-1">Tarif limiti</h5>
        <p class="text-muted small px-4 mb-3" id="litsenziya-limit-matn">—</p>
      </div>
      <div class="px-4 pb-2 d-flex flex-column gap-2">
        <a href="https://t.me/ads253" target="_blank" class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none text-dark">
          <span class="d-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:#229ed9;color:#fff"><i class="bi bi-telegram"></i></span>
          <span><b class="d-block" style="font-size:13.5px">Telegram</b><span class="text-muted small">@ads253</span></span>
        </a>
        <a href="tel:+998945510600" class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none text-dark">
          <span class="d-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:#16a34a;color:#fff"><i class="bi bi-telephone-fill"></i></span>
          <span><b class="d-block" style="font-size:13.5px">Telefon</b><span class="text-muted small">+998 94 551 06 00</span></span>
        </a>
        <a href="mailto:adilshod.82@gmail.com" class="d-flex align-items-center gap-2 p-2 border rounded-3 text-decoration-none text-dark">
          <span class="d-flex align-items-center justify-content-center rounded-3" style="width:36px;height:36px;background:#ef4444;color:#fff"><i class="bi bi-envelope-fill"></i></span>
          <span><b class="d-block" style="font-size:13.5px">Email</b><span class="text-muted small">adilshod.82@gmail.com</span></span>
        </a>
      </div>
      <div class="px-4 pb-4 pt-2">
        <div class="bg-light rounded-3 p-3">
          <div class="text-muted small fw-bold mb-2 text-uppercase" style="font-size:11px">Do'kon kodingiz</div>
          <div class="font-monospace fw-bold text-center bg-white border rounded-3 py-2 mb-2" style="color:#4f46e5;font-size:18px;letter-spacing:1px" id="litsenziya-limit-dukon-kodi">{{ \App\Services\Litsenziya::dukonKodiChiroyli() }}</div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light flex-fill btn-sm" onclick="litsenziyaLimitKodNusxa()"><i class="bi bi-clipboard me-1"></i>Nusxa olish</button>
            <button type="button" class="btn btn-primary flex-fill btn-sm" style="background:#229ed9;border-color:#229ed9" onclick="litsenziyaLimitTelegramgaYubor()"><i class="bi bi-send-fill me-1"></i>Telegramga yuborish</button>
          </div>
        </div>
      </div>
      <div class="px-4 pb-4">
        <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">
          <i class="bi bi-x-lg me-1"></i>Yopish
        </button>
      </div>
    </div>
  </div>
</div>

<script>
window.LITSENZIYA_BLOK = {
    bloklangan: {
        mijoz: @json(\App\Services\Litsenziya::moduleBloklanganmi('mijoz')),
        tovar: @json(\App\Services\Litsenziya::moduleBloklanganmi('tovar')),
        shartnoma: @json(\App\Services\Litsenziya::moduleBloklanganmi('shartnoma')),
        hisobot: @json(\App\Services\Litsenziya::moduleBloklanganmi('hisobot')),
        dashboard: @json(\App\Services\Litsenziya::moduleBloklanganmi('dashboard'))
    },
    limit: {
        mijoz: @json(\App\Services\Litsenziya::limitOshganmi('mijoz')),
        tovar: @json(\App\Services\Litsenziya::limitOshganmi('tovar')),
        shartnoma: @json(\App\Services\Litsenziya::limitOshganmi('shartnoma'))
    },
    limitMax: {
        mijoz: @json(\App\Services\Litsenziya::limitQiymati('mijoz')),
        tovar: @json(\App\Services\Litsenziya::limitQiymati('tovar')),
        shartnoma: @json(\App\Services\Litsenziya::limitQiymati('shartnoma'))
    },
    posOchiq: @json(\App\Services\Litsenziya::posOchiqmi())
};

function litsenziyaLimitKorsat(sarlavha, matn) {
    document.getElementById('litsenziya-limit-sarlavha').textContent = sarlavha;
    document.getElementById('litsenziya-limit-matn').textContent = matn;
    var elem = document.getElementById('litsenziyaLimitModal');
    bootstrap.Modal.getOrCreateInstance(elem).show();
}

function litsenziyaTekshir(modul, modulNomi) {
    if (window.LITSENZIYA_BLOK && window.LITSENZIYA_BLOK.bloklangan[modul]) {
        document.getElementById('litsenziya-blok-modul-nomi').textContent = modulNomi;
        var elem = document.getElementById('litsenziyaBlokModal');
        bootstrap.Modal.getOrCreateInstance(elem).show();
        return false;
    }
    if (window.LITSENZIYA_BLOK && window.LITSENZIYA_BLOK.limit && window.LITSENZIYA_BLOK.limit[modul]) {
        var max = window.LITSENZIYA_BLOK.limitMax[modul];
        litsenziyaLimitKorsat(modulNomi, "Joriy tarifingizda " + modulNomi.toLowerCase() + " limiti (" + max + " ta) ga yetdingiz. Tarifingizni kengaytirish uchun administratorga murojaat qiling.");
        return false;
    }
    return true;
}

function litsenziyaPosTekshir() {
    if (window.LITSENZIYA_BLOK && !window.LITSENZIYA_BLOK.posOchiq) {
        litsenziyaLimitKorsat('Naqd sotish (POS)', "Joriy tarifingizda naqd sotish (POS) bo'limi yopiq. Bu funksiyani ochish uchun tarifingizni kengaytiring.");
        return false;
    }
    return true;
}

function litsenziyaModalKodNusxa() {
    var kod = document.getElementById('litsenziya-blok-dukon-kodi').textContent.trim();
    navigator.clipboard.writeText(kod);
}

function litsenziyaModalTelegramgaYubor() {
    var kod = document.getElementById('litsenziya-blok-dukon-kodi').textContent.trim();
    var xabar = "Salom! Litsenziya muddati tugadi. Do'kon kodim: " + kod;
    navigator.clipboard.writeText(xabar).then(function() {
        window.open('https://t.me/ads253', '_blank');
    });
}

function litsenziyaLimitKodNusxa() {
    var kod = document.getElementById('litsenziya-limit-dukon-kodi').textContent.trim();
    navigator.clipboard.writeText(kod);
}

function litsenziyaLimitTelegramgaYubor() {
    var kod = document.getElementById('litsenziya-limit-dukon-kodi').textContent.trim();
    var xabar = "Salom! Tarifimni kengaytirish kerak. Do'kon kodim: " + kod;
    navigator.clipboard.writeText(xabar).then(function() {
        window.open('https://t.me/ads253', '_blank');
    });
}
</script>
@endif
@endauth

@stack('scripts')
</body>
</html>
