# ADSmarket POS moduli — HANDOFF SUMMARY

> Server: `/var/www/adsmarket` (SSH: `nasiyapro`, config `/d/ClaudeProjekt/.ssh/config`)
> Local nusxa: `D:\ClaudeProjekt\adsmarket_sql\`
> Git: server repoda mavjud (`git status` — o'zgarishlar hali commit qilinmagan)
> Sana: 2026-07-07

## 1. Umumiy kontekst

Foydalanuvchi (Uzbek/Cyrillic, 28 bo'limli spec) to'liq alohida **POS (Kassa) moduli**
buyurtma qildi: unified POS menyu guruhi, Dashboard, Kassir smenasi, Qaytim/Vozvrat,
Fullscreen PIN-kirish terminal, multi-barkod, chek/printer sozlamalari, to'liq RBAC va
audit-log. Ish **bosqichma-bosqich** rejim bilan olib borildi — foydalanuvchi har
bosqichdan keyin keyingisiga aniq buyruq berdi:

- ✅ **Bosqich 1** — POS guruh (sidebar), POS Dashboard, POS hisobotlar
- ✅ **Bosqich 2** — Kassir smenasi (ochish/yopish/topshirish)
- ✅ **Bosqich 3** — Qaytim/Vozvrat moduli
- ✅ **Bosqich 4 (qism 1)** — Fullscreen PIN-kirish rejimi — **TUGALLANDI va TEST QILINDI**
- ⬜ **Bosqich 4 (qolgan qismlari)** — multi-barkod, chek/printer sozlamalari — **BOSHLANMAGAN**

**Muhim tamoyil butun sessiya davomida**: *"Mavjud ishlayotgan funksiyalarni buzma."*
Shu sababli ko'p joyda **refactor emas, duplikatsiya** yo'li tanlandi (masalan,
`terminal/sotish.blade.php` — `ombor/pos/index.blade.php`ning mustaqil nusxasi).

---

## 2. Bosqich 1 — POS guruh, Dashboard, Hisobotlar (TUGALLANGAN)

- Sidebar'da yagona **"POS"** guruhi yaratildi (`layouts/app.blade.php`), eski
  "Kassa POS" / "Sotuv tarixi" / "Kassalar" / "Kassa transferi" shu guruhga ko'chirildi
  (marshrutlar/ruxsatlar buzilmadi).
- Yangi **POS Dashboard** (`/pos/dashboard`, `PosController::dashboard()`,
  `ombor/pos/dashboard.blade.php`) — real ma'lumot bilan kartalar+jadvallar.
- Yangi **POS hisobotlar** (`/pos/hisobotlar`, `PosController::hisobotlar()`,
  `ombor/pos/hisobotlar.blade.php`) — filtrlar + Excel export.
- **Tuzatilgan xato**: `dashboard()` dagi JOIN'da noaniq `holat` ustuni
  (`pos_sotuv.holat` bilan `foydalanuvchilar.holat` orasida ziddiyat) — qator
  qo'shib aniqlashtirildi.

## 3. Bosqich 2 — Kassir smenasi (TUGALLANGAN)

**Yangi jadval**: `057_pos_smenalar.php`
- `pos_smenalar`: smena_raqami (unique, format `SM-{filial}-{Ymd}-{seq}`), filial_id,
  xodim_id, ochilgan_vaqt, yopilgan_vaqt, dastlabki_qoldiq, hisoblangan_qoldiq,
  yakuniy_qoldiq, farq, topshirilgan_summa, topshirish_holati
  (`yoq/kutilmoqda/tasdiqlangan/rad_etildi`), qabul_qilgan_id, qabul_vaqti,
  rad_sababi, holat (`ochiq/yopiq`).
- `pos_sotuv` jadvaliga `smena_id` (nullable FK) qo'shildi.

**Model**: `app/Models/PosSmena.php`
- `joriyNaqdQoldiq()` — joriy naqd qoldiqni hisoblaydi:
  ```php
  dastlabki_qoldiq + SUM(naqd_summa - qayta_pul) [tugallangan sotuvlar]
                    - SUM(qaytimlar naqd) [tugallangan qaytimlar]
  ```
- `scopeOchiq()`, `static yangiSmenaRaqami($filialId)`.

**Controller**: `app/Http/Controllers/PosSmenaController.php`
- `static joriy(int $filialId): ?PosSmena` — **markaziy metod**, boshqa
  controller'lar (`PosController`, `PosTerminalController`) shundan foydalanadi.
- `ochishForma/ochish/yopishForma/yopish/topshirish/topshirishTasdiqlash/
  topshirishRad/royxat/korish`.
- `egalikTekshir()` — faqat egasi yoki admin/menejer amal qila oladi (403).

**Muhim biznes-logika**: `PosController::index()` va `store()` endi **majburiy**
ochiq smena talab qiladi — smena bo'lmasa `pos.smena.ochish-forma`ga
redirect (yoki `store()`da 422 xato).

**Yangi view'lar**: `resources/views/ombor/pos/smena/{ochish,yopish,royxat,korish}.blade.php`

**Sidebar/route**: `pos.smena.*` prefiksi, "Kassir smenalari" nav-item.

## 4. Bosqich 3 — Qaytim/Vozvrat (TUGALLANGAN)

**Yangi jadvallar**:
- `058_pos_qaytim_kategoriya.php` — `pul_kategoriyalar`ga `CF-2740` ("POS savdo
  qaytimi", yunalish=chiqim, ota=`CF-2700`) qo'shadi.
- `059_pos_qaytimlar.php` — `pos_qaytimlar` (qaytim_raqami unique
  `QR-{filial}-{Ymd}-{seq}`, sotuv_id, smena_id — **qaytarish vaqtidagi** smena,
  tolov_turi, jami_summa, sabab, holat) + `pos_qaytim_tafsilot`.

**Model**: `PosQaytim.php`, `PosQaytimTafsilot.php`.
`PosTafsilot::qaytarilganMiqdor()` — shu tafsilot bo'yicha jami qaytarilgan
miqdorni hisoblaydi (faqat `holat=tugallangan` qaytimlar bo'yicha) — **ortiqcha
qaytarishni oldini olish** shu metodga tayanadi.

**Controller**: `app/Http/Controllers/PosQaytimController.php`
- `boshlash/saqlash/royxat/korish`.
- `saqlash()`: DB::transaction ichida yozuv + `StockService::kirim()` (ombor
  qoldig'ini tiklaydi), keyin **transaction TASHQARISIDA**
  `TulovService::pulOqimigaYozKassaTuri(..., kategoriyaKodi:'CF-2740',
  manbaTur:'pos_qaytim', yunalish:'chiqim')` — pul oqimidan **chiqim** yoziladi.
- Har qatorda live tekshiruv: `qolgan = sotilgan - qaytarilgan` — 0 bo'lsa
  ro'yxatdan chiqariladi, ortiqcha kiritilsa 422 xato.

**Yangi view'lar**: `resources/views/ombor/pos/qaytim/{boshlash,royxat,korish}.blade.php`
`ombor/pos/tarix.blade.php` va `chek.blade.php`ga "Qaytim" tugmasi qo'shildi
(faqat `holat===tugallangan` bo'lganda).

**To'liq end-to-end test qilingan**: smena ochish → sotuv (−2 dona) → qaytim
(+1 dona) → ombor qoldig'i tiklanishi, smena naqd balansi, `pul_oqimlari`
`yunalish=chiqim` yozuvi, ortiqcha qaytarish bloklanishi — barchasi tasdiqlangan,
test ma'lumotlari tozalangan.

## 5. Bosqich 4 (qism 1) — Fullscreen PIN-kirish rejimi (TUGALLANGAN, JORIY SESSIYADA)

### 5.1 Yangi migratsiyalar
- **`060_foydalanuvchi_pin.php`** — `foydalanuvchilar`ga qo'shadi:
  `pin_kod` (nullable string), `pin_bloklangan_gacha` (nullable timestamp),
  `pin_xato_soni` (unsignedTinyInteger, default 0).
- **`061_pos_terminal_loglar.php`** — `pos_terminal_loglar` jadvali: xodim_id,
  filial_id, `hodisa` enum(`kirish/xato_pin/bloklandi/qulflash/yechish/chiqish`),
  ip, izoh, timestamps.

### 5.2 Model o'zgarishlari
**`app/Models/Foydalanuvchi.php`**:
```php
protected $fillable = [..., 'pin_kod', 'pin_xato_soni', 'pin_bloklangan_gacha', ...];
protected $hidden   = [..., 'pin_kod', ...];
protected $casts    = [..., 'pin_kod' => 'hashed', 'pin_bloklangan_gacha' => 'datetime'];

