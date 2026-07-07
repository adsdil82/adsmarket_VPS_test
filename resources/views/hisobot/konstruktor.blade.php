@extends('layouts.app')
@section('title', 'Hisobot konstruktori')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('hisobotlar.index') }}">Hisobotlar</a></li>
    <li class="breadcrumb-item active">Konstruktor</li>
@endsection

@push('styles')
<style>
/* ── Office 2022 uslubidagi Ribbon ────────────────────────────── */
.ribbon-wrap { border:1px solid #c7d7f8; border-radius:8px; overflow:hidden; background:#fff; margin-bottom:12px; }
.ribbon-tabs { display:flex; background:#f3f6fd; border-bottom:1px solid #c7d7f8; overflow-x:auto; }
.ribbon-tab {
    padding:9px 18px; font-size:.82rem; font-weight:700; color:#475569; cursor:pointer;
    border-right:1px solid #e2e8f4; white-space:nowrap; user-select:none; transition:all .12s;
}
.ribbon-tab i { margin-right:5px; }
.ribbon-tab:hover { background:#e7eefc; color:#1d4ed8; }
.ribbon-tab.active { background:#fff; color:#1d4ed8; border-bottom:3px solid #2563eb; margin-bottom:-1px; }

.ribbon-toolbar { display:flex; gap:0; background:linear-gradient(180deg,#fbfdff,#f3f6fd); padding:8px 10px; overflow-x:auto; }
.ribbon-group {
    display:flex; flex-direction:column; justify-content:space-between; align-items:center;
    border-right:1px solid #dbe4f5; padding:2px 12px; min-width:max-content;
}
.ribbon-group:last-child { border-right:none; }
.ribbon-group-body { display:flex; gap:4px; align-items:flex-end; flex-wrap:wrap; justify-content:flex-start; flex:1; }
.rb-grid-4 { display:grid; grid-template-columns:repeat(4,max-content); gap:3px; }
.rb-grid-4 .rb-mini { white-space:nowrap; padding:4px 8px; }
.rb-grid-3 { display:grid; grid-template-columns:repeat(3,max-content); gap:3px; }
.rb-grid-3 .rb-mini { white-space:nowrap; }
.ribbon-group-caption { font-size:.64rem; color:#7a89a8; text-transform:uppercase; letter-spacing:.03em; margin-top:4px; font-weight:700; }

.rb-btn {
    display:flex; flex-direction:column; align-items:center; gap:2px; background:none; border:1px solid transparent;
    border-radius:5px; padding:5px 8px; font-size:.66rem; font-weight:600; color:#334155; cursor:pointer; min-width:52px;
}
.rb-btn i { font-size:1.05rem; }
.rb-btn:hover { background:#e7eefc; border-color:#c7d7f8; }
.rb-btn.rb-primary { color:#1d4ed8; }
.rb-btn.rb-success { color:#15803d; }
.rb-btn.rb-danger  { color:#b91c1c; }
.rb-mini { padding:3px 9px; font-size:.68rem; font-weight:700; border-radius:4px; border:1px solid #c7d7f8; background:#fff; color:#334155; cursor:pointer; white-space:nowrap; }
.rb-mini:hover { background:#e7eefc; border-color:#2563eb; color:#1d4ed8; }
.rb-mini.active { background:#2563eb; color:#fff; border-color:#2563eb; }
.rb-inp { font-size:.76rem; padding:3px 6px; border:1px solid #c7d7f8; border-radius:4px; }
.rb-shart-box { display:flex; flex-direction:column; gap:2px; }
.rb-stack { display:flex; flex-direction:column; gap:3px; }
.rb-shart-box label { font-size:.62rem; color:#7a89a8; font-weight:700; text-transform:uppercase; }

/* ── Ustunlar tanlash modali ───────────────────────────────────── */
.col-check { display:flex; gap:8px; align-items:center; padding:6px 8px; border-radius:5px; cursor:pointer; border:1px solid transparent; }
.col-check:hover { background:#eef3ff; border-color:#c7d7f8; }
.col-check input { width:15px; height:15px; cursor:pointer; }

/* ── Excel-uslubidagi natija jadvali ──────────────────────────── */
.bank-table { border-collapse:collapse; font-size:.8rem; width:100%; }
.bank-table, .bank-table th, .bank-table td { border:1px solid #d7e2f5; }
.bank-table thead { position:sticky; top:0; z-index:6; }
.bank-table thead th {
    background:linear-gradient(180deg,#3b82f6,#1d4ed8); color:#fff; font-weight:800;
    font-size:.66rem; letter-spacing:.03em; text-transform:uppercase; padding:6px 8px; white-space:nowrap; text-align:right;
}
.bank-table thead th.tl { text-align:left; }
.bank-table tbody tr { height:26px; }
.bank-table tbody tr:hover td { background:#e0edff !important; }
.bank-table tbody tr:nth-child(odd)  td { background:#ffffff; }
.bank-table tbody tr:nth-child(even) td { background:#eef4ff; }
.bank-table tbody td { padding:4px 8px; vertical-align:middle; white-space:nowrap; font-size:.8rem; }
.bank-table tbody td.num { font-family:'Roboto Mono','Courier New',monospace; text-align:right; font-weight:700; color:#0f172a; }
.bank-wrap-result { overflow:auto; max-height:calc(100vh - 380px); min-height:200px; border:1px solid #93c5fd; border-radius:0 0 6px 6px; }
.natija-footer {
    display:flex; justify-content:space-between; align-items:center; background:linear-gradient(90deg,#eef3ff,#e8f0fe);
    border:1px solid #93c5fd; border-top:none; border-radius:0 0 6px 6px; padding:8px 14px; font-size:.8rem;
}

/* ── Moliyaviy bank-daraxt (tree) jadvali ─────────────────────── */
.moliyaviy-tur-btn.active { background:#7c2d12; color:#fff; border-color:#7c2d12; }
#moliyaviy-tab.active-tab { background:#fff7ed; color:#7c2d12; border-bottom:3px solid #7c2d12; margin-bottom:-1px; }
.mf-bolim-row td { background:linear-gradient(90deg,#eef3ff,#dbe4f9) !important; font-weight:800; color:#1e3a8a; padding:7px 10px !important; border-top:2px solid #93c5fd; }
.mf-qator-row td:first-child { padding-left:26px !important; }
.mf-jami-row td { background:linear-gradient(90deg,#fef9c3,#fde68a) !important; font-weight:800; color:#7c2d12; border-top:1px solid #fbbf24; }
.mf-yakuniy-row td { background:linear-gradient(90deg,#14532d,#15803d) !important; color:#fff !important; font-weight:800; font-size:.86rem; padding:8px 10px !important; }
.mf-positive { color:#15803d !important; }
.mf-negative { color:#b91c1c !important; }
.mf-mock-badge { font-size:.7rem; background:#fef3c7; color:#92400e; border:1px solid #fde68a; border-radius:3px; padding:1px 4px; margin-left:6px; }
.mf-auto-badge { font-size:.7rem; background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:3px; padding:1px 4px; margin-left:6px; }
.mf-ogohlantirish { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:6px; padding:10px 14px; margin-bottom:8px; font-size:.82rem; }

@media print {
    .ribbon-wrap, .natija-footer, .no-print { display:none !important; }
    .bank-wrap-result { max-height:none !important; overflow:visible !important; border:none !important; }
}
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('hisobotlar.konstruktor.hisobot') }}" id="konstruktor-form">
@csrf
<input type="hidden" name="modul" id="modul-input" value="{{ $modul ?? 'kreditlar' }}">
<input type="hidden" name="sana_turi" id="sana-turi-input" value="{{ $sanaTuri ?? 'bu_oy' }}">
<input type="hidden" name="guruhlash" id="guruhlash-input" value="{{ $guruhlash ?? '' }}">

<div class="ribbon-wrap no-print">
    {{-- ── Tab menyu (modullar) ─────────────────────────────────── --}}
    <div class="ribbon-tabs">
        @foreach($modullar as $key => $mod)
        <div class="ribbon-tab {{ ($modul ?? 'kreditlar') === $key ? 'active' : '' }}" onclick="modulTanla('{{ $key }}')">
            <i class="bi {{ $mod['icon'] }}"></i>{{ $mod['nomi'] }}
        </div>
        @endforeach
        @if($moliyaviyRuxsat ?? false)
        <div class="ribbon-tab" id="moliyaviy-tab" onclick="moliyaviyTabOch()" style="color:#7c2d12"><i class="bi bi-bank2"></i>Moliyaviy</div>
        @endif
        <div class="ribbon-tab" onclick="shablonPanelOch()"><i class="bi bi-bookmark-star"></i>Shablonlar</div>
        <a href="{{ route('hisobotlar.index') }}" class="ribbon-tab ms-auto no-print" style="border-right:none" title="Ortga">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>

    {{-- ── Ribbon asboblar paneli ────────────────────────────────── --}}
    <div class="ribbon-toolbar" id="oddiy-toolbar">

        {{-- Davr guruhi --}}
        <div class="ribbon-group">
            <div class="ribbon-group-body">
                <div class="rb-stack">
                    <div class="rb-shart-box">
                        <label>Dan</label>
                        <input type="date" name="dan_sana" id="dan-sana" class="rb-inp" value="{{ $danSana ?? now()->startOfMonth()->toDateString() }}" onchange="sanaTuriTanla('maxsus')">
                    </div>
                    <div class="rb-shart-box">
                        <label>Gacha</label>
                        <input type="date" name="gacha_sana" id="gacha-sana" class="rb-inp" value="{{ $gachaSana ?? now()->toDateString() }}" onchange="sanaTuriTanla('maxsus')">
                    </div>
                </div>
                <div class="rb-grid-3">
                    <button type="button" class="rb-mini" data-sana="bugun" onclick="sanaTezkor('bugun')">Bugun</button>
                    <button type="button" class="rb-mini" data-sana="kecha" onclick="sanaTezkor('kecha')">Kecha</button>
                    <button type="button" class="rb-mini active" data-sana="bu_oy" onclick="sanaTezkor('bu_oy')">Joriy oy</button>
                    <button type="button" class="rb-mini" data-sana="otgan_oy" onclick="sanaTezkor('otgan_oy')">O'tgan oy</button>
                    <button type="button" class="rb-mini" data-sana="bu_chorak" onclick="sanaTezkor('bu_chorak')">Joriy chorak</button>
                    <button type="button" class="rb-mini" data-sana="bu_yil" onclick="sanaTezkor('bu_yil')">Joriy yil</button>
                </div>
                @if(Auth::user()->isAdmin())
                <div class="rb-shart-box">
                    <label>Filial</label>
                    <select name="filial_id" class="rb-inp">
                        <option value="">Barchasi</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}" {{ request('filial_id') == $f->id ? 'selected' : '' }}>{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="ribbon-group-caption">Davr</div>
        </div>

        {{-- Ustunlar guruhi --}}
        <div class="ribbon-group">
            <div class="ribbon-group-body">
                <button type="button" class="rb-btn" onclick="ustunlarModalOch()">
                    <i class="bi bi-layout-three-columns"></i>Ustunlar
                </button>
            </div>
            <div class="ribbon-group-caption">Ustunlar</div>
        </div>

        {{-- Filtrlar guruhi (modulga xos) --}}
        <div class="ribbon-group">
            <div class="ribbon-group-body" id="shartlar-body"></div>
            <div class="ribbon-group-caption">Filtrlar</div>
        </div>

        {{-- Guruhlash guruhi (faqat qo'llab-quvvatlaydigan modullarda) --}}
        <div class="ribbon-group" id="guruhlash-group" style="display:none">
            <div class="ribbon-group-body">
                <select class="rb-inp" id="guruhlash-select" onchange="document.getElementById('guruhlash-input').value=this.value">
                    <option value="">Guruhlanmasin</option>
                </select>
            </div>
            <div class="ribbon-group-caption">Guruhlash</div>
        </div>

        {{-- Amallar guruhi --}}
        <div class="ribbon-group">
            <div class="ribbon-group-body">
                <button type="submit" class="rb-btn rb-primary"><i class="bi bi-play-fill"></i>Shakllantirish</button>
                <button type="submit" class="rb-btn"><i class="bi bi-arrow-clockwise"></i>Yangilash</button>
                <button type="button" class="rb-btn" onclick="tozalash()"><i class="bi bi-eraser"></i>Tozalash</button>
                <button type="submit" formaction="{{ route('hisobotlar.konstruktor.excel') }}" class="rb-btn rb-success"><i class="bi bi-file-earmark-excel"></i>Excel</button>
                <button type="submit" formaction="{{ route('hisobotlar.konstruktor.csv') }}" class="rb-btn rb-success"><i class="bi bi-filetype-csv"></i>CSV</button>
                <button type="button" class="rb-btn" onclick="window.print()"><i class="bi bi-printer"></i>Chop etish</button>
                <button type="button" class="rb-btn" onclick="shablonSaqlashModalOch()"><i class="bi bi-save"></i>Shablon saqlash</button>
                <button type="button" class="rb-btn" onclick="shablonPanelOch()"><i class="bi bi-folder2-open"></i>Shablon ochish</button>
            </div>
            <div class="ribbon-group-caption">Amallar</div>
        </div>
    </div>

    {{-- ── Moliyaviy tab uchun alohida ribbon (default holatda berkitilgan) ─── --}}
    @if($moliyaviyRuxsat ?? false)
    <div class="ribbon-toolbar" id="moliyaviy-toolbar" style="display:none">
        <div class="ribbon-group">
            <div class="ribbon-group-body rb-grid-4" id="moliyaviy-turlar-body">
                @foreach($moliyaviyTurlari as $key => $t)
                <button type="button" class="rb-mini moliyaviy-tur-btn {{ $loop->first ? 'active' : '' }}" data-turi="{{ $key }}" onclick="moliyaviyTurTanla('{{ $key }}')">
                    <i class="bi {{ $t['icon'] }} me-1"></i>{{ $t['nomi'] }}
                </button>
                @endforeach
            </div>
            <div class="ribbon-group-caption">Hisobot turi</div>
        </div>

        <div class="ribbon-group">
            <div class="ribbon-group-body">
                <div class="rb-stack">
                    <div class="rb-shart-box">
                        <label>Dan</label>
                        <input type="date" id="mf-dan-sana" class="rb-inp" value="{{ now()->startOfMonth()->toDateString() }}">
                    </div>
                    <div class="rb-shart-box">
                        <label>Gacha</label>
                        <input type="date" id="mf-gacha-sana" class="rb-inp" value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div class="rb-grid-3">
                    <button type="button" class="rb-mini" onclick="moliyaviySanaTezkor('bugun')">Bugun</button>
                    <button type="button" class="rb-mini" onclick="moliyaviySanaTezkor('bu_oy')">Joriy oy</button>
                    <button type="button" class="rb-mini" onclick="moliyaviySanaTezkor('otgan_oy')">O'tgan oy</button>
                    <button type="button" class="rb-mini" onclick="moliyaviySanaTezkor('bu_chorak')">Joriy chorak</button>
                    <button type="button" class="rb-mini" onclick="moliyaviySanaTezkor('bu_yil')">Joriy yil</button>
                </div>
                @if(Auth::user()->isAdmin())
                <div class="rb-shart-box">
                    <label>Filial</label>
                    <select id="mf-filial-id" class="rb-inp">
                        <option value="">Barchasi</option>
                        @foreach($filiallar as $f)
                        <option value="{{ $f->id }}">{{ $f->nomi }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="ribbon-group-caption">Davr</div>
        </div>

        <div class="ribbon-group" id="moliyaviy-solishtirma-group" style="display:none">
            <div class="ribbon-group-body">
                <div class="rb-shart-box">
                    <label>Ichki hisobot</label>
                    <select id="mf-ichki-turi" class="rb-inp">
                        <option value="foyda_zarar">Foyda va zarar</option>
                        <option value="xarajatlar">Xarajatlar</option>
                        <option value="daromadlar">Daromadlar</option>
                        <option value="cash_flow">Cash Flow</option>
                        <option value="balans">Balans</option>
                    </select>
                </div>
                <div class="rb-shart-box">
                    <label>Solishtirish davri</label>
                    <select id="mf-oldingi-davr-turi" class="rb-inp">
                        <option value="otgan_oy">O'tgan oy</option>
                        <option value="otgan_davr">O'tgan (teng) davr</option>
                        <option value="otgan_yil">O'tgan yil shu davri</option>
                        <option value="maxsus">Ixtiyoriy sana oralig'i</option>
                    </select>
                </div>
                <div class="rb-stack" id="mf-oldingi-maxsus-box" style="display:none">
                    <div class="rb-shart-box">
                        <label>Oldingi dan</label>
                        <input type="date" id="mf-oldingi-dan" class="rb-inp">
                    </div>
                    <div class="rb-shart-box">
                        <label>Oldingi gacha</label>
                        <input type="date" id="mf-oldingi-gacha" class="rb-inp">
                    </div>
                </div>
            </div>
            <div class="ribbon-group-caption">Solishtirma</div>
        </div>

        <div class="ribbon-group">
            <div class="ribbon-group-body">
                <button type="button" class="rb-btn rb-primary" onclick="moliyaviyShakllantirish()"><i class="bi bi-play-fill"></i>Shakllantirish</button>
                <button type="button" class="rb-btn rb-success" onclick="moliyaviyEksport('excel')"><i class="bi bi-file-earmark-excel"></i>Excel</button>
                <button type="button" class="rb-btn rb-success" onclick="moliyaviyEksport('csv')"><i class="bi bi-filetype-csv"></i>CSV</button>
                <button type="button" class="rb-btn" onclick="window.print()"><i class="bi bi-printer"></i>Chop etish</button>
            </div>
            <div class="ribbon-group-caption">Amallar</div>
        </div>
    </div>
    @endif
</div>

<div id="moliyaviy-panel" style="display:none">
    <div id="moliyaviy-ogohlantirish"></div>
    <div class="bank-wrap-result shadow-sm">
        <table class="bank-table" id="moliyaviy-table">
            <thead><tr><th class="tl">Bo'lim / Modda</th><th>Summa</th></tr></thead>
            <tbody id="moliyaviy-tbody">
                <tr><td colspan="2" class="text-center text-muted py-5">Yuqoridan hisobot turini tanlab, "Shakllantirish" tugmasini bosing.</td></tr>
            </tbody>
        </table>
    </div>
    <div class="natija-footer no-print">
        <span id="moliyaviy-davr-matni" class="text-muted"></span>
    </div>
</div>

{{-- ── Natija (oddiy modullar) ──────────────────────────────────────── --}}
<div id="oddiy-panel">
@if($natija !== null)
<div class="bank-wrap-result shadow-sm">
    <table class="bank-table">
        <thead>
            <tr>
                @if(!empty($natija['rows']))
                    @foreach(array_keys($natija['rows'][0]) as $col)
                    @if(empty($ustunlar) || in_array($col,$ustunlar))
                    <th class="{{ is_numeric($natija['rows'][0][$col] ?? null) ? '' : 'tl' }}">{{ $modullar[$modul]['ustunlar'][$col] ?? $col }}</th>
                    @endif
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($natija['rows'] as $row)
            <tr>
                @foreach($row as $col => $val)
                @if(empty($ustunlar) || in_array($col,$ustunlar))
                <td class="{{ (is_numeric($val) && $val !== '') ? 'num' : '' }}">
                    @if(is_numeric($val) && abs((float)$val) >= 100)
                        {{ number_format((float)$val,0,'.',' ') }}
                    @else
                        {{ $val }}
                    @endif
                </td>
                @endif
                @endforeach
            </tr>
            @empty
            <tr><td class="text-center text-muted py-4">Ma'lumot topilmadi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="natija-footer no-print">
    <span><i class="bi bi-table me-1"></i>Jami: <strong>{{ $natija['soni'] }}</strong> qator</span>
    @if($natija['soni'] >= 5000)
    <span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Maksimal 5000 qator ko'rsatildi — to'liq ma'lumot uchun Excel/CSV yuklab oling.</span>
    @endif
    <span class="text-muted">{{ $danSana }} — {{ $gachaSana }}</span>
</div>
@else
<div class="ribbon-wrap">
    <div class="p-5 text-center text-muted">
        <i class="bi bi-tools fs-2 d-block mb-2" style="color:#6366f1;opacity:.4"></i>
        <div>Yuqoridagi tab orqali hisobot turini tanlang, filtrlarni sozlang</div>
        <div>so'ng <strong>"Shakllantirish"</strong> tugmasini bosing.</div>
    </div>
</div>
@endif
</div>

{{-- ── Ustunlar tanlash modali ────────────────────────────────────── --}}
<div class="modal fade" id="ustunlar-modal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(90deg,#1e3a8a,#1d4ed8);color:#fff">
                <h6 class="mb-0 fw-bold"><i class="bi bi-layout-three-columns me-2"></i>Ustunlarni tanlang</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="rb-mini" onclick="barchasini(true)" style="color:#15803d">Barchasini belgilash</span>
                    <span class="rb-mini" onclick="barchasini(false)" style="color:#b91c1c">Tozalash</span>
                </div>
                <div id="ustunlar-body" class="row row-cols-2 row-cols-md-3 g-1"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="bi bi-check-lg me-1"></i>Qo'llash</button>
            </div>
        </div>
    </div>
</div>
</form>

{{-- ── Shablon saqlash modali ────────────────────────────────────── --}}
<div class="modal fade" id="shablon-saqlash-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(90deg,#1e3a8a,#1d4ed8);color:#fff">
                <h6 class="mb-0 fw-bold"><i class="bi bi-save me-2"></i>Shablon sifatida saqlash</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-medium small">Shablon nomi</label>
                <input type="text" id="shablon-nomi" class="form-control" placeholder="Masalan: Oylik savdo — filiallar bo'yicha">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="button" class="btn btn-primary" onclick="shablonSaqlashYubor()">Saqlash</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Shablonlar ro'yxati modali ────────────────────────────────── --}}
<div class="modal fade" id="shablon-royxat-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(90deg,#1e3a8a,#1d4ed8);color:#fff">
                <h6 class="mb-0 fw-bold"><i class="bi bi-bookmark-star me-2"></i>Saqlangan shablonlar</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="shablon-royxat-body" style="max-height:340px;overflow-y:auto">
                <div class="text-center text-muted small py-3"><div class="spinner-border spinner-border-sm"></div></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var MODULLAR = @json($modullar);
var JORIY_MODUL = '{{ $modul ?? "kreditlar" }}';
var TANLANGAN_USTUNLAR = @json($ustunlar ?? []);
var JORIY_SHARTLAR = @json($shartlar ?? []);
var JORIY_SANA_TURI = '{{ $sanaTuri ?? "bu_oy" }}';
var JORIY_GURUHLASH = '{{ $guruhlash ?? "" }}';

const SANA_TEZKOR_DIAPAZON = {
    bugun:     () => { const d = new Date().toISOString().slice(0,10); return [d,d]; },
    kecha:     () => { const d = new Date(Date.now()-86400000).toISOString().slice(0,10); return [d,d]; },
    bu_oy:     () => { const n = new Date(); const dan = new Date(n.getFullYear(), n.getMonth(), 1).toISOString().slice(0,10); return [dan, n.toISOString().slice(0,10)]; },
    otgan_oy:  () => { const n = new Date(); const dan = new Date(n.getFullYear(), n.getMonth()-1, 1); const gacha = new Date(n.getFullYear(), n.getMonth(), 0); return [dan.toISOString().slice(0,10), gacha.toISOString().slice(0,10)]; },
    bu_chorak: () => { const n = new Date(); const q = Math.floor(n.getMonth()/3); const dan = new Date(n.getFullYear(), q*3, 1); return [dan.toISOString().slice(0,10), n.toISOString().slice(0,10)]; },
    bu_yil:    () => { const n = new Date(); const dan = new Date(n.getFullYear(), 0, 1); return [dan.toISOString().slice(0,10), n.toISOString().slice(0,10)]; },
};

function modulTanla(key) {
    JORIY_MODUL = key;
    document.getElementById('modul-input').value = key;
    document.querySelectorAll('.ribbon-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.ribbon-tab').forEach(t => { if (t.textContent.trim() === (MODULLAR[key]?.nomi || '')) t.classList.add('active'); });
    ustunlarKorsatish(key);
    shartlarKorsatish(key);
    guruhlashKorsatish(key);
    moliyaviyPanelniYashir();
}

/* ── Moliyaviy tab ─────────────────────────────────────────────── */
function moliyaviyPanelniYashir() {
    var mtab = document.getElementById('moliyaviy-tab');
    if (mtab) mtab.classList.remove('active-tab');
    var toolbar = document.getElementById('moliyaviy-toolbar');
    if (toolbar) toolbar.style.display = 'none';
    var oddiyToolbar = document.getElementById('oddiy-toolbar');
    if (oddiyToolbar) oddiyToolbar.style.display = '';
    document.getElementById('moliyaviy-panel').style.display = 'none';
    document.getElementById('oddiy-panel').style.display = '';
}

function moliyaviyTabOch() {
    document.querySelectorAll('.ribbon-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('moliyaviy-tab').classList.add('active-tab');
    document.getElementById('oddiy-toolbar').style.display = 'none';
    document.getElementById('moliyaviy-toolbar').style.display = '';
    document.getElementById('oddiy-panel').style.display = 'none';
    document.getElementById('moliyaviy-panel').style.display = '';
}

var MF_JORIY_TURI = 'foyda_zarar';

function moliyaviyTurTanla(key) {
    MF_JORIY_TURI = key;
    document.querySelectorAll('.moliyaviy-tur-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('.moliyaviy-tur-btn[data-turi="'+key+'"]').classList.add('active');
    document.getElementById('moliyaviy-solishtirma-group').style.display = key === 'solishtirma' ? '' : 'none';
}

document.getElementById('mf-oldingi-davr-turi')?.addEventListener('change', function() {
    document.getElementById('mf-oldingi-maxsus-box').style.display = this.value === 'maxsus' ? '' : 'none';
});

const MF_SANA_TEZKOR = {
    bugun:     () => { const d = new Date().toISOString().slice(0,10); return [d,d]; },
    bu_oy:     () => { const n = new Date(); return [new Date(n.getFullYear(), n.getMonth(), 1).toISOString().slice(0,10), n.toISOString().slice(0,10)]; },
    otgan_oy:  () => { const n = new Date(); return [new Date(n.getFullYear(), n.getMonth()-1, 1).toISOString().slice(0,10), new Date(n.getFullYear(), n.getMonth(), 0).toISOString().slice(0,10)]; },
    bu_chorak: () => { const n = new Date(); const q = Math.floor(n.getMonth()/3); return [new Date(n.getFullYear(), q*3, 1).toISOString().slice(0,10), n.toISOString().slice(0,10)]; },
    bu_yil:    () => { const n = new Date(); return [new Date(n.getFullYear(), 0, 1).toISOString().slice(0,10), n.toISOString().slice(0,10)]; },
};

function moliyaviySanaTezkor(tur) {
    var [dan, gacha] = MF_SANA_TEZKOR[tur]();
    document.getElementById('mf-dan-sana').value = dan;
    document.getElementById('mf-gacha-sana').value = gacha;
}

function moliyaviySummaHtml(summa, mock) {
    var cls = summa > 0 ? 'mf-positive' : (summa < 0 ? 'mf-negative' : '');
    var badge = mock
        ? '<span class="mf-mock-badge" title="Bu modda avtomatik hisoblanmaydi — Pul Oqimlari > Balans hisoboti sahifasida qo\'lda kiritiladi"><i class="bi bi-hand-index-thumb"></i></span>'
        : '<span class="mf-auto-badge" title="Avtomatik hisoblanadi"><i class="bi bi-robot"></i></span>';
    return '<span class="'+cls+'">' + Number(summa).toLocaleString('uz-UZ') + " so'm</span>" + badge;
}

function moliyaviyNatijaChiz(natija, turi) {
    var tbody = document.getElementById('moliyaviy-tbody');
    var thead = document.querySelector('#moliyaviy-table thead tr');
    var solishtirma = turi === 'solishtirma';
    thead.innerHTML = solishtirma
        ? '<th class="tl">Bo\'lim / Modda</th><th>Joriy davr</th><th>Oldingi davr</th><th>Farq</th><th>Farq %</th>'
        : '<th class="tl">Bo\'lim / Modda</th><th>Summa</th>';

    var html = '';
    (natija.bolimlar || []).forEach(function(bolim) {
        html += '<tr class="mf-bolim-row"><td colspan="'+(solishtirma?5:2)+'">'+bolim.nomi+'</td></tr>';
        bolim.qatorlar.forEach(function(q) {
            if (solishtirma) {
                var farqCls = q.farq > 0 ? 'mf-positive' : (q.farq < 0 ? 'mf-negative' : '');
                var farqFoizi = (q.farq_foizi === null || q.farq_foizi === undefined) ? '<i class="bi bi-slash-circle" title="Solishtirish mumkin emas (oldingi davr 0)"></i>' : (q.farq_foizi + '%');
                var qBadge = q.mock
                    ? '<span class="mf-mock-badge" title="Qo\'lda kiritiladi"><i class="bi bi-hand-index-thumb"></i></span>'
                    : '<span class="mf-auto-badge" title="Avtomatik hisoblanadi"><i class="bi bi-robot"></i></span>';
                html += '<tr class="mf-qator-row"><td class="tl">'+q.nomi+qBadge+'</td>'
                    + '<td class="num">'+Number(q.joriy).toLocaleString('uz-UZ')+'</td>'
                    + '<td class="num">'+Number(q.oldingi).toLocaleString('uz-UZ')+'</td>'
                    + '<td class="num '+farqCls+'">'+Number(q.farq).toLocaleString('uz-UZ')+'</td>'
                    + '<td class="num '+farqCls+'">'+farqFoizi+'</td></tr>';
            } else {
                html += '<tr class="mf-qator-row"><td class="tl">'+q.nomi+'</td><td class="num">'+moliyaviySummaHtml(q.summa, q.mock)+'</td></tr>';
            }
        });
        if (bolim.jami !== null && bolim.jami !== undefined) {
            if (solishtirma) {
                var jFarqCls = bolim.jami.farq > 0 ? 'mf-positive' : (bolim.jami.farq < 0 ? 'mf-negative' : '');
                var jFarqFoizi = (bolim.jami.farq_foizi === null || bolim.jami.farq_foizi === undefined) ? '<i class="bi bi-slash-circle" title="Solishtirish mumkin emas (oldingi davr 0)"></i>' : (bolim.jami.farq_foizi + '%');
                html += '<tr class="mf-jami-row"><td class="tl">JAMI — '+bolim.nomi+'</td>'
                    + '<td class="num">'+Number(bolim.jami.joriy).toLocaleString('uz-UZ')+'</td>'
                    + '<td class="num">'+Number(bolim.jami.oldingi).toLocaleString('uz-UZ')+'</td>'
                    + '<td class="num '+jFarqCls+'">'+Number(bolim.jami.farq).toLocaleString('uz-UZ')+'</td>'
                    + '<td class="num '+jFarqCls+'">'+jFarqFoizi+'</td></tr>';
            } else {
                html += '<tr class="mf-jami-row"><td class="tl">JAMI — '+bolim.nomi+'</td><td class="num">'+Number(bolim.jami).toLocaleString('uz-UZ')+" so'm</td></tr>";
            }
        }
    });
    (natija.yakuniy || []).forEach(function(y) {
        html += '<tr class="mf-yakuniy-row"><td class="tl">'+y.nomi+'</td><td class="num">'+Number(y.summa).toLocaleString('uz-UZ')+" so'm</td></tr>";
    });
    tbody.innerHTML = html || '<tr><td colspan="2" class="text-center text-muted py-4">Ma\'lumot topilmadi</td></tr>';

    var ogohBox = document.getElementById('moliyaviy-ogohlantirish');
    ogohBox.innerHTML = natija.ogohlantirish ? '<div class="mf-ogohlantirish"><i class="bi bi-exclamation-triangle me-1"></i>'+natija.ogohlantirish+'</div>' : '';

    document.getElementById('moliyaviy-davr-matni').textContent = (natija.dan_sana || '') + ' — ' + (natija.gacha_sana || '');
}

function moliyaviySorovBadge() {
    var body = {
        hisobot_turi: MF_JORIY_TURI,
        sana_turi: 'maxsus',
        dan_sana: document.getElementById('mf-dan-sana').value,
        gacha_sana: document.getElementById('mf-gacha-sana').value,
        filial_id: document.getElementById('mf-filial-id')?.value || '',
    };
    if (MF_JORIY_TURI === 'solishtirma') {
        body.ichki_turi = document.getElementById('mf-ichki-turi').value;
        body.oldingi_davr_turi = document.getElementById('mf-oldingi-davr-turi').value;
        body.oldingi_dan_sana = document.getElementById('mf-oldingi-dan').value;
        body.oldingi_gacha_sana = document.getElementById('mf-oldingi-gacha').value;
    }
    return body;
}

async function moliyaviyShakllantirish() {
    document.getElementById('moliyaviy-tbody').innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>';
    const res = await fetch('{{ route("hisobotlar.konstruktor.moliyaviy") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(moliyaviySorovBadge()),
    });
    if (!res.ok) { document.getElementById('moliyaviy-tbody').innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Xatolik yuz berdi</td></tr>'; return; }
    const natija = await res.json();
    moliyaviyNatijaChiz(natija, MF_JORIY_TURI);
}

function moliyaviyEksport(format) {
    var body = moliyaviySorovBadge();
    body.format = format;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("hisobotlar.konstruktor.moliyaviy") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
    for (var k in body) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = body[k];
        form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
}

function ustunlarKorsatish(modulKey) {
    var mod = MODULLAR[modulKey] || {};
    var ustunlar = mod.ustunlar || {};
    var html = '';
    for (var k in ustunlar) {
        var checked = TANLANGAN_USTUNLAR.length === 0 || TANLANGAN_USTUNLAR.includes(k);
        html += '<div class="col"><label class="col-check">';
        html += '<input type="checkbox" name="ustunlar[]" value="'+k+'" '+(checked?'checked':'')+'>';
        html += '<span style="font-size:.8rem">'+ustunlar[k]+'</span>';
        html += '</label></div>';
    }
    document.getElementById('ustunlar-body').innerHTML = html;
}

function ustunlarModalOch() {
    new bootstrap.Modal(document.getElementById('ustunlar-modal')).show();
}

function shartlarKorsatish(modulKey) {
    var mod = MODULLAR[modulKey] || {};
    var shartlar = mod.shartlar || {};
    var html = '';
    for (var k in shartlar) {
        var s = shartlar[k];
        var joriyQiymat = (JORIY_MODUL === modulKey && JORIY_SHARTLAR && JORIY_SHARTLAR[k]) ? JORIY_SHARTLAR[k] : '';
        html += '<div class="rb-shart-box"><label>'+s.nomi+'</label>';
        if (s.tur === 'select') {
            html += '<select name="shartlar['+k+']" class="rb-inp">';
            html += '<option value="">— Hammasi —</option>';
            (s.qiymatlar||[]).forEach(function(v) {
                html += '<option value="'+v+'" '+(joriyQiymat===v?'selected':'')+'>'+v+'</option>';
            });
            html += '</select>';
        } else if (s.tur === 'number') {
            html += '<input type="number" name="shartlar['+k+']" class="rb-inp" style="width:100px" value="'+joriyQiymat+'" placeholder="Minimal...">';
        } else {
            html += '<input type="text" name="shartlar['+k+']" class="rb-inp" value="'+joriyQiymat+'">';
        }
        html += '</div>';
    }
    document.getElementById('shartlar-body').innerHTML = html || '<span class="text-muted small">Filtr yo\'q</span>';
}

function guruhlashKorsatish(modulKey) {
    var mod = MODULLAR[modulKey] || {};
    var qiymatlar = mod.guruhlash_qiymatlari || null;
    var group = document.getElementById('guruhlash-group');
    var select = document.getElementById('guruhlash-select');
    if (!qiymatlar) { group.style.display = 'none'; document.getElementById('guruhlash-input').value = ''; return; }
    group.style.display = '';
    var html = '<option value="">Guruhlanmasin</option>';
    for (var k in qiymatlar) {
        html += '<option value="'+k+'" '+(JORIY_GURUHLASH===k?'selected':'')+'>'+qiymatlar[k]+'</option>';
    }
    select.innerHTML = html;
}

function barchasini(belgi) {
    document.querySelectorAll('#ustunlar-body input[type=checkbox]').forEach(cb => cb.checked = belgi);
}


function sanaTuriTanla(tur) {
    document.getElementById('sana-turi-input').value = tur;
    document.querySelectorAll('.rb-mini[data-sana]').forEach(b => b.classList.remove('active'));
    var btn = document.querySelector('.rb-mini[data-sana="'+tur+'"]');
    if (btn) btn.classList.add('active');
}

function sanaTezkor(tur) {
    sanaTuriTanla(tur);
    var [dan, gacha] = SANA_TEZKOR_DIAPAZON[tur]();
    document.getElementById('dan-sana').value = dan;
    document.getElementById('gacha-sana').value = gacha;
}

function tozalash() {
    document.querySelectorAll('#shartlar-body input, #shartlar-body select').forEach(el => el.value = '');
    barchasini(true);
}

function shablonSaqlashModalOch() {
    document.getElementById('shablon-nomi').value = '';
    new bootstrap.Modal(document.getElementById('shablon-saqlash-modal')).show();
}

async function shablonSaqlashYubor() {
    var nomi = document.getElementById('shablon-nomi').value.trim();
    if (!nomi) { alert("Shablon nomini kiriting!"); return; }

    var ustunlar = Array.from(document.querySelectorAll('#ustunlar-body input:checked')).map(i => i.value);
    var shartlar = {};
    document.querySelectorAll('#shartlar-body [name^="shartlar["]').forEach(el => {
        var key = el.name.match(/shartlar\[(.+)\]/)[1];
        if (el.value) shartlar[key] = el.value;
    });

    const body = {
        nomi: nomi,
        modul: JORIY_MODUL,
        ustunlar: ustunlar,
        shartlar: shartlar,
        sana_turi: document.getElementById('sana-turi-input').value,
        dan_sana: document.getElementById('dan-sana').value,
        gacha_sana: document.getElementById('gacha-sana').value,
        guruhlash: document.getElementById('guruhlash-input').value,
    };

    const res = await fetch('{{ route("hisobotlar.konstruktor.shablon.saqlash") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!data.ok) { alert("Saqlashda xatolik yuz berdi."); return; }
    bootstrap.Modal.getInstance(document.getElementById('shablon-saqlash-modal')).hide();
}

async function shablonPanelOch() {
    new bootstrap.Modal(document.getElementById('shablon-royxat-modal')).show();
    const res = await fetch('{{ route("hisobotlar.konstruktor.shablonlar") }}', { headers: { 'Accept': 'application/json' } });
    const list = await res.json();
    const body = document.getElementById('shablon-royxat-body');
    if (!list.length) { body.innerHTML = '<div class="text-center text-muted small py-3">Hali shablon saqlanmagan</div>'; return; }
    body.innerHTML = list.map(s => `
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <div class="fw-bold small">${s.nomi}</div>
                <div class="text-muted" style="font-size:.72rem">${MODULLAR[s.modul]?.nomi || s.modul}</div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary py-0" onclick='shablonYukla(${JSON.stringify(s).replace(/'/g,"&#39;")})'>Ochish</button>
                <button class="btn btn-sm btn-outline-danger py-0" onclick="shablonOchirish(${s.id})"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    `).join('');
}

function shablonYukla(s) {
    modulTanla(s.modul);
    TANLANGAN_USTUNLAR = s.ustunlar || [];
    ustunlarKorsatish(s.modul);
    JORIY_SHARTLAR = s.shartlar || {};
    shartlarKorsatish(s.modul);
    if (s.guruhlash) { JORIY_GURUHLASH = s.guruhlash; guruhlashKorsatish(s.modul); document.getElementById('guruhlash-input').value = s.guruhlash; }
    if (s.sana_turi && s.sana_turi !== 'maxsus') {
        sanaTezkor(s.sana_turi);
    } else {
        sanaTuriTanla('maxsus');
        if (s.dan_sana) document.getElementById('dan-sana').value = s.dan_sana;
        if (s.gacha_sana) document.getElementById('gacha-sana').value = s.gacha_sana;
    }
    bootstrap.Modal.getInstance(document.getElementById('shablon-royxat-modal')).hide();
    document.getElementById('konstruktor-form').requestSubmit();
}

async function shablonOchirish(id) {
    if (!confirm("Shablonni o'chirishni tasdiqlaysizmi?")) return;
    await fetch(`/hisobotlar/konstruktor/shablon/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    });
    shablonPanelOch();
}

document.addEventListener('DOMContentLoaded', function() {
    ustunlarKorsatish(JORIY_MODUL);
    shartlarKorsatish(JORIY_MODUL);
    guruhlashKorsatish(JORIY_MODUL);
    sanaTuriTanla(JORIY_SANA_TURI);
});
</script>
@endpush