public function pinTogri(string $pin): bool { ... Hash::check ... }
public function pinBloklanganmi(): bool { ... isFuture() ... }
```
> ⚠️ **MUHIM TUZATILGAN XATO**: dastlab `pin_xato_soni` va `pin_bloklangan_gacha`
> `$fillable`da YO'Q edi → `update()` chaqiruvlari bu maydonlarni **jimgina
> e'tiborsiz qoldirar edi** (mass-assignment himoyasi). Tinker orqali test qilinmaguncha
> aniqlanmagan. Hozir tuzatilgan va deploy qilingan.

**`app/Models/PosTerminalLog.php`** — yangi model, `static yoz($hodisa, $xodimId, $filialId, $izoh=null)`.

### 5.3 Controller
**`app/Http/Controllers/PosTerminalController.php`** — yangi:
- `pinForma()` — kassir tanlash + PIN klaviatura ekrani.
- `pinTekshir(Request)` — dastlabki kirish, xato hisoblash, bloklash (5 xato →
  15 daqiqa blok), `session(['pos_terminal' => [...]])`, `PosTerminalLog::yoz('kirish', ...)`.
- `index()` — fullscreen savdo ekrani; sessiyani tekshiradi, ochiq smenani talab
  qiladi (`PosSmenaController::joriy()`).
- `qulflash()` — ekranni JS orqali qulflaydi (`session('pos_terminal.qulflangan')=true`).
- `yechish(Request)` — PIN qayta kiritilganda: **barcha nomzod kassirlar bo'yicha
  sikl** (`pinTogri()` har biriga), chunki hash to'g'ridan-to'g'ri qidirilmaydi.
  Boshqa kassir aniqlansa `boshqa_kassir:true` qaytaradi — frontend savatni
  tozalab, sahifani qayta yuklaydi.
- `chiqish()` — sessiyani tozalaydi, `pos.terminal.pin-forma`ga redirect.

> ⚠️ **MUHIM TUZATILGAN XATO #2**: `pinForma()` va `yechish()`dagi nomzodlar
> so'rovi dastlab `->where('filial_id', $filialId)` bilan filtrlangan edi.
> **Admin (`rol=admin`) foydalanuvchilarning `filial_id`si odatda `NULL`** —
> shu sabab admin PIN bilan hech qachon topilmas edi ("PIN noto'g'ri" xatosi,
> hatto to'g'ri PIN kiritilsa ham). Tuzatildi:
> `->where(fn($q) => $q->where('filial_id', $filialId)->orWhere('rol', 'admin'))`

### 5.4 Marshrutlar (`routes/web.php`)
```php
Route::prefix('terminal')->name('terminal.')->group(function () {
    Route::get('/pin',       [PosTerminalController::class, 'pinForma'])->name('pin-forma');
    Route::post('/pin',      [PosTerminalController::class, 'pinTekshir'])->name('pin-tekshir');
    Route::get('/',          [PosTerminalController::class, 'index'])->name('index');
    Route::post('/qulflash', [PosTerminalController::class, 'qulflash'])->name('qulflash');
    Route::post('/yechish',  [PosTerminalController::class, 'yechish'])->name('yechish');
    Route::get('/chiqish',   [PosTerminalController::class, 'chiqish'])->name('chiqish');
});
```
(shu `Route::middleware('auth')` blok ichida, `pos.*` guruhidan keyin joylashgan.)

`admin.foydalanuvchilar.pin` — `POST /admin/foydalanuvchilar/{foydalanuvchi}/pin`
→ `AdminController::foydalanuvchiPinOrnat()` (parol-reset patterniga o'xshash,
`digits_between:4,6|confirmed` validatsiya bilan).

### 5.5 View'lar (yangi)
- **`resources/views/terminal/pin.blade.php`** — mustaqil (o'z `<html>`,
  Bootstrap 5.3.3 CDN), kassir tanlash select + raqamli klaviatura.
- **`resources/views/terminal/sotish.blade.php`** — mustaqil fullscreen savdo
  ekrani. **`ombor/pos/index.blade.php`dan qasddan duplikatsiya qilingan**
  (refactor emas!) — bir xil `/pos/tovarlar` va `/pos/saqlash` endpoint'laridan
  foydalanadi, ustiga: qulflash overlay (PIN qayta kiritish), avtomatik
  qulflash timer (10 daqiqa, `autoLockDaqiqa` orqali sozlanadigan).

### 5.6 Admin UI
`resources/views/admin/foydalanuvchilar.blade.php` — har bir kassir/menejer/admin
qatoriga PIN o'rnatish tugmasi (🔢 ikonka) + modal qo'shildi (parol-reset
modaliga o'xshash pattern).

### 5.7 Sidebar
`layouts/app.blade.php`:
- "Fullscreen kassa" nav-item (`target="_blank"`, `route('terminal.pin-forma')`)
  — "Kassa POS" ostiga qo'shildi.
- `$aktiv_grup` aniqlash qatoriga `terminal.*` qo'shildi.

### 5.8 Xavfsizlik chegarasi (ONGLI QAROR — foydalanuvchiga eslatish kerak)
PIN qatlami **faqat "qaysi kassir terminaldan foydalanmoqda"ni aniqlaydi**
(smena/audit maqsadida). Haqiqiy xavfsizlik chegarasi — odatiy Laravel
`auth` sessiyasi (qurilma allaqachon tizimga kirgan bo'lishi kerak).
`/pos/tovarlar` va `/pos/saqlash` endpoint'lari **PIN bo'yicha qayta
tekshirilmaydi** — ular `Auth::user()->filial_id`ga tayanadi, PIN-sessiyaga
emas. Bu **qasddan qilingan tanlov** (mavjud, test qilingan POS asosini
buzmaslik uchun), lekin **haqiqiy production xavfsizlik talabi** bo'lsa,
kelajakda qayta ko'rib chiqish kerak.

---

## 6. O'zgargan/yaratilgan fayllar ro'yxati (server repo, `git status` asosida)

### Yangi (untracked) — bevosita POS bilan bog'liq:
```
app/Http/Controllers/PosQaytimController.php
app/Http/Controllers/PosSmenaController.php
app/Http/Controllers/PosTerminalController.php
app/Models/PosQaytim.php
app/Models/PosQaytimTafsilot.php
app/Models/PosSmena.php
app/Models/PosTerminalLog.php
database/migrations/057_pos_smenalar.php
database/migrations/058_pos_qaytim_kategoriya.php
database/migrations/059_pos_qaytimlar.php
database/migrations/060_foydalanuvchi_pin.php
database/migrations/061_pos_terminal_loglar.php
resources/views/ombor/pos/dashboard.blade.php
resources/views/ombor/pos/hisobotlar.blade.php
resources/views/ombor/pos/qaytim/         (papka)
resources/views/ombor/pos/smena/          (papka)
resources/views/terminal/                 (papka: pin.blade.php, sotish.blade.php)
```

### O'zgartirilgan (modified) — bevosita POS bilan bog'liq:
```
app/Http/Controllers/AdminController.php     (+ foydalanuvchiPinOrnat)
app/Http/Controllers/PosController.php       (+ smena majburiyligi, dashboard/hisobotlar)
app/Models/Foydalanuvchi.php                 (+ PIN maydonlari/metodlar)
app/Models/PosSotuv.php                      (+ smena_id)
app/Models/PosTafsilot.php                   (+ qaytarilganMiqdor)
resources/views/admin/foydalanuvchilar.blade.php  (+ PIN modal)
resources/views/layouts/app.blade.php        (+ POS guruh, Fullscreen kassa link)
resources/views/ombor/pos/chek.blade.php     (+ Qaytim tugmasi)
resources/views/ombor/pos/index.blade.php    (+ smena-bar)
resources/views/ombor/pos/tarix.blade.php    (+ Qaytim tugmasi)
routes/web.php                               (+ pos.smena.*, pos.qaytim.*, terminal.*, admin PIN route)
```

### Boshqa (bu sessiyaga bevosita aloqasi yo'q, oldingi sessiyalardan qolgan
### tugallanmagan/tugallangan ishlar — HANDOFF uchun eslatib o'tiladi, lekin
### POS spec doirasiga kirmaydi):
```
Bonus tovar avtomatik hisoblash (047-049 migratsiyalar, PLReportService,
  BLReportService, hisobot/bonus_tovarlar.blade.php)
Etiketka shablonlari (050, EtiketkaShablon model, BarcodeLabelController)
Hisobot shablonlari (051, HisobotShablon model, hisobot/konstruktor.blade.php)
Balans tenglashtiruvchi/rezerv (052-053, pul-oqimlari/balans/index.blade.php)
Eski-ID unique ustunlar (054-056 — insertOrIgnore dedup uchun)
Kirim/Chiqim hujjatlar refactor (ombor/kirim/, ombor/chiqim/ papkalar,
  hujjatlar/ subpapkalar, ImportService)
lang/uz/validation.php — yangi validatsiya xabarlari
```
> Bu fayllar HAM commit qilinmagan holatda turibdi. Agar ular boshqa
> (tugallangan) ishlar bo'lsa, POS commit'idan **alohida** commit qilish
> tavsiya etiladi — aralashtirmaslik uchun. Batafsil TODO_NEXT.md'da.

---

## 7. Test qilingan narsalar (bu sessiyada, Bosqich 4)

Barchasi **tinker** orqali (`php artisan tinker /tmp/test_*.php`) + **curl**
route-darajasida (`302` kutilgan, `500` yo'q, `laravel.log`da yangi xato yo'q):

1. `Foydalanuvchi::pinTogri()` / `pinBloklanganmi()` — to'g'ri/noto'g'ri PIN,
   hash tekshiruvi.
2. Bloklash logikasi: `pin_xato_soni++` → 5-chida `pin_bloklangan_gacha` o'rnatiladi
   → `pinBloklanganmi()=true`.
3. **To'liq terminal oqimi**: `pinForma()` → noto'g'ri PIN (422) → to'g'ri PIN (200,
   sessiya o'rnatiladi) → `index()` (ochiq smena bilan ishlaydi) → `qulflash()` →
   `yechish()` noto'g'ri PIN (422) → `yechish()` to'g'ri PIN (200,
   `boshqa_kassir:false`) → `chiqish()` (sessiya tozalanadi).
4. `AdminController::foydalanuvchiPinOrnat()` — PIN o'rnatish, keyin
   `pinTogri()` bilan tasdiqlash.
5. Route darajasida: `/terminal/pin`, `/terminal`, `/admin/foydalanuvchilar`,
   `/pos/dashboard` — barchasi `302` (login redirect), 500 yo'q.

**Barcha test ma'lumotlari** (PIN qiymatlari, `pos_terminal_loglar` yozuvlari)
tozalangan — production ma'lumotlarga ta'sir qilmagan.

> ❌ **Test QILINMAGAN**: real brauzerda PIN klaviaturasi bosish, qulflash
> overlay UI/UX, avtomatik-qulflash timer real vaqt oqimida, "boshqa kassir"
> holatida frontend `location.reload()` xatti-harakati, mobil/planshet
> ekranida fullscreen ko'rinish. Bular faqat **backend/logic darajasida**
> tasdiqlangan.

---

## 8. Keyingi bosqichlar (foydalanuvchi so'ragan, hali BOSHLANMAGAN)

1. **Multi-barkod** — bitta tovar uchun bir nechta shtrix-kod
   (`product_barcodes` jadvali specda ko'rsatilgan, hali yaratilmagan).
2. **Chek/printer sozlamalari** — `pos_printer_settings`, `pos_receipt_templates`
   jadvallari, POS sozlamalar sahifasi — hali boshlanmagan.
3. **POS sozlamalari** umumiy sahifasi (`pos_settings` jadvali) — auto-lock
   daqiqasi hozircha kodda hardcoded (`AUTO_LOCK_DAQIQA=10`), sozlamalar
   sahifasi orqali boshqarilishi kerak edi spec bo'yicha.
4. Fullscreen terminalda haqiqiy browser-darajasida test (yuqoridagi 7-band).
